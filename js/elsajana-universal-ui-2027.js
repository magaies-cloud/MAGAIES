/**
 * ELSAJANA 2027 — Universal Purchase Theme Engine (13 Tones), Strict Daylight Guard (#0F172A),
 * Sidebar-Aware Laser Centering, 10x Telescopic Vector Zoom & Dual View (Compact/Full) + LIFO Back/Exit.
 * Shared across smart-logistics.php, Main Dashboard, Purchase & all Popups/Modals (0ms execution).
 */
(function () {
    'use strict';
    if (window.ElsajanaUniversalUI2027) return;

    const TONES = [
        { id: 'dark_midnight',   name: 'داكن كحلي ملكي (Dark)',         bg: '#0f172a', fg: '#1e293b', brd: '#38bdf8', isDark: true, themeKey: 'midnight_obsidian' },
        { id: 'pure_oled',       name: 'أسود ناصع للشاشات (OLED)',      bg: '#010308', fg: '#090d16', brd: '#0284c7', isDark: true, themeKey: 'pure_oled' },
        { id: 'mocha_brown',     name: 'بني قهوة فخم (Mocha Dark)',     bg: '#1f120d', fg: '#3e2723', brd: '#f59e0b', isDark: true, themeKey: 'mocha_brown' },
        { id: 'slate_twilight',  name: 'رمادي معدني داكن (Slate)',       bg: '#1e293b', fg: '#334155', brd: '#94a3b8', isDark: true, themeKey: 'slate_twilight' },
        { id: 'ivory_default',   name: 'عاجي دافئ (فاتح افتراضي)',       bg: '#d9c39e', fg: '#f3e5ab', brd: '#854d0e', themeKey: 'warm_cream' },
        { id: 'royal_bronze',    name: '👑 الذهبي الملكي (برونزي فاخر)', bg: '#b07239', fg: '#d9a066', brd: '#78350f', themeKey: 'warm_cream' },
        { id: 'platinum_gray',   name: 'رمادي بلاتيني احترافي',          bg: '#64748b', fg: '#94a3b8', brd: '#334155', themeKey: 'exec_gray' },
        { id: 'crystal_silver',  name: 'فضي جليدي ناصع',                 bg: '#cbd5e1', fg: '#e2e8f0', brd: '#64748b', themeKey: 'ultra_white' },
        { id: 'sage_pearl',      name: 'لؤلؤي زمردي هادئ',               bg: '#10b981', fg: '#6ee7b7', brd: '#047857', themeKey: 'warm_cream' },
        { id: 'sky_alabaster',   name: 'سماوي لازوردي فاتح',             bg: '#0284c7', fg: '#38bdf8', brd: '#0369a1', themeKey: 'ultra_white' },
        { id: 'rose_blossom',    name: 'زهري ملكي ناعم (Rose)',          bg: '#ec4899', fg: '#fbcfe8', brd: '#9d174d', themeKey: 'warm_cream' },
        { id: 'warm_mocha_dark', name: 'موكا صحراوي غامق',               bg: '#6b4226', fg: '#9c6b43', brd: '#451a03', themeKey: 'warm_cream' },
        { id: 'deep_slate_dark', name: 'رصاصي فحمي غامق',                bg: '#334155', fg: '#475569', brd: '#0f172a', themeKey: 'exec_gray' }
    ];

    const playToneClickBeep = () => {
        if (typeof window.playGlobalErpSystemSoundTone === 'function') {
            window.playGlobalErpSystemSoundTone();
            return;
        }
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = window._elsUiAudioCtx || (window._elsUiAudioCtx = new Ctx());
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(660, ctx.currentTime);
            gain.gain.setValueAtTime(0.04, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.07);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 0.07);
        } catch (_) {}
    };

    const getCk = (n) => {
        const m = document.cookie.match(new RegExp('(?:^|; )' + n + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    };

    const applyUnifiedSystemTone = (toneId, notify = false) => {
        const t = TONES.find(x => x.id === toneId) || (['dark_midnight', 'midnight_obsidian', 'dark', 'pure_oled', 'mocha_brown', 'slate_twilight'].includes(toneId) ? TONES[0] : TONES[4]);
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
        } catch (_) {}
        const _s = (k, v) => { try { localStorage.setItem(k, v); } catch (_) {} };
        _s('pur_light_tone_palette', t.id);
        _s('magaies_light_tone', t.id);
        ['magaies_active_theme', 'magaiees_global_theme', 'magaiees_mobile_theme', 'ui_theme'].forEach(k => _s(k, targetTheme));
        _s('erp_theme', t.isDark ? 'dark' : 'light');

        if (!t.isDark) runStrictDaylightGuardScan();
        if (notify) {
            playToneClickBeep();
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: targetTheme, tone: t.id, isLight: !t.isDark } }));
            if (typeof window.showToast === 'function') window.showToast((t.isDark ? '🌙 ' : '🎨 ') + 'تم تعميم المظهر الموحد: ' + t.name, 'success', 2400);
        }
    };

    const openUniversalErpTonePopover = (anchorEl) => {
        let pop = document.getElementById('els-universal-tone-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.id = 'els-universal-tone-popover';
            document.body.appendChild(pop);
            document.addEventListener('click', (e) => {
                const isBtn = e.target && e.target.closest && e.target.closest('#sl-master-theme-btn, .msg-quartet-qtheme, .theme-toggle-btn, .msg-quartet-themes, #magaiees-master-themes-btn');
                if (pop.style.display !== 'none' && !pop.contains(e.target) && !isBtn) pop.style.display = 'none';
            });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDarkNow = !document.documentElement.classList.contains('light-theme');
        const rawToneAttr = document.documentElement.getAttribute('data-light-tone') || localStorage.getItem('magaies_light_tone') || (isDarkNow ? 'dark_midnight' : 'ivory_default');
        const cur = TONES.some(x => x.id === rawToneAttr) ? rawToneAttr : (isDarkNow ? 'dark_midnight' : 'ivory_default');
        const hdrCol = isDarkNow ? '#fde047' : '#0F172A';
        pop.style.cssText = 'position:fixed;z-index:2147483644;background:' + (isDarkNow ? 'linear-gradient(145deg,#0f172a,#1e293b)' : 'linear-gradient(145deg,#fffbf2,#f3ead8)') + ';border:2.5px solid #fbbf24;border-radius:14px;padding:10px;box-shadow:0 18px 42px rgba(0,0,0,0.65);min-width:275px;max-height:86vh;overflow-y:auto;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
        pop.innerHTML = `<div style="color:${hdrCol}!important;-webkit-text-fill-color:${hdrCol}!important;font-weight:900;font-size:0.92rem;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.35);display:flex;justify-content:space-between;align-items:center;"><span>🎨 المظهر ونغمات النظام (${TONES.length} ثيماً)</span><span style="font-size:0.74rem;color:${isDarkNow ? '#94a3b8' : '#1E293B'}!important;">تطابق 100%</span></div>` +
            TONES.map(t => {
                const txtCol = t.isDark ? '#ffffff' : '#0F172A';
                const btnBg = t.isDark ? `linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%)` : `linear-gradient(135deg,#ffffff 0%,${t.fg} 100%)`;
                return `<button type="button" onclick="window.ElsajanaUniversalUI2027.applyUnifiedSystemTone('${t.id}',true);document.getElementById('els-universal-tone-popover').style.display='none';" style="width:100%;display:flex;align-items:center;gap:10px;padding:7px 10px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${btnBg}!important;border:${cur === t.id ? '2.5px solid #d97706' : '1.5px solid ' + t.brd}!important;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;font-weight:900;font-size:0.88rem;text-align:right;"><span style="width:26px;height:26px;border-radius:7px;background:linear-gradient(135deg,${t.fg} 0%,${t.bg} 100%);border:2px solid ${t.brd};flex-shrink:0;"></span><span style="flex:1;color:${txtCol}!important;-webkit-text-fill-color:${txtCol}!important;">${t.name}</span>${cur === t.id ? `<span style="color:${t.isDark ? '#fde047' : '#047857'}!important;">✓</span>` : ''}</button>`;
            }).join('');
        const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 460, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 290, r.left)) + 'px';
        pop.style.display = 'block';
    };

    window.openUniversalErpTonePopover = openUniversalErpTonePopover;
    if (typeof window.openPurchaseLightTonePopover !== 'function') window.openPurchaseLightTonePopover = openUniversalErpTonePopover;
    if (typeof window.applyPurchaseLightTone !== 'function') window.applyPurchaseLightTone = applyUnifiedSystemTone;

    // ── STRICT DAYLIGHT GUARD (Auto-converts any pale text/icon over light surfaces to #0F172A / #1E293B) ──
    const isProtectedDarkBadge = (el) => {
        if (!el || !el.closest) return false;
        return !!el.closest('button, .dmr-btn, .sl-btn, .sl-fin-pill, .sl-grand-tot-box, .sl-btn-cnt, .grn-fin-cap, .grn-dark-surface-btn, .grn-tot-capsule-usd, .grn-btn-save-primary, #grn-3level-bar, .crystal-btn, .royal-crystal-btn, .bal-card, #wa-both-creditor-card, #wa-modal-live-balance, .header-nav-toggle-btn, .icon-box, #scp-fc1-smart-ai-command-bar, .magaiees-stack-btn, .elsajana-ftoast, .dmr-toast-item, .global-sidebar, .pa-dna-btn, .status-btn.active, .type-chip.active, .preset-ribbon-btn, .hub-tab-btn.active, [data-dark-capsule="1"]');
    };

    const parseRgb = (str) => {
        const m = String(str || '').match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
        return m ? [parseInt(m[1], 10), parseInt(m[2], 10), parseInt(m[3], 10)] : null;
    };

    const runStrictDaylightGuardScan = (root = document.body) => {
        if (!root || !document.documentElement.classList.contains('light-theme')) return;
        const candidates = root.querySelectorAll('[style*="color"], h1, h2, h3, h4, label, th, td, select, input');
        for (let i = 0; i < candidates.length; i++) {
            const el = candidates[i];
            if (isProtectedDarkBadge(el)) continue;
            const cs = window.getComputedStyle(el);
            const rgb = parseRgb(cs.color);
            if (!rgb) continue;
            const lum = 0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2];
            // If text luminance is pale/light (>155: white, pale yellow, light sky, light gray), check if ancestor surface is light
            if (lum > 155) {
                let bgLum = 235;
                let p = el;
                while (p && p !== document.documentElement) {
                    const bgc = window.getComputedStyle(p).backgroundColor;
                    if (bgc && bgc !== 'rgba(0, 0, 0, 0)' && bgc !== 'transparent') {
                        const brgb = parseRgb(bgc);
                        if (brgb) { bgLum = 0.299 * brgb[0] + 0.587 * brgb[1] + 0.114 * brgb[2]; break; }
                    }
                    p = p.parentElement;
                }
                if (bgLum > 145) {
                    el.style.setProperty('color', '#0F172A', 'important');
                    el.style.setProperty('-webkit-text-fill-color', '#0F172A', 'important');
                    el.dataset.daylightGuarded = '1';
                }
            }
        }
    };

    // ── SIDEBAR-AWARE LASER CENTERING + 10X TELESCOPIC VECTOR ZOOM ──
    let _zoomLevel = 1;
    const syncLaserSidebarGeometry = () => {
        const sb = document.querySelector('.global-sidebar');
        let sbW = 0;
        if (window.innerWidth > 768 && document.body && document.body.classList.contains('has-sidebar') && !document.body.classList.contains('no-sidebar')) {
            sbW = (sb && window.getComputedStyle(sb).display !== 'none')
                ? (Math.round(sb.getBoundingClientRect().width) || (document.body.classList.contains('sidebar-open') ? 240 : 62))
                : (document.body.classList.contains('sidebar-open') ? 240 : 62);
        }
        document.documentElement.style.setProperty('--sb-live-w', sbW + 'px');
    };

    const setTelescopicZoom = (val) => {
        _zoomLevel = Math.max(0.5, Math.min(10, Math.round(val * 100) / 100));
        const stage = document.getElementById('sl-zoom-stage')
            || document.getElementById('grn-zoom-stage')
            || document.getElementById('scp-zoom-stage')
            || document.getElementById('mcp-zoom-stage')
            || document.getElementById('pa-zoom-stage')
            || document.getElementById('mb-zoom-stage')
            || document.getElementById('dmr-zoom-stage')
            || (document.getElementById('purchase-editor-view') && document.getElementById('purchase-editor-view').style.display !== 'none' ? document.getElementById('purchase-editor-view') : null)
            || document.getElementById('purchase-hub-view');
        if (stage) {
            stage.style.zoom = String(_zoomLevel);
            const pctTxt = Math.round(_zoomLevel * 100) + '%';
            const badge = document.getElementById('sl-zoom-pct-lbl');
            if (badge) badge.textContent = pctTxt;
            const scpB = document.getElementById('scp-fc1-zoom-badge');
            if (scpB) scpB.textContent = '📐 مقاس (6:8): ' + pctTxt;
            const mcpB = document.getElementById('mcpC15ZoomHudVal');
            if (mcpB) mcpB.textContent = pctTxt + ' (6:8)';
            const paB = document.getElementById('pa-c1-zoom-badge');
            if (paB) paB.textContent = '📐 أبعاد (6:8) ' + pctTxt;
            try { localStorage.setItem('magaies_telescopic_zoom_scale', String(_zoomLevel)); } catch (_) {}
        }
    };

    // ── DUAL VIEW (COMPACT / FULL) + SEQUENTIAL LIFO BACK & EXIT ──
    const toggleDualViewMode = () => {
        const isCompact = document.body.classList.toggle('els-compact-mode');
        try { localStorage.setItem('els_sl_compact_mode', isCompact ? '1' : '0'); } catch (_) {}
        const btn = document.getElementById('sl-dual-mode-btn');
        if (btn) btn.innerHTML = isCompact ? '🖥️ الوضع الكامل' : '⚡ الوضع المختصر';
        if (typeof window.showToast === 'function') {
            window.showToast(isCompact ? '⚡ تم التفعيل اللحظي للوضع المختصر السريع' : '🖥️ تم التفعيل اللحظي للوضع المحاسبي الكامل', 'success', 2000);
        }
    };

    const sequentialGoBack = () => {
        // Step 1: Close any open modal or popover first
        const openPop = document.getElementById('els-universal-tone-popover');
        if (openPop && openPop.style.display === 'block') { openPop.style.display = 'none'; return; }
        if (typeof window.handleGrnOneStepBack === 'function' && window.handleGrnOneStepBack()) return;
        const scpDynModals = document.querySelectorAll('.scp-fc1-modal-overlay, #mgC14GlobalReportModal[style*="flex"], .preview-modal[style*="flex"], .fc1-modal-open, .fc2-modal-open');
        if (scpDynModals.length > 0) {
            scpDynModals.forEach(m => {
                if (m.classList.contains('scp-fc1-modal-overlay')) m.remove();
                else if (m.classList.contains('fc1-modal-open') || m.classList.contains('fc2-modal-open')) m.classList.remove('fc1-modal-open', 'fc2-modal-open');
                else m.style.display = 'none';
            });
            return;
        }
        const mcpSplit = document.getElementById('mcpFc1SplitScreenOverlay');
        if (mcpSplit && mcpSplit.classList.contains('fc1-split-open')) { mcpSplit.classList.remove('fc1-split-open'); document.body.classList.remove('mcp-fc1-split-active'); return; }
        const paModal = document.getElementById('pa-c1-nested-modal-overlay');
        if (paModal && (paModal.classList.contains('show') || paModal.style.display === 'flex')) { if (typeof window.closePaC1Modal === 'function') window.closePaC1Modal(); else paModal.style.display = 'none'; return; }
        const mbCtx = document.querySelectorAll('#columnContextMenu[style*="block"], #partnerContextMenu[style*="block"]');
        if (mbCtx.length > 0) { mbCtx.forEach(m => { m.style.display = 'none'; }); return; }
        const mbSetSb = document.getElementById('settings-sidebar');
        if (mbSetSb && mbSetSb.classList.contains('open')) { if (typeof window.toggleSettingsSidebar === 'function') window.toggleSettingsSidebar(); else mbSetSb.classList.remove('open'); return; }
        const mbVisHub = document.getElementById('visual-intelligence-hub');
        if (mbVisHub && mbVisHub.style.display !== 'none' && mbVisHub.offsetParent !== null) { if (typeof window.toggleVisualHub === 'function') window.toggleVisualHub(); else mbVisHub.style.display = 'none'; return; }
        const dmrHud = document.getElementById('dmr-smart-guide-micro-hud');
        if (dmrHud && dmrHud.style.display !== 'none') { if (window.DailyMasterSupremeEngine && typeof window.DailyMasterSupremeEngine.closeSmartGuideHud === 'function') window.DailyMasterSupremeEngine.closeSmartGuideHud(); else dmrHud.style.display = 'none'; return; }
        const dmrModals = document.querySelectorAll('.dmr-modal-backdrop.active');
        if (dmrModals.length > 0) { dmrModals[dmrModals.length - 1].classList.remove('active'); return; }
        const openModals = document.querySelectorAll('.modal-overlay[style*="flex"], .modal-overlay.open, .modal-overlay.visible, .modal-overlay.show, #sl-cols-drawer[style*="block"]');
        if (openModals.length > 0) {
            openModals.forEach(m => { m.style.display = 'none'; m.classList.remove('open', 'visible', 'show'); });
            return;
        }
        const paDet = document.getElementById('detail-content');
        if (paDet && paDet.style.display !== 'none' && typeof window.resetSelection === 'function') { window.resetSelection(); return; }
        const editWarnBanner = document.getElementById('edit-warning-banner');
        if (editWarnBanner && editWarnBanner.style.display !== 'none' && typeof window.cancelEditOldVoucher === 'function') {
            window.cancelEditOldVoucher();
            return;
        }
        // Step 2: If in smart-logistics with an active loaded document, clear back to clean registry view
        const invInput = document.getElementById('sl-doc-no-display') || document.getElementById('sl-lbl-inv-no');
        if (invInput && invInput.textContent && invInput.textContent.trim() !== '---' && invInput.textContent.trim() !== '' && window._slCurrentLoadedDoc) {
            window._slCurrentLoadedDoc = null;
            const reg = document.getElementById('sl-registry-card');
            if (reg) reg.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        // Step 3: LIFO Back Stack
        if (typeof window.magaiesGoBackStepLIFO === 'function') { window.magaiesGoBackStepLIFO(); return; }
        if (window.history.length > 1) { window.history.back(); return; }
        window.location.href = 'magaiees_unified_dashboard.php';
    };

    const directExitToMain = () => {
        if (typeof window.magaiesExitToMainDashboard === 'function') { window.magaiesExitToMainDashboard(); return; }
        window.location.href = 'magaiees_unified_dashboard.php';
    };

    // ── INJECT UNIFIED CSS VARIABLES & STRICT DAYLIGHT RULES ──
    const injectUnifiedStyles = () => {
        if (document.getElementById('els-universal-constitution1-css')) return;
        const st = document.createElement('style');
        st.id = 'els-universal-constitution1-css';
        st.textContent = `
            /* All 13 System Themes & Tones Unified Variables (4 Dark + 9 Light) */
            :root:not(.light-theme), :root:not(.light-theme)[data-light-tone="dark_midnight"] { --dk-canvas:#010308; --dk-card-1:#1b3159; --dk-card-2:#132341; --dk-sub-1:#0b1120; --dk-border:#38bdf8; }
            :root:not(.light-theme)[data-light-tone="pure_oled"], :root[data-theme="pure_oled"] { --dk-canvas:#000000; --dk-card-1:#050811; --dk-card-2:#020409; --dk-sub-1:#000000; --dk-border:#0284c7; }
            :root:not(.light-theme)[data-light-tone="mocha_brown"], :root[data-theme="mocha_brown"] { --dk-canvas:#120906; --dk-card-1:#3b2318; --dk-card-2:#24140e; --dk-sub-1:#140b07; --dk-border:#f59e0b; }
            :root:not(.light-theme)[data-light-tone="slate_twilight"], :root[data-theme="slate_twilight"] { --dk-canvas:#0f172a; --dk-card-1:#334155; --dk-card-2:#1e293b; --dk-sub-1:#0f172a; --dk-border:#94a3b8; }
            :root.light-theme[data-light-tone="rose_blossom"] { --lt-canvas-1:#fce7f3; --lt-canvas-2:#fbcfe8; --lt-hdr-1:#fdf2f8; --lt-hdr-2:#fce7f3; --lt-hdr-3:#f9a8d4; --lt-card-1:#ffffff; --lt-card-2:#fdf2f8; --lt-sub-1:#ffffff; --lt-sub-2:#fce7f3; --lt-th-1:#fce7f3; --lt-th-2:#fbcfe8; --lt-row-odd:#ffffff; --lt-row-even:#fdf2f8; --lt-border:#9d174d; --lt-border-hover:#db2777; --lt-ink:#0F172A; }

            html:not(.light-theme) #sl-main-wrapper ~ style,
            html:not(.light-theme) body[data-logistics-mode] { background: var(--dk-canvas, #010308) !important; color: #ffffff !important; }
            html:not(.light-theme) body[data-logistics-mode] .sl-glass-card,
            html:not(.light-theme) body[data-logistics-mode] .sl-floating-summary {
                background: linear-gradient(145deg, var(--dk-card-1, #1b3159) 0%, var(--dk-card-2, #132341) 100%) !important;
                border: 2px solid var(--dk-border, #38bdf8) !important;
                animation: none !important;
            }
            :root.light-theme .sl-glass-card, :root.light-theme .sl-floating-summary { animation: none !important; }

            /* Strict Daylight Contrast Guard across Screen, Main Dashboard & All Modals */
            :root.light-theme #sl-top-cockpit-bar h1,
            :root.light-theme #sl-main-title-banner {
                background: linear-gradient(135deg, #ffffff 0%, var(--lt-sub-2, #fbf4e6) 100%) !important;
                color: #0F172A !important; -webkit-text-fill-color: #0F172A !important;
                border: 2px solid var(--lt-border, #5c2808) !important;
            }
            :root.light-theme #sl-reg-wh-filter,
            :root.light-theme #sl-reg-limit,
            :root.light-theme .modal-overlay .modal-card,
            :root.light-theme .modal-overlay .modal-card h3,
            :root.light-theme .modal-overlay .modal-card label,
            :root.light-theme .modal-overlay .modal-card div:not(.sl-btn),
            :root.light-theme #sl-mobile-bridge-modal div:not(.sl-btn) {
                color: #0F172A !important; -webkit-text-fill-color: #0F172A !important;
            }
            :root.light-theme .modal-overlay .modal-card {
                background: linear-gradient(145deg, #ffffff 0%, var(--lt-card-2, #fcf7ee) 100%) !important;
                border: 2.5px solid var(--lt-border, #5c2808) !important;
            }

            /* Purchase 13-Tone Unification & Sculpted Borders for Single, Multi Cash Payment, Posting Accounts, Main Balances & Child Modals */
            #mcp-proportional-root ~ .ambient-bg, body:has(#mcp-proportional-root) > .ambient-bg, body:has(#pa-proportional-root) > .ambient-bg, body:has(#mb-proportional-root) > .ambient-bg { display: none !important; animation: none !important; }
            :root.light-theme #mb-proportional-root .report-header, :root.light-theme #mb-proportional-root #analytics-dashboard, :root.light-theme #mb-proportional-root #analytics-dashboard > div > div, :root.light-theme #mb-proportional-root #print-container, :root.light-theme #mb-proportional-root .v-chart-card, :root.light-theme #mb-proportional-root .v-section-banner, :root.light-theme #columnContextMenu, :root.light-theme #partnerContextMenu, :root.light-theme #settings-sidebar { background: linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #fcf7ee) 100%) !important; border: 2.5px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; text-shadow: none !important; }
            html:not(.light-theme) #mb-proportional-root .report-header, html:not(.light-theme) #mb-proportional-root #analytics-dashboard, html:not(.light-theme) #mb-proportional-root #print-container, html:not(.light-theme) #mb-proportional-root .v-chart-card, html:not(.light-theme) #columnContextMenu, html:not(.light-theme) #partnerContextMenu, html:not(.light-theme) #settings-sidebar { background: linear-gradient(145deg, var(--dk-card-1, #1b3159) 0%, var(--dk-card-2, #132341) 100%) !important; border: 2px solid var(--dk-border, #38bdf8) !important; color: #ffffff !important; }
            :root.light-theme #mb-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="range"]), :root.light-theme #mb-proportional-root select, :root.light-theme #settings-sidebar input:not([type="checkbox"]):not([type="color"]):not([type="range"]), :root.light-theme #settings-sidebar select { background-color: #FFFFFF !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2px solid var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
            :root.light-theme #mb-proportional-root #kpi-dr, :root.light-theme #mb-proportional-root #kpi-dr-foreign { color: #047857 !important; -webkit-text-fill-color: #047857 !important; text-shadow: none !important; }
            :root.light-theme #mb-proportional-root #kpi-cr, :root.light-theme #mb-proportional-root #kpi-cr-foreign { color: #be123c !important; -webkit-text-fill-color: #be123c !important; text-shadow: none !important; }
            :root.light-theme #mb-proportional-root #kpi-bal, :root.light-theme #mb-proportional-root #kpi-bal-foreign { color: #0369a1 !important; -webkit-text-fill-color: #0369a1 !important; text-shadow: none !important; }
            :root.light-theme #mb-proportional-root #analytics-dashboard div, :root.light-theme #mb-proportional-root #analytics-dashboard span, :root.light-theme #mb-proportional-root .v-chart-card h4, :root.light-theme #mb-proportional-root .v-chart-card span, :root.light-theme #columnContextMenu div, :root.light-theme #partnerContextMenu div, :root.light-theme #settings-sidebar label, :root.light-theme #settings-sidebar h3, :root.light-theme #settings-sidebar p { text-shadow: none !important; }
            :root.light-theme #pa-proportional-root .filters-wrapper,
            :root.light-theme #pa-proportional-root .master-pane,
            :root.light-theme #pa-proportional-root .detail-pane,
            :root.light-theme #pa-proportional-root .pa-c1-card,
            :root.light-theme .pa-c1-modal-box,
            :root.light-theme #custom-confirm-modal .modal-box { background: linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #fcf7ee) 100%) !important; border: 2.5px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            html:not(.light-theme) #pa-proportional-root .filters-wrapper,
            html:not(.light-theme) #pa-proportional-root .master-pane,
            html:not(.light-theme) #pa-proportional-root .detail-pane,
            html:not(.light-theme) #pa-proportional-root .pa-c1-card,
            html:not(.light-theme) .pa-c1-modal-box { background: linear-gradient(145deg, var(--dk-card-1, #1b3159) 0%, var(--dk-card-2, #132341) 100%) !important; border: 2px solid var(--dk-border, #38bdf8) !important; color: #ffffff !important; }
            :root.light-theme #pa-proportional-root input:not([type="checkbox"]):not([type="radio"]), :root.light-theme #pa-proportional-root select, :root.light-theme .pa-c1-modal-box input, :root.light-theme .pa-c1-modal-box select { background-color: #FFFFFF !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2px solid var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
            :root.light-theme #pa-proportional-root .type-filters a, :root.light-theme #pa-proportional-root .type-chip:not(.active), :root.light-theme #pa-proportional-root #pa-c7-conn-led, :root.light-theme #pa-proportional-root #pa-kpi-unposted-tafqeet, :root.light-theme #pa-proportional-root #pa-kpi-balance-seal, :root.light-theme #pa-proportional-root #pa-kpi-crdt-sync { color: #0F172A !important; -webkit-text-fill-color: #0F172A !important; font-weight: 900 !important; }
            :root.light-theme #scp-proportional-root .single-unified-form,
            :root.light-theme #mcp-proportional-root #main-form-panel,
            :root.light-theme #mcp-proportional-root .account-block { background: linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #fcf7ee) 100%) !important; border: 2.5px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            :root.light-theme #scp-proportional-root .sub-header-bar,
            :root.light-theme #mcp-proportional-root #payment-methods-row { background: linear-gradient(135deg, var(--lt-sub-1, #ffffff) 0%, var(--lt-sub-2, #fbf4e6) 100%) !important; border: 2px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            html:not(.light-theme) #scp-proportional-root .single-unified-form,
            html:not(.light-theme) #mcp-proportional-root #main-form-panel { background: linear-gradient(145deg, var(--dk-card-1, #1b3159) 0%, var(--dk-card-2, #132341) 100%) !important; border: 2px solid var(--dk-border, #38bdf8) !important; }
            :root.light-theme .scp-fc1-modal-card, :root.light-theme .mg-c14-modal-card, :root.light-theme .mcp-fc1-modal-card, :root.light-theme .mcp-fc2-modal-card { background: linear-gradient(145deg, #ffffff 0%, var(--lt-card-2, #fcf7ee) 100%) !important; border: 2.5px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            :root.light-theme #scp-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="hidden"]),
            :root.light-theme #scp-proportional-root select, :root.light-theme #scp-proportional-root textarea,
            :root.light-theme #mcp-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="hidden"]),
            :root.light-theme #mcp-proportional-root select, :root.light-theme #mcp-proportional-root textarea { background-color: #FFFFFF !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2px solid var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
            html:not(.light-theme) #scp-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="hidden"]),
            html:not(.light-theme) #scp-proportional-root select, html:not(.light-theme) #scp-proportional-root textarea,
            html:not(.light-theme) #mcp-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="hidden"]),
            html:not(.light-theme) #mcp-proportional-root select, html:not(.light-theme) #mcp-proportional-root textarea { background-color: #000000 !important; color: #FFFFFF !important; -webkit-text-fill-color: #FFFFFF !important; border: 1.8px solid rgba(255,255,255,0.28) !important; font-weight: 900 !important; }

            /* Laser Centering inside Net Workspace */
            #sl-main-wrapper, #grn-proportional-root, #scp-proportional-root, #mcp-proportional-root, #pa-proportional-root, #mb-proportional-root, #dmr-proportional-root {
                width: calc(100% - 24px) !important;
                max-width: calc(100% - 24px) !important;
                margin-left: 12px !important;
                margin-right: 12px !important;
                transform: none !important;
                box-sizing: border-box !important;
            }
            #pa-proportional-root, #mb-proportional-root, #dmr-proportional-root { overflow-y: auto !important; overflow-x: auto !important; font-family: 'Cairo', 'Tajawal', 'Outfit', sans-serif !important; }
            :root.light-theme #dmr-proportional-root .no-print.bg-slate-900\/80 { background: linear-gradient(145deg, var(--lt-card-1, #ffffff) 0%, var(--lt-card-2, #fcf7ee) 100%) !important; border: 2.5px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            :root.light-theme #dmr-proportional-root .bg-slate-800\/80, :root.light-theme #dmr-proportional-root .bg-slate-800\/60 { background: var(--lt-sub-2, #fbf4e6) !important; border: 1.8px solid var(--lt-border, #5c2808) !important; color: #0F172A !important; }
            :root.light-theme #dmr-proportional-root input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="range"]), :root.light-theme #dmr-proportional-root select, :root.light-theme .dmr-modal-box input:not([type="checkbox"]):not([type="radio"]), :root.light-theme .dmr-modal-box select { background-color: #FFFFFF !important; color: #000000 !important; -webkit-text-fill-color: #000000 !important; border: 2px solid var(--lt-border, #5c2808) !important; font-weight: 900 !important; }
            #mb-proportional-root th, #mb-proportional-root td, #mb-proportional-root label, #mb-proportional-root button { white-space: normal !important; text-overflow: clip !important; }

            /* Dual View: Compact Mode */
            body.els-compact-mode #sl-qr-camera-card,
            body.els-compact-mode .sl-fin-strip-bar,
            body.els-compact-mode #grn-hub-fin-strip,
            body.els-compact-mode #grn-party-fin-strip,
            body.els-compact-mode #grn-hub-filters-bar,
            body.els-compact-mode #grn-c15-master-bar,
            body.els-compact-mode #scp-fc1-smart-ai-command-bar,
            body.els-compact-mode #scp-proportional-root .group-balances,
            body.els-compact-mode #scp-fc2-live-summary-strip,
            body.els-compact-mode #mcp-proportional-root #auto-send-control-wrapper,
            body.els-compact-mode #mcpFc1AiTopCommandBar,
            body.els-compact-mode #pa-c1-kpi-strip,
            body.els-compact-mode #pa-proportional-root #type-filters,
            body.els-compact-mode #mb-proportional-root .report-header .filter-row:first-child,
            body.els-compact-mode #mb-proportional-root #analytics-dashboard,
            body.els-compact-mode #dmr-peace-of-mind-banner,
            body.els-compact-mode #dmr-visual-analytics-suite,
            body.els-compact-mode #dmr-financial-position-hub { display: none !important; }
            body.els-compact-mode #sl-inspection-cockpit { grid-template-columns: 1fr !important; }
        `;
        document.head.appendChild(st);
    };

    injectUnifiedStyles();
    const initTone = getCk('magaies_light_tone') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || 'ivory_default';
    applyUnifiedSystemTone(initTone, false);

    document.addEventListener('DOMContentLoaded', () => {
        applyUnifiedSystemTone(getCk('magaies_light_tone') || localStorage.getItem('magaies_light_tone') || initTone, false);
        syncLaserSidebarGeometry();
        window.addEventListener('resize', syncLaserSidebarGeometry, { passive: true });

        // Observe sidebar width transitions (collapsed 62px <-> expanded 240px) for 100% real-time laser centering
        const attachSbObserver = () => {
            const sb = document.querySelector('.global-sidebar');
            if (sb && typeof ResizeObserver !== 'undefined' && !sb._laserObserved) {
                sb._laserObserved = true;
                new ResizeObserver(syncLaserSidebarGeometry).observe(sb);
            }
            syncLaserSidebarGeometry();
        };
        attachSbObserver();
        setTimeout(attachSbObserver, 250);

        // Wrap Smart-Logistics or Purchase-Receipt content below top bar into zoom stage so top bar & sidebar stay rock-steady during 10x zoom
        const wrap = document.getElementById('sl-main-wrapper');
        const topBar = document.getElementById('sl-top-cockpit-bar');
        if (wrap && topBar && !document.getElementById('sl-zoom-stage')) {
            const stage = document.createElement('div');
            stage.id = 'sl-zoom-stage';
            stage.style.cssText = 'display:flex;flex-direction:column;gap:8px;width:100%;transform-origin:top center;';
            while (topBar.nextSibling) stage.appendChild(topBar.nextSibling);
            wrap.appendChild(stage);
        }
        const grnRoot = document.getElementById('grn-proportional-root');
        const grnHub = document.getElementById('view-hub');
        const grnEd = document.getElementById('view-editor');
        if (grnRoot && grnHub && !document.getElementById('grn-zoom-stage')) {
            const gStage = document.createElement('div');
            gStage.id = 'grn-zoom-stage';
            gStage.style.cssText = 'display:flex;flex-direction:column;width:100%;transform-origin:top center;';
            grnHub.parentNode.insertBefore(gStage, grnHub);
            gStage.appendChild(grnHub);
            if (grnEd) gStage.appendChild(grnEd);
        }

        const scpRoot = document.getElementById('scp-proportional-root');
        const scpMainWrap = document.getElementById('main-layout-wrapper');
        if (scpRoot && scpMainWrap && !document.getElementById('scp-zoom-stage')) {
            const sStage = document.createElement('div');
            sStage.id = 'scp-zoom-stage';
            sStage.style.cssText = 'display:flex;flex-direction:column;width:100%;transform-origin:top center;';
            scpMainWrap.parentNode.insertBefore(sStage, scpMainWrap);
            sStage.appendChild(scpMainWrap);
        }
        const mcpRoot = document.getElementById('mcp-proportional-root');
        if (mcpRoot && scpMainWrap && !document.getElementById('mcp-zoom-stage')) {
            const mStage = document.createElement('div');
            mStage.id = 'mcp-zoom-stage';
            mStage.style.cssText = 'display:flex;flex-direction:column;width:100%;transform-origin:top center;';
            scpMainWrap.parentNode.insertBefore(mStage, scpMainWrap);
            mStage.appendChild(scpMainWrap);
        }
        const paRoot = document.getElementById('pa-proportional-root');
        const paFlt = paRoot ? paRoot.querySelector('.filters-wrapper') : null;
        const paSplit = paRoot ? paRoot.querySelector('.split-layout') : null;
        if (paRoot && paFlt && paSplit && !document.getElementById('pa-zoom-stage')) {
            const pStage = document.createElement('div');
            pStage.id = 'pa-zoom-stage';
            pStage.style.cssText = 'display:flex;flex-direction:column;flex:1;min-height:0;width:100%;transform-origin:top center;';
            paFlt.parentNode.insertBefore(pStage, paFlt);
            pStage.appendChild(paFlt);
            pStage.appendChild(paSplit);
        }
        const mbRoot = document.getElementById('mb-proportional-root');
        const mbHdr = mbRoot ? mbRoot.querySelector('.report-header') : null;
        if (mbRoot && mbHdr && !document.getElementById('mb-zoom-stage')) {
            const bStage = document.createElement('div');
            bStage.id = 'mb-zoom-stage';
            bStage.style.cssText = 'display:flex;flex-direction:column;width:100%;transform-origin:top center;';
            mbHdr.parentNode.insertBefore(bStage, mbHdr);
            while (bStage.nextSibling) bStage.appendChild(bStage.nextSibling);
        }
        const dmrRoot = document.getElementById('dmr-proportional-root');
        const dmrTopBar = document.getElementById('dmr-top-nav-bar');
        if (dmrRoot && dmrTopBar && !document.getElementById('dmr-zoom-stage')) {
            const dStage = document.createElement('div');
            dStage.id = 'dmr-zoom-stage';
            dStage.style.cssText = 'display:flex;flex-direction:column;gap:14px;width:100%;transform-origin:top center;';
            dmrTopBar.parentNode.insertBefore(dStage, dmrTopBar);
            while (dStage.nextSibling) dStage.appendChild(dStage.nextSibling);
        }

        // Universal Auto-Select on Focus + Inline Math Calculator (12*24) + Arabic Digit Normalization (0ms)
        const _arMap = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
        const evalInlineMathField = (el) => {
            if (!el || el.tagName !== 'INPUT' || el.readOnly || el.disabled) return;
            const idOrCls = (el.id + ' ' + el.className).toLowerCase();
            if (!/(amount|rate|price|qty|total|discount|tax|cost|paid|cash|bank|cheque)/.test(idOrCls) && el.inputMode !== 'decimal') return;
            const norm = String(el.value || '').replace(/[٠-٩۰-۹]/g, d => _arMap[d] || d).replace(/,/g, '').trim();
            if (/^[\d.+\-*/()\s]+$/.test(norm) && /[+\-*/]/.test(norm)) {
                try {
                    const res = Function('"use strict"; return (' + norm + ')')();
                    if (typeof res === 'number' && isFinite(res) && res >= 0) {
                        el.value = String(Math.round(res * 100) / 100);
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } catch (_) {}
            }
        };
        document.addEventListener('focusin', (ev) => {
            const t = ev.target;
            if (t && t.tagName === 'INPUT' && !t.readOnly && !['checkbox','radio','range','color','file','date'].includes(t.type)) {
                setTimeout(() => { try { if (document.activeElement === t) t.select(); } catch (_) {} }, 10);
            }
        }, true);
        document.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') evalInlineMathField(ev.target); }, true);
        document.addEventListener('blur', (ev) => evalInlineMathField(ev.target), true);

        document.body.classList.remove('els-compact-mode');
        try { localStorage.removeItem('els_sl_compact_mode'); } catch (_) {}

        // Populate Sovereign Command Strip (13 Themes + LIFO Back + Exit — without Compact Mode or 100% Zoom Capsule)
        const slot = document.getElementById('magaies-quartet-header-slot');
        const hasGlobalHdrStack = !!document.getElementById('magaiees-master-themes-btn');
        const curPage = (window.location.pathname.split('/').pop() || '').toLowerCase();
        const isMainDash = (curPage === '' || curPage === 'index.php' || curPage === 'magaiees_unified_dashboard.php');
        if (slot && !document.getElementById('sl-sovereign-law1-bar')) {
            const bar = document.createElement('div');
            bar.id = 'sl-sovereign-law1-bar';
            bar.style.cssText = 'display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;';
            const btnsHtml = `
                ${hasGlobalHdrStack ? '' : `<button type="button" id="sl-master-theme-btn" onclick="window.ElsajanaUniversalUI2027.openUniversalErpTonePopover(this)" style="height:35px;padding:0 12px;border-radius:9px;background:linear-gradient(135deg,#d97706,#b45309)!important;border:2px solid #fde047!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font-weight:900;font-size:0.84rem;cursor:pointer;">🎨 المظهر (13 لوناً)</button>`}
                ${(hasGlobalHdrStack || isMainDash) ? '' : `<button type="button" onclick="window.ElsajanaUniversalUI2027.sequentialGoBack()" title="رجوع متسلسل خطوة للخلف (LIFO)" style="height:34px;padding:0 11px;border-radius:9px;background:linear-gradient(135deg,#b45309,#78350f)!important;border:2px solid #fde047!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font-weight:900;font-size:0.82rem;cursor:pointer;">↩️ رجوع</button>`}`.trim();
            if (btnsHtml) {
                bar.innerHTML = btnsHtml;
                slot.prepend(bar);
            }
        }

        let _pinchDist = 0, _pinchStartZoom = 1;
        window.addEventListener('touchstart', (e) => {
            if (e.touches && e.touches.length === 2) {
                _pinchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
                _pinchStartZoom = _zoomLevel;
            }
        }, { passive: true });
        window.addEventListener('touchmove', (e) => {
            if (e.touches && e.touches.length === 2 && _pinchDist > 10) {
                e.preventDefault();
                const d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
                setTelescopicZoom(_pinchStartZoom * (d / _pinchDist));
            }
        }, { passive: false });

        // Live MutationObserver for Strict Daylight Guard on dynamic table rows & modals
        let _dlTimer = 0;
        if (typeof MutationObserver !== 'undefined') {
            new MutationObserver(() => {
                if (!document.documentElement.classList.contains('light-theme') || _dlTimer) return;
                _dlTimer = requestAnimationFrame(() => { _dlTimer = 0; runStrictDaylightGuardScan(); });
            }).observe(document.body, { childList: true, subtree: true });
        }
        runStrictDaylightGuardScan();
        applyScreenErgonomics();
    });

    // ── OPHTHALMIC & MULTI-DISPLAY ERGONOMICS ENGINE (Laptop / Desktop Wide / Portrait / Full) ──
    const ERGO_PROFILES = [
        { id: 'auto',            icon: '🛡️', name: 'الوضع القياسي الثابت (يدوي — افتراضي)', desc: 'مقاسات أصلية ثابتة 100% بدون أي تدخل أو تغيير تلقائي' },
        { id: 'laptop',          icon: '💻', name: 'مقاس اللابتوب المتوازن (1480px)', desc: 'تحديد يدوي لزاوية نظر مريحة على اللابتوب' },
        { id: 'desktop_comfort', icon: '🖥️', name: 'شاشة ديسكتوب عريضة (+ارتفاع رأسي)', desc: 'تحديد يدوي يمنع التمطيط العرضي على الشاشات العريضة' },
        { id: 'portrait_tall',   icon: '📱', name: 'شاشة طولية (Portrait Vertical)', desc: 'تحديد يدوي للشاشات الطولية الدوارة' },
        { id: 'ultrawide_full',  icon: '↔️', name: 'ملء العرض الكامل (100% عريض)', desc: 'يمتد يدوياً على كامل عرض الشاشة بدون قيود' }
    ];
    const pageErgoKey = () => 'magaies_ergo_page_' + (location.pathname.split('/').pop() || 'index.php');
    const detectHardwareMode = () => {
        const w = window.innerWidth || 1366, h = Math.max(window.innerHeight || 768, 320), ar = w / h;
        if (h > w * 1.02) return 'portrait_tall';
        if (w >= 1600 || ar >= 1.82) return 'desktop_comfort';
        return 'laptop';
    };
    const applyScreenErgonomics = (notify = false) => {
        const pgVal = localStorage.getItem(pageErgoKey()) || '';
        const glbVal = localStorage.getItem('magaies_ergo_global_mode') || 'auto';
        const vBoost = parseInt(localStorage.getItem('magaies_ergo_vboost_px') || '0', 10) || 0;
        const chosen = pgVal || glbVal || 'auto';
        const resolved = chosen === 'auto' ? 'auto_balanced' : chosen;
        const root = document.documentElement;
        root.setAttribute('data-ergo-mode', chosen);
        root.setAttribute('data-ergo-resolved', resolved);
        let maxW = 'calc(100% - 28px)', gridH = 'auto', vpad = vBoost + 'px';
        if (resolved === 'laptop') { maxW = 'min(calc(100% - 28px), 1480px)'; gridH = (52 + Math.round(vBoost / 4)) + 'dvh'; }
        else if (resolved === 'desktop_comfort') { maxW = 'min(calc(100% - 40px), calc(100dvh * 1.68), 1620px)'; gridH = (60 + Math.round(vBoost / 3)) + 'dvh'; vpad = (4 + vBoost) + 'px'; }
        else if (resolved === 'portrait_tall') { maxW = 'calc(100% - 18px)'; gridH = (72 + Math.round(vBoost / 3)) + 'dvh'; vpad = (3 + vBoost) + 'px'; }
        root.style.setProperty('--erp-ergo-max-w', maxW);
        root.style.setProperty('--erp-ergo-grid-min-h', gridH);
        root.style.setProperty('--erp-ergo-vpad', vpad);
        const lbl = document.getElementById('magaies-ergo-hdr-lbl');
        if (lbl) lbl.textContent = (pgVal ? '📌 ' : '') + (resolved === 'desktop_comfort' ? 'مكتبي مريح' : resolved === 'portrait_tall' ? 'طولي' : resolved === 'laptop' ? 'لابتوب' : resolved === 'ultrawide_full' ? 'عريض كامل' : 'مقاس الشاشة');
        if (typeof window.renderSettingsErgoStudio === 'function') window.renderSettingsErgoStudio();
        if (notify && typeof window.showToast === 'function') window.showToast('🖥️ تم ضبط هندسة الشاشة وراحة العين فوراً', 'success', 2200);
    };

    const setErgoProfile = (mode, scope = 'global') => {
        if (scope === 'page') { if (mode === 'clear_page') localStorage.removeItem(pageErgoKey()); else localStorage.setItem(pageErgoKey(), mode); }
        else { localStorage.setItem('magaies_ergo_global_mode', mode); localStorage.removeItem(pageErgoKey()); }
        applyScreenErgonomics(true);
    };
    const setErgoVerticalBoost = (px) => { localStorage.setItem('magaies_ergo_vboost_px', String(parseInt(px, 10) || 0)); applyScreenErgonomics(false); };
    const openErgoPopover = (anchorEl) => {
        let pop = document.getElementById('els-ergo-screen-popover');
        if (!pop) {
            pop = document.createElement('div'); pop.id = 'els-ergo-screen-popover'; document.body.appendChild(pop);
            document.addEventListener('click', (e) => { if (pop.style.display === 'block' && !pop.contains(e.target) && !(e.target.closest && e.target.closest('#magaiees-master-screen-ergo-btn'))) pop.style.display = 'none'; });
        }
        if (pop.style.display === 'block') { pop.style.display = 'none'; return; }
        const isDk = !document.documentElement.classList.contains('light-theme');
        const pgVal = localStorage.getItem(pageErgoKey()) || '', glbVal = localStorage.getItem('magaies_ergo_global_mode') || 'auto';
        const cur = pgVal || glbVal, scope = pgVal ? 'page' : 'global', vb = parseInt(localStorage.getItem('magaies_ergo_vboost_px') || '0', 10) || 0;
        const ink = isDk ? '#ffffff' : '#0F172A';
        pop.style.cssText = `position:fixed;z-index:2147483645;background:${isDk ? '#0f172a' : '#fffbf2'};border:2.5px solid #38bdf8;border-radius:14px;padding:11px;box-shadow:0 18px 42px rgba(0,0,0,0.65);width:315px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;color:${ink};`;
        pop.innerHTML = `<div style="font-weight:900;font-size:0.9rem;margin-bottom:7px;display:flex;justify-content:space-between;"><span>🖥️ هندسة الشاشات وراحة العين</span><span style="font-size:0.75rem;color:#0ea5e9;">${window.innerWidth}×${window.innerHeight}</span></div>
            <div style="display:flex;gap:5px;margin-bottom:8px;"><button type="button" id="ergo-sc-glb" style="flex:1;padding:5px;border-radius:8px;font-weight:900;font-size:0.76rem;cursor:pointer;border:2px solid ${scope==='global'?'#10b981':'#64748b'};background:${scope==='global'?'#059669':'transparent'};color:${scope==='global'?'#fff':ink};">🌐 للبرنامج كله</button><button type="button" id="ergo-sc-pg" style="flex:1;padding:5px;border-radius:8px;font-weight:900;font-size:0.76rem;cursor:pointer;border:2px solid ${scope==='page'?'#f59e0b':'#64748b'};background:${scope==='page'?'#d97706':'transparent'};color:${scope==='page'?'#fff':ink};">📌 لهذه الشاشة فقط</button></div>` +
            ERGO_PROFILES.map(p => `<button type="button" data-ep="${p.id}" style="width:100%;text-align:right;padding:7px 9px;margin-bottom:5px;border-radius:9px;cursor:pointer;background:${cur===p.id?(isDk?'#1e293b':'#fef3c7'):'transparent'};border:${cur===p.id?'2px solid #0ea5e9':'1px solid rgba(148,163,184,0.4)'};color:${ink}!important;font-weight:900;font-size:0.82rem;"><div>${p.icon} ${p.name} ${cur===p.id?'✓':''}</div><div style="font-size:0.71rem;opacity:0.8;font-weight:700;">${p.desc}</div></button>`).join('') +
            `<div style="margin-top:6px;padding-top:6px;border-top:1px dashed rgba(148,163,184,0.35);font-size:0.78rem;font-weight:900;">↕️ توسيع الارتفاع الرأسي للجداول: <span id="ergo-vb-lbl">+${vb}px</span><input type="range" min="0" max="24" step="2" value="${vb}" style="width:100%;margin-top:4px;" oninput="document.getElementById('ergo-vb-lbl').textContent='+'+this.value+'px';window.MagaiesScreenErgonomics.setVerticalBoost(this.value);"></div>`;
        let activeScope = scope;
        pop.querySelector('#ergo-sc-glb').onclick = () => { activeScope = 'global'; localStorage.removeItem(pageErgoKey()); applyScreenErgonomics(true); pop.style.display = 'none'; };
        pop.querySelector('#ergo-sc-pg').onclick = () => { activeScope = 'page'; localStorage.setItem(pageErgoKey(), cur); applyScreenErgonomics(true); pop.style.display = 'none'; };
        pop.querySelectorAll('[data-ep]').forEach(b => { b.onclick = () => { setErgoProfile(b.dataset.ep, activeScope); pop.style.display = 'none'; }; });
        const r = anchorEl ? anchorEl.getBoundingClientRect() : { bottom: 60, left: 20 };
        pop.style.top = Math.max(10, Math.min(window.innerHeight - 430, r.bottom + 8)) + 'px';
        pop.style.left = Math.max(12, Math.min(window.innerWidth - 330, r.left)) + 'px';
        pop.style.display = 'block';
    };
    applyScreenErgonomics();
    window.addEventListener('resize', () => applyScreenErgonomics(false));
    window.MagaiesScreenErgonomics = { ERGO_PROFILES, detectHardwareMode, applyScreenErgonomics, setProfile: setErgoProfile, setVerticalBoost: setErgoVerticalBoost, openPopover: openErgoPopover };

    // 7. Universal Clean UI Declutter across ALL screens (strips static explanatory badges & duplicate 13-color/exit pills while keeping real functional buttons)
    if (!document.getElementById('els-clean-ui-declutter-2027')) {
        const clSt = document.createElement('style');
        clSt.id = 'els-clean-ui-declutter-2027';
        clSt.textContent = `#grn-c11-cheque-radar-strip,#grn-c12-security-bar,#grn-c13-cyber-bar,#grn-c14-command-bar,#split-fc1-ai-cockpit-bar,#split-fc2-sovereign-row,#gl-c14-master-report-bar,#dmr-world-print-touch-top-btn,#grn-hub-export-print-bar,#scp-c11-bar,#scp-c12-bar,#scp-c13-bar,#scp-c14-bar,#mcp-c11-bar,#mcp-c12-bar,#mcp-c13-bar,#mcp-c14-bar,#mb-c14-bar,.constitution-explanatory-strip,.static-radar-pill,[onclick*="magaiesExitToMainDashboard"]{display:none!important;} #els-global-7charts-btn,#auto-charts-toggle-btn{display:inline-flex!important;}`;
        document.head.appendChild(clSt);
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('button, span.badge, div.pulse-badge').forEach(el => {
                const tx = (el.textContent || '').trim();
                if (/13\s*لون|دستور\s*\d/.test(tx) && el.id !== 'magaiees-master-themes-btn') {
                    if (el.tagName === 'BUTTON' && /13\s*لون/.test(tx)) el.textContent = '🎨 السمات';
                    else if (/دستور\s*\d/.test(tx)) el.style.display = 'none';
                }
            });
        });
    }

    // 8. Universal Live Master-Data 4-Pillar Sync (Warehouses, Customers, Suppliers, Items) across all open screens & reports
    const refreshLiveMasterDropdowns = (changedKey) => {
        try {
            if (!changedKey || changedKey === 'erp_warehouses_v1') {
                const whs = (JSON.parse(localStorage.getItem('erp_warehouses_v1') || '[]') || []).filter(w => w && !w.deleted && w.name);
                if (whs.length) {
                    document.querySelectorAll('select#hdr-warehouse, select#grn-warehouse, select#warehouse-filter, select[data-master-warehouses]').forEach(sel => {
                        const prev = sel.value;
                        const hasAll = Array.from(sel.options).some(o => o.value === '' || o.value === 'all');
                        sel.innerHTML = (hasAll ? '<option value="all">كل المخازن</option>' : '') + whs.map(w => `<option value="${w.id || w.name}">${w.name}</option>`).join('');
                        if (prev && Array.from(sel.options).some(o => String(o.value) === String(prev))) sel.value = prev;
                    });
                }
            }
            if (!changedKey || changedKey === 'erp_banks_v1') {
                const bnks = (JSON.parse(localStorage.getItem('erp_banks_v1') || '[]') || []).filter(b => b && !b.deleted && b.name);
                if (bnks.length) {
                    document.querySelectorAll('select#bank-select, select.pay-bank-select, select[data-master-banks]').forEach(sel => {
                        const prev = sel.value;
                        sel.innerHTML = bnks.map(b => `<option value="${b.id || b.name}">${b.name}</option>`).join('');
                        if (prev && Array.from(sel.options).some(o => String(o.value) === String(prev))) sel.value = prev;
                    });
                }
            }
            ['renderReport', 'loadReportData', 'refreshInventoryData', 'renderPendingInvoices', 'loadSplitInvoices', 'cycleExecutiveSummaryPeriod'].forEach(fn => { if (typeof window[fn] === 'function') { try { fn === 'cycleExecutiveSummaryPeriod' ? window[fn](window._execSumPeriodIdx || 0, true) : window[fn](); } catch (_) {} } });
            window.dispatchEvent(new CustomEvent('magaies:master-data-synced', { detail: { key: changedKey || 'all' } }));
        } catch (_) {}
    };
    window.addEventListener('storage', (ev) => {
        if (ev && ['erp_warehouses_v1', 'erp_customers_v1', 'erp_suppliers_v1', 'erp_items_v1', 'erp_safes_v1', 'erp_banks_v1'].includes(ev.key)) refreshLiveMasterDropdowns(ev.key);
    });
    if ('BroadcastChannel' in window) {
        try {
            const mdBc = new BroadcastChannel('erp_global_sync');
            mdBc.onmessage = (ev) => { if (ev && ev.data) refreshLiveMasterDropdowns(ev.data.key || ''); };
        } catch (_) {}
    }

    // 9. Negative-Stock Option (Goods-in-Transit) + Anti-Zero-Inflation & Credit-Limit Guard across Warehouses, Settings & Sales
    const getAllowNegStock = () => {
        try {
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}') || {};
            if (typeof cfg.allowNegativeStock === 'boolean') return cfg.allowNegativeStock;
        } catch (_) {}
        return localStorage.getItem('magaies_allow_negative_stock') !== '0';
    };
    const syncNegStockUi = () => {
        const allow = getAllowNegStock();
        ['md-wh-allow-neg-btn', 'sales-hdr-neg-stock-btn'].forEach(id => {
            const b = document.getElementById(id);
            if (!b) return;
            b.innerHTML = allow ? '🚚 البيع بالسالب (بضاعة بالطريق): مسموح ✅' : '⛔ البيع بالسالب: ممنوع (مقفول)';
            b.style.borderColor = allow ? '#10b981' : '#f43f5e';
            b.style.background = allow ? 'rgba(16,185,129,0.2)' : 'rgba(225,29,72,0.22)';
            b.style.color = allow ? '#6ee7b7' : '#fda4af';
        });
        const chk = document.getElementById('cfg-allow-negative-stock');
        if (chk) chk.checked = allow;
    };
    const toggleAllowNegativeStock = () => {
        const next = !getAllowNegStock();
        localStorage.setItem('magaies_allow_negative_stock', next ? '1' : '0');
        try {
            const cfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}') || {};
            cfg.allowNegativeStock = next;
            localStorage.setItem('erp_settings_v1', JSON.stringify(cfg));
        } catch (_) {}
        syncNegStockUi();
        if (typeof window.showToast === 'function') window.showToast(next ? '🚚 تم تفعيل السماح بالبيع بالسالب (لبضاعة الطريق)' : '⛔ تم إيقاف البيع بالسالب (لن يُصرف أكثر من الرصيد الفعلي)', next ? 'success' : 'warning', 2600);
    };
    document.addEventListener('DOMContentLoaded', () => {
        const pg = (location.pathname.split('/').pop() || '').toLowerCase();
        if (pg.includes('sales') && !document.getElementById('sales-hdr-neg-stock-btn')) {
            const whSel = document.getElementById('hdr-warehouse');
            if (whSel && whSel.parentElement) {
                const btn = document.createElement('button');
                btn.type = 'button'; btn.id = 'sales-hdr-neg-stock-btn';
                btn.style.cssText = 'height:32px;padding:0 10px;margin-right:6px;border-radius:8px;font-weight:900;font-size:0.76rem;cursor:pointer;border:2px solid #10b981;white-space:nowrap;';
                btn.onclick = toggleAllowNegativeStock;
                whSel.parentElement.appendChild(btn);
            }
        }
        syncNegStockUi();
    });
    // Hard Extra-Zeros Guard (>50 Billion ceiling on numeric inputs so phone/barcode paste never creates trillion-SDG typos)
    document.addEventListener('change', (e) => {
        const t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        const id = (t.id || t.name || '').toLowerCase();
        if (/phone|tel|mobile|whatsapp|barcode|code|iban|accountno|pin|ref/.test(id)) return;
        if (t.type === 'number' || /balance|amount|price|qty|limit|credit|debit/.test(id)) {
            const v = Math.abs(parseFloat(String(t.value || '').replace(/,/g, '')) || 0);
            if (v > 50000000000) {
                alert('⚠️ حارس الأصفار الزائدة: الرقم المدخل (' + v.toLocaleString('en-US') + ') ضخم بشكل غير منطقي (قد يكون رقم هاتف أو باركود بالخطأ). تم إيقافه لحماية الحسابات!');
                t.value = '0';
                t.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }, true);
    window.MagaiesInventoryGuard2027 = { isNegativeAllowed: getAllowNegStock, toggleAllowNegativeStock, syncNegStockUi };

    window.ElsajanaUniversalUI2027 = {
        TONES,
        applyUnifiedSystemTone,
        openUniversalErpTonePopover,
        runStrictDaylightGuardScan,
        syncLaserSidebarGeometry,
        setTelescopicZoom,
        stepZoom: (delta) => setTelescopicZoom(_zoomLevel + delta),
        toggleDualViewMode,
        sequentialGoBack,
        directExitToMain
    };
})();

// Magaies 2027: Monthly Day-1 Payroll Accrual + Unified Statement Engine + Per-Employee Screen Checkboxes
(function () {
    'use strict';
    const parseJ = (k, fb = []) => { try { return JSON.parse(localStorage.getItem(k) || JSON.stringify(fb)) || fb; } catch (_e) { return fb; } };

    function runMonthlyAccrual() {
        const ym = new Date().toISOString().slice(0, 7);
        const dayFirst = ym + '-01';
        let jvs = parseJ('erp_journal_vouchers_v1', []);
        let changed = false;
        ['erp_users_v1', 'erp_employees_v1'].forEach(storeKey => {
            const arr = parseJ(storeKey, []);
            let storeChanged = false;
            arr.forEach(emp => {
                if (!emp || emp.deleted || emp.status === 'inactive' || emp.status === 'موقوف') return;
                const sal = parseFloat(emp.salary || 0) || 0;
                if (sal <= 0 || !emp.id) return;
                const jvId = 'JV-SAL-' + ym.replace('-', '') + '-' + emp.id;
                if (!jvs.some(v => v && String(v.id) === jvId)) {
                    jvs.push({
                        id: jvId, refNo: jvId, date: dayFirst, currency: 'SDG', currencyRate: 1,
                        notes: 'استحقاق راتب شهري تلقائي (يوم 1) - ' + (emp.name || emp.fullName || emp.id) + ' (' + ym + ')',
                        rows: [
                            { accountId: 'exp-salaries', description: 'مصروف راتب شهري ' + ym, debit: sal, credit: 0 },
                            { accountId: String(emp.id), description: 'استحقاق راتب شهري ' + ym, debit: 0, credit: sal }
                        ]
                    });
                    changed = true;
                }
                const accruedMap = emp._accruedMonths && typeof emp._accruedMonths === 'object' ? emp._accruedMonths : {};
                if (!accruedMap[ym]) {
                    const baseBal = parseFloat(emp.currentBalance ?? emp.balance ?? emp.openingBalance ?? 0) || 0;
                    emp.currentBalance = baseBal - sal;
                    emp.balance = emp.currentBalance;
                    accruedMap[ym] = sal;
                    emp._accruedMonths = accruedMap;
                    storeChanged = true;
                }
            });
            if (storeChanged) localStorage.setItem(storeKey, JSON.stringify(arr));
        });
        if (changed) localStorage.setItem('erp_journal_vouchers_v1', JSON.stringify(jvs));
    }
    window.MagaiesPayrollAccrual2027 = { runMonthlyAccrual };
    setTimeout(runMonthlyAccrual, 400);

    window.MagaiesBuildUnifiedStatementRows = function (accountId, getAccountNameFn) {
        if (!accountId || accountId === 'all_accounts') return [];
        runMonthlyAccrual();
        const getName = typeof getAccountNameFn === 'function' ? getAccountNameFn : (x => x);
        const custs = parseJ('erp_customers_v1'), sups = parseJ('erp_suppliers_v1'), banks = parseJ('erp_banks_v1'), safes = parseJ('erp_safes_v1');
        const emps = [...parseJ('erp_users_v1'), ...parseJ('erp_employees_v1')];
        const cleanId = String(accountId).replace(/^(cust|supp|bank|safe|exp)-/i, '');
        const matchEnt = (arr) => arr.find(x => x && !x.deleted && (String(x.id) === String(accountId) || String(x.id) === cleanId));
        const cEnt = matchEnt(custs), sEnt = matchEnt(sups), bEnt = matchEnt(banks), sfEnt = matchEnt(safes), eEnt = matchEnt(emps);
        const ent = cEnt || sEnt || bEnt || sfEnt || eEnt;
        const accName = ent ? String(ent.name || ent.fullName || ent.bankName || ent.safeName || '').trim() : '';
        const isPartnerMatch = (idVal, nameVal) => {
            const s = String(idVal || '').trim(), cs = s.replace(/^(cust|supp|bank|safe|exp)-/i, '');
            if (s && (s === String(accountId) || s === cleanId || cs === cleanId)) return true;
            return !!(accName && nameVal && String(nameVal).trim() === accName);
        };

        const rawRows = [];
        const seenRefs = new Set();
        let opDr = 0, opCr = 0;
        if (ent) {
            const rawOp = parseFloat(ent.openingBalance ?? ent.opening_balance ?? 0) || 0;
            const dOp = parseFloat(ent.openingDebit || 0) || 0, cOp = parseFloat(ent.openingCredit || 0) || 0;
            if (sEnt) {
                const netSup = (cOp || dOp) ? (cOp - dOp) : rawOp;
                if (netSup >= 0) opCr = netSup; else opDr = Math.abs(netSup);
            } else {
                const netDr = (dOp || cOp) ? (dOp - cOp) : rawOp;
                if (netDr >= 0) opDr = netDr; else opCr = Math.abs(netDr);
            }
            if (opDr > 0 || opCr > 0) {
                rawRows.push({ sortTs: '0000-01-01', date: ent.date || ent.openingDate || '-', ref: 'OPEN-BAL', voucher: 'رصيد أول المدة', desc: 'رصيد افتتاحي مسجل', contraAccount: 'الأرصدة الافتتاحية', notes: ent.notes || '', costCenter: '-', dr: opDr, cr: opCr, curr: ent.currency || 'SDG', rate: parseFloat(ent.exRate || ent.exchangeRate || 1) || 1 });
            }
        }

        parseJ('erp_sales_invoices_v1').filter(i => i && !i.deleted && i.status !== 'preliminary').forEach(inv => {
            const rate = parseFloat(inv.exchangeRate || 1) || 1, cur = inv.currency || 'SDG';
            const gt = parseFloat(inv.grandTotal ?? inv.total ?? 0) || 0;
            const ref = String(inv.invoiceNo || inv.id || '');
            if (isPartnerMatch(inv.customer || inv.customerId, inv.customerName)) {
                seenRefs.add(ref);
                rawRows.push({ sortTs: (inv.date || '') + ' 01', date: inv.date || '-', ref, voucher: ref, desc: 'فاتورة مبيعات #' + ref, contraAccount: 'المبيعات', notes: inv.notes || '', costCenter: inv.costCenter || '-', dr: gt, cr: 0, curr: cur, rate });
                const paid = (parseFloat(inv.cashAmount || 0) || 0) + (Array.isArray(inv.bankTransfers) ? inv.bankTransfers.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0) : 0);
                if (paid > 0) rawRows.push({ sortTs: (inv.date || '') + ' 02', date: inv.date || '-', ref: ref + '-PAID', voucher: ref, desc: 'سداد مصاحب لفاتورة مبيعات #' + ref, contraAccount: 'الخزينة / البنك', notes: '', costCenter: '-', dr: 0, cr: paid, curr: cur, rate });
            }
            if ((bEnt || sfEnt) && parseFloat(inv.cashAmount || 0) > 0 && isPartnerMatch(inv.paymentAccountId, '')) {
                rawRows.push({ sortTs: (inv.date || '') + ' 02', date: inv.date || '-', ref: ref + '-CASH', voucher: ref, desc: 'تحصيل نقدي مبيعات #' + ref, contraAccount: inv.customerName || 'عميل', notes: '', costCenter: '-', dr: parseFloat(inv.cashAmount), cr: 0, curr: cur, rate });
            }
            if (bEnt && Array.isArray(inv.bankTransfers)) {
                inv.bankTransfers.forEach((bt, bi) => {
                    if (parseFloat(bt.amount || 0) > 0 && isPartnerMatch(bt.bankAccountId, '')) {
                        rawRows.push({ sortTs: (inv.date || '') + ' 02', date: inv.date || '-', ref: ref + '-BNK' + bi, voucher: bt.reference || ref, desc: 'تحويل بنكي مبيعات #' + ref, contraAccount: inv.customerName || 'عميل', notes: '', costCenter: '-', dr: parseFloat(bt.amount), cr: 0, curr: cur, rate });
                    }
                });
            }
        });

        parseJ('erp_purchase_invoices_v1').filter(i => i && !i.deleted && !i.isGRN && i.status !== 'draft' && i.saleType !== 'proforma').forEach(inv => {
            const rate = parseFloat(inv.exchangeRate || 1) || 1, cur = inv.currency || 'SDG';
            const gt = parseFloat(inv.localTotal ?? inv.grandTotal ?? inv.total ?? 0) || 0;
            const ref = String(inv.invoiceNo || inv.id || '');
            if (isPartnerMatch(inv.customer || inv.supplierId || inv.supplier, inv.supplierName)) {
                seenRefs.add(ref);
                rawRows.push({ sortTs: (inv.date || '') + ' 03', date: inv.date || '-', ref, voucher: ref, desc: 'فاتورة مشتريات #' + ref, contraAccount: 'المشتريات', notes: inv.notes || '', costCenter: inv.costCenter || '-', dr: 0, cr: gt, curr: cur, rate });
                const paid = (parseFloat(inv.cashAmount || 0) || 0) + (Array.isArray(inv.bankTransfers) ? inv.bankTransfers.reduce((a, b) => a + (parseFloat(b.amount) || 0), 0) : 0);
                if (paid > 0) {
                    const paidLoc = cur === 'SDG' ? paid : paid * rate;
                    rawRows.push({ sortTs: (inv.date || '') + ' 04', date: inv.date || '-', ref: ref + '-PAID', voucher: ref, desc: 'سداد مصاحب لفاتورة مشتريات #' + ref, contraAccount: 'الخزينة / البنك', notes: '', costCenter: '-', dr: paidLoc, cr: 0, curr: cur, rate });
                }
            }
        });

        parseJ('erp_single_receipts_v1').filter(v => v && !v.deleted).forEach(v => {
            const amt = parseFloat(v.amountLocal ?? v.amount ?? 0) || 0, ref = String(v.receiptNo || v.voucherNo || v.id || '');
            if (!amt || (v.refInvoice && seenRefs.has(String(v.refInvoice)))) return;
            if (isPartnerMatch(v.partnerId || v.fromAccount || v.customerId, v.partnerName)) {
                seenRefs.add(ref);
                rawRows.push({ sortTs: (v.date || '') + ' 05', date: v.date || '-', ref, voucher: ref, desc: v.notes || 'سند قبض نقدية / بنكي', contraAccount: getName(v.toAccount || v.accountId || 'الخزينة'), notes: v.reference || '', costCenter: v.costCenter || '-', dr: 0, cr: amt, curr: v.currency || 'SDG', rate: parseFloat(v.rate || 1) || 1 });
            } else if (isPartnerMatch(v.toAccount || v.accountId, '')) {
                rawRows.push({ sortTs: (v.date || '') + ' 05', date: v.date || '-', ref, voucher: ref, desc: v.notes || 'إيداع سند قبض', contraAccount: v.partnerName || getName(v.fromAccount || '-'), notes: '', costCenter: '-', dr: amt, cr: 0, curr: v.currency || 'SDG', rate: parseFloat(v.rate || 1) || 1 });
            }
        });

        parseJ('erp_single_payments_v1').filter(v => v && !v.deleted).forEach(v => {
            const amt = parseFloat(v.amountLocal ?? v.amount ?? 0) || 0, ref = String(v.paymentNo || v.voucherNo || v.id || '');
            if (!amt || (v.refInvoice && seenRefs.has(String(v.refInvoice)))) return;
            if (isPartnerMatch(v.partnerId || v.toAccount || v.supplierId, v.partnerName)) {
                seenRefs.add(ref);
                rawRows.push({ sortTs: (v.date || '') + ' 06', date: v.date || '-', ref, voucher: ref, desc: v.notes || 'سند صرف نقدية / بنكي', contraAccount: getName(v.fromAccount || v.accountId || 'الخزينة'), notes: v.reference || '', costCenter: v.costCenter || '-', dr: amt, cr: 0, curr: v.currency || 'SDG', rate: parseFloat(v.rate || 1) || 1 });
            } else if (isPartnerMatch(v.fromAccount || v.accountId, '')) {
                rawRows.push({ sortTs: (v.date || '') + ' 06', date: v.date || '-', ref, voucher: ref, desc: v.notes || 'صرف بموجب سند', contraAccount: v.partnerName || getName(v.toAccount || '-'), notes: '', costCenter: '-', dr: 0, cr: amt, curr: v.currency || 'SDG', rate: parseFloat(v.rate || 1) || 1 });
            }
        });

        parseJ('erp_journal_vouchers_v1').forEach(v => {
            if (!v || !Array.isArray(v.rows)) return;
            const ref = String(v.refNo || v.id || '');
            if (ref && seenRefs.has(ref)) return;
            const matchedRows = v.rows.filter(r => isPartnerMatch(r.accountId, ''));
            if (!matchedRows.length) return;
            const others = v.rows.filter(r => !isPartnerMatch(r.accountId, '')).map(r => getName(r.accountId)).join('، ').slice(0, 35) || '-';
            matchedRows.forEach(r => {
                rawRows.push({ sortTs: (v.date || '') + ' 07', date: v.date || '-', ref, voucher: v.id || '-', desc: r.description || v.notes || 'قيد يومية', contraAccount: others, notes: v.notes || '', costCenter: v.costCenter || '-', dr: parseFloat(r.debit) || 0, cr: parseFloat(r.credit) || 0, curr: v.currency || 'SDG', rate: parseFloat(v.currencyRate || 1) || 1 });
            });
        });

        rawRows.sort((a, b) => String(a.sortTs).localeCompare(String(b.sortTs)));
        let runBal = 0;
        const out = rawRows.map(r => {
            runBal += (r.dr - r.cr);
            let dFmt = r.date;
            if (dFmt && dFmt !== '-' && /^\d{4}-\d{2}-\d{2}/.test(dFmt)) {
                const dt = new Date(dFmt);
                if (!isNaN(dt)) dFmt = dt.toLocaleDateString('en-GB');
            }
            const rt = r.rate || 1;
            return { date: dFmt, ref: r.ref, voucher: r.voucher, desc: r.desc, contraAccount: r.contraAccount, notes: r.notes, costCenter: r.costCenter, dr: r.dr, cr: r.cr, bal: runBal, curr: r.curr, rate: rt, dr_fc: r.dr / rt, cr_fc: r.cr / rt, bal_fc: runBal / rt };
        });

        const strip = document.getElementById('ent-credit-limit-strip');
        if (strip) {
            if (ent && (cEnt || sEnt || eEnt)) {
                const lim = parseFloat(ent.creditLimit || 0) || 0;
                const opNet = opDr - opCr;
                const usedDebt = cEnt ? Math.max(0, runBal) : (sEnt ? Math.max(0, -runBal) : 0);
                const rem = lim > 0 ? (lim - usedDebt) : 0;
                const salTxt = eEnt && parseFloat(ent.salary || 0) > 0 ? ` | 💰 الراتب الشهري: ${Number(ent.salary).toLocaleString('en-US')} SDG` : '';
                const limTxt = lim > 0 ? ` | 💳 السقف الائتماني: ${lim.toLocaleString('en-US')} | ${rem >= 0 ? '✅ المتاح: ' + rem.toLocaleString('en-US') : '🚫 تجاوز السقف بـ: ' + Math.abs(rem).toLocaleString('en-US')}` : ' | 💳 السقف: مفتوح';
                strip.style.display = 'inline-block';
                strip.style.background = (lim > 0 && rem < 0) ? '#fef2f2' : '#eff6ff';
                strip.style.borderColor = (lim > 0 && rem < 0) ? '#ef4444' : '#93c5fd';
                strip.style.color = (lim > 0 && rem < 0) ? '#991b1b' : '#0f172a';
                strip.textContent = `🏁 الرصيد الافتتاحي: ${opNet.toLocaleString('en-US')} | 📊 الرصيد الختامي: ${runBal.toLocaleString('en-US')} SDG${eEnt ? salTxt : limTxt}`;
            } else {
                strip.style.display = 'none';
            }
        }
        return out;
    };

    window.openEmployeeScreenBoxesModal = function (empId) {
        const users = parseJ('erp_users_v1', []), emps = parseJ('erp_employees_v1', []);
        const emp = users.find(u => u && String(u.id) === String(empId)) || emps.find(u => u && String(u.id) === String(empId));
        if (!emp) return;
        const old = document.getElementById('emp-screen-boxes-modal'); if (old) old.remove();
        const mp = window.MagaieesPermissions || {};
        const cats = Array.isArray(mp.SYSTEM_SCREENS_CATALOG) ? mp.SYSTEM_SCREENS_CATALOG : [];
        const repCats = Array.isArray(mp.SYSTEM_REPORTS_CATALOG) ? mp.SYSTEM_REPORTS_CATALOG : [];
        const dashComps = Array.isArray(mp.DASHBOARD_COMPONENTS) ? mp.DASHBOARD_COMPONENTS : [];
        const actLabels = { new: '➕ جديد', save: '💾 حفظ', edit: '✏️ تعديل', delete: '🗑️ حذف', print: '🖨️ طباعة', share: '📤 مشاركة', export: '📥 تصدير', preview: '🔎 معاينة', receive: '📦 استلام', return: '↩️ مرتجع', approve: '✅ اعتماد' };
        const alwScr = emp.allowedScreens || {}, scrBtns = emp.screenButtons || {}, voScr = emp.viewOnlyScreens || {}, alwRep = emp.allowedReports || {}, dshPerm = emp.dashboardPermissions || {};
        const fl = emp.financialLimits || {};
        const cp = (emp.customPermissions && typeof emp.customPermissions === 'object') ? emp.customPermissions : {};
        let seq = 0, cardsHtml = `<div style="margin-bottom:16px;padding:12px 14px;border-radius:12px;background:rgba(245,158,11,0.1);border:1.5px solid rgba(245,158,11,0.45);">
            <div style="font-weight:900;color:#fbbf24;margin-bottom:8px;font-size:0.9rem;">💰 سقوف الخصم والمبالغ المسموحة لهذا الموظف (يُطلب كلمة سر المالك عند التجاوز)</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(195px,1fr));gap:10px;">
                <div><label style="font-size:0.77rem;color:#cbd5e1;font-weight:800;">أقصى نسبة خصم (%)</label><input id="emp-fl-disc-pct" type="number" step="0.1" value="${fl.maxDiscountPercent ?? ''}" placeholder="مثال: 5" style="width:100%;margin-top:3px;padding:6px 9px;border-radius:8px;border:1px solid #f59e0b;background:#020617;color:#fff;font-weight:900;"></div>
                <div><label style="font-size:0.77rem;color:#cbd5e1;font-weight:800;">أقصى مبلغ خصم (SDG)</label><input id="emp-fl-disc-amt" type="number" step="any" value="${fl.maxDiscountAmount ?? ''}" placeholder="مثال: 50000" style="width:100%;margin-top:3px;padding:6px 9px;border-radius:8px;border:1px solid #f59e0b;background:#020617;color:#fff;font-weight:900;"></div>
                <div><label style="font-size:0.77rem;color:#cbd5e1;font-weight:800;">أقصى مبلغ سند صرف (SDG)</label><input id="emp-fl-vch-amt" type="number" step="any" value="${fl.maxVoucherAmount ?? ''}" placeholder="مثال: 500000" style="width:100%;margin-top:3px;padding:6px 9px;border-radius:8px;border:1px solid #f59e0b;background:#020617;color:#fff;font-weight:900;"></div>
                <div><label style="font-size:0.77rem;color:#cbd5e1;font-weight:800;">أقصى مبلغ فاتورة (SDG)</label><input id="emp-fl-inv-amt" type="number" step="any" value="${fl.maxInvoiceAmount ?? ''}" placeholder="مفتوح إن تُرك فارغاً" style="width:100%;margin-top:3px;padding:6px 9px;border-radius:8px;border:1px solid #f59e0b;background:#020617;color:#fff;font-weight:900;"></div>
            </div>
        </div>`;

        cats.forEach(cat => {
            const items = (cat.screens || []).map(sc => {
                seq++;
                const f = sc.file, acts = (sc.supportedActions && sc.supportedActions.length) ? sc.supportedActions : ['new', 'save', 'edit', 'delete', 'print', 'preview'];
                const allowed = alwScr[f] !== undefined ? !!alwScr[f] : (cp.screens && cp.screens[sc.id] ? cp.screens[sc.id].access !== false : true);
                const btnMap = scrBtns[f] || (cp.screens && cp.screens[sc.id] && cp.screens[sc.id].actions) || {};
                const isVO = !!voScr[f] || (allowed && acts.filter(a => a !== 'preview').every(a => btnMap[a] === false));
                const actHtml = acts.map(a => {
                    const aOn = isVO ? (a === 'preview') : (btnMap[a] !== undefined ? !!btnMap[a] : true);
                    return `<label style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;background:rgba(2,6,23,0.65);border:1px solid rgba(148,163,184,0.28);font-size:0.76rem;cursor:pointer;color:#e2e8f0;"><input type="checkbox" class="emp-act-chk" data-file="${f}" data-act="${a}" ${aOn ? 'checked' : ''} style="accent-color:#38bdf8;">${actLabels[a] || a}</label>`;
                }).join('');
                return `<div class="emp-scr-card" data-search="${(sc.title + ' ' + f + ' ' + cat.categoryName).toLowerCase()}" style="padding:10px 12px;border-radius:11px;background:rgba(15,23,42,0.85);border:1.5px solid ${allowed ? (isVO ? '#f59e0b' : 'rgba(16,185,129,0.5)') : 'rgba(239,68,68,0.45)'};display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:900;font-size:0.86rem;color:#fff;"><input type="checkbox" class="emp-scr-chk" data-kind="screen" data-id="${sc.id}" data-file="${f}" ${allowed ? 'checked' : ''} style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;"><span>${seq}. ${sc.title}</span></label>
                        <label title="يدخل الشاشة للرؤية والنظر بعينه فقط مع قفل الحفظ والتعديل والحذف والطباعة" style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:rgba(245,158,11,0.16);border:1px solid #f59e0b;color:#fde047;font-size:0.75rem;font-weight:900;cursor:pointer;"><input type="checkbox" class="emp-vo-chk" data-file="${f}" ${isVO ? 'checked' : ''} style="accent-color:#f59e0b;">👁️ الرؤية والنظر فقط</label>
                    </div>
                    <div class="emp-acts-row" data-file="${f}" style="display:${allowed ? 'flex' : 'none'};flex-wrap:wrap;gap:6px;padding-top:6px;border-top:1px dashed rgba(255,255,255,0.1);">${actHtml}</div>
                </div>`;
            }).join('');
            if (items) cardsHtml += `<div class="emp-cat-blk" style="margin-bottom:16px;"><div style="font-weight:900;color:#38bdf8;margin-bottom:8px;font-size:0.92rem;">📂 ${cat.categoryName}</div><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:9px;">${items}</div></div>`;
        });
        if (dashComps.length) {
            const dItems = dashComps.map(dc => {
                const allowed = dshPerm[dc.id] !== undefined ? !!dshPerm[dc.id] : true;
                return `<label class="emp-scr-card" data-search="${(dc.title + ' مؤشر السيولة').toLowerCase()}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:9px;background:rgba(15,23,42,0.8);border:1px solid ${allowed ? 'rgba(16,185,129,0.45)' : 'rgba(239,68,68,0.4)'};cursor:pointer;font-weight:800;font-size:0.82rem;color:#f8fafc;"><input type="checkbox" class="emp-scr-chk" data-kind="dash" data-id="${dc.id}" ${allowed ? 'checked' : ''} style="width:17px;height:17px;accent-color:#10b981;"><span>🟢 ${dc.title}</span></label>`;
            }).join('');
            cardsHtml += `<div class="emp-cat-blk" style="margin-bottom:14px;"><div style="font-weight:900;color:#34d399;margin-bottom:7px;font-size:0.9rem;">🛡️ مؤشرات لوحة القيادة (بما فيها مؤشر السيولة المطمئنة)</div><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;">${dItems}</div></div>`;
        }
        if (repCats.length) {
            const rItems = repCats.flatMap(rc => rc.reports || [rc]).filter(r => r && r.id).map(rp => {
                const allowed = alwRep[rp.id] !== undefined ? !!alwRep[rp.id] : (cp.reports && cp.reports[rp.id] !== undefined ? !!cp.reports[rp.id] : true);
                return `<label class="emp-scr-card" data-search="${(rp.title || rp.name || rp.id).toLowerCase()}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:9px;background:rgba(15,23,42,0.8);border:1px solid ${allowed ? 'rgba(56,189,248,0.45)' : 'rgba(239,68,68,0.4)'};cursor:pointer;font-weight:800;font-size:0.82rem;color:#f8fafc;"><input type="checkbox" class="emp-scr-chk" data-kind="report" data-id="${rp.id}" data-file="${rp.file || rp.url || ''}" ${allowed ? 'checked' : ''} style="width:17px;height:17px;accent-color:#0ea5e9;"><span>📊 ${rp.title || rp.name || rp.id}</span></label>`;
            }).join('');
            if (rItems) cardsHtml += `<div class="emp-cat-blk" style="margin-bottom:14px;"><div style="font-weight:900;color:#fbbf24;margin-bottom:7px;font-size:0.9rem;">📑 التقارير والكشوفات الرقابية</div><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;">${rItems}</div></div>`;
        }

        const wrap = document.createElement('div');
        wrap.id = 'emp-screen-boxes-modal';
        wrap.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,0.88);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;';
        wrap.innerHTML = `<div style="background:#0f172a;border:2px solid #38bdf8;border-radius:18px;width:100%;max-width:1080px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.75);">
            <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,0.12);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <h3 style="margin:0;color:#fff;font-size:1.12rem;font-weight:900;">🛡️ مصفوفة الأمن السيبراني وصلاحيات الشاشات للموظف: <span style="color:#34d399;">${emp.name || emp.fullName || emp.username}</span></h3>
                    <div style="font-size:0.77rem;color:#94a3b8;margin-top:3px;">حدد دخول كل شاشة، واختر (👁️ الرؤية والنظر فقط) أو خصص أزرار (جديد • حفظ • تعديل • حذف • طباعة) لكل شاشة على حدة</div>
                </div>
                <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap;">
                    <input id="emp-scr-filter" type="text" placeholder="🔍 ابحث باسم الشاشة أو التقرير..." style="padding:6px 11px;border-radius:8px;border:1px solid #38bdf8;background:#020617;color:#fff;font-weight:800;font-size:0.8rem;width:210px;">
                    <button type="button" id="emp-scr-all-on" style="padding:6px 11px;border-radius:8px;background:rgba(16,185,129,0.2);color:#6ee7b7;border:1px solid #10b981;font-weight:900;cursor:pointer;">✅ فتح الكل</button>
                    <button type="button" id="emp-scr-all-vo" style="padding:6px 11px;border-radius:8px;background:rgba(245,158,11,0.2);color:#fde047;border:1px solid #f59e0b;font-weight:900;cursor:pointer;">👁️ الكل نظر فقط</button>
                    <button type="button" id="emp-scr-all-off" style="padding:6px 11px;border-radius:8px;background:rgba(239,68,68,0.2);color:#fca5a5;border:1px solid #ef4444;font-weight:900;cursor:pointer;">⛔ قفل الكل</button>
                </div>
            </div>
            <div style="padding:16px 20px;overflow-y:auto;flex:1;">${cardsHtml || '<div style="color:#94a3b8;">انتقل إلى استوديو الصلاحيات لتحميل القائمة الكاملة.</div>'}</div>
            <div style="padding:12px 20px;border-top:1px solid rgba(255,255,255,0.1);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="emp-scr-cancel" style="padding:8px 18px;border-radius:10px;background:rgba(255,255,255,0.08);color:#cbd5e1;border:1px solid rgba(255,255,255,0.15);font-weight:800;cursor:pointer;">إلغاء</button>
                <button type="button" id="emp-scr-save" style="padding:8px 24px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:2px solid #6ee7b7;font-weight:900;cursor:pointer;">💾 حفظ وتطبيق الصلاحيات فوراً</button>
            </div>
        </div>`;
        document.body.appendChild(wrap);
        wrap.querySelector('#emp-scr-cancel').onclick = () => wrap.remove();
        wrap.querySelector('#emp-scr-filter').oninput = (e) => {
            const q = e.target.value.trim().toLowerCase();
            wrap.querySelectorAll('.emp-scr-card').forEach(el => { el.style.display = (!q || (el.dataset.search || '').includes(q)) ? '' : 'none'; });
        };
        wrap.querySelectorAll('.emp-scr-chk[data-kind="screen"]').forEach(chk => {
            chk.onchange = () => {
                const row = wrap.querySelector(`.emp-acts-row[data-file="${chk.dataset.file}"]`);
                if (row) row.style.display = chk.checked ? 'flex' : 'none';
            };
        });
        wrap.querySelectorAll('.emp-vo-chk').forEach(vo => {
            vo.onchange = () => {
                const f = vo.dataset.file, sChk = wrap.querySelector(`.emp-scr-chk[data-file="${f}"]`);
                if (vo.checked && sChk) { sChk.checked = true; sChk.onchange(); }
                wrap.querySelectorAll(`.emp-act-chk[data-file="${f}"]`).forEach(ac => { ac.checked = vo.checked ? (ac.dataset.act === 'preview') : true; });
            };
        });
        wrap.querySelectorAll('.emp-act-chk').forEach(ac => {
            ac.onchange = () => {
                if (ac.checked && ac.dataset.act !== 'preview') {
                    const vo = wrap.querySelector(`.emp-vo-chk[data-file="${ac.dataset.file}"]`);
                    if (vo) vo.checked = false;
                }
            };
        });
        wrap.querySelector('#emp-scr-all-on').onclick = () => { wrap.querySelectorAll('.emp-scr-chk, .emp-act-chk').forEach(c => { c.checked = true; }); wrap.querySelectorAll('.emp-vo-chk').forEach(v => { v.checked = false; }); wrap.querySelectorAll('.emp-acts-row').forEach(r => { r.style.display = 'flex'; }); };
        wrap.querySelector('#emp-scr-all-vo').onclick = () => { wrap.querySelectorAll('.emp-scr-chk').forEach(c => { c.checked = true; }); wrap.querySelectorAll('.emp-vo-chk').forEach(v => { v.checked = true; v.onchange(); }); };
        wrap.querySelector('#emp-scr-all-off').onclick = () => { wrap.querySelectorAll('.emp-scr-chk, .emp-act-chk, .emp-vo-chk').forEach(c => { c.checked = false; }); wrap.querySelectorAll('.emp-acts-row').forEach(r => { r.style.display = 'none'; }); };
        wrap.querySelector('#emp-scr-save').onclick = () => {
            const nAllowed = Object.assign({}, alwScr), nBtns = Object.assign({}, scrBtns), nVO = {}, nReps = Object.assign({}, alwRep), nDash = Object.assign({}, dshPerm), nCpScr = {};
            wrap.querySelectorAll('.emp-scr-chk').forEach(chk => {
                const kind = chk.dataset.kind, id = chk.dataset.id, f = chk.dataset.file;
                if (kind === 'report') { nReps[id] = chk.checked; if (f) nAllowed[f] = chk.checked; }
                else if (kind === 'dash') { nDash[id] = chk.checked; }
                else if (f) {
                    nAllowed[f] = chk.checked;
                    const vo = wrap.querySelector(`.emp-vo-chk[data-file="${f}"]`);
                    nVO[f] = !!(chk.checked && vo && vo.checked);
                    const acts = {};
                    wrap.querySelectorAll(`.emp-act-chk[data-file="${f}"]`).forEach(ac => { acts[ac.dataset.act] = nVO[f] ? (ac.dataset.act === 'preview') : ac.checked; });
                    nBtns[f] = acts;
                    nCpScr[id] = { access: chk.checked, viewOnly: nVO[f], actions: acts };
                }
            });
            const vNum = id => { const v = wrap.querySelector('#' + id)?.value; return (v !== undefined && v !== '') ? parseFloat(v) : ''; };
            const nLimits = Object.assign({}, emp.financialLimits || {}, {
                maxDiscountPercent: vNum('emp-fl-disc-pct'), maxDiscountAmount: vNum('emp-fl-disc-amt'),
                maxVoucherAmount: vNum('emp-fl-vch-amt'), maxInvoiceAmount: vNum('emp-fl-inv-amt')
            });
            const applyToUser = (uObj) => {
                uObj.allowedScreens = nAllowed; uObj.screenButtons = nBtns; uObj.viewOnlyScreens = nVO;
                uObj.allowedReports = nReps; uObj.dashboardPermissions = nDash; uObj.financialLimits = nLimits;
                uObj.customPermissions = { enabled: true, useCustomMatrix: true, screens: nCpScr, reports: nReps };
                uObj.useCustomPermissions = true;
                if (uObj.role === 'admin' && Object.values(nAllowed).some(v => v === false)) uObj.role = 'accountant';
            };
            ['erp_users_v1', 'erp_employees_v1'].forEach(k => {
                const list = parseJ(k, []), idx = list.findIndex(x => x && String(x.id) === String(empId));
                if (idx > -1) { applyToUser(list[idx]); localStorage.setItem(k, JSON.stringify(list)); if (k === 'erp_users_v1' && typeof window.syncToBackend === 'function') window.syncToBackend('save_employee', list[idx]); }
            });
            const curU = parseJ('user', null);
            if (curU && String(curU.id) === String(empId)) { applyToUser(curU); localStorage.setItem('user', JSON.stringify(curU)); }
            if (mp.broadcastSecurityUpdate) mp.broadcastSecurityUpdate(empId);
            wrap.remove();
            if (typeof window.showToast === 'function') window.showToast('✅ تم اعتماد مصفوفة الشاشات والصلاحيات للموظف "' + (emp.name || empId) + '" فوراً', 'success', 3200);
        };
    };
})();
