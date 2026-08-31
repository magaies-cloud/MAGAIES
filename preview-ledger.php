<?php
// preview-balances-design.php
// Excel-Style Preview Page for Balances - Pro Edition with Studio Tabs & Page Setup
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة وتصميم الأرصدة | Al-Sajana ERP</title>
    <!-- Professional Arabic & Number Fonts Suite -->
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

        table.excel-table.theme-luxury-black th { background: #0a0a0a !important; color: #f5f5f5 !important; border-color: #000000 !important; font-weight: 900 !important; text-shadow: 0 1px 2px rgba(0,0,0,0.5); letter-spacing: 0.3px; }
        table.excel-table.theme-luxury-black td { border-color: #1a1a1a !important; color: #171717 !important; font-weight: 600 !important; }
        table.excel-table.theme-luxury-black tbody tr:nth-child(even) { background: #fafafa !important; }
        table.excel-table.theme-luxury-black tbody tr:nth-child(odd) { background: #ffffff !important; }
        table.excel-table.theme-luxury-black tfoot td { background: #171717 !important; color: #f5f5f5 !important; font-weight: 900 !important; border-color: #000000 !important; }
        
        /* ── Magnified Number Audit Effect (4x Size, High Contrast: Blue on White) ── */
        .excel-table tbody td.num-magnified,
        .excel-table td.num-magnified,
        td.num-magnified {
            font-size: 3.5rem !important;
            font-weight: 900 !important;
            font-family: 'JetBrains Mono', 'Fira Code', 'Roboto Mono', monospace !important;
            font-variant-numeric: tabular-nums !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0044cc !important;
            border: 3.5px solid #0044cc !important;
            border-radius: 14px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(0, 68, 204, 0.4), inset 0 0 8px rgba(0, 68, 204, 0.1) !important;
            z-index: 999999 !important;
            position: relative !important;
            text-shadow: none !important;
            padding: 6px 22px !important;
            white-space: nowrap !important;
            min-width: max-content !important;
            width: auto !important;
            overflow: visible !important;
            display: table-cell !important;
            letter-spacing: 1px !important;
            transform: none !important;
            line-height: 1.15 !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer !important;
        }
        :root.light-theme .excel-table tbody td.num-magnified,
        :root.light-theme .excel-table td.num-magnified {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0044cc !important;
            border: 3.5px solid #0044cc !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35), 0 0 25px rgba(0, 68, 204, 0.3) !important;
            text-shadow: none !important;
        }

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

        /* Hide injected global navigation sidebar/header on preview page */
        .global-sidebar, header.premium-header, .global-header, .navbar {
            display: none !important;
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-color);
            transition: background 0.3s, color 0.3s;
        }

        /* Luxury Ribbon Toolbar with Pill Buttons (Ultra Compact 0.8rem) */
        .ribbon {
            position: sticky; top: 0; z-index: 1000;
            background: var(--ribbon-bg); color: var(--ribbon-text);
            padding: 5px 14px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            border-bottom: 2px solid #0284c7;
            flex-wrap: nowrap; gap: 8px; overflow-x: auto;
        }
        /* Ribbon & Preset Buttons */
        .ribbon-btn {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, color-mix(in srgb, var(--btn-color, #fff) 8%, rgba(255,255,255,0.05)), rgba(0,0,0,0.2));
            border: 1px solid color-mix(in srgb, var(--btn-color, #fff) 40%, rgba(255,255,255,0.2));
            color: #f8fafc;
            padding: 4px 12px;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
            font-size: 0.8rem;
            height: 32px;
            white-space: nowrap;
        }
        .ribbon-btn:hover {
            transform: translateY(-2px);
            border-color: var(--btn-color, #fff);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
            color: #fff !important;
        }

        .preset-btn {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(15,23,42,0.85)) !important;
            border: 1.5px solid rgba(255,255,255,0.25) !important;
            color: #f1f5f9 !important;
            padding: 5px 14px !important;
            border-radius: 20px !important;
            font-family: inherit;
            font-size: 0.85rem !important;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.25);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            white-space: nowrap;
        }
        .preset-btn:hover:not(.active) {
            background: linear-gradient(135deg, rgba(56,189,248,0.35), rgba(3,105,161,0.8)) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(56,189,248,0.4);
        }
        .preset-btn.active {
            background: linear-gradient(135deg, rgba(16,185,129,0.5), rgba(4,120,87,0.9)) !important;
            border-color: #34d399 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 16px rgba(16,185,129,0.55), inset 0 1px 1px rgba(255,255,255,0.5) !important;
            transform: scale(1.04);
        }


        /* Old panel styles removed - now controlled by css/studio-ribbon-v4.css */

        /* Printable Area - Realistic Solid Paper Sheet Boundary */
        .print-area {
            padding: 35px 40px;
            margin: 25px auto 50px auto;
            background: var(--table-bg);
            color: var(--text-color);
            border: 1px solid var(--table-border);
            border-radius: 4px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            box-sizing: border-box;
            position: relative;
        }

        body.dark-mode .print-area {
            border-color: #334155;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        }

        .print-area.paper-portrait { max-width: 1000px; width: 94%; }
        .print-area.paper-landscape { max-width: 98%; width: 98%; }

        /* Paper sheet indicator badge */
        .print-area::before {
            content: '📄 ورقة طباعة رسمية (معاينة)';
            position: absolute;
            top: -12px;
            right: 25px;
            background: var(--ribbon-bg);
            color: var(--ribbon-text);
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* Company Header */
        .company-header {
            display: flex; justify-content: space-between; align-items: flex-start; height: 3.5cm;
            border-bottom: 3px solid var(--text-color); margin-bottom: 15px; padding-bottom: 10px;
            transition: all 0.3s;
        }
        .company-logo-wrap { width: 3.5cm; height: 100%; border: 1px solid var(--table-border); display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; }
        .company-logo-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* Excel-like Flat Table */
        table.excel-table {
            width: 100%; border-collapse: collapse; table-layout: auto;
            background: var(--table-bg); color: inherit;
        }
        table.excel-table th, table.excel-table td {
            border: 1px solid var(--table-border);
            padding: 7px 10px; text-align: center; font-size: 0.9rem; color: inherit;
            white-space: nowrap !important; word-break: keep-all !important;
        }
        table.excel-table th {
            background: var(--table-header-bg); font-weight: 800; cursor: pointer; position: relative; user-select: none;
            white-space: nowrap !important; word-break: keep-all !important;
        }
        table.excel-table th:hover { background: rgba(56,189,248,0.25); }
        table.excel-table th.selected-col { background: #bae6fd !important; color: #0369a1 !important; border-bottom: 3px solid #0284c7 !important; }
        body.dark-mode table.excel-table th.selected-col { background: #0369a1 !important; color: #ffffff !important; border-bottom-color: #38bdf8 !important; }
        
        table.excel-table td.num-cell { font-family: var(--num-font-family); font-weight: 600; direction: ltr; }

        /* Default Column Background & Font Colors */
        table.excel-table td[data-col-idx="5"], table.excel-table td[data-col-idx="7"] {
            background-color: #e0f2fe !important;
            color: #0044cc !important;
            font-weight: 700 !important;
        }
        table.excel-table td[data-col-idx="6"], table.excel-table td[data-col-idx="8"] {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            font-weight: 700 !important;
        }
        table.excel-table td[data-col-idx="9"] {
            background-color: #dcfce7 !important;
            color: #059669 !important;
            font-weight: 700 !important;
        }
        table.excel-table td[data-col-idx="10"], table.excel-table td[data-col-idx="11"] {
            background-color: #fef3c7 !important;
            color: #78350f !important;
            font-weight: 800 !important;
        }

        table.excel-table tfoot td { font-weight: 900; background: var(--table-header-bg); }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .ribbon, .settings-panel, .print-area::before, #global-print-header {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-area {
                background: #ffffff !important;
                color: inherit !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            table.excel-table {
                width: 100% !important;
                table-layout: auto !important;
            }
            table.excel-table th, table.excel-table td {
                border: 1px solid var(--table-border) !important;
                white-space: nowrap !important;
                word-break: keep-all !important;
                padding: 6px 8px !important;
            }
        }

        /* Toasts container & animations */
        #studio-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .studio-toast {
            pointer-events: auto;
            min-width: 250px;
            max-width: 350px;
            direction: rtl;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            font-weight: 800;
            font-size: 0.95rem;
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .studio-toast.success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 1px solid #34d399;
        }
        .studio-toast.info {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            border: 1px solid #38bdf8;
        }
        .studio-toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: 1px solid #f87171;
        }
        .studio-toast.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* Custom Glassmorphic Modal */
        .reset-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .reset-modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .reset-modal-container {
            background: rgba(30, 41, 59, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            border-radius: 20px;
            padding: 30px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            color: #f8fafc;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            direction: rtl;
            font-family: 'Tajawal', sans-serif;
        }
        .reset-modal-overlay.show .reset-modal-container {
            transform: scale(1);
        }
        .reset-modal-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            animation: pulse-warn 2s infinite;
        }
        @keyframes pulse-warn {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.5)); }
            100% { transform: scale(1); }
        }
        .reset-modal-title {
            margin: 0 0 10px 0;
            font-size: 1.25rem;
            font-weight: 900;
            line-height: 1.4;
        }
        .reset-modal-desc {
            margin: 0 0 24px 0;
            font-size: 0.92rem;
            color: #cbd5e1;
            line-height: 1.6;
        }
        .reset-modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .modal-btn {
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 800;
            padding: 10px 24px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.25s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            user-select: none;
        }
        .modal-btn.cancel {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
        }
        .modal-btn.cancel:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            transform: translateY(-1px);
        }
        .modal-btn.ok {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden;
        }
        .modal-btn.ok:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }
        .modal-btn.ok:active {
            transform: translateY(0);
        }
        /* SVG Circle progress styles */
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.1s linear;
        }
    </style>
</head>
<body>

<!-- Luxury Ribbon Toolbar -->
<div class="ribbon no-print">
    <!-- Right Side: 6 Presets -->
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div id="presets-container" style="display:flex; gap:8px; flex-wrap: wrap;">
            <!-- Preset buttons rendered via JS -->
        </div>
    </div>

    <!-- Center/Left Side: Controls & Live Search -->
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Live Quick Search Box -->
        <input type="text" id="live-search-input" placeholder="🔍 بحث سريع..." oninput="filterTableRows(this.value)" style="padding: 4px 10px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.3); color: #fff; font-family: inherit; font-size: 0.8rem; outline: none; width: 120px; height: 30px; box-sizing: border-box;">

        <script>
        function saveCurrentPresetAndLock() {
            if (typeof saveCurrentPreset === 'function') {
                saveCurrentPreset(window.currentPresetIndex || 0);
                let presets = getSavedPresets();
                if (presets[window.currentPresetIndex || 0]) {
                    presets[window.currentPresetIndex || 0].isLocked = true;
                    savePresets(presets);
                    syncPresetsToDB('ledger_preview_presets_v20', presets);
                    if (typeof showToast === 'function') showToast('تم حفظ التصميم وقفل الطبلة بنجاح!', 'success');
                    setTimeout(() => { window.history.back(); }, 800);
                }
            }
        }
        </script>

        <button class="ribbon-btn" onclick="executePrintWithPermission()" style="--btn-color: #0ea5e9;">
            <span style="font-size: 1.05rem;">🖨️</span> طباعة
        </button>

        <!-- Settings and Save Group -->
        <div style="display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12);">
            <button class="ribbon-btn" onclick="toggleSettingsPanel()" style="--btn-color: #eab308; margin:0;">
                <span style="font-size: 1.05rem;">⚙️</span> إعدادات التصميم
            </button>
            <button class="ribbon-btn" id="mega-save-btn" onclick="saveCurrentPresetAndLock()" style="--btn-color: #10b981; font-weight: 900; display: none; margin:0;">
                <span style="font-size: 1.05rem;">💾</span> حفظ التصميم 🔒
            </button>
        </div>

        <button class="ribbon-btn" onclick="toggleDarkMode()" style="--btn-color: #a855f7;">
            <span style="font-size: 1.05rem;">🌙</span> الوضع الليلي
        </button>
        <button class="ribbon-btn" onclick="window.history.back()" style="--btn-color: #ef4444;">
            <span style="font-size: 1.05rem;">✖</span> إغلاق / عودة
        </button>
    </div>
</div>

<!-- Pro Studio Dropdown Panel with Slide Animation -->
<?php include 'includes/studio-settings-panel.php'; ?>
<!-- Print Area - Paper Sheet Layout View -->
<div class="print-area paper-landscape" id="print-area-wrapper">
    <div id="print-top-bar" class="print-meta-bar" style="display:none;"></div>
    
    <!-- Company Header Area -->
    <div class="company-header report-header" id="main-company-header">
        <!-- Right Side: Company Details -->
        <div id="company-info-block" style="flex: 1; text-align: right; color: var(--text-color);">
            <h2 id="hdr-c-name" style="margin: 0 0 4px 0; font-size: 1.5rem; font-weight: 900;"></h2>
            <p id="hdr-c-reg" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-vat" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-phone" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
            <p id="hdr-c-addr" style="margin: 0 0 2px 0; font-size: 0.9rem; font-weight: 600;"></p>
        </div>

        <!-- Center Side: Report Title -->
        <div style="flex: 1; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
            <h2 style="margin: 0; padding: 8px 22px; background: rgba(0,0,0,0.04); border-radius: 8px; border: 2px solid var(--text-color); font-size: 1.5rem; font-weight: 900;" id="report-main-title">دفتر الأستاذ العام</h2>
        </div>

        <!-- Left Side: Logo Area -->
        <div id="company-logo-block" style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-end; height: 100%;">
            <div class="company-logo-wrap">
                <img id="company-logo" src="" alt="شعار الشركة" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    <!-- Subheader Row: 3-column flex layout (Right: Customer, Center: Date, Left: Period) -->
    <div id="report-subheader-row" style="display: flex; justify-content: space-between; align-items: center; margin: 12px 0 16px 0; padding: 10px 18px; background: rgba(0,0,0,0.02); border-radius: 8px; border: 1px solid rgba(0,0,0,0.12); font-family: 'Tajawal', sans-serif;">
        <!-- Right Column: Customer Name -->
        <div style="flex: 1; text-align: right;">
            <span id="report-customer-name-lbl" style="font-size: 1.4rem; font-weight: 900; color: var(--text-color);"></span>
        </div>
        
        <!-- Center Column: Report Date -->
        <div style="flex: 1; text-align: center;">
            <span id="report-date-lbl" style="font-size: 1.4rem; font-weight: 900; color: var(--text-color);"></span>
        </div>
        
        <!-- Left Column: Period -->
        <div style="flex: 1; text-align: left;">
            <span id="report-period-lbl" style="font-size: 1.15rem; font-weight: 800; color: var(--text-color);"></span>
        </div>
    </div>

    <!-- Table -->
    <table class="excel-table font-newsons" id="preview-table">
        <thead id="preview-thead">
            <!-- Headers rendered via JS -->
        </thead>
        <tbody id="preview-tbody">
            <!-- Body rendered via JS -->
        </tbody>
        <tfoot id="preview-tfoot">
            <!-- Summary totals row rendered via JS -->
        </tfoot>
    </table>
    
    <div id="print-footer-bar" class="print-meta-bar print-meta-footer" style="display:none;"></div>
</div>

<!-- Simple Context Menu for Preview Ledger -->
<div id="preview-context-menu" style="position: fixed; display: none; background: rgba(15, 23, 42, 0.96); border: 1px solid rgba(56, 189, 248, 0.5); border-radius: 10px; padding: 6px 0; z-index: 999999; backdrop-filter: blur(16px); min-width: 220px; box-shadow: 0 15px 30px rgba(0,0,0,0.6), 0 0 15px rgba(56, 189, 248, 0.3); direction: rtl; font-family: 'Tajawal', sans-serif;">
    <div id="preview-ctx-drilldown" style="padding: 12px 18px; color: #fff; cursor: pointer; font-size: 0.95rem; font-weight: 800; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
        <span>🔗 الذهاب إلى أصل السند</span><span style="color:#38bdf8; font-size:0.8rem;">◀</span>
    </div>
</div>

<!-- Long Press HUD Overlay for 8-second Save & Lock -->
<?php include 'includes/studio-modals.php'; ?>
<script src="js/localforage.min.js"></script>
<script src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '1'; ?>"></script>
<script src="js/general-ledger.js?v=<?php echo time(); ?>"></script>
<script src="js/preview-ledger.js?v=<?php echo time(); ?>"></script>
</body>
</html>
