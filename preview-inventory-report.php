<?php
// preview-inventory-report.php
// Universal Studio Preview Page for ALL Inventory Reports
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&#x0627;&#x0633;&#x062A;&#x0648;&#x062F;&#x064A;&#x0648; &#x0645;&#x0639;&#x0627;&#x064A;&#x0646;&#x0629; &#x0648;&#x062A;&#x0635;&#x0645;&#x064A;&#x0645; &#x0627;&#x0644;&#x062A;&#x0642;&#x0631;&#x064A;&#x0631; | MAGAIEES ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;800&family=Almarai:wght@400;700;800&family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@400;600;800;900&family=Changa:wght@400;600;800&family=Tajawal:wght@400;500;700;800;900&family=JetBrains+Mono:wght@400;600;800&family=Roboto+Mono:wght@400;600;800&family=Fira+Code:wght@400;600;800&family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style id="dynamic-paper-style">
        @page { size: A4 landscape; margin: 10mm; }
    </style>

    <style>
        :root {
            --bg-page: #f1f5f9;
            --text-color: #0f172a;
            --ribbon-bg: #1e293b;
            --ribbon-text: #f8fafc;
            --table-border: #cbd5e1;
            --table-header-bg: #e2e8f0;
            --table-bg: #ffffff;
            --num-font-family: 'Inter', sans-serif;
        }
        body.dark-mode {
            --bg-page: #0b0f19;
            --text-color: #f8fafc;
            --ribbon-bg: #090d16;
            --ribbon-text: #f8fafc;
            --table-border: #334155;
            --table-header-bg: #1e293b;
            --table-bg: #0f172a;
        }

        /* Themes */
        table.excel-table.theme-gold th { background: #b45309 !important; color: #ffffff !important; border-color: #78350f !important; }
        table.excel-table.theme-gold td { border-color: #fde68a !important; }
        table.excel-table.theme-lightblue th { background: #0284c7 !important; color: #ffffff !important; border-color: #0369a1 !important; }
        table.excel-table.theme-lightblue td { border-color: #bae6fd !important; }
        table.excel-table.theme-slate th { background: #475569 !important; color: #ffffff !important; border-color: #334155 !important; }
        table.excel-table.theme-slate td { border-color: #cbd5e1 !important; }
        table.excel-table.theme-classic-navy th { background: #1e3a8a !important; color: #ffffff !important; border-color: #172554 !important; }
        table.excel-table.theme-classic-navy td { border-color: #dbeafe !important; }
        table.excel-table.theme-emerald-pro th { background: #065f46 !important; color: #ffffff !important; border-color: #022c22 !important; }
        table.excel-table.theme-emerald-pro td { border-color: #d1fae5 !important; }
        table.excel-table.theme-modern-slate th { background: #334155 !important; color: #ffffff !important; border-color: #0f172a !important; }
        table.excel-table.theme-modern-slate td { border-color: #e2e8f0 !important; }
        table.excel-table.theme-luxury-gold th { background: #78350f !important; color: #ffffff !important; border-color: #451a03 !important; }
        table.excel-table.theme-luxury-gold td { border-color: #fef3c7 !important; }
        table.excel-table.theme-luxury-black th { background: #0a0a0a !important; color: #f5f5f5 !important; border-color: #000000 !important; font-weight: 900 !important; }
        table.excel-table.theme-luxury-black td { border-color: #1a1a1a !important; color: #171717 !important; font-weight: 600 !important; }
        table.excel-table.theme-luxury-black tbody tr:nth-child(even) { background: #fafafa !important; }
        table.excel-table.theme-luxury-black tbody tr:nth-child(odd) { background: #ffffff !important; }
        table.excel-table.theme-luxury-black tfoot td { background: #171717 !important; color: #f5f5f5 !important; font-weight: 900 !important; }

        /* Fonts */
        .font-newsons { font-family: 'JetBrains Mono', monospace !important; }
        .font-ribot { font-family: 'Roboto Mono', monospace !important; }
        .font-firacode { font-family: 'Fira Code', monospace !important; }
        .font-ultra { font-family: 'Inter', sans-serif !important; font-weight: 400 !important; font-variant-numeric: tabular-nums !important; }
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif !important; font-variant-numeric: tabular-nums !important; }
        .font-roboto { font-family: 'Roboto', sans-serif !important; font-variant-numeric: tabular-nums !important; }
        .font-outfit { font-family: 'Outfit', sans-serif !important; font-variant-numeric: tabular-nums !important; }
        .font-tajawal { font-family: 'Tajawal', sans-serif !important; font-weight: 400 !important; font-variant-numeric: tabular-nums !important; }
        .font-cairo { font-family: 'Cairo', sans-serif !important; font-weight: 400 !important; font-variant-numeric: tabular-nums !important; }

        .global-sidebar, header.premium-header, .global-header, .navbar { display: none !important; }

        body {
            margin: 0; padding: 0;
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-color);
            transition: background 0.3s, color 0.3s;
        }

        /* Ribbon */
        .ribbon {
            position: sticky; top: 0; z-index: 1000;
            background: var(--ribbon-bg); color: var(--ribbon-text);
            padding: 5px 14px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            border-bottom: 2px solid #0284c7;
            flex-wrap: nowrap; gap: 8px; overflow-x: auto;
        }
        .ribbon-btn, .preset-btn { position: relative; overflow: hidden; background: linear-gradient(135deg, color-mix(in srgb, var(--btn-color, #fff) 8%, rgba(255,255,255,0.05)), rgba(0,0,0,0.2)); background-image: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.07) 1px, transparent 1px), linear-gradient(135deg, color-mix(in srgb, var(--btn-color, #fff) 10%, rgba(255,255,255,0.05)), rgba(0,0,0,0.2)); background-size: 3px 3px, auto; border: 1px solid color-mix(in srgb, var(--btn-color, #fff) 40%, rgba(255,255,255,0.2)); backdrop-filter: blur(12px) saturate(150%); color: #f8fafc; padding: 4px 12px; border-radius: 10px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); font-family: inherit; font-size: 0.8rem; height: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 1; white-space: nowrap; flex-shrink: 0; }
        .ribbon-btn::before, .preset-btn::before { content: ''; position: absolute; inset: 0; border-radius: inherit; padding: 2px; background: linear-gradient(135deg, var(--btn-color, rgba(255,255,255,0.8)), transparent 40%, var(--btn-color, rgba(255,255,255,0.5))); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; opacity: 0.9; transition: opacity 0.4s; }
        .ribbon-btn::after, .preset-btn::after { content: ''; position: absolute; top: 0; left: -100%; width: 75%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.65), transparent); transform: skewX(-25deg); animation: luxurySweep 4s cubic-bezier(0.4, 0, 0.2, 1) infinite; z-index: -1; }
        .ribbon-btn:hover, .preset-btn:hover:not(.active) { transform: translateY(-2px); border-color: var(--btn-color, #fff); box-shadow: 0 6px 16px rgba(0,0,0,0.3); color: #fff !important; text-shadow: 0 0 8px rgba(255,255,255,0.6); }
        .ribbon-btn:active, .preset-btn:active { transform: translateY(0); }
        .preset-btn.active { background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.3)); border-color: rgba(16,185,129,0.4); color: #ffffff !important; --btn-color: #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.3), 0 0 15px rgba(16,185,129,0.5); animation: none; }
        .preset-btn.active::before { opacity: 1; background: linear-gradient(135deg, #10b981, transparent); }
        @keyframes luxurySweep { 0% { left: -100%; } 20% { left: 200%; } 100% { left: 200%; } }
        .preset-btn.is-pressing { transform: scale(0.96) translateY(1px) !important; box-shadow: 0 0 16px rgba(16, 185, 129, 0.9) !important; border-color: #10b981 !important; }

        :root.light-theme .ribbon-btn, :root.light-theme .preset-btn { background: linear-gradient(135deg, #ffffff, #f8fafc); color: #0f172a; border-color: rgba(0,0,0,0.08); }
        :root.light-theme .ribbon-btn:hover, :root.light-theme .preset-btn:hover:not(.active) { background: #ffffff; border-color: rgba(0,0,0,0.15); color: #000 !important; text-shadow: none; }

        /* Print Area */
        .print-area {
            padding: 35px 40px; margin: 25px auto 50px auto;
            background: var(--table-bg); color: var(--text-color);
            border: 1px solid var(--table-border); border-radius: 4px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            transition: all 0.3s ease; box-sizing: border-box; position: relative;
        }
        body.dark-mode .print-area { border-color: #334155; box-shadow: 0 12px 40px rgba(0,0,0,0.6); }
        .print-area.paper-portrait { max-width: 1000px; width: 94%; }
        .print-area.paper-landscape { max-width: 98%; width: 98%; }
        .print-area::before {
            content: '\ud83d\udcc4 \u0648\u0631\u0642\u0629 \u0637\u0628\u0627\u0639\u0629 \u0631\u0633\u0645\u064a\u0629 (\u0645\u0639\u0627\u064a\u0646\u0629)';
            position: absolute; top: -12px; right: 25px;
            background: var(--ribbon-bg); color: var(--ribbon-text);
            padding: 3px 12px; border-radius: 12px; font-size: 0.72rem; font-weight: 800;
        }

        /* Company Header */
        .company-header { display: flex; justify-content: space-between; align-items: flex-start; height: 3.5cm; border-bottom: 3px solid var(--text-color); margin-bottom: 15px; padding-bottom: 10px; }
        .company-logo-wrap { width: 3.5cm; height: 100%; border: 1px solid var(--table-border); display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; }
        .company-logo-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* Table */
        table.excel-table { width: 100%; border-collapse: collapse; table-layout: auto; background: var(--table-bg); color: inherit; }
        table.excel-table th, table.excel-table td { border: 1px solid var(--table-border); padding: 7px 10px; text-align: center; font-size: 0.9rem; color: inherit; white-space: nowrap !important; }
        table.excel-table th { background: var(--table-header-bg); font-weight: 800; cursor: pointer; user-select: none; }
        table.excel-table th:hover { background: rgba(56,189,248,0.25); }
        table.excel-table th.selected-col { background: #bae6fd !important; color: #0369a1 !important; border-bottom: 3px solid #0284c7 !important; }
        body.dark-mode table.excel-table th.selected-col { background: #0369a1 !important; color: #ffffff !important; }
        table.excel-table tfoot td { font-weight: 900; background: var(--table-header-bg); }

        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .ribbon, .settings-panel, .print-area::before, #global-print-header { display: none !important; }
            body { background: #ffffff !important; color: #0f172a !important; margin: 0 !important; padding: 0 !important; }
            .print-area { background: #ffffff !important; padding: 0 !important; margin: 0 !important; border: none !important; box-shadow: none !important; max-width: 100% !important; width: 100% !important; }
            table.excel-table { width: 100% !important; }
        }

        /* Toasts */
        #studio-toast-container { position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
    </style>
<style>
/* Ensure content scrolls under sticky ribbon */
body { padding-top: 0 !important; }
.settings-panel { position: sticky; top: 0; z-index: 1200; }
.preview-content, .report-body, .preview-main, #preview-content, #report-body {
    scroll-margin-top: 100px;
}
</style>
<!-- sticky-ribbon-offset -->
</head>
<body>

<!-- Luxury Ribbon Toolbar -->
<div class="ribbon no-print">
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div id="presets-container" style="display:flex; gap:8px; flex-wrap: wrap;">
            <!-- Preset buttons rendered via JS -->
        </div>
    </div>

    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <input type="text" id="live-search-input" placeholder="&#x1F50D; &#x0628;&#x062D;&#x062B; &#x0633;&#x0631;&#x064A;&#x0639;..." oninput="filterTableRows(this.value)" style="padding: 4px 10px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.3); color: #fff; font-family: inherit; font-size: 0.8rem; outline: none; width: 120px; height: 30px;">

        <button class="ribbon-btn" onclick="executePrintWithPermission()" style="--btn-color: #0ea5e9;">
            <span style="font-size: 1.05rem;">&#x1F5A8;&#xFE0F;</span> &#x0637;&#x0628;&#x0627;&#x0639;&#x0629;
        </button>
        <button class="ribbon-btn" onclick="toggleSettingsPanel()" style="--btn-color: #eab308;">
            <span style="font-size: 1.05rem;">&#x2699;&#xFE0F;</span> &#x0625;&#x0639;&#x062F;&#x0627;&#x062F;&#x0627;&#x062A; &#x0627;&#x0644;&#x0623;&#x0639;&#x0645;&#x062F;&#x0629;
        </button>
        <button class="ribbon-btn" onclick="toggleDarkMode()" style="--btn-color: #a855f7;">
            <span style="font-size: 1.05rem;">&#x1F319;</span> &#x0639;&#x0643;&#x0633; &#x0627;&#x0644;&#x0623;&#x0644;&#x0648;&#x0627;&#x0646;
        </button>
        <button class="ribbon-btn" onclick="window.close(); window.history.back();" style="--btn-color: #ef4444;">
            <span style="font-size: 1.05rem;">&#x274C;</span> &#x0625;&#x063A;&#x0644;&#x0627;&#x0642; / &#x0631;&#x062C;&#x0648;&#x0639;
        </button>
    </div>
</div>

<!-- Studio Settings Panel -->
<?php include 'includes/studio-settings-panel.php'; ?>

<!-- Print Area -->
<div class="print-area paper-landscape" id="print-area-wrapper">
    <div id="print-top-bar" class="print-meta-bar" style="display:none;"></div>
    
    <div class="company-header report-header" id="main-company-header">
        <div id="company-info-block" style="flex: 1; text-align: right; color: var(--text-color);">
            <h2 id="hdr-c-name" style="margin: 0 0 4px 0; font-size: 1.5rem; font-weight: 900;"></h2>
            <p id="hdr-c-reg" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-vat" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-phone" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-addr" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
        </div>
        <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
            <h2 style="margin: 0; padding: 8px 22px; background: rgba(0,0,0,0.04); border-radius: 8px; border: 2px solid var(--text-color); font-size: 1.5rem; font-weight: 900;" id="report-main-title">&#x062A;&#x0642;&#x0631;&#x064A;&#x0631; &#x0627;&#x0644;&#x0645;&#x062E;&#x0627;&#x0632;&#x0646;</h2>
        </div>
        <div id="company-logo-block" style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
            <div class="company-logo-wrap">
                <img id="company-logo" src="" alt="" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    <div id="report-subheader-row" style="display: flex; justify-content: space-between; align-items: center; margin: 12px 0 16px 0; padding: 10px 18px; background: rgba(0,0,0,0.02); border-radius: 8px; border: 1px solid rgba(0,0,0,0.12); font-family: 'Tajawal', sans-serif;">
        <div style="flex: 1; text-align: right;">
            <span id="report-customer-name-lbl" style="font-size: 1.4rem; font-weight: 900; color: var(--text-color);"></span>
        </div>
        <div style="flex: 1; text-align: center;">
            <span id="report-date-lbl" style="font-size: 1.4rem; font-weight: 900; color: var(--text-color);"></span>
        </div>
        <div style="flex: 1; text-align: left;">
            <span id="report-period-lbl" style="font-size: 1.15rem; font-weight: 800; color: var(--text-color);"></span>
        </div>
    </div>

    <!-- Table injected from sessionStorage -->
    <div id="preview-table-container">
        <table class="excel-table font-ultra" id="preview-table">
            <thead id="preview-thead"></thead>
            <tbody id="preview-tbody"></tbody>
            <tfoot id="preview-tfoot"></tfoot>
        </table>
    </div>
    
    <div id="print-footer-bar" class="print-meta-bar print-meta-footer" style="display:none;"></div>
</div>

<!-- Modals -->
<?php include 'includes/studio-modals.php'; ?>

<script src="js/localforage.min.js"></script>
<script src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>

<script>
// ── Preview Inventory Report Engine ──
const PRESETS_STORAGE_KEY = 'inv_preview_presets_v2';
const MAX_PRESETS_LIMIT = 7;
let currentData = [];
let filteredData = [];
let currentPresetIndex = 0;
let selectedColIndex = null;
let currentTheme = '';
let currentFont = 'font-ultra';
let currentPaperSize = 'A4';
let currentOrientation = 'landscape';
let showHeaderLogo = true;
let showHeaderInfo = true;
let isLongPressTriggered = false;
let longPressInterval = null;

let baseColumns = [];
let columnOrder = [];
let columnSettings = {};
let presets = [];

document.addEventListener('DOMContentLoaded', function() {
    loadTableFromSession();
    loadCompanyHeader();
    loadPresets();
    renderPresets();
    applyPresetByIndex(0);
});

function loadTableFromSession() {
    var tableHtml = sessionStorage.getItem('urs_preview_table_html');
    var title = sessionStorage.getItem('urs_preview_title') || '\u062a\u0642\u0631\u064a\u0631 \u0627\u0644\u0645\u062e\u0627\u0632\u0646';
    var filters = [];
    try { filters = JSON.parse(sessionStorage.getItem('urs_preview_filters') || '[]'); } catch(e){}

    document.getElementById('report-main-title').textContent = title;

    var now = new Date();
    document.getElementById('report-date-lbl').textContent = now.toLocaleDateString('ar-SD');

    if (filters.length > 0) {
        var filterText = filters.map(function(f) { return (f.label ? f.label + ': ' : '') + f.value; }).join(' | ');
        document.getElementById('report-period-lbl').textContent = filterText;
    }

    if (tableHtml) {
        var container = document.getElementById('preview-table-container');
        container.innerHTML = tableHtml;
        var table = container.querySelector('table');
        if (table) {
            table.classList.add('excel-table', 'font-ultra');
            table.id = 'preview-table';
            // Remove action columns & buttons
            table.querySelectorAll('button, .btn-action, .btn-link').forEach(function(el) { el.remove(); });
        }
        discoverColumns();
    }
}

function discoverColumns() {
    baseColumns = [];
    var table = document.getElementById('preview-table');
    if (!table) return;
    var thead = table.querySelector('thead');
    if (!thead) return;
    var headerRow = thead.querySelector('tr:last-child') || thead.querySelector('tr');
    if (!headerRow) return;
    var ths = headerRow.querySelectorAll('th');
    ths.forEach(function(th, idx) {
        baseColumns.push({
            idx: idx,
            defaultLabel: th.textContent.trim(),
            field: 'col_' + idx,
            isNum: false,
            visible: true,
            label: th.textContent.trim(),
            align: 'center', width: '', fs: 0.9, fontWeight: '800',
            fontFamily: 'inherit', color: '', bgColor: ''
        });
        columnOrder.push(idx);
    });
}

function loadCompanyHeader() {
    try {
        var cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
        var user = JSON.parse(localStorage.getItem('user') || '{}');
        var cn = cfg.companyName || user.company_name || localStorage.getItem('companyName') || '\u0645\u0624\u0633\u0633\u0629 \u0645\u0642\u0627\u064a\u064a\u0633 \u0644\u0644\u0623\u0639\u0645\u0627\u0644';
        document.getElementById('hdr-c-name').textContent = cn;
        document.getElementById('hdr-c-reg').textContent = cfg.regNumber ? '\u0633.\u062a: ' + cfg.regNumber : '';
        document.getElementById('hdr-c-vat').textContent = cfg.taxNumber ? '\u0631.\u0636: ' + cfg.taxNumber : '';
        document.getElementById('hdr-c-phone').textContent = cfg.phone ? '\u0647\u0627\u062a\u0641: ' + cfg.phone : '';
        document.getElementById('hdr-c-addr').textContent = cfg.address || '';
        var logo = cfg.logoBase64 || localStorage.getItem('company_logo_base64') || user.logo || '';
        if (logo) document.getElementById('company-logo').src = logo;
    } catch(e){}
}

// ── Presets ──
function loadPresets() {
    try {
        var raw = localStorage.getItem(PRESETS_STORAGE_KEY);
        if (raw) presets = JSON.parse(raw);
    } catch(e) {}
    if (!presets || presets.length === 0) {
        presets = [
            { name: '\u0627\u0641\u062a\u0631\u0627\u0636\u064a 1', settings: null },
            { name: '\u062a\u062e\u0635\u064a\u0635 2', settings: null },
            { name: '\u062a\u062e\u0635\u064a\u0635 3', settings: null }
        ];
    }
}

function renderPresets() {
    var container = document.getElementById('presets-container');
    if (!container) return;
    container.innerHTML = '';
    presets.forEach(function(p, i) {
        var btn = document.createElement('button');
        btn.className = 'preset-btn' + (i === currentPresetIndex ? ' active' : '');
        btn.setAttribute('data-preset', i);
        btn.innerHTML = '<span style="font-size:1rem;">' + (p.settings ? '\ud83d\udd12' : '\ud83d\udccc') + '</span> ' + p.name;
        btn.style.cssText = '--btn-color: ' + (i === currentPresetIndex ? '#10b981' : '#94a3b8') + ';';
        btn.addEventListener('click', function() { applyPresetByIndex(i); });
        container.appendChild(btn);
    });
}

function applyPresetByIndex(idx) {
    currentPresetIndex = idx;
    var p = presets[idx];
    if (p && p.settings) {
        if (p.settings.columns) {
            p.settings.columns.forEach(function(cs) {
                var col = baseColumns.find(function(c) { return c.idx === cs.idx; });
                if (col) Object.assign(col, cs);
            });
        }
    }
    applyColumnSettings();
    renderPresets();
    populateStudioControls();
}

function applyColumnSettings() {
    var table = document.getElementById('preview-table');
    if (!table) return;
    var rows = table.querySelectorAll('tr');
    baseColumns.forEach(function(col) {
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('th, td');
            if (cells[col.idx]) {
                cells[col.idx].style.display = col.visible ? '' : 'none';
                if (col.visible) {
                    if (col.align) cells[col.idx].style.textAlign = col.align;
                    if (col.width) cells[col.idx].style.width = col.width + '%';
                    if (col.fs) cells[col.idx].style.fontSize = col.fs + 'rem';
                    if (col.fontWeight) cells[col.idx].style.fontWeight = col.fontWeight;
                    if (col.fontFamily && col.fontFamily !== 'inherit') cells[col.idx].style.fontFamily = col.fontFamily;
                    if (col.color) cells[col.idx].style.color = col.color;
                }
            }
        });
    });
}

function populateStudioControls() {
    var colSelect = document.getElementById('selected-col-select');
    if (!colSelect) return;
    colSelect.innerHTML = '';
    baseColumns.forEach(function(col) {
        var opt = document.createElement('option');
        opt.value = col.idx;
        opt.textContent = (col.visible ? '' : '\u274c ') + (col.label || col.defaultLabel);
        colSelect.appendChild(opt);
    });
    var chipsContainer = document.getElementById('studio-columns-chips-container');
    if (chipsContainer) {
        chipsContainer.innerHTML = '';
        baseColumns.forEach(function(col) {
            var chip = document.createElement('label');
            chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:8px;font-size:0.78rem;font-weight:800;cursor:pointer;border:1px solid ' + (col.visible ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.3)') + ';background:' + (col.visible ? 'rgba(16,185,129,0.08)' : 'rgba(239,68,68,0.06)') + ';color:' + (col.visible ? '#10b981' : '#ef4444') + ';';
            var cb = document.createElement('input');
            cb.type = 'checkbox'; cb.checked = col.visible; cb.style.cssText = 'cursor:pointer;';
            cb.addEventListener('change', function() { col.visible = this.checked; applyColumnSettings(); populateStudioControls(); });
            chip.appendChild(cb);
            chip.appendChild(document.createTextNode(col.label || col.defaultLabel));
            chipsContainer.appendChild(chip);
        });
    }
    if (selectedColIndex !== null) selectColumn(selectedColIndex);
    else if (baseColumns.length > 0) selectColumn(0);
}

// ── Global Functions for studio-settings-panel.php ──
window.toggleSettingsPanel = function() {
    var panel = document.getElementById('settings-panel');
    if (panel) { panel.classList.toggle('show'); if (panel.classList.contains('show')) populateStudioControls(); }
};
window.switchStudioTab = function(tab) {
    ['cols','paper','presets'].forEach(function(t) {
        var el = document.getElementById('studio-tab-' + t);
        var oldBtn = document.getElementById('tab-btn-' + t);
        var newBtn = document.getElementById('sr-tab-' + t);
        if (el) el.style.display = (t === tab) ? 'block' : 'none';
        if (oldBtn) oldBtn.classList.toggle('active', t === tab);
        if (newBtn) newBtn.classList.toggle('active', t === tab);
    });
};
window.selectColumn = function(idx) {
    selectedColIndex = idx;
    var col = baseColumns.find(function(c) { return c.idx === idx; });
    if (!col) return;
    var el;
    el = document.getElementById('selected-col-select'); if (el) el.value = idx;
    el = document.getElementById('col-visible-chk'); if (el) el.checked = col.visible;
    el = document.getElementById('col-label-input'); if (el) el.value = col.label || col.defaultLabel;
    el = document.getElementById('col-align-select'); if (el) el.value = col.align || 'center';
    el = document.getElementById('col-width-input'); if (el) el.value = col.width || '';
    el = document.getElementById('col-width-range'); if (el) el.value = col.width || 15;
    el = document.getElementById('col-fs-range'); if (el) el.value = col.fs || 0.9;
    el = document.getElementById('col-fs-val'); if (el) el.textContent = (col.fs || 0.9) + 'rem';
};
window.updateSelectedCol = function(prop, value) {
    if (selectedColIndex === null) return;
    var col = baseColumns.find(function(c) { return c.idx === selectedColIndex; });
    if (!col) return;
    col[prop] = value;
    if (prop === 'fs') { var v = document.getElementById('col-fs-val'); if(v) v.textContent = value+'rem'; }
    applyColumnSettings();
    if (prop === 'visible' || prop === 'label') populateStudioControls();
};
window.setAllColumnsVisibility = function(v) { baseColumns.forEach(function(c){c.visible=v;}); applyColumnSettings(); populateStudioControls(); };
window.resetDefaultColumnOrder = function() { baseColumns.forEach(function(c){ c.visible=true; c.label=c.defaultLabel; c.align='center'; c.width=''; c.fs=0.9; c.fontWeight='800'; c.fontFamily='inherit'; c.color=''; c.bgColor=''; }); applyColumnSettings(); populateStudioControls(); };
window.restoreHiddenColumn = function(val) { if(!val&&val!==0) return; var c=baseColumns.find(function(x){return x.idx===parseInt(val);}); if(c){c.visible=true; applyColumnSettings(); populateStudioControls();} };
window.moveSelectedCol = function(dir) {};
window.setTableWidth = function(val) { var t=document.getElementById('preview-table'); if(t) t.style.width=(val||100)+'%'; var l=document.getElementById('table-width-label'); if(l) l.textContent=(val||100)+'%'; };
window.setTableTheme = function(theme) { var t=document.getElementById('preview-table'); if(!t) return; t.className=t.className.replace(/theme-\S+/g,'').trim(); if(theme) t.classList.add(theme); };
window.setLayoutTheme = function(v) {};
window.setNumberFont = function(f) { var t=document.getElementById('preview-table'); if(!t) return; ['font-newsons','font-ribot','font-firacode','font-ultra','font-jakarta','font-roboto','font-outfit','font-tajawal','font-cairo'].forEach(function(c){t.classList.remove(c);}); if(f) t.classList.add(f); };
window.toggleHeaderElement = function(type, checked) { if(type==='logo') document.getElementById('company-logo-block').style.display = checked?'':'none'; if(type==='info') document.getElementById('company-info-block').style.display = checked?'':'none'; };
window.setPaperSize = function(size) {
    var el = document.getElementById('dynamic-paper-style');
    var orient = currentOrientation || 'landscape';
    if(el) el.textContent = '@page { size: ' + size + ' ' + orient + '; margin: 10mm; }';
    currentPaperSize = size;
};
window.setPageOrientation = function(orient) {
    currentOrientation = orient;
    var wrapper = document.getElementById('print-area-wrapper');
    if(wrapper) { wrapper.classList.remove('paper-portrait','paper-landscape'); wrapper.classList.add('paper-' + orient); }
    var el = document.getElementById('dynamic-paper-style');
    if(el) el.textContent = '@page { size: ' + (currentPaperSize||'A4') + ' ' + orient + '; margin: 10mm; }';
};
window.filterTableRows = function(q) { var t=document.getElementById('preview-table'); if(!t)return; q=(q||'').toLowerCase(); t.querySelectorAll('tbody tr').forEach(function(r){r.style.display=(!q||r.textContent.toLowerCase().includes(q))?'':'none';}); };
window.saveCurrentPreset = function() {
    presets[currentPresetIndex].settings = { columns: baseColumns.map(function(c){return{idx:c.idx,label:c.label,visible:c.visible,align:c.align,width:c.width,fs:c.fs,fontWeight:c.fontWeight,fontFamily:c.fontFamily,color:c.color,bgColor:c.bgColor};}) };
    localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets));
    renderPresets();
    alert('\u2705 \u062a\u0645 \u062d\u0641\u0638 \u0627\u0644\u062a\u0635\u0645\u064a\u0645 \u0628\u0646\u062c\u0627\u062d!');
};
window.addNewPresetPrompt = function() { if(presets.length>=7) return; presets.push({name:'\u0642\u0627\u0644\u0628 '+(presets.length+1),settings:null}); localStorage.setItem(PRESETS_STORAGE_KEY, JSON.stringify(presets)); renderPresets(); };
window.executePrintWithPermission = function() { window.print(); };
window.toggleDarkMode = function() { document.body.classList.toggle('dark-mode'); };
window.renderTable = function() { applyColumnSettings(); };
</script>
<script src="js/universal-report-studio.js?v=<?php echo time(); ?>"></script>
</body>
</html>