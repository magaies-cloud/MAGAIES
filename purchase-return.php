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

        <!-- Cross-Screen Navigation Bar + 6 KPI Summary Strip -->
        <div style="padding: 12px 20px 0 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:rgba(15,23,42,0.75); border:1px solid rgba(244,63,94,0.28); border-radius:14px; padding:10px 16px; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-weight:900; color:#fda4af; font-size:0.95rem;">↩️ بوابة مرتجعات المشتريات والإشعارات المدينة 360°</span>
                    <button id="tab-btn-invoices" onclick="switchReturnHubTab('invoices')" style="background:linear-gradient(135deg,#f43f5e,#e11d48); color:#fff; border:none; padding:6px 14px; border-radius:8px; font-weight:800; font-size:0.82rem; cursor:pointer; font-family:'Tajawal',sans-serif;">📄 الفواتير القابلة للاسترجاع</button>
                    <button id="tab-btn-returns" onclick="switchReturnHubTab('returns')" style="background:rgba(30,41,59,0.85); color:#cbd5e1; border:1px solid rgba(148,163,184,0.3); padding:6px 14px; border-radius:8px; font-weight:800; font-size:0.82rem; cursor:pointer; font-family:'Tajawal',sans-serif;">📋 سجل إشعارات المرتجعات المعتمدة (<span id="ret-badge-count">0</span>)</button>
                </div>
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <a href="purchase.php" style="text-decoration:none; background:rgba(249,115,22,0.15); border:1px solid rgba(249,115,22,0.4); color:#fdba74; padding:5px 11px; border-radius:8px; font-size:0.78rem; font-weight:800;">💵 فاتورة مشتريات</a>
                    <a href="smart-logistics.php?mode=purchases" style="text-decoration:none; background:rgba(14,165,233,0.15); border:1px solid rgba(56,189,248,0.4); color:#38bdf8; padding:5px 11px; border-radius:8px; font-size:0.78rem; font-weight:800;">📥 بوابة استلام المشتريات</a>
                    <a href="purchase-receipt.php" style="text-decoration:none; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4); color:#34d399; padding:5px 11px; border-radius:8px; font-size:0.78rem; font-weight:800;">🚛 استلام بضاعة GRN</a>
                    <a href="supplier_reliability.php" style="text-decoration:none; background:rgba(245,158,11,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24; padding:5px 11px; border-radius:8px; font-size:0.78rem; font-weight:800;">🤝 اعتمادية الموردين 360°</a>
                    <a href="item-ledger.php" style="text-decoration:none; background:rgba(168,85,247,0.15); border:1px solid rgba(168,85,247,0.4); color:#d8b4fe; padding:5px 11px; border-radius:8px; font-size:0.78rem; font-weight:800;">📊 كشف حركة الصنف</a>
                </div>
            </div>

            <!-- KPI Cards Strip -->
            <div id="return-kpi-strip" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(175px,1fr)); gap:10px; margin-bottom:8px;">
                <div style="background:rgba(15,23,42,0.75); border:1px solid rgba(56,189,248,0.25); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:700;">📄 فواتير المشتريات القابلة للإرجاع</div>
                    <div id="kpi-ret-eligible" style="font-size:1.25rem; font-weight:900; color:#38bdf8; margin-top:2px;">0</div>
                </div>
                <div style="background:rgba(15,23,42,0.75); border:1px solid rgba(244,63,94,0.3); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:700;">↩️ إجمالي إشعارات المرتجعات</div>
                    <div id="kpi-ret-count" style="font-size:1.25rem; font-weight:900; color:#fb7185; margin-top:2px;">0</div>
                </div>
                <div style="background:rgba(15,23,42,0.75); border:1px solid rgba(16,185,129,0.28); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:700;">💰 إجمالي القيمة المستردة (SDG / USD)</div>
                    <div id="kpi-ret-total-val" style="font-size:1.05rem; font-weight:900; color:#34d399; margin-top:2px;">0 SDG</div>
                </div>
                <div style="background:rgba(15,23,42,0.75); border:1px solid rgba(245,158,11,0.28); border-radius:12px; padding:10px 14px;">
                    <div style="font-size:0.75rem; color:#94a3b8; font-weight:700;">🤖 مؤشر جودة التوريد ومعدل المرتجع</div>
                    <div id="kpi-ret-rate-ai" style="font-size:1.05rem; font-weight:900; color:#fbbf24; margin-top:2px;">0.0% (ممتاز)</div>
                </div>
            </div>
        </div>

        <!-- View 1: Hub (Select Invoice or View Returns Registry) -->
        <div class="view-container active" id="view-hub">
            <div class="neo-card" id="hub-invoices-card">
                <div class="neo-card-title" style="flex-wrap:wrap; gap:10px;">
                    <span>📄 فواتير المشتريات المعتمدة (اختر فاتورة لإصدار إشعار مرتجع / خصم مورد)</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="text" id="search-inv" class="v-input" placeholder="🔍 ابحث برقم الفاتورة أو اسم المورد أو الصنف..." style="width: 310px;" oninput="renderInvoices()">
                    </div>
                </div>
                
                <div class="grid-scroll-wrap">
                    <table class="pos-grid">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم الفاتورة</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">المورد</th>
                                <th style="text-align:center;">المستودع</th>
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

            <!-- Saved Returns Registry Card -->
            <div class="neo-card" id="hub-returns-card" style="display:none;">
                <div class="neo-card-title" style="flex-wrap:wrap; gap:10px;">
                    <span>📋 سجل إشعارات مرتجعات المشتريات المعتمدة (Debit Notes Registry)</span>
                    <input type="text" id="search-ret-reg" class="v-input" placeholder="🔍 بحث برقم المرتجع أو الفاتورة الأصلية أو المورد..." style="width: 310px;" oninput="renderSavedReturnsRegistry()">
                </div>
                <div class="grid-scroll-wrap">
                    <table class="pos-grid">
                        <thead>
                            <tr>
                                <th style="text-align:right;">رقم إشعار المرتجع</th>
                                <th style="text-align:right;">الفاتورة الأصلية</th>
                                <th style="text-align:right;">التاريخ</th>
                                <th style="text-align:right;">المورد</th>
                                <th style="text-align:right;">السبب والتصنيف</th>
                                <th style="text-align:center;">طريقة الاسترداد</th>
                                <th style="text-align:center;">القيمة المستردة</th>
                                <th style="text-align:center;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="saved-returns-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- View 2: Editor (Return Items) -->
        <div class="view-container" id="view-editor">
            <div class="editor-layout">
                <!-- Left: Items Table -->
                <div class="editor-left">
                    <div class="neo-card-title" style="flex-wrap:wrap; gap:8px;">
                        <span>📦 أصناف الفاتورة: <span id="lbl-inv-no" style="color:var(--return-primary); font-family:'Outfit';"></span></span>
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <button type="button" onclick="fillAllReturnQty()" style="background:rgba(244,63,94,0.16); border:1px solid rgba(244,63,94,0.45); color:#fda4af; padding:5px 11px; border-radius:8px; font-weight:800; font-size:0.78rem; cursor:pointer; font-family:'Tajawal',sans-serif;">⚡ إرجاع كامل المتبقي</button>
                            <button type="button" onclick="clearAllReturnQty()" style="background:rgba(148,163,184,0.15); border:1px solid rgba(148,163,184,0.35); color:#cbd5e1; padding:5px 11px; border-radius:8px; font-weight:800; font-size:0.78rem; cursor:pointer; font-family:'Tajawal',sans-serif;">🧹 تصفير الكميات</button>
                            <button class="btn-action btn-cancel" style="padding: 6px 12px; font-size: 0.8rem;" onclick="showHub()">إلغاء التحديد</button>
                        </div>
                    </div>
                    
                    <div class="grid-scroll-wrap" style="flex:1;">
                        <table class="pos-grid">
                            <thead>
                                <tr>
                                    <th style="text-align:right; width:32%;">الصنف</th>
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
                        <h3>تفاصيل المورد والمستودع</h3>
                        <div class="info-row"><span class="info-lbl">المورد:</span> <span class="info-val" id="lbl-supplier" style="direction:rtl;">-</span></div>
                        <div class="info-row"><span class="info-lbl">تاريخ الشراء:</span> <span class="info-val" id="lbl-date">-</span></div>
                        <div class="info-row"><span class="info-lbl">المستودع الأصلي:</span> <span class="info-val" id="lbl-warehouse" style="direction:rtl;">-</span></div>
                        <div style="margin-top:8px;">
                            <label style="font-size:0.78rem; color:#94a3b8; font-weight:700; display:block; margin-bottom:4px;">📦 المستودع المخصوم منه المرتجع:</label>
                            <select id="sel-return-warehouse" class="v-select" style="width:100%;"></select>
                        </div>
                    </div>

                    <div class="info-card" style="flex:1; overflow-y:auto;">
                        <h3>ملخص المردودات والإشعار المدين</h3>
                        <div class="info-row"><span class="info-lbl">إجمالي قيمة المردودات:</span> <span class="info-val" id="lbl-subtotal">0.00</span></div>
                        <div class="info-row"><span class="info-lbl">الضريبة المرتجعة:</span> <span class="info-val" id="lbl-tax">0.00</span></div>
                        <div class="info-row"><span class="info-lbl">الخصم المسترد:</span> <span class="info-val" id="lbl-discount">0.00</span></div>
                        
                        <div class="tot-grand">
                            <span class="lbl">الصافي على المورد</span>
                            <span class="val" id="lbl-grand-total">0.00</span>
                        </div>
                        <div id="lbl-grand-usd" style="text-align:left; font-size:0.78rem; color:#a855f7; font-weight:800; margin-top:4px;">≈ $0.00 USD</div>
                        
                        <h3 style="margin-top: 14px;">تصنيف وسبب الاسترجاع (يغذي تقييم المورد AI)</h3>
                        <select id="sel-return-category" class="v-select" style="width:100%; margin-bottom:8px;">
                            <option value="تالف مصنعي / كسر في الشحنة">⚠️ تالف مصنعي / كسر في الشحنة</option>
                            <option value="مخالفة للمواصفات المتفق عليها">🔍 مخالفة للمواصفات المتفق عليها</option>
                            <option value="قرب أو انتهاء الصلاحية">⏳ قرب أو انتهاء الصلاحية</option>
                            <option value="فرق سعر / تسوية خصم مكتسب">💲 فرق سعر / تسوية خصم مكتسب</option>
                            <option value="فائض عن الحاجة بالتراضي">📦 فائض عن الحاجة بالتراضي</option>
                        </select>
                        <input type="text" id="return-reason" class="v-input" placeholder="تفاصيل إضافية لسبب الإرجاع..." style="width: 100%;">
                        
                        <h3 style="margin-top: 14px;">طريقة الاسترداد المالي <span style="color:#f43f5e;">*</span></h3>
                        <div class="refund-options">
                            <button class="refund-btn active" id="btn-refund-supplier" onclick="setRefundMethod('supplier')">
                                <span>📉</span> خصم من رصيد المورد (إشعار مدين)
                            </button>
                            <button class="refund-btn" id="btn-refund-cash" onclick="setRefundMethod('cash')">
                                <span>💵</span> استرداد نقدي فوري (الخزنة)
                            </button>
                            <div class="refund-select-wrapper" id="wrap-refund-cash">
                                <select id="sel-refund-cash" class="v-select">
                                    <option value="">-- اختر الخزنة --</option>
                                </select>
                            </div>
                            <button class="refund-btn" id="btn-refund-bank" onclick="setRefundMethod('bank')">
                                <span>🏦</span> استرداد لحساب بنكي
                            </button>
                            <div class="refund-select-wrapper" id="wrap-refund-bank">
                                <select id="sel-refund-bank" class="v-select">
                                    <option value="">-- اختر الحساب البنكي --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <button class="btn-action" style="padding: 15px; font-size: 1.05rem; display:flex; align-items:center; justify-content:center; gap:10px;" onclick="saveReturnInvoice()">
                            <span>💾</span> اعتماد وحفظ فاتورة المردودات (Debit Note)
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
    <script defer src="js/app.js?v=20261016_v28"></script>
    <script>
        let allPurchases = [];
        let itemsDb = [];
        let suppliersDb = [];
        let warehousesDb = [];
        let safesDb = [];
        let banksDb = [];
        let currentInvoice = null;
        let returnLines = [];
        let activeRefundMethod = 'supplier';
        let activeHubTab = 'invoices';

        function getUsdExchangeRate() {
            try {
                const curCfg = JSON.parse(localStorage.getItem('erp_currencies_v1') || '[]');
                const usd = Array.isArray(curCfg) ? curCfg.find(c => String(c.code || '').toUpperCase() === 'USD') : null;
                const r = parseFloat(usd?.exchangeRate || localStorage.getItem('erp_exchange_rate_usd') || localStorage.getItem('usd_rate') || 2700);
                return r > 0 ? r : 2700;
            } catch (e) { return 2700; }
        }

        function fmtNum(n) { return Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function parseNum(str) { if(!str) return 0; return parseFloat(String(str).replace(/,/g, '')) || 0; }
        function showToast(msg, type='info') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const t = document.createElement('div');
            t.style.padding = '12px 20px'; t.style.borderRadius = '8px'; t.style.color = '#fff'; t.style.fontSize = '0.9rem'; t.style.fontWeight = '700'; t.style.boxShadow = '0 5px 15px rgba(0,0,0,0.4)'; t.style.animation = 'slideDown 0.3s ease forwards';
            if(type === 'error') t.style.background = 'rgba(239, 68, 68, 0.92)';
            else if(type === 'success') t.style.background = 'rgba(16, 185, 129, 0.92)';
            else t.style.background = 'rgba(56, 189, 248, 0.92)';
            t.textContent = msg;
            container.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(), 300); }, 3200);
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
            warehousesDb = loadKey('erp_warehouses_v1');
            safesDb = loadKey('erp_safes_v1');
            banksDb = loadKey('erp_banks_v1');
            
            populateRefundSelects();
            updateReturnKPIs();
            renderInvoices();
            renderSavedReturnsRegistry();
            handleReturnDeepLink();
        }

        function updateReturnKPIs() {
            const validPurchases = (allPurchases || []).filter(inv => inv && !inv.deleted && !inv.isReturn && !inv.isGRN && inv.status !== 'draft' && inv.status !== 'proforma' && inv.saleType !== 'proforma');
            const savedReturns = (allPurchases || []).filter(inv => inv && !inv.deleted && inv.isReturn);
            const fx = getUsdExchangeRate();

            let totalPurVal = 0;
            validPurchases.forEach(p => { totalPurVal += Math.abs(parseFloat(p.grandTotal || p.total || 0)); });
            let totalRetVal = 0;
            savedReturns.forEach(r => { totalRetVal += Math.abs(parseFloat(r.grandTotal || r.total || 0)); });

            const ratePct = totalPurVal > 0 ? ((totalRetVal / totalPurVal) * 100) : 0;
            const elEligible = document.getElementById('kpi-ret-eligible');
            const elCount = document.getElementById('kpi-ret-count');
            const elBadge = document.getElementById('ret-badge-count');
            const elTotal = document.getElementById('kpi-ret-total-val');
            const elRate = document.getElementById('kpi-ret-rate-ai');

            if (elEligible) elEligible.textContent = validPurchases.length;
            if (elCount) elCount.textContent = savedReturns.length;
            if (elBadge) elBadge.textContent = savedReturns.length;
            if (elTotal) elTotal.innerHTML = `${fmtNum(totalRetVal)} SDG <span style="font-size:0.75rem;color:#a855f7;">($${(totalRetVal / fx).toFixed(1)})</span>`;
            if (elRate) {
                const statusTxt = ratePct > 8 ? '⚠️ مرتفع — راجع الموردين' : (ratePct > 3 ? '📊 متوسط' : '✅ ممتاز');
                elRate.textContent = `${ratePct.toFixed(1)}% (${statusTxt})`;
                elRate.style.color = ratePct > 8 ? '#f87171' : (ratePct > 3 ? '#fbbf24' : '#34d399');
            }
        }

        function switchReturnHubTab(tab) {
            activeHubTab = tab;
            const invCard = document.getElementById('hub-invoices-card');
            const retCard = document.getElementById('hub-returns-card');
            const btnInv = document.getElementById('tab-btn-invoices');
            const btnRet = document.getElementById('tab-btn-returns');
            if (tab === 'returns') {
                if (invCard) invCard.style.display = 'none';
                if (retCard) retCard.style.display = 'block';
                if (btnRet) { btnRet.style.background = 'linear-gradient(135deg,#f43f5e,#e11d48)'; btnRet.style.color = '#fff'; }
                if (btnInv) { btnInv.style.background = 'rgba(30,41,59,0.85)'; btnInv.style.color = '#cbd5e1'; }
                renderSavedReturnsRegistry();
            } else {
                if (invCard) invCard.style.display = 'block';
                if (retCard) retCard.style.display = 'none';
                if (btnInv) { btnInv.style.background = 'linear-gradient(135deg,#f43f5e,#e11d48)'; btnInv.style.color = '#fff'; }
                if (btnRet) { btnRet.style.background = 'rgba(30,41,59,0.85)'; btnRet.style.color = '#cbd5e1'; }
                renderInvoices();
            }
        }

        function populateRefundSelects() {
            const safeSel = document.getElementById('sel-refund-cash');
            const bankSel = document.getElementById('sel-refund-bank');
            const whSel = document.getElementById('sel-return-warehouse');
            if (safeSel) {
                safeSel.innerHTML = '<option value="">-- اختر الخزنة --</option>';
                safesDb.forEach(s => {
                    if (!s.deleted) safeSel.innerHTML += `<option value="${s.id}">${s.name || s.safeName}</option>`;
                });
            }
            if (bankSel) {
                bankSel.innerHTML = '<option value="">-- اختر الحساب البنكي --</option>';
                banksDb.forEach(b => {
                    if (!b.deleted) bankSel.innerHTML += `<option value="${b.id}">${b.name || b.bankName}</option>`;
                });
            }
            if (whSel) {
                whSel.innerHTML = '';
                const whList = (warehousesDb && warehousesDb.length > 0) ? warehousesDb : [{ id: 'w-main', name: 'المستودع الرئيسي' }];
                whList.forEach(w => {
                    if (!w.deleted) whSel.innerHTML += `<option value="${w.id}">${w.name}</option>`;
                });
            }
        }
        
        function setRefundMethod(method) {
            activeRefundMethod = method;
            document.querySelectorAll('.refund-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.refund-select-wrapper').forEach(w => w.classList.remove('active'));
            
            const btn = document.getElementById(`btn-refund-${method}`);
            if (btn) btn.classList.add('active');
            const wrap = document.getElementById(`wrap-refund-${method}`);
            if (wrap) wrap.classList.add('active');
        }

        let _deepLinkHandled = false;
        function handleReturnDeepLink() {
            if (_deepLinkHandled) return;
            const params = new URLSearchParams(window.location.search);
            const targetInv = (params.get('inv') || params.get('id') || params.get('invoice') || '').trim();
            if (!targetInv || !allPurchases || !allPurchases.length) return;
            const match = allPurchases.find(x => x && !x.isGRN && !x.isReturn && String(x.invoiceNo || x.id) === targetInv);
            if (match) {
                _deepLinkHandled = true;
                openReturnEditor(match.invoiceNo || match.id);
            }
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
            setTimeout(() => { if (!allPurchases.length) initData(); }, 350);
        }
        window.addEventListener('cloudDataRestored', initData);
        window.addEventListener('strictDataReady', initData);

        function renderInvoices() {
            const tbody = document.getElementById('invoices-tbody');
            const search = (document.getElementById('search-inv')?.value || '').toLowerCase().trim();
            if (!tbody) return;
            tbody.innerHTML = '';
            
            // Exclude deleted, returns, GRNs (Z26...), drafts, and proforma invoices
            const validInvoices = (allPurchases || []).filter(inv => {
                if (!inv || inv.deleted || inv.isReturn || inv.isGRN || inv.status === 'draft' || inv.status === 'proforma' || inv.saleType === 'proforma') return false;
                const invId = String(inv.invoiceNo || inv.id || '');
                if (invId.startsWith('PF')) return false;
                if (search) {
                    const supName = (inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId) || '').toLowerCase();
                    const itemNames = (inv.items || []).map(i => (i.itemName || i.name || getItemName(i.itemId) || '')).join(' ').toLowerCase();
                    return invId.toLowerCase().includes(search) || supName.includes(search) || itemNames.includes(search);
                }
                return true;
            });

            if (validInvoices.length === 0) {
                document.getElementById('empty-hub').style.display = 'flex';
            } else {
                document.getElementById('empty-hub').style.display = 'none';
                validInvoices.sort((a,b) => new Date(b.timestamp || b.date || 0) - new Date(a.timestamp || a.date || 0));
                
                validInvoices.forEach(inv => {
                    const supName = inv.supplierName || getSupplierName(inv.customer || inv.customerId || inv.supplierId);
                    const invId = inv.invoiceNo || inv.id;
                    const whLabel = (!inv.warehouse || inv.warehouse === 'PENDING_RECEIPT') ? '<span style="color:#f59e0b;font-weight:800;">قيد الاستلام</span>' : (getWarehouseName(inv.warehouse) || inv.warehouseName || inv.warehouse);
                    
                    const returnsForThis = allPurchases.filter(r => r && !r.deleted && r.isReturn && String(r.originalInvoiceId) === String(invId));
                    let returnStatus = '<span style="color:#94a3b8; font-size:0.8rem;">لا توجد مردودات</span>';
                    if (returnsForThis.length > 0) {
                        const retSum = returnsForThis.reduce((acc, r) => acc + Math.abs(parseFloat(r.grandTotal || 0)), 0);
                        returnStatus = `<span style="color:var(--return-primary); font-size:0.78rem; font-weight:800; background:rgba(244,63,94,0.12); padding:4px 9px; border-radius:12px; border:1px solid rgba(244,63,94,0.35);">مسترد (${returnsForThis.length}) — ${fmtNum(retSum)}</span>`;
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="text-align:right; font-family:'Outfit'; color:#38bdf8; font-weight:800;">${invId}</td>
                        <td style="text-align:right; font-family:'Outfit';">${new Date(inv.timestamp || inv.date).toLocaleDateString('en-GB')}</td>
                        <td style="text-align:right; font-weight:700;">${supName}</td>
                        <td style="text-align:center; font-weight:700; color:#cbd5e1;">${whLabel}</td>
                        <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${fmtNum(inv.grandTotal || inv.total || 0)} <span style="font-size:0.75rem;color:#a855f7;">${inv.currency || 'SDG'}</span></td>
                        <td style="text-align:center;">${returnStatus}</td>
                        <td style="text-align:center;">
                            <button class="btn-action" style="padding:6px 15px; font-size:0.8rem;" onclick="openReturnEditor('${invId}')">↩️ إرجاع أصناف</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        function renderSavedReturnsRegistry() {
            const tbody = document.getElementById('saved-returns-tbody');
            if (!tbody) return;
            const q = (document.getElementById('search-ret-reg')?.value || '').toLowerCase().trim();
            const returnsList = (allPurchases || []).filter(r => {
                if (!r || r.deleted || !r.isReturn) return false;
                if (!q) return true;
                const sName = (r.supplierName || getSupplierName(r.customerId) || '').toLowerCase();
                return String(r.id || r.invoiceNo || '').toLowerCase().includes(q)
                    || String(r.originalInvoiceId || '').toLowerCase().includes(q)
                    || sName.includes(q)
                    || String(r.notes || '').toLowerCase().includes(q);
            }).sort((a, b) => new Date(b.date || 0) - new Date(a.date || 0));

            if (returnsList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:28px;color:#94a3b8;font-weight:700;">لا توجد إشعارات مرتجعات مسجلة حتى الآن.</td></tr>';
                return;
            }
            const methodMap = {
                supplier: '<span style="color:#fbbf24;font-weight:800;">📉 خصم من رصيد المورد</span>',
                cash: '<span style="color:#34d399;font-weight:800;">💵 استرداد نقدي (خزنة)</span>',
                bank: '<span style="color:#38bdf8;font-weight:800;">🏦 استرداد بنكي</span>'
            };
            tbody.innerHTML = returnsList.map(r => {
                const retId = r.id || r.invoiceNo;
                const supName = r.supplierName || getSupplierName(r.customerId);
                const val = Math.abs(parseFloat(r.grandTotal || r.total || 0));
                return `<tr>
                    <td style="text-align:right;font-weight:900;color:#fb7185;font-family:'Outfit';">${retId}</td>
                    <td style="text-align:right;font-weight:800;color:#38bdf8;font-family:'Outfit';"><a href="purchase.php" style="color:#38bdf8;text-decoration:none;">#${r.originalInvoiceId || '—'}</a></td>
                    <td style="text-align:right;font-family:'Outfit';">${new Date(r.date).toLocaleString('en-GB')}</td>
                    <td style="text-align:right;font-weight:700;">${supName}</td>
                    <td style="text-align:right;color:#cbd5e1;font-size:0.82rem;">${r.returnCategory ? `<strong style="color:#fda4af;">[${r.returnCategory}]</strong> ` : ''}${r.notes || '—'}</td>
                    <td style="text-align:center;">${methodMap[r.refundMethod] || methodMap.supplier}</td>
                    <td style="text-align:center;font-weight:900;color:#f43f5e;font-family:'Outfit';">${fmtNum(val)} SDG</td>
                    <td style="text-align:center;white-space:nowrap;">
                        <button onclick="printReturnDebitNote('${retId}')" style="background:rgba(56,189,248,0.15);border:1px solid rgba(56,189,248,0.4);color:#38bdf8;border-radius:6px;padding:5px 9px;cursor:pointer;font-weight:800;font-size:0.76rem;margin-left:3px;" title="طباعة إشعار خصم مدين QR">🖨️ طباعة</button>
                        <button onclick="sendReturnWhatsApp('${retId}')" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);color:#34d399;border-radius:6px;padding:5px 9px;cursor:pointer;font-weight:800;font-size:0.76rem;margin-left:3px;" title="إشعار المورد عبر واتساب">💬 واتساب</button>
                        <button onclick="reversePurchaseReturn('${retId}')" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#f87171;border-radius:6px;padding:5px 9px;cursor:pointer;font-weight:800;font-size:0.76rem;" title="إلغاء المرتجع وعكس الأثر المخزني والمالي">🗑️ عكس</button>
                    </td>
                </tr>`;
            }).join('');
        }

        function getSupplierName(id) {
            if(!id) return 'مورد نقدي';
            const sup = suppliersDb.find(s => String(s.id) === String(id));
            return sup ? sup.name : String(id);
        }

        function getItemName(id) {
            const it = itemsDb.find(i => String(i.id) === String(id));
            return it ? it.name : (id || 'صنف غير معروف');
        }

        function resolveLineUnitFactor(line, dbItem) {
            const explicitFactor = parseFloat(line.customFactor || line.factor || line.unitFactor || 0);
            if (explicitFactor > 0) return explicitFactor;
            if (!dbItem) return 1;
            const uName = String(line.unitName || line.unit || line.selectedUnit || '').trim();
            if (Array.isArray(dbItem.units) && uName) {
                const matchU = dbItem.units.find(u => String(u.name || u.unitName || '').trim() === uName || String(u.id || '') === String(line.unitId || ''));
                if (matchU && parseFloat(matchU.factor || matchU.equiv) > 0) return parseFloat(matchU.factor || matchU.equiv);
            }
            if (Array.isArray(dbItem.alternativeUnits) && line.unitId) {
                const alt = dbItem.alternativeUnits.find(u => String(u.unitId) === String(line.unitId));
                if (alt && parseFloat(alt.conversionRate) > 0) return parseFloat(alt.conversionRate);
            }
            return 1;
        }

        function showHub() {
            document.getElementById('view-editor').classList.remove('active');
            document.getElementById('view-hub').classList.add('active');
            currentInvoice = null;
            returnLines = [];
            updateReturnKPIs();
            renderInvoices();
            renderSavedReturnsRegistry();
        }

        function openReturnEditor(invId) {
            currentInvoice = allPurchases.find(x => x && !x.isGRN && !x.isReturn && String(x.invoiceNo || x.id) === String(invId));
            if (!currentInvoice) return showToast('الفاتورة غير موجودة', 'error');

            const currentInvId = currentInvoice.invoiceNo || currentInvoice.id;
            document.getElementById('lbl-inv-no').textContent = currentInvId;
            document.getElementById('lbl-supplier').textContent = currentInvoice.supplierName || getSupplierName(currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId);
            document.getElementById('lbl-date').textContent = new Date(currentInvoice.timestamp || currentInvoice.date).toLocaleDateString('en-GB');
            const origWh = (!currentInvoice.warehouse || currentInvoice.warehouse === 'PENDING_RECEIPT') ? (warehousesDb[0]?.id || 'w-main') : currentInvoice.warehouse;
            document.getElementById('lbl-warehouse').textContent = getWarehouseName(origWh) || 'المستودع الرئيسي';
            const whSel = document.getElementById('sel-return-warehouse');
            if (whSel) whSel.value = origWh;

            const previousReturns = allPurchases.filter(r => r && !r.deleted && r.isReturn && String(r.originalInvoiceId) === String(currentInvId));
            
            const itemsList = currentInvoice.items || currentInvoice.lines || [];
            returnLines = itemsList.map((line, index) => {
                let alreadyReturned = 0;
                previousReturns.forEach(retInv => {
                    const retItems = retInv.items || retInv.lines || [];
                    const matchingRetLine = retItems.find(ri => String(ri.itemId) === String(line.itemId) && String(ri.unitName || ri.unitId || '') === String(line.unitName || line.selectedUnit || line.unitId || ''));
                    if (matchingRetLine) {
                        alreadyReturned += Math.abs(parseFloat(matchingRetLine.quantity) || 0);
                    }
                });

                const originalQty = parseFloat(line.quantity || line.qty) || 0;
                const maxReturnable = Math.max(0, +(originalQty - alreadyReturned).toFixed(3));
                const dbItem = itemsDb.find(i => String(i.id) === String(line.itemId));
                const factor = resolveLineUnitFactor(line, dbItem);

                return {
                    id: 'L' + index,
                    itemId: line.itemId,
                    itemName: line.itemName || line.name || getItemName(line.itemId),
                    unitId: line.unitId || line.selectedUnit || line.unitName || 'حبة',
                    unitName: line.unitName || line.selectedUnit || line.unit || (dbItem ? dbItem.baseUnit : 'حبة') || 'حبة',
                    factor: factor,
                    price: parseFloat(line.price || line.unitPrice) || 0,
                    taxRate: parseFloat(line.taxRate) || 0,
                    discountVal: parseFloat(line.discountValue) || parseFloat(line.discount) || 0,
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

        function fillAllReturnQty() {
            returnLines.forEach(l => { l.returnQty = l.maxReturnable; });
            renderReturnLines();
            showToast('⚡ تم تعبئة كامل الكميات القابلة للإرجاع', 'info');
        }

        function clearAllReturnQty() {
            returnLines.forEach(l => { l.returnQty = 0; });
            renderReturnLines();
        }

        function getWarehouseName(id) {
            if(!id) return '';
            const wh = (warehousesDb || []).find(w => String(w.id) === String(id) || String(w.name) === String(id));
            return wh ? wh.name : String(id);
        }

        function renderReturnLines() {
            const tbody = document.getElementById('items-tbody');
            tbody.innerHTML = '';

            returnLines.forEach(line => {
                const tr = document.createElement('tr');
                const lineTotal = line.returnQty * line.price;
                
                tr.innerHTML = `
                    <td style="text-align:right; font-weight:700;">${line.itemName || getItemName(line.itemId)}</td>
                    <td style="text-align:center; color:#94a3b8;">${line.unitName}${line.factor > 1 ? ` <span style="font-size:0.68rem;color:#38bdf8;">(×${line.factor})</span>` : ''}</td>
                    <td style="text-align:center; font-family:'Outfit'; font-weight:800;">${line.originalQty}</td>
                    <td style="text-align:center; font-family:'Outfit'; color:#f59e0b;">${line.alreadyReturned}</td>
                    <td style="text-align:center;">
                        <input type="text" class="return-input" inputmode="decimal" value="${line.returnQty || ''}" placeholder="0" 
                               onfocus="this.select()" onchange="updateReturnQty('${line.id}', this.value)">
                        <div style="font-size:0.65rem; color:#94a3b8; margin-top:4px;">الحد الأقصى: ${line.maxReturnable}</div>
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
                    let lineTax = lineSub * (line.taxRate / 100);
                    taxTotal += lineTax;
                }
            });

            let globalDiscRate = 0;
            if (currentInvoice && currentInvoice.discountValue > 0 && currentInvoice.subtotal > 0) {
                globalDiscRate = currentInvoice.discountValue / currentInvoice.subtotal;
                discountTotal = subtotal * globalDiscRate;
            }

            let grand = (subtotal - discountTotal) + taxTotal;
            const fx = getUsdExchangeRate();

            document.getElementById('lbl-subtotal').textContent = fmtNum(subtotal);
            document.getElementById('lbl-tax').textContent = fmtNum(taxTotal);
            document.getElementById('lbl-discount').textContent = fmtNum(discountTotal);
            document.getElementById('lbl-grand-total').textContent = fmtNum(grand);
            const usdEl = document.getElementById('lbl-grand-usd');
            if (usdEl) usdEl.textContent = `≈ $${(grand / fx).toFixed(2)} USD`;
        }

        function saveReturnInvoice() {
            const returningItems = returnLines.filter(x => x.returnQty > 0);
            if (returningItems.length === 0) {
                return showToast('يجب تحديد كمية للإرجاع في صنف واحد على الأقل', 'error');
            }

            let refundAccountId = null;
            if (activeRefundMethod === 'cash') {
                refundAccountId = document.getElementById('sel-refund-cash').value;
                if (!refundAccountId) return showToast('يرجى اختيار الخزنة لاسترداد المبلغ', 'error');
            } else if (activeRefundMethod === 'bank') {
                refundAccountId = document.getElementById('sel-refund-bank').value;
                if (!refundAccountId) return showToast('يرجى اختيار الحساب البنكي لاسترداد المبلغ', 'error');
            }

            const selectedWhId = document.getElementById('sel-return-warehouse')?.value || currentInvoice.warehouse || 'w-main';
            const selectedWhName = getWarehouseName(selectedWhId) || 'المستودع الرئيسي';

            let subtotal = 0;
            let taxTotal = 0;

            const newItems = returningItems.map(line => {
                let lineSub = line.returnQty * line.price;
                subtotal += lineSub;
                let lineTax = lineSub * (line.taxRate / 100);
                taxTotal += lineTax;
                const factor = parseFloat(line.factor) || 1;

                return {
                    itemId: line.itemId,
                    itemName: line.itemName,
                    unitId: line.unitId,
                    unitName: line.unitName,
                    factor: factor,
                    customFactor: factor,
                    customBaseQty: -(line.returnQty * factor),
                    warehouseId: selectedWhId,
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
            const returnCategory = document.getElementById('sel-return-category')?.value || 'تالف مصنعي / كسر في الشحنة';
            const userReason = document.getElementById('return-reason').value.trim();
            const notesText = userReason ? `مردودات [${returnCategory}]: ${userReason} (أصل الفاتورة ${currentInvId})` : `مردودات [${returnCategory}] لفاتورة المشتريات رقم ${currentInvId}`;
            
            const curUser = (typeof window.getCurrentUserInfo === 'function') ? window.getCurrentUserInfo() : { id: 1, name: 'المسؤول', username: 'admin' };
            const retInvId = 'RET-' + Date.now();
            const retInv = {
                id: retInvId,
                invoiceNo: retInvId,
                isReturn: true,
                originalInvoiceId: currentInvId,
                type: 'purchase',
                customerId: currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId,
                supplierName: currentInvoice.supplierName || getSupplierName(currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId),
                date: new Date().toISOString(),
                warehouse: selectedWhId,
                warehouseName: selectedWhName,
                subtotal: -subtotal,
                taxValue: -taxTotal,
                discountValue: -discountTotal,
                grandTotal: -grand,
                items: newItems,
                status: 'completed',
                returnCategory: returnCategory,
                notes: notesText,
                refundMethod: activeRefundMethod,
                refundAccountId: refundAccountId,
                createdBy: curUser.id,
                createdByName: curUser.fullName || curUser.name,
                createdByUsername: curUser.username
            };

            allPurchases.push(retInv);
            const serializedPurchases = JSON.stringify(allPurchases);
            localStorage.setItem('erp_purchase_invoices_v1', serializedPurchases);
            localStorage.setItem('erp_purchases_v1', serializedPurchases);
            localStorage.setItem('magaies_local_purchase_invoices_v1', serializedPurchases);

            if (window.logSystemAction) {
                window.logSystemAction('استرداد بضاعة / مردودات مشتريات', 'المشتريات', retInvId, `مردودات مشتريات رقم ${retInvId} (${returnCategory}) بقيمة ${fmtNum(grand)} للفاتورة ${currentInvId}`);
            }

            // --- Apply Inventory Effects Properly (Preserve per-warehouse stock object!) ---
            let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            let itemsDbLocal = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
            
            newItems.forEach(retItem => {
                const baseQty = Math.abs(parseFloat(retItem.customBaseQty) || (Math.abs(retItem.quantity) * (parseFloat(retItem.factor) || 1)));
                const invRec = inventory.find(x =>
                    (String(x.itemId || x.id) === String(retItem.itemId)) &&
                    (String(x.warehouseId || x.warehouse) === String(selectedWhId) || String(x.warehouse || x.warehouseId) === String(selectedWhName))
                );
                if (invRec) {
                    if (invRec.qty !== undefined) invRec.qty = Math.max(0, (parseFloat(invRec.qty) || 0) - baseQty);
                    if (invRec.quantity !== undefined) invRec.quantity = Math.max(0, (parseFloat(invRec.quantity) || 0) - baseQty);
                }

                const dbItem = itemsDbLocal.find(i => String(i.id) === String(retItem.itemId));
                if (dbItem) {
                    if (!dbItem.stock || typeof dbItem.stock !== 'object') {
                        const prevScalar = parseFloat(dbItem.stock || dbItem.currentStock || dbItem.quantity || 0) || 0;
                        dbItem.stock = { [selectedWhId]: prevScalar };
                    }
                    dbItem.stock[selectedWhId] = Math.max(0, (parseFloat(dbItem.stock[selectedWhId]) || 0) - baseQty);
                    if (dbItem.actualStock && typeof dbItem.actualStock === 'object') {
                        dbItem.actualStock[selectedWhId] = Math.max(0, (parseFloat(dbItem.actualStock[selectedWhId]) || 0) - baseQty);
                    }
                    const totalRealStock = Object.entries(dbItem.stock)
                        .filter(([k]) => k !== 'PENDING_RECEIPT')
                        .reduce((acc, [, v]) => acc + (parseFloat(v) || 0), 0);
                    dbItem.currentStock = totalRealStock;
                    dbItem.quantity = totalRealStock;
                    if (Array.isArray(dbItem.units) && dbItem.units[0]) {
                        dbItem.units[0].stock = totalRealStock;
                    }
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
            localStorage.setItem('erp_items_v1', JSON.stringify(itemsDbLocal));

            const exRate = parseFloat(currentInvoice.exchangeRate) || parseFloat(currentInvoice.currencyRate) || 1;
            const grandLocal = grand * exRate;

            // --- Apply Financial Effects (Treasury / Bank / Supplier) ---
            if (activeRefundMethod === 'supplier') {
                let suppliers = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
                let custId = currentInvoice.customer || currentInvoice.customerId || currentInvoice.supplierId;
                if (custId && custId !== 'walk-in') {
                    const sIdx = suppliers.findIndex(s => String(s.id) === String(custId));
                    if (sIdx > -1) {
                        suppliers[sIdx].currentBalance = (parseFloat(suppliers[sIdx].currentBalance) || 0) - grandLocal; 
                        localStorage.setItem('erp_suppliers_v1', JSON.stringify(suppliers));
                    }
                }
            } else if (activeRefundMethod === 'cash' || activeRefundMethod === 'bank') {
                let storageKey = activeRefundMethod === 'cash' ? 'erp_safes_v1' : 'erp_banks_v1';
                let safesOrBanks = JSON.parse(localStorage.getItem(storageKey) || '[]');
                let sIdx = safesOrBanks.findIndex(s => String(s.id) === String(refundAccountId));
                if (sIdx > -1) {
                    safesOrBanks[sIdx].currentBalance = (parseFloat(safesOrBanks[sIdx].currentBalance) || 0) + grandLocal;
                    localStorage.setItem(storageKey, JSON.stringify(safesOrBanks));
                }

                let receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1') || '[]');
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

            if (typeof AccountingEngine !== 'undefined' && AccountingEngine.postPurchaseInvoice) {
                let jv = AccountingEngine.postPurchaseInvoice(retInv);
                if (jv) {
                    let storedJVs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
                    jv.description = "قيد " + notesText;
                    storedJVs.push(jv);
                    localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(storedJVs));
                }
            }

            if (typeof window.pushToCloud === 'function') {
                window.pushToCloud(true).catch(e => console.warn("Cloud push failed:", e));
            }

            showToast(`✅ تم حفظ إشعار المرتجع بنجاح برقم ${retInvId}`, 'success');
            
            setTimeout(() => {
                initData();
                showHub();
                switchReturnHubTab('returns');
            }, 900);
        }

        function printReturnDebitNote(retId) {
            const ret = (allPurchases || []).find(r => r && String(r.id || r.invoiceNo) === String(retId));
            if (!ret) return showToast('إشعار المرتجع غير موجود', 'error');
            const supName = ret.supplierName || getSupplierName(ret.customerId);
            const val = Math.abs(parseFloat(ret.grandTotal || 0));
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent('DEBIT-NOTE:' + retId + '|ORIG:' + (ret.originalInvoiceId || '') + '|VAL:' + val)}`;
            const rowsHtml = (ret.items || []).map((it, idx) => `
                <tr>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;">${idx + 1}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;font-weight:700;">${it.itemName || getItemName(it.itemId)}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;">${it.unitName || 'وحدة'}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;font-weight:800;">${Math.abs(parseFloat(it.quantity) || 0)}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;">${fmtNum(it.price || 0)}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;font-weight:800;">${fmtNum(Math.abs(parseFloat(it.total) || 0))}</td>
                </tr>
            `).join('');
            const w = window.open('', '_blank', 'width=850,height=700');
            if (!w) return;
            w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>إشعار خصم مدين / مرتجع مشتريات ${retId}</title>
            <style>body{font-family:Tajawal,Arial,sans-serif;padding:24px;color:#0f172a;} table{width:100%;border-collapse:collapse;margin-top:16px;} @media print{.no-print{display:none;}}</style></head><body>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #e11d48;padding-bottom:14px;">
                <div>
                    <h2 style="margin:0;color:#e11d48;">إشعار خصم مدين / فاتورة مرتجع مشتريات (Debit Note)</h2>
                    <div style="margin-top:6px;font-size:0.92rem;">رقم الإشعار: <strong>${retId}</strong> | الفاتورة الأصلية: <strong>#${ret.originalInvoiceId || '—'}</strong></div>
                    <div style="margin-top:4px;font-size:0.92rem;">المورد: <strong>${supName}</strong> | التاريخ: <strong>${new Date(ret.date).toLocaleString('en-GB')}</strong></div>
                    <div style="margin-top:4px;font-size:0.88rem;color:#475569;">السبب: ${ret.notes || '—'}</div>
                </div>
                <img src="${qrUrl}" alt="QR" style="width:95px;height:95px;border:1px solid #cbd5e1;padding:4px;border-radius:8px;">
            </div>
            <table><thead><tr style="background:#f1f5f9;">
                <th style="padding:8px;border:1px solid #cbd5e1;">#</th>
                <th style="padding:8px;border:1px solid #cbd5e1;text-align:right;">الصنف</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">الوحدة</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">الكمية المرتجعة</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">السعر</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">الإجمالي</th>
            </tr></thead><tbody>${rowsHtml}</tbody></table>
            <div style="margin-top:18px;text-align:left;font-size:1.15rem;font-weight:900;color:#e11d48;">صافي القيمة المخصومة على المورد: ${fmtNum(val)} SDG</div>
            <div class="no-print" style="margin-top:20px;text-align:center;"><button onclick="window.print()" style="padding:10px 24px;background:#e11d48;color:#fff;border:none;border-radius:8px;font-weight:800;cursor:pointer;">🖨️ طباعة الآن</button></div>
            </body></html>`);
            w.document.close();
        }

        function sendReturnWhatsApp(retId) {
            const ret = (allPurchases || []).find(r => r && String(r.id || r.invoiceNo) === String(retId));
            if (!ret) return;
            const supName = ret.supplierName || getSupplierName(ret.customerId);
            const supObj = (suppliersDb || []).find(s => String(s.id) === String(ret.customerId) || s.name === supName);
            const phone = (supObj?.phone || supObj?.mobile || '').replace(/[^0-9]/g, '');
            const val = fmtNum(Math.abs(parseFloat(ret.grandTotal || 0)));
            const itemsTxt = (ret.items || []).map(i => `• ${i.itemName || getItemName(i.itemId)}: ${Math.abs(parseFloat(i.quantity) || 0)} ${i.unitName || ''}`).join('\n');
            const msg = `📋 *إشعار مرتجع مشتريات / خصم مدين*\nرقم الإشعار: *${retId}*\nالفاتورة الأصلية: *#${ret.originalInvoiceId}*\nالسادة / *${supName}*\n\nالأصناف المرتجعة:\n${itemsTxt}\n\n💰 إجمالي القيمة المستردة: *${val} SDG*\n📝 السبب: ${ret.notes || '—'}`;
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
        }

        function reversePurchaseReturn(retId) {
            const retIdx = (allPurchases || []).findIndex(r => r && String(r.id || r.invoiceNo) === String(retId));
            if (retIdx === -1) return;
            if (!confirm(`هل أنت متأكد من إلغاء وعكس إشعار المرتجع رقم ${retId} وإعادة الكميات للمخزن؟`)) return;
            const ret = allPurchases[retIdx];
            const whId = ret.warehouse || 'w-main';
            const whName = ret.warehouseName || getWarehouseName(whId) || 'المستودع الرئيسي';

            let inventory = JSON.parse(localStorage.getItem('erp_inventory_v1') || '[]');
            let itemsDbLocal = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
            (ret.items || []).forEach(retItem => {
                const baseQty = Math.abs(parseFloat(retItem.customBaseQty) || (Math.abs(retItem.quantity) * (parseFloat(retItem.factor) || 1)));
                const invRec = inventory.find(x =>
                    (String(x.itemId || x.id) === String(retItem.itemId)) &&
                    (String(x.warehouseId || x.warehouse) === String(whId) || String(x.warehouse || x.warehouseId) === String(whName))
                );
                if (invRec) {
                    if (invRec.qty !== undefined) invRec.qty = (parseFloat(invRec.qty) || 0) + baseQty;
                    if (invRec.quantity !== undefined) invRec.quantity = (parseFloat(invRec.quantity) || 0) + baseQty;
                }
                const dbItem = itemsDbLocal.find(i => String(i.id) === String(retItem.itemId));
                if (dbItem) {
                    if (!dbItem.stock || typeof dbItem.stock !== 'object') dbItem.stock = {};
                    dbItem.stock[whId] = (parseFloat(dbItem.stock[whId]) || 0) + baseQty;
                    const totalRealStock = Object.entries(dbItem.stock)
                        .filter(([k]) => k !== 'PENDING_RECEIPT')
                        .reduce((acc, [, v]) => acc + (parseFloat(v) || 0), 0);
                    dbItem.currentStock = totalRealStock;
                    dbItem.quantity = totalRealStock;
                }
            });
            localStorage.setItem('erp_inventory_v1', JSON.stringify(inventory));
            localStorage.setItem('erp_items_v1', JSON.stringify(itemsDbLocal));

            allPurchases[retIdx].deleted = true;
            const ser = JSON.stringify(allPurchases);
            localStorage.setItem('erp_purchase_invoices_v1', ser);
            localStorage.setItem('erp_purchases_v1', ser);
            localStorage.setItem('magaies_local_purchase_invoices_v1', ser);
            if (window.logSystemAction) {
                window.logSystemAction('إلغاء مرتجع مشتريات', 'المشتريات', retId, `تم إلغاء وعكس إشعار المرتجع رقم ${retId}`);
            }
            showToast(`✅ تم عكس وإلغاء إشعار المرتجع ${retId} بنجاح`, 'success');
            initData();
        }

    </script>
</body>
</html>
