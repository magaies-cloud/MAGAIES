<?php
require_once __DIR__ . '/includes/auth_guard.php';
$page_title = 'مركز إدارة المهام والأعمال التنفيذية وأوامر الشغل 360°';
$header_theme_class = 'header-theme-dashboard';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز إدارة المهام والأعمال التنفيذية 360° | Magaiees ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14">
    <link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        :root {
            --bg-dark: #070b14;
            --card-bg: rgba(15, 23, 42, 0.85);
            --border-glass: rgba(148, 163, 184, 0.16);
        }
        body {
            background: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .jobs-wrapper {
            max-width: 1650px;
            margin: 0 auto;
            padding: 14px 20px 60px;
        }
        .glass-box {
            background: var(--card-bg);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 16px 20px;
            backdrop-filter: blur(16px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
            margin-bottom: 16px;
        }
        .kpi-grid-6 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .kpi-exec-card {
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.78));
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 14px;
            padding: 14px 16px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
            cursor: pointer;
        }
        .kpi-exec-card:hover {
            transform: translateY(-3px);
            border-color: #38bdf8;
        }
        .kpi-exec-label {
            font-size: 0.8rem;
            font-weight: 800;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .kpi-exec-val {
            font-size: 1.45rem;
            font-weight: 900;
            font-family: 'Outfit', 'Tajawal', sans-serif;
            margin: 6px 0 2px;
        }
        .kpi-exec-sub {
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
        }
        .btn-exec {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 15px;
            border-radius: 10px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 800;
            font-size: 0.84rem;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.14);
            transition: all 0.2s;
            color: #fff;
            text-decoration: none;
        }
        .btn-exec:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .btn-exec-primary { background: linear-gradient(135deg, #0284c7, #2563eb); border-color: #38bdf8; }
        .btn-exec-ai { background: linear-gradient(135deg, #7c3aed, #c026d3); border-color: #e879f9; box-shadow: 0 0 18px rgba(192, 38, 211, 0.35); }
        .btn-exec-emerald { background: linear-gradient(135deg, #059669, #10b981); border-color: #34d399; }
        .btn-exec-amber { background: linear-gradient(135deg, #d97706, #f59e0b); border-color: #fbbf24; }
        .btn-exec-dark { background: rgba(30, 41, 59, 0.9); border-color: rgba(148, 163, 184, 0.3); color: #e2e8f0; }

        .sop-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #38bdf8;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sop-chip:hover {
            background: rgba(56, 189, 248, 0.22);
            transform: translateY(-1px);
        }

        .kanban-4-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            align-items: start;
        }
        @media (max-width: 1200px) {
            .kanban-4-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .kanban-4-grid { grid-template-columns: 1fr; }
        }
        .kanban-col {
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 16px;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            transition: all 0.2s;
        }
        .kanban-col.drag-over {
            border-color: #38bdf8;
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.25);
            background: rgba(15, 23, 42, 0.95);
        }
        .col-header {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 900;
            font-size: 0.92rem;
            border-radius: 16px 16px 0 0;
        }
        .col-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
        }
        .job-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 13px;
            padding: 13px 14px;
            position: relative;
            cursor: grab;
            transition: all 0.2s;
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }
        .job-card:hover {
            transform: translateY(-2px);
            border-color: rgba(56, 189, 248, 0.5);
        }
        .job-card.priority-critical { border-right: 4px solid #ef4444; }
        .job-card.priority-high { border-right: 4px solid #f59e0b; }
        .job-card.priority-medium { border-right: 4px solid #38bdf8; }
        .job-card.priority-low { border-right: 4px solid #10b981; }

        .job-title {
            font-size: 0.93rem;
            font-weight: 900;
            color: #fff;
            margin: 0 0 6px 0;
            line-height: 1.4;
        }
        .job-desc {
            font-size: 0.8rem;
            color: #cbd5e1;
            margin: 0 0 10px 0;
            line-height: 1.5;
        }
        .job-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 9px;
        }
        .job-chip {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            background: rgba(255,255,255,0.06);
            color: #cbd5e1;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .job-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 5px;
            padding-top: 8px;
            border-top: 1px dashed rgba(148, 163, 184, 0.18);
            flex-wrap: wrap;
        }
        .mini-act-btn {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            color: #e2e8f0;
            font-family: 'Tajawal', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mini-act-btn:hover { background: rgba(56, 189, 248, 0.25); border-color: #38bdf8; color: #fff; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(4, 8, 18, 0.85);
            backdrop-filter: blur(8px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-card {
            background: #0f172a;
            border: 1.5px solid #38bdf8;
            border-radius: 18px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 22px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.85);
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 800;
            color: #94a3b8;
            margin-bottom: 5px;
        }
        .form-input {
            width: 100%;
            box-sizing: border-box;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 9px;
            padding: 9px 12px;
            color: #fff;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.86rem;
        }
        .exec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
        }
        .exec-table th, .exec-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            text-align: right;
        }
        .exec-table th {
            background: rgba(30, 41, 59, 0.8);
            color: #38bdf8;
            font-weight: 900;
        }
        :root.light-theme body { background: #f1f5f9; color: #0f172a; }
        :root.light-theme .glass-box, :root.light-theme .kanban-col, :root.light-theme .kpi-exec-card, :root.light-theme .job-card, :root.light-theme .modal-card {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        :root.light-theme .job-title { color: #0f172a !important; }
        :root.light-theme .job-desc { color: #334155 !important; }
        :root.light-theme .form-input { background: #f8fafc !important; color: #0f172a !important; border-color: #cbd5e1 !important; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="jobs-wrapper">
        <?php include 'includes/header.php'; ?>

        <!-- Top Executive Command & Live Exchange Rate Bar -->
        <div class="glass-box no-print" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-color:rgba(56,189,248,0.35);">
            <div>
                <h1 style="margin:0; font-size:1.35rem; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px;">
                    <span style="background:rgba(56,189,248,0.18); border:1px solid #38bdf8; color:#38bdf8; width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center;">💼</span>
                    مركز إدارة المهام والأعمال التنفيذية وأوامر الشغل 360°
                </h1>
                <div style="font-size:0.8rem; color:#94a3b8; margin-top:4px; font-weight:700;">
                    مربوط لحظياً مع الفواتير، أعمار الديون، نواقص المخزون، الشيكات، سعر الصرف، وسجل الرقابة الجنائي
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <!-- Live FX Badge & Currency Toggle -->
                <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(15,23,42,0.9); border:1px solid rgba(245,158,11,0.45); padding:6px 12px; border-radius:10px;">
                    <span style="font-size:0.78rem; color:#fbbf24; font-weight:900;">💱 سعر الصرف:</span>
                    <strong id="jobs-fx-rate-badge" style="color:#38bdf8; font-family:'Outfit',sans-serif; font-size:0.9rem;">1 USD = 1 SDG</strong>
                    <button type="button" onclick="toggleJobsCurrencyView()" id="jobs-cur-toggle-btn" class="mini-act-btn" style="background:rgba(56,189,248,0.2); border-color:#38bdf8; color:#fff;">
                        العرض: SDG 🇸🇩
                    </button>
                </div>

                <button type="button" class="btn-exec btn-exec-ai" onclick="generateAISystemTasks()" title="فحص الديون المتأخرة ونواقص المخزون والأذونات المعلقة والشيكات وتوليد مهام عمل فورية">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> توليد المهام الذكي من النظام (AI)
                </button>
                <button type="button" class="btn-exec btn-exec-primary" onclick="openJobModal()">
                    <i class="fa-solid fa-plus"></i> مهمة / أمر شغل جديد
                </button>
                <button type="button" class="btn-exec btn-exec-emerald" onclick="exportJobsCSV()">
                    <i class="fa-solid fa-file-excel"></i> تصدير Excel
                </button>
                <button type="button" class="btn-exec btn-exec-dark" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> طباعة
                </button>
            </div>
        </div>

        <!-- 6 Executive KPI Cards -->
        <div class="kpi-grid-6 no-print">
            <div class="kpi-exec-card" onclick="filterByStatus('all')">
                <div class="kpi-exec-label"><span>📋 إجمالي المهام وأوامر الشغل</span><i class="fa-solid fa-list-check" style="color:#38bdf8;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-total" style="color:#38bdf8;">0</div>
                <div class="kpi-exec-sub">جميع المهام المسجلة والنشطة</div>
            </div>
            <div class="kpi-exec-card" onclick="filterByPriority('critical')">
                <div class="kpi-exec-label"><span>🚨 مهام عاجلة وحرجة</span><i class="fa-solid fa-fire" style="color:#f87171;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-critical" style="color:#f87171;">0</div>
                <div class="kpi-exec-sub">تتطلب تدخلاً تنفيذياً فورياً</div>
            </div>
            <div class="kpi-exec-card" onclick="filterByStatus('doing')">
                <div class="kpi-exec-label"><span>⚡ قيد التنفيذ والمراجعة</span><i class="fa-solid fa-spinner" style="color:#fbbf24;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-doing" style="color:#fbbf24;">0</div>
                <div class="kpi-exec-sub">مهام جاري العمل عليها حالياً</div>
            </div>
            <div class="kpi-exec-card" onclick="filterByOverdue()">
                <div class="kpi-exec-label"><span>⏰ متأخرة عن الموعد (SLA)</span><i class="fa-solid fa-triangle-exclamation" style="color:#fb7185;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-overdue" style="color:#fb7185;">0</div>
                <div class="kpi-exec-sub">تجاوزت تاريخ الاستحقاق المحدد</div>
            </div>
            <div class="kpi-exec-card" onclick="filterByStatus('done')">
                <div class="kpi-exec-label"><span>✅ نسبة الإنجاز التشغيلي</span><i class="fa-solid fa-chart-pie" style="color:#34d399;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-rate" style="color:#34d399;">0%</div>
                <div class="kpi-exec-sub" id="kpi-jobs-done-sub">0 مهمة مكتملة</div>
            </div>
            <div class="kpi-exec-card" onclick="toggleJobsCurrencyView()">
                <div class="kpi-exec-label"><span>💰 الأثر المالي للمهام المرتبطة</span><i class="fa-solid fa-coins" style="color:#c084fc;"></i></div>
                <div class="kpi-exec-val" id="kpi-jobs-amount" style="color:#c084fc;">0 SDG</div>
                <div class="kpi-exec-sub" id="kpi-jobs-amount-sub">تحصيل / توريد / تسويات معلقة</div>
            </div>
        </div>

        <!-- Quick SOP Templates & Smart Filter Bar -->
        <div class="glass-box no-print" style="padding:12px 16px;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid rgba(148,163,184,0.12);">
                <span style="font-size:0.8rem; font-weight:900; color:#fbbf24;"><i class="fa-solid fa-bolt"></i> قوالب مهام تشغيلية ومحاسبية سريعة:</span>
                <span class="sop-chip" onclick="createQuickSOPTask('مطابقة السيولة والخزينة والبنوك اليومية', 'مراجعة أرصدة الصناديق والبنوك ومطابقتها مع كشف الحساب الفعلي', 'finance', 'high', 'vouchers-hub.php')">💵 مطابقة الخزينة والبنوك</span>
                <span class="sop-chip" onclick="createQuickSOPTask('تحديث سعر الصرف وتسوية فروق العملة الأجنبية', 'مراجعة سعر صرف الدولار مقابل العملة الأساسية وإثبات قيد فروق تقييم العملة', 'fx', 'high', 'journal-voucher.php?template=fx_diff')">💱 تحديث سعر الصرف وفروق العملة</span>
                <span class="sop-chip" onclick="createQuickSOPTask('متابعة تحصيل الديون المتأخرة وسقوف الائتمان', 'التواصل مع العملاء المتأخرين في السداد وجدولة التحصيل الفوري', 'collection', 'critical', 'aging-report.php')">📞 حملة تحصيل الديون المتأخرة</span>
                <span class="sop-chip" onclick="createQuickSOPTask('جرد دوري ومطابقة أرصدة المستودعات', 'فحص الأصناف الحرجة ومطابقة الرصيد الفعلي مع الدفتري', 'inventory', 'medium', 'warehouse-inventory.php')">📦 جرد ومطابقة المستودعات</span>
                <span class="sop-chip" onclick="createQuickSOPTask('مراجعة ميزان المراجعة والقيود اليومية', 'التأكد من توازن الحسابات وعدم وجود حسابات معلقة قبل الإقفال', 'audit', 'high', 'trial-balance.php')">⚖️ فحص ميزان المراجعة</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex:1;">
                    <input type="text" id="filter-search" class="form-input" placeholder="🔍 ابحث في عنوان المهمة، الموظف، العميل، المورد..." oninput="renderJobs()" style="max-width:280px;">
                    <select id="filter-category" class="form-input" onchange="renderJobs()" style="max-width:170px;">
                        <option value="all">📂 كل الأقسام</option>
                        <option value="collection">📞 تحصيل ديون</option>
                        <option value="delivery">🚚 تسليم واستلام</option>
                        <option value="inventory">📦 مخازن ونواقص</option>
                        <option value="finance">💵 مالية وخزينة وشيكات</option>
                        <option value="fx">💱 سعر الصرف والعملات</option>
                        <option value="audit">🛡️ مراجعة وتدقيق</option>
                        <option value="admin">🏢 إدارية وعامة</option>
                    </select>
                    <select id="filter-priority" class="form-input" onchange="renderJobs()" style="max-width:160px;">
                        <option value="all">🎯 كل الأولويات</option>
                        <option value="critical">🚨 حرجة وعاجلة جداً</option>
                        <option value="high">🔴 أولوية عالية</option>
                        <option value="medium">🟡 أولوية متوسطة</option>
                        <option value="low">🟢 أولوية عادية</option>
                    </select>
                    <select id="filter-user" class="form-input" onchange="renderJobs()" style="max-width:170px;">
                        <option value="all">👤 كل الموظفين</option>
                    </select>
                </div>

                <div style="display:flex; gap:6px;">
                    <button type="button" id="view-btn-kanban" class="btn-exec btn-exec-primary" onclick="setJobsViewMode('kanban')">
                        <i class="fa-solid fa-table-columns"></i> لوحة كانبان (4 مراحل)
                    </button>
                    <button type="button" id="view-btn-table" class="btn-exec btn-exec-dark" onclick="setJobsViewMode('table')">
                        <i class="fa-solid fa-table-list"></i> السجل الجدولي الشامل
                    </button>
                </div>
            </div>
        </div>

        <!-- 4-Column Kanban Board -->
        <div id="jobs-kanban-view" class="kanban-4-grid">
            <div class="kanban-col" id="col-todo" ondrop="dropJob(event, 'todo')" ondragover="allowJobDrop(event)" ondragleave="dragJobLeave(event)">
                <div class="col-header" style="background:rgba(56,189,248,0.12); color:#38bdf8;">
                    <span>📋 مهام جديدة (To Do)</span>
                    <span class="job-chip" id="count-todo" style="background:rgba(56,189,248,0.25); color:#fff;">0</span>
                </div>
                <div class="col-body" id="list-todo"></div>
            </div>

            <div class="kanban-col" id="col-doing" ondrop="dropJob(event, 'doing')" ondragover="allowJobDrop(event)" ondragleave="dragJobLeave(event)">
                <div class="col-header" style="background:rgba(245,158,11,0.14); color:#fbbf24;">
                    <span>⚡ قيد التنفيذ (In Progress)</span>
                    <span class="job-chip" id="count-doing" style="background:rgba(245,158,11,0.25); color:#fff;">0</span>
                </div>
                <div class="col-body" id="list-doing"></div>
            </div>

            <div class="kanban-col" id="col-review" ondrop="dropJob(event, 'review')" ondragover="allowJobDrop(event)" ondragleave="dragJobLeave(event)">
                <div class="col-header" style="background:rgba(168,85,247,0.14); color:#c084fc;">
                    <span>🔍 قيد المراجعة والاعتماد</span>
                    <span class="job-chip" id="count-review" style="background:rgba(168,85,247,0.25); color:#fff;">0</span>
                </div>
                <div class="col-body" id="list-review"></div>
            </div>

            <div class="kanban-col" id="col-done" ondrop="dropJob(event, 'done')" ondragover="allowJobDrop(event)" ondragleave="dragJobLeave(event)">
                <div class="col-header" style="background:rgba(16,185,129,0.14); color:#34d399;">
                    <span>✅ مكتملة ومنجزة (Done)</span>
                    <span class="job-chip" id="count-done" style="background:rgba(16,185,129,0.25); color:#fff;">0</span>
                </div>
                <div class="col-body" id="list-done"></div>
            </div>
        </div>

        <!-- Analytical Table View -->
        <div id="jobs-table-view" class="glass-box" style="display:none; overflow-x:auto;">
            <table class="exec-table">
                <thead>
                    <tr>
                        <th>رقم الأمر</th>
                        <th>المهمة / البيان</th>
                        <th>القسم</th>
                        <th>الموظف المكلف</th>
                        <th>الجهة / الشاشة المرتبطة</th>
                        <th>الأثر المالي</th>
                        <th>تاريخ الاستحقاق</th>
                        <th>الأولوية</th>
                        <th>الحالة</th>
                        <th class="no-print">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="jobs-table-tbody"></tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Job Modal -->
    <div class="modal-overlay no-print" id="jobModal">
        <div class="modal-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(148,163,184,0.2);">
                <h3 id="job-modal-title" style="margin:0; color:#38bdf8; font-weight:900;">➕ إنشاء مهمة / أمر شغل تنفيذي</h3>
                <button type="button" onclick="closeJobModal()" style="background:none; border:none; color:#f87171; font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>

            <input type="hidden" id="jEditId" value="">

            <div class="form-grid-2">
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">عنوان المهمة / أمر الشغل *</label>
                    <input type="text" id="jTitle" class="form-input" placeholder="مثال: تحصيل مديونية عميل / جرد مخزن / تسوية فروق سعر الصرف">
                </div>

                <div>
                    <label class="form-label">القسم المختص</label>
                    <select id="jCategory" class="form-input">
                        <option value="collection">📞 تحصيل ديون وائتمان</option>
                        <option value="delivery">🚚 تسليم واستلام بضائع</option>
                        <option value="inventory">📦 مخازن وجرد ونواقص</option>
                        <option value="finance">💵 مالية وخزينة وشيكات</option>
                        <option value="fx">💱 سعر الصرف والعملات</option>
                        <option value="audit">🛡️ مراجعة وتدقيق محاسبي</option>
                        <option value="admin">🏢 إدارية وعامة</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">درجة الأولوية</label>
                    <select id="jPriority" class="form-input">
                        <option value="critical">🚨 حرجة وعاجلة جداً</option>
                        <option value="high">🔴 أولوية عالية</option>
                        <option value="medium" selected>🟡 أولوية متوسطة</option>
                        <option value="low">🟢 أولوية عادية</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">إسناد إلى الموظف</label>
                    <select id="jUser" class="form-input">
                        <option value="">-- اختر الموظف المسند إليه --</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">هاتف الموظف (لإشعار واتساب)</label>
                    <input type="text" id="jPhone" class="form-input" placeholder="2499xxxxxxxx">
                </div>

                <div>
                    <label class="form-label">تاريخ الاستحقاق (SLA Due Date)</label>
                    <input type="date" id="jDueDate" class="form-input">
                </div>

                <div>
                    <label class="form-label">الشاشة التنفيذية المرتبطة بالنظام</label>
                    <select id="jLinkedScreen" class="form-input">
                        <option value="">-- بدون شاشة محددة --</option>
                        <option value="aging-report.php">⏳ أعمار الديون وسقوف الائتمان</option>
                        <option value="single-cash-payment.php">💵 حركة مالية (قبض / صرف)</option>
                        <option value="journal-voucher.php">⚖️ سند قيد اليومية والتسويات</option>
                        <option value="sales-delivery.php">📤 بوابة تسليم المبيعات</option>
                        <option value="purchase.php">🛍️ فاتورة وطلبات المشتريات</option>
                        <option value="purchase-receipt.php">🚛 استلام بضاعة المشتريات</option>
                        <option value="warehouse-inventory.php">📦 الجرد الشامل للمستودعات</option>
                        <option value="checks-cycle.php">🏦 دورة الشيكات والأوراق التجارية</option>
                        <option value="customer-portfolio.php">👥 محفظة العملاء والموردين 360°</option>
                        <option value="supplier_reliability.php">🤝 اعتمادية وموثوقية الموردين 360°</option>
                        <option value="settings.php">⚙️ إعدادات العملات وسعر الصرف</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">العميل / المورد / الصنف المرتبط</label>
                    <input type="text" id="jEntityName" class="form-input" list="entities-datalist" placeholder="اختر أو اكتب اسم العميل/المورد/الصنف...">
                    <datalist id="entities-datalist"></datalist>
                </div>

                <div>
                    <label class="form-label">الأثر المالي أو المبلغ المستهدف (إن وجد)</label>
                    <div style="display:flex; gap:6px;">
                        <input type="number" step="any" id="jAmount" class="form-input" placeholder="0.00">
                        <select id="jCurrency" class="form-input" style="max-width:95px;">
                            <option value="SDG">SDG</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label class="form-label">التفاصيل والخطوات المطلوبة</label>
                    <textarea id="jDesc" class="form-input" rows="3" placeholder="اكتب خطوات التنفيذ أو أرقام الفواتير المرتبطة..."></textarea>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label class="form-label">قائمة المهام الفرعية (افصل بين كل خطوة بفاصلة أو سطر جديد)</label>
                    <textarea id="jChecklist" class="form-input" rows="2" placeholder="مثال: الاتصال بالعميل، استلام الشيك، ترحيل سند القبض"></textarea>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <button type="button" class="btn-exec btn-exec-dark" onclick="closeJobModal()">إلغاء</button>
                <button type="button" class="btn-exec btn-exec-primary" onclick="saveJob()"><i class="fa-solid fa-floppy-disk"></i> حفظ واعتماد المهمة</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261103_v29"></script>
    <script>
        let jobs = [];
        let jobsViewMode = 'kanban';
        let jobsDisplayCurrency = 'SDG';
        let currentRateUSD = 1;
        let baseCurCode = 'SDG';
        let foreignCurCode = 'USD';

        const CATEGORY_LABELS = {
            collection: '📞 تحصيل ديون',
            delivery: '🚚 تسليم واستلام',
            inventory: '📦 مخازن ونواقص',
            finance: '💵 مالية وخزينة',
            fx: '💱 سعر الصرف',
            audit: '🛡️ مراجعة وتدقيق',
            admin: '🏢 إدارية وعامة'
        };

        const PRIORITY_LABELS = {
            critical: { label: '🚨 حرجة جداً', color: '#f87171' },
            high: { label: '🔴 عالية', color: '#fbbf24' },
            medium: { label: '🟡 متوسطة', color: '#38bdf8' },
            low: { label: '🟢 عادية', color: '#34d399' }
        };

        function loadFXSettings() {
            try {
                const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
                baseCurCode = cfg.currency || 'SDG';
                foreignCurCode = cfg.foreignCurrency || 'USD';
                currentRateUSD = parseFloat(cfg.rateUSD) || 1;
                if (!jobsDisplayCurrency) jobsDisplayCurrency = baseCurCode;
                const badge = document.getElementById('jobs-fx-rate-badge');
                if (badge) {
                    badge.textContent = `1 ${foreignCurCode} = ${currentRateUSD.toLocaleString('en-US')} ${baseCurCode}`;
                }
            } catch (e) {}
        }

        function convertJobAmount(amount, cur) {
            const val = parseFloat(amount) || 0;
            if (val === 0) return 0;
            const itemCur = (cur || baseCurCode).toUpperCase();
            const rate = currentRateUSD > 0 ? currentRateUSD : 1;
            let inBase = (itemCur === foreignCurCode) ? (val * rate) : val;
            return (jobsDisplayCurrency === foreignCurCode) ? (inBase / rate) : inBase;
        }

        function toggleJobsCurrencyView() {
            jobsDisplayCurrency = (jobsDisplayCurrency === baseCurCode) ? foreignCurCode : baseCurCode;
            const btn = document.getElementById('jobs-cur-toggle-btn');
            if (btn) {
                btn.innerHTML = jobsDisplayCurrency === foreignCurCode
                    ? `العرض: ${foreignCurCode} 💵`
                    : `العرض: ${baseCurCode} 🇸🇩`;
            }
            renderJobs();
        }

        function initJobsSystem() {
            loadFXSettings();
            populateEmployeesAndEntities();
            try {
                jobs = JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]');
                if (!Array.isArray(jobs)) jobs = [];
            } catch (e) { jobs = []; }
            renderJobs();
        }

        document.addEventListener('DBReady', initJobsSystem);
        if (window.APP_READY) initJobsSystem();
        window.addEventListener('storage', (e) => {
            if (e.key === 'erp_settings_v1' || e.key === 'erp_jobs_v1') {
                loadFXSettings();
                try { jobs = JSON.parse(localStorage.getItem('erp_jobs_v1') || '[]'); } catch(err){}
                renderJobs();
            }
        });

        function populateEmployeesAndEntities() {
            const users = JSON.parse(localStorage.getItem('erp_users_v1') || '[]');
            const emps = JSON.parse(localStorage.getItem('erp_employees_v1') || '[]').filter(x => !x.deleted);
            const jUser = document.getElementById('jUser');
            const fUser = document.getElementById('filter-user');
            const namesMap = new Map();

            emps.forEach(e => {
                const n = e.name || e.fullName;
                if (n) namesMap.set(n, e.phone || '');
            });
            users.forEach(u => {
                const n = u.fullName || u.name || u.username;
                if (n && !namesMap.has(n)) namesMap.set(n, u.phone || '');
            });

            if (jUser) {
                jUser.innerHTML = '<option value="">-- اختر الموظف المسند إليه --</option>';
                namesMap.forEach((phone, name) => {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.dataset.phone = phone;
                    opt.textContent = name;
                    jUser.appendChild(opt);
                });
                jUser.onchange = function() {
                    const sel = jUser.options[jUser.selectedIndex];
                    if (sel && sel.dataset.phone && !document.getElementById('jPhone').value) {
                        document.getElementById('jPhone').value = sel.dataset.phone;
                    }
                };
            }

            if (fUser) {
                fUser.innerHTML = '<option value="all">👤 كل الموظفين</option>';
                namesMap.forEach((_, name) => {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    fUser.appendChild(opt);
                });
            }

            const dl = document.getElementById('entities-datalist');
            if (dl) {
                const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
                const sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
                const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(x => !x.deleted);
                let html = '';
                custs.forEach(c => { if (c.name) html += `<option value="عميل: ${c.name}">`; });
                sups.forEach(s => { if (s.name) html += `<option value="مورد: ${s.name}">`; });
                items.forEach(i => { if (i.name) html += `<option value="صنف: ${i.name}">`; });
                dl.innerHTML = html;
            }
        }

        function saveJobsStore() {
            localStorage.setItem('erp_jobs_v1', JSON.stringify(jobs));
        }

        function renderJobs() {
            const q = (document.getElementById('filter-search')?.value || '').trim().toLowerCase();
            const catFilter = document.getElementById('filter-category')?.value || 'all';
            const priFilter = document.getElementById('filter-priority')?.value || 'all';
            const usrFilter = document.getElementById('filter-user')?.value || 'all';
            const todayStr = new Date().toISOString().split('T')[0];

            ['todo', 'doing', 'review', 'done'].forEach(st => {
                const el = document.getElementById('list-' + st);
                if (el) el.innerHTML = '';
            });
            const tbody = document.getElementById('jobs-table-tbody');
            if (tbody) tbody.innerHTML = '';

            let counts = { todo: 0, doing: 0, review: 0, done: 0 };
            let criticalCount = 0;
            let overdueCount = 0;
            let totalFinancialImpact = 0;

            const filtered = jobs.filter(j => {
                if (window._onlyOverdueFilter) {
                    const isOver = j.dueDate && j.dueDate < todayStr && j.status !== 'done';
                    if (!isOver) return false;
                }
                if (window._statusQuickFilter && window._statusQuickFilter !== 'all') {
                    if (window._statusQuickFilter === 'doing') {
                        if (j.status !== 'doing' && j.status !== 'review') return false;
                    } else if (j.status !== window._statusQuickFilter) return false;
                }
                if (catFilter !== 'all' && (j.category || 'admin') !== catFilter) return false;
                if (priFilter !== 'all' && (j.priority || 'medium') !== priFilter) return false;
                if (usrFilter !== 'all' && (j.user || '') !== usrFilter) return false;
                if (q) {
                    const hay = `${j.title || ''} ${j.desc || ''} ${j.user || ''} ${j.entityName || ''} ${j.id || ''}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                return true;
            });

            jobs.forEach(j => {
                const st = ['todo', 'doing', 'review', 'done'].includes(j.status) ? j.status : 'todo';
                if (j.priority === 'critical' && st !== 'done') criticalCount++;
                if (j.dueDate && j.dueDate < todayStr && st !== 'done') overdueCount++;
                if (st !== 'done' && Number(j.amount) > 0) {
                    totalFinancialImpact += convertJobAmount(j.amount, j.currency);
                }
            });

            filtered.forEach(j => {
                const st = ['todo', 'doing', 'review', 'done'].includes(j.status) ? j.status : 'todo';
                counts[st]++;
                const priObj = PRIORITY_LABELS[j.priority] || PRIORITY_LABELS.medium;
                const catLabel = CATEGORY_LABELS[j.category] || '🏢 إدارية';
                const isOverdue = j.dueDate && j.dueDate < todayStr && st !== 'done';
                const convAmt = convertJobAmount(j.amount, j.currency);

                const checklistArr = Array.isArray(j.checklist) ? j.checklist : [];
                const doneSub = checklistArr.filter(c => c.done).length;
                const subPct = checklistArr.length > 0 ? Math.round((doneSub / checklistArr.length) * 100) : (st === 'done' ? 100 : (st === 'review' ? 75 : (st === 'doing' ? 40 : 0)));

                const card = document.createElement('div');
                card.className = `job-card priority-${j.priority || 'medium'}`;
                card.draggable = true;
                card.id = j.id;
                card.ondragstart = dragJob;

                let checklistHTML = '';
                if (checklistArr.length > 0) {
                    checklistHTML = `<div style="margin:6px 0 8px; padding:6px 8px; background:rgba(0,0,0,0.2); border-radius:8px; font-size:0.74rem;">` +
                        checklistArr.map((item, idx) => `
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin-bottom:3px; color:${item.done ? '#94a3b8' : '#e2e8f0'}; text-decoration:${item.done ? 'line-through' : 'none'};">
                                <input type="checkbox" ${item.done ? 'checked' : ''} onchange="toggleSubtask('${j.id}', ${idx})">
                                <span>${item.text}</span>
                            </label>
                        `).join('') + `</div>`;
                }

                card.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:6px;">
                        <h3 class="job-title">${j.title}</h3>
                        <span style="font-size:0.68rem; font-weight:900; color:${priObj.color}; white-space:nowrap;">${priObj.label}</span>
                    </div>
                    <p class="job-desc">${j.desc || 'لا توجد تفاصيل إضافية'}</p>
                    ${checklistHTML}
                    <div style="height:5px; background:rgba(255,255,255,0.08); border-radius:4px; overflow:hidden; margin-bottom:8px;">
                        <div style="width:${subPct}%; height:100%; background:${st === 'done' ? '#10b981' : '#38bdf8'};"></div>
                    </div>
                    <div class="job-meta-row">
                        <span class="job-chip">${catLabel}</span>
                        <span class="job-chip">👤 ${j.user || 'غير مسند'}</span>
                        ${j.entityName ? `<span class="job-chip" style="color:#38bdf8; border-color:rgba(56,189,248,0.35);">🏷️ ${j.entityName}</span>` : ''}
                        ${convAmt > 0 ? `<span class="job-chip" style="color:#fbbf24; border-color:rgba(245,158,11,0.4);">💰 ${Math.round(convAmt).toLocaleString('en-US')} ${jobsDisplayCurrency}</span>` : ''}
                        ${j.dueDate ? `<span class="job-chip" style="${isOverdue ? 'background:rgba(239,68,68,0.22); color:#fca5a5; border-color:#ef4444;' : ''}">📅 ${j.dueDate} ${isOverdue ? '⚠️ متأخر' : ''}</span>` : ''}
                    </div>
                    <div class="job-actions-bar no-print">
                        <div style="display:flex; gap:4px; flex-wrap:wrap;">
                            ${j.linkedScreen ? `<a href="${j.linkedScreen}" class="mini-act-btn" style="background:rgba(16,185,129,0.2); border-color:#10b981; color:#34d399;" title="فتح الشاشة المرتبطة للتنفيذ الفوري">⚡ تنفيذ</a>` : ''}
                            ${st !== 'done' ? `<button type="button" class="mini-act-btn" onclick="advanceJobStatus('${j.id}')" title="نقل للمرحلة التالية">⏭️ تقدم</button>` : ''}
                            <button type="button" class="mini-act-btn" onclick="sendJobWhatsApp('${j.id}')" style="color:#4ade80;" title="إرسال أمر التكليف عبر واتساب">💬</button>
                            <button type="button" class="mini-act-btn" onclick="printSingleJobOrder('${j.id}')" title="طباعة أمر شغل رسمي بـ QR">🖨️</button>
                        </div>
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="mini-act-btn" onclick="openJobModal('${j.id}')" title="تعديل">✏️</button>
                            <button type="button" class="mini-act-btn" onclick="deleteJob('${j.id}')" style="color:#f87171;" title="حذف">🗑️</button>
                        </div>
                    </div>
                `;

                const colList = document.getElementById('list-' + st);
                if (colList) colList.appendChild(card);

                if (tbody) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong style="color:#38bdf8;">#${String(j.id).slice(-6)}</strong></td>
                        <td><strong>${j.title}</strong><div style="font-size:0.75rem; color:#94a3b8;">${j.desc || ''}</div></td>
                        <td>${catLabel}</td>
                        <td>👤 ${j.user || 'غير مسند'}</td>
                        <td>${j.entityName || '-'} ${j.linkedScreen ? `<a href="${j.linkedScreen}" class="mini-act-btn" style="margin-right:4px;">🔗 فتح</a>` : ''}</td>
                        <td><strong style="color:#fbbf24;">${convAmt > 0 ? Math.round(convAmt).toLocaleString('en-US') + ' ' + jobsDisplayCurrency : '-'}</strong></td>
                        <td>${j.dueDate || '-'} ${isOverdue ? '<span style="color:#f87171; font-weight:900;">(متأخر)</span>' : ''}</td>
                        <td><span style="color:${priObj.color}; font-weight:800;">${priObj.label}</span></td>
                        <td>${st === 'done' ? '✅ مكتملة' : (st === 'review' ? '🔍 مراجعة' : (st === 'doing' ? '⚡ قيد التنفيذ' : '📋 جديدة'))}</td>
                        <td class="no-print">
                            <div style="display:flex; gap:4px;">
                                <button type="button" class="mini-act-btn" onclick="openJobModal('${j.id}')">✏️</button>
                                <button type="button" class="mini-act-btn" onclick="sendJobWhatsApp('${j.id}')">💬</button>
                                <button type="button" class="mini-act-btn" onclick="printSingleJobOrder('${j.id}')">🖨️</button>
                                <button type="button" class="mini-act-btn" onclick="deleteJob('${j.id}')" style="color:#f87171;">🗑️</button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
            });

            document.getElementById('count-todo').innerText = counts.todo;
            document.getElementById('count-doing').innerText = counts.doing;
            document.getElementById('count-review').innerText = counts.review;
            document.getElementById('count-done').innerText = counts.done;

            const totalAll = jobs.length;
            const doneAll = jobs.filter(j => j.status === 'done').length;
            const doingAll = jobs.filter(j => j.status === 'doing' || j.status === 'review').length;
            const completionRate = totalAll > 0 ? Math.round((doneAll / totalAll) * 100) : 0;

            document.getElementById('kpi-jobs-total').innerText = totalAll;
            document.getElementById('kpi-jobs-critical').innerText = criticalCount;
            document.getElementById('kpi-jobs-doing').innerText = doingAll;
            document.getElementById('kpi-jobs-overdue').innerText = overdueCount;
            document.getElementById('kpi-jobs-rate').innerText = completionRate + '%';
            document.getElementById('kpi-jobs-done-sub').innerText = `${doneAll} من أصل ${totalAll} مهمة منجزة`;
            document.getElementById('kpi-jobs-amount').innerText = `${Math.round(totalFinancialImpact).toLocaleString('en-US')} ${jobsDisplayCurrency}`;
        }

        function setJobsViewMode(mode) {
            jobsViewMode = mode;
            document.getElementById('jobs-kanban-view').style.display = mode === 'kanban' ? 'grid' : 'none';
            document.getElementById('jobs-table-view').style.display = mode === 'table' ? 'block' : 'none';
            document.getElementById('view-btn-kanban').className = mode === 'kanban' ? 'btn-exec btn-exec-primary' : 'btn-exec btn-exec-dark';
            document.getElementById('view-btn-table').className = mode === 'table' ? 'btn-exec btn-exec-primary' : 'btn-exec btn-exec-dark';
        }

        function filterByStatus(st) {
            window._onlyOverdueFilter = false;
            window._statusQuickFilter = st;
            renderJobs();
        }
        function filterByPriority(pri) {
            window._onlyOverdueFilter = false;
            window._statusQuickFilter = 'all';
            document.getElementById('filter-priority').value = pri;
            renderJobs();
        }
        function filterByOverdue() {
            window._onlyOverdueFilter = !window._onlyOverdueFilter;
            renderJobs();
        }

        function allowJobDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('drag-over');
        }
        function dragJobLeave(ev) {
            ev.currentTarget.classList.remove('drag-over');
        }
        function dragJob(ev) {
            ev.dataTransfer.setData('jobId', ev.currentTarget.id);
        }
        function dropJob(ev, newStatus) {
            ev.preventDefault();
            ev.currentTarget.classList.remove('drag-over');
            const jobId = ev.dataTransfer.getData('jobId');
            const idx = jobs.findIndex(j => j.id === jobId);
            if (idx > -1) {
                const oldStatus = jobs[idx].status;
                jobs[idx].status = newStatus;
                jobs[idx].updatedAt = Date.now();
                saveJobsStore();
                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('تحديث حالة مهمة تنفيذية', `تم نقل المهمة (${jobs[idx].title}) من [${oldStatus}] إلى [${newStatus}]`, 'JOBS', jobs[idx].id);
                }
                renderJobs();
            }
        }

        function advanceJobStatus(id) {
            const order = ['todo', 'doing', 'review', 'done'];
            const idx = jobs.findIndex(j => j.id === id);
            if (idx > -1) {
                const cur = order.indexOf(jobs[idx].status || 'todo');
                const next = order[Math.min(order.length - 1, cur + 1)];
                jobs[idx].status = next;
                jobs[idx].updatedAt = Date.now();
                saveJobsStore();
                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('ترقية مرحلة مهمة', `ترقية المهمة (${jobs[idx].title}) إلى مرحلة [${next}]`, 'JOBS', id);
                }
                renderJobs();
            }
        }

        function toggleSubtask(jobId, subIdx) {
            const j = jobs.find(x => x.id === jobId);
            if (j && Array.isArray(j.checklist) && j.checklist[subIdx]) {
                j.checklist[subIdx].done = !j.checklist[subIdx].done;
                const allDone = j.checklist.length > 0 && j.checklist.every(c => c.done);
                if (allDone && j.status !== 'done') j.status = 'review';
                saveJobsStore();
                renderJobs();
            }
        }

        function openJobModal(editId = '') {
            const modal = document.getElementById('jobModal');
            document.getElementById('jEditId').value = editId || '';
            const today = new Date().toISOString().split('T')[0];

            if (editId) {
                const j = jobs.find(x => x.id === editId);
                if (!j) return;
                document.getElementById('job-modal-title').innerText = '✏️ تعديل المهمة / أمر الشغل';
                document.getElementById('jTitle').value = j.title || '';
                document.getElementById('jCategory').value = j.category || 'admin';
                document.getElementById('jPriority').value = j.priority || 'medium';
                document.getElementById('jUser').value = j.user || '';
                document.getElementById('jPhone').value = j.phone || '';
                document.getElementById('jDueDate').value = j.dueDate || today;
                document.getElementById('jLinkedScreen').value = j.linkedScreen || '';
                document.getElementById('jEntityName').value = j.entityName || '';
                document.getElementById('jAmount').value = j.amount || '';
                document.getElementById('jCurrency').value = j.currency || baseCurCode;
                document.getElementById('jDesc').value = j.desc || '';
                document.getElementById('jChecklist').value = Array.isArray(j.checklist) ? j.checklist.map(c => c.text).join('، ') : '';
            } else {
                document.getElementById('job-modal-title').innerText = '➕ إنشاء مهمة / أمر شغل تنفيذي جديد';
                document.getElementById('jTitle').value = '';
                document.getElementById('jCategory').value = 'collection';
                document.getElementById('jPriority').value = 'medium';
                document.getElementById('jUser').value = '';
                document.getElementById('jPhone').value = '';
                document.getElementById('jDueDate').value = today;
                document.getElementById('jLinkedScreen').value = '';
                document.getElementById('jEntityName').value = '';
                document.getElementById('jAmount').value = '';
                document.getElementById('jCurrency').value = baseCurCode;
                document.getElementById('jDesc').value = '';
                document.getElementById('jChecklist').value = '';
            }
            modal.style.display = 'flex';
        }

        function closeJobModal() {
            document.getElementById('jobModal').style.display = 'none';
        }

        function saveJob() {
            const title = document.getElementById('jTitle').value.trim();
            if (!title) { alert('يرجى إدخال عنوان المهمة / أمر الشغل'); return; }

            const editId = document.getElementById('jEditId').value;
            const rawChecklist = document.getElementById('jChecklist').value.trim();
            const parsedChecklist = rawChecklist
                ? rawChecklist.split(/[\n,،]+/).map(s => s.trim()).filter(Boolean).map(text => ({ text, done: false }))
                : [];

            const payload = {
                id: editId || ('JOB-' + Date.now()),
                title,
                category: document.getElementById('jCategory').value,
                priority: document.getElementById('jPriority').value,
                user: document.getElementById('jUser').value,
                phone: document.getElementById('jPhone').value.trim(),
                dueDate: document.getElementById('jDueDate').value,
                linkedScreen: document.getElementById('jLinkedScreen').value,
                entityName: document.getElementById('jEntityName').value.trim(),
                amount: parseFloat(document.getElementById('jAmount').value) || 0,
                currency: document.getElementById('jCurrency').value || baseCurCode,
                desc: document.getElementById('jDesc').value.trim(),
                checklist: parsedChecklist,
                status: editId ? (jobs.find(x => x.id === editId)?.status || 'todo') : 'todo',
                createdAt: editId ? (jobs.find(x => x.id === editId)?.createdAt || Date.now()) : Date.now(),
                updatedAt: Date.now()
            };

            if (editId) {
                const idx = jobs.findIndex(x => x.id === editId);
                if (idx > -1) jobs[idx] = payload;
            } else {
                jobs.unshift(payload);
            }

            saveJobsStore();
            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction(editId ? 'تعديل مهمة تنفيذية' : 'إنشاء مهمة تنفيذية جديدة', `${payload.title} - المسند إليه: ${payload.user || 'غير محدد'}`, 'JOBS', payload.id);
            }
            closeJobModal();
            renderJobs();
        }

        function createQuickSOPTask(title, desc, category, priority, linkedScreen) {
            const today = new Date().toISOString().split('T')[0];
            const newJob = {
                id: 'JOB-' + Date.now(),
                title,
                desc,
                category,
                priority,
                user: '',
                phone: '',
                dueDate: today,
                linkedScreen,
                entityName: 'النظام العام',
                amount: 0,
                currency: baseCurCode,
                checklist: [
                    { text: 'مراجعة البيانات في الشاشة المرتبطة', done: false },
                    { text: 'تنفيذ الإجراء المحاسبي أو المخزني', done: false },
                    { text: 'اعتماد الإغلاق في سجل الرقابة', done: false }
                ],
                status: 'todo',
                createdAt: Date.now()
            };
            jobs.unshift(newJob);
            saveJobsStore();
            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('توليد مهمة قياسية سريعة SOP', title, 'JOBS', newJob.id);
            }
            renderJobs();
        }

        function generateAISystemTasks() {
            const today = new Date().toISOString().split('T')[0];
            let addedCount = 0;

            const existingTitles = new Set(jobs.filter(j => j.status !== 'done').map(j => j.title));
            const invoices = (JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]')).filter(x => !x.deleted && x.saleType !== 'preliminary');
            const items = (JSON.parse(localStorage.getItem('erp_items_v1') || '[]')).filter(x => !x.deleted);
            const inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            const customers = (JSON.parse(localStorage.getItem('erp_customers_v1') || '[]')).filter(x => !x.deleted);
            const checks = (JSON.parse(localStorage.getItem('erp_checks_v1') || '[]')).filter(x => !x.deleted);

            // 1. Overdue credit invoices & High Customer Debts
            customers.forEach(c => {
                const bal = parseFloat(c.currentBalance) || 0;
                const limit = parseFloat(c.creditLimit) || 0;
                if (bal > 0 && (limit > 0 && bal >= limit * 0.9)) {
                    const t = `تحصيل مديونية حرجة وتجاوز سقف ائتمان: ${c.name}`;
                    if (!existingTitles.has(t)) {
                        jobs.unshift({
                            id: 'JOB-AI-' + Date.now() + '-' + Math.floor(Math.random()*1000),
                            title: t,
                            desc: `العميل (${c.name}) بلغت مديونيته ${bal.toLocaleString()} وتجاوزت 90% من سقف الائتمان المسموح.`,
                            category: 'collection',
                            priority: 'critical',
                            user: '',
                            phone: c.phone || '',
                            dueDate: today,
                            linkedScreen: 'aging-report.php',
                            entityName: `عميل: ${c.name}`,
                            amount: bal,
                            currency: baseCurCode,
                            checklist: [
                                { text: 'مراجعة كشف حساب العميل وأعمار الديون', done: false },
                                { text: 'الاتصال بالعميل وإرسال مطالبة واتساب', done: false },
                                { text: 'تحرير سند قبض بالمبلغ المحصل', done: false }
                            ],
                            status: 'todo',
                            createdAt: Date.now()
                        });
                        existingTitles.add(t);
                        addedCount++;
                    }
                }
            });

            // 2. Pending deliveries
            invoices.slice(0, 15).forEach(inv => {
                if (inv.deliveryStatus && inv.deliveryStatus !== 'delivered' && inv.deliveryStatus !== 'Admin_Closed') {
                    const t = `تسليم بضاعة معلق للفاتورة #${inv.invoiceNo || inv.id}`;
                    if (!existingTitles.has(t)) {
                        jobs.unshift({
                            id: 'JOB-AI-' + Date.now() + '-' + Math.floor(Math.random()*1000),
                            title: t,
                            desc: `إذن تسليم مبيعات معلق بانتظار الصرف المخزني للعميل (${inv.customerName || inv.customer || 'عميل'}).`,
                            category: 'delivery',
                            priority: 'high',
                            user: '',
                            phone: '',
                            dueDate: today,
                            linkedScreen: 'sales-delivery.php',
                            entityName: inv.customerName || inv.customer || '',
                            amount: parseFloat(inv.grandTotal || inv.totalAmount) || 0,
                            currency: inv.currency || baseCurCode,
                            checklist: [
                                { text: 'فحص توفر الصنف في المخزن', done: false },
                                { text: 'اعتماد تسليم البضاعة للعميل', done: false }
                            ],
                            status: 'todo',
                            createdAt: Date.now()
                        });
                        existingTitles.add(t);
                        addedCount++;
                    }
                }
            });

            // 3. Low Stock Items
            items.forEach(it => {
                const stock = inventory.filter(x => x.itemId === it.id && x.warehouse !== 'PENDING_RECEIPT').reduce((s, x) => s + (Number(x.qty) || 0), 0);
                const minQ = Number(it.safeLimit) || Number(it.minQty) || 5;
                if (stock <= minQ) {
                    const t = `إعادة طلب توريد صنف ناقص: ${it.name}`;
                    if (!existingTitles.has(t)) {
                        jobs.unshift({
                            id: 'JOB-AI-' + Date.now() + '-' + Math.floor(Math.random()*1000),
                            title: t,
                            desc: `الرصيد المتوفر حالياً (${stock}) أقل من حد الأمان (${minQ}). يوصى بإصدار طلب شراء فوري.`,
                            category: 'inventory',
                            priority: stock <= 0 ? 'critical' : 'high',
                            user: '',
                            phone: '',
                            dueDate: today,
                            linkedScreen: 'purchase.php',
                            entityName: `صنف: ${it.name}`,
                            amount: 0,
                            currency: baseCurCode,
                            checklist: [
                                { text: 'مراجعة أفضل مورد للصنف في شاشة اعتمادية الموردين', done: false },
                                { text: 'إصدار فاتورة مشتريات / أمر توريد', done: false }
                            ],
                            status: 'todo',
                            createdAt: Date.now()
                        });
                        existingTitles.add(t);
                        addedCount++;
                    }
                }
            });

            // 4. Checks nearing maturity
            checks.forEach(chk => {
                if (chk.status !== 'collected' && chk.status !== 'paid' && chk.dueDate) {
                    const t = `متابعة تحصيل/سداد شيك رقم #${chk.checkNo || chk.id}`;
                    if (!existingTitles.has(t)) {
                        jobs.unshift({
                            id: 'JOB-AI-' + Date.now() + '-' + Math.floor(Math.random()*1000),
                            title: t,
                            desc: `شيك مستحق بتاريخ ${chk.dueDate} بقيمة ${Number(chk.amount || 0).toLocaleString()}.`,
                            category: 'finance',
                            priority: 'high',
                            user: '',
                            phone: '',
                            dueDate: chk.dueDate,
                            linkedScreen: 'checks-cycle.php',
                            entityName: chk.beneficiary || chk.drawer || 'شيك بنكي',
                            amount: parseFloat(chk.amount) || 0,
                            currency: baseCurCode,
                            checklist: [
                                { text: 'مراجعة البنك وتحصيل أو تسوية الشيك', done: false }
                            ],
                            status: 'todo',
                            createdAt: Date.now()
                        });
                        existingTitles.add(t);
                        addedCount++;
                    }
                }
            });

            if (addedCount === 0) {
                createQuickSOPTask('فحص ومراجعة المؤشرات المالية وسعر الصرف اليومي', 'تم فحص النظام الذكي: يوصى بمراجعة سعر الصرف والسيولة اليومية', 'fx', 'medium', 'magaiees_unified_dashboard.php');
                addedCount = 1;
            } else {
                saveJobsStore();
                renderJobs();
            }

            if (typeof window.logSystemAction === 'function') {
                window.logSystemAction('توليد المهام الذكي AI', `قام الرادار الذكي بتوليد ${addedCount} مهام تنفيذية مرتبطة بالشاشات`, 'JOBS');
            }
            alert(`🤖 اكتمل فحص النظام الذكي وتم توليد (${addedCount}) مهام عمل تنفيذية مربوطة بالشاشات المعنية!`);
        }

        function sendJobWhatsApp(id) {
            const j = jobs.find(x => x.id === id);
            if (!j) return;
            const phone = (j.phone || '').replace(/[^0-9]/g, '');
            const msg = `📋 *أمر تكليف مهمة تنفيذية - نظام مقاييس*\n` +
                `━━━━━━━━━━━━━━━━━━━━\n` +
                `🔹 *رقم الأمر:* ${j.id}\n` +
                `📌 *المهمة:* ${j.title}\n` +
                `👤 *المكلف:* ${j.user || 'الإدارة التنفيذية'}\n` +
                `🏷️ *الجهة المرتبطة:* ${j.entityName || '-'}\n` +
                `📅 *تاريخ الاستحقاق:* ${j.dueDate || 'فوري'}\n` +
                (j.amount > 0 ? `💰 *المبلغ المستهدف:* ${Number(j.amount).toLocaleString()} ${j.currency || baseCurCode}\n` : '') +
                `📝 *التفاصيل:* ${j.desc || '-'}\n` +
                `━━━━━━━━━━━━━━━━━━━━\n` +
                `يرجى التنفيذ وتحديث الحالة في النظام.`;
            const url = phone
                ? `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`
                : `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        function printSingleJobOrder(id) {
            const j = jobs.find(x => x.id === id);
            if (!j) return;
            const qrData = encodeURIComponent(`JOB:${j.id}|TITLE:${j.title}|USER:${j.user || ''}|DUE:${j.dueDate || ''}`);
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${qrData}`;
            const w = window.open('', '_blank', 'width=820,height=700');
            w.document.write(`
                <!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8">
                <title>أمر تكليف رسمي #${j.id}</title>
                <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;900&display=swap" rel="stylesheet">
                <style>
                    body { font-family:'Tajawal',sans-serif; padding:30px; color:#0f172a; }
                    .box { border:2px solid #0f172a; border-radius:14px; padding:24px; }
                    .hdr { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #0f172a; padding-bottom:15px; margin-bottom:20px; }
                    .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px; }
                    .cell { background:#f8fafc; border:1px solid #cbd5e1; padding:10px 14px; border-radius:8px; }
                </style></head><body onload="window.print()">
                <div class="box">
                    <div class="hdr">
                        <div>
                            <h2 style="margin:0;">💼 أمر تكليف ومهمة عمل رسمية</h2>
                            <div>رقم الأمر: <strong>${j.id}</strong> | تاريخ الإصدار: ${new Date().toLocaleDateString('ar-EG')}</div>
                        </div>
                        <img src="${qrUrl}" width="95" height="95" alt="QR">
                    </div>
                    <div class="grid">
                        <div class="cell"><strong>عنوان المهمة:</strong> ${j.title}</div>
                        <div class="cell"><strong>الموظف المكلف:</strong> ${j.user || 'غير محدد'}</div>
                        <div class="cell"><strong>الجهة / البند المرتبط:</strong> ${j.entityName || '-'}</div>
                        <div class="cell"><strong>تاريخ الاستحقاق:</strong> ${j.dueDate || 'فوري'}</div>
                        <div class="cell"><strong>الأثر المالي:</strong> ${j.amount > 0 ? Number(j.amount).toLocaleString() + ' ' + (j.currency || baseCurCode) : '-'}</div>
                        <div class="cell"><strong>الشاشة التنفيذية:</strong> ${j.linkedScreen || '-'}</div>
                    </div>
                    <div class="cell" style="margin-bottom:20px;"><strong>التفاصيل والتعليمات التنفيذية:</strong><br>${j.desc || '-'}</div>
                    <div style="display:flex; justify-content:space-between; margin-top:45px; font-weight:bold;">
                        <div>توقيع الموظف المكلف: ........................</div>
                        <div>توقيع المدير المسؤول: ........................</div>
                    </div>
                </div></body></html>
            `);
            w.document.close();
        }

        function exportJobsCSV() {
            if (!jobs.length) { alert('لا توجد مهام لتصديرها'); return; }
            const headers = ['رقم الأمر', 'العنوان', 'القسم', 'الموظف', 'الجهة المرتبطة', 'المبلغ', 'العملة', 'تاريخ الاستحقاق', 'الأولوية', 'الحالة'];
            const rows = jobs.map(j => [
                j.id,
                `"${(j.title || '').replace(/"/g, '""')}"`,
                CATEGORY_LABELS[j.category] || j.category || '',
                `"${(j.user || '').replace(/"/g, '""')}"`,
                `"${(j.entityName || '').replace(/"/g, '""')}"`,
                j.amount || 0,
                j.currency || baseCurCode,
                j.dueDate || '',
                j.priority || '',
                j.status || ''
            ]);
            const csv = '\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `executive_tasks_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
        }

        function deleteJob(id) {
            const j = jobs.find(x => x.id === id);
            if (!j) return;
            if (confirm(`هل أنت متأكد من حذف المهمة (${j.title})؟`)) {
                jobs = jobs.filter(x => x.id !== id);
                saveJobsStore();
                if (typeof window.logSystemAction === 'function') {
                    window.logSystemAction('حذف مهمة تنفيذية', `تم حذف المهمة (${j.title})`, 'JOBS', id);
                }
                renderJobs();
            }
        }
    </script>
    <script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
    <script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
