<?php $page_title = 'مقاييس - طباعة الباركود'; ?>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مقاييس - طباعة الباركود</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        .barcode-container { padding: 20px; padding-bottom: 100px; display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        @media (max-width: 768px) { .barcode-container { grid-template-columns: 1fr; } }
        
        .print-preview {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            min-height: 400px;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 10px;
            overflow-y: auto;
            max-height: 600px;
        }

        .barcode-sticker {
            width: 140px; /* 38x25 mm approx */
            height: 90px;
            border: 1px dashed #ccc;
            padding: 5px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            color: #000;
        }

        .barcode-sticker .company-name { font-size: 0.65rem; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .barcode-sticker .item-name { font-size: 0.65rem; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .barcode-sticker .item-price { font-size: 0.8rem; font-weight: 900; }
        .barcode-sticker svg { max-width: 100%; height: auto; margin: auto; }

        @media print {
            body * { visibility: hidden; }
            .print-preview, .print-preview * { visibility: visible; }
            .print-preview { position: absolute; left: 0; top: 0; padding: 0; width: 100%; max-height: none; overflow: visible; background: transparent; }
            .barcode-sticker { border: none; page-break-inside: avoid; }
            nav, .control-panel { display: none !important; }
        }
    
        
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

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <nav class="navbar glass-panel">
        <button class="back-btn" onclick="history.back()">➞</button>
        <h1 class="brand-title">طباعة ملصقات الباركود</h1>
        <div style="width: 30px;"></div>
    </nav>

    <div class="barcode-container">
        <!-- Controls -->
        <div class="control-panel md-card glass-panel">
            <h3 style="color:var(--primary-accent); margin-top:0;">إعدادات الطباعة</h3>
            
            <div class="input-group">
                <label>اختر الصنف</label>
                <select id="item-select" onchange="generatePreview()">
                    <option value="">-- اختر صنفاً --</option>
                </select>
            </div>

            <div class="input-group">
                <label>عدد الملصقات المطلوبة</label>
                <input type="number" id="label-count" value="1" min="1" max="100" onchange="generatePreview()">
            </div>

            <div class="input-group">
                <label>نوع الورق</label>
                <select id="paper-type" onchange="generatePreview()">
                    <option value="a4">ورق عادي (A4)</option>
                    <option value="thermal">طابعة ملصقات حرارية (38x25 mm)</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top: 20px;">
                <button class="btn-save" style="flex:1;" onclick="window.print()">🖨️ طباعة الآن</button>
                <button class="btn-cancel" style="flex:1;" onclick="clearPreview()">مسح الكل</button>
            </div>
        </div>

        <!-- Preview -->
        <div class="preview-panel md-card glass-panel">
            <h3 style="margin-top:0;">المعاينة</h3>
            <div class="print-preview" id="print-preview">
                <div style="width:100%; text-align:center; color:var(--text-secondary); margin-top:100px;">
                    يرجى اختيار صنف لمعاينة ملصقات الباركود
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script defer src="js/barcode-printer.js"></script>
</body>
</html>

