<?php $page_title = 'تقرير الأرصدة الإجمالية'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الأرصدة الإجمالية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
    <link rel="stylesheet" href="css/sales-invoice.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .toast-container { position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
        .toast { padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.85rem; display:flex; align-items:center; gap:8px; pointer-events:auto; backdrop-filter:blur(20px); box-shadow:0 12px 40px rgba(0,0,0,0.5); animation:toastIn 0.4s cubic-bezier(0.16,1,0.3,1) both; font-family:'Cairo',sans-serif; }
        .toast.success { background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.4); color:#34d399; }
        .toast.warning { background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; }
        @keyframes toastIn { from { opacity:0; transform:translateY(-20px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

        /* Report Header */
        .report-header { background: linear-gradient(135deg, rgba(56,189,248,0.15), rgba(15,23,42,0.9)); border: 1px solid rgba(56,189,248,0.2); border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-group label { display: block; color: #94a3b8; font-size: 0.8rem; margin-bottom: 5px; font-weight: bold; }
        .filter-input { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #f8fafc; padding: 10px 12px; border-radius: 8px; font-family: inherit; font-size: 0.95rem; transition: all 0.2s; }
        .filter-input:focus { border-color: #38bdf8; outline: none; background: rgba(0,0,0,0.5); }

        .badge-sgl { background: #38bdf8; color: #0f172a; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: bold; letter-spacing: 1px; }
        .badge-active { background: rgba(52,211,153,0.15); color: #34d399; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; border: 1px solid rgba(52,211,153,0.3); }

        .btn-run { background: #38bdf8; color: #0f172a; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; font-family: inherit; font-size: 0.95rem; }
        .btn-run:hover { background: #0ea5e9; transform: translateY(-1px); }
        .btn-action { background: rgba(255,255,255,0.05); color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-family: inherit; }
        .btn-action:hover { background: rgba(255,255,255,0.1); }

        .audit-stamp {
            margin-top: 20px; padding: 15px; background: rgba(56,189,248,0.05);
            border: 1px dashed rgba(56,189,248,0.3); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            color: #38bdf8; font-weight: bold; font-size: 0.9rem;
        }

        /* Dynamic Enterprise Variables & Clickable Numbers */
        #v-classic-ent { background: #fff; padding: 40px; border-radius: 8px; color: #111827; }
        #v-classic-ent table { --fs-text: 1.15rem; --fs-num: 1.15rem; --font-num: 'JetBrains Mono', monospace; transition: all 0.3s; width: 100%; table-layout: fixed; border-collapse: collapse; text-align: right; border: 4px solid #000000; }
        #v-classic-ent th { position: relative; background: #e2e8f0; color: #000000; padding: 10px 12px; border: 3px solid #111827; line-height: 1.2; font-size: calc(var(--fs-text) * 1.1); font-weight: 900; text-align: center; }
        #v-classic-ent td { padding: 5px 12px; border: 2px solid #475569; color: #000000; font-weight: 600; line-height: 1.3; font-size: var(--fs-text); text-align: center; }
        #v-classic-ent tr:nth-child(even) { background: #f9fafb; }
        
        #v-classic-ent td.num { 
            text-align: center;
            font-size: var(--fs-num); 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            font-family: var(--font-num);
            font-weight: 600; color: #111827;
            letter-spacing: 0px;
            position: relative;
            cursor: pointer;
        }
        #v-classic-ent td.num:hover {
            transform: scale(1.05);
            color: #38bdf8;
        }

        .resizer {
            position: absolute;
            left: -4px;
            top: 0;
            bottom: 0;
            width: 10px;
            cursor: col-resize;
            background: transparent;
            z-index: 10;
        }
        .resizer:hover, .resizer.resizing {
            background: #38bdf8;
            opacity: 0.8;
        }
        
        /* The Focused Specific Number Feature */
        .focused-num {
            transform: scale(1.6) translateY(-2px) !important;
            color: #8b5cf6 !important;
            font-weight: 900 !important;
            background: #f3e8ff !important;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4), inset 0 0 0 2px #8b5cf6 !important;
            z-index: 50;
            display: inline-block;
            padding: 0 8px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            #v-classic-ent { padding: 0 !important; }
            #print-container { background: #fff !important; }
        }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

<div class="toast-container" id="toast-container"></div>

<div id="columnContextMenu" style="display:none; position:absolute; z-index:10000; background:#fff; border:2px solid #111827; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.5); padding:15px; width:300px; color:#000; max-height: 85vh; overflow-y: auto;">
    <div style="font-weight:900; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:10px; text-align:center; font-size:1.05rem;">⚙️ إعدادات الأعمدة</div>
    <div id="columnToggles"></div>
    <button onclick="saveLayout()" style="width: 100%; margin-top: 15px; padding: 10px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 6px; font-weight: bold; font-family:'Tajawal', sans-serif; cursor: pointer; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">💾 حفظ التصميم</button>
</div>

<!-- Custom Context Menu for Partner Rows -->
<div id="partnerContextMenu" style="display:none; position:absolute; z-index:10000; background:rgba(15, 23, 42, 0.95); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.5); padding:8px; width:220px; color:#f8fafc; font-family:'Tajawal', sans-serif;">
    <div id="partnerMenuHeader" style="font-weight:bold; font-size:0.85rem; color:#94a3b8; padding:8px 12px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">اسم الحساب</div>
    <div id="gotoLedgerItem" style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; cursor:pointer; font-weight:bold; font-size:0.9rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.15)'; this.style.color='#38bdf8';" onmouseout="this.style.background='transparent'; this.style.color='#f8fafc';">
        <span style="font-size:1.1rem;">📖</span>
        <span>الذهاب إلى دفتر الأستاذ</span>
    </div>
</div>


<div style="max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 80px;">
    <!-- Premium Navigation Header -->
    <?php include 'includes/header.php'; ?>

    <!-- FILTERS -->
    <div class="report-header no-print" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 25px; margin-bottom: 30px;">
        <div class="filter-row">
            <div class="filter-group">
                <label>نوع المجموعة (Group Type)</label>
                <select class="filter-input" id="filter-group">
                    <option value="ALL">كل الأرصدة الإجمالية</option>
                    <option value="CUST">العملاء (مدين)</option>
                    <option value="SUPP">الموردين (دائن)</option>
                    <option value="BANK">البنوك</option>
                    <option value="SAFE">الخزن</option>
                    <option value="EXP">المصروفات والحسابات</option>
                </select>
            </div>
            <div class="filter-group">
                <label>الترتيب (Sort By)</label>
                <select class="filter-input" id="filter-sort">
                    <option value="BALANCE_DESC">الرصيد (الأعلى للأسفل)</option>
                    <option value="BALANCE_ASC">الرصيد (الأدنى للأعلى)</option>
                    <option value="NAME">اسم الحساب (أبجدي)</option>
                    <option value="LAST_DATE">تاريخ آخر حركة (الأحدث)</option>
                </select>
            </div>
            <div class="filter-group">
                <label>مركز التكلفة (Cost Center)</label>
                <select class="filter-input" id="filter-costcenter">
                    <option value="ALL">جميع مراكز التكلفة (الكل)</option>
                </select>
            </div>
        </div>
        <div style="display:flex; gap:12px; margin-top:10px; align-items:center; flex-wrap:wrap;">
            <button class="btn-run" onclick="runReport()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                تشغيل التقرير
            </button>
        </div>
    </div>

    <!-- KPI Dashboard -->
    <div id="analytics-dashboard" class="no-print" style="display:none; margin-bottom: 30px; padding: 25px; background: rgba(15, 23, 42, 0.6); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h3 style="margin: 0; color: #f8fafc; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">📈</span> لوحة أرصدة الحسابات الإجمالية
            </h3>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            <div style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(16,185,129,0.2); text-align: center;">
                <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">إجمالي رصيد العملاء (عليه)</div>
                <div id="kpi-dr" class="num" style="color: #10b981; font-size: 1.5rem; font-weight: 900;">0.00</div>
            </div>
            <div style="background: linear-gradient(135deg, rgba(244,63,94,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(244,63,94,0.2); text-align: center;">
                <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">إجمالي رصيد الموردين (له)</div>
                <div id="kpi-cr" class="num" style="color: #f43f5e; font-size: 1.5rem; font-weight: 900;">0.00</div>
            </div>
            <div style="background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(0,0,0,0.3)); border-radius: 12px; padding: 15px; border: 1px solid rgba(56,189,248,0.2); text-align: center;">
                <div style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 5px; font-weight: bold;">إجمالي الحصيلة الصافية</div>
                <div id="kpi-bal" class="num" style="color: #38bdf8; font-size: 1.5rem; font-weight: 900;">0.00</div>
            </div>
        </div>
    </div>

    <!-- Actions Panel -->
    <div class="actions-panel no-print" id="actions-panel" style="display:none; flex-wrap: wrap; margin-bottom: 20px;">
        <button class="btn-action btn-secondary" onclick="window.print()" title="طباعة">🖨️ طباعة وتوثيق</button>
        <button class="btn-action btn-primary" onclick="exportPDF()" title="تصدير PDF">📄 تصدير PDF</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;" onclick="exportExcel()" title="تصدير إكسل">📊 إكسل (Excel)</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; border: none;" onclick="shareEmail()" title="مشاركة عبر الإيميل">📧 إيميل</button>
        <button class="btn-action" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none;" onclick="shareWhatsApp()" title="مشاركة عبر واتساب">💬 واتساب</button>
    </div>

    <!-- TABLE -->
    <div id="print-container" style="display:none; padding: 20px; border-radius: 16px; background: rgba(255,255,255,0.02);">
        
        <div id="v-classic-ent">
            
            <div class="report-header" style="display: flex; justify-content: space-between; align-items: flex-start; height: 4cm; border-bottom: 3px solid #111827; margin-bottom: 20px; padding-bottom: 15px; font-family: 'Tajawal', sans-serif;">
                
                <div style="flex: 1; text-align: right; color: #111827;">
                    <h2 style="margin: 0 0 5px 0; font-size: 1.6rem; font-weight: 900;">شركة الأفق للتجارة</h2>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">سجل تجاري: 1010101010</p>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرقم الضريبي: 300000000000003</p>
                    <p style="margin: 0 0 3px 0; font-size: 0.95rem; font-weight: 600;">الرياض، المملكة العربية السعودية</p>
                </div>

                <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                    <h2 style="margin: 0; padding: 10px 20px; background: #f8fafc; border-radius: 8px; border: 2px solid #111827; font-size: 1.4rem; color: #111827;">تقرير الأرصدة الإجمالية</h2>
                </div>

                <div style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
                    <div style="width: 4cm; height: 100%; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: #f8fafc; overflow: hidden; position: relative;" title="منطقة الشعار - يتم جلب الشعار من البيانات الأساسية">
                        <img id="company-logo" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgZmlsbD0iI2UydThmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1zaXplPSIyMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY0NzQ4YiI+TE9HTzwvdGV4dD48L3N2Zz4=" alt="شعار الشركة" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                    </div>
                </div>
            </div>

            <table id="balances-table">
                <thead>
                    <tr id="balances-header-row">
                        <th data-orig-idx="0">اسم الحساب<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="1">رقم الهاتف<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="2">الرقم الضريبي<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="3">العملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="4">عليه عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="5">له عملة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="6">عليه<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="7">له<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                        <th data-orig-idx="8">ملحوظة<div class="resizer" title="اسحب لتغيير عرض العمود (Double click للإرجاع)"></div></th>
                    </tr>
                </thead>
                <tbody id="balances-tbody">
                    <tr>
                        <td colspan="11" class="empty-state">
                            <span style="font-size:3rem; display:block; margin-bottom:15px;">🔍</span>
                            <div>إضغط "تشغيل التقرير" لجلب جميع الأرصدة</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="audit-stamp">
                <span>✔️</span>
                <span>ENCRYPTED_DATA_LOCK: البيانات الواردة نهائية وتم استخراجها من السجلات المالية المشفرة للنظام.</span>
            </div>

        </div>

    </div>

</div>

<!-- Include html2pdf library to export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script src="js/localforage.min.js"></script>
<script src="js/app.js?v=1783054966.35444"></script>
<script src="js/balances-report.js"></script>
</body>
</html>
