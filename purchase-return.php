<?php $page_title = 'مردودات المشتريات'; ?>
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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مردودات المشتريات | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        :root {
            --bg-color: #0d0609;
            --return-primary: #f43f5e;
            --return-hover: #e11d48;
            --return-glow: rgba(244, 63, 94, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            background-color: var(--bg-color);
            color: #f1f5f9;
            font-family: 'Tajawal', sans-serif;
            margin: 0; padding: 0;
            overflow-x: hidden;
            display: flex;
            height: 100vh;
        }

        body.has-sidebar { padding-right: 0 !important; }

        /* Ambient Animated Background for Returns (Red/Pink tones) */
        .ambient-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            z-index: -1; overflow: hidden; pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #150912 0%, #000000 100%);
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px);
            animation: floatOrb 20s infinite alternate ease-in-out; opacity: 0.15;
        }
        .orb-1 { width: 600px; height: 600px; background: #f43f5e; top: -10%; right: -10%; }
        .orb-2 { width: 500px; height: 500px; background: #9f1239; bottom: -10%; left: -10%; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #e11d48; top: 40%; left: 40%; animation-delay: -10s; opacity: 0.1;}

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -50px) scale(1.1); }
        }

        .dash-main {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
        }

        /* Premium Header */
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        
        .premium-header {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.15), rgba(159, 18, 57, 0.15), rgba(225, 29, 72, 0.15));
            background-size: 300% 300%;
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding: 12px 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden; flex-shrink: 0;
            margin: 15px; border-radius: 20px;
        }

        .header-brand { display: flex; align-items: center; gap: 15px; }
        .brand-icon {
            width: 50px; height: 50px; border-radius: 16px;
            background: linear-gradient(135deg, #f43f5e, #be123c);
            display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
            box-shadow: 0 8px 20px rgba(244, 63, 94, 0.4), inset 0 2px 5px rgba(255,255,255,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .brand-text h1 { margin: 0; font-size: 1.4rem; font-weight: 900; color: #fff; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(255,255,255,0.1); }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #fda4af; font-weight: 500; }
        
        .header-controls { display: flex; align-items: center; gap: 15px; }
        .icon-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 46px; height: 46px; border-radius: 14px; color: #fff; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; }
        .icon-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }

        /* Container Views */
        .view-container {
            display: none; flex-direction: column; height: 100%; flex: 1; padding: 0 15px 15px 15px; overflow-y: auto;
            animation: fadeIn 0.5s ease backwards;
        }
        .view-container.active { display: flex; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Hub View (Invoice Selection) */
        .neo-card {
            background: rgba(18, 15, 25, 0.65); border: 1px solid var(--glass-border); border-radius: 20px;
            padding: 20px; display: flex; flex-direction: column; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); flex: 1;
        }
        .neo-card-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

        .v-input {
            width: 100%; padding: 10px 15px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px; color: #fff; font-size: 0.9rem; outline: none; transition: 0.3s; font-family: 'Outfit', 'Tajawal', sans-serif;
        }
        .v-input:focus { border-color: var(--return-primary); box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.15); }

        /* Data Grid */
        .grid-scroll-wrap { flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px; }
        .grid-scroll-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
        .grid-scroll-wrap::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 6px; }
        
        .pos-grid { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 900px; }
        .pos-grid thead th {
            background: rgba(255,255,255,0.02); color: #94a3b8; font-size: 0.85rem; font-weight: 800;
            padding: 12px 15px; text-align: center; position: sticky; top: 0; z-index: 5;
            border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .pos-grid tbody tr {
            background: rgba(0,0,0,0.2); transition: all 0.3s ease;
        }
        .pos-grid tbody tr:hover {
            background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-grid tbody td {
            padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.02);
            font-size: 0.9rem; vertical-align: middle;
        }
        .pos-grid tbody td:first-child { border-radius: 12px 0 0 12px; border-right: 1px solid rgba(255,255,255,0.02); }
        .pos-grid tbody td:last-child { border-radius: 0 12px 12px 0; border-left: 1px solid rgba(255,255,255,0.02); }

        .btn-action {
            background: linear-gradient(135deg, var(--return-primary), var(--return-hover)); color: #fff;
            border: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; cursor: pointer;
            transition: 0.3s; box-shadow: 0 4px 15px var(--return-glow); font-family: 'Tajawal', sans-serif;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--return-glow); }
        .btn-cancel { background: rgba(255,255,255,0.05); box-shadow: none; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-cancel:hover { background: rgba(255,255,255,0.1); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-dim); padding: 40px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; filter: drop-shadow(0 0 10px rgba(244,63,94,0.3)); }

        /* Return Editor View */
        .editor-layout { display: flex; gap: 20px; height: 100%; }
        .editor-left { flex: 1; display: flex; flex-direction: column; background: rgba(18, 15, 25, 0.65); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .editor-right { width: 350px; display: flex; flex-direction: column; gap: 20px; }
        
        .info-card { background: rgba(0,0,0,0.4); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); padding: 20px; }
        .info-card h3 { margin: 0 0 15px 0; color: #fff; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; }
        .info-lbl { color: #94a3b8; }
        .info-val { color: #fff; font-weight: 800; font-family: 'Outfit', sans-serif; direction: ltr; }
        
        .return-input {
            width: 80px; text-align: center; background: rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.1);
            color: #fff; padding: 10px; border-radius: 10px; font-weight: 800; font-size: 1.1rem; font-family: 'Outfit', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); outline: none;
        }
        .return-input:focus { border-color: #10b981; box-shadow: 0 0 15px rgba(16,185,129,0.3); background: rgba(16,185,129,0.1); transform: scale(1.05); }
        .return-input.error { border-color: #ef4444; box-shadow: 0 0 15px rgba(239,68,68,0.3); background: rgba(239,68,68,0.1); }

        .tot-grand {
            margin-top: 15px; background: linear-gradient(135deg, rgba(244,63,94,0.15), rgba(159,18,57,0.15)); border: 1px solid rgba(244,63,94,0.4);
            border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .tot-grand .lbl { color: #fff; font-weight: 800; font-size: 1.1rem; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
        .tot-grand .val { color: #fff; font-weight: 900; font-size: 1.8rem; direction: ltr; text-shadow: 0 0 20px rgba(244,63,94,0.6); font-family: 'Outfit', sans-serif; }
        
        .refund-options { display: flex; flex-direction: column; gap: 10px; margin-top: 15px; }
        .refund-btn { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; padding: 10px; border-radius: 10px; cursor: pointer; transition: 0.3s; font-weight: 700; font-family: 'Tajawal'; display: flex; align-items: center; gap: 10px; }
        .refund-btn.active { background: rgba(16,185,129,0.15); border-color: #10b981; color: #10b981; box-shadow: 0 4px 15px rgba(16,185,129,0.2); }
        .refund-select-wrapper { display: none; margin-top: 5px; animation: fadeIn 0.3s ease; }
        .refund-select-wrapper.active { display: block; }
        .v-select { width: 100%; padding: 10px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-family: 'Tajawal'; outline: none; }


    
        
        /* Universal Light Theme Overrides for Internal Screens */
        :root.light-theme body,
        :root.light-theme .ambient-bg {
            background: #f8fafc !important;
        }
        :root.light-theme .glass-panel,
        :root.light-theme .neo-card,
        :root.light-theme .summary-card,
        :root.light-theme .filter-panel,
        :root.light-theme .chart-card,
        :root.light-theme .kpi-card,
        :root.light-theme .premium-header,
        :root.light-theme .top-header,
        :root.light-theme .header-area {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            color: #0f172a !important;
        }
        :root.light-theme .glass-panel *,
        :root.light-theme .neo-card *,
        :root.light-theme .summary-card * {
            text-shadow: none !important;
        }
        :root.light-theme table.pro-table th,
        :root.light-theme .pos-grid thead th,
        :root.light-theme .ledger-table th,
        :root.light-theme .report-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        :root.light-theme table.pro-table td,
        :root.light-theme .pos-grid tbody td,
        :root.light-theme .ledger-table td,
        :root.light-theme .report-table td {
            color: #1e293b !important;
            border-bottom: 1px solid rgba(0,0,0,0.08) !important;
        }
        :root.light-theme table.pro-table tbody tr:hover td,
        :root.light-theme .pos-grid tbody tr:hover td,
        :root.light-theme .ledger-table tbody tr:hover td,
        :root.light-theme .report-table tbody tr:hover td {
            background: rgba(0,0,0,0.02) !important;
        }
        :root.light-theme .h-label,
        :root.light-theme .form-label,
        :root.light-theme .tot-lbl,
        :root.light-theme .lbl,
        :root.light-theme label,
        :root.light-theme .table-header {
            color: #334155 !important;
        }
        :root.light-theme .tot-val,
        :root.light-theme .val {
            color: #0f172a !important;
        }
        :root.light-theme .h-form-group,
        :root.light-theme .control-panel {
            background: transparent !important;
            border-color: rgba(0,0,0,0.08) !important;
        }
        :root.light-theme .modern-btn {
            color: #1e293b !important;
        }
        :root.light-theme .modern-btn:hover {
            color: #0f172a !important;
        }
        /* Neon text colors correction for light theme */
        :root.light-theme .text-emerald-400, :root.light-theme span[style*="color: #34d399"], :root.light-theme span[style*="color:#34d399"] { color: #059669 !important; }
        :root.light-theme .text-rose-400, :root.light-theme span[style*="color: #f87171"], :root.light-theme span[style*="color:#f87171"] { color: #e11d48 !important; }
        :root.light-theme .text-sky-400, :root.light-theme span[style*="color: #38bdf8"], :root.light-theme span[style*="color:#38bdf8"] { color: #0284c7 !important; }
        :root.light-theme .text-purple-400, :root.light-theme span[style*="color: #c084fc"], :root.light-theme span[style*="color:#c084fc"] { color: #7c3aed !important; }
        :root.light-theme .text-amber-400, :root.light-theme span[style*="color: #fbbf24"], :root.light-theme span[style*="color:#fbbf24"] { color: #b45309 !important; }
\n</style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="dash-main">
        <?php $header_theme_class = 'header-theme-purchase'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- View 1: Hub (Select Invoice) -->
        <div class="view-container active" id="view-hub">
            <div class="neo-card">
                <div class="neo-card-title">
                    <span>📄 فواتير المشتريات المكتملة</span>
                    <input type="text" id="search-inv" class="v-input" placeholder="ابحث برقم الفاتورة أو المورد..." style="width: 300px;" oninput="renderInvoices()">
                </div>
                
                <div class="grid-scroll-wrap">
                    <table class="pos-grid">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم الفاتورة</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">المورد</th>
                                <th style="text-align:center;">إجمالي الفاتورة</th>
                                <th style="text-align:center;">حالة المردودات</th>
                                <th style="text-align:center;">إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="invoices-tbody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                    <div class="empty-state" id="empty-hub" style="display: none;">
                        <div class="icon">📭</div>
                        <h2>لا توجد فواتير مشتريات مطابقة</h2>
                        <p>تأكد من وجود فواتير سابقة ومكتملة للتمكن من عمل استرداد بضاعة.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- View 2: Editor (Return Items) -->
        <div class="view-container" id="view-editor">
            <div class="editor-layout">
                <!-- Left: Items Table -->
                <div class="editor-left">
                    <div class="neo-card-title">
                        <span>📦 أصناف الفاتورة: <span id="lbl-inv-no" style="color:var(--return-primary); font-family:'Outfit';"></span></span>
                        <button class="btn-action btn-cancel" style="padding: 6px 12px; font-size: 0.8rem;" onclick="showHub()">إلغاء التحديد</button>
                    </div>
                    
                    <div class="grid-scroll-wrap" style="flex:1;">
                        <table class="pos-grid">
                            <thead>
                                <tr>
                                    <th style="text-align:right; width:35%;">الصنف</th>
                                    <th style="text-align:center;">الوحدة</th>
                                    <th style="text-align:center;">الكمية المشتراة</th>
                                    <th style="text-align:center;">تم إرجاعه مسبقاً</th>
                                    <th style="text-align:center; color:#f43f5e;">الكمية للإرجاع (الآن)</th>
                                    <th style="text-align:center;">السعر (فردي)</th>
                                    <th style="text-align:center;">إجمالي الإرجاع</th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Totals & Save -->
                <div class="editor-right">
                    <div class="info-card">
                        <h3>تفاصيل المورد</h3>
                        <div class="info-row"><span class="info-lbl">المورد:</span> <span class="info-val" id="lbl-supplier" style="direction:rtl;">-</span></div>
                        <div class="info-row"><span class="info-lbl">تاريخ الشراء:</span> <span class="info-val" id="lbl-date">-</span></div>
                        <div class="info-row"><span class="info-lbl">المستودع الأصلي:</span> <span class="info-val" id="lbl-warehouse" style="direction:rtl;">-</span></div>
                    </div>

                    <div class="info-card" style="flex:1; overflow-y:auto;">
                        <h3>ملخص المردودات</h3>
                        <div class="info-row"><span class="info-lbl">إجمالي قيمة المردودات:</span> <span class="info-val" id="lbl-subtotal">0.00</span></div>
                        <div class="info-row"><span class="info-lbl">الضريبة المرتجعة:</span> <span class="info-val" id="lbl-tax">0.00</span></div>
                        <div class="info-row"><span class="info-lbl">الخصم المسترد:</span> <span class="info-val" id="lbl-discount">0.00</span></div>
                        
                        <div class="tot-grand">
                            <span class="lbl">الصافي للمورد</span>
                            <span class="val" id="lbl-grand-total">0.00</span>
                        </div>
                        
                        <h3 style="margin-top: 20px;">سبب الاسترجاع</h3>
                        <input type="text" id="return-reason" class="v-input" placeholder="مثال: أصناف تالفة، غير مطابقة..." style="width: 100%;">
                        
                        <h3 style="margin-top: 20px;">طريقة الاسترداد <span style="color:#f43f5e;">*</span></h3>
                        <div class="refund-options">
                            <button class="refund-btn active" id="btn-refund-supplier" onclick="setRefundMethod('supplier')">
                                <span>📉</span> خصم من رصيد المورد
                            </button>
                            <button class="refund-btn" id="btn-refund-cash" onclick="setRefundMethod('cash')">
                                <span>💵</span> استرداد نقدي (الخزنة)
                            </button>
                            <div class="refund-select-wrapper" id="wrap-refund-cash">
                                <select id="sel-refund-cash" class="v-select">
                                    <option value="">-- اختر الخزنة --</option>
                                </select>
                            </div>
                            <button class="refund-btn" id="btn-refund-bank" onclick="setRefundMethod('bank')">
                                <span>🏦</span> استرداد بنكي
                            </button>
                            <div class="refund-select-wrapper" id="wrap-refund-bank">
                                <select id="sel-refund-bank" class="v-select">
                                    <option value="">-- اختر الحساب البنكي --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <button class="btn-action" style="padding: 15px; font-size: 1.1rem; display:flex; align-items:center; justify-content:center; gap:10px;" onclick="saveReturnInvoice()">
                            <span>💾</span> حفظ فاتورة المردودات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast Container -->
    <div class="toast-container" id="toast-container" style="position:fixed; bottom:20px; left:20px; z-index:99999; display:flex; flex-direction:column; gap:10px;"></div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/accounting-engine.js"></script>
    <script defer src="js/app.js?v=<?= time() ?>"></script>
    <script>
        let allPurchases = [];
        let itemsDb = [];
        let suppliersDb = [];
        let safesDb = [];
        let banksDb = [];
        let currentInvoice = null;
        let returnLines = [];
        let activeRefundMethod = 'supplier';

        // Formatting utilities
        function fmtNum(n) { return Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function parseNum(str) { if(!str) return 0; return parseFloat(String(str).replace(/,/g, '')) || 0; }
        function showToast(msg, type='info') {
            const container = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.style.padding = '12px 20px'; t.style.borderRadius = '8px'; t.style.color = '#fff'; t.style.fontSize = '0.9rem'; t.style.fontWeight = '700'; t.style.boxShadow = '0 5px 15px rgba(0,0,0,0.4)'; t.style.animation = 'slideDown 0.3s ease forwards';
            if(type === 'error') t.style.background = 'rgba(239, 68, 68, 0.9)';
            else if(type === 'success') t.style.background = 'rgba(16, 185, 129, 0.9)';
            else t.style.background = 'rgba(56, 189, 248, 0.9)';
            t.textContent = msg;
            container.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(), 300); }, 3000);
        }

        function initData() {
            function loadKey(key) {
                let raw = localStorage.getItem(key);
                if (typeof raw === 'string') {
                    try { return JSON.parse(raw) || []; } catch(e) { return []; }
                } else if (raw && typeof raw === 'object') {
                    return raw;
                }
                return [];
            }
            allPurchases = loadKey('erp_purchase_invoices_v1');
            itemsDb = loadKey('erp_items_v1');
            suppliersDb = loadKey('erp_suppliers_v1');
            safesDb = loadKey('erp_safes_v1');
            banksDb = loadKey('erp_banks_v1');
            
            populateRefundSelects();
            renderInvoices();
        }

        function populateRefundSelects() {
            const safeSel = document.getElementById('sel-refund-cash');
            const bankSel = document.getElementById('sel-refund-bank');
            safeSel.innerHTML = '<option value="">-- اختر الخزنة --</option>';
            bankSel.innerHTML = '<option value="">-- اختر الحساب البنكي --</option>';
            
            safesDb.forEach(s => {
                if (!s.deleted) safeSel.innerHTML += `<option value="${s.id}">${s.name || s.safeName}</option>`;
            });
            banksDb.forEach(b => {
                if (!b.deleted) bankSel.innerHTML += `<option value="${b.id}">${b.name || b.bankName}</option>`;
            });
        }
        
        function setRefundMethod(method) {
            activeRefundMethod = method;
            document.querySelectorAll('.refund-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.refund-select-wrapper').forEach(w => w.classList.remove('active'));
            
            document.getElementById(`btn-refund-${method}`).classList.add('active');
            const wrap = document.getElementById(`wrap-refund-${method}`);
            if (wrap) wrap.classList.add('active');
        }

        if (window.APP_READY) {
            initData();
            if (typeof window.pullFromCloud === 'function') {
                window.pullFromCloud(true);
            }
        } else {
            document.addEventListener('DBReady', () => {
                initData();
                if (typeof window.pullFromCloud === 'function') {
                    window.pullFromCloud(true);
                }
            });
        }
        window.addEventListener('cloudDataRestored', initData);
        window.addEventListener('strictDataReady', initData);

        function renderInvoices() {
            const tbody = document.getElementById('invoices-tbody');
            const search = document.getElementById('search-inv').value.toLowerCase();
            tbody.innerHTML = '';
            
            // Only show NON-RETURN and completed purchases.
            const validInvoices = allPurchases.filter(inv => {
                if (inv.deleted || inv.isReturn || inv.status === 'draft' || inv.status === 'proforma') return false;
                const invId = inv.invoiceNo || inv.id;
                if (search) {
                    const supName = getSupplierName(inv.customer || inv.customerId || inv.supplierId).toLowerCase();
                    return (invId && invId.toLowerCase().includes(search)) || supName.includes(search);
                }
                return true;
            });

            if (validInvoices.length === 0) {
                document.getElementById('empty-hub').style.display = 'flex';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                // Sort by date desc
                validInvoices.sort((a,b) => new Date(b.date) - new Date(a.date));
                
                validInvoices.forEach(inv => {
                    const supName = getSupplierName(inv.customer || inv.customerId || inv.supplierId);
                    const invId = inv.invoiceNo || inv.id;
                    
                    // Calculate if it has returns
                    const returnsForThis = allPurchases.filter(r => !r.deleted && r.isReturn && r.originalInvoiceId === invId);
                    let returnStatus = '<span style="color:#94a3b8; font-size:0.8rem;">لا توجد مردودات</span>';
                    if (returnsForThis.length > 0) {
                        returnStatus = `<span style="color:var(--return-primary); font-size:0.8rem; font-weight:800; background:rgba(244,63,94,0.1); padding:4px 8px; border-radius:12px;">مسترد جزئياً (${returnsForThis.length})</span>`;
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:700;">${invId}</td>
                        <td style="text-align:right; font-family:'Outfit';">${new Date(inv.date).toLocaleDateString('en-GB')}</td>
                        <td style="text-align:right; font-weight:700;">${supName}</td>
                        <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${fmtNum(inv.grandTotal)}</td>
                        <td style="text-align:center;">${returnStatus}</td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="padding:6px 15px; font-size:0.8rem;" onclick="openReturnEditor('${invId}')">إرجاع أصناف</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => s.id === id);
            return sup ? sup.name : 'مورد نقدي';
        }

        function getItemName(id) {
            const it = itemsDb.find(i => i.id === id);
            return it ? it.name : 'صنف غير معروف';
        }

        function showHub() {
            document.getElementById('view-editor').classList.remove('active');
            document.getElementById('view-hub').classList.add('active');
            currentInvoice = null;
            returnLines = [];
        }

        function openReturnEditor(invId) {
            currentInvoice = allPurchases.find(x => (x.invoiceNo || x.id) === invId);
            if (!currentInvoice) return showToast('الفاتورة غير موجودة', 'error');

            const currentInvId = currentInvoice.invoiceNo || currentInvoice.id;
            document.getElementById('lbl-inv-no').textContent = currentInvId;
            document.getElementById('lbl-supplier').textContent = getSupplierName(currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId);
            document.getElementById('lbl-date').textContent = new Date(currentInvoice.date).toLocaleDateString('en-GB');
            document.getElementById('lbl-warehouse').textContent = getWarehouseName(currentInvoice.warehouse) || 'المستودع الرئيسي';

            // Find previous returns for this invoice to calculate Max Returnable Quantity
            const previousReturns = allPurchases.filter(r => !r.deleted && r.isReturn && r.originalInvoiceId === currentInvId);
            
            // Build returnLines
            const itemsList = currentInvoice.items || currentInvoice.lines || [];
            returnLines = itemsList.map((line, index) => {
                // Calculate previously returned quantity for this exact line item
                let alreadyReturned = 0;
                previousReturns.forEach(retInv => {
                    const retItems = retInv.items || retInv.lines || [];
                    // match by item ID (since line IDs might not be strict in legacy)
                    const matchingRetLine = retItems.find(ri => ri.itemId === line.itemId && ri.unitId === line.unitId);
                    if (matchingRetLine) {
                        alreadyReturned += Math.abs(parseFloat(matchingRetLine.quantity) || 0);
                    }
                });

                const originalQty = parseFloat(line.quantity) || 0;
                const maxReturnable = Math.max(0, originalQty - alreadyReturned);

                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName || 'حبة',
                    price: parseFloat(line.price) || 0,
                    taxRate: parseFloat(line.taxRate) || 0,
                    discountVal: parseFloat(line.discountValue) || parseFloat(line.discount) || 0, // Absolute discount per unit usually, or total
                    originalQty: originalQty,
                    alreadyReturned: alreadyReturned,
                    maxReturnable: maxReturnable,
                    returnQty: 0
                };
            });

            document.getElementById('view-hub').classList.remove('active');
            document.getElementById('view-editor').classList.add('active');
            renderReturnLines();
        }

        function getWarehouseName(id) {
            if(!id) return '';
            const wh = (JSON.parse(localStorage.getItem('erp_warehouses_v1'))||[]).find(w=>w.id===id);
            return wh ? wh.name : '';
        }

        function renderReturnLines() {
            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            returnLines.forEach(line => {
                const tr = document.createElement('tr');
                
                const lineTotal = line.returnQty * line.price; // Subtotal before tax/discount
                
                tr.innerHTML = `
                    <td style="text-align:right; font-weight:700;">${getItemName(line.itemId)}</td>
                    <td style="text-align:center; color:#94a3b8;">${line.unitName}</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${line.originalQty}</td>
                    <td style="text-align:center; font-family:'Outfit'; color:#f59e0b;">${line.alreadyReturned}</td>
                    <td style="text-align:center;">
                        <input type="text" class="return-input" inputmode="decimal" value="${line.returnQty || ''}" placeholder="0" 
                               onfocus="this.select()" onchange="updateReturnQty('${line.id}', this.value)">
                        <div style="font-size:0.6rem; color:#94a3b8; margin-top:4px;">الحد الأقصى: ${line.maxReturnable}</div>
                    </td>
                    <td style="text-align:center; font-family:'Outfit';">${fmtNum(line.price)}</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800; color:var(--return-primary);">${fmtNum(lineTotal)}</td>
                `;
                tbody.appendChild(tr);
            });

            calcReturnTotals();
        }

        function updateReturnQty(lineId, valStr) {
            const val = parseNum(valStr);
            const line = returnLines.find(x => x.id === lineId);
            const inputEl = document.querySelector(`input[onchange="updateReturnQty('${lineId}', this.value)"]`);
            if (line) {
                if (val > line.maxReturnable) {
                    showToast(`الكمية المرتجعة لا يمكن أن تتجاوز ${line.maxReturnable}`, 'error');
                    line.returnQty = line.maxReturnable;
                    if(inputEl) { inputEl.classList.add('error'); setTimeout(()=>inputEl.classList.remove('error'), 500); }
                } else if (val < 0) {
                    line.returnQty = 0;
                    if(inputEl) { inputEl.classList.add('error'); setTimeout(()=>inputEl.classList.remove('error'), 500); }
                } else {
                    line.returnQty = val;
                    if(inputEl && val > 0) { inputEl.style.borderColor = '#10b981'; setTimeout(()=>inputEl.style.borderColor='', 500); }
                }
            }
            renderReturnLines();
        }

        function calcReturnTotals() {
            let subtotal = 0;
            let taxTotal = 0;
            let discountTotal = 0;

            returnLines.forEach(line => {
                if (line.returnQty > 0) {
                    let lineSub = line.returnQty * line.price;
                    subtotal += lineSub;
                    
                    // Approximate proportional discount if the original had a total discount
                    // For accuracy, we assume the discount was per unit if it's small, or proportional.
                    // This is a simplified safe assumption for returns.
                    let lineDisc = 0; 
                    
                    // Simple tax
                    let lineTax = (lineSub - lineDisc) * (line.taxRate / 100);
                    taxTotal += lineTax;
                }
            });

            // Adjust based on original invoice global discount
            let globalDiscRate = 0;
            if (currentInvoice && currentInvoice.discountValue > 0 && currentInvoice.subtotal > 0) {
                globalDiscRate = currentInvoice.discountValue / currentInvoice.subtotal;
                discountTotal = subtotal * globalDiscRate;
            }

            let grand = (subtotal - discountTotal) + taxTotal;

            document.getElementById('lbl-subtotal').textContent = fmtNum(subtotal);
            document.getElementById('lbl-tax').textContent = fmtNum(taxTotal);
            document.getElementById('lbl-discount').textContent = fmtNum(discountTotal);
            document.getElementById('lbl-grand-total').textContent = fmtNum(grand);
        }

        function saveReturnInvoice() {
            const returningItems = returnLines.filter(x => x.returnQty > 0);
            if (returningItems.length === 0) {
                return showToast('يجب تحديد كمية للإرجاع في صنف واحد على الأقل', 'error');
            }

            // Validate Refund Method
            let refundAccountId = null;
            if (activeRefundMethod === 'cash') {
                refundAccountId = document.getElementById('sel-refund-cash').value;
                if (!refundAccountId) return showToast('يرجى اختيار الخزنة لاسترداد المبلغ', 'error');
            } else if (activeRefundMethod === 'bank') {
                refundAccountId = document.getElementById('sel-refund-bank').value;
                if (!refundAccountId) return showToast('يرجى اختيار الحساب البنكي لاسترداد المبلغ', 'error');
            }

            // Create Return Invoice Object
            let subtotal = 0;
            let taxTotal = 0;

            const newItems = returningItems.map(line => {
                let lineSub = line.returnQty * line.price;
                subtotal += lineSub;
                let lineTax = lineSub * (line.taxRate / 100);
                taxTotal += lineTax;

                return {
                    itemId: line.itemId,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    // Store as NEGATIVE to auto-reduce inventory in inventory-report
                    quantity: -line.returnQty, 
                    price: line.price,
                    taxRate: line.taxRate,
                    total: -(lineSub + lineTax)
                };
            });

            let globalDiscRate = 0;
            if (currentInvoice.discountValue > 0 && currentInvoice.subtotal > 0) {
                globalDiscRate = currentInvoice.discountValue / currentInvoice.subtotal;
            }
            let discountTotal = subtotal * globalDiscRate;
            let grand = (subtotal - discountTotal) + taxTotal;

            const currentInvId = currentInvoice.invoiceNo || currentInvoice.id;
            const userReason = document.getElementById('return-reason').value.trim();
            const notesText = userReason ? `مردودات: ${userReason} (أصل الفاتورة ${currentInvId})` : `مردودات لفاتورة المشتريات رقم ${currentInvId}`;
            
            const retInvId = 'RET-' + Date.now();
            const retInv = {
                id: retInvId,
                invoiceNo: retInvId,
                isReturn: true,
                originalInvoiceId: currentInvId,
                type: 'purchase',
                customerId: currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId,
                supplierName: currentInvoice.supplierName,
                date: new Date().toISOString(),
                warehouse: currentInvoice.warehouse,
                subtotal: -subtotal,
                taxValue: -taxTotal,
                discountValue: -discountTotal,
                grandTotal: -grand,
                items: newItems,
                status: 'completed',
                notes: notesText,
                refundMethod: activeRefundMethod,
                refundAccountId: refundAccountId
            };

            allPurchases.push(retInv);
            localStorage.setItem('erp_purchase_invoices_v1', JSON.stringify(allPurchases));

            // --- Apply Inventory Effects (Decrease stock because we return to supplier) ---
            let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1')) || [];
            let itemsDbLocal = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
            
            newItems.forEach(retItem => {
                const dbItem = itemsDbLocal.find(i => String(i.id) === String(retItem.itemId));
                if (dbItem) {
                    const returnQtyNum = Math.abs(retItem.quantity);
                    let unitRate = 1;
                    if (retItem.unitId !== dbItem.primaryUnit) {
                        const alt = (dbItem.alternativeUnits || []).find(u => u.unitId === retItem.unitId);
                        if (alt) unitRate = parseFloat(alt.conversionRate) || 1;
                    }
                    const baseQty = returnQtyNum * unitRate;
                    
                    const invRec = inventory.find(x => String(x.itemId) === String(retItem.itemId) && String(x.warehouseId) === String(currentInvoice.warehouse));
                    if (invRec) {
                        invRec.quantity = (parseFloat(invRec.quantity) || 0) - baseQty;
                    }
                    dbItem.stock = (parseFloat(dbItem.stock) || 0) - baseQty;
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
            localStorage.setItem('erp_items_v1', JSON.stringify(itemsDbLocal));

            const exRate = parseFloat(currentInvoice.exchangeRate) || parseFloat(currentInvoice.currencyRate) || 1;
            const grandLocal = grand * exRate;

            // --- Apply Financial Effects (Treasury / Bank / Supplier) ---
            if (activeRefundMethod === 'supplier') {
                let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1')) || [];
                let custId = currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId;
                if (custId && custId !== 'walk-in') {
                    const sIdx = suppliers.findIndex(s => String(s.id) === String(custId));
                    if (sIdx > -1) {
                        suppliers[sIdx].currentBalance = (parseFloat(suppliers[sIdx].currentBalance) || 0) - grandLocal; 
                        localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                    }
                }
            } else if (activeRefundMethod === 'cash' || activeRefundMethod === 'bank') {
                // Update Treasury Balance
                let safesOrBanks = [];
                let storageKey = activeRefundMethod === 'cash' ? 'erp_safes_v1' : 'erp_banks_v1';
                safesOrBanks = JSON.parse(localStorage.getItem(storageKey)) || [];
                let sIdx = safesOrBanks.findIndex(s => String(s.id) === String(refundAccountId));
                if (sIdx > -1) {
                    safesOrBanks[sIdx].currentBalance = (parseFloat(safesOrBanks[sIdx].currentBalance) || 0) + grandLocal;
                    localStorage.setItem(storageKey, JSON.stringify(safesOrBanks));
                }

                // Log a Single Receipt (سند قبض) for traceability
                let receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1')) || [];
                let receiptNo = 'RCT-' + Date.now();
                let newReceipt = {
                    id: receiptNo,
                    receiptNo: receiptNo,
                    date: new Date().toISOString().split('T')[0],
                    amount: grandLocal,
                    amountText: grandLocal + " " + (currentInvoice.currency || "SDG"),
                    fromAccount: `supp-${currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId}`,
                    toAccount: (activeRefundMethod === 'cash' ? 'safe-' : 'bank-') + refundAccountId,
                    paymentMethod: activeRefundMethod,
                    type: 'receipt',
                    statement: `استرداد مبلغ مرتجع مشتريات للفاتورة ${currentInvId}`,
                    notes: notesText,
                    status: 'completed',
                    isDeleted: false,
                    isReturnRefund: true,
                    originalReturnId: retInvId
                };
                receipts.push(newReceipt);
                localStorage.setItem('erp_single_receipts_v1', JSON.stringify(receipts));
            }

            // --- Post GL Journal Voucher (if AccountingEngine is available) ---
            if (typeof AccountingEngine !== 'undefined' && AccountingEngine.postPurchaseInvoice) {
                // Post an inverse purchase invoice (since grandTotal and quantities are negative, it creates inverse entries automatically in the engine)
                let jv = AccountingEngine.postPurchaseInvoice(retInv);
                if (jv) {
                    // Update the JV list
                    let storedJVs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1')) || [];
                    jv.description = "قيد " + notesText;
                    storedJVs.push(jv);
                    localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(storedJVs));
                }
            }

            // Ensure Immediate Cloud Sync
            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true).catch(e => console.warn("Cloud push failed:", e));
            }

            showToast(`تم حفظ المردودات بنجاح برقم ${retInvId}`, 'success');
            
            setTimeout(() => {
                initData(); // Refresh UI data
                showHub();
            }, 1500);
        }

    </script>
</body>
</html>
