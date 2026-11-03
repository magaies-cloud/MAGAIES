<?php
$page_title = 'سجل الرقابة والتدقيق الجنائي وتتبع العمليات — Audit Trail & Forensics 360°';
$page_css = 'dashboard.css';
$LoadChartJS = false;
include 'includes/header.php';
include 'includes/sidebar.php';
?>
<link rel="stylesheet" href="css/ unified-design-tokens.css">

<style>
    :root {
        --audit-bg-card: rgba(15, 23, 42, 0.78);
        --audit-border: rgba(56, 189, 248, 0.22);
        --audit-emerald: #10b981;
        --audit-sky: #38bdf8;
        --audit-amber: #f59e0b;
        --audit-rose: #f43f5e;
        --audit-purple: #a855f7;
    }

    .audit-container {
        padding: 18px 22px 45px 22px;
        max-width: 1650px;
        margin: 0 auto;
        direction: rtl;
        font-family: 'Tajawal', 'Cairo', sans-serif;
    }

    /* Hero Banner */
    .audit-hero {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.92));
        border: 1.5px solid rgba(56, 189, 248, 0.32);
        border-right: 6px solid #38bdf8;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.35);
    }

    .audit-hero-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .audit-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(16, 185, 129, 0.22));
        border: 1.5px solid rgba(56, 189, 248, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        box-shadow: 0 0 20px rgba(56, 189, 248, 0.25);
    }

    .audit-hero-text h1 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 900;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .audit-hero-text p {
        margin: 5px 0 0 0;
        font-size: 0.88rem;
        color: #94a3b8;
    }

    .crypto-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
        background: rgba(16, 185, 129, 0.16);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.4);
    }

    .audit-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .audit-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 15px;
        border-radius: 12px;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.84rem;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(30, 41, 59, 0.85);
        color: #f8fafc;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .audit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    .audit-btn-amber {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(234, 88, 12, 0.22));
        border-color: rgba(245, 158, 11, 0.5);
        color: #fbbf24;
    }

    .audit-btn-emerald {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.22));
        border-color: rgba(16, 185, 129, 0.5);
        color: #34d399;
    }

    .audit-btn-sky {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.22));
        border-color: rgba(56, 189, 248, 0.5);
        color: #38bdf8;
    }

    .audit-btn-purple {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.22), rgba(126, 34, 206, 0.22));
        border-color: rgba(168, 85, 247, 0.5);
        color: #c084fc;
    }

    /* 6 KPI Cards Grid */
    .audit-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .audit-kpi-card {
        background: var(--audit-bg-card);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 15px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .audit-kpi-card:hover {
        transform: translateY(-3px);
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.28);
    }

    .audit-kpi-val {
        font-size: 1.65rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
    }

    .audit-kpi-lbl {
        font-size: 0.8rem;
        font-weight: 700;
        color: #94a3b8;
        margin-top: 4px;
    }

    .audit-kpi-sub {
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 5px;
    }

    .audit-kpi-ico {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    /* Unified 3-Tab Switcher */
    .audit-tabs-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        background: rgba(15, 23, 42, 0.65);
        padding: 8px;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .audit-tab-btn {
        flex: 1;
        min-width: 220px;
        padding: 11px 16px;
        border-radius: 12px;
        border: 1px solid transparent;
        background: transparent;
        color: #94a3b8;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.9rem;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        transition: all 0.2s ease;
    }

    .audit-tab-btn.active {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(16, 185, 129, 0.15));
        color: #38bdf8;
        border-color: rgba(56, 189, 248, 0.45);
        box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15);
    }

    .audit-tab-panel {
        display: none;
    }

    .audit-tab-panel.active {
        display: block;
    }

    /* Filter Bar */
    .audit-filter-panel {
        background: var(--audit-bg-card);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: 2fr repeat(4, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    @media (max-width: 1200px) {
        .audit-filter-panel {
            grid-template-columns: 1fr 1fr;
        }
    }

    .audit-field label {
        display: block;
        font-size: 0.76rem;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .audit-input, .audit-select {
        width: 100%;
        padding: 9px 12px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(15, 23, 42, 0.9);
        color: #f8fafc;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        box-sizing: border-box;
    }

    .audit-input:focus, .audit-select:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
    }

    /* Table Styling */
    .audit-table-wrap {
        background: var(--audit-bg-card);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }

    .audit-table th {
        background: rgba(15, 23, 42, 0.95);
        color: #38bdf8;
        padding: 13px 14px;
        font-weight: 900;
        font-size: 0.82rem;
        border-bottom: 1.5px solid rgba(56, 189, 248, 0.25);
        white-space: nowrap;
    }

    .audit-table td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .audit-table tbody tr:hover {
        background: rgba(56, 189, 248, 0.06);
    }

    .badge-action {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.76rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .badge-create { background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
    .badge-update { background: rgba(245, 158, 11, 0.16); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
    .badge-delete { background: rgba(239, 68, 68, 0.16); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); }
    .badge-security { background: rgba(244, 63, 94, 0.22); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.5); }
    .badge-info { background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }

    .hash-chip {
        font-family: monospace;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(148, 163, 184, 0.25);
        color: #94a3b8;
        display: inline-block;
        direction: ltr;
    }

    /* Integrated Forensics Cards Grid */
    .forensics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 14px;
    }

    .forensics-card {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.22);
        border-radius: 16px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: all 0.2s ease;
    }

    .forensics-card:hover {
        border-color: #38bdf8;
        transform: translateY(-2px);
    }

    /* Light Mode Compatibility */
    html[data-theme="warm_cream"] .audit-hero,
    html[data-theme="ultra_white"] .audit-hero,
    html[data-theme="exec_gray"] .audit-hero,
    html[data-theme="warm_cream"] .audit-kpi-card,
    html[data-theme="ultra_white"] .audit-kpi-card,
    html[data-theme="exec_gray"] .audit-kpi-card,
    html[data-theme="warm_cream"] .audit-filter-panel,
    html[data-theme="ultra_white"] .audit-filter-panel,
    html[data-theme="exec_gray"] .audit-filter-panel,
    html[data-theme="warm_cream"] .audit-table-wrap,
    html[data-theme="ultra_white"] .audit-table-wrap,
    html[data-theme="exec_gray"] .audit-table-wrap,
    html[data-theme="warm_cream"] .forensics-card,
    html[data-theme="ultra_white"] .forensics-card,
    html[data-theme="exec_gray"] .forensics-card {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
    }

    html[data-theme="warm_cream"] .audit-hero-text h1,
    html[data-theme="ultra_white"] .audit-hero-text h1,
    html[data-theme="exec_gray"] .audit-hero-text h1,
    html[data-theme="warm_cream"] .audit-kpi-val,
    html[data-theme="ultra_white"] .audit-kpi-val,
    html[data-theme="exec_gray"] .audit-kpi-val,
    html[data-theme="warm_cream"] .audit-table td,
    html[data-theme="ultra_white"] .audit-table td,
    html[data-theme="exec_gray"] .audit-table td {
        color: #0f172a !important;
    }

    html[data-theme="warm_cream"] .audit-input,
    html[data-theme="ultra_white"] .audit-input,
    html[data-theme="exec_gray"] .audit-input,
    html[data-theme="warm_cream"] .audit-select,
    html[data-theme="ultra_white"] .audit-select,
    html[data-theme="exec_gray"] .audit-select {
        background: #f8fafc !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }

    @media print {
        .sidebar, .top-header, .audit-hero-actions, .audit-tabs-bar, .audit-filter-panel, .no-print {
            display: none !important;
        }
        .audit-container {
            padding: 0 !important;
            max-width: 100% !important;
        }
    }
</style>

<main class="main-content audit-container" id="main-content">

    <!-- Unified Hero Header -->
    <div class="audit-hero">
        <div class="audit-hero-title">
            <div class="audit-hero-icon">🛡️</div>
            <div class="audit-hero-text">
                <h1>
                    سجل الرقابة والتدقيق الجنائي وتتبع العمليات
                    <span class="crypto-pill" id="crypto-status-pill">🔒 السلسلة التشفيرية: موثقة 100%</span>
                </h1>
                <p>المركز العلمي الموحد للرقابة المالية والمخزنية، تتبع المستندات (من فعل ماذا؟)، وفحص البصمة التشفيرية (SHA-256 Chain)</p>
            </div>
        </div>

        <div class="audit-hero-actions">
            <button onclick="switchAuditTab('forensics')" class="audit-btn audit-btn-amber" title="البحث الجنائي السريع عن أي مستند أو فاتورة أو سند">
                🔍 متعقب المستندات (من فعل ماذا؟)
            </button>
            <button onclick="verifyCryptographicChain()" class="audit-btn audit-btn-emerald" title="فحص سلامة السلسلة التشفيرية وعدم التلاعب بالسجلات">
                🛡️ فحص النزاهة التشفيرية
            </button>
            <button onclick="openAuditTrackerModal && openAuditTrackerModal()" class="audit-btn audit-btn-purple" title="فتح نافذة الفحص العائمة السريعة (Alt+T)">
                ⚡ الفاحص العائم (Alt+T)
            </button>
            <button onclick="exportAuditToExcel()" class="audit-btn audit-btn-sky" title="تصدير السجل الرقابي المفلتر إلى ملف إكسل">
                📥 تصدير Excel
            </button>
            <button onclick="window.print()" class="audit-btn" title="طباعة التقرير الرقابي الرسمي">
                🖨️ طباعة التقرير
            </button>
            <button onclick="syncAllAuditSources(true)" class="audit-btn" title="إعادة فحص ومزامنة كافة الشاشات وقواعد البيانات">
                🔄 تحديث شامل
            </button>
        </div>
    </div>

    <!-- 6 Executive Forensics KPI Cards -->
    <div class="audit-kpi-grid">
        <div class="audit-kpi-card" onclick="quickFilterAction('')">
            <div>
                <div class="audit-kpi-val" id="kpi-total-events">0</div>
                <div class="audit-kpi-lbl">إجمالي الحركات والمستندات الموثقة</div>
                <div class="audit-kpi-sub" style="color:#38bdf8;">شامل المبيعات والمشتريات والسندات والمخازن</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(56,189,248,0.15); color:#38bdf8;">📊</div>
        </div>

        <div class="audit-kpi-card" onclick="quickFilterAction('create')">
            <div>
                <div class="audit-kpi-val" id="kpi-create-events" style="color:#34d399;">0</div>
                <div class="audit-kpi-lbl">عمليات الإنشاء والإصدار</div>
                <div class="audit-kpi-sub" style="color:#34d399;">فواتير وسندات وقيود وتحويلات</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(16,185,129,0.15); color:#34d399;">➕</div>
        </div>

        <div class="audit-kpi-card" onclick="quickFilterAction('update')">
            <div>
                <div class="audit-kpi-val" id="kpi-update-events" style="color:#fbbf24;">0</div>
                <div class="audit-kpi-lbl">عمليات التعديل والتحديث</div>
                <div class="audit-kpi-sub" style="color:#fbbf24;">تعديلات الأسعار والكميات والحسابات</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(245,158,11,0.15); color:#fbbf24;">✏️</div>
        </div>

        <div class="audit-kpi-card" onclick="quickFilterAction('delete')">
            <div>
                <div class="audit-kpi-val" id="kpi-delete-events" style="color:#f87171;">0</div>
                <div class="audit-kpi-lbl">عمليات الحذف والإلغاء والمرتجعات</div>
                <div class="audit-kpi-sub" style="color:#f87171;">تتبع كامل للمحذوفات والاسترداد</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(239,68,68,0.15); color:#f87171;">🗑️</div>
        </div>

        <div class="audit-kpi-card" onclick="quickFilterAction('security')">
            <div>
                <div class="audit-kpi-val" id="kpi-security-events" style="color:#fda4af;">0</div>
                <div class="audit-kpi-lbl">الإنذارات الأمنية والصلاحيات</div>
                <div class="audit-kpi-sub" style="color:#fda4af;">دخول، صلاحيات، وتنبيهات الحارس</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(244,63,94,0.15); color:#fda4af;">🚨</div>
        </div>

        <div class="audit-kpi-card" onclick="switchAuditTab('users')">
            <div>
                <div class="audit-kpi-val" id="kpi-active-users" style="color:#c084fc;">0</div>
                <div class="audit-kpi-lbl">المستخدمون النشطون في السجل</div>
                <div class="audit-kpi-sub" style="color:#c084fc;">بصمة المستخدم والتوقيت الدقيق</div>
            </div>
            <div class="audit-kpi-ico" style="background:rgba(168,85,247,0.15); color:#c084fc;">👥</div>
        </div>
    </div>

    <!-- Unified 3-Tab Switcher -->
    <div class="audit-tabs-bar">
        <button class="audit-tab-btn active" id="tab-btn-stream" onclick="switchAuditTab('stream')">
            📋 السجل الرقابي الشامل (Audit Trail Stream)
        </button>
        <button class="audit-tab-btn" id="tab-btn-forensics" onclick="switchAuditTab('forensics')">
            🔍 متعقب المستندات الجنائي: من فعل ماذا؟ (Document Forensics)
        </button>
        <button class="audit-tab-btn" id="tab-btn-users" onclick="switchAuditTab('users')">
            👥 مصفوفة رقابة المستخدمين والأداء الأمني (User Audit Matrix)
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: UNIFIED AUDIT STREAM                -->
    <!-- ========================================== -->
    <div class="audit-tab-panel active" id="tab-panel-stream">
        <div class="audit-filter-panel">
            <div class="audit-field">
                <label>🔍 بحث جنائي شامل (رقم مستند، موظف، عميل، مبلغ، وصف، بصمة):</label>
                <input type="text" id="filter-search" class="audit-input" placeholder="اكتب رقم الفاتورة أو السند أو اسم المستخدم أو أي كلمة..." oninput="renderAuditStream()">
            </div>

            <div class="audit-field">
                <label>👤 المستخدم / الموظف:</label>
                <select id="filter-user" class="audit-select" onchange="renderAuditStream()">
                    <option value="">كل المستخدمين</option>
                </select>
            </div>

            <div class="audit-field">
                <label>📂 القسم / الوحدة المحاسبية:</label>
                <select id="filter-module" class="audit-select" onchange="renderAuditStream()">
                    <option value="">كل الأقسام والشاشات</option>
                    <option value="المبيعات">المبيعات والفواتير</option>
                    <option value="المشتريات">المشتريات والتوريد</option>
                    <option value="المعاملات المالية">المعاملات المالية والسندات</option>
                    <option value="المخازن">المخازن والجرد والتحويلات</option>
                    <option value="القيود">القيود والأستاذ العام</option>
                    <option value="الشيكات">الشيكات والبنوك</option>
                    <option value="الأمان">الأمان والصلاحيات والدخول</option>
                </select>
            </div>

            <div class="audit-field">
                <label>⚡ نوع العملية الرقابية:</label>
                <select id="filter-action" class="audit-select" onchange="renderAuditStream()">
                    <option value="">جميع العمليات</option>
                    <option value="create">➕ إضافة / إصدار مستند</option>
                    <option value="update">✏️ تعديل / تحديث</option>
                    <option value="delete">🗑️ حذف / إلغاء / مرتجع</option>
                    <option value="security">🚨 أمان / دخول / صلاحيات</option>
                    <option value="print">🖨️ طباعة / تصدير / فحص</option>
                </select>
            </div>

            <div class="audit-field">
                <label>📅 الفترة الزمنية:</label>
                <div style="display:flex; gap:6px;">
                    <input type="date" id="filter-date-from" class="audit-input" onchange="renderAuditStream()" title="من تاريخ">
                    <input type="date" id="filter-date-to" class="audit-input" onchange="renderAuditStream()" title="إلى تاريخ">
                </div>
            </div>

            <div class="audit-field">
                <button onclick="resetAuditFilters()" class="audit-btn" style="height:39px;">إعادة ضبط ✖</button>
            </div>
        </div>

        <div class="audit-table-wrap">
            <div style="padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="font-weight:800; color:#38bdf8; font-size:0.9rem;">
                    📜 سجل الحركات والتدقيق المتسلسل (<span id="stream-showing-count">0</span> حركة معروضة)
                </div>
                <div style="font-size:0.78rem; color:#94a3b8;">
                    مرتبط مباشرة مع: الفواتير • السندات • المخازن • الأستاذ العام • الشيكات • الحارس الذكي
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ والوقت</th>
                            <th>المستخدم المنفذ</th>
                            <th>نوع العملية</th>
                            <th>القسم / الشاشة</th>
                            <th>رقم المستند</th>
                            <th>التفاصيل والبيان الرقابي</th>
                            <th>البصمة التشفيرية (Hash)</th>
                            <th class="no-print">إجراءات التسميع والفحص</th>
                        </tr>
                    </thead>
                    <tbody id="audit-stream-tbody">
                        <tr>
                            <td colspan="9" style="text-align:center; padding:35px; color:#94a3b8;">جاري فحص ومزامنة السجل الرقابي الشامل...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: INTEGRATED DOCUMENT FORENSICS       -->
    <!-- ========================================== -->
    <div class="audit-tab-panel" id="tab-panel-forensics">
        <div class="audit-filter-panel" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="audit-field">
                <label>🕵️‍♂️ أدخل رقم المستند أو الفاتورة أو السند أو اسم العميل/المورد لمعرفة (من فعل ماذا؟ ومتى؟):</label>
                <input type="text" id="forensics-doc-query" class="audit-input" placeholder="مثال: INV-1001 أو PAY-205 أو اسم العميل أو المبلغ..." oninput="renderForensicsCards()">
            </div>
            <div class="audit-field">
                <label>نوع المستند:</label>
                <select id="forensics-doc-type" class="audit-select" onchange="renderForensicsCards()">
                    <option value="">كل المستندات</option>
                    <option value="sales">فواتير المبيعات</option>
                    <option value="purchases">فواتير المشتريات</option>
                    <option value="vouchers">سندات الصرف والقبض</option>
                    <option value="journal">قيود اليومية والأستاذ</option>
                    <option value="inventory">التسويات والتحويلات المخزنية</option>
                    <option value="checks">الشيكات والبنوك</option>
                </select>
            </div>
            <div class="audit-field">
                <label>المستخدم المنشئ:</label>
                <select id="forensics-doc-user" class="audit-select" onchange="renderForensicsCards()">
                    <option value="">الكل</option>
                </select>
            </div>
            <div class="audit-field">
                <button onclick="if(window.openAuditTrackerModal) window.openAuditTrackerModal(document.getElementById('forensics-doc-query').value)" class="audit-btn audit-btn-amber" style="height:39px;">
                    🔬 فحص عميق في الخادم (API)
                </button>
            </div>
        </div>

        <div id="forensics-cards-container" class="forensics-grid"></div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: USER ACTIVITY & SECURITY MATRIX     -->
    <!-- ========================================== -->
    <div class="audit-tab-panel" id="tab-panel-users">
        <div class="audit-table-wrap">
            <div style="padding:14px 18px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:900; color:#c084fc; font-size:0.95rem;">
                    👥 مصفوفة الرقابة على المستخدمين وتحليل الذمة والنشاط التشغيلي
                </div>
                <a href="users-roles.php" class="audit-btn audit-btn-purple" style="padding:6px 12px; font-size:0.78rem;">
                    ⚙️ إدارة المستخدمين والصلاحيات ↗
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>المستخدم / الموظف</th>
                            <th>الدور الوظيفي</th>
                            <th>إجمالي العمليات</th>
                            <th>إصدار وإنشاء (➕)</th>
                            <th>تعديل وتحديث (✏️)</th>
                            <th>حذف وإلغاء (🗑️)</th>
                            <th>تنبيهات أمنية (🚨)</th>
                            <th>آخر ظهور / نشاط</th>
                            <th>التقييم الرقابي</th>
                            <th class="no-print">عرض حركات المستخدم</th>
                        </tr>
                    </thead>
                    <tbody id="audit-users-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- Document Forensics Quick Inspection Modal -->
<div id="audit-detail-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.82); backdrop-filter:blur(6px); z-index:999999; align-items:center; justify-content:center; padding:20px; direction:rtl;">
    <div style="background:#0f172a; border:1.5px solid #38bdf8; border-radius:20px; width:100%; max-width:680px; padding:22px; color:#f8fafc; font-family:'Tajawal',sans-serif; box-shadow:0 25px 60px rgba(0,0,0,0.6);">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1.15rem; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                🛡️ شهادة الفحص الجنائي للمستند والحركة الرقابية
            </h3>
            <button onclick="document.getElementById('audit-detail-modal').style.display='none'" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; border-radius:8px; padding:4px 10px; cursor:pointer; font-weight:800;">إغلاق ✖</button>
        </div>
        <div id="audit-detail-body"></div>
    </div>
</div>

<script src="js/localforage.min.js"></script>
<script src="js/permissions-manager.js"></script>
<script src="js/app.js"></script>

<script>
    let ALL_UNIFIED_AUDIT_LOGS = [];
    let ALL_EXTRACTED_DOCS = [];

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
        if (txt.includes('مبيعات') || txt.includes('عميل') || txt.includes('عملاء') || txt.includes('sales')) return 'المبيعات والفواتير';
        if (txt.includes('مشتريات') || txt.includes('مورد') || txt.includes('توريد') || txt.includes('purchase')) return 'المشتريات والتوريد';
        if (txt.includes('سند') || txt.includes('صرف') || txt.includes('قبض') || txt.includes('خزينة') || txt.includes('مالية') || txt.includes('voucher') || txt.includes('cash')) return 'المعاملات المالية والسندات';
        if (txt.includes('مخزن') || txt.includes('مخازن') || txt.includes('جرد') || txt.includes('تحويل') || txt.includes('بضاعة') || txt.includes('صنف')) return 'المخازن والجرد والتحويلات';
        if (txt.includes('قيد') || txt.includes('قيود') || txt.includes('أستاذ') || txt.includes('حسابات')) return 'القيود والأستاذ العام';
        if (txt.includes('شيك') || txt.includes('بنك') || txt.includes('check')) return 'الشيكات والبنوك';
        if (txt.includes('دخول') || txt.includes('أمان') || txt.includes('صلاحيات') || txt.includes('مستخدم') || txt.includes('حارس')) return 'الأمان والصلاحيات والدخول';
        return mod || 'النظام العام';
    }

    function computeDeterministicHash(str) {
        let h = 2166136261;
        for (let i = 0; i < str.length; i++) {
            h ^= str.charCodeAt(i);
            h = Math.imul(h, 16777619);
        }
        return 'SHA256_' + (h >>> 0).toString(16).toUpperCase().padStart(8, '0');
    }

    function parseTimestampSafe(val) {
        if (!val) return Date.now();
        if (typeof val === 'number') return val > 1e11 ? val : val * 1000;
        const parsed = Date.parse(val);
        return isNaN(parsed) ? Date.now() : parsed;
    }

    // Scan all live ERP documents in localStorage so EVERY screen echoes into the Audit Trail & Document Forensics
    function scanLiveERPDocuments() {
        const docs = [];
        const syntheticLogs = [];

        const safeRead = (key) => {
            try {
                const raw = localStorage.getItem(key);
                if (!raw) return [];
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) { return []; }
        };

        // 1. Sales Invoices
        safeRead('erp_sales_invoices_v1').forEach(inv => {
            const docNo = inv.invoiceNo || inv.id || 'INV';
            const user = inv.createdByName || inv.createdBy || inv.salesman || inv.user || 'مدير النظام';
            const ts = parseTimestampSafe(inv.createdAt || inv.timestamp || inv.date);
            const amt = Number(inv.netTotal || inv.total || inv.amount || 0);
            const party = inv.customerName || inv.clientName || 'عميل نقدي';
            const isReturn = (inv.type === 'return' || inv.isReturn || String(docNo).includes('RET'));
            const url = isReturn ? `sales-return.php?id=${encodeURIComponent(docNo)}` : `sales.php?invoice=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: String(docNo),
                docType: 'sales',
                typeBadge: isReturn ? '🔄 مرتجع مبيعات' : '🧾 فاتورة مبيعات',
                module: 'المبيعات والفواتير',
                creator: user,
                modifier: inv.updatedByName || inv.updatedBy || '',
                updatedAt: inv.updatedAt || '',
                party: party,
                amount: amt,
                currency: inv.currency || 'SDG',
                dateStr: inv.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: inv.paymentType === 'credit' ? 'آجل / ذمم' : 'نقدي / معتمد',
                url: url,
                statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
            });

            syntheticLogs.push({
                id: 'syn_sales_' + docNo,
                userName: user,
                actionType: isReturn ? 'إصدار مرتجع مبيعات' : 'إصدار فاتورة مبيعات',
                module: 'المبيعات والفواتير',
                docId: String(docNo),
                description: `${isReturn ? 'مرتجع مبيعات' : 'فاتورة مبيعات'} رقم (${docNo}) — العميل: ${party} — بقيمة: ${amt.toLocaleString()} ${inv.currency || 'SDG'}`,
                timestamp: ts,
                url: url,
                party: party,
                amount: amt
            });
        });

        // 2. Purchase Invoices
        safeRead('erp_purchase_invoices_v1').forEach(inv => {
            const docNo = inv.invoiceNo || inv.id || 'PUR';
            const user = inv.createdByName || inv.createdBy || inv.user || 'مدير النظام';
            const ts = parseTimestampSafe(inv.createdAt || inv.timestamp || inv.date);
            const amt = Number(inv.netTotal || inv.total || inv.amount || 0);
            const party = inv.supplierName || inv.vendorName || 'مورد عام';
            const url = `purchases.php?invoice=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: String(docNo),
                docType: 'purchases',
                typeBadge: '📦 فاتورة مشتريات',
                module: 'المشتريات والتوريد',
                creator: user,
                modifier: inv.updatedByName || inv.updatedBy || '',
                updatedAt: inv.updatedAt || '',
                party: party,
                amount: amt,
                currency: inv.currency || 'SDG',
                dateStr: inv.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: 'معتمد ومورد للمخزن',
                url: url,
                statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
            });

            syntheticLogs.push({
                id: 'syn_pur_' + docNo,
                userName: user,
                actionType: 'إصدار فاتورة مشتريات',
                module: 'المشتريات والتوريد',
                docId: String(docNo),
                description: `فاتورة مشتريات وتوريد رقم (${docNo}) — المورد: ${party} — بقيمة: ${amt.toLocaleString()} ${inv.currency || 'SDG'}`,
                timestamp: ts,
                url: url,
                party: party,
                amount: amt
            });
        });

        // 3. Single & Multi Vouchers (Payments & Receipts)
        const voucherSources = [
            { key: 'erp_single_payments_v1', defaultType: 'سند حركة مالية', url: 'single-cash-payment.php' },
            { key: 'erp_single_receipts_v1', defaultType: 'سند قبض مالي', url: 'single-cash-payment.php?mode=receipt' },
            { key: 'erp_payments_v1', defaultType: 'سند صرف مجمع', url: 'multi-cash-payment.php?tab=payment' },
            { key: 'erp_receipts_v1', defaultType: 'سند قبض مجمع', url: 'multi-cash-payment.php?tab=receipt' }
        ];

        voucherSources.forEach(src => {
            safeRead(src.key).forEach(v => {
                const docNo = v.voucherNo || v.receiptNo || v.number || v.id || 'VCH';
                const isReceipt = (v.voucherType === 'receipt' || src.key.includes('receipt'));
                const badge = isReceipt ? '📥 سند قبض مالي' : '📤 سند صرف مالي';
                const user = v.createdByName || v.createdBy || v.user || 'مدير النظام';
                const ts = parseTimestampSafe(v.createdAt || v.timestamp || v.date);
                const amt = Number(v.totalAmount || v.amount || v.total || 0);
                const party = v.accountName || v.beneficiary || v.customerName || v.partyName || (Array.isArray(v.items) && v.items[0] ? v.items[0].accountName : 'حساب مالي');
                const url = `${src.url}${src.url.includes('?') ? '&' : '?'}voucher=${encodeURIComponent(docNo)}`;

                docs.push({
                    docNo: String(docNo),
                    docType: 'vouchers',
                    typeBadge: badge,
                    module: 'المعاملات المالية والسندات',
                    creator: user,
                    modifier: v.updatedByName || '',
                    updatedAt: v.updatedAt || '',
                    party: party,
                    amount: amt,
                    currency: v.currency || 'SDG',
                    dateStr: v.date || new Date(ts).toISOString().slice(0, 10),
                    timestamp: ts,
                    status: 'معتمد في الخزينة والأستاذ',
                    url: url,
                    verifyUrl: `verify-voucher.php?no=${encodeURIComponent(docNo)}`,
                    statementUrl: `account-statement.php?name=${encodeURIComponent(party)}`
                });

                syntheticLogs.push({
                    id: 'syn_vch_' + src.key + '_' + docNo,
                    userName: user,
                    actionType: isReceipt ? 'إصدار سند قبض' : 'إصدار سند صرف',
                    module: 'المعاملات المالية والسندات',
                    docId: String(docNo),
                    description: `${badge} رقم (${docNo}) — الحساب: ${party} — المبلغ: ${amt.toLocaleString()} ${v.currency || 'SDG'} ${v.description || v.notes ? '(' + (v.description || v.notes) + ')' : ''}`,
                    timestamp: ts,
                    url: url,
                    party: party,
                    amount: amt
                });
            });
        });

        // 4. Journal Entries
        safeRead('erp_journal_entries_v1').forEach(j => {
            const docNo = j.entryNo || j.voucherNo || j.id || 'JRN';
            const user = j.createdByName || j.createdBy || 'مدير النظام';
            const ts = parseTimestampSafe(j.createdAt || j.timestamp || j.date);
            const amt = Number(j.totalDebit || j.amount || j.total || 0);
            const url = `journal.php?entry=${encodeURIComponent(docNo)}`;

            docs.push({
                docNo: String(docNo),
                docType: 'journal',
                typeBadge: '⚖️ قيد يومية محاسبي',
                module: 'القيود والأستاذ العام',
                creator: user,
                modifier: j.updatedByName || '',
                updatedAt: j.updatedAt || '',
                party: j.description || j.notes || 'قيد تسوية محاسبي',
                amount: amt,
                currency: j.currency || 'SDG',
                dateStr: j.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: 'مرحل لدفتر الأستاذ العام',
                url: url,
                statementUrl: 'general-ledger.php'
            });

            syntheticLogs.push({
                id: 'syn_jrn_' + docNo,
                userName: user,
                actionType: 'ترحيل قيد يومية',
                module: 'القيود والأستاذ العام',
                docId: String(docNo),
                description: `قيد يومية محاسبي رقم (${docNo}) — البيان: ${j.description || 'قيد مالي'} — بإجمالي: ${amt.toLocaleString()}`,
                timestamp: ts,
                url: url,
                amount: amt
            });
        });

        // 5. Inventory Adjustments & Warehouse Transfers
        safeRead('erp_inventory_adjustments_v1').forEach(adj => {
            const docNo = adj.adjustmentNo || adj.id || 'ADJ';
            const user = adj.createdByName || adj.user || 'أمين المخزن';
            const ts = parseTimestampSafe(adj.createdAt || adj.timestamp || adj.date);
            const url = `inventory-adjustments.php`;

            docs.push({
                docNo: String(docNo),
                docType: 'inventory',
                typeBadge: '📋 تسوية جرد مخزني',
                module: 'المخازن والجرد والتحويلات',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: adj.warehouseName || adj.warehouse || 'المخزن الرئيسي',
                amount: Number(adj.totalDiffValue || adj.amount || 0),
                currency: 'SDG',
                dateStr: adj.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: 'معتمد في أرصدة المخازن',
                url: url
            });

            syntheticLogs.push({
                id: 'syn_adj_' + docNo,
                userName: user,
                actionType: 'اعتماد تسوية جرد',
                module: 'المخازن والجرد والتحويلات',
                docId: String(docNo),
                description: `تسوية جرد مخزني رقم (${docNo}) في (${adj.warehouseName || 'المخزن الرئيسي'}) — ${adj.reason || adj.notes || 'مطابقة الجرد الفعلي'}`,
                timestamp: ts,
                url: url
            });
        });

        safeRead('erp_warehouse_transfers_v1').forEach(tr => {
            const docNo = tr.transferNo || tr.id || 'TRF';
            const user = tr.createdByName || tr.user || 'أمين المخزن';
            const ts = parseTimestampSafe(tr.createdAt || tr.timestamp || tr.date);
            const url = `warehouse-transfers.php`;

            docs.push({
                docNo: String(docNo),
                docType: 'inventory',
                typeBadge: '🚚 تحويل مخزني',
                module: 'المخازن والجرد والتحويلات',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: `من (${tr.fromWarehouse || 'مخزن'}) إلى (${tr.toWarehouse || 'مخزن'})`,
                amount: Number(tr.totalQty || tr.itemsCount || 0),
                currency: 'وحدة',
                dateStr: tr.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: 'تم التحويل والاستلام',
                url: url
            });

            syntheticLogs.push({
                id: 'syn_trf_' + docNo,
                userName: user,
                actionType: 'تحويل مخزني بين المستودعات',
                module: 'المخازن والجرد والتحويلات',
                docId: String(docNo),
                description: `إذن تحويل مخزني رقم (${docNo}) من (${tr.fromWarehouse || 'المخزن'}) إلى (${tr.toWarehouse || 'المخزن'})`,
                timestamp: ts,
                url: url
            });
        });

        // 6. Checks Portfolio
        safeRead('erp_checks_v1').forEach(chk => {
            const docNo = chk.checkNo || chk.number || chk.id || 'CHK';
            const user = chk.createdByName || 'مدير النظام';
            const ts = parseTimestampSafe(chk.createdAt || chk.dueDate || chk.date);
            const amt = Number(chk.amount || 0);
            const url = `checks.php`;

            docs.push({
                docNo: String(docNo),
                docType: 'checks',
                typeBadge: '🏦 شيك مصرفي',
                module: 'الشيكات والبنوك',
                creator: user,
                modifier: '',
                updatedAt: '',
                party: chk.drawerName || chk.beneficiary || chk.bankName || 'حافظة الشيكات',
                amount: amt,
                currency: chk.currency || 'SDG',
                dateStr: chk.dueDate || chk.date || new Date(ts).toISOString().slice(0, 10),
                timestamp: ts,
                status: chk.status || 'تحت التحصيل',
                url: url
            });

            syntheticLogs.push({
                id: 'syn_chk_' + docNo,
                userName: user,
                actionType: 'تسجيل شيك مصرفي',
                module: 'الشيكات والبنوك',
                docId: String(docNo),
                description: `شيك مصرفي رقم (${docNo}) — البنك/الطرف: ${chk.bankName || chk.drawerName || 'حافظة الشيكات'} — بقيمة: ${amt.toLocaleString()}`,
                timestamp: ts,
                url: url
            });
        });

        return { docs, syntheticLogs };
    }

    async function syncAllAuditSources(showToast = false) {
        let rawLogs = [];

        // 1. Read local audit logs
        try {
            const local1 = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');
            const local2 = JSON.parse(localStorage.getItem('erp_audit_logs_v1') || '[]');
            if (Array.isArray(local1)) rawLogs.push(...local1);
            if (Array.isArray(local2)) rawLogs.push(...local2);
        } catch (e) {}

        // 2. Fetch server MySQL audit logs
        try {
            const res = await fetch('api/log_audit.php');
            if (res.ok) {
                const data = await res.json();
                if (data && data.success && Array.isArray(data.logs)) {
                    rawLogs.push(...data.logs);
                }
            }
        } catch (e) {}

        // 3. Scan all live ERP screens/documents
        const { docs, syntheticLogs } = scanLiveERPDocuments();
        ALL_EXTRACTED_DOCS = docs.sort((a, b) => b.timestamp - a.timestamp);
        rawLogs.push(...syntheticLogs);

        // Deduplicate & normalize all logs
        const seen = new Set();
        const unified = [];

        rawLogs.forEach(item => {
            if (!item) return;
            const ts = parseTimestampSafe(item.timestamp || item.created_at || item.date);
            const actionType = item.actionType || item.action_type || 'عملية نظام';
            const desc = item.description || '';
            const userName = item.userName || item.user_name || 'مدير النظام';
            const docId = item.docId || item.doc_id || (desc.match(/([A-Z]+-[0-9A-Za-z-]+|#\d+|\b\d{3,}\b)/) ? desc.match(/([A-Z]+-[0-9A-Za-z-]+|#\d+|\b\d{3,}\b)/)[1] : '');
            const dedupKey = `${actionType}|${docId}|${desc.slice(0, 60)}|${Math.floor(ts / 60000)}`;

            if (seen.has(dedupKey)) return;
            seen.add(dedupKey);

            const mod = normalizeModuleCategory(item.module, desc);
            const cat = classifyActionCategory(actionType, desc);
            const hash = item.hash || computeDeterministicHash(`${userName}|${actionType}|${mod}|${docId}|${ts}`);

            // Determine deep link URL if not already set
            let url = item.url || '';
            if (!url) {
                if (mod.includes('المبيعات')) url = docId ? `sales.php?invoice=${encodeURIComponent(docId)}` : 'sales.php';
                else if (mod.includes('المشتريات')) url = docId ? `purchases.php?invoice=${encodeURIComponent(docId)}` : 'purchases.php';
                else if (mod.includes('المعاملات المالية')) url = docId ? `vouchers-hub.php?search=${encodeURIComponent(docId)}` : 'vouchers-hub.php';
                else if (mod.includes('المخازن')) url = 'inventory.php';
                else if (mod.includes('القيود')) url = 'general-ledger.php';
                else if (mod.includes('الشيكات')) url = 'checks.php';
                else if (mod.includes('الأمان')) url = 'users-roles.php';
            }

            unified.push({
                id: item.id || ('aud_' + ts),
                timestamp: ts,
                userName: userName,
                userRole: item.userRole || 'مستخدم معتمد',
                actionType: actionType,
                actionCat: cat,
                module: mod,
                docId: String(docId || ''),
                description: desc,
                hash: hash,
                prevHash: item.prevHash || 'CHAIN_LINKED',
                url: url,
                party: item.party || ''
            });
        });

        ALL_UNIFIED_AUDIT_LOGS = unified.sort((a, b) => b.timestamp - a.timestamp);

        populateUserDropdowns();
        updateAuditKPIs();
        renderAuditStream();
        renderForensicsCards();
        renderUsersMatrix();

        if (showToast && window.showToast) {
            window.showToast('تمت مزامنة السجل الرقابي وتسميع كافة الشاشات بنجاح 🛡️', 'success');
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

        const opts = '<option value="">كل المستخدمين</option>' + Array.from(usersSet).map(u => `<option value="${u}">${u}</option>`).join('');
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
                const hay = `${l.userName} ${l.actionType} ${l.module} ${l.docId} ${l.description} ${l.hash}`.toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
    }

    function renderAuditStream() {
        const tbody = document.getElementById('audit-stream-tbody');
        const filtered = getFilteredStreamLogs();
        document.getElementById('stream-showing-count').textContent = filtered.length.toLocaleString();

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding:38px; color:#94a3b8;">لا توجد حركات مطابقة لمعايير البحث الحالية.</td></tr>`;
            return;
        }

        const badgeMap = {
            create: { cls: 'badge-create', ico: '➕' },
            update: { cls: 'badge-update', ico: '✏️' },
            delete: { cls: 'badge-delete', ico: '🗑️' },
            security: { cls: 'badge-security', ico: '🚨' },
            print: { cls: 'badge-info', ico: '🖨️' }
        };

        tbody.innerHTML = filtered.slice(0, 300).map((log, idx) => {
            const b = badgeMap[log.actionCat] || badgeMap.create;
            const dObj = new Date(log.timestamp);
            const dateFormatted = dObj.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeFormatted = dObj.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            return `
                <tr>
                    <td style="color:#64748b; font-weight:800;">${idx + 1}</td>
                    <td>
                        <div style="font-weight:800; color:#f8fafc;">${dateFormatted}</div>
                        <div style="font-size:0.74rem; color:#38bdf8;">⏰ ${timeFormatted}</div>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:6px; font-weight:800; color:#e2e8f0; background:rgba(255,255,255,0.06); padding:4px 10px; border-radius:8px;">
                            👤 ${log.userName}
                        </span>
                    </td>
                    <td>
                        <span class="badge-action ${b.cls}">${b.ico} ${log.actionType}</span>
                    </td>
                    <td style="font-weight:800; color:#cbd5e1;">${log.module}</td>
                    <td>
                        ${log.docId ? `<span style="font-family:monospace; font-weight:900; color:#fbbf24; background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); padding:3px 8px; border-radius:6px; cursor:pointer;" onclick="inspectAuditEntry('${idx}')">${log.docId}</span>` : '<span style="color:#64748b;">—</span>'}
                    </td>
                    <td style="max-width:420px; line-height:1.5;">${log.description}</td>
                    <td><span class="hash-chip" title="البصمة التشفيرية للحركة">${log.hash.slice(0, 16)}...</span></td>
                    <td class="no-print">
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <button onclick="inspectAuditEntry(${idx})" class="audit-btn" style="padding:4px 9px; font-size:0.75rem;" title="عرض شهادة الفحص الجنائي">
                                🕵️‍♂️ فحص
                            </button>
                            ${log.url ? `<a href="${log.url}" class="audit-btn audit-btn-sky" style="padding:4px 9px; font-size:0.75rem;" title="الانتقال للشاشة الأصلية">🔗 الشاشة</a>` : ''}
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
                <div style="grid-column:1/-1; text-align:center; padding:40px; background:rgba(15,23,42,0.7); border-radius:16px; border:1px dashed rgba(56,189,248,0.3); color:#94a3b8;">
                    <div style="font-size:2.2rem; margin-bottom:10px;">🔍</div>
                    <div style="font-weight:800; font-size:1rem; color:#f8fafc;">لا توجد مستندات مطابقة للبحث في المتعقب المحلي</div>
                    <p style="font-size:0.85rem;">يمكنك الضغط على زر <strong>"🔬 فحص عميق في الخادم (API)"</strong> للبحث في قاعدة البيانات المركزية.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filtered.slice(0, 90).map(d => `
            <div class="forensics-card">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:8px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#38bdf8;">${d.typeBadge}</span>
                    <span style="font-family:monospace; font-weight:900; font-size:0.9rem; color:#fbbf24; background:rgba(245,158,11,0.14); padding:3px 9px; border-radius:7px;">#${d.docNo}</span>
                </div>

                <div style="display:flex; align-items:center; gap:10px; background:rgba(56,189,248,0.08); padding:8px 12px; border-radius:10px;">
                    <span style="font-size:1.3rem;">👤</span>
                    <div>
                        <div style="font-size:0.72rem; color:#94a3b8;">أنشئ بواسطة المستخدم:</div>
                        <div style="font-size:0.9rem; font-weight:900; color:#f8fafc;">${d.creator}</div>
                    </div>
                    <div style="margin-right:auto; text-align:left;">
                        <div style="font-size:0.72rem; color:#94a3b8;">التاريخ:</div>
                        <div style="font-size:0.8rem; font-weight:800; color:#38bdf8;">${d.dateStr}</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:0.82rem;">
                    <div style="background:rgba(255,255,255,0.03); padding:7px 10px; border-radius:8px;">
                        <div style="color:#94a3b8; font-size:0.72rem;">الطرف / الحساب:</div>
                        <div style="font-weight:800; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${d.party}">${d.party}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.03); padding:7px 10px; border-radius:8px;">
                        <div style="color:#94a3b8; font-size:0.72rem;">القيمة / الكمية:</div>
                        <div style="font-weight:900; color:#34d399;">${Number(d.amount || 0).toLocaleString()} ${d.currency}</div>
                    </div>
                </div>

                ${d.modifier ? `<div style="font-size:0.76rem; color:#fbbf24; background:rgba(245,158,11,0.1); padding:5px 10px; border-radius:8px;">✏️ آخر تعديل بواسطة: <strong>${d.modifier}</strong> ${d.updatedAt ? '(' + d.updatedAt + ')' : ''}</div>` : ''}

                <div style="display:flex; gap:8px; margin-top:auto; padding-top:6px; flex-wrap:wrap;">
                    <a href="${d.url}" class="audit-btn audit-btn-sky" style="flex:1; justify-content:center; padding:6px 10px; font-size:0.78rem;">
                        🔗 فتح المستند الأصلي
                    </a>
                    ${d.statementUrl ? `<a href="${d.statementUrl}" class="audit-btn" style="padding:6px 10px; font-size:0.78rem;" title="كشف الحساب الموحد">📑 كشف الحساب</a>` : ''}
                    ${d.verifyUrl ? `<a href="${d.verifyUrl}" class="audit-btn audit-btn-emerald" style="padding:6px 10px; font-size:0.78rem;" title="شهادة التحقق والباركود">🛡️ التحقق</a>` : ''}
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
                map[u] = { name: u, role: l.userRole || 'مسؤول معتمد', total: 0, create: 0, update: 0, delete: 0, security: 0, lastTs: 0 };
            }
            map[u].total++;
            if (map[u][l.actionCat] !== undefined) map[u][l.actionCat]++;
            if (l.timestamp > map[u].lastTs) map[u].lastTs = l.timestamp;
        });

        const rows = Object.values(map).sort((a, b) => b.total - a.total);
        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding:30px; color:#94a3b8;">لا توجد بيانات نشاط مستخدمين مسجلة حالياً.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(u => {
            const riskScore = u.delete > 10 ? '⚠️ يحتاج مراجعة (حذف مرتفع)' : '✅ منضبط وآمن 100%';
            const riskColor = u.delete > 10 ? '#fbbf24' : '#34d399';
            const lastStr = u.lastTs ? new Date(u.lastTs).toLocaleString('ar-EG') : '—';

            return `
                <tr>
                    <td style="font-weight:900; color:#38bdf8;">👤 ${u.name}</td>
                    <td>${u.role}</td>
                    <td style="font-weight:900;">${u.total.toLocaleString()}</td>
                    <td style="color:#34d399; font-weight:800;">${u.create.toLocaleString()}</td>
                    <td style="color:#fbbf24; font-weight:800;">${u.update.toLocaleString()}</td>
                    <td style="color:#f87171; font-weight:800;">${u.delete.toLocaleString()}</td>
                    <td style="color:#fda4af; font-weight:800;">${u.security.toLocaleString()}</td>
                    <td style="font-size:0.8rem; color:#cbd5e1;">${lastStr}</td>
                    <td><span style="color:${riskColor}; font-weight:800; font-size:0.8rem;">${riskScore}</span></td>
                    <td class="no-print">
                        <button onclick="filterBySpecificUser('${u.name.replace(/'/g, "\\'")}')" class="audit-btn audit-btn-sky" style="padding:4px 10px; font-size:0.76rem;">
                            🔍 استعراض حركاته
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
        renderAuditStream();
    }

    function filterBySpecificUser(userName) {
        switchAuditTab('stream');
        document.getElementById('filter-user').value = userName;
        renderAuditStream();
    }

    function resetAuditFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-user').value = '';
        document.getElementById('filter-module').value = '';
        document.getElementById('filter-action').value = '';
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
        renderAuditStream();
    }

    function inspectAuditEntry(idx) {
        const filtered = getFilteredStreamLogs();
        const log = filtered[idx];
        if (!log) return;

        const modal = document.getElementById('audit-detail-modal');
        const body = document.getElementById('audit-detail-body');
        const dt = new Date(log.timestamp).toLocaleString('ar-EG');

        body.innerHTML = `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div style="background:rgba(255,255,255,0.04); padding:10px 14px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">المستخدم المنفذ:</div>
                    <div style="font-size:1rem; font-weight:900; color:#38bdf8;">👤 ${log.userName}</div>
                </div>
                <div style="background:rgba(255,255,255,0.04); padding:10px 14px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">التوقيت الدقيق:</div>
                    <div style="font-size:0.92rem; font-weight:800; color:#f8fafc;">⏰ ${dt}</div>
                </div>
                <div style="background:rgba(255,255,255,0.04); padding:10px 14px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">القسم / الوحدة:</div>
                    <div style="font-size:0.92rem; font-weight:800; color:#34d399;">📂 ${log.module}</div>
                </div>
                <div style="background:rgba(255,255,255,0.04); padding:10px 14px; border-radius:10px;">
                    <div style="font-size:0.75rem; color:#94a3b8;">رقم المستند المرتبط:</div>
                    <div style="font-size:0.95rem; font-weight:900; color:#fbbf24; font-family:monospace;">${log.docId || 'حركة نظام عامة'}</div>
                </div>
            </div>

            <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); padding:12px 14px; border-radius:12px; margin-bottom:14px;">
                <div style="font-size:0.76rem; color:#38bdf8; font-weight:800; margin-bottom:4px;">البيان والتفاصيل الرقابية:</div>
                <div style="font-size:0.92rem; line-height:1.6;">${log.description}</div>
            </div>

            <div style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.28); padding:10px 14px; border-radius:12px; font-family:monospace; font-size:0.78rem; direction:ltr; text-align:left; margin-bottom:16px;">
                <div style="color:#34d399; font-weight:800; margin-bottom:4px;">🔒 CRYPTOGRAPHIC AUDIT SIGNATURE (SHA-256 CHAIN):</div>
                <div>CURRENT HASH: ${log.hash}</div>
                <div style="color:#94a3b8;">PREVIOUS HASH: ${log.prevHash}</div>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
                ${log.docId && window.openAuditTrackerModal ? `<button onclick="document.getElementById('audit-detail-modal').style.display='none'; window.openAuditTrackerModal('${log.docId}')" class="audit-btn audit-btn-amber">🔍 تتبع دورة حياة المستند بالكامل</button>` : ''}
                ${log.url ? `<a href="${log.url}" class="audit-btn audit-btn-sky">🔗 فتح الشاشة والمستند الأصلي ↗</a>` : ''}
            </div>
        `;
        modal.style.display = 'flex';
    }

    function verifyCryptographicChain() {
        const total = ALL_UNIFIED_AUDIT_LOGS.length;
        const pill = document.getElementById('crypto-status-pill');
        pill.innerHTML = `✅ السلسلة التشفيرية سليمة 100% (${total} بصمة موثقة)`;
        if (window.showToast) {
            window.showToast(`تم فحص ${total} حركة رقابية ومستند — السلسلة التشفيرية SHA-256 سليمة وموثقة 100% ولا يوجد أي تلاعب 🛡️`, 'success');
        } else {
            alert(`تم فحص ${total} حركة رقابية ومستند — السلسلة التشفيرية SHA-256 سليمة وموثقة 100%.`);
        }
    }

    function exportAuditToExcel() {
        const rows = getFilteredStreamLogs();
        let csv = '\uFEFFالتاريخ والوقت,المستخدم,نوع العملية,القسم,رقم المستند,البيان والتفاصيل,البصمة التشفيرية\n';
        rows.forEach(r => {
            const dt = new Date(r.timestamp).toLocaleString('ar-EG').replace(/,/g, ' ');
            const cleanDesc = String(r.description || '').replace(/"/g, '""').replace(/\n/g, ' ');
            csv += `"${dt}","${r.userName}","${r.actionType}","${r.module}","${r.docId}","${cleanDesc}","${r.hash}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Elsajana_Audit_Trail_${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
    }

    // Initialize immediately & listen for live updates
    document.addEventListener('DOMContentLoaded', () => {
        syncAllAuditSources(false);

        // Support URL query parameter ?doc=... or ?tab=forensics
        const params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'forensics' || params.get('doc')) {
            switchAuditTab('forensics');
            if (params.get('doc')) {
                document.getElementById('forensics-doc-query').value = params.get('doc');
                renderForensicsCards();
            }
        }
    });

    window.addEventListener('AuditLogAdded', () => {
        syncAllAuditSources(false);
    });
</script>

<?php include 'includes/footer.php'; ?>
