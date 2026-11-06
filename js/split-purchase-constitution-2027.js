// ============================================================================
// PURCHASE DESIGN CONSTITUTION 2027 — APPLIED TO SPLIT INVOICE VIEW (?action=split)
// 9 Tones + 3px Sculpted Borders + 3D Capsules + Price Radar + Dual SDG/USD
// ============================================================================
(function () {
    const css = `
    :root.light-theme, :root.light-theme[data-light-tone="ivory_default"] { --lt-canvas-1:#efe5d0; --lt-canvas-2:#e5d6ba; --lt-hdr-1:#f5ebd6; --lt-hdr-2:#ecdec2; --lt-hdr-3:#e2cfa9; --lt-card-1:#ffffff; --lt-card-2:#fcf7ee; --lt-sub-1:#ffffff; --lt-sub-2:#fbf4e6; --lt-th-1:#faf3e3; --lt-th-2:#f1e4ca; --lt-row-odd:#ffffff; --lt-row-even:#fefcf7; --lt-border:#5c2808; --lt-border-hover:#b45309; --lt-glow:rgba(245,158,11,0.45); --lt-ink:#120500; }
    :root.light-theme[data-light-tone="royal_bronze"] { --lt-canvas-1:#e6cbb0; --lt-canvas-2:#d9b694; --lt-hdr-1:#eed7be; --lt-hdr-2:#e3c4a2; --lt-hdr-3:#d7b189; --lt-card-1:#fffbf7; --lt-card-2:#f9efe3; --lt-sub-1:#fffdfa; --lt-sub-2:#f6e7d5; --lt-th-1:#f5e5d1; --lt-th-2:#ebd2b7; --lt-row-odd:#ffffff; --lt-row-even:#fcf6ee; --lt-border:#4a2106; --lt-border-hover:#9a3412; --lt-glow:rgba(217,119,6,0.48); --lt-ink:#140600; }
    :root.light-theme[data-light-tone="platinum_gray"] { --lt-canvas-1:#d9e3ee; --lt-canvas-2:#cad7e6; --lt-hdr-1:#e2ebf5; --lt-hdr-2:#d4e1ef; --lt-hdr-3:#c5d6e8; --lt-card-1:#ffffff; --lt-card-2:#f2f7fc; --lt-sub-1:#ffffff; --lt-sub-2:#eef4fb; --lt-th-1:#ecf3fa; --lt-th-2:#dde8f5; --lt-row-odd:#ffffff; --lt-row-even:#f7fafe; --lt-border:#1e293b; --lt-border-hover:#0284c7; --lt-glow:rgba(56,189,248,0.45); --lt-ink:#020617; }
    :root.light-theme[data-light-tone="crystal_silver"] { --lt-canvas-1:#e4e9f0; --lt-canvas-2:#d6dde7; --lt-hdr-1:#ecf0f5; --lt-hdr-2:#e0e6ee; --lt-hdr-3:#d3dbe6; --lt-card-1:#ffffff; --lt-card-2:#f5f7fb; --lt-sub-1:#ffffff; --lt-sub-2:#f1f4f9; --lt-th-1:#eff3f8; --lt-th-2:#e3e8f0; --lt-row-odd:#ffffff; --lt-row-even:#f9fafc; --lt-border:#111827; --lt-border-hover:#2563eb; --lt-glow:rgba(59,130,246,0.45); --lt-ink:#030712; }
    :root.light-theme[data-light-tone="sage_pearl"] { --lt-canvas-1:#d4eee1; --lt-canvas-2:#c1e4d3; --lt-hdr-1:#ddf3e8; --lt-hdr-2:#cbead9; --lt-hdr-3:#b8e0cb; --lt-card-1:#ffffff; --lt-card-2:#eefaf4; --lt-sub-1:#ffffff; --lt-sub-2:#e8f8f0; --lt-th-1:#e5f6ed; --lt-th-2:#d3efe0; --lt-row-odd:#ffffff; --lt-row-even:#f6fdf9; --lt-border:#064e3b; --lt-border-hover:#059669; --lt-glow:rgba(16,185,129,0.45); --lt-ink:#022c22; }
    :root.light-theme[data-light-tone="sky_alabaster"] { --lt-canvas-1:#d3e9fb; --lt-canvas-2:#beddf7; --lt-hdr-1:#dceffd; --lt-hdr-2:#cae4fa; --lt-hdr-3:#b7d9f6; --lt-card-1:#ffffff; --lt-card-2:#eef7fe; --lt-sub-1:#ffffff; --lt-sub-2:#e7f4fd; --lt-th-1:#e4f2fd; --lt-th-2:#d1e9fb; --lt-row-odd:#ffffff; --lt-row-even:#f6fbff; --lt-border:#0c4a6e; --lt-border-hover:#0284c7; --lt-glow:rgba(14,165,233,0.45); --lt-ink:#082f49; }
    :root.light-theme[data-light-tone="warm_mocha_dark"] { --lt-canvas-1:#d2baa1; --lt-canvas-2:#c1a384; --lt-hdr-1:#decab4; --lt-hdr-2:#cfb69b; --lt-hdr-3:#bfa182; --lt-card-1:#fdfaf6; --lt-card-2:#f6ede2; --lt-sub-1:#fefcf9; --lt-sub-2:#f2e6d8; --lt-th-1:#f0e2d2; --lt-th-2:#e4d1bc; --lt-row-odd:#ffffff; --lt-row-even:#faf4ec; --lt-border:#3e1b04; --lt-border-hover:#7c2d12; --lt-glow:rgba(180,83,9,0.48); --lt-ink:#0f0400; }
    :root.light-theme[data-light-tone="deep_slate_dark"] { --lt-canvas-1:#c1cedf; --lt-canvas-2:#aebfd4; --lt-hdr-1:#ced9e8; --lt-hdr-2:#bdcce0; --lt-hdr-3:#acbed6; --lt-card-1:#f9fbfe; --lt-card-2:#eef3fa; --lt-sub-1:#fcfdff; --lt-sub-2:#e8eff8; --lt-th-1:#e4ecf6; --lt-th-2:#d5e1f0; --lt-row-odd:#ffffff; --lt-row-even:#f4f8fc; --lt-border:#0f172a; --lt-border-hover:#1d4ed8; --lt-glow:rgba(37,99,235,0.45); --lt-ink:#020617; }
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
    html:not(.light-theme) #inline-totals-container,
    html:not(.light-theme) #totals-calc-card,
    html:not(.light-theme) #payment-panel,
    html:not(.light-theme) .sales-fc1-modal-card,
    html:not(.light-theme) #split-invoice-view .neo-card,
    html:not(.light-theme) #split-invoice-view #split-fc1-ai-cockpit-bar,
    html:not(.light-theme) #split-invoice-view #split-search-toolbar,
    html:not(.light-theme) #split-invoice-view #split-header-info {
        background:linear-gradient(145deg,var(--dk-card-1,#1b3159) 0%,var(--dk-card-2,#132341) 100%) !important;
        border:2.2px solid var(--dk-border,rgba(56,189,248,0.78)) !important;
        border-radius:15px !important;
        box-shadow:0 12px 32px rgba(0,0,0,0.85),inset 0 1.5px 0 rgba(255,255,255,0.22) !important;
        color:#ffffff !important;
    }
    html:not(.light-theme) #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html:not(.light-theme) #split-invoice-view select,
    html:not(.light-theme) #split-invoice-view textarea {
        background-color:#0b1120 !important;
        -webkit-box-shadow:0 0 0 1000px #0b1120 inset !important;
        box-shadow:0 0 0 1000px #0b1120 inset !important;
        color:#ffffff !important;
        -webkit-text-fill-color:#ffffff !important;
        border:2px solid #38bdf8 !important;
        font-weight:900 !important;
    }
    html:not(.light-theme) #split-invoice-view table.pos-grid thead th {
        background:#0d182c !important;
        color:#ffffff !important;
        font-weight:900 !important;
        font-size:1rem !important;
        border:2px solid rgba(56,189,248,0.55) !important;
    }
    html:not(.light-theme) #split-invoice-view table.pos-grid tbody td {
        background:#13223f !important;
        color:#ffffff !important;
        font-weight:900 !important;
        font-size:0.98rem !important;
        border:1.5px solid rgba(56,189,248,0.28) !important;
    }
    html:not(.light-theme) #split-invoice-view table.pos-grid tbody tr:nth-child(even) td {
        background:#17294b !important;
    }

    /* 2. All 8 Light Tones (--lt-* variables, 3px sculpted borders, pure white inputs, pitch-black 900 text) across ALL Direct Sales Views & Modals */
    html.light-theme body, html.light-theme #sales-hub-view, html.light-theme #invoice-editor-view, html.light-theme #split-invoice-view {
        background:linear-gradient(180deg,var(--lt-canvas-1,#efe5d0) 0%,var(--lt-canvas-2,#e5d6ba) 100%) !important;
        color:var(--lt-ink,#120500) !important;
    }
    html.light-theme #sales-fc1-smart-ai-command-bar,
    html.light-theme #sales-fc2-live-result-interpreter-bar,
    html.light-theme #form-fields-card,
    html.light-theme .sales-glass-card,
    html.light-theme #items-panel,
    html.light-theme .table-container,
    html.light-theme #inline-totals-container,
    html.light-theme #totals-calc-card,
    html.light-theme #payment-panel,
    html.light-theme .sales-fc1-modal-card,
    html.light-theme .modal-card,
    html.light-theme .modal-content,
    html.light-theme #split-invoice-view .neo-card,
    html.light-theme #split-invoice-view #split-fc1-ai-cockpit-bar,
    html.light-theme #split-invoice-view #split-search-toolbar,
    html.light-theme #split-invoice-view #split-header-info,
    html.light-theme #split-invoice-view .grid-scroll-wrap {
        background:linear-gradient(145deg,var(--lt-card-1,#ffffff) 0%,var(--lt-card-2,#fcf7ee) 100%) !important;
        border:3px solid var(--lt-border,#5c2808) !important;
        border-radius:15px !important;
        box-shadow:0 10px 26px rgba(43,17,3,0.14),inset 0 2px 0 #ffffff !important;
        color:var(--lt-ink,#120500) !important;
    }
    html.light-theme #sales-fc1-smart-ai-command-bar > div:first-child > div:first-child,
    html.light-theme #split-invoice-view #split-fc1-ai-cockpit-bar > div:first-child > div:first-child {
        background:#ffffff !important;
        border:2.5px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #sales-proportional-root label,
    html.light-theme #sales-proportional-root .h-label,
    html.light-theme #sales-proportional-root .v-label,
    html.light-theme #sales-proportional-root h1,
    html.light-theme #sales-proportional-root h2,
    html.light-theme #sales-proportional-root h3,
    html.light-theme #sales-proportional-root h4,
    html.light-theme #sales-fc2-live-result-summary-text,
    html.light-theme .sales-fc1-modal-card *:not(button) {
        color:var(--lt-ink,#000000) !important;
        -webkit-text-fill-color:var(--lt-ink,#000000) !important;
        text-shadow:none !important;
    }
    #sales-proportional-root button, #sales-proportional-root button:hover, #sales-proportional-root button:active, #sales-proportional-root button:focus,
    .sales-fc1-modal-card button, .sales-fc1-modal-card button:hover {
        text-shadow:none !important;
        -webkit-text-stroke:0px transparent !important;
        transform:none !important;
        animation:none !important;
    }
    html.light-theme #split-invoice-view input:not([type="checkbox"]):not([type="radio"]),
    html.light-theme #split-invoice-view select,
    html.light-theme #split-invoice-view textarea,
    html.light-theme #split-invoice-view input:focus {
        background-color:#ffffff !important;
        -webkit-box-shadow:0 0 0 1000px #ffffff inset !important;
        box-shadow:0 0 0 1000px #ffffff inset !important;
        color:#000000 !important;
        -webkit-text-fill-color:#000000 !important;
        border:2.5px solid var(--lt-border,#5c2808) !important;
        font-weight:900 !important;
    }
    html.light-theme #split-invoice-view table.pos-grid thead th {
        background:linear-gradient(180deg,var(--lt-th-1,#faf3e3) 0%,var(--lt-th-2,#f1e4ca) 100%) !important;
        color:var(--lt-ink,#120500) !important;
        -webkit-text-fill-color:var(--lt-ink,#120500) !important;
        font-weight:900 !important;
        font-size:1rem !important;
        border:2.5px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #split-invoice-view table.pos-grid tbody td {
        background:var(--lt-row-odd,#ffffff) !important;
        color:var(--lt-ink,#120500) !important;
        -webkit-text-fill-color:var(--lt-ink,#120500) !important;
        font-weight:900 !important;
        font-size:0.98rem !important;
        border:2px solid var(--lt-border,#5c2808) !important;
    }
    html.light-theme #split-invoice-view table.pos-grid tbody tr:nth-child(even) td {
        background:var(--lt-row-even,#fefcf7) !important;
    }
    #split-invoice-view .split-3d-cap,
    #split-invoice-view .split-3d-cap *,
    #split-invoice-view #split-fin-curr-toggle-btn,
    #split-invoice-view .split-ledger-btn {
        color:#ffffff !important;
        -webkit-text-fill-color:#ffffff !important;
        font-weight:900 !important;
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
    #split-invoice-view input.is-zero-val:not(:focus) { opacity:0.38 !important; }
    `;

    function ensureSplitStyleLast() {
        let st = document.getElementById('split-purchase-constitution-2027-style');
        if (!st) {
            st = document.createElement('style');
            st.id = 'split-purchase-constitution-2027-style';
            st.textContent = css;
            document.head.appendChild(st);
        } else if (document.head && document.head.lastElementChild !== st) {
            document.head.appendChild(st);
        }
    }
    ensureSplitStyleLast();
    setTimeout(ensureSplitStyleLast, 250);
    setTimeout(ensureSplitStyleLast, 900);

    // 12-Tone Popover Engine (8 Light + 4 Dark/Metallic) + 4 Interactive System Sound Tones
    const SOUND_TONE_KEY = 'magaies_system_sound_tone_v1';
    const TONES = [
        { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',    bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true,  themeKey: 'midnight_obsidian' },
        { id: 'pure_oled',       name: 'سواد مطلق فاحم (OLED)',    bg: '#000000', fg: '#090d16', brd: '#0284c7', isDark: true,  themeKey: 'pure_oled' },
        { id: 'mocha_brown',     name: 'بني ملكي دافئ (Mocha)',    bg: '#1f120d', fg: '#3e2723', brd: '#f59e0b', isDark: true,  themeKey: 'mocha_brown' },
        { id: 'slate_twilight',  name: 'سليت فحمي مسائي (Slate)',  bg: '#1e293b', fg: '#334155', brd: '#94a3b8', isDark: true,  themeKey: 'slate_twilight' },
        { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)', bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', isDark: false, themeKey: 'warm_cream' },
        { id: 'royal_bronze',    name: 'برونزي ملكي فاخر',       bg: '#b07239', fg: '#d9a066', brd: '#78350f', isDark: false, themeKey: 'warm_cream' },
        { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي',  bg: '#64748b', fg: '#94a3b8', brd: '#334155', isDark: false, themeKey: 'exec_gray' },
        { id: 'crystal_silver',  name: 'فضي جليدي ناصع',         bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', isDark: false, themeKey: 'ultra_white' },
        { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',       bg: '#10b981', fg: '#6ee7b7', brd: '#047857', isDark: false, themeKey: 'warm_cream' },
        { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',     bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', isDark: false, themeKey: 'ultra_white' },
        { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',       bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', isDark: false, themeKey: 'warm_cream' },
        { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',        bg: '#334155', fg: '#475569', brd: '#0f172a', isDark: false, themeKey: 'exec_gray' }
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
    window.setSalesSystemAudioTone = function (mode) {
        localStorage.setItem(SOUND_TONE_KEY, mode);
        playSalesSystemAudioTone(960);
        if (typeof showToast === 'function') showToast('🔊 تم ضبط نغمة النظام التفاعلية: ' + mode, 'success');
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
            localStorage.setItem('pur_light_tone_palette', t.id);
            localStorage.setItem('magaies_light_tone', t.id);
            ['magaies_active_theme', 'magaiees_global_theme', 'magaiees_mobile_theme', 'ui_theme'].forEach(k => localStorage.setItem(k, targetTheme));
            localStorage.setItem('erp_theme', t.isDark ? 'dark' : 'light');
        } catch (_e) {}
        ensureSplitStyleLast();
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
                const isThemeBtn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                if (pop.style.display !== 'none' && !pop.contains(e.target) && !isThemeBtn) pop.style.display = 'none';
            });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDarkNow = !document.documentElement.classList.contains('light-theme');
        pop.style.cssText = 'position:fixed;z-index:9999999;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65),0 0 22px rgba(245,158,11,0.35);min-width:255px;direction:rtl;font-family:Tajawal,Cairo,sans-serif;';
        const cur = document.documentElement.getAttribute('data-light-tone') || (isDarkNow ? 'dark_midnight' : 'ivory_default');
        const curSound = localStorage.getItem(SOUND_TONE_KEY) || 'crystal';
        const hdrCol = isDarkNow ? '#fde047' : '#000000';
        pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 المظهر ونغمات النظام (12)</span><span style="font-size:0.75rem;color:${isDarkNow?'#94a3b8':'#000000'}!important;-webkit-text-fill-color:${isDarkNow?'#94a3b8':'#000000'}!important;">مطابق للمشتريات 100%</span></div>` +
            `<div style="max-height:330px;overflow-y:auto;padding-left:2px;">` +
            TONES.map(t => {
                const txtCol = t.isDark ? '#ffffff' : '#000000';
                const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                return `<button type="button" data-tone-dark="${t.isDark?'1':'0'}" onclick="window.applyPurchaseLightTone('${t.id}',true);document.getElementById('pur-light-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:6px 10px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur===t.id?'2.5px solid #d97706':'1.5px solid '+t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.86rem;text-align:right;"><span style="width:26px;height:26px;border-radius:7px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur===t.id?`<span style="color:${t.isDark?'#fde047':'#047857'}!important;-webkit-text-fill-color:${t.isDark?'#fde047':'#047857'}!important;">✓</span>`:''}</button>`;
            }).join('') +
            `</div>` +
            `<div style="margin-top:6px;padding-top:6px;border-top:1px solid rgba(148,163,184,0.35);display:grid;grid-template-columns:repeat(4,1fr);gap:4px;">` +
            SYSTEM_AUDIO_TONES.map(s => `<button type="button" onclick="window.setSalesSystemAudioTone('${s.id}');window.openPurchaseLightTonePopover();window.openPurchaseLightTonePopover();" style="padding:4px 4px;border-radius:7px;font-size:0.72rem;font-weight:900;cursor:pointer;border:${curSound===s.id?'2px solid #10b981':'1px solid rgba(148,163,184,0.4)'}!important;background:${curSound===s.id?'rgba(16,185,129,0.22)':'transparent'}!important;color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;">${s.name}</button>`).join('') +
            `</div>`;
        const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 450, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 280, r.left)) + 'px';
        pop.style.display = 'block';
    };
    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest && e.target.closest('.msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        window.openPurchaseLightTonePopover(btn);
    }, true);

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
    setTimeout(refreshSplitConstitutionEnhancements, 400);
})();
