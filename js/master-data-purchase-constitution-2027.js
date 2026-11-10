/**
 * MASTER DATA — PURCHASE DESIGN CONSTITUTION 2027 (سمة المشتريات 2027)
 * Applies the 9 Unified Tones (8 Light Tones + Royal Dark), 3D Financial Capsules,
 * Live SDG <-> USD Currency Toggle, Ledger (أستاذ) Quick Buttons, 3D Price Radar Lamps,
 * and Faint Zeros (.is-zero-val) across all 8 Master Data tabs and child modals without deleting any code.
 */
(function () {
    'use strict';
    if (window.__MD_PURCHASE_CONSTITUTION_2027_ACTIVE__) return;
    window.__MD_PURCHASE_CONSTITUTION_2027_ACTIVE__ = true;

    const SOUND_TONE_KEY = 'magaies_system_sound_tone_v1';
    const TONES = [
        { id: 'dark_midnight',   label: 'داكن كحلي ملكي (Dark)',   swatch: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true,  themeKey: 'midnight_obsidian' },
        { id: 'ivory_default',   label: 'عاجي دافئ (فاتح افتراضي)', swatch: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', isDark: false, themeKey: 'warm_cream' },
        { id: 'royal_bronze',    label: 'برونزي ملكي فاخر',      swatch: '#b07239', fg: '#d9a066', brd: '#78350f', isDark: false, themeKey: 'warm_cream' },
        { id: 'platinum_gray',   label: 'رمادي بلاتيني احترافي', swatch: '#64748b', fg: '#94a3b8', brd: '#334155', isDark: false, themeKey: 'exec_gray' },
        { id: 'crystal_silver',  label: 'فضي جليدي ناصع',        swatch: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', isDark: false, themeKey: 'ultra_white' },
        { id: 'sage_pearl',      label: 'لؤلؤي زمردي هادئ',      swatch: '#10b981', fg: '#6ee7b7', brd: '#047857', isDark: false, themeKey: 'warm_cream' },
        { id: 'sky_alabaster',   label: 'سماوي لازوردي فاتح',    swatch: '#0284c7', fg: '#38bdf8', brd: '#0369a1', isDark: false, themeKey: 'ultra_white' },
        { id: 'warm_mocha_dark', label: 'موكا صحراوي غامق',      swatch: '#6b4226', fg: '#9c6b43', brd: '#451a03', isDark: false, themeKey: 'warm_cream' },
        { id: 'deep_slate_dark', label: 'رصاصي فحمي غامق',       swatch: '#334155', fg: '#475569', brd: '#0f172a', isDark: false, themeKey: 'exec_gray' },
        { id: 'rose_blossom',    label: 'وردي ياقوتي نهاري',      swatch: '#e11d48', fg: '#fecdd3', brd: '#9f1239', isDark: false, themeKey: 'warm_cream' },
        { id: 'amber_sand',      label: 'كهرماني ذهبي مشرق',     swatch: '#d97706', fg: '#fde68a', brd: '#92400e', isDark: false, themeKey: 'warm_cream' },
        { id: 'lavender_mist',   label: 'بنفسجي ملكي هادئ',      swatch: '#7c3aed', fg: '#ddd6fe', brd: '#5b21b6', isDark: false, themeKey: 'ultra_white' }
    ];
    const SYSTEM_AUDIO_TONES = [
        { id: 'crystal',    name: '🔔 كريستال' },
        { id: 'soft',       name: '🎵 زمردي دافئ' },
        { id: 'mechanical', name: '⌨️ ميكانيكية' },
        { id: 'mute',       name: '🔇 صامت' }
    ];

    function injectConstitutionStyle() {
        let st = document.getElementById('md-purchase-constitution-2027-style');
        if (!st) {
            st = document.createElement('style');
            st.id = 'md-purchase-constitution-2027-style';
        }
        st.textContent = `
            :root, html.light-theme, body.light-theme {
                --lt-bg-main: #e4d7c5; --lt-bg-card: #f2eadf; --lt-bg-strip: #d8c8b2;
                --lt-bg-input: #ffffff; --lt-border: #85684a; --lt-text: #120500; --lt-subtext: #2a1004;
            }
            html.light-theme[data-light-tone="royal_bronze"], body.light-theme[data-light-tone="royal_bronze"] {
                --lt-bg-main: #d8c3a5; --lt-bg-card: #eae0d2; --lt-bg-strip: #cbb28f; --lt-bg-input: #ffffff; --lt-border: #705232; --lt-text: #120500;
            }
            html.light-theme[data-light-tone="platinum_gray"], body.light-theme[data-light-tone="platinum_gray"] {
                --lt-bg-main: #cbd5e1; --lt-bg-card: #e2e8f0; --lt-bg-strip: #b8c4d4; --lt-bg-input: #ffffff; --lt-border: #475569; --lt-text: #020617;
            }
            html.light-theme[data-light-tone="crystal_silver"], body.light-theme[data-light-tone="crystal_silver"] {
                --lt-bg-main: #dcdfe4; --lt-bg-card: #eef0f4; --lt-bg-strip: #cfd4dc; --lt-bg-input: #ffffff; --lt-border: #64748b; --lt-text: #000000;
            }
            html.light-theme[data-light-tone="sage_pearl"], body.light-theme[data-light-tone="sage_pearl"] {
                --lt-bg-main: #cfded6; --lt-bg-card: #e3ece7; --lt-bg-strip: #bed2c7; --lt-bg-input: #ffffff; --lt-border: #3d6b58; --lt-text: #021a10;
            }
            html.light-theme[data-light-tone="sky_alabaster"], body.light-theme[data-light-tone="sky_alabaster"] {
                --lt-bg-main: #cad9e8; --lt-bg-card: #e1ebf5; --lt-bg-strip: #b7cce0; --lt-bg-input: #ffffff; --lt-border: #3b668c; --lt-text: #031526;
            }
            html.light-theme[data-light-tone="warm_mocha_dark"], body.light-theme[data-light-tone="warm_mocha_dark"] {
                --lt-bg-main: #c7b299; --lt-bg-card: #dcd0c0; --lt-bg-strip: #b89f82; --lt-bg-input: #ffffff; --lt-border: #593e25; --lt-text: #120500;
            }
            html.light-theme[data-light-tone="deep_slate_dark"], body.light-theme[data-light-tone="deep_slate_dark"] {
                --lt-bg-main: #b0bec5; --lt-bg-card: #cfd8dc; --lt-bg-strip: #90a4ae; --lt-bg-input: #ffffff; --lt-border: #37474f; --lt-text: #000000;
            }
            html.light-theme[data-light-tone="rose_blossom"], body.light-theme[data-light-tone="rose_blossom"] {
                --lt-bg-main: #f5d0da; --lt-bg-card: #fce7ed; --lt-bg-strip: #eebbc9; --lt-bg-input: #ffffff; --lt-border: #881337; --lt-text: #1f030b;
            }
            html.light-theme[data-light-tone="amber_sand"], body.light-theme[data-light-tone="amber_sand"] {
                --lt-bg-main: #eedba5; --lt-bg-card: #f8edcc; --lt-bg-strip: #e2c983; --lt-bg-input: #ffffff; --lt-border: #78350f; --lt-text: #1c0a00;
            }
            html.light-theme[data-light-tone="lavender_mist"], body.light-theme[data-light-tone="lavender_mist"] {
                --lt-bg-main: #ddd6fe; --lt-bg-card: #ede9fe; --lt-bg-strip: #c4b5fd; --lt-bg-input: #ffffff; --lt-border: #4c1d95; --lt-text: #11052C;
            }

            /* 4 DARK TONES (dark_midnight, pure_oled, mocha_brown, slate_twilight) */
            html:not(.light-theme), body:not(.light-theme) {
                --dk-bg-main: #060b16; --dk-bg-card: #152342; --dk-bg-strip: #0d182e; --dk-border: #2b4570;
            }
            html:not(.light-theme)[data-light-tone="pure_oled"], body:not(.light-theme)[data-light-tone="pure_oled"] {
                --dk-bg-main: #010308; --dk-bg-card: #090d16; --dk-bg-strip: #050811; --dk-border: #0284c7;
            }
            html:not(.light-theme)[data-light-tone="mocha_brown"], body:not(.light-theme)[data-light-tone="mocha_brown"] {
                --dk-bg-main: #180d09; --dk-bg-card: #2b1810; --dk-bg-strip: #21120c; --dk-border: #d97706;
            }
            html:not(.light-theme)[data-light-tone="slate_twilight"], body:not(.light-theme)[data-light-tone="slate_twilight"] {
                --dk-bg-main: #0f172a; --dk-bg-card: #1e293b; --dk-bg-strip: #172033; --dk-border: #64748b;
            }
            html:not(.light-theme) body,
            html:not(.light-theme) .ambient-bg {
                background: var(--dk-bg-main, #010308) !important;
                color: #ffffff !important;
            }
            html:not(.light-theme) .md-card,
            html:not(.light-theme) .neon-glass-panel,
            html:not(.light-theme) .md-tabs-sidebar,
            html:not(.light-theme) .md-child-modal-card,
            html:not(.light-theme) .md-c1-modal-card {
                background: var(--dk-bg-card, #152342) !important;
                border: 3px solid var(--dk-border, #38bdf8) !important;
                color: #ffffff !important;
                box-shadow: 0 10px 26px rgba(0,0,0,0.65) !important;
            }
            html:not(.light-theme) .input-group,
            html:not(.light-theme) .pro-field-card {
                background: var(--dk-bg-strip, #0d182e) !important;
                border: 2.5px solid rgba(56, 189, 248, 0.72) !important;
            }
            html:not(.light-theme) .pro-field-label,
            html:not(.light-theme) .pro-field-label span,
            html:not(.light-theme) .input-group label,
            html:not(.light-theme) .input-group label span {
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff !important;
                font-weight: 900 !important;
                opacity: 1 !important;
            }
            html:not(.light-theme) input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
            html:not(.light-theme) select,
            html:not(.light-theme) textarea {
                background: #0b1120 !important;
                -webkit-box-shadow: 0 0 0 1000px #0b1120 inset !important;
                box-shadow: 0 0 0 1000px #0b1120 inset !important;
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff !important;
                border: 2.4px solid rgba(56, 189, 248, 0.82) !important;
                font-weight: 900 !important;
            }
            html:not(.light-theme) input::placeholder,
            html:not(.light-theme) textarea::placeholder,
            html:not(.light-theme) input::-webkit-input-placeholder,
            html:not(.light-theme) textarea::-webkit-input-placeholder {
                color: #64748b !important;
                -webkit-text-fill-color: #64748b !important;
                font-weight: 500 !important;
                opacity: 1 !important;
            }
            html:not(.light-theme) input.is-zero-val:not(:focus) {
                opacity: 1 !important;
                color: #64748b !important;
                -webkit-text-fill-color: #64748b !important;
                font-weight: 600 !important;
            }

            /* 8 LIGHT TONES (Purchase 2027) + STRICT FONT CONTRAST LOCK */
            html.light-theme body,
            html.light-theme .ambient-bg {
                background: var(--lt-bg-main) !important;
                color: var(--lt-text) !important;
            }
            html.light-theme .md-card,
            html.light-theme .neon-glass-panel,
            html.light-theme .md-tabs-sidebar,
            html.light-theme #customer-form,
            html.light-theme #supplier-form,
            html.light-theme #warehouse-form,
            html.light-theme #bank-form,
            html.light-theme #safe-form,
            html.light-theme #item-form,
            html.light-theme #employee-form,
            html.light-theme #costcenter-form,
            html.light-theme .md-child-modal-card,
            html.light-theme .md-c1-modal-card {
                background: var(--lt-bg-card) !important;
                border: 3.2px solid var(--lt-border) !important;
                color: #000000 !important;
                box-shadow: 0 6px 18px rgba(0,0,0,0.14) !important;
            }
            html.light-theme .md-card h1, html.light-theme .md-card h2, html.light-theme .md-card h3,
            html.light-theme .md-card h4, html.light-theme .md-card h5, html.light-theme .md-card label,
            html.light-theme .pro-field-label, html.light-theme .pro-field-label span,
            html.light-theme .input-group label, html.light-theme .input-group label span,
            html.light-theme .md-card p, html.light-theme .md-card span:not(.badge),
            html.light-theme .md-child-modal-card *, html.light-theme .md-c1-modal-card * {
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
                font-weight: 900 !important;
                opacity: 1 !important;
            }
            html.light-theme .input-group,
            html.light-theme .pro-field-card {
                background: var(--lt-bg-strip) !important;
                border: 2.8px solid var(--lt-border) !important;
                color: #000000 !important;
            }
            html.light-theme input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
            html.light-theme select,
            html.light-theme textarea {
                background: #ffffff !important;
                -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
                box-shadow: 0 0 0 1000px #ffffff inset !important;
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
                border: 2.5px solid var(--lt-border) !important;
                font-weight: 900 !important;
            }
            html.light-theme input::placeholder,
            html.light-theme textarea::placeholder,
            html.light-theme input::-webkit-input-placeholder,
            html.light-theme textarea::-webkit-input-placeholder {
                color: #9ca3af !important;
                -webkit-text-fill-color: #9ca3af !important;
                font-weight: 500 !important;
                opacity: 1 !important;
            }
            html.light-theme input.is-zero-val:not(:focus) {
                opacity: 1 !important;
                color: #9ca3af !important;
                -webkit-text-fill-color: #9ca3af !important;
                font-weight: 600 !important;
            }
            html.light-theme table.pro-table thead tr,
            html.light-theme table.pro-table th {
                background: var(--lt-bg-strip) !important;
                color: var(--lt-text) !important;
                border-bottom: 3px solid var(--lt-border) !important;
                font-weight: 900 !important;
            }
            html.light-theme table.pro-table td {
                color: var(--lt-text) !important;
                border-bottom: 1.5px solid var(--lt-border) !important;
                font-weight: 900 !important;
            }

            /* 3D FINANCIAL STRIP CAPSULES */
            #md-pur2027-fin-strip {
                display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
                gap: 8px; padding: 8px 14px; margin: 6px 0 10px 0; border-radius: 14px;
                background: var(--lt-bg-card, #152342); border: 3px solid var(--lt-border, #2b4570);
                box-shadow: 0 6px 16px rgba(0,0,0,0.22);
            }
            html:not(.light-theme) #md-pur2027-fin-strip {
                background: #152342 !important; border-color: #2b4570 !important;
            }
            .md-fin-cap {
                display: inline-flex; align-items: center; gap: 8px; padding: 5px 13px;
                border-radius: 10px; color: #ffffff !important; font-weight: 900;
                box-shadow: 0 3px 8px rgba(0,0,0,0.28), inset 0 1px 0 rgba(255,255,255,0.25);
            }
            .md-fin-cap .cap-lbl { font-size: 0.82rem; color: #ffffff !important; opacity: 0.95; }
            .md-fin-cap .cap-val { font-size: 1.35rem; font-family: 'Outfit', 'Tajawal', monospace; color: #ffffff !important; font-weight: 900; }
            .md-cap-sky     { background: linear-gradient(135deg, #0369a1, #0284c7) !important; border: 2px solid #7dd3fc !important; }
            .md-cap-crimson { background: linear-gradient(135deg, #be123c, #e11d48) !important; border: 2px solid #fda4af !important; }
            .md-cap-emerald { background: linear-gradient(135deg, #047857, #059669) !important; border: 2px solid #6ee7b7 !important; }
            .md-cap-amber   { background: linear-gradient(135deg, #b45309, #d97706) !important; border: 2px solid #fde68a !important; }

            /* 3D PRICE RADAR LAMP & FAINT ZEROS */
            .pur-price-lamp {
                width: 18px; height: 18px; border-radius: 50%; display: inline-block; flex-shrink: 0;
                box-shadow: inset -2px -2px 4px rgba(0,0,0,0.45), inset 2px 2px 4px rgba(255,255,255,0.6), 0 0 8px currentColor;
                vertical-align: middle; margin-right: 6px;
            }
            .pur-lamp-green { background: radial-gradient(circle at 35% 35%, #6ee7b7, #059669 65%, #064e3b); color: #10b981; }
            .pur-lamp-amber { background: radial-gradient(circle at 35% 35%, #fde68a, #d97706 65%, #78350f); color: #f59e0b; }
            .pur-lamp-red   { background: radial-gradient(circle at 35% 35%, #fda4af, #e11d48 65%, #881337); color: #f43f5e; }
            .md-ostaz-fixed-btn {
                background: linear-gradient(135deg, #0284c7, #0369a1) !important; color: #ffffff !important;
                border: 2px solid #7dd3fc !important; border-radius: 6px !important; padding: 2px 8px !important;
                font-size: 0.76rem !important; font-weight: 900 !important; cursor: pointer !important;
                white-space: nowrap !important; display: inline-flex !important; align-items: center !important; gap: 3px !important;
                box-shadow: inset 0 1px 1px rgba(255,255,255,0.85), inset 0 8px 10px -4px rgba(255,255,255,0.45) !important;
            }
            /* +1 DEGREE STRONGER BORDERS ON ALL SEGMENTED & ICON ACTION BUTTONS */
            .md-entity-seg-group {
                border: 2.2px solid rgba(148, 163, 184, 0.62) !important;
            }
            html.light-theme .md-entity-seg-group {
                background: #ffffff !important;
                border: 2.4px solid var(--lt-border, #334155) !important;
            }
            .md-entity-seg-btn:not(.is-active-seg) {
                border: 1.8px solid rgba(148, 163, 184, 0.5) !important;
            }
            html.light-theme .md-entity-seg-btn:not(.is-active-seg) {
                border: 1.8px solid rgba(51, 65, 85, 0.55) !important;
                background: rgba(15, 23, 42, 0.06) !important;
                color: #1e293b !important;
                -webkit-text-fill-color: #1e293b !important;
            }
            .md-entity-seg-btn.is-active-seg {
                border-width: 2.2px !important;
            }
            html.light-theme .md-entity-seg-btn.is-active-seg {
                border: 2.2px solid var(--lt-border, #0f172a) !important;
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
            }
            .md-hdr-pill-btn,
            #customer-form button,
            #supplier-form button,
            #admin-wa-policy-panel-cust button,
            #admin-wa-policy-panel-sup button,
            .btn-action,
            .btn-text {
                border-width: 2px !important;
            }
            html.light-theme .md-hdr-pill-btn,
            html.light-theme #customer-form button:not(.md-ostaz-fixed-btn):not(.btn-c3-save):not(.btn-c3-cancel),
            html.light-theme #supplier-form button:not(.md-ostaz-fixed-btn):not(.btn-c3-save):not(.btn-c3-cancel),
            html.light-theme #admin-wa-policy-panel-cust button,
            html.light-theme #admin-wa-policy-panel-sup button {
                border: 2.2px solid var(--lt-border, #334155) !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.12) !important;
            }
            /* UNIFORM EQUAL BUTTONS + INNER CRYSTAL GLASS SHINE (اللمعة الزجاجية الكريستالية من الداخل) */
            .md-tabs-sidebar {
                display: grid !important; grid-template-columns: repeat(9, minmax(0, 1fr)) !important;
                gap: 10px !important; align-items: stretch !important; padding: 10px 12px !important;
            }
            .md-tabs-sidebar > div[style*="flex"] { display: none !important; }
            .md-tabs-sidebar .md-tab-btn {
                width: 100% !important; min-width: 0 !important; height: 50px !important; min-height: 50px !important; max-height: 50px !important;
                padding: 0 8px !important; margin: 0 !important; box-sizing: border-box !important;
                display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 7px !important;
                font-size: 0.98rem !important; font-weight: 900 !important; border-radius: 14px !important;
                background: var(--tab-active-bg) !important; color: #ffffff !important;
                -webkit-text-fill-color: #ffffff !important; -webkit-text-stroke: 0px transparent !important;
                border: 2.8px solid var(--tab-border-color, #ffffff) !important;
                text-shadow: none !important;
                box-shadow: 0 6px 16px rgba(0,0,0,0.28), inset 0 2px 2px rgba(255,255,255,0.95), inset 0 15px 18px -6px rgba(255,255,255,0.58), inset 0 -6px 12px rgba(0,0,0,0.28) !important;
                transform: none !important;
            }
            .md-tabs-sidebar .md-tab-btn:hover, .md-tabs-sidebar .md-tab-btn.active, .md-tabs-sidebar .md-tab-btn:focus {
                transform: none !important;
                text-shadow: none !important;
                -webkit-text-stroke: 0px transparent !important;
                box-shadow: 0 10px 24px var(--tab-glow-color), 0 0 18px var(--tab-glow-color), inset 0 2px 3px rgba(255,255,255,1), inset 0 16px 20px -5px rgba(255,255,255,0.68), inset 0 -6px 12px rgba(0,0,0,0.3) !important;
            }
            html.light-theme .md-tabs-sidebar .md-tab-btn,
            html.light-theme .md-tabs-sidebar .md-tab-btn:hover,
            html.light-theme .md-tabs-sidebar .md-tab-btn.active,
            html.light-theme .md-tabs-sidebar .md-tab-btn:focus {
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
                -webkit-text-stroke: 0px transparent !important;
                text-shadow: none !important;
                transform: none !important;
            }
            .md-tabs-sidebar .md-tab-btn .md-tab-icon,
            html.light-theme .md-tabs-sidebar .md-tab-btn .md-tab-icon {
                -webkit-text-fill-color: initial !important;
                -webkit-text-stroke: 0px transparent !important;
                text-shadow: none !important;
                filter: none !important;
                transform: none !important;
            }
            html.light-theme .md-tabs-sidebar .md-tab-btn {
                background: linear-gradient(145deg, #ffffff 0%, var(--lt-bg-strip, #e2e8f0) 100%) !important;
                border: 2.8px solid var(--tab-border-color, var(--lt-border, #1e293b)) !important;
            }
            html.light-theme .md-tabs-sidebar .md-tab-btn.active,
            html.light-theme .md-tabs-sidebar .md-tab-btn:hover {
                background: linear-gradient(135deg, var(--lt-bg-card, #ffffff) 0%, var(--tab-border-color, #38bdf8) 140%) !important;
                border-color: #000000 !important;
            }
            #admin-wa-policy-panel-cust > div:first-child, #admin-wa-policy-panel-sup > div:first-child {
                display: grid !important; grid-template-columns: repeat(5, minmax(0, 1fr)) !important; gap: 8px !important; width: 100% !important;
            }
            #admin-wa-policy-panel-cust > div:first-child > button, #admin-wa-policy-panel-sup > div:first-child > button,
            .md-card-header .btn-add, .md-c3-form-footer .btn-c3-save, .md-c3-form-footer .btn-c3-cancel {
                position: relative !important; overflow: hidden !important; height: 40px !important; min-height: 40px !important;
                display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;
                border-radius: 11px !important; font-weight: 900 !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.22), inset 0 2px 2px rgba(255,255,255,0.9), inset 0 13px 16px -6px rgba(255,255,255,0.48), inset 0 -4px 10px rgba(0,0,0,0.22) !important;
            }
            html.light-theme .md-col-ctrl-bar {
                background: var(--lt-bg-strip, #e6d7be) !important;
                border-top: 2px solid var(--lt-border, #854d0e) !important;
                color: #000000 !important;
            }
            html.light-theme .md-col-ctrl-bar * {
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
            }
            html.light-theme .md-col-ctrl-btn {
                background: #ffffff !important;
                border: 2px solid var(--lt-border, #334155) !important;
                color: #000000 !important;
                -webkit-text-fill-color: #000000 !important;
                font-weight: 900 !important;
            }
        `;
        (document.body || document.head).appendChild(st);
    }

    const toneBc = (typeof BroadcastChannel !== 'undefined') ? new BroadcastChannel('elsajana_sovereign_10x_viewport_v1') : null;
    function playSystemAudioTone(freqOverride) {
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
    window.setMdSystemAudioTone = function (mode) {
        localStorage.setItem(SOUND_TONE_KEY, mode);
        playSystemAudioTone(960);
        if (typeof showToast === 'function') showToast('🔊 تم ضبط نغمة النظام التفاعلية بنجاح', 'success', 2000);
    };

    window.applyPurchaseLightTone = function (toneId, notify, fromBroadcast) {
        const isDarkAlias = ['dark_midnight', 'pure_oled', 'mocha_brown', 'slate_twilight', 'midnight_obsidian', 'dark'].includes(toneId);
        const t = isDarkAlias ? TONES[0] : (TONES.find(x => x.id === toneId) || TONES[1]);
        const targetTheme = t.themeKey || (t.isDark ? 'midnight_obsidian' : 'warm_cream');
        [document.documentElement, document.body].forEach(el => {
            if (!el) return;
            el.classList.toggle('light-theme', !t.isDark);
            el.classList.toggle('dark-theme', !!t.isDark);
            el.setAttribute('data-theme', targetTheme);
            el.setAttribute('data-light-tone', t.id);
        });
        try {
            document.cookie = 'magaies_active_theme=' + encodeURIComponent(targetTheme) + ';path=/;max-age=315360000;SameSite=Lax';
            document.cookie = 'magaies_light_tone=' + encodeURIComponent(t.id) + ';path=/;max-age=315360000;SameSite=Lax';
            ['magaies_active_theme', 'magaiees_global_theme', 'magaiees_mobile_theme', 'ui_theme'].forEach(k => localStorage.setItem(k, targetTheme));
            localStorage.setItem('magaies_light_tone', t.id);
            localStorage.setItem('pur_light_tone_palette', t.id);
            localStorage.setItem('erp_theme', t.isDark ? 'dark' : 'light');
        } catch (e) {}
        injectConstitutionStyle();
        if (typeof window.applyAiSmartContrastLock === 'function') window.applyAiSmartContrastLock();
        if (notify) {
            playSystemAudioTone(1040);
            if (typeof showToast === 'function') showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تثبيت المظهر في كل البرنامج: ' + t.label, 'success', 2400);
        }
        if (!fromBroadcast && toneBc) {
            try { toneBc.postMessage({ type: 'TONE_SYNC', toneId: t.id }); } catch (e) {}
        }
    };

    window.runStrictDaylightContrastGuard = function () {
        const isLight = document.documentElement.classList.contains('light-theme');
        if (!isLight) return;
        const PALE_HEX_RE = /#(fff|ffffff|f8fafc|f1f5f9|e2e8f0|cbd5e1|94a3b8|fde047|fef08a|fbbf24|7dd3fc|38bdf8|a7f3d0|6ee7b7|34d399|e9d5ff|d8b4fe|c084fc|fca5a5|f9a8d4)\b/i;
        document.querySelectorAll('.md-card [style*="color"], .md-c1-modal-card [style*="color"], .md-child-modal-card [style*="color"], #md-constitution1-quick-strip [style*="color"]').forEach(el => {
            if (el.getAttribute('data-tone-dark') === '1' || el.classList.contains('md-ostaz-fixed-btn') || el.classList.contains('btn-c3-save') || el.classList.contains('btn-c3-cancel') || el.classList.contains('btn-add')) return;
            const st = el.getAttribute('style') || '';
            if (PALE_HEX_RE.test(st)) {
                const bg = window.getComputedStyle(el).backgroundColor || '';
                const m = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
                let isDarkSurface = false;
                if (m) {
                    const r = +m[1], g = +m[2], b = +m[3], a = m[4] !== undefined ? +m[4] : 1;
                    if (a > 0.55 && ((0.299 * r + 0.587 * g + 0.114 * b) / 255) < 0.38) isDarkSurface = true;
                }
                if (!isDarkSurface) {
                    el.style.setProperty('color', '#0F172A', 'important');
                    el.style.setProperty('-webkit-text-fill-color', '#0F172A', 'important');
                }
            }
        });
    };

    window.applyAiSmartContrastLock = function () {
        const isLight = document.documentElement.classList.contains('light-theme');
        if (!isLight) return;
        window.runStrictDaylightContrastGuard();
        document.querySelectorAll('.md-card button, .md-tabs-sidebar .md-tab-btn, #pur-light-tone-popover button, .md-col-ctrl-btn, .preset-pill-btn, .ribbon-btn, #md-constitution1-quick-strip button').forEach(el => {
            if (el.getAttribute('data-tone-dark') === '1' || el.classList.contains('md-ostaz-fixed-btn') || el.classList.contains('btn-c3-save') || el.classList.contains('btn-c3-cancel')) return;
            const cs = window.getComputedStyle(el);
            const bg = (cs.backgroundColor || '') + ' ' + (cs.backgroundImage || '');
            const m = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            if (m) {
                const r = +m[1], g = +m[2], b = +m[3], a = m[4] !== undefined ? +m[4] : 1;
                const lum = a < 0.35 ? 0.85 : (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                if (lum > 0.46) {
                    el.style.setProperty('color', '#000000', 'important');
                    el.style.setProperty('-webkit-text-fill-color', '#000000', 'important');
                }
            }
        });
    };

    window.openPurchaseLightTonePopover = function (anchorEl) {
        let pop = document.getElementById('pur-light-tone-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'pur-light-tone-popover';
            document.body.appendChild(pop);
            document.addEventListener('click', (e) => {
                const isBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn, #btn-md-c1-theme-switch');
                if (pop.style.display !== 'none' && !pop.contains(e.target) && !isBtn) pop.style.display = 'none';
            });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDarkNow = !document.documentElement.classList.contains('light-theme');
        pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:275px;max-height:86vh;overflow-y:auto;direction:rtl;font-family:Tajawal,Cairo,sans-serif;display:none;';
        const cur = isDarkNow ? 'dark_midnight' : (document.documentElement.getAttribute('data-light-tone') || localStorage.getItem('magaies_light_tone') || 'ivory_default');
        const curSound = localStorage.getItem(SOUND_TONE_KEY) || 'crystal';
        const hdrCol = isDarkNow ? '#fde047' : '#000000';
        const audioHtml = `<div style="margin-top:8px;padding-top:7px;border-top:1.5px dashed rgba(148,163,184,0.45);">` +
            `<div style="font-size:0.8rem;font-weight:900;color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;margin-bottom:5px;">🔊 نغمات النظام التفاعلية (4 نغمات):</div>` +
            `<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:5px;">` +
            SYSTEM_AUDIO_TONES.map(s => {
                const act = curSound === s.id;
                const sBg = act ? 'linear-gradient(135deg,#10b981,#059669)' : (isDarkNow ? 'rgba(30,41,59,0.9)' : '#ffffff');
                const sCol = act ? '#ffffff' : (isDarkNow ? '#e2e8f0' : '#0f172a');
                return `<button type="button" data-tone-dark="${act || isDarkNow ? '1' : '0'}" onclick="window.setMdSystemAudioTone('${s.id}');window.openPurchaseLightTonePopover();window.openPurchaseLightTonePopover();" style="padding:6px 8px;border-radius:8px;cursor:pointer;font-size:0.78rem;font-weight:900;border:${act ? '2px solid #fde047' : '1.5px solid #64748b'}!important;background:${sBg}!important;color:${sCol}!important;-webkit-text-fill-color:${sCol}!important;">${s.name}${act ? ' ✓' : ''}</button>`;
            }).join('') + `</div></div>`;
        pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 المظهر ونغمات النظام (${TONES.length})</span><span style="font-size:0.75rem;color:${isDarkNow?'#94a3b8':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#000000'}!important;">حارس نهاري 100%</span></div>` +
            TONES.map(t => {
                const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.swatch} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                const txtCol = t.isDark ? '#ffffff' : '#000000';
                const chkCol = t.isDark ? '#fde047' : '#047857';
                return `<button type="button" data-tone-dark="${t.isDark ? '1' : '0'}" onclick="window.applyPurchaseLightTone('${t.id}',true);document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:6px 10px;margin-bottom:4px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.85rem;text-align:right;"><span style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,${t.fg} 0%,${t.swatch} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;">${t.label}</span>${cur===t.id?`<span style="color:${chkCol}!important;-webkit-text-fill-color:${chkCol}!important;font-weight:900;">✓</span>`:''}</button>`;
            }).join('') + audioHtml;
        const r = anchorEl && anchorEl.getBoundingClientRect ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 520, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 295, r.left)) + 'px';
        pop.style.display = 'block';
    };
    window.openUniversalErpTonePopover = window.openPurchaseLightTonePopover;

    let mdCurMode = 'SDG';
    function getRate() {
        const inp = document.getElementById('md-hdr-exchange-rate');
        if (inp && Number(inp.value) > 0) return Number(inp.value);
        return Number(localStorage.getItem('erp_exchange_rate_usd') || localStorage.getItem('usd_exchange_rate') || 600) || 600;
    }

    function readList(key) {
        try {
            const tk = window.getTenantKey ? window.getTenantKey(key) : key;
            const arr = JSON.parse(localStorage.getItem(tk) || localStorage.getItem(key) || '[]');
            return Array.isArray(arr) ? arr.filter(x => x && !x.deleted) : [];
        } catch (e) { return []; }
    }

    window.refreshMdPurchaseConstitutionStrip = function () {
        const rate = getRate();
        const custs = readList('erp_customers_v1');
        const sups  = readList('erp_suppliers_v1');
        const banks = readList('erp_banks_v1');
        const safes = readList('erp_safes_v1');
        const items = readList('erp_items_v1');
        const whs   = readList('erp_warehouses_v1');

        const custBal = custs.reduce((s, c) => s + (Number(c.balance ?? c.openingBalance ?? 0) || 0), 0);
        const supBal  = sups.reduce((s, c) => s + (Number(c.balance ?? c.openingBalance ?? 0) || 0), 0);
        const liqBal  = banks.reduce((s, b) => s + (Number(b.balance ?? b.openingBalance ?? 0) || 0), 0) +
                        safes.reduce((s, sf) => s + (Number(sf.balance ?? sf.openingBalance ?? 0) || 0), 0);

        const fmt = (val) => mdCurMode === 'USD'
            ? ('$' + (val / rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
            : (Math.round(val).toLocaleString('en-US') + ' ج.س');

        const elC = document.getElementById('md-cap-val-cust');
        const elS = document.getElementById('md-cap-val-sup');
        const elL = document.getElementById('md-cap-val-liq');
        const elI = document.getElementById('md-cap-val-items');
        if (elC) { elC.textContent = fmt(custBal); elC.classList.toggle('is-zero-val', custBal === 0); }
        if (elS) { elS.textContent = fmt(supBal);  elS.classList.toggle('is-zero-val', supBal === 0); }
        if (elL) { elL.textContent = fmt(liqBal);  elL.classList.toggle('is-zero-val', liqBal === 0); }
        if (elI) { elI.textContent = `${items.length} صنف / ${whs.length} مخزن`; }
    };

    window.toggleMdPurchaseStripCurrency = function () {
        mdCurMode = (mdCurMode === 'SDG') ? 'USD' : 'SDG';
        const btn = document.getElementById('md-fin-strip-currency-btn');
        if (btn) btn.textContent = mdCurMode === 'USD' ? '💱 دولار ($) ⇄ جنيه' : '💱 جنيه (ج.س) ⇄ دولار';
        window.refreshMdPurchaseConstitutionStrip();
    };

    function mountFinStrip() {
        const old = document.getElementById('md-pur2027-fin-strip');
        if (old) old.remove();
    }

    function enhanceInputsAndTables() {
        // 1. Faint zeros (.is-zero-val)
        document.querySelectorAll('input[type="number"], input[inputmode="decimal"]').forEach(inp => {
            const v = String(inp.value || '').trim();
            inp.classList.toggle('is-zero-val', v === '' || Number(v) === 0);
            if (!inp.dataset.purZeroBound) {
                inp.dataset.purZeroBound = '1';
                inp.addEventListener('input', () => {
                    const cv = String(inp.value || '').trim();
                    inp.classList.toggle('is-zero-val', cv === '' || Number(cv) === 0);
                });
            }
        });

        // 2. Price Radar 3D Lamp on Item Buy/Sell inputs
        const buyInp = document.getElementById('item-buy-price');
        const sellInp = document.getElementById('item-sell-price');
        if (sellInp && !document.getElementById('md-item-price-radar-lamp')) {
            const lamp = document.createElement('span');
            lamp.id = 'md-item-price-radar-lamp';
            lamp.className = 'pur-price-lamp pur-lamp-green';
            lamp.title = 'رادار الأسعار وهامش الربح 2027';
            const sub = document.createElement('div');
            sub.id = 'md-item-price-radar-sub';
            sub.style.cssText = 'font-size:0.76rem;font-weight:900;margin-top:3px;';
            if (sellInp.parentNode) {
                const lbl = sellInp.parentNode.querySelector('label');
                if (lbl) lbl.appendChild(lamp);
                sellInp.parentNode.appendChild(sub);
            }
            const updateRadar = () => {
                const b = Number(buyInp ? buyInp.value : 0) || 0;
                const s = Number(sellInp.value) || 0;
                if (b <= 0 && s <= 0) {
                    lamp.className = 'pur-price-lamp pur-lamp-amber';
                    sub.innerHTML = '<span style="color:#d97706;">● بانتظار إدخال سعر الشراء والبيع</span>';
                } else if (s < b) {
                    lamp.className = 'pur-price-lamp pur-lamp-red';
                    sub.innerHTML = `<span style="color:#e11d48;">▼ تحذير: سعر البيع أقل من التكلفة (${Math.round(((s - b) / (b || 1)) * 100)}%)</span>`;
                } else {
                    const pct = b > 0 ? Math.round(((s - b) / b) * 100) : 100;
                    lamp.className = 'pur-price-lamp pur-lamp-green';
                    sub.innerHTML = `<span style="color:#059669;">▲ هامش ربح آمن: +${pct}% (+${(s - b).toLocaleString()})</span>`;
                }
            };
            if (buyInp) buyInp.addEventListener('input', updateRadar);
            sellInp.addEventListener('input', updateRadar);
            updateRadar();
        }

        // 3. Add 📖 أستاذ button next to Customer / Supplier / Bank / Safe / Employee name inputs
        ['cust-name', 'sup-name', 'bank-name', 'safe-name', 'emp-name'].forEach(id => {
            const inp = document.getElementById(id);
            if (!inp || !inp.parentNode || inp.parentNode.querySelector('.md-ostaz-fixed-btn')) return;
            const lbl = inp.parentNode.querySelector('label');
            if (lbl) {
                lbl.style.display = 'flex';
                lbl.style.alignItems = 'center';
                lbl.style.justifyContent = 'space-between';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'md-ostaz-fixed-btn';
                btn.innerHTML = '📖 أستاذ';
                btn.onclick = (e) => {
                    e.preventDefault();
                    const q = String(inp.value || '').trim();
                    window.location.href = 'general-ledger.php' + (q ? ('?search=' + encodeURIComponent(q)) : '');
                };
                lbl.appendChild(btn);
            }
        });
        if (typeof window.applyAiSmartContrastLock === 'function') window.applyAiSmartContrastLock();
    }

    function initAll() {
        const getCkMdTone = () => { const m = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/); return m ? decodeURIComponent(m[1]) : ''; };
        const savedTone = getCkMdTone() || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || 'ivory_default';
        window.applyPurchaseLightTone(savedTone, false, true);
        document.addEventListener('click', (e) => {
            const btn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn, #btn-md-c1-theme-switch');
            if (!btn) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            window.openPurchaseLightTonePopover(btn);
        }, true);
        if (toneBc) {
            toneBc.addEventListener('message', (ev) => {
                if (ev.data && (ev.data.type === 'TONE_SYNC' || ev.data.type === 'TONE_CHANGED') && ev.data.toneId) {
                    window.applyPurchaseLightTone(ev.data.toneId, false, true);
                }
            });
        }
        window.addEventListener('storage', (ev) => {
            if (ev.key === 'magaies_light_tone' && ev.newValue) {
                window.applyPurchaseLightTone(ev.newValue, false, true);
            }
        });
        mountFinStrip();
        enhanceInputsAndTables();
        setInterval(() => {
            if (!document.hidden) {
                window.refreshMdPurchaseConstitutionStrip();
                enhanceInputsAndTables();
            }
        }, 3500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
