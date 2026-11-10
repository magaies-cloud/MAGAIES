// ============================================================================
// PURCHASE DESIGN CONSTITUTION 2027 & FINAL MESSAGE (1 OF 3 — 8 IRON LAWS)
// APPLIED TO ALL DIRECT SALES VIEWS: ?action=new (#invoice-editor-view),
// #sales-hub-view, ?action=split (#split-invoice-view), AND ALL MODALS
// ============================================================================
(function () {
    'use strict';
    if (window.__SALES_CONSTITUTION_1OF3_2027_LOADED__) return;
    window.__SALES_CONSTITUTION_1OF3_2027_LOADED__ = true;

    const css = `
    /* ═══ LAW 2: 13 UNIFIED PURCHASE TONES (9 LIGHT + 4 DARK) & STRICT DAYLIGHT GUARD ═══ */
    :root.light-theme, :root.light-theme[data-light-tone="ivory_default"] { --lt-canvas-1:#dfd0b4; --lt-canvas-2:#d2be9a; --lt-hdr-1:#eadbc0; --lt-hdr-2:#dec9a4; --lt-hdr-3:#d1b689; --lt-card-1:#fffdf8; --lt-card-2:#f7efe0; --lt-sub-1:#fffefb; --lt-sub-2:#f6ecda; --lt-th-1:#f5ead4; --lt-th-2:#e6d4b5; --lt-row-odd:#fffefb; --lt-row-even:#f7efe0; --lt-border:#5c2808; --lt-border-hover:#b45309; --lt-glow:rgba(245,158,11,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="royal_bronze"] { --lt-canvas-1:#d4b08c; --lt-canvas-2:#c29970; --lt-hdr-1:#e2be98; --lt-hdr-2:#d4a679; --lt-hdr-3:#c48f5c; --lt-card-1:#fdf6ee; --lt-card-2:#f3e2ce; --lt-sub-1:#fef9f3; --lt-sub-2:#efd8be; --lt-th-1:#edd5ba; --lt-th-2:#dfbda0; --lt-row-odd:#fdf6ee; --lt-row-even:#f3e2ce; --lt-border:#4a2106; --lt-border-hover:#9a3412; --lt-glow:rgba(217,119,6,0.48); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="platinum_gray"] { --lt-canvas-1:#c2d0e0; --lt-canvas-2:#aebfd4; --lt-hdr-1:#cfdeee; --lt-hdr-2:#bccfe4; --lt-hdr-3:#a8bfd9; --lt-card-1:#f7faff; --lt-card-2:#e6eef8; --lt-sub-1:#fafdff; --lt-sub-2:#e1ebf6; --lt-th-1:#dee9f6; --lt-th-2:#cbdbed; --lt-row-odd:#f7faff; --lt-row-even:#e6eef8; --lt-border:#1e293b; --lt-border-hover:#0284c7; --lt-glow:rgba(56,189,248,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="crystal_silver"] { --lt-canvas-1:#d2d8e2; --lt-canvas-2:#bfc7d4; --lt-hdr-1:#dfe4ec; --lt-hdr-2:#cfd6e2; --lt-hdr-3:#bec7d5; --lt-card-1:#fafbfd; --lt-card-2:#edf1f7; --lt-sub-1:#ffffff; --lt-sub-2:#e7ecf3; --lt-th-1:#e5eaf1; --lt-th-2:#d5dce7; --lt-row-odd:#fafbfd; --lt-row-even:#edf1f7; --lt-border:#111827; --lt-border-hover:#2563eb; --lt-glow:rgba(59,130,246,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="sage_pearl"] { --lt-canvas-1:#bce0ce; --lt-canvas-2:#a4d1bb; --lt-hdr-1:#c8ead9; --lt-hdr-2:#b0ddca; --lt-hdr-3:#98cfb8; --lt-card-1:#f4fcf8; --lt-card-2:#e0f5ec; --lt-sub-1:#f9fefb; --lt-sub-2:#d8f2e6; --lt-th-1:#d4f0e3; --lt-th-2:#bfe6d4; --lt-row-odd:#f4fcf8; --lt-row-even:#e0f5ec; --lt-border:#064e3b; --lt-border-hover:#059669; --lt-glow:rgba(16,185,129,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="sky_alabaster"] { --lt-canvas-1:#bad9f5; --lt-canvas-2:#a2caed; --lt-hdr-1:#c7e3fb; --lt-hdr-2:#afd4f5; --lt-hdr-3:#96c4ee; --lt-card-1:#f4faff; --lt-card-2:#dfeffd; --lt-sub-1:#f9fcff; --lt-sub-2:#d6ebfc; --lt-th-1:#d2e9fc; --lt-th-2:#bdddf9; --lt-row-odd:#f4faff; --lt-row-even:#dfeffd; --lt-border:#0c4a6e; --lt-border-hover:#0284c7; --lt-glow:rgba(14,165,233,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="rose_blossom"] { --lt-canvas-1:#f5d0e3; --lt-canvas-2:#ebaed0; --lt-hdr-1:#fae0ee; --lt-hdr-2:#f3c6df; --lt-hdr-3:#ebadd0; --lt-card-1:#fff7fb; --lt-card-2:#fce8f3; --lt-sub-1:#fffafd; --lt-sub-2:#f9dfee; --lt-th-1:#f7d6e8; --lt-th-2:#efc2dc; --lt-row-odd:#fff7fb; --lt-row-even:#fce8f3; --lt-border:#831843; --lt-border-hover:#be185d; --lt-glow:rgba(236,72,153,0.45); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="warm_mocha_dark"] { --lt-canvas-1:#b89878; --lt-canvas-2:#a3805e; --lt-hdr-1:#ccb094; --lt-hdr-2:#ba9878; --lt-hdr-3:#a8825e; --lt-card-1:#faf3ea; --lt-card-2:#eee0cf; --lt-sub-1:#fdf8f2; --lt-sub-2:#e8d6c1; --lt-th-1:#e5d1ba; --lt-th-2:#d6bc9f; --lt-row-odd:#faf3ea; --lt-row-even:#eee0cf; --lt-border:#3e1b04; --lt-border-hover:#7c2d12; --lt-glow:rgba(180,83,9,0.48); --lt-ink:#0F172A; }
    :root.light-theme[data-light-tone="deep_slate_dark"] { --lt-canvas-1:#a3b4ca; --lt-canvas-2:#8da1bb; --lt-hdr-1:#b8c7dc; --lt-hdr-2:#a2b5cf; --lt-hdr-3:#8ca3c1; --lt-card-1:#f2f6fc; --lt-card-2:#e2eaf5; --lt-sub-1:#f7faff; --lt-sub-2:#dbe5f2; --lt-th-1:#d6e1f0; --lt-th-2:#c3d2e6; --lt-row-odd:#f2f6fc; --lt-row-even:#e2eaf5; --lt-border:#0f172a; --lt-border-hover:#1d4ed8; --lt-glow:rgba(37,99,235,0.45); --lt-ink:#0F172A; }
    html:not(.light-theme), html:not(.light-theme)[data-light-tone="dark_midnight"] { --dk-canvas:#010308; --dk-card-1:#1b3159; --dk-card-2:#132341; --dk-sub:#0b1120; --dk-border:#38bdf8; }
    html:not(.light-theme)[data-light-tone="pure_oled"] { --dk-canvas:#000000; --dk-card-1:#090d16; --dk-card-2:#050811; --dk-sub:#000000; --dk-border:#0284c7; }
    html:not(.light-theme)[data-light-tone="mocha_brown"] { --dk-canvas:#140b07; --dk-card-1:#2b1810; --dk-card-2:#1f120d; --dk-sub:#120906; --dk-border:#f59e0b; }
    html:not(.light-theme)[data-light-tone="slate_twilight"] { --dk-canvas:#0f172a; --dk-card-1:#1e293b; --dk-card-2:#172033; --dk-sub:#0b1120; --dk-border:#94a3b8; }

    /* 1. 4 Dark Tones across ALL Direct Sales & Split Views & Modals */
    html:not(.light-theme) body, html:not(.light-theme) #sales-hub-view, html:not(.light-theme) #invoice-editor-view, html:not(.light-theme) #split-invoice-view { background:var(--dk-canvas,#010308) !important; color:#ffffff !important; }
    html:not(.light-theme) #sales-fc1-smart-ai-command-bar,
    html:not(.light-theme) #form-fields-card,
    html:not(.light-theme) .sales-glass-card,
    html:not(.light-theme) #items-panel,
    html:not(.light-theme) #items-panel .panel-header-row,
    html:not(.light-theme) #inline-totals-container,
    html:not(.light-theme) #totals-calc-card,
    html:not(.light-theme) #payment-panel,
    html:not(.light-theme) #invoice-editor-view .items-toolbar,
    html:not(.light-theme) #sales-hub-view .neo-card,
    html:not(.light-theme) .sales-fc1-modal-card,
    html:not(.light-theme) #split-invoice-view .neo-card,
    html:not(.light-theme) #split-invoice-view #split-fc1-ai-cockpit-bar,
    html:not(.light-theme) #split-invoice-view #split-search-toolbar,
    html:not(.light-theme) #split-invoice-view #split-header-info {
        background:linear-gradient(145deg,var(--dk-card-1,#1b3159) 0%,var(--dk-card-2,#132341) 100%) !important;
        border:2.2px solid var(--dk-border,rgba(56,189,248,0.78)) !important;
        border-radius:15px !important;
        box-shadow:0 10px 26px rgba(0,0,0,0.75),inset 0 1px 0 rgba(255,255,255,0.18) !important;
        color:#ffffff !important;
    }
    html:not(.light-theme) #invoice-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    html:not(.light-theme) #invoice-editor-view select:not(#hdr-currency),
    html:not(.light-theme) #invoice-editor-view textarea,
    html:not(.light-theme) #sales-hub-view input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme) #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme) #split-invoice-view select,
    html:not(.light-theme) #split-invoice-view textarea {
        background-color:#0b1120 !important;
        -webkit-box-shadow:0 0 0 1000px #0b1120 inset !important;
        box-shadow:0 0 0 1000px #0b1120 inset !important;
        color:#ffffff !important;
        -webkit-text-fill-color:#ffffff !important;
        border:1.8px solid var(--dk-border,#38bdf8) !important;
        font-weight:900 !important;
    }
    html:not(.light-theme) #pos-grid thead th,
    html:not(.light-theme) #sales-hub-view table.pos-grid thead th,
    html:not(.light-theme) #split-invoice-view table.pos-grid thead th {
        background:#0d182c !important;
        color:#ffffff !important;
        font-weight:900 !important;
        font-size:0.98rem !important;
        border:1.8px solid rgba(56,189,248,0.5) !important;
    }
    html:not(.light-theme) #pos-grid tbody td,
    html:not(.light-theme) #sales-hub-view table.pos-grid tbody td,
    html:not(.light-theme) #split-invoice-view table.pos-grid tbody td {
        background:#13223f !important;
        color:#ffffff !important;
        font-weight:900 !important;
        font-size:0.96rem !important;
        border:1.5px solid rgba(56,189,248,0.25) !important;
    }
    html:not(.light-theme) #pos-grid tbody tr:nth-child(even) td,
    html:not(.light-theme) #sales-hub-view table.pos-grid tbody tr:nth-child(even) td,
    html:not(.light-theme) #split-invoice-view table.pos-grid tbody tr:nth-child(even) td {
        background:#17294b !important;
    }

    /* 2. All 9 Light Tones + STRICT DAYLIGHT GUARD (Zero Light Text on Light Surfaces) */
    html.light-theme body, html.light-theme #sales-hub-view, html.light-theme #invoice-editor-view, html.light-theme #split-invoice-view {
        background:linear-gradient(180deg,var(--lt-canvas-1,#dfd0b4) 0%,var(--lt-canvas-2,#d2be9a) 100%) !important;
        color:var(--lt-ink,#0F172A) !important;
    }
    html.light-theme #sales-fc1-smart-ai-command-bar,
    html.light-theme #sales-fc2-live-result-interpreter-bar,
    html.light-theme #sales-top-container #form-fields-card,
    html.light-theme #form-fields-card,
    html.light-theme .sales-glass-card,
    html.light-theme #items-panel,
    html.light-theme #items-panel .panel-header-row,
    html.light-theme .table-container,
    html.light-theme #inline-totals-container,
    html.light-theme #totals-calc-card,
    html.light-theme #payment-panel,
    html.light-theme #payment-panel .panel-header-row,
    html.light-theme #pay-credit-section,
    html.light-theme #pay-cash-section,
    html.light-theme #pay-bank-section,
    html.light-theme #pay-cheque-section,
    html.light-theme #invoice-editor-view .items-toolbar,
    html.light-theme #sales-hub-view .neo-card,
    html.light-theme #sales-hub-view .hub-action-card,
    html.light-theme .sales-fc1-modal-card,
    html.light-theme .modal-card,
    html.light-theme .modal-content,
    html.light-theme #split-invoice-view .neo-card,
    html.light-theme #split-invoice-view #split-constitution-1of3-bar,
    html.light-theme #split-invoice-view #split-fc1-ai-cockpit-bar,
    html.light-theme #split-invoice-view #split-fc2-board-sajana-strip,
    html.light-theme #split-invoice-view .fc3-chart-card,
    html.light-theme #split-invoice-view #split-search-toolbar,
    html.light-theme #split-invoice-view #split-fin-summary-strip,
    html.light-theme #split-invoice-view #split-header-info,
    html.light-theme #split-invoice-view .grid-scroll-wrap {
        background:linear-gradient(145deg,var(--lt-card-1,#fffdf8) 0%,var(--lt-card-2,#f7efe0) 100%) !important;
        border:2.8px solid var(--lt-border,#5c2808) !important;
        border-radius:15px !important;
        box-shadow:0 8px 22px rgba(43,17,3,0.14),inset 0 2px 0 #ffffff !important;
        color:var(--lt-ink,#0F172A) !important;
    }
    html.light-theme #invoice-info-box,
    html.light-theme #invoice-info-box .pro-serial-container,
    html.light-theme #customer-financial-card .fin-strip-inner {
        background:linear-gradient(135deg,var(--lt-sub-1,#fffefb) 0%,var(--lt-sub-2,#f6ecda) 100%) !important;
        border:2px solid var(--lt-border,#5c2808) !important;
        color:#0F172A !important;
    }
    /* Strict Daylight Guard: High-Contrast #0F172A / #1E293B on all labels, titles, and light surfaces */
    html.light-theme #sales-proportional-root label,
    html.light-theme #sales-proportional-root .h-label,
    html.light-theme #sales-proportional-root .v-label,
    html.light-theme #sales-proportional-root #hdr-inv-label,
    html.light-theme #sales-proportional-root #hdr-date-label,
    html.light-theme #sales-proportional-root #inv-number,
    html.light-theme #sales-proportional-root .panel-title,
    html.light-theme #sales-proportional-root .panel-title span,
    html.light-theme #sales-proportional-root .neo-card-title span:not([style*="background"]),
    html.light-theme #sales-proportional-root .hub-action-card .title,
    html.light-theme #sales-proportional-root #pos-grid tfoot tr.tot-row span,
    html.light-theme #sales-proportional-root #payment-panel span:not(.m7-solid-badge-guard),
    html.light-theme #sales-proportional-root .pay-toggle-btn:not(.active),
    html.light-theme #sales-proportional-root .tot-toggle-btn:not(.active),
    html.light-theme #sales-proportional-root h1,
    html.light-theme #sales-proportional-root h2,
    html.light-theme #sales-proportional-root h3,
    html.light-theme #sales-proportional-root h4,
    html.light-theme #sales-fc2-live-result-summary-text,
    html.light-theme #split-invoice-view #split-fc1-live-tafqeet-pill,
    html.light-theme #split-invoice-view #split-fc2-live-ai-exec-summary,
    html.light-theme #split-invoice-view #split-header-info *,
    html.light-theme #split-invoice-view #split-board-step .neo-card span,
    html.light-theme #share-popup-menu button,
    html.light-theme #share-popup-menu button span,
    html.light-theme #auto-send-sales-dropdown-menu *,
    html.light-theme .custom-dropdown-list *,
    html.light-theme .sales-fc1-modal-card *:not(button),
    html.light-theme .modal-overlay .modal-card *:not(button),
    html.light-theme .modal-overlay .modal-content *:not(button) {
        color:var(--lt-ink,#0F172A) !important;
        -webkit-text-fill-color:var(--lt-ink,#0F172A) !important;
        font-weight:900 !important;
        text-shadow:none !important;
    }
    html.light-theme #sales-proportional-root .pay-toggle-btn:not(.active),
    html.light-theme #sales-proportional-root .tot-toggle-btn:not(.active) {
        background:#ffffff !important;
        border:2px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #share-popup-menu,
    html.light-theme #auto-send-sales-dropdown-menu,
    html.light-theme .custom-dropdown-list {
        background:linear-gradient(145deg,#ffffff 0%,var(--lt-card-2,#f7efe0) 100%) !important;
        border:2.5px solid var(--lt-border,#5c2808) !important;
        color:#0F172A !important;
        box-shadow:0 14px 34px rgba(15,23,42,0.22) !important;
    }
    html.light-theme #invoice-editor-view input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    html.light-theme #invoice-editor-view select:not(#hdr-currency),
    html.light-theme #invoice-editor-view textarea,
    html.light-theme #sales-hub-view input:not([type="checkbox"]):not([type="radio"]),
    html.light-theme #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html.light-theme #split-invoice-view select,
    html.light-theme #split-invoice-view textarea,
    html.light-theme #split-invoice-view input:focus {
        background-color:#ffffff !important;
        -webkit-box-shadow:0 0 0 1000px #ffffff inset !important;
        box-shadow:0 0 0 1000px #ffffff inset !important;
        color:#0F172A !important;
        -webkit-text-fill-color:#0F172A !important;
        border:2.5px solid var(--lt-border,#5c2808) !important;
        font-weight:900 !important;
    }
    html.light-theme #pos-grid thead th,
    html.light-theme #sales-hub-view table.pos-grid thead th,
    html.light-theme #split-invoice-view table.pos-grid thead th {
        background:linear-gradient(180deg,var(--lt-th-1,#f5ead4) 0%,var(--lt-th-2,#e6d4b5) 100%) !important;
        color:var(--lt-ink,#0F172A) !important;
        -webkit-text-fill-color:var(--lt-ink,#0F172A) !important;
        font-weight:900 !important;
        font-size:0.98rem !important;
        border:2.2px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #pos-grid tbody td,
    html.light-theme #sales-hub-view table.pos-grid tbody td,
    html.light-theme #split-invoice-view table.pos-grid tbody td {
        background:var(--lt-row-odd,#fffefb) !important;
        color:var(--lt-ink,#0F172A) !important;
        -webkit-text-fill-color:var(--lt-ink,#0F172A) !important;
        font-weight:900 !important;
        font-size:0.96rem !important;
        border:1.8px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #pos-grid tbody tr:nth-child(even) td,
    html.light-theme #sales-hub-view table.pos-grid tbody tr:nth-child(even) td,
    html.light-theme #split-invoice-view table.pos-grid tbody tr:nth-child(even) td {
        background:var(--lt-row-even,#f7efe0) !important;
    }
    /* Keep white text on dark/solid badges & primary buttons */
    html.light-theme .m7-solid-badge-guard,
    html.light-theme .m7-solid-badge-guard *,
    html.light-theme #customer-financial-card .fin-metric-pill,
    html.light-theme #customer-financial-card .fin-metric-pill *,
    html.light-theme #fin-strip-currency-btn,
    html.light-theme #split-invoice-view .split-3d-cap,
    html.light-theme #split-invoice-view .split-3d-cap *,
    html.light-theme #split-invoice-view #split-fin-curr-toggle-btn,
    html.light-theme #split-invoice-view .split-ledger-btn,
    html.light-theme .royal-crystal-btn,
    html.light-theme .royal-crystal-btn *,
    html.light-theme .pay-toggle-btn.active,
    html.light-theme .tot-toggle-btn.active {
        color:#ffffff !important;
        -webkit-text-fill-color:#ffffff !important;
        font-weight:900 !important;
    }

    /* ═══ LAW 4: COMPACT MODE (الوضع المختصر) VS FULL MODE (الوضع الكامل) ═══ */
    body[data-sales-compact="1"] #sales-row2-bar,
    body[data-sales-compact="1"] #pos-grid th.th-factor,
    body[data-sales-compact="1"] #pos-grid td[data-col-id="col_factor"],
    body[data-sales-compact="1"] #pos-grid th.th-baseqty,
    body[data-sales-compact="1"] #pos-grid td[data-col-id="col_baseqty"],
    body[data-sales-compact="1"] #pos-grid th.th-wh,
    body[data-sales-compact="1"] #pos-grid td[data-col-id="col_wh"],
    body[data-sales-compact="1"] #pos-grid th.th-note,
    body[data-sales-compact="1"] #pos-grid td[data-col-id="col_note"],
    body[data-sales-compact="1"] #toggles-area,
    body[data-sales-compact="1"] #split-fc2-sovereign-row,
    body[data-sales-compact="1"] #split-eligible-kpi-strip,
    body[data-sales-compact="1"] #split-select-step table.pos-grid th:nth-child(4),
    body[data-sales-compact="1"] #split-select-step table.pos-grid td:nth-child(4),
    body[data-sales-compact="1"] #split-select-step table.pos-grid th:nth-child(6),
    body[data-sales-compact="1"] #split-select-step table.pos-grid td:nth-child(6) {
        display:none !important;
    }

    /* ═══ LAW 5: CONTAINED BUTTON HOVER & ANTI-EXPLOSION POPOVERS ═══ */
    #sales-proportional-root button,
    #sales-proportional-root .btn,
    #sales-proportional-root .btn-action,
    #sales-proportional-root .hub-action-card,
    .sales-fc1-modal-card button {
        overflow:hidden !important;
        isolation:isolate !important;
        -webkit-text-stroke:0px transparent !important;
        transition:transform 0.08s cubic-bezier(0.2,0.9,0.2,1), box-shadow 0.12s ease !important;
    }
    #sales-proportional-root button:active {
        transform:scale(0.97) !important;
    }
    #customer-dropdown-list,
    .custom-dropdown-list,
    #quick-item-dropdown-list,
    .item-dropdown-list,
    #share-popup-menu,
    #auto-send-sales-dropdown-menu,
    #pur-light-tone-popover,
    #header-quick-tools-popover {
        max-height:min(320px, 62vh) !important;
        overflow-y:auto !important;
        overflow-x:hidden !important;
        max-width:min(92vw, 520px) !important;
        box-sizing:border-box !important;
        overscroll-behavior:contain !important;
    }

    /* ═══ LAW 6: SMART TEXT TRUNCATION (ELLIPSIS) ═══ */
    #hdr-customer-search:not(:focus),
    #hdr-description:not(:focus),
    #pos-grid input[type="text"]:not(:focus),
    #pos-grid td,
    #sales-hub-view table.pos-grid td,
    #split-invoice-view table.pos-grid td {
        text-overflow:ellipsis !important;
    }

    /* ═══ LAW 8: STICKY TABLE HEADER & STICKY ITEM COLUMN + ACTIVE ROW GLOW ═══ */
    #pos-grid thead th,
    #sales-hub-view table.pos-grid thead th,
    #split-invoice-view table.pos-grid thead th {
        position:sticky !important;
        top:0 !important;
        z-index:35 !important;
    }
    #pos-grid th.th-item,
    #pos-grid td[data-col-id="col_item"],
    #pos-grid td[data-col-id="item"] {
        position:sticky !important;
        right:0 !important;
        z-index:24 !important;
    }
    #pos-grid thead th.th-item {
        z-index:38 !important;
    }
    html.light-theme #pos-grid tbody tr:focus-within td,
    html.light-theme #pos-grid tbody tr:hover td,
    html.light-theme #sales-hub-view table.pos-grid tbody tr:hover td,
    html.light-theme #split-invoice-view table.pos-grid tbody tr:hover td {
        background-color:#fde68a !important;
        color:#0F172A !important;
        -webkit-text-fill-color:#0F172A !important;
    }

    .pur-price-lamp {
        width:20px !important; height:20px !important; min-width:20px !important;
        border-radius:50% !important; display:inline-block !important; vertical-align:middle !important;
        cursor:pointer !important; border:2px solid rgba(255,255,255,0.9) !important; flex-shrink:0 !important;
    }
    .pur-price-lamp.lamp-green { background:radial-gradient(circle at 32% 32%,#6ee7b7,#10b981 55%,#065f46 100%) !important; box-shadow:0 0 10px rgba(16,185,129,0.8) !important; }
    .pur-price-lamp.lamp-red   { background:radial-gradient(circle at 32% 32%,#fda4af,#ef4444 55%,#991b1b 100%) !important; box-shadow:0 0 12px rgba(239,68,68,0.85) !important; }
    .pur-price-lamp.lamp-amber { background:radial-gradient(circle at 32% 32%,#fde68a,#f59e0b 55%,#92400e 100%) !important; box-shadow:0 0 10px rgba(245,158,11,0.8) !important; }
    .pur-price-lamp.lamp-blue  { background:radial-gradient(circle at 32% 32%,#93c5fd,#3b82f6 55%,#1e40af 100%) !important; box-shadow:0 0 10px rgba(59,130,246,0.8) !important; }
    #split-invoice-view input.is-zero-val:not(:focus),
    #invoice-editor-view input.is-zero-val:not(:focus) { opacity:0.45 !important; }
    `;

    function ensureSplitStyleLast() {
        let st = document.getElementById('split-purchase-constitution-2027-style');
        const host = document.body || document.head;
        if (!st) {
            st = document.createElement('style');
            st.id = 'split-purchase-constitution-2027-style';
            st.textContent = css;
            if (host) host.appendChild(st);
        } else if (host && host.lastElementChild !== st) {
            host.appendChild(st);
        }
    }
    ensureSplitStyleLast();
    setTimeout(ensureSplitStyleLast, 200);
    setTimeout(ensureSplitStyleLast, 800);

    // ═══ LAW 2: 12–13 TONE POPOVER ENGINE + 4 INTERACTIVE SYSTEM SOUND TONES + STRICT DAYLIGHT GUARD ═══
    const SOUND_TONE_KEY = 'magaies_system_sound_tone_v1';
    const TONES = [
        { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',          bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true,  themeKey: 'midnight_obsidian' },
        { id: 'pure_oled',       name: 'سواد مطلق فاحم (OLED)',          bg: '#000000', fg: '#090d16', brd: '#0284c7', isDark: true,  themeKey: 'pure_oled' },
        { id: 'mocha_brown',     name: 'بني ملكي دافئ (Mocha)',          bg: '#1f120d', fg: '#3e2723', brd: '#f59e0b', isDark: true,  themeKey: 'mocha_brown' },
        { id: 'slate_twilight',  name: 'سليت فحمي مسائي (Slate)',        bg: '#1e293b', fg: '#334155', brd: '#94a3b8', isDark: true,  themeKey: 'slate_twilight' },
        { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)',       bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', isDark: false, themeKey: 'warm_cream' },
        { id: 'royal_bronze',    name: '👑 الذهبي الملكي (برونزي فاخر)', bg: '#b07239', fg: '#d9a066', brd: '#78350f', isDark: false, themeKey: 'warm_cream' },
        { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي',          bg: '#64748b', fg: '#94a3b8', brd: '#334155', isDark: false, themeKey: 'exec_gray' },
        { id: 'crystal_silver',  name: 'فضي جليدي ناصع',                 bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', isDark: false, themeKey: 'ultra_white' },
        { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',               bg: '#10b981', fg: '#6ee7b7', brd: '#047857', isDark: false, themeKey: 'warm_cream' },
        { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',             bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', isDark: false, themeKey: 'ultra_white' },
        { id: 'rose_blossom',    name: 'زهري ملكي ناعم (Rose)',          bg: '#ec4899', fg: '#fbcfe8', brd: '#9d174d', isDark: false, themeKey: 'warm_cream' },
        { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',               bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', isDark: false, themeKey: 'warm_cream' },
        { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',                bg: '#334155', fg: '#475569', brd: '#0f172a', isDark: false, themeKey: 'exec_gray' }
    ];
    const SYSTEM_AUDIO_TONES = [
        { id: 'crystal',    name: '🔔 كريستال' },
        { id: 'mechanical', name: '⌨️ ميكانيكية' },
        { id: 'soft',       name: '🎵 هادئة' },
        { id: 'mute',       name: '🔇 صامت' }
    ];
    const toneBc = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('elsajana_sovereign_10x_viewport_v1') : null;

    function playSalesSystemAudioTone(freqOverride) {
        const mode = localStorage.getItem(SOUND_TONE_KEY) || 'crystal';
        if (mode === 'mute') return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx(), osc = ctx.createOscillator(), gain = ctx.createGain();
            osc.type = mode === 'mechanical' ? 'triangle' : 'sine';
            osc.frequency.setValueAtTime(freqOverride || (mode === 'crystal' ? 880 : mode === 'mechanical' ? 320 : 540), ctx.currentTime);
            if (mode === 'crystal') osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.06, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.09);
            osc.connect(gain); gain.connect(ctx.destination); osc.start(); osc.stop(ctx.currentTime + 0.095);
            setTimeout(() => { try { ctx.close(); } catch (e) {} }, 150);
        } catch (e) {}
    }
    window.playSalesSystemAudioTone = playSalesSystemAudioTone;

    window.setSalesSystemAudioTone = function (mode) {
        localStorage.setItem(SOUND_TONE_KEY, mode);
        playSalesSystemAudioTone(960);
        if (typeof showToast === 'function') showToast('🔊 تم ضبط نغمة النظام التفاعلية: ' + mode, 'success');
    };

    // Strict Daylight Guard: Automatically converts any light text on light surfaces in Light Mode to #0F172A
    window.runSalesStrictDaylightGuard = function () {
        const isLight = document.documentElement.classList.contains('light-theme');
        const root = document.getElementById('sales-proportional-root') || document.body;
        if (!root) return;
        if (!isLight) {
            root.querySelectorAll('[data-daylight-guarded="1"]').forEach(el => {
                el.style.removeProperty('color');
                el.style.removeProperty('-webkit-text-fill-color');
                el.removeAttribute('data-daylight-guarded');
            });
            return;
        }
        const candidates = root.querySelectorAll('span, label, div, th, td, h1, h2, h3, h4, p');
        for (let i = 0; i < candidates.length; i++) {
            const el = candidates[i];
            if (el.closest('button, .m7-solid-badge-guard, .fin-metric-pill, .split-3d-cap, .royal-crystal-btn, #dashboard-athkar-container')) continue;
            const inlineCol = el.style && el.style.color ? el.style.color : '';
            if (!inlineCol) continue;
            if (/fff|cbd5e1|94a3b8|e2e8f0|fde047|fef08a|fcd34d|fde68a|38bdf8|7dd3fc|bae6fd|6ee7b7|a7f3d0|fecdd3|d8b4fe|e9d5ff/i.test(inlineCol)) {
                el.setAttribute('data-daylight-guarded', '1');
                el.style.setProperty('color', '#0F172A', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#0F172A', 'important');
            }
        }
    };

    window.applyPurchaseLightTone = function (toneId, notify, fromBroadcast) {
        const t = TONES.find(x => x.id === toneId) || (['dark_midnight','midnight_obsidian','dark','pure_oled','mocha_brown','slate_twilight'].includes(toneId) ? TONES[0] : TONES[4]);
        const targetTheme = t.themeKey || (t.isDark ? 'midnight_obsidian' : 'warm_cream');
        document.documentElement.setAttribute('data-light-tone', t.id);
        document.documentElement.setAttribute('data-theme', targetTheme);
        document.documentElement.classList.toggle('light-theme', !t.isDark);
        document.documentElement.classList.toggle('dark-theme', !!t.isDark);
        if (document.body) {
            document.body.setAttribute('data-light-tone', t.id);
            document.body.setAttribute('data-theme', targetTheme);
            document.body.classList.toggle('light-theme', !t.isDark);
            document.body.classList.toggle('dark-theme', !!t.isDark);
        }
        try {
            document.cookie = 'magaies_light_tone=' + encodeURIComponent(t.id) + '; path=/; max-age=315360000; SameSite=Lax';
            document.cookie = 'magaies_active_theme=' + encodeURIComponent(targetTheme) + '; path=/; max-age=315360000; SameSite=Lax';
        } catch (_e) {}
        const _s = (k, v) => { try { localStorage.setItem(k, v); } catch(_e) { try { ['erp_items_v1','erp_global_units_v1','erp_auto_backups'].forEach(rk => localStorage.removeItem(rk)); localStorage.setItem(k, v); } catch(_e2) {} } };
        _s('pur_light_tone_palette', t.id);
        _s('magaies_light_tone', t.id);
        ['magaies_active_theme', 'magaiees_global_theme', 'magaiees_mobile_theme', 'ui_theme'].forEach(k => _s(k, targetTheme));
        _s('erp_theme', t.isDark ? 'dark' : 'light');
        ensureSplitStyleLast();
        window.runSalesStrictDaylightGuard();
        if (notify) {
            playSalesSystemAudioTone(1040);
            if (typeof showToast === 'function') showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تثبيت المظهر في كل البرنامج: ' + t.name, 'success');
        }
        if (!fromBroadcast && toneBc) {
            try { toneBc.postMessage({ type: 'TONE_SYNC', toneId: t.id }); } catch (_e) {}
        }
    };
    if (toneBc) {
        toneBc.addEventListener('message', (ev) => {
            if (ev.data && (ev.data.type === 'TONE_SYNC' || ev.data.type === 'TONE_CHANGED') && ev.data.toneId) {
                window.applyPurchaseLightTone(ev.data.toneId, false, true);
            }
        });
    }
    window.addEventListener('storage', (ev) => {
        if ((ev.key === 'magaies_light_tone' || ev.key === 'pur_light_tone_palette') && ev.newValue) {
            window.applyPurchaseLightTone(ev.newValue, false, true);
        }
    });
    const getCkSplitTone = () => { const m = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/); return m ? decodeURIComponent(m[1]) : ''; };
    const savedTone = getCkSplitTone() || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || document.documentElement.getAttribute('data-light-tone') || 'ivory_default';
    window.applyPurchaseLightTone(savedTone, false);

    window.openPurchaseLightTonePopover = function (anchorEl) {
        if (typeof window.openUniversalErpTonePopover === 'function' && window.openUniversalErpTonePopover !== window.openPurchaseLightTonePopover) {
            window.openUniversalErpTonePopover(anchorEl);
            return;
        }
        let pop = document.getElementById('pur-light-tone-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'pur-light-tone-popover';
            document.body.appendChild(pop);
            document.addEventListener('click', (e) => {
                const isThemeBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn, .sales-theme-tone-btn');
                if (pop.style.display !== 'none' && !pop.contains(e.target) && !isThemeBtn) pop.style.display = 'none';
            });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDarkNow = !document.documentElement.classList.contains('light-theme');
        pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:265px;max-width:min(92vw,320px);max-height:82vh;overflow-y:auto;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
        const cur = document.documentElement.getAttribute('data-light-tone') || (isDarkNow ? 'dark_midnight' : 'ivory_default');
        const curSound = localStorage.getItem(SOUND_TONE_KEY) || 'crystal';
        const hdrCol = isDarkNow ? '#fde047' : '#0F172A';
        pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 المظهر ونغمات النظام (${TONES.length})</span><span style="font-size:0.75rem;color:${isDarkNow?'#94a3b8':'#0F172A'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#0F172A'}!important;">مطابق للمشتريات 100%</span></div>` +
            `<div style="max-height:310px;overflow-y:auto;padding-left:2px;">` +
            TONES.map(t => {
                const txtCol = t.isDark ? '#ffffff' : '#0F172A';
                const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                return `<button type="button" data-tone-dark="${t.isDark?'1':'0'}" onclick="window.applyPurchaseLightTone('${t.id}',true);document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:6px 10px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.86rem;text-align:right;"><span style="width:26px;height:26px;border-radius:7px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur===t.id?`<span style="color:${t.isDark?'#fde047':'#047857'}!important;-webkit-text-fill-color:${t.isDark?'#fde047':'#047857'}!important;">✓</span>`:''}</button>`;
            }).join('') +
            `</div>` +
            `<div style="margin-top:6px;padding-top:6px;border-top:1px solid rgba(148,163,184,0.35);display:grid;grid-template-columns:repeat(4,1fr);gap:4px;">` +
            SYSTEM_AUDIO_TONES.map(s => `<button type="button" onclick="window.setSalesSystemAudioTone('${s.id}');window.openPurchaseLightTonePopover();window.openPurchaseLightTonePopover();" style="padding:4px 4px;border-radius:7px;font-size:0.72rem;font-weight:900;cursor:pointer;border:${curSound===s.id?'2px solid #10b981':'1px solid rgba(148,163,184,0.4)'}!important;background:${curSound===s.id?'rgba(16,185,129,0.22)':'transparent'}!important;color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;">${s.name}</button>`).join('') +
            `</div>`;
        const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 450, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 290, r.left)) + 'px';
        pop.style.display = 'block';
    };
    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        window.openPurchaseLightTonePopover(btn);
    }, true);

    // ═══ LAW 4: DUAL MODE (COMPACT / FULL) & STRICT BACK (رجوع) VS EXIT (خروج) SEQUENCE ═══
    const COMPACT_MODE_KEY = 'magaies_sales_compact_mode_v1';
    window.toggleSalesCompactFullMode = function (forceVal) {
        if (!document.body) return;
        const cur = document.body.getAttribute('data-sales-compact') === '1';
        const next = typeof forceVal === 'boolean' ? forceVal : !cur;
        if (next) document.body.setAttribute('data-sales-compact', '1');
        else document.body.removeAttribute('data-sales-compact');
        try { localStorage.setItem(COMPACT_MODE_KEY, next ? '1' : '0'); } catch (e) {}
        document.querySelectorAll('.sales-compact-mode-btn').forEach(btn => {
            btn.innerHTML = next ? '🌐 الوضع الكامل' : '⚡ الوضع المختصر';
            btn.title = next ? 'التبديل اللحظي إلى الوضع الكامل (إظهار كافة الحقول والأعمدة)' : 'التبديل اللحظي إلى الوضع المختصر السريع';
        });
        playSalesSystemAudioTone(next ? 980 : 760);
        if (typeof showToast === 'function') {
            showToast(next ? '⚡ تم تفعيل الوضع المختصر السريع (0ms)' : '🌐 تم تفعيل الوضع الكامل الشامل (0ms)', 'success');
        }
    };
    if (localStorage.getItem(COMPACT_MODE_KEY) === '1' && document.body) {
        document.body.setAttribute('data-sales-compact', '1');
    }

    // Strict "رجوع" (Step-by-step LIFO Back) vs "خروج" (Direct Exit to Main Unified Dashboard)
    window.salesConstitutionalBack = function () {
        playSalesSystemAudioTone(640);
        // 1. Close any open modal or popover first
        const fcModal = document.querySelector('.sales-fc1-modal-overlay');
        if (fcModal) { fcModal.remove(); return; }
        const activeModal = document.querySelector('.modal-overlay.active, .modal-overlay[style*="display: flex"], .modal-overlay[style*="display: block"]');
        if (activeModal) {
            activeModal.classList.remove('active');
            activeModal.style.display = 'none';
            return;
        }
        const popIds = ['pur-light-tone-popover', 'share-popup-menu', 'auto-send-sales-dropdown-menu', 'sales-editor-micro-hud-guide'];
        for (let i = 0; i < popIds.length; i++) {
            const p = document.getElementById(popIds[i]);
            if (p && p.style.display !== 'none' && p.style.display !== '') {
                p.style.display = 'none';
                return;
            }
        }
        // 2. If inside Split Board Step 2, return to Split Select Step 1
        const splitView = document.getElementById('split-invoice-view');
        const splitBoard = document.getElementById('split-board-step');
        if (splitView && splitView.style.display !== 'none' && splitBoard && splitBoard.style.display !== 'none') {
            if (typeof window.cancelCurrentSplit === 'function') window.cancelCurrentSplit();
            return;
        }
        // 3. If inside Invoice Editor or Split View, return to Sales Hub View
        const editorView = document.getElementById('invoice-editor-view');
        const hubView = document.getElementById('sales-hub-view');
        if (hubView && ((editorView && editorView.style.display !== 'none') || (splitView && splitView.style.display !== 'none'))) {
            if (typeof window.showSalesHub === 'function') {
                window.showSalesHub();
                return;
            }
        }
        // 4. Otherwise step back in LIFO history
        if (typeof window.magaiesGoBackStepLIFO === 'function') window.magaiesGoBackStepLIFO();
        else if (typeof window.magaieesGoBack === 'function') window.magaieesGoBack();
        else window.location.href = 'magaiees_unified_dashboard.php';
    };

    window.salesConstitutionalExit = function () {
        playSalesSystemAudioTone(520);
        if (typeof window.saveSalesCrashVaultDraft === 'function') window.saveSalesCrashVaultDraft();
        if (typeof window.magaiesExitToMainDashboard === 'function') {
            window.magaiesExitToMainDashboard();
        } else {
            window.location.href = 'magaiees_unified_dashboard.php';
        }
    };

    // ═══ LAW 5: ANTI-EXPLOSION POPOVER VIEWPORT BOUNDARY GUARD ═══
    window.clampSalesOpenPopoversToViewport = function () {
        const pops = document.querySelectorAll('#customer-dropdown-list, .custom-dropdown-list, #quick-item-dropdown-list, .item-dropdown-list, #share-popup-menu, #auto-send-sales-dropdown-menu, #pur-light-tone-popover');
        pops.forEach(p => {
            if (!p || p.style.display === 'none' || p.offsetParent === null) return;
            const r = p.getBoundingClientRect();
            if (r.bottom > window.innerHeight - 8) {
                const avail = Math.max(140, window.innerHeight - r.top - 12);
                p.style.maxHeight = avail + 'px';
            }
            if (r.left < 6) {
                p.style.left = '8px';
            }
        });
    };
    document.addEventListener('click', () => setTimeout(window.clampSalesOpenPopoversToViewport, 30), true);

    // ═══ LAW 6: SMART TEXT PREVIEW CARD ON HOVER + ARABIC DIGIT NORMALIZATION + INLINE CALCULATOR ═══
    function getOrCreateSmartPreviewCard() {
        let card = document.getElementById('sales-smart-text-preview-card');
        if (!card && document.body) {
            card = document.createElement('div');
            card.id = 'sales-smart-text-preview-card';
            card.style.cssText = 'display:none;position:fixed;z-index:2147483646;max-width:440px;padding:8px 12px;border-radius:10px;background:#0F172A;color:#FFFFFF;border:2px solid #38bdf8;box-shadow:0 14px 32px rgba(0,0,0,0.65);font-family:Cairo,Tajawal,sans-serif;font-size:0.88rem;font-weight:900;line-height:1.5;pointer-events:none;direction:rtl;word-break:break-word;';
            document.body.appendChild(card);
        }
        return card;
    }
    document.addEventListener('mouseover', function (e) {
        const t = e.target;
        if (!t || !t.closest || !t.closest('#sales-proportional-root')) return;
        let txt = '';
        if (t.tagName === 'INPUT' && (t.type === 'text' || !t.type)) {
            txt = (t.value || '').trim();
        } else if (t.tagName === 'TD' || t.tagName === 'TH') {
            if (!t.querySelector('input, button, select')) txt = (t.textContent || '').trim();
        }
        const card = getOrCreateSmartPreviewCard();
        if (!card) return;
        if (txt.length >= 22 || (txt.length >= 10 && t.scrollWidth > t.clientWidth + 4)) {
            card.textContent = '🔍 المعاينة الكاملة: ' + txt;
            const r = t.getBoundingClientRect();
            card.style.top = Math.max(8, r.top - 44) + 'px';
            card.style.left = Math.max(10, Math.min(window.innerWidth - 360, r.left)) + 'px';
            card.style.display = 'block';
        } else if (card.style.display === 'block') {
            card.style.display = 'none';
        }
    }, { passive: true });
    document.addEventListener('mouseout', function () {
        const card = document.getElementById('sales-smart-text-preview-card');
        if (card && card.style.display === 'block') card.style.display = 'none';
    }, { passive: true });

    // Real-time Arabic/Persian Digit Normalization (١٢٣ -> 123)
    const AR_DIGITS = '٠١٢٣٤٥٦٧٨٩', FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
    function normDigits(s) {
        return String(s || '').replace(/[٠-٩]/g, d => AR_DIGITS.indexOf(d)).replace(/[۰-۹]/g, d => FA_DIGITS.indexOf(d));
    }
    document.addEventListener('input', function (e) {
        const el = e.target;
        if (!el || el.tagName !== 'INPUT' || el.type === 'date') return;
        const v = el.value;
        if (/[٠-٩۰-۹]/.test(v)) {
            const norm = normDigits(v);
            if (norm !== v) {
                const pos = el.selectionStart;
                el.value = norm;
                try { if (typeof pos === 'number') el.setSelectionRange(pos, pos); } catch (_) {}
            }
        }
    }, true);

    // ═══ LAW 7: CRASH VAULT (AUTO-SAVE & 1-CLICK RESTORE) + MULTI-STEP UNDO / REDO (CTRL+Z / CTRL+Y) ═══
    const CRASH_VAULT_KEY = 'magaies_sales_crash_vault_v1';
    const undoStack = [];
    const redoStack = [];
    let isApplyingUndoRedo = false;

    document.addEventListener('focusin', function (e) {
        const el = e.target;
        if (el && (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA')) {
            el._prevConstitutionalVal = el.value;
        }
    }, true);

    document.addEventListener('change', function (e) {
        if (isApplyingUndoRedo) return;
        const el = e.target;
        if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'SELECT' && el.tagName !== 'TEXTAREA')) return;
        const prev = el._prevConstitutionalVal !== undefined ? el._prevConstitutionalVal : '';
        const next = el.value;
        if (prev !== next) {
            undoStack.push({ el: el, prev: prev, next: next });
            if (undoStack.length > 60) undoStack.shift();
            redoStack.length = 0;
            el._prevConstitutionalVal = next;
        }
        window.saveSalesCrashVaultDraft();
    }, true);

    window.triggerSalesConstitutionalUndo = function () {
        const item = undoStack.pop();
        if (!item || !item.el) {
            if (typeof window.triggerSalesFc1InstantUndo === 'function') window.triggerSalesFc1InstantUndo();
            return;
        }
        isApplyingUndoRedo = true;
        redoStack.push(item);
        item.el.value = item.prev;
        item.el._prevConstitutionalVal = item.prev;
        item.el.dispatchEvent(new Event('input', { bubbles: true }));
        item.el.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof window.recalcAll === 'function') window.recalcAll();
        isApplyingUndoRedo = false;
        playSalesSystemAudioTone(680);
        if (typeof showToast === 'function') showToast('↩️ تم التراجع (Undo) واستعادة القيمة السابقة', 'info');
    };

    window.triggerSalesConstitutionalRedo = function () {
        const item = redoStack.pop();
        if (!item || !item.el) {
            if (typeof showToast === 'function') showToast('↪️ لا توجد خطوة لاحقة للإعادة (Redo)', 'info');
            return;
        }
        isApplyingUndoRedo = true;
        undoStack.push(item);
        item.el.value = item.next;
        item.el._prevConstitutionalVal = item.next;
        item.el.dispatchEvent(new Event('input', { bubbles: true }));
        item.el.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof window.recalcAll === 'function') window.recalcAll();
        isApplyingUndoRedo = false;
        playSalesSystemAudioTone(820);
        if (typeof showToast === 'function') showToast('↪️ تمت الإعادة (Redo) بنجاح', 'success');
    };

    let _vaultTimer = 0;
    const SPLIT_CRASH_VAULT_KEY = 'magaies_split_crash_vault_v1';
    window.saveSalesCrashVaultDraft = function () {
        clearTimeout(_vaultTimer);
        _vaultTimer = setTimeout(() => {
            try {
                const splitView = document.getElementById('split-invoice-view');
                if (splitView && splitView.style.display !== 'none') {
                    const splitQtys = {};
                    splitView.querySelectorAll('#split-items-body input[id^="split-qty-"], #split-items-body input[id^="split-subqty-"]').forEach(inp => {
                        if (inp.id) splitQtys[inp.id] = inp.value;
                    });
                    const searchVal = (document.getElementById('split-invoice-search') || {}).value || '';
                    const nlpVal = (document.getElementById('split-fc1-nlp-input') || {}).value || '';
                    const activeInvId = (window.activeSplitInvoice && (window.activeSplitInvoice.id || window.activeSplitInvoice.invoiceNo)) || '';
                    if (Object.keys(splitQtys).length > 0 || searchVal || nlpVal || activeInvId) {
                        localStorage.setItem(SPLIT_CRASH_VAULT_KEY, JSON.stringify({
                            ts: Date.now(),
                            timeStr: new Date().toLocaleTimeString('ar-EG'),
                            activeInvId: activeInvId,
                            searchVal: searchVal,
                            nlpVal: nlpVal,
                            splitQtys: splitQtys
                        }));
                    }
                    if (typeof window.saveCurrentSplitDraftState === 'function') {
                        try { window.saveCurrentSplitDraftState(); } catch (_) {}
                    }
                    return;
                }
                const editor = document.getElementById('invoice-editor-view');
                if (!editor || editor.style.display === 'none') return;
                const rows = [];
                document.querySelectorAll('#pos-grid tbody tr').forEach(tr => {
                    const itemInp = tr.querySelector('input[id^="item-search-"], td[data-col-id="col_item"] input');
                    const qtyInp = tr.querySelector('input[id^="qty-"], td[data-col-id="col_qty"] input');
                    const priceInp = tr.querySelector('input[id^="price-"], td[data-col-id="col_price"] input');
                    const noteInp = tr.querySelector('input[id^="note-"], td[data-col-id="col_note"] input');
                    if (itemInp && itemInp.value.trim()) {
                        rows.push({
                            item: itemInp.value,
                            qty: qtyInp ? qtyInp.value : '1',
                            price: priceInp ? priceInp.value : '0',
                            note: noteInp ? noteInp.value : ''
                        });
                    }
                });
                const customer = (document.getElementById('hdr-customer-search') || {}).value || '';
                const desc = (document.getElementById('hdr-description') || {}).value || '';
                if (!customer && !desc && rows.length === 0) return;
                const payload = {
                    ts: Date.now(),
                    timeStr: new Date().toLocaleTimeString('ar-EG'),
                    customer: customer,
                    customerId: (document.getElementById('hdr-customer') || {}).value || '',
                    date: (document.getElementById('hdr-date') || {}).value || '',
                    currency: (document.getElementById('hdr-currency') || {}).value || 'SDG',
                    exchangeRate: (document.getElementById('hdr-exchange-rate') || {}).value || '',
                    description: desc,
                    rows: rows
                };
                localStorage.setItem(CRASH_VAULT_KEY, JSON.stringify(payload));
            } catch (_) {}
        }, 250);
    };

    window.restoreSalesCrashVaultDraft = function () {
        try {
            const splitView = document.getElementById('split-invoice-view');
            if (splitView && splitView.style.display !== 'none') {
                const rawSplit = localStorage.getItem(SPLIT_CRASH_VAULT_KEY);
                if (!rawSplit) {
                    if (typeof showToast === 'function') showToast('🛡️ خزنة الطوارئ للتقسيم (Crash Vault): لا توجد مسودة تقسيم محفوظة حالياً', 'info');
                    return;
                }
                const sData = JSON.parse(rawSplit);
                if (sData.searchVal && document.getElementById('split-invoice-search')) {
                    const sInp = document.getElementById('split-invoice-search');
                    sInp.value = sData.searchVal;
                    if (typeof window.filterSplitInvoices === 'function') window.filterSplitInvoices();
                }
                if (sData.nlpVal && document.getElementById('split-fc1-nlp-input')) {
                    document.getElementById('split-fc1-nlp-input').value = sData.nlpVal;
                }
                if (sData.splitQtys && typeof sData.splitQtys === 'object') {
                    Object.keys(sData.splitQtys).forEach(id => {
                        const el = document.getElementById(id);
                        if (el) {
                            el.value = sData.splitQtys[id];
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                }
                playSalesSystemAudioTone(1080);
                if (typeof showToast === 'function') showToast('🛡️ تمت استعادة مسودة تقسيم الفاتورة من خزنة الطوارئ (Crash Vault) بنجاح!', 'success');
                return;
            }
            const raw = localStorage.getItem(CRASH_VAULT_KEY);
            if (!raw) {
                if (typeof showToast === 'function') showToast('🛡️ خزنة الطوارئ (Crash Vault): لا توجد مسودة معلقة حالياً', 'info');
                return;
            }
            const data = JSON.parse(raw);
            if (data.customer && document.getElementById('hdr-customer-search')) {
                document.getElementById('hdr-customer-search').value = data.customer;
            }
            if (data.customerId && document.getElementById('hdr-customer')) {
                document.getElementById('hdr-customer').value = data.customerId;
            }
            if (data.description && document.getElementById('hdr-description')) {
                document.getElementById('hdr-description').value = data.description;
            }
            if (Array.isArray(data.rows) && data.rows.length > 0) {
                const tbody = document.querySelector('#pos-grid tbody');
                if (tbody) {
                    while (tbody.querySelectorAll('tr').length < data.rows.length && typeof window.addRow === 'function') {
                        window.addRow();
                    }
                    const trs = tbody.querySelectorAll('tr');
                    data.rows.forEach((r, idx) => {
                        const tr = trs[idx];
                        if (!tr) return;
                        const itemInp = tr.querySelector('input[id^="item-search-"], td[data-col-id="col_item"] input');
                        const qtyInp = tr.querySelector('input[id^="qty-"], td[data-col-id="col_qty"] input');
                        const priceInp = tr.querySelector('input[id^="price-"], td[data-col-id="col_price"] input');
                        const noteInp = tr.querySelector('input[id^="note-"], td[data-col-id="col_note"] input');
                        if (itemInp) itemInp.value = r.item || '';
                        if (qtyInp) { qtyInp.value = r.qty || '1'; qtyInp.dispatchEvent(new Event('input', { bubbles: true })); }
                        if (priceInp) { priceInp.value = r.price || '0'; priceInp.dispatchEvent(new Event('input', { bubbles: true })); }
                        if (noteInp) noteInp.value = r.note || '';
                    });
                }
            }
            if (typeof window.recalcAll === 'function') window.recalcAll();
            playSalesSystemAudioTone(1080);
            if (typeof showToast === 'function') showToast('🛡️ تمت استعادة مسودة الفاتورة من خزنة الطوارئ (Crash Vault) بنجاح!', 'success');
        } catch (_) {}
    };
    document.addEventListener('input', () => window.saveSalesCrashVaultDraft(), { passive: true });

    // ═══ LAW 8: SMOOTH ENTER KEY NAVIGATION ACROSS HEADER, POS GRID & SPLIT VIEW ═══
    document.addEventListener('keydown', function (ev) {
        if (ev.ctrlKey && (ev.key === 'y' || ev.key === 'Y' || ev.key === 'غ')) {
            ev.preventDefault();
            window.triggerSalesConstitutionalRedo();
            return;
        }
        if (ev.ctrlKey && (ev.key === 'z' || ev.key === 'Z' || ev.key === 'ئ') && !ev.shiftKey) {
            if (!document.activeElement || document.activeElement.tagName !== 'INPUT' || undoStack.length > 0) {
                ev.preventDefault();
                window.triggerSalesConstitutionalUndo();
                return;
            }
        }
        if (ev.key === 'Enter' && !ev.shiftKey && !ev.ctrlKey && !ev.altKey) {
            const t = ev.target;
            if (!t || !t.closest || !t.closest('#invoice-editor-view, #split-invoice-view')) return;
            // Inline calculator check first
            if (t.tagName === 'INPUT' && t.type !== 'date') {
                const raw = normDigits(t.value).trim();
                if (/^[0-9.,+\-*/()\s]+$/.test(raw) && /[+\-*/]/.test(raw.slice(1))) {
                    try {
                        const res = Function('"use strict";return (' + raw.replace(/,/g, '') + ')')();
                        if (typeof res === 'number' && isFinite(res)) {
                            t.value = Math.round(res * 100) / 100;
                            t.dispatchEvent(new Event('input', { bubbles: true }));
                            t.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    } catch (_) {}
                }
            }
            if (t.closest('#split-invoice-view')) {
                if (t.closest('#split-items-body')) {
                    ev.preventDefault();
                    const allSplitInputs = Array.from(document.querySelectorAll('#split-items-body input:not([type="hidden"]):not([disabled])')).filter(el => el.offsetParent !== null);
                    const curIdx = allSplitInputs.indexOf(t);
                    if (curIdx !== -1 && curIdx + 1 < allSplitInputs.length) {
                        allSplitInputs[curIdx + 1].focus();
                        if (typeof allSplitInputs[curIdx + 1].select === 'function') allSplitInputs[curIdx + 1].select();
                    }
                }
                return;
            }
            const headerOrder = ['hdr-date', 'hdr-customer-search', 'hdr-warehouse', 'hdr-description', 'hdr-cost-center'];
            const hIdx = headerOrder.indexOf(t.id);
            if (hIdx !== -1) {
                ev.preventDefault();
                for (let i = hIdx + 1; i < headerOrder.length; i++) {
                    const nxt = document.getElementById(headerOrder[i]);
                    if (nxt && nxt.offsetParent !== null) {
                        nxt.focus();
                        if (typeof nxt.select === 'function') nxt.select();
                        return;
                    }
                }
                const firstGridInput = document.querySelector('#pos-grid tbody tr input:not([type="hidden"])');
                if (firstGridInput) {
                    firstGridInput.focus();
                    if (typeof firstGridInput.select === 'function') firstGridInput.select();
                }
            }
        }
    }, true);

    // ═══ INJECT SOVEREIGN CONSTITUTIONAL CONTROLS INTO ?action=new (#invoice-editor-view), ?action=split (#split-invoice-view) & #sales-hub-view ═══
    function injectSalesConstitutionalToolbars() {
        const isCompact = document.body && document.body.getAttribute('data-sales-compact') === '1';
        // 1. Inside #invoice-editor-view (#items-panel .panel-header-row)
        const editorHeader = document.querySelector('#invoice-editor-view #items-panel .panel-header-row > div:last-child');
        if (editorHeader && !document.getElementById('sales-editor-constitution-1of3-bar')) {
            const bar = document.createElement('div');
            bar.id = 'sales-editor-constitution-1of3-bar';
            bar.style.cssText = 'display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;';
            bar.innerHTML =
                `<button type="button" class="sales-theme-tone-btn m7-solid-badge-guard" onclick="window.openPurchaseLightTonePopover(this)" style="height:36px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#d97706,#b45309);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.83rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:5px;" title="القانون (2): المظهر ونغمات النظام (13 لوناً + 4 نغمات)">🎨 المظهر (13)</button>` +
                `<button type="button" class="sales-compact-mode-btn m7-solid-badge-guard" onclick="window.toggleSalesCompactFullMode()" style="height:36px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#0284c7,#0369a1);border:1.5px solid #7dd3fc;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;" title="القانون (4): التبديل اللحظي بين الوضع المختصر والوضع الكامل">${isCompact ? '🌐 الوضع الكامل' : '⚡ الوضع المختصر'}</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="if(window.toggleSalesFc1SunlightMode)window.toggleSalesFc1SunlightMode();" style="height:36px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#ca8a04,#a16207);border:1.5px solid #fef08a;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;" title="القانون (8): وضع وضوح شمس السوق">☀️ شمس السوق</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.triggerSalesConstitutionalUndo()" style="height:36px;padding:0 9px;border-radius:9px;background:linear-gradient(135deg,#334155,#1e293b);border:1.5px solid #94a3b8;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (7): تراجع فوري (Ctrl+Z)">↩️ تراجع</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.triggerSalesConstitutionalRedo()" style="height:36px;padding:0 9px;border-radius:9px;background:linear-gradient(135deg,#334155,#1e293b);border:1.5px solid #94a3b8;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (7): إعادة (Ctrl+Y)">↪️ إعادة</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.restoreSalesCrashVaultDraft()" style="height:36px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);border:1.5px solid #6ee7b7;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (7): استعادة مسودة الفاتورة من خزنة الطوارئ (Crash Vault)">🛡️ المسودة</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalBack()" style="height:36px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#b45309,#78350f);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;" title="القانون (4): رجوع متسلسل خطوة للخلف (Esc)">↩️ رجوع</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalExit()" style="height:36px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#991b1b);border:1.5px solid #fca5a5;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;" title="القانون (4): خروج مباشر للشاشة الرئيسية">🚪 خروج</button>`;
            editorHeader.insertBefore(bar, editorHeader.firstChild);
        }
        // 2. Inside #split-invoice-view (?action=split)
        const splitCard = document.querySelector('#split-invoice-view > .neo-card');
        if (splitCard && !document.getElementById('split-constitution-1of3-bar')) {
            const sBar = document.createElement('div');
            sBar.id = 'split-constitution-1of3-bar';
            sBar.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;padding:8px 12px;border-radius:12px;background:rgba(15,23,42,0.85);border:2px solid rgba(56,189,248,0.55);';
            sBar.innerHTML =
                `<div style="display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap;">` +
                    `<span class="m7-solid-badge-guard" style="padding:5px 12px;border-radius:9px;background:linear-gradient(135deg,#0284c7,#1d4ed8);border:1.5px solid #7dd3fc;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.88rem;font-weight:900;">✂️ بوابة تقسيم الفواتير الذكية (Split Protocol)</span>` +
                    `<div style="display:inline-flex;align-items:center;gap:4px;background:rgba(15,23,42,0.9);border:1.5px solid #38bdf8;border-radius:9px;padding:2px 6px;">` +
                        `<button type="button" class="m7-solid-badge-guard" onclick="if(window.setSalesFc1ProportionalZoom)window.setSalesFc1ProportionalZoom((window.__salesFc1ZoomLevel||1)-0.1);" style="height:28px;padding:0 8px;border-radius:6px;background:#1e293b;border:1px solid #64748b;color:#fff!important;-webkit-text-fill-color:#fff!important;font-weight:900;cursor:pointer;" title="تصغير (Ctrl + رولة الماوس)">➖</button>` +
                        `<button type="button" class="m7-solid-badge-guard" onclick="if(window.setSalesFc1ProportionalZoom)window.setSalesFc1ProportionalZoom(1.0);" style="height:28px;padding:0 9px;border-radius:6px;background:#0369a1;border:1px solid #7dd3fc;color:#fff!important;-webkit-text-fill-color:#fff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="إعادة الضبط القياسي 100% (1x)">100%</button>` +
                        `<button type="button" class="m7-solid-badge-guard" onclick="if(window.setSalesFc1ProportionalZoom)window.setSalesFc1ProportionalZoom((window.__salesFc1ZoomLevel||1)+0.1);" style="height:28px;padding:0 8px;border-radius:6px;background:#1e293b;border:1px solid #64748b;color:#fff!important;-webkit-text-fill-color:#fff!important;font-weight:900;cursor:pointer;" title="تكبير تلسكوبي حتى 10x (Ctrl + رولة الماوس)">➕</button>` +
                    `</div>` +
                `</div>` +
                `<div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;">` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="if(window.openSalesFc3Global7ChartsModal)window.openSalesFc3Global7ChartsModal('ALL');" style="height:34px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#0284c7,#0369a1);border:1.5px solid #7dd3fc;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="البند الختامي (1): دستور السباعية العالمية للمخططات التفاعلية (7/7) في 0ms">📈 المخططات (7/7)</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="if(window.openSalesFc2ForensicOwnerRadarModal)window.openSalesFc2ForensicOwnerRadarModal();" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#991b1b);border:1.5px solid #fca5a5;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="البند الختامي (2): رادار الذكاء الاصطناعي الجنائي للمالك (🟡/🟠/🔴) والصندوق الأسود">🕵️‍♂️ الرادار الجنائي</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="if(window.openSalesFc2AntiEmbezzlementLocksModal)window.openSalesFc2AntiEmbezzlementLocksModal();" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#7c3aed,#5b21b6);border:1.5px solid #d8b4fe;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="البند الختامي (3): قفل الطبعة (1) والجرد الأعمى وبطاقة واتساب">🔒 الأقفال والجرد الأعمى</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="if(window.openSalesFc2MasterAudit10AdditionsModal)window.openSalesFc2MasterAudit10AdditionsModal();" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#0d9488,#0f766e);border:1.5px solid #5eead4;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="البند الختامي (6): بوابة الإقرار الجراحي (مراجعة 50/50 + 10 إضافات ذكية + زيرو خطأ 100%)">🏆 الإقرار (50/50)</button>` +
                    `<button type="button" class="sales-theme-tone-btn m7-solid-badge-guard" onclick="window.openPurchaseLightTonePopover(this)" style="height:34px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#d97706,#b45309);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:5px;" title="القانون (2): المظهر ونغمات النظام (13 لوناً + 4 نغمات)">🎨 المظهر (13)</button>` +
                    `<button type="button" class="sales-compact-mode-btn m7-solid-badge-guard" onclick="window.toggleSalesCompactFullMode()" style="height:34px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#0284c7,#0369a1);border:1.5px solid #7dd3fc;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (4): التبديل اللحظي بين الوضع المختصر والوضع الكامل">${isCompact ? '🌐 الوضع الكامل' : '⚡ الوضع المختصر'}</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="if(window.toggleSalesFc1SunlightMode)window.toggleSalesFc1SunlightMode();" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#ca8a04,#a16207);border:1.5px solid #fef08a;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (8): وضع وضوح شمس السوق">☀️ شمس السوق</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="window.triggerSalesConstitutionalUndo()" style="height:34px;padding:0 9px;border-radius:9px;background:linear-gradient(135deg,#334155,#1e293b);border:1.5px solid #94a3b8;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="القانون (7): تراجع فوري (Ctrl+Z)">↩️ تراجع</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="window.triggerSalesConstitutionalRedo()" style="height:34px;padding:0 9px;border-radius:9px;background:linear-gradient(135deg,#334155,#1e293b);border:1.5px solid #94a3b8;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="القانون (7): إعادة (Ctrl+Y)">↪️ إعادة</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="window.restoreSalesCrashVaultDraft()" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#059669,#047857);border:1.5px solid #6ee7b7;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.78rem;font-weight:900;cursor:pointer;" title="القانون (7): استعادة مسودة التقسيم من خزنة الطوارئ (Crash Vault)">🛡️ المسودة</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalBack()" style="height:34px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#b45309,#78350f);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (4): رجوع متسلسل خطوة للخلف (Esc)">↩️ رجوع</button>` +
                    `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalExit()" style="height:34px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#991b1b);border:1.5px solid #fca5a5;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;" title="القانون (4): خروج مباشر للشاشة الرئيسية">🚪 خروج</button>` +
                `</div>`;
            splitCard.insertBefore(sBar, splitCard.firstChild);
        }
        // 3. Inside #sales-hub-view
        const hubHeader = document.querySelector('#sales-hub-view .neo-card-title > div:first-child');
        if (hubHeader && !document.getElementById('sales-hub-constitution-1of3-bar')) {
            const wrap = document.createElement('div');
            wrap.id = 'sales-hub-constitution-1of3-bar';
            wrap.style.cssText = 'display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;';
            wrap.innerHTML =
                `<button type="button" class="sales-theme-tone-btn m7-solid-badge-guard" onclick="window.openPurchaseLightTonePopover(this)" style="height:34px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#d97706,#b45309);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.82rem;font-weight:900;cursor:pointer;">🎨 المظهر (13)</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalBack()" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#b45309,#78350f);border:1.5px solid #fde047;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;">↩️ رجوع</button>` +
                `<button type="button" class="m7-solid-badge-guard" onclick="window.salesConstitutionalExit()" style="height:34px;padding:0 10px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#991b1b);border:1.5px solid #fca5a5;color:#ffffff!important;-webkit-text-fill-color:#ffffff!important;font-size:0.8rem;font-weight:900;cursor:pointer;">🚪 خروج</button>`;
            hubHeader.appendChild(wrap);
        }
    }

    // 3D Financial Summary Bar & "جنيه ⇄ دولار" Toggle in #split-select-step
    window._splitShowUsd = false;
    function ensureSplitFinancialStrip() {
        const toolbar = document.getElementById('split-search-toolbar');
        if (!toolbar || document.getElementById('split-fin-summary-strip')) return;
        const strip = document.createElement('div');
        strip.id = 'split-fin-summary-strip';
        strip.style.cssText = 'display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; padding:8px 12px; border-radius:12px; background:rgba(15,23,42,0.75); border:2px solid rgba(56,189,248,0.5);';
        strip.innerHTML =
            '<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">' +
                '<span class="split-3d-cap" style="background:linear-gradient(180deg,#334155,#0f172a); border:1.5px solid #94a3b8; padding:4px 12px; border-radius:8px; font-size:0.88rem;">📊 الفواتير: <strong id="split-cap-count" style="font-size:1.05rem;">0</strong></span>' +
                '<span class="split-3d-cap" style="background:linear-gradient(180deg,#0284c7,#075985); border:1.5px solid #7dd3fc; padding:4px 12px; border-radius:8px; font-size:0.88rem;">💰 الإجمالي: <strong id="split-cap-total" style="font-size:1.12rem;">0</strong> <small class="split-cap-curr">ج.س</small></span>' +
                '<span class="split-3d-cap" style="background:linear-gradient(180deg,#059669,#065f46); border:1.5px solid #6ee7b7; padding:4px 12px; border-radius:8px; font-size:0.88rem;">💵 المعادل بالدولار: <strong id="split-cap-usd" style="font-size:1.08rem;">0.00 $</strong></span>' +
            '</div>' +
            '<button type="button" id="split-fin-curr-toggle-btn" onclick="window.toggleSplitConstitutionCurrency()" style="background:linear-gradient(180deg,#0284c7 0%,#0369a1 52%,#075985 100%); border:1.8px solid #7dd3fc; border-bottom:3px solid #082f49; padding:5px 14px; border-radius:8px; cursor:pointer; font-size:0.88rem;">💱 جنيه ⇄ دولار</button>';
        toolbar.parentNode.insertBefore(strip, toolbar.nextSibling);
    }

    window.toggleSplitConstitutionCurrency = function () {
        window._splitShowUsd = !window._splitShowUsd;
        const btn = document.getElementById('split-fin-curr-toggle-btn');
        if (btn) btn.innerHTML = window._splitShowUsd ? '💵 دولار ($) ⇄' : '💱 جنيه ⇄ دولار';
        refreshSplitConstitutionEnhancements();
    };

    function refreshSplitConstitutionEnhancements() {
        ensureSplitFinancialStrip();
        injectSalesConstitutionalToolbars();
        window.runSalesStrictDaylightGuard();
        const rateEl = document.getElementById('hdr-exchange-rate');
        const rate = (rateEl && parseFloat(rateEl.value) > 1) ? parseFloat(rateEl.value) : (parseFloat(localStorage.getItem('magaies_usd_exchange_rate') || '2650') || 2650);
        const invBody = document.getElementById('split-invoices-body');
        if (invBody) {
            let cnt = 0, sumSdg = 0;
            invBody.querySelectorAll('tr').forEach(tr => {
                if (tr.style.display === 'none' || tr.querySelector('td[colspan]')) return;
                cnt++;
                const tds = tr.querySelectorAll('td');
                if (tds[2] && !tds[2].querySelector('.split-ledger-btn')) {
                    const cName = tds[2].textContent.trim();
                    if (cName && cName !== '---') {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'split-ledger-btn';
                        btn.style.cssText = 'margin-right:8px; padding:2px 8px; border-radius:6px; background:linear-gradient(135deg,#0284c7,#0369a1); border:1.5px solid #7dd3fc; font-size:0.74rem; cursor:pointer;';
                        btn.innerHTML = '📖 أستاذ';
                        btn.onclick = (ev) => { ev.stopPropagation(); window.location.href = 'account-statement.php?type=customer&search=' + encodeURIComponent(cName); };
                        tds[2].appendChild(btn);
                    }
                }
                if (tds[4]) {
                    const raw = parseFloat(tds[4].getAttribute('data-raw-sdg') || tds[4].textContent.replace(/[^0-9.-]/g, '')) || 0;
                    if (!tds[4].hasAttribute('data-raw-sdg') && raw > 0) tds[4].setAttribute('data-raw-sdg', String(raw));
                    sumSdg += raw;
                }
            });
            const elCnt = document.getElementById('split-cap-count');
            const elTot = document.getElementById('split-cap-total');
            const elUsd = document.getElementById('split-cap-usd');
            if (elCnt) elCnt.textContent = cnt.toLocaleString('en-US');
            if (elTot) elTot.textContent = (window._splitShowUsd ? (sumSdg / rate) : sumSdg).toLocaleString('en-US', { maximumFractionDigits: window._splitShowUsd ? 2 : 0 });
            if (elUsd) elUsd.textContent = (sumSdg / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' $';
            document.querySelectorAll('.split-cap-curr').forEach(s => { s.textContent = window._splitShowUsd ? '$' : 'ج.س'; });
        }
        const itemsBody = document.getElementById('split-items-body');
        if (itemsBody) {
            itemsBody.querySelectorAll('tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds[2] && !tds[2].querySelector('.pur-price-lamp')) {
                    const lamp = document.createElement('span');
                    lamp.className = 'pur-price-lamp lamp-green';
                    lamp.style.marginLeft = '6px';
                    lamp.title = 'رادار الأسعار';
                    tds[2].insertBefore(lamp, tds[2].firstChild);
                }
                tr.querySelectorAll('input').forEach(inp => {
                    const v = String(inp.value || '').trim();
                    if (v === '0' || v === '0.00') inp.classList.add('is-zero-val');
                    else inp.classList.remove('is-zero-val');
                });
            });
        }
    }

    const splitView = document.getElementById('split-invoice-view');
    if (splitView && typeof MutationObserver !== 'undefined') {
        new MutationObserver(() => requestAnimationFrame(refreshSplitConstitutionEnhancements)).observe(splitView, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshSplitConstitutionEnhancements);
    } else {
        refreshSplitConstitutionEnhancements();
    }
    setTimeout(refreshSplitConstitutionEnhancements, 350);
})();
