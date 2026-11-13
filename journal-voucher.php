<?php
$page_title = 'منظومة سند قيد اليومية العام والتسويات المحاسبية الذكية 360°';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منظومة سند قيد اليومية العام والتسويات الذكية 360° | السيطرة</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <link rel="stylesheet" href="css/unified-design-tokens.css">
    <style>
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast {
            padding:12px 20px; border-radius:12px; font-weight:800; font-size:0.86rem;
            display:flex; align-items:center; gap:8px; pointer-events:auto;
            backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5);
            animation:toastIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
            font-family:'Tajawal',sans-serif; max-width:90vw;
        }
        .toast.success { background:rgba(16,185,129,0.18); border:1px solid rgba(16,185,129,0.45); color:#34d399; }
        .toast.error { background:rgba(239,68,68,0.18); border:1px solid rgba(239,68,68,0.45); color:#f87171; }
        .toast.warning { background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.45); color:#fbbf24; }
        .toast.hide { animation:toastOut 0.35s forwards; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity:0; transform:translateY(-20px) scale(0.95); } }

        .jv-master-wrap {
            max-width: 1660px;
            margin: 0 auto;
            padding: 16px 22px 110px 22px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
            direction: rtl;
        }

        /* Hero Banner */
        .jv-hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
            border: 1.5px solid rgba(192, 132, 252, 0.38);
            border-right: 6px solid #c084fc;
            border-radius: 20px;
            padding: 18px 22px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.35);
        }

        .jv-hero-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .jv-hero-ico {
            width: 54px;
            height: 54px;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(192, 132, 252, 0.25), rgba(56, 189, 248, 0.2));
            border: 1.5px solid rgba(192, 132, 252, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .jv-hero-text h1 {
            margin: 0;
            font-size: 1.38rem;
            font-weight: 900;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .jv-hero-text p {
            margin: 4px 0 0 0;
            font-size: 0.84rem;
            color: #94a3b8;
        }

        .jv-top-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 13px;
            border-radius: 11px;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(30, 41, 59, 0.88);
            color: #f8fafc;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .jv-top-btn:hover {
            transform: translateY(-2px);
            border-color: #c084fc;
        }

        .jv-btn-purple { background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(126, 34, 206, 0.22)); border-color: rgba(168, 85, 247, 0.5); color: #c084fc; }
        .jv-btn-emerald { background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.22)); border-color: rgba(16, 185, 129, 0.5); color: #34d399; }
        .jv-btn-amber { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(217, 119, 6, 0.22)); border-color: rgba(245, 158, 11, 0.5); color: #fbbf24; }
        .jv-btn-sky { background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(37, 99, 235, 0.22)); border-color: rgba(56, 189, 248, 0.5); color: #38bdf8; }
        .jv-btn-rose { background: linear-gradient(135deg, rgba(244, 63, 94, 0.25), rgba(225, 29, 72, 0.22)); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }

        /* Smart Templates Bar */
        .jv-templates-bar {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(192, 132, 252, 0.22);
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .jv-tpl-chip {
            padding: 6px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .jv-tpl-chip:hover {
            background: rgba(192, 132, 252, 0.2);
            border-color: #c084fc;
            color: #fff;
            transform: translateY(-1px);
        }

        /* Balance Box */
        .balance-box {
            background: rgba(15, 23, 42, 0.92);
            border: 1.5px solid rgba(192, 132, 252, 0.35);
            border-radius: 16px;
            padding: 14px 22px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
            gap: 14px;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            margin-bottom: 18px;
            position: sticky;
            top: 10px;
            z-index: 50;
            backdrop-filter: blur(12px);
        }
        .bal-stat { text-align: center; padding: 4px 8px; border-left: 1px solid rgba(255,255,255,0.08); }
        .bal-stat:last-child { border-left: none; }
        .bal-stat .lbl { display: block; font-size: 0.76rem; font-weight: 800; color: #94a3b8; margin-bottom: 4px; }
        .bal-stat .val { font-size: 1.28rem; font-weight: 900; font-family: monospace; }
        .val-debit { color: #34d399; }
        .val-credit { color: #f87171; }
        .val-diff { color: #fbbf24; }
        .val-diff.balanced { color: #34d399; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }
        .form-input {
            width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.14);
            color: #f8fafc; padding: 9px 12px; border-radius: 10px;
            font-family: 'Tajawal', sans-serif; font-size: 0.88rem; font-weight: 700; box-sizing: border-box;
        }
        .form-input:focus { border-color: #c084fc; outline: none; box-shadow: 0 0 0 3px rgba(192,132,252,0.18); }

        .form-section-title {
            font-size: 1rem; font-weight: 900; color: #c084fc;
            margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px;
            border-bottom: 1px solid rgba(192,132,252,0.22); padding-bottom: 8px;
        }

        /* JV Table */
        .jv-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
        .jv-table th {
            text-align: right; padding: 11px 10px; color: #c084fc;
            font-weight: 900; font-size: 0.8rem; background: rgba(15, 23, 42, 0.95);
            border-bottom: 1.5px solid rgba(192,132,252,0.3); white-space: nowrap;
        }
        .jv-row td { padding: 4px 5px; background: rgba(15, 23, 42, 0.6); vertical-align: middle; }
        .jv-input {
            width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.12);
            color: #f8fafc; padding: 8px 10px; border-radius: 8px; font-size: 0.86rem;
            font-family: 'Tajawal', sans-serif; font-weight: 700; box-sizing: border-box;
        }
        .jv-input:focus { border-color: #c084fc; outline: none; }
        .jv-input.debit { color: #34d399; font-weight: 900; font-family: monospace; }
        .jv-input.credit { color: #f87171; font-weight: 900; font-family: monospace; }
        .jv-del-btn { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; color: #f87171; font-size: 1rem; cursor: pointer; padding: 6px 9px; }

        .btn-add-row {
            background: rgba(192, 132, 252, 0.12); color: #c084fc; border: 1.5px dashed #c084fc;
            padding: 10px 18px; border-radius: 10px; cursor: pointer; font-weight: 800; font-family: 'Tajawal', sans-serif;
            transition: all 0.2s;
        }
        .btn-add-row:hover { background: rgba(192, 132, 252, 0.22); }

        /* Archive Table */
        .jv-archive-table { width: 100%; border-collapse: collapse; text-align: right; }
        .jv-archive-table th { background: rgba(15,23,42,0.95); color: #38bdf8; padding: 11px 12px; font-size: 0.8rem; font-weight: 900; border-bottom: 1.5px solid rgba(56,189,248,0.25); }
        .jv-archive-table td { padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.84rem; color: #e2e8f0; }
        .jv-archive-table tbody tr:hover { background: rgba(192,132,252,0.07); }

        /* Light Mode */
        html[data-theme="warm_cream"] .jv-hero,
        html[data-theme="ultra_white"] .jv-hero,
        html[data-theme="exec_gray"] .jv-hero,
        html[data-theme="warm_cream"] .jv-templates-bar,
        html[data-theme="ultra_white"] .jv-templates-bar,
        html[data-theme="exec_gray"] .jv-templates-bar,
        html[data-theme="warm_cream"] .balance-box,
        html[data-theme="ultra_white"] .balance-box,
        html[data-theme="exec_gray"] .balance-box,
        html[data-theme="warm_cream"] .glass-panel,
        html[data-theme="ultra_white"] .glass-panel,
        html[data-theme="exec_gray"] .glass-panel {
            background: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06) !important;
        }
        html[data-theme="warm_cream"] .jv-hero-text h1,
        html[data-theme="ultra_white"] .jv-hero-text h1,
        html[data-theme="exec_gray"] .jv-hero-text h1,
        html[data-theme="warm_cream"] .jv-archive-table td,
        html[data-theme="ultra_white"] .jv-archive-table td,
        html[data-theme="exec_gray"] .jv-archive-table td {
            color: #0f172a !important;
        }
        html[data-theme="warm_cream"] .form-input,
        html[data-theme="ultra_white"] .form-input,
        html[data-theme="exec_gray"] .form-input,
        html[data-theme="warm_cream"] .jv-input,
        html[data-theme="ultra_white"] .jv-input,
        html[data-theme="exec_gray"] .jv-input {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }

        @media print {
            .ambient-bg, .jv-templates-bar, .sticky-footer, .btn-add-row, .jv-del-btn, #toast-container, header, nav, .no-print, #jv-archive-section {
                display: none !important;
            }
            body, .jv-master-wrap { background: #fff !important; color: #000 !important; padding: 0 !important; }
            .glass-panel, .balance-box, .jv-hero { background: #fff !important; border: 1px solid #cbd5e1 !important; box-shadow: none !important; color: #000 !important; }
            .jv-print-audit-footer { display: flex !important; justify-content: space-between !important; border-top: 2px solid #334155 !important; padding-top: 12px !important; margin-top: 28px !important; font-size: 9pt !important; color: #0f172a !important; }
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <div class="jv-master-wrap">
        <?php $header_theme_class = 'header-theme-sales'; include 'includes/header.php'; ?>

        <!-- Hero Header -->
        <div class="jv-hero">
            <div class="jv-hero-title">
                <div class="jv-hero-ico">⚖️</div>
                <div class="jv-hero-text">
                    <h1>
                        منظومة سند قيد اليومية العام والتسويات المحاسبية الذكية 360°
                        <span id="voucher-number" style="font-family:monospace; font-size:1.05rem; background:rgba(192,132,252,0.2); border:1px solid #c084fc; color:#e9d5ff; padding:3px 12px; border-radius:9px;">JV-00001</span>
                    </h1>
                    <p>إصدار قيود اليومية المزدوجة، التسويات الجردية والمالية، القيود العكسية، مع الترحيل اللحظي لدفتر الأستاذ العام وكشوفات الحسابات</p>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;" class="no-print">
                <div class="inv-nav" style="display:inline-flex; align-items:center; background:rgba(15,23,42,0.9); border-radius:10px; border:1px solid rgba(192,132,252,0.35); padding:4px 8px; gap:6px;">
                    <button class="jv-top-btn" style="padding:4px 9px;" onclick="loadPreviousVoucher()" title="القيد السابق">▶ السابق</button>
                    <button class="jv-top-btn" style="padding:4px 9px;" onclick="loadNextVoucher()" title="القيد التالي">التالي ◀</button>
                </div>

                <button onclick="autoBalanceJournal()" class="jv-top-btn jv-btn-emerald" title="موازنة الفرق بين المدين والدائن تلقائياً (Alt+B)">
                    ⚡ موازنة تلقائية
                </button>
                <button onclick="reverseCurrentJournal()" class="jv-top-btn jv-btn-amber" title="عكس القيد المحاسبي (قلب المدين دائناً والدائن مديناً) (Alt+R)">
                    🔄 عكس القيد
                </button>
                <button onclick="duplicateCurrentJournal()" class="jv-top-btn jv-btn-purple" title="نسخ وتكرار القيد الحالي بتاريخ اليوم">
                    📋 تكرار القيد
                </button>
                <button onclick="runAIJournalValidator()" class="jv-top-btn jv-btn-sky" title="فحص وتدقيق القيد بالذكاء الاصطناعي">
                    🤖 تدقيق القيد (AI)
                </button>
                <a href="general-ledger.php" class="jv-top-btn" title="فتح دفتر الأستاذ العام الموحد">
                    📘 الأستاذ العام
                </a>
                <a href="posting-accounts.php" class="jv-top-btn" title="شجرة ودليل الحسابات">
                    🌳 دليل الحسابات
                </a>
                <div id="jv-user-audit-container" style="display:inline-flex; align-items:center;"></div>
            </div>
        </div>

        <!-- 8 Smart Accounting Templates Ribbon -->
        <div class="jv-templates-bar no-print">
            <span style="font-weight:900; color:#c084fc; font-size:0.82rem;">⚡ قوالب القيود الذكية الجاهزة:</span>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('expense_settlement')">💼 تسوية عهدة ومصروفات</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('partner_offset')">🔄 مقاصة بين عميل ومورد</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('ifrs9_provision')">🚨 مخصص ديون مشكوك فيها (IFRS-9)</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('depreciation')">📉 قيد إهلاك أصول ثابتة</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('payroll')">👥 استحقاق رواتب وأجور</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('fx_revaluation')">💱 تسوية فروق أسعار صرف</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('inventory_diff')">📦 تسوية فروق جرد مخزني</button>
            <button type="button" class="jv-tpl-chip" onclick="applyJournalTemplate('opening_entry')">🏛️ قيد افتتاحي للأرصدة</button>
        </div>

        <!-- MAIN FORM -->
        <div class="glass-panel" style="padding: 22px; border-radius: 18px;">

            <!-- STICKY BALANCE BOX -->
            <div class="balance-box">
                <div class="bal-stat">
                    <span class="lbl">🟢 إجمالي المدين (Debit SDG)</span>
                    <span class="val val-debit" id="tot-debit">0.00</span>
                </div>
                <div class="bal-stat">
                    <span class="lbl">🔴 إجمالي الدائن (Credit SDG)</span>
                    <span class="val val-credit" id="tot-credit">0.00</span>
                </div>
                <div class="bal-stat">
                    <span class="lbl">⚖️ الفرق (Difference)</span>
                    <span class="val val-diff" id="tot-diff">0.00</span>
                </div>
                <div class="bal-stat">
                    <span class="lbl">🛡️ حالة القيد المحاسبي</span>
                    <span id="jv-balance-status-badge" style="font-weight:900; font-size:0.86rem; color:#fbbf24;">⏳ بانتظار إدخال الأطراف</span>
                </div>
            </div>

            <!-- SECTION 1: HEADER INFO -->
            <div style="margin-bottom: 20px;">
                <div class="form-section-title">
                    <span>📋 البيانات الأساسية لسند القيد</span>
                    <span style="font-size:0.78rem; color:#94a3b8; font-weight:700;">يرتبط القيد آلياً بدفتر الأستاذ العام وميزان المراجعة وسجل الرقابة</span>
                </div>
                <div class="form-grid">
                    <div>
                        <label class="label" style="font-size:0.78rem; font-weight:800; color:#94a3b8; display:block; margin-bottom:5px;">📅 تاريخ القيد</label>
                        <input type="date" class="form-input" id="hdr-date">
                    </div>
                    <div>
                        <label class="label" style="font-size:0.78rem; font-weight:800; color:#94a3b8; display:block; margin-bottom:5px;">📂 نوع القيد المحاسبي</label>
                        <select class="form-input" id="hdr-jv-type">
                            <option value="قيد يومية عام">📝 قيد يومية عام (General Entry)</option>
                            <option value="قيد تسوية محاسبية">⚖️ قيد تسوية محاسبية (Adjustment)</option>
                            <option value="قيد مقاصة شركاء">🔄 قيد مقاصة بين الحسابات (Clearing)</option>
                            <option value="قيد استحقاق ومخصصات">🚨 قيد استحقاق / مخصصات (Accrual)</option>
                            <option value="قيد عكسي">↩️ قيد عكسي / تصحيحي (Reversal)</option>
                            <option value="قيد افتتاحي">🏛️ قيد افتتاحي (Opening Balance)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" style="font-size:0.78rem; font-weight:800; color:#94a3b8; display:block; margin-bottom:5px;">🔖 المرجع / رقم المستند المرفق</label>
                        <input type="text" class="form-input" id="hdr-ref" placeholder="مثال: تسوية جرد رقم 10 أو مستند رقم...">
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="label" style="font-size:0.78rem; font-weight:800; color:#94a3b8; display:block; margin-bottom:5px;">✍️ البيان العام (الشرح المحاسبي للقيد)</label>
                        <input type="text" class="form-input" id="hdr-statement" placeholder="اكتب شرحاً محاسبياً واضحاً لسبب تسجيل القيد...">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: JV GRID -->
            <div style="margin-bottom: 20px; overflow-x: auto;">
                <div class="form-section-title">
                    <span>📊 جدول أطراف القيد المزدوج (المدين والدائن والعملات ومراكز التكلفة)</span>
                    <div class="no-print" style="display:flex; gap:8px;">
                        <button type="button" onclick="addGridRow()" class="jv-top-btn jv-btn-purple" style="padding:5px 11px; font-size:0.76rem;">➕ إضافة سطر (Alt+N)</button>
                        <button type="button" onclick="autoBalanceJournal()" class="jv-top-btn jv-btn-emerald" style="padding:5px 11px; font-size:0.76rem;">⚡ موازنة الفرق تلقائياً</button>
                    </div>
                </div>
                <table class="jv-table" id="jv-table">
                    <thead>
                        <tr>
                            <th style="width: 24%;">الحساب المحاسبي (Account)</th>
                            <th style="width: 13%;">مدين (Debit)</th>
                            <th style="width: 13%;">دائن (Credit)</th>
                            <th style="width: 9%;">العملة</th>
                            <th style="width: 9%;">سعر الصرف</th>
                            <th style="width: 12%;">مركز التكلفة / المشروع</th>
                            <th style="width: 16%;">بيان السطر (Line Note)</th>
                            <th style="width: 4%;" class="no-print">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="jv-tbody">
                        <!-- Rows injected via JS -->
                    </tbody>
                </table>
                <button class="btn-add-row no-print" onclick="addGridRow()">+ إضافة سطر جديد (طرف مدين أو دائن للقيد)</button>
            </div>

            <!-- AI Validator Result Box -->
            <div id="jv-ai-validator-box" style="display:none; margin-top:14px; padding:14px 18px; border-radius:14px; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.35); color:#f8fafc; font-size:0.86rem; line-height:1.7;"></div>

            <!-- Official Print Signatures & Audit Footer -->
            <div class="jv-print-audit-footer" style="display:none;">
                <div style="width:100%;">
                    <div style="display:flex; justify-content:space-around; margin-bottom:25px; text-align:center; font-weight:800;">
                        <div>إعداد المحاسب<br><br>____________________</div>
                        <div>المراجعة والتدقيق<br><br>____________________</div>
                        <div>اعتماد المدير المالي<br><br>____________________</div>
                    </div>
                    <div style="display:flex; justify-content:space-between; border-top:1px dashed #64748b; padding-top:8px;">
                        <span>👤 معد القيد: <strong id="jv-print-creator">—</strong> | طُبع بواسطة: <strong id="jv-print-printer">—</strong> | التوقيت: <span id="jv-print-time">—</span></span>
                        <span style="font-weight: 800; color: #7c3aed;">نظام السيطرة المحاسبي الموحد ⚡</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ==================================================================== -->
        <!-- SECTION 3: INTEGRATED JOURNAL REGISTER & ARCHIVE (دمج دفتر اليومية) -->
        <!-- ==================================================================== -->
        <div class="glass-panel no-print" id="jv-archive-section" style="padding: 20px; border-radius: 18px; margin-top: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:12px;">
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:900; color:#38bdf8; display:flex; align-items:center; gap:8px;">
                        📜 سجل ودفتر القيود اليومية المعتمدة (Integrated Journal Register)
                    </h3>
                    <p style="margin:3px 0 0 0; font-size:0.78rem; color:#94a3b8;">اضغط على أي قيد لاستعراضه أو تعديله أو طباعته أو عكسه بضغطة زر</p>
                </div>

                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="text" id="jv-archive-search" class="form-input" style="width:240px; padding:7px 12px; font-size:0.82rem;" placeholder="🔍 ابحث برقم القيد، البيان، أو الحساب..." oninput="renderJournalArchiveTable()">
                    <input type="date" id="jv-archive-from" class="form-input" style="width:140px; padding:6px 10px; font-size:0.8rem;" onchange="renderJournalArchiveTable()" title="من تاريخ">
                    <input type="date" id="jv-archive-to" class="form-input" style="width:140px; padding:6px 10px; font-size:0.8rem;" onchange="renderJournalArchiveTable()" title="إلى تاريخ">
                    <button onclick="exportJournalRegisterExcel()" class="jv-top-btn jv-btn-emerald" style="padding:7px 12px; font-size:0.78rem;">📥 تصدير السجل Excel</button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="jv-archive-table">
                    <thead>
                        <tr>
                            <th>رقم القيد</th>
                            <th>التاريخ</th>
                            <th>نوع القيد</th>
                            <th>المرجع</th>
                            <th>البيان العام</th>
                            <th>عدد الأطراف</th>
                            <th>إجمالي القيد (SDG)</th>
                            <th>المستخدم المنفذ</th>
                            <th>إجراءات فورية</th>
                        </tr>
                    </thead>
                    <tbody id="jv-archive-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- STICKY FOOTER -->
        <div class="sticky-footer no-print" style="background: rgba(15,23,42,0.96); border-top: 1.5px solid rgba(192,132,252,0.35); border-radius: 14px 14px 0 0; padding: 12px 20px; position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;">
            <div class="footer-actions" style="width: 100%; justify-content: center; max-width: 1300px; margin: 0 auto; gap: 10px; display: flex; flex-wrap: wrap;">
                <button class="btn btn-ghost" style="color:#f87171; border-color:rgba(239,68,68,0.3);" onclick="if(document.referrer) { window.history.back(); } else { window.location.href='magaiees_unified_dashboard.php'; }">🚪 خروج</button>
                <button class="btn btn-ghost" onclick="resetVoucher()">🆕 قيد جديد</button>
                <button class="btn btn-ghost" style="color:#34d399; border-color:rgba(16,185,129,0.35);" onclick="autoBalanceJournal()">⚡ موازنة القيد</button>
                <button class="btn btn-ghost" style="color:#fbbf24; border-color:rgba(245,158,11,0.35);" onclick="reverseCurrentJournal()">🔄 عكس القيد</button>
                <button class="btn btn-ghost" style="color:#25d366; border-color:rgba(37,211,102,0.35);" onclick="shareJournalWhatsApp()">📲 مشاركة واتساب</button>
                <button class="btn btn-ghost" style="color: #f87171; border-color: rgba(239,68,68,0.3);" onclick="confirmDelete()">🗑️ حذف القيد</button>
                <button class="btn btn-ghost" onclick="printVoucher()">🖨️ طباعة رسمية</button>
                <button class="btn btn-success" id="btn-save" style="background: linear-gradient(135deg, #a855f7, #7c3aed); border-color: #c084fc; min-width: 210px; font-weight:900;" onclick="submitVoucher()">✓ حفظ واعتماد وترحيل القيد (Ctrl+S)</button>
            </div>
        </div>
    </div>

    <!-- Edit Warning Modal -->
    <div id="edit-warning-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(15px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); border: 2px solid #f87171; border-radius: 20px; padding: 35px; width: 90%; max-width: 560px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
            <div style="font-size: 4rem; margin-bottom: 15px;">⚠️</div>
            <h2 style="color: #ffffff; font-size: 1.8rem; font-weight: 900; margin: 0 0 12px 0;">تنبيه: تعديل قيد مرحّل مسبقاً</h2>
            <p style="color: #fecaca; font-size: 1.05rem; line-height: 1.6; margin-bottom: 28px;">أنت تقوم الآن بتعديل قيد يومية معتمد. سيتم تحديث دفتر الأستاذ العام وسجل الرقابة الجنائي تلقائياً.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="confirmEditOldVoucher()" style="background: #ef4444; color: white; font-size: 1.1rem; font-weight: 900; padding: 12px 32px; border: none; border-radius: 12px; cursor: pointer;">نعم، اعتمد التعديل</button>
                <button onclick="cancelEditOldVoucher()" style="background: rgba(255,255,255,0.1); color: #ffffff; font-size: 1.1rem; font-weight: 900; padding: 12px 28px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; cursor: pointer;">تراجع ❌</button>
            </div>
        </div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script defer src="js/journal-voucher.js?v=<?= time() ?>"></script>
    <script>
    let hasWarnedAboutEdit = false;

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (typeof window.initJVApp === 'function' && !window._jvAppInitialized) {
                window.initJVApp();
            }
            const originalSubmit = window.submitVoucher;
            if (typeof originalSubmit === 'function') {
                window.submitVoucher = function() {
                    if (typeof currentViewedIndex !== 'undefined' && currentViewedIndex !== -1 && !hasWarnedAboutEdit) {
                        const modal = document.getElementById('edit-warning-modal');
                        modal.style.display = 'flex';
                        setTimeout(() => { modal.style.opacity = '1'; }, 10);
                        return;
                    }
                    hasWarnedAboutEdit = false;
                    originalSubmit.apply(this, arguments);
                };
            }
        }, 250);
    });

    function confirmEditOldVoucher() {
        hasWarnedAboutEdit = true;
        const modal = document.getElementById('edit-warning-modal');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            window.submitVoucher();
        }, 250);
    }

    function cancelEditOldVoucher() {
        const modal = document.getElementById('edit-warning-modal');
        modal.style.opacity = '0';
        setTimeout(() => { modal.style.display = 'none'; }, 250);
    }
    </script>
    <script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
    <script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
