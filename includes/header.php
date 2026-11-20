<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) {
    @ob_start('ob_gzhandler');
}
if (!defined('ALLOW_OFFLINE_DB_FALLBACK')) {
    define('ALLOW_OFFLINE_DB_FALLBACK', true);
}
if (!isset($systemUsdRate) || (float)$systemUsdRate <= 1) {
    $systemUsdRate = 5999.0;
    try {
        if (!function_exists('getSystemUsdExchangeRate') && file_exists(__DIR__ . '/../db_bridge.php')) {
            require_once __DIR__ . '/../db_bridge.php';
        }
        if (function_exists('getSystemUsdExchangeRate')) {
            $systemUsdRate = getSystemUsdExchangeRate(isset($pdo) ? $pdo : null);
        } elseif (isset($pdo) && $pdo) {
            $stmtHdrRate = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate' LIMIT 1");
            $valHdrRate = $stmtHdrRate ? $stmtHdrRate->fetchColumn() : null;
            if ($valHdrRate) {
                if (is_numeric($valHdrRate) && (float)$valHdrRate > 1) {
                    $systemUsdRate = (float)$valHdrRate;
                } else {
                    $decHdrRate = json_decode($valHdrRate, true);
                    if (is_array($decHdrRate)) {
                        $rHdr = (float)($decHdrRate['rate'] ?? ($decHdrRate['current'] ?? ($decHdrRate['rateUSD'] ?? 0)));
                        if ($rHdr > 1) $systemUsdRate = $rHdr;
                    }
                }
            }
        }
    } catch (Throwable $e) {}
}
?>
<script>
window.SYSTEM_USD_RATE = <?= json_encode((float)$systemUsdRate) ?>;
window._cachedGlobalRate = window.SYSTEM_USD_RATE;
(function syncDatabaseUsdRateEverywhere() {
    function applyDbUsdRate(rateVal) {
        var r = parseFloat(rateVal);
        if (!r || r <= 1) return;
        window.SYSTEM_USD_RATE = r;
        window._cachedGlobalRate = r;
        try {
            localStorage.setItem('erp_rate_usd', String(r));
            localStorage.setItem('magaiees_system_usd_rate', String(r));
            localStorage.setItem('magaies_custom_usd_rate', String(r));
            localStorage.setItem('magaies_usd_rate', String(r));
            localStorage.setItem('erp_cached_global_rate', String(r));
            localStorage.setItem('erp_saas_rate_usd', String(r));
            localStorage.setItem('erp_manual_rate_usd', String(r));
            var cr = {};
            try { cr = JSON.parse(localStorage.getItem('erp_currency_rates_v1') || '{}') || {}; } catch(e) { cr = {}; }
            cr.USD = r;
            cr.SDG = 1;
            localStorage.setItem('erp_currency_rates_v1', JSON.stringify(cr));
            ['erp_settings_v1'].forEach(function(baseKey) {
                var keys = [baseKey];
                try {
                    var u = JSON.parse(localStorage.getItem('user') || '{}');
                    var tId = u.tenant_id || localStorage.getItem('currentTenantId') || 'default';
                    keys.push('tenant_' + tId + '_' + baseKey);
                } catch(e) {}
                keys.forEach(function(k) {
                    try {
                        var cfg = JSON.parse(localStorage.getItem(k) || '{}') || {};
                        cfg.rateUSD = r;
                        cfg.exchangeRate = r;
                        localStorage.setItem(k, JSON.stringify(cfg));
                    } catch(e) {}
                });
            });
        } catch(e) {}
        var updateDomInputs = function() {
            var gVal = document.getElementById('global-exchange-rate-val');
            var gBox = document.getElementById('global-exchange-rate-box');
            if (gVal) gVal.innerText = r.toLocaleString('en-US', {maximumFractionDigits: 2});
            if (gBox) gBox.style.display = 'block';
            ['hdr-exchange-rate', 'exchange-rate'].forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                var curSel = document.getElementById('hdr-currency') || document.getElementById('currency');
                var cur = curSel ? curSel.value : 'SDG';
                if (cur === 'SDG' || cur === 'USD' || !cur) {
                    var curVal = parseFloat(String(el.value || '').replace(/,/g, '')) || 0;
                    if (curVal <= 1 || curVal === 3000 || curVal === 2850 || curVal === 2600 || !el.dataset.userEdited) {
                        el.value = r;
                        el.dataset.raw = r;
                    }
                }
            });
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateDomInputs, { once: true });
        } else {
            updateDomInputs();
        }
    }
    applyDbUsdRate(window.SYSTEM_USD_RATE);
    if (!(window.SYSTEM_USD_RATE > 1)) {
        try {
            fetch('api/mirror_sync.php?action=get_currency_rate&_t=' + Date.now())
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.success && parseFloat(data.rate) > 1) {
                        applyDbUsdRate(parseFloat(data.rate));
                    }
                }).catch(function() {});
        } catch(e) {}
    }
})();
</script>
<style id="global-zero-lag-css">
.ambient-bg, .ambient-bg .orb, .preset-ribbon-btn::after, .preset-btn::after, .btn-action-hero::after, .btn-run::after, .action-tab.active::before, .sticky-footer::before { display: none !important; content: none !important; }
*:not(#market-broker-ticker-track):not(#market-broker-orders-track):not(#global-screens-broker-ticker-track):not(#athkar-marquee-track):not(.quran-seamless-track), *::before, *::after { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; animation: none !important; }
button, .btn, .modern-btn, .preset-ribbon-btn, .preset-btn, .btn-action-hero, .btn-run, .action-tab, .fin-card, .pos-grid tbody tr { transition: background-color 0.08s ease, border-color 0.08s ease, opacity 0.08s ease !important; }
button:hover, .btn:hover, .modern-btn:hover, .preset-ribbon-btn:hover, .preset-btn:hover, .btn-action-hero:hover, .del-btn:hover, .modal-close:hover { transform: none !important; }
</style>
<style id="master-2027-height-and-colprops">
/* ═══ MASTER 2027 UI HARMONY: +20% HEIGHT ONLY, UNIFIED COL PROPS (FAR LEFT), CENTERED NO-ICON HUBS & ZERO BLUR HALOS ═══ */
#sales-editor-constitution-1of3-bar, #split-constitution-1of3-bar, #sales-hub-constitution-1of3-bar,
#sl-sovereign-law1-bar, #pur-hub-sovereign-tools, #split-fc2-sovereign-row,
.ambient-bg, .ambient-bg .orb, .orb, .pos-lemon-title::before,
#payment-panel::before, #payment-panel::after, .payment-details-card::before, .payment-details-card::after,
#pay-credit-section::before, #pay-credit-section::after {
    display: none !important;
    background: none !important;
    filter: none !important;
    animation: none !important;
}

/* Remove graphic icons & center text in Purchase / Sales / Receipt Gate Hub Cards */
.hub-action-card .icon,
#purchase-hub-view .hub-action-card .icon,
#sales-hub-view .hub-action-card .icon {
    display: none !important;
}
.hub-action-card,
#purchase-hub-view .hub-action-card,
#sales-hub-view .hub-action-card {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    min-height: 60px !important;
    padding: 12px 16px !important;
}
.hub-action-card .text-content,
.hub-action-card .title {
    width: 100% !important;
    text-align: center !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 !important;
}

/* Unified Column Properties Button (خصائص الأعمدة — بنفس لون دفتر الأستاذ وفي أقصى اليسار) */
.unified-col-props-btn,
#btn-gl-columns-cfg,
button[onclick*="openGridSettingsModal"],
button[onclick*="openGridSettings("],
button[onclick*="openColumnContextMenu"] {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    height: 44px !important;
    min-height: 44px !important;
    padding: 0 18px !important;
    border-radius: 10px !important;
    background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important;
    border: 1.8px solid #38bdf8 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-family: 'Cairo', 'Tajawal', sans-serif !important;
    font-size: 0.92rem !important;
    font-weight: 900 !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25) !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
}
.unified-col-props-far-left {
    margin-right: auto !important;
    margin-left: 0 !important;
}

/* +20% HEIGHT ONLY (الارتفاع فقط بنسبة +20% بدون زيادة العرض — لتناسب الشاشات الطولية وكافة شاشات وتقارير النظام) */
.filter-input,
.ctrl-input:not(textarea),
.v-input:not(textarea),
.sl-input:not(textarea),
.report-header select,
.report-header input:not([type="checkbox"]):not([type="radio"]) {
    min-height: 46px !important;
}
.preset-ribbon-btn,
.preset-pill-btn,
.ribbon-btn,
.luxury-action-btn,
.sl-btn {
    min-height: 43px !important;
}
table.pos-grid thead th,
#balances-table thead th,
#recent-table thead th,
.report-table thead th,
.data-table thead th {
    min-height: 46px !important;
    padding-top: 12px !important;
    padding-bottom: 12px !important;
}
table.pos-grid tbody td,
#balances-table tbody td,
#recent-table tbody td,
.report-table tbody td,
.data-table tbody td {
    min-height: 46px !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}
table.pos-grid tbody td input:not([type="checkbox"]):not([type="radio"]),
table.pos-grid tbody td select {
    min-height: 43px !important;
}
/* Extra vertical harmony on Portrait / Vertical Desktop Monitors (الشاشات الطولية) */
@media screen and (min-width: 769px) and (orientation: portrait), screen and (min-width: 769px) and (min-height: 1050px) {
    #purchase-row0-bar, #sales-row0-bar { min-height: 54px !important; }
    #invoice-info-box { min-height: 52px !important; max-height: 56px !important; }
    #invoice-info-box .group-invoice, #invoice-info-box .pro-serial-container { height: 46px !important; min-height: 46px !important; max-height: 46px !important; }
    .filter-input, .ctrl-input:not(textarea), .v-input:not(textarea), .sl-input:not(textarea) { min-height: 50px !important; }
    table.pos-grid tbody td, #balances-table tbody td, #recent-table tbody td, .report-table tbody td { padding-top: 12px !important; padding-bottom: 12px !important; }
}
</style>
<script defer src="js/bban_auto_helper.js"></script>
<script defer src="js/master-banks-helper.js?v=20260924_v1"></script>
<script defer src="js/permissions-manager.js?v=<?= file_exists(__DIR__ . '/../js/permissions-manager.js') ? filemtime(__DIR__ . '/../js/permissions-manager.js') : '20261016_v28' ?>"></script>
<script defer src="js/audit-tracker.js?v=20260927"></script>
<script>
    // ═══════════════════════════════════════════════════════════════════
    // 🛡️ الحماية المطلقة الشاملة لمنع ظهور رسالة المتصفح (Leave Site) نهائياً
    // ═══════════════════════════════════════════════════════════════════
    (function() {
        const cleanBeforeUnload = function(e) {
            if (!e) return;
            try {
                delete e.returnValue;
                e.returnValue = undefined;
            } catch(err) {}
        };

        try {
            const origAEL = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                if (type === 'beforeunload') {
                    const safeListener = function(e) {
                        try {
                            if (typeof listener === 'function') listener.call(this, e);
                            else if (listener && typeof listener.handleEvent === 'function') listener.handleEvent(e);
                        } catch(err) {}
                        cleanBeforeUnload(e);
                    };
                    return origAEL.call(this, type, safeListener, options);
                }
                return origAEL.call(this, type, listener, options);
            };
        } catch(e) {}

        try {
            Object.defineProperty(window, 'onbeforeunload', {
                get: function() { return null; },
                set: function() {},
                configurable: false
            });
        } catch(e) {}

        try {
            if (window.BeforeUnloadEvent && window.BeforeUnloadEvent.prototype) {
                window.BeforeUnloadEvent.prototype.preventDefault = function() {};
                Object.defineProperty(window.BeforeUnloadEvent.prototype, 'returnValue', {
                    get: function() { return undefined; },
                    set: function() {},
                    configurable: true
                });
            }
        } catch(e) {}

        window.addEventListener('beforeunload', function(e) {
            e.stopImmediatePropagation();
            cleanBeforeUnload(e);
        }, true);
    })();

    // قراءة الثيم الذي اختاره الباشمهندس مهدي من الشاشة الموحدة وتطبيقه طالما الصفحة تفتح
    // ═══════════════════════════════════════════════════════════════════
    // 🎨 تطبيق فوري ومبكر لأحد السمات السبعة المعتمدة (7 Master Themes)
    // ═══════════════════════════════════════════════════════════════════
    (function initMaster7ThemeEarly() {
        var getCk = function(n) { var m = document.cookie.match(new RegExp("(?:^|; )" + n + "=([^;]*)")); return m ? decodeURIComponent(m[1]) : ""; };
        var savedTone = getCk("magaies_light_tone") || localStorage.getItem("magaies_light_tone") || localStorage.getItem("pur_light_tone_palette") || "";
        var rawTheme = getCk("magaies_active_theme") || localStorage.getItem("magaies_active_theme") || localStorage.getItem("magaiees_global_theme") || localStorage.getItem("ui_theme") || "warm_cream";
        var aliasMap = {
            "dark_midnight": "midnight_obsidian", "dark_mocha": "mocha_brown", "dark_oled": "pure_oled", "dark_slate": "slate_twilight",
            "ivory_default": "warm_cream", "royal_bronze": "warm_cream", "platinum_gray": "exec_gray", "crystal_silver": "ultra_white",
            "sage_pearl": "warm_cream", "sky_alabaster": "ultra_white", "rose_blossom": "warm_cream", "warm_mocha_dark": "warm_cream", "deep_slate_dark": "exec_gray",
            "warm-alabaster": "warm_cream", "beige": "warm_cream", "ivory": "warm_cream", "light-linen": "warm_cream",
            "pure-platinum": "ultra_white", "offwhite": "ultra_white", "white": "ultra_white", "light": "ultra_white",
            "pearl-platinum": "exec_gray", "nordic-paper": "warm_cream", "solar_contrast": "warm_cream",
            "midnight-executive": "midnight_obsidian", "navy-kohl": "midnight_obsidian", "royal-blue": "midnight_obsidian", "ocean-navy": "midnight_obsidian", "navy": "midnight_obsidian",
            "sepia-brown": "mocha_brown", "desert-mocha": "mocha_brown", "brown": "mocha_brown",
            "obsidian-charcoal": "pure_oled", "midnight": "pure_oled", "black": "pure_oled", "dark": "pure_oled",
            "twilight-indigo": "slate_twilight", "emerald-forest": "slate_twilight", "forest": "slate_twilight",
            "theme-imperial": "warm_cream"
        };
        var activeKey = (savedTone && aliasMap[savedTone]) ? aliasMap[savedTone] : (aliasMap[rawTheme] || rawTheme);
        var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
        if (!valid7.includes(activeKey)) activeKey = 'warm_cream';

        var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);
        var validTones = ['dark_midnight', 'pure_oled', 'mocha_brown', 'slate_twilight', 'ivory_default', 'royal_bronze', 'platinum_gray', 'crystal_silver', 'sage_pearl', 'sky_alabaster', 'rose_blossom', 'warm_mocha_dark', 'deep_slate_dark'];
        var activeTone = validTones.includes(savedTone) ? savedTone : (isLight ? 'ivory_default' : 'dark_midnight');

        document.documentElement.setAttribute('data-theme', activeKey);
        document.documentElement.setAttribute('data-light-tone', activeTone);
        if (isLight) {
            document.documentElement.classList.add('light-theme');
            document.documentElement.classList.remove('dark-theme');
        } else {
            document.documentElement.classList.remove('light-theme');
            document.documentElement.classList.add('dark-theme');
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (document.body) {
                document.body.setAttribute('data-theme', activeKey);
                document.body.setAttribute('data-light-tone', activeTone);
                if (isLight) {
                    document.body.classList.add('light-theme');
                    document.body.classList.remove('dark-theme');
                } else {
                    document.body.classList.remove('light-theme');
                    document.body.classList.add('dark-theme');
                }
            }
        });
    })();
    
    // رصد وتطبيق كلاس الشريط الجانبي فورا على HTML و Body لمنع اختفاء/تداخل الشاشة خلف الشريط الجانبي عند بداية الفتح
    const pageName = window.location.pathname.split('/').pop().toLowerCase();
    if (pageName !== 'login.php' && pageName !== 'index.php' && pageName !== 'register.php' && !pageName.includes('preview')) {
        document.documentElement.classList.add('has-sidebar');
        if (document.body) {
            document.body.classList.add('has-sidebar');
        } else {
            document.addEventListener("DOMContentLoaded", function() {
                if (document.body) document.body.classList.add('has-sidebar');
            });
        }
    }

    const isReportPage = pageName && pageName !== 'smart_reports_hub.php' && pageName !== 'dashboard-pro.php' && pageName !== 'login.php' && pageName !== 'index.php' && pageName !== 'inventory-report.php' && pageName !== 'analytics.php';
    if (isReportPage) {
        document.documentElement.classList.add('inner-report-page');
    } else if (pageName === 'inventory-report.php') {
        document.documentElement.classList.add('inventory-hub-page');
    } else if (pageName === 'analytics.php') {
        document.documentElement.classList.add('hub-report-page');
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (document.body) {
            const isLight = document.documentElement.classList.contains('light-theme');
            document.body.classList.add(isLight ? 'light-theme' : 'dark-theme');
            const activeTheme = document.documentElement.getAttribute('data-theme');
            if (activeTheme) document.body.setAttribute('data-theme', activeTheme);
            if (document.documentElement.classList.contains('has-sidebar')) {
                document.body.classList.add('has-sidebar');
            }
        }
    });
</script>
<script id="magaies-natural-100-screen-guard">
(function(){
    try {
        var CLEAN_FLAG = 'magaies_natural_screen_fix_20261115_v7';
        var ZOOM_KEYS = ['magaies_global_master_scale_v1','elsajana_global_screen_scale_v15','magaies_erp_global_proportional_scale_v15','elsajana_grn_zoom_scale','magaies_sl_fc1_scale_6_8','magaies_global_font_px','magaies_telescopic_zoom_scale','gl_c1_proportional_zoom','erp_global_6_8_zoom_scale','sales_c1_prop_zoom','pur_c1_prop_zoom','erp_split_zoom_scale_v1','erp_global_laptop_zoom_scale_v1','erp_pa_c15_zoom_scale','magaies_gate_table_scale_v2','erp_gate_table_scale_v1','magaiees_font_scale','magaies_font_scale','magaiees_typography_hierarchy_v1','magaies_ergo_global_mode','magaies_ergo_vboost_px'];
        if (localStorage.getItem(CLEAN_FLAG) !== '1') {
            ZOOM_KEYS.forEach(function(k){ localStorage.removeItem(k); });
            for (var i = localStorage.length - 1; i >= 0; i--) {
                var lk = localStorage.key(i) || '';
                if (lk.indexOf('magaies_ergo_page_') === 0 || /(_zoom_scale|_prop_zoom|_screen_scale|viewport_profile)/i.test(lk)) localStorage.removeItem(lk);
            }
            localStorage.setItem(CLEAN_FLAG, '1');
            if ('caches' in window) { caches.keys().then(function(ks){ ks.forEach(function(c){ caches.delete(c); }); }).catch(function(){}); }
            if ('serviceWorker' in navigator) { navigator.serviceWorker.getRegistrations().then(function(rs){ rs.forEach(function(r){ r.update(); }); }).catch(function(){}); }
        }
        var root = document.documentElement;
        root.style.removeProperty('zoom');
        root.style.removeProperty('--mg-natural-comp-zoom');
        root.style.fontSize = '';
        root.removeAttribute('data-magaiees-scaled');
        root.style.setProperty('--global-master-scale', '1');
        root.style.setProperty('--magaiees-font-scale', '1');
        var chkZoom = function(){
            if (window.__calibrateTrueNatural100Screen) { window.__calibrateTrueNatural100Screen(); return; }
            if (/Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent || '')) return;
            var iw = window.innerWidth || 1366, sw = (window.screen && window.screen.availWidth) || iw;
            var isMax = Math.abs(window.screenX || 0) <= 30 && (!window.outerWidth || window.outerWidth >= sw * 0.88);
            var zPct = isMax ? Math.round((sw / Math.max(iw, 200)) * 100) : 100;
            var hud = document.getElementById('mg-chrome-zoom-guide-banner');
            if (hud) { hud.style.display = 'none'; }
        };
        window.addEventListener('DOMContentLoaded', chkZoom, { once: true });
        window.addEventListener('resize', chkZoom, { passive: true });
    } catch(e) {}
})();
</script>
<link id="magaiees-google-fonts-pack" rel="stylesheet" href="css/local-fonts.css?v=2027">
<script>(function(){try{var f=localStorage.getItem('magaiees_font_family')||'default',s=parseInt(localStorage.getItem('magaiees_font_scale')||'100',10)||100,w=localStorage.getItem('magaiees_font_weight')||'auto',r=document.documentElement,fm={'default':"'Tajawal','Cairo',sans-serif",'ibm_plex':"'IBM Plex Sans Arabic','Inter',sans-serif",'readex_pro':"'Readex Pro','Inter',sans-serif",'almarai':"'Almarai','Inter',sans-serif"};if(f&&f!=='default'&&fm[f]){r.setAttribute('data-magaiees-font',f);r.style.setProperty('--magaiees-active-font',fm[f]);}else{r.removeAttribute('data-magaiees-font');r.style.removeProperty('--magaiees-active-font');}if(w&&w!=='auto'){r.setAttribute('data-mg-master-weight',w);r.style.setProperty('--mg-master-weight',w);}else{r.removeAttribute('data-mg-master-weight');r.style.removeProperty('--mg-master-weight');}r.style.setProperty('--magaiees-font-scale',(s/100).toFixed(3));if(s!==100){r.setAttribute('data-magaiees-scaled','true');r.style.fontSize=(16*(s/100)).toFixed(2)+'px';}else{r.removeAttribute('data-magaiees-scaled');r.style.fontSize='';}var h=JSON.parse(localStorage.getItem('magaiees_typography_hierarchy_v1')||'{}'),lvls=['sidebar','flyout','titles','modals','details','tables','mobile'];for(var i=0;i<lvls.length;i++){var k=lvls[i],c=h[k]||{};var ls=parseInt(c.scale||100,10)||100,lw=c.weight||'auto',lf=c.font||'inherit';if(ls!==100||lw!=='auto'||lf!=='inherit'){r.setAttribute('data-mg-lvl-'+k,'1');r.style.setProperty('--mg-lvl-'+k+'-scale',(ls/100).toFixed(3));if(lw!=='auto'){r.style.setProperty('--mg-lvl-'+k+'-weight',lw);r.style.setProperty('--mg-lvl-'+k+'-weight-sub',lw);}else{r.style.removeProperty('--mg-lvl-'+k+'-weight');r.style.removeProperty('--mg-lvl-'+k+'-weight-sub');}if(lf!=='inherit'&&fm[lf])r.style.setProperty('--mg-lvl-'+k+'-font',fm[lf]);else r.style.removeProperty('--mg-lvl-'+k+'-font');}else{r.removeAttribute('data-mg-lvl-'+k);r.style.removeProperty('--mg-lvl-'+k+'-scale');r.style.removeProperty('--mg-lvl-'+k+'-weight');r.style.removeProperty('--mg-lvl-'+k+'-font');}}}catch(e){}})();</script>
<link rel="stylesheet" href="css/theme.css?v=<?= file_exists(__DIR__ . '/../css/theme.css') ? filemtime(__DIR__ . '/../css/theme.css') : '12' ?>">
<link rel="stylesheet" href="css/magaiees-royal-buttons.css?v=<?= file_exists(__DIR__ . '/../css/magaiees-royal-buttons.css') ? filemtime(__DIR__ . '/../css/magaiees-royal-buttons.css') : time() ?>">
<link id="magaies-ophthalmic-ergo-css" rel="stylesheet" href="css/ophthalmic-ergonomics-2027.css?v=<?= file_exists(__DIR__ . '/../css/ophthalmic-ergonomics-2027.css') ? filemtime(__DIR__ . '/../css/ophthalmic-ergonomics-2027.css') : time() ?>">
<script>
// Early sidebar injector - runs BEFORE deferred app.js (340KB)
// This tiny inline script injects the sidebar immediately when body is available
(function(){
    var p = window.location.pathname.split('/').pop().toLowerCase();
    if (p === 'login.php' || p === 'index.php' || p === 'register.php' || p.indexOf('preview') !== -1) return;
    
    var icons = {
        dash: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        pur: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>',
        sal: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
        fin: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
        inv: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
        rep: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        mas: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>',
        alr: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        tol: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>',
        set: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        out: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        brand: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        mob: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        shd: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v5.5c0 4.8-3.2 8.3-7 9.5-3.8-1.2-7-4.7-7-9.5V6l7-3z"/>'
    };
    var items = [
        ['magaiees_unified_dashboard.php','لوحة التحكم','#22d3ee','dash'],
        ['purchase.php','المشتريات','#fb923c','pur'],
        ['direct-sales.php','المبيعات','#34d399','sal'],
        ['inventory-report.php','تقارير المخازن','#a78bfa','inv'],
        ['vouchers-hub.php','المعاملات المالية','#fbbf24','fin'],
        ['analytics.php','تقارير مالية','#38bdf8','rep'],
        ['master-data.php','البيانات الأساسية','#f472b6','mas'],
        ['alarm.php','التنبيهات','#f87171','alr'],
        ['engineering_tools.php','أدوات مساعدة','#ec4899','tol'],
        ['settings.php','الإعدادات','#a3e635','set'],
        ['mobile-dashboard.php','نسخة الجوال 📱','#38bdf8','mob']
    ];
    var mk = function(ic,c){return '<svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">'+icons[ic]+'</svg>';};
    var nav = '';
    for(var i=0;i<items.length;i++){
        var it=items[i], ac=(p===it[0]?' active':'');
        var clk = (it[0] === 'mobile-dashboard.php') ? "try{localStorage.setItem('magaiees_view_preference','mobile');document.cookie='magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax';}catch(e){}; window.location.href='mobile-dashboard.php?force_mobile=1';" : "window.location.href='"+it[0]+"';";
        nav+='<a href="'+it[0]+'" style="cursor:pointer" data-href="'+it[0]+'" onclick="'+clk+'" class="sidebar-item'+ac+'"><span class="sidebar-icon" style="color:'+it[2]+'">'+mk(it[3],it[2])+'</span><span class="sidebar-text">'+it[1]+'</span></a>';
    }
    nav+='<a href="#" data-href="system-self-audit.php" onclick="if(window.openMagaiesGuardianVault)window.openMagaiesGuardianVault();return false;" class="sidebar-item mgv-sidebar-slot magaiees-guardian-red-box" title="درع السيادة — غرفة القيادة والسيطرة"><span class="sidebar-icon mgv-shield-icon" style="color:#10b981"><span style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;">'+mk('shd','#10b981')+'<span class="mgv-num" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:inherit;font-weight:900;font-size:8.5px;line-height:1;padding-top:1px;color:currentColor;">0</span></span></span><span class="sidebar-text" style="font-family:inherit !important;font-weight:900 !important;letter-spacing:0;">درع السيادة</span></a>';
    var html='<aside class="global-sidebar"><div class="sidebar-header"><span class="sidebar-logo-icon"><svg width="24" height="24" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">'+icons.brand+'</svg></span><span class="sidebar-logo-text" style="font-size:0.86rem !important;font-weight:900 !important;letter-spacing:0 !important;">السيطرة (AL-SAYTARA)</span></div><nav class="sidebar-nav">'+nav+'</nav><div class="sidebar-footer"><a href="#" onclick="logout();return false;" class="sidebar-item sidebar-logout"><span class="sidebar-icon" style="color:#f87171"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">'+icons.out+'</svg></span><span class="sidebar-text">خروج</span></a></div></aside>';
    
    function inject(){
        if(document.body && !document.querySelector('.global-sidebar')){
            document.body.insertAdjacentHTML('afterbegin',html);
            document.body.classList.add('has-sidebar');
        }
    }
    // Try immediately (body may exist if header.php is included after <body>)
    inject();
    // If body doesn't exist yet, wait for it
    if(!document.body){
        var mo = new MutationObserver(function(m,o){ if(document.body){inject();o.disconnect();} });
        mo.observe(document.documentElement,{childList:true});
    }
})();
</script>
<script defer src="js/sidebar-panoramic-flyout.js?v=<?= file_exists(__DIR__ . '/../js/sidebar-panoramic-flyout.js') ? filemtime(__DIR__ . '/../js/sidebar-panoramic-flyout.js') : '2027_natural_v6' ?>"></script>
<script defer src="js/erp_alerts_guard.js?v=20260902_v1"></script>
<style>
@keyframes neonPulseHeader {
    0% { box-shadow: 0 0 15px rgba(168, 85, 247, 0.2), inset 0 0 10px rgba(168, 85, 247, 0.1); border-color: rgba(168, 85, 247, 0.3); }
    50% { box-shadow: 0 0 35px rgba(168, 85, 247, 0.5), inset 0 0 20px rgba(168, 85, 247, 0.25); border-color: rgba(192, 132, 252, 0.8); }
    100% { box-shadow: 0 0 15px rgba(168, 85, 247, 0.2), inset 0 0 10px rgba(168, 85, 247, 0.1); border-color: rgba(168, 85, 247, 0.3); }
}
@keyframes glassShine {
    0% { transform: translateX(-100%) skewX(-15deg); }
    100% { transform: translateX(200%) skewX(-15deg); }
}
.magical-glass-header {
    background: linear-gradient(135deg, rgba(20, 10, 35, 0.65), rgba(35, 15, 60, 0.45)) !important;
    /* gpu-opt */
    /* gpu-opt */
    border: 1px solid rgba(192, 132, 252, 0.4) !important;
    border-top: 2px solid rgba(192, 132, 252, 0.9) !important;
    border-radius: 20px !important;
    margin: 15px 25px !important;
    padding: 12px 25px !important;
    animation: none; /* cpu-opt */
    position: relative;
    z-index: 1000;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), color 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: visible !important;
}
.magical-glass-header:hover {
    transform: translateY(-3px) scale(1.005);
    box-shadow: 0 15px 45px rgba(192, 132, 252, 0.6), inset 0 0 25px rgba(192, 132, 252, 0.4) !important;
}
.magical-glass-header::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 50%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: none; /* cpu-opt */
    z-index: 1;
    pointer-events: none;
}
.magical-glass-header > div {
    position: relative;
    z-index: 2;
}
/* Magical tweaks for inner items */
.magical-glass-header .clock-container, 
.magical-glass-header .user-info, 
.magical-glass-header #online-users-badge,
.magical-glass-header #global-exchange-rate-box {
    background: rgba(20, 10, 40, 0.6) !important;
    border: 1px solid rgba(192, 132, 252, 0.25) !important;
    /* gpu-opt */
    border-radius: 8px !important;
    transition: transform 0.25s ease, opacity 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
}
.magical-glass-header .clock-container:hover, 
.magical-glass-header .user-info:hover, 
.magical-glass-header #online-users-badge:hover,
.magical-glass-header #global-exchange-rate-box:hover {
    border-color: rgba(192, 132, 252, 0.8) !important;
    box-shadow: 0 0 16px rgba(192, 132, 252, 0.35), inset 0 0 8px rgba(192, 132, 252, 0.18) !important;
    transform: translateY(-1px);
    background: rgba(30, 15, 60, 0.7) !important;
}
.magical-glass-header .header-page-title {
    text-shadow: 0 0 15px rgba(192, 132, 252, 0.8), 0 0 30px rgba(192, 132, 252, 0.4);
    color: #fff !important;
}
.magical-glass-header button.fullscreen-toggle-btn,
.magical-glass-header button.theme-toggle-btn {
    background: rgba(168, 85, 247, 0.16) !important;
    border: 1.5px solid rgba(168, 85, 247, 0.42) !important;
    color: #e9d5ff !important;
    border-radius: 9px !important;
}
.magical-glass-header button.fullscreen-toggle-btn:hover,
.magical-glass-header button.theme-toggle-btn:hover {
    background: rgba(168, 85, 247, 0.38) !important;
    box-shadow: 0 4px 14px rgba(168, 85, 247, 0.5) !important;
    transform: translateY(-2px) scale(1.06) !important;
    color: #fff !important;
}
.magical-glass-header #header-clock {
    color: #34d399 !important;
    text-shadow: 0 0 10px rgba(16, 185, 129, 0.45) !important;
}
@media (max-width: 768px) {
    .magical-glass-header {
        margin: 10px !important;
        padding: 10px !important;
        border-radius: 16px !important;
    }
}

.header-tv-mini-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 34px;
    padding: 4px 12px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(30, 20, 50, 0.85), rgba(15, 23, 42, 0.95));
    border: 1.5px solid rgba(192, 132, 252, 0.4);
    color: #f8fafc;
    cursor: pointer;
    transition: transform 0.25s ease, opacity 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.35);
    white-space: nowrap;
    user-select: none;
}
.header-tv-mini-btn:hover {
    transform: translateY(-2px);
    border-color: #f59e0b;
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.3);
    color: #fef08a;
}
@keyframes header-tv-pulse-glow {
    0%, 100% {
        box-shadow: 0 0 18px #f59e0b, 0 0 35px rgba(245, 158, 11, 0.8), inset 0 0 12px rgba(245, 158, 11, 0.5) !important;
        border-color: #f59e0b !important;
    }
    50% {
        box-shadow: 0 0 28px #fbbf24, 0 0 55px rgba(251, 191, 36, 0.95), 0 0 70px #ffffff, inset 0 0 18px rgba(251, 191, 36, 0.7) !important;
        border-color: #ffffff !important;
    }
}
.header-tv-mini-btn.is-playing {
    border-color: #f59e0b !important;
    background: linear-gradient(135deg, rgba(180, 83, 9, 0.85), rgba(15, 23, 42, 0.98)) !important;
    animation: none; /* cpu-opt */
    color: #ffffff !important;
    font-weight: 900 !important;
}
.header-tv-mini-btn.is-playing .header-tv-icon {
    animation: none; /* cpu-opt */
    filter: drop-shadow(0 0 8px #fbbf24);
}
.header-tv-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    box-shadow: 0 0 8px #ef4444;
    animation: none; /* cpu-opt */
    display: inline-block;
    flex-shrink: 0;
}
@keyframes headerTvPulse {
    0% { transform: scale(0.85); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.6; }
    100% { transform: scale(0.85); opacity: 1; }
}
.header-tv-popover {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    width: 460px !important;
    max-width: calc(100vw - 20px) !important;
    max-height: calc(100vh - 30px) !important;
    overflow-y: auto !important;
    background: linear-gradient(155deg, rgba(15, 23, 42, 0.99), rgba(5, 10, 24, 0.99)) !important;
    border: 2px solid #f59e0b !important;
    border-radius: 24px !important;
    padding: 18px 20px !important;
    box-shadow: 0 25px 80px rgba(0,0,0,0.95), 0 0 50px rgba(245, 158, 11, 0.45) !important;
    /* gpu-opt */
    /* gpu-opt */
    z-index: 2147483647 !important;
    direction: rtl !important;
    text-align: right !important;
    animation: popoverCenterZoom 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
@keyframes popoverCenterZoom {
    from { opacity: 0; transform: translate(-50%, -46%) scale(0.95); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
#header-media-backdrop {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, 0.72) !important;
    /* gpu-opt */
    /* gpu-opt */
    z-index: 2147483640 !important;
    transition: opacity 0.2s ease !important;
}
.header-pop-ch-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 7px 3px;
    border-radius: 11px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    color: #f8fafc;
    font-size: 10.5px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}
.header-pop-ch-btn:hover {
    background: rgba(255,255,255,0.12);
    border-color: var(--ch-c);
    color: var(--ch-c);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 12px var(--ch-c);
}
.header-pop-ch-btn.active-ch {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(15, 23, 42, 0.95));
    border-color: var(--ch-c);
    box-shadow: 0 0 15px var(--ch-c), inset 0 0 8px var(--ch-c);
    color: #fff;
    transform: translateY(-1px);
}

/* ═══════════════════════════════════════════════════════════
   🌟 AUTO-ASCENT RETURN PILL & WORKSPACE GLOW STYLES
   ═══════════════════════════════════════════════════════════ */
.auto-scroll-return-pill {
    position: fixed;
    top: 14px;
    left: 50%;
    transform: translateX(-50%) translateY(-65px);
    opacity: 0;
    pointer-events: none;
    z-index: 999999;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(30, 41, 59, 0.96));
    /* gpu-opt */
    /* gpu-opt */
    border: 1.5px solid rgba(56, 189, 248, 0.45);
    border-radius: 30px;
    padding: 6px 18px;
    color: #f8fafc;
    font-size: 0.85rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.65), 0 0 20px rgba(56, 189, 248, 0.25);
    display: flex;
    align-items: center;
    gap: 9px;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease, box-shadow 0.3s ease, border-color 0.3s ease, background 0.3s ease;
    user-select: none;
}
.auto-scroll-return-pill.visible {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
    pointer-events: auto;
}
.auto-scroll-return-pill:hover {
    border-color: #38bdf8;
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.98));
    color: #38bdf8;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), 0 0 28px rgba(56, 189, 248, 0.55);
    transform: translateX(-50%) translateY(-2px) scale(1.03);
}
.auto-scroll-return-pill .pill-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.2);
    color: #38bdf8;
    font-size: 0.75rem;
    transition: transform 0.25s, opacity 0.25s, background-color 0.25s, border-color 0.25s, color 0.25s;
}
.auto-scroll-return-pill:hover .pill-arrow {
    background: #38bdf8;
    color: #0f172a;
    transform: translateY(-2px);
}

</style>
<header class="premium-header magical-glass-header <?php echo isset($header_theme_class) ? $header_theme_class : ''; ?>" style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:6px 12px !important; margin:0 !important; width:100% !important; max-width:100% !important; height:auto !important; min-height:0 !important; box-sizing:border-box !important; zoom:1 !important;">
    <?php
    $dynamic_icon = '✨'; // default
    if (isset($page_title)) {
        if (strpos($page_title, 'لوحة التحكم') !== false || strpos($page_title, 'SaaS') !== false || strpos($page_title, 'Dashboard') !== false) $dynamic_icon = '🎛️';
        elseif (strpos($page_title, 'مبيعات') !== false || strpos($page_title, 'المبيعات') !== false || strpos($page_title, 'Sales') !== false) $dynamic_icon = '🛒';
        elseif (strpos($page_title, 'مشتريات') !== false || strpos($page_title, 'المشتريات') !== false || strpos($page_title, 'استلام') !== false || strpos($page_title, 'Purchase') !== false) $dynamic_icon = '🛍️';
        elseif (strpos($page_title, 'مخزون') !== false || strpos($page_title, 'مخازن') !== false || strpos($page_title, 'جرد') !== false || strpos($page_title, 'صنف') !== false || strpos($page_title, 'أصناف') !== false || strpos($page_title, 'تسليم') !== false || strpos($page_title, 'أذونات') !== false || strpos($page_title, 'Inventory') !== false) $dynamic_icon = '📦';
        elseif (strpos($page_title, 'حساب') !== false || strpos($page_title, 'مالي') !== false || strpos($page_title, 'صرف') !== false || strpos($page_title, 'قبض') !== false || strpos($page_title, 'خزن') !== false || strpos($page_title, 'بنوك') !== false || strpos($page_title, 'أرصدة') !== false || strpos($page_title, 'أرباح') !== false || strpos($page_title, 'ديون') !== false || strpos($page_title, 'قيد') !== false || strpos($page_title, 'Finance') !== false || strpos($page_title, 'Account') !== false) $dynamic_icon = '💰';
        elseif (strpos($page_title, 'شيكات') !== false || strpos($page_title, 'Check') !== false) $dynamic_icon = '🧾';
        elseif (strpos($page_title, 'شركاء') !== false || strpos($page_title, 'مهام') !== false || strpos($page_title, 'شغل') !== false || strpos($page_title, 'رواتب') !== false || strpos($page_title, 'Partner') !== false) $dynamic_icon = '👥';
        elseif (strpos($page_title, 'إعدادات') !== false || strpos($page_title, 'تدقيق') !== false || strpos($page_title, 'نشاط') !== false || strpos($page_title, 'نظام') !== false || strpos($page_title, 'أساسية') !== false || strpos($page_title, 'نسخ') !== false || strpos($page_title, 'تصفير') !== false || strpos($page_title, 'Settings') !== false || strpos($page_title, 'Master') !== false) $dynamic_icon = '⚙️';
        elseif (strpos($page_title, 'تقارير') !== false || strpos($page_title, 'إحصائيات') !== false || strpos($page_title, 'تحليل') !== false || strpos($page_title, 'Report') !== false) $dynamic_icon = '📊';
        elseif (strpos($page_title, 'تنبيهات') !== false || strpos($page_title, 'Alert') !== false) $dynamic_icon = '🔔';
        elseif (strpos($page_title, 'باركود') !== false || strpos($page_title, 'Barcode') !== false) $dynamic_icon = '🖨️';
        elseif (strpos($page_title, 'Audit') !== false || strpos($page_title, 'الصندوق') !== false) $dynamic_icon = '🔒';
    }
    ?>
    <!-- 1. الركن الأيمن الملكي الفاخر (مهذب بالكامل: نظام إدارة الشركات + سعر صرف الدولار البارز + التاريخ والوقت فقط) -->
    <div class="header-compact-right-cluster header-flagship-identity-suite" style="display:flex; flex-direction:column; justify-content:space-between; gap:4px; flex:0.88 1 315px !important; min-width:280px !important; max-width:455px !important; height:78px !important; min-height:78px !important; max-height:78px !important; box-sizing:border-box !important; background:linear-gradient(135deg, rgba(2, 6, 23, 0.96) 0%, rgba(15, 23, 42, 0.95) 55%, rgba(6, 78, 59, 0.55) 100%); border:1.5px solid rgba(251, 191, 36, 0.85); border-radius:13px; padding:6px 10px; box-shadow:0 7px 21px rgba(0, 0, 0, 0.8), 0 0 16px rgba(251, 191, 36, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.22); position:relative; overflow:hidden;">
        <!-- الصف الأول: شعار السيطرة وكلمة السيطرة أولاً + بعد ذلك نظام إدارة الشركات ERP -->
        <div style="display:flex; align-items:center; justify-content:flex-start; gap:8px; white-space:nowrap; height:34px; border-bottom:1px solid rgba(251, 191, 36, 0.22); padding-bottom:3px; overflow:hidden;">
            <!-- 1. شعار السيطرة المعتمد الكبير وكلمة السيطرة في الأول -->
            <a href="index.php" class="header-magaiees-emblem-badge" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none; flex-shrink:0; height:32px; padding:1px 7px; border-radius:8px; background:linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(2, 6, 23, 0.98)); border:1.5px solid rgba(251, 191, 36, 0.75); box-shadow:0 2px 10px rgba(0, 0, 0, 0.7), 0 0 12px rgba(251, 191, 36, 0.35); transition:all 0.25s ease;" title="شعار السيطرة المعتمد — AL-SAYTARA ERP">
                <img src="assets/logo.png?v=saytara_shield_v3" alt="شعار السيطرة المعتمد" style="height:30px; width:auto; max-height:32px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(0,0,0,0.9)) drop-shadow(0 0 8px rgba(251, 191, 36, 0.5));">
                <span style="color:#fef08a; font-weight:900; font-size:14.5px; letter-spacing:0.4px; text-shadow:0 2px 6px rgba(0,0,0,0.95), 0 0 10px rgba(251, 191, 36, 0.4);">السيطرة</span>
            </a>
            <!-- 2. بعد ذلك نظام إدارة الشركات ERP -->
            <div style="display:inline-flex; align-items:center; gap:6px; min-width:0; overflow:hidden; flex-shrink:1;">
                <span style="color:#ffffff; font-weight:900; font-size:17.5px; letter-spacing:0.2px; text-shadow:0 2px 8px rgba(0,0,0,0.95), 0 0 14px rgba(251, 191, 36, 0.5); overflow:hidden; text-overflow:ellipsis;" id="header-company-name-top" data-i18n="sidebar.brand">نظام إدارة الشركات</span>
                <span style="background:linear-gradient(135deg, rgba(251, 191, 36, 0.35), rgba(16, 185, 129, 0.35)); border:1.5px solid rgba(254, 240, 138, 0.85); color:#fef08a; font-size:11.5px; font-weight:900; padding:2px 7px; border-radius:999px; letter-spacing:0.4px; flex-shrink:0; box-shadow:0 0 8px rgba(251, 191, 36, 0.3);">ERP</span>
            </div>
            <?php if (!empty($page_title)): ?><span class="header-page-title m7-solid-badge-guard" style="margin-right:auto; height:26px; font-size:12px; font-weight:900; background:linear-gradient(135deg, #ea580c, #9a3412); border:1.5px solid #fb923c; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; padding:0 9px; border-radius:7px; display:inline-flex !important; align-items:center; gap:4px; line-height:1; flex-shrink:0; box-shadow:0 2px 8px rgba(249, 115, 22, 0.35);"><span class="header-page-title-icon" style="font-size:12px;"><?php echo $dynamic_icon; ?></span><span style="color:#ffffff !important; -webkit-text-fill-color:#ffffff !important;"><?php echo htmlspecialchars($page_title); ?></span></span><?php else: ?><span class="header-page-title" style="display:none !important;"><span class="header-page-title-icon"></span></span><?php endif; ?>
        </div>

        <!-- الصف الثاني: بطاقة سعر صرف الدولار البارزة والمطلوبة + كبسولة التاريخ والوقت المدمجة -->
        <div class="header-enterprise-meta-pod" style="display:flex !important; flex-direction:row !important; align-items:center !important; justify-content:space-between !important; gap:8px !important; padding:0 !important; background:transparent !important; border:none !important; box-shadow:none !important; white-space:nowrap; width:100% !important; height:30px !important;">
            <!-- بطاقة سعر الدولار الرسمية (بارزة وواضحة وقابلة للنقر) -->
            <div id="global-exchange-rate-box" class="enterprise-fx-pill" style="display:inline-flex !important; height:29px !important; padding:0 9px !important; border-radius:8px !important; background:linear-gradient(135deg, rgba(120, 53, 15, 0.95), rgba(180, 83, 9, 0.85)) !important; border:1.5px solid #fbbf24 !important; box-shadow:0 0 10px rgba(251, 191, 36, 0.35) !important; cursor:pointer;" onclick="if(typeof openGlobalExchangeRateModal==='function'){openGlobalExchangeRateModal();}else if(typeof toggleExchangeHistory==='function'){toggleExchangeHistory(event);}" title="سعر صرف الدولار الأمريكي الرسمي — اضغط للتعديل أو سجل الأسعار">
                <span class="fx-pill-inner" style="font-size:11.5px !important; font-weight:900 !important; display:inline-flex; align-items:center; gap:4px;">
                    <span style="font-size:12px;">💵 $</span>
                    <span style="color:#fde047;">الدولار:</span>
                    <span id="global-exchange-rate-trend" style="margin-left:1px;"></span>
                    <span id="global-exchange-rate-val" style="font-size:12.5px !important; font-weight:900 !important; color:#ffffff !important; letter-spacing:0.4px !important;"><?php echo number_format((float)$systemUsdRate, 2); ?></span>
                    <span style="font-size:10px; color:#fef08a;">SDG</span>
                </span>
            </div>

            <!-- كبسولة التاريخ والوقت -->
            <div class="clock-container enterprise-time-pill" style="height:29px !important; padding:0 8px !important; border-radius:8px !important; gap:4px !important; width:auto !important; background:linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.99)); border:1.5px solid rgba(251, 191, 36, 0.65); box-shadow:inset 0 1px 2px rgba(0,0,0,0.6), 0 2px 8px rgba(0,0,0,0.4);">
                <span style="font-size:11px;">📅</span>
                <span id="header-date-mini" style="font-size:11px !important; font-weight:900 !important; color:#fef08a; letter-spacing:0.3px !important;"><script>(function(){var _d=new Date();var _dd=String(_d.getDate()).padStart(2,'0');var _mm=String(_d.getMonth()+1).padStart(2,'0');var _yy=_d.getFullYear();document.write(_dd+'/'+_mm+'/'+_yy);})()</script></span>
                <span class="time-pill-divider" style="font-size:10.5px; color:rgba(251, 191, 36, 0.5);">|</span>
                <span style="font-size:11px;">🕒</span>
                <span id="header-clock" style="font-size:11.5px !important; font-weight:900 !important; color:#34d399; letter-spacing:0.6px !important;"><script>var _n=new Date();document.write(_n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}))</script></span>
            </div>

            <!-- شارة بورصة السيطرة المدمجة تحت لوحة التحكم مباشرة بدون مربع جانبي وبنفس نسق لوحة التحكم -->
            <span id="header-magaiees-bourse-btn" role="button" tabindex="0" class="m7-solid-badge-guard" onclick="window.toggleMagaieesBourseGlobal();" title="إظهار أو إخفاء شريط بورصة السيطرة فوراً" style="margin-right:auto; height:22px; font-size:11px; font-weight:900; background:linear-gradient(135deg, #059669, #065f46); border:1.5px solid #fde047; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; padding:0 8px; border-radius:6px; display:inline-flex !important; align-items:center; justify-content:center; line-height:1; flex-shrink:0; cursor:pointer; user-select:none; box-shadow:0 0 8px rgba(16, 185, 129, 0.4);">بورصة السيطرة</span>
            <script>
            window.toggleMagaieesBourseGlobal = function() {
                var dashWrap = document.getElementById('market-broker-ticker-wrap');
                var btn = document.getElementById('header-magaiees-bourse-btn');
                if (dashWrap) {
                    var isHidden = dashWrap.classList.contains('is-broker-hidden') || dashWrap.style.display === 'none' || window.getComputedStyle(dashWrap).display === 'none';
                    var nextShow = isHidden;
                    window._userManuallyClosedBroker = !nextShow;
                    localStorage.setItem('magaiees_hide_broker_ticker', nextShow ? '0' : '1');
                    dashWrap.classList.toggle('is-broker-hidden', !nextShow);
                    dashWrap.style.setProperty('display', nextShow ? 'flex' : 'none', 'important');
                    if (nextShow) {
                        var r1 = document.getElementById('market-broker-offers-row');
                        var r2 = document.getElementById('market-broker-orders-row');
                        if (r1) { r1.classList.remove('is-row-hidden'); r1.style.setProperty('display', 'flex', 'important'); }
                        if (r2) { r2.classList.remove('is-row-hidden'); r2.style.setProperty('display', 'flex', 'important'); }
                    }
                    if (btn) {
                        btn.style.background = nextShow ? 'linear-gradient(135deg, #059669, #065f46)' : 'linear-gradient(135deg, #be123c, #881337)';
                    }
                    return;
                }
                var gBar = document.getElementById('global-screens-broker-ticker');
                if (gBar) {
                    localStorage.setItem('magaiees_hide_broker_ticker', '1');
                    gBar.remove();
                    if (btn) btn.style.background = 'linear-gradient(135deg, #be123c, #881337)';
                } else {
                    localStorage.setItem('magaiees_hide_broker_ticker', '0');
                    if (btn) btn.style.background = 'linear-gradient(135deg, #059669, #065f46)';
                    if (typeof window.renderGlobalScreensBrokerBar === 'function') window.renderGlobalScreensBrokerBar();
                }
            };
            </script>
        </div>
    </div>

    <!-- 2. الوسط: محراب الآية القرآنية والذكر الحكيم في المنتصف (آية من الجمال والوضوح، حركة هادئة لسهولة القراءة، ومتصلة تبدأ مباشرة بعد ثانية واحدة بدون انقطاع طويل) -->
    <style>
        @keyframes quranRoyalContinuousFlow {
            0%   { transform: translate3d(0%, 0, 0); }
            100% { transform: translate3d(50%, 0, 0); }
        }
        @keyframes quranMihrabBorderGlow {
            0% {
                border-color: #fbbf24;
                box-shadow: 0 7px 22px rgba(0, 0, 0, 0.8), 0 0 18px rgba(251, 191, 36, 0.35), inset 0 0 18px rgba(16, 185, 129, 0.25);
            }
            100% {
                border-color: #fef08a;
                box-shadow: 0 9px 26px rgba(0, 0, 0, 0.88), 0 0 26px rgba(251, 191, 36, 0.65), 0 0 36px rgba(16, 185, 129, 0.35), inset 0 0 24px rgba(251, 191, 36, 0.22);
            }
        }
        #dashboard-athkar-container:hover .quran-seamless-track {
            animation-play-state: paused !important;
        }
        .quran-seamless-track {
            display: inline-flex !important;
            align-items: center !important;
            white-space: nowrap !important;
            width: max-content !important;
            direction: rtl !important;
            padding: 0 !important;
            will-change: transform;
            animation: none; /* cpu-opt */
        }
        .quran-seamless-segment {
            display: inline-flex !important;
            align-items: center !important;
            gap: 14px !important;
            padding-left: 28px !important; /* مسافة قصيرة جداً تعادل ثانية واحدة فقط قبل بداية الآية من جديد */
            font-family: 'Amiri', 'Traditional Arabic', 'Cairo', 'Tajawal', serif !important;
            font-size: 17.5px !important; /* زيادة درجة واحدة للآية الكريمة بناء على توجيه المستخدم */
            font-weight: 900 !important;
            color: #fef08a !important;
            -webkit-text-fill-color: #fef08a !important;
            letter-spacing: 0.2px !important;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.98), 0 0 14px rgba(251, 191, 36, 0.6), 0 0 24px rgba(16, 185, 129, 0.4) !important;
        }
        .quran-verse-highlight {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.98), 0 0 12px rgba(52, 211, 153, 0.7) !important;
        }
        .quran-ornament-star { color: #34d399 !important; -webkit-text-fill-color: #34d399 !important; font-size: 14px !important; }
        /* Global UI Purity & 120fps Smooth Wheel Scroll: Hide exit buttons near Quranic verse & redundant showcase/constitution clutter strips */
        #sl-sovereign-law1-bar [onclick*="directExitToMain"], header [onclick*="directExitToMain"], .unified-header [onclick*="ExitToMain"], .global-sidebar .sidebar-logout,
        #split-fc2-sovereign-row, #split-fc3-inline-7charts-body, #sales-fc3-hub-strip, #sl-fc3-hub-strip,
        [onclick*="SupremeConstitutionModal"], [onclick*="Inline7ChartsDrawer"], [onclick*="MasterAudit10AdditionsModal"],
        [onclick*="AtomicSyncAndDrillDownModal"], [onclick*="AiExecutiveInsightsModal"], [onclick*="ChequesAndBankakRadarModal"],
        [onclick*="ForensicOwnerRadarModal"], [onclick*="AntiEmbezzlementLocksModal"], [onclick*="CyberBiometricAndPrintStudioModal"],
        [onclick*="ProactiveAlertsCenterModal"], [onclick*="toggleDualSplitScreen"], [onclick*="openPurchaseAIAuditModal"], [onclick*="openGrnAIAuditModal"],
        #split-laser-symmetry-badge, #split-sovereign-10x-badge, #split-fc2-crdt-pill, #btn-md-fc1-audit-gate, #btn-grn-fc1-gate { display: none !important; }
        .ambient-bg { pointer-events: none !important; contain: strict; }
        .ambient-bg, .ambient-bg .orb { display: none !important; animation: none !important; filter: none !important; }
    .global-sidebar, .premium-header, .header-enterprise-meta-pod, .header-crystal-icon-dock, .auto-scroll-return-pill {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    .global-sidebar {
        background: #070d1a !important;
        contain: layout style paint;
        will-change: width;
        transition: width 0.14s cubic-bezier(0.2, 0, 0, 1) !important;
        box-shadow: -4px 0 18px rgba(0,0,0,0.55) !important;
    }
    .sidebar-nav {
        overscroll-behavior: contain;
        scroll-behavior: auto !important;
    }
    html, body {
        scroll-behavior: auto !important;
    }
    #dashboard-athkar-container {
        animation: none !important;
    }
        #dashboard-athkar-container { animation: none !important; transform: translateZ(0); contain: layout paint; }
    </style>
    <div id="dashboard-athkar-container" class="islamic-ticker-container" style="display:flex !important; flex:1.35 1 340px !important; min-width:240px !important; max-width:none !important; height:78px !important; min-height:78px !important; max-height:78px !important; box-sizing:border-box !important; margin:0 !important; padding:0 !important; border-radius:14px !important; background:radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.35), transparent 70%), linear-gradient(135deg, #022c22 0%, #064e3b 35%, #020617 70%, #1e1b4b 100%) !important; border:2px solid #fbbf24 !important; align-items:center !important; justify-content:center !important; overflow:hidden !important; position:relative !important; animation: quranMihrabBorderGlow 3.5s ease-in-out infinite alternate !important;" title="آيات قرآنية وأذكار مباركة — مرر الماوس لإيقاف الحركة مؤقتاً للقراءة">
        <!-- إطار زخرفي ذهبي داخلي فائق الجمال -->
        <div style="position:absolute; inset:3px; border:1px solid rgba(254, 240, 138, 0.32); border-radius:10px; pointer-events:none; z-index:2; box-shadow:inset 0 0 12px rgba(251, 191, 36, 0.12);"></div>
        <!-- تدرج جانبي ناعم لبروز النص في المنتصف بوضوح فائق -->
        <div style="position:absolute; top:0; bottom:0; right:0; width:18px; background:linear-gradient(to left, rgba(2, 44, 34, 0.92), transparent); z-index:3; pointer-events:none;"></div>
        <div style="position:absolute; top:0; bottom:0; left:0; width:18px; background:linear-gradient(to right, rgba(15, 23, 42, 0.92), transparent); z-index:3; pointer-events:none;"></div>

        <!-- شريط علوي مصغر ثابت في قلب المحراب يبرز جمالية الآية في المنتصف -->
        <div style="position:absolute; top:3px; left:50%; transform:translateX(-50%); z-index:4; background:linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.22), transparent); padding:0 12px; font-size:9.5px; font-weight:900; color:#6ee7b7; letter-spacing:0.4px; white-space:nowrap; pointer-events:none; text-shadow:0 1px 3px #000;">
            🕌 ﴿ بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ﴾ 🕌
        </div>

        <div style="width:100%; overflow:hidden; position:relative; height:100%; display:flex; align-items:center; padding-top:11px; direction:rtl;">
            <div class="quran-seamless-track">
                <div class="quran-seamless-segment">
                    <span>﴿ إِنَّ اللَّهَ وَمَلَائِكَتَهُ يُصَلُّونَ عَلَى النَّبِيِّ ۚ يَا أَيُّهَا الَّذِينَ آمَنُوا صَلُّوا عَلَيْهِ وَسَلِّمُوا تَسْلِيماً ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span class="quran-verse-highlight">﴿ وَقُلْ رَبِّ زِدْنِي عِلْماً ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span>﴿ رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span class="quran-verse-highlight">﴿ وَمَنْ يَتَّقِ اللَّهَ يَجْعَلْ لَهُ مَخْرَجاً • وَيَرْزُقْهُ مِنْ حَيْثُ لَا يَحْتَسِبُ ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span>سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ وَبَارِكْ عَلَى نَبِيِّنَا مُحَمَّدٍ</span>
                    <span class="quran-ornament-star">۞</span>
                </div>
                <div class="quran-seamless-segment" aria-hidden="true">
                    <span>﴿ إِنَّ اللَّهَ وَمَلَائِكَتَهُ يُصَلُّونَ عَلَى النَّبِيِّ ۚ يَا أَيُّهَا الَّذِينَ آمَنُوا صَلُّوا عَلَيْهِ وَسَلِّمُوا تَسْلِيماً ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span class="quran-verse-highlight">﴿ وَقُلْ رَبِّ زِدْنِي عِلْماً ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span>﴿ رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span class="quran-verse-highlight">﴿ وَمَنْ يَتَّقِ اللَّهَ يَجْعَلْ لَهُ مَخْرَجاً • وَيَرْزُقْهُ مِنْ حَيْثُ لَا يَحْتَسِبُ ﴾</span>
                    <span class="quran-ornament-star">۞</span>
                    <span>سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ وَبَارِكْ عَلَى نَبِيِّنَا مُحَمَّدٍ</span>
                    <span class="quran-ornament-star">۞</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. اليسار: شريط المستطيلات الرباعية الموحدة (ثابت المقاس لا يتأثر بتكبير الأيقونات) -->
    <div class="header-enterprise-controls" style="display:flex; align-items:center; gap:6px; flex:0 0 auto; justify-content:flex-end;">

        <!-- 2. Unified Horizontal Crystal Icon Dock (Shield, Search/Audit, Fullscreen, Theme Toggle, TV) -->
        <?php
        $current_page_name = basename($_SERVER['PHP_SELF']);
        $is_dashboard_page = (isset($current_page_name) && $current_page_name === 'magaiees_unified_dashboard.php') || (isset($header_theme_class) && $header_theme_class === 'header-theme-dashboard');
        $show_header_currency = false; // Disabled by user request
        if ($show_header_currency):
            $header_curr = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
        endif;
        ?>
        <div class="header-crystal-icon-dock" style="position:relative;">
            <!-- زر موحد لأدوات النظام السريعة (درع المزامنة، المتعقب، ملء الشاشة، تبديل المظهر) لمنع تكدس الأيقونات -->
            <div style="position:relative; display:flex; align-items:center;">
                <button type="button" id="header-tools-menu-btn" class="dock-icon-btn" onclick="var m=document.getElementById('header-quick-tools-popover'); if(m){m.style.display=(m.style.display==='flex'?'none':'flex');} event.stopPropagation();" title="أدوات النظام السريعة (درع الحماية، متعقب المستندات، ملء الشاشة)" style="width:auto !important; padding:0 10px !important; gap:5px !important; font-size:11.5px !important; font-weight:800 !important; color:#e2e8f0 !important; position:relative;">
                    <span>⚡</span>
                    <span>الأدوات</span>
                </button>
                <div id="header-quick-tools-popover" onclick="event.stopPropagation()" style="display:none; position:absolute; top:calc(100% + 8px); left:0; background:rgba(15,23,42,0.98); border:1.5px solid rgba(56,189,248,0.45); border-radius:12px; padding:8px; gap:8px; align-items:center; box-shadow:0 15px 35px rgba(0,0,0,0.85); z-index:999999; backdrop-filter:blur(12px);">
                    <button class="audit-tracker-btn dock-icon-btn dock-icon-search" onclick="window.openAuditTrackerModal()" title="متعقب المستندات وسجل تتبع المستخدمين (من فعل ماذا) - اضغط Alt+T">🔍</button>

                    <button class="fullscreen-toggle-btn dock-icon-btn dock-icon-fullscreen" onclick="toggleFullScreen()" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>

                    <button class="theme-toggle-btn dock-icon-btn dock-icon-theme" onclick="toggleTheme()" data-i18n-title="header.toggleTheme" title="تبديل المظهر السريع (فاتح / داكن)">🌓</button>
                </div>
            </div>
            <script>
            document.addEventListener('click', function() {
                var m = document.getElementById('header-quick-tools-popover');
                if (m && m.style.display === 'flex') m.style.display = 'none';
            });
            </script>

            <!-- 📺 SACRED TV & QURAN MINI CONTROLLER -->
            <div id="header-media-control-wrap" style="position:relative; display:flex; align-items:center; gap:5px;">
                <button id="header-live-mic-btn" type="button" onclick="if(typeof window.toggleDirectLiveVoiceMic==='function'){window.toggleDirectLiveVoiceMic();}" class="header-tv-mini-btn" style="border-color:#fde047;" title="مايك الحوار الصوتي المفتوح مع البروفيسور (اضغط ليضيء المايك وتحدث مباشرة)">
                    <span class="header-tv-icon">🎙️</span>
                    <span class="header-tv-label">الحوار</span>
                </button>
                <button id="header-media-btn" type="button" onclick="toggleHeaderMediaPopover(event)" class="header-tv-mini-btn" title="قنوات التلفاز والقرآن الكريم (بث حي في الخلفية)">
                    <span id="header-media-icon" class="header-tv-icon">📺</span>
                    <span id="header-media-label" class="header-tv-label">التلفاز</span>
                    <span id="header-media-dot" class="header-tv-status-dot" style="display: none;"></span>
                </button>
            </div>
        </div>

        <!-- 3. Symmetrical 2x2 Paired Action Matrix:
             Right Column: نسخة الموبايل (top) + تثبيت التطبيق (bottom / تحتها)
             Left Column (يسارها): السمات (top) + الرجوع (bottom / ثم الرجوع) -->
        <?php if ($is_dashboard_page): ?>
        <div id="magaiees-dashboard-header-stack" class="header-quad-matrix">
            <!-- العمود الأيمن: تثبيت التطبيق -->
            <div class="quad-matrix-col quad-col-right">
                <button id="magaiees-top-install-btn" type="button" onclick="if(window.PWAManager && PWAManager.triggerInstall){PWAManager.triggerInstall();}else{alert('لتثبيت تطبيق السيطرة: اضغط على أيقونة التثبيت ⊕ في شريط عنوان المتصفح بالأعلى');}" class="magaiees-stack-btn stack-btn-install" title="تثبيت تطبيق السيطرة على جهازك كبرنامج مستقل">
                    <span class="stack-btn-icon"><i class="fa-solid fa-download"></i></span>
                    <span class="stack-btn-text">تثبيت التطبيق 📲</span>
                </button>
            </div>

            <!-- العمود الأيسر (يسارها): السمات (13 لوناً) وتحتها الرجوع بالتسلسل -->
            <div class="quad-matrix-col quad-col-left">
                <button id="magaiees-master-themes-btn" type="button" class="magaiees-stack-btn stack-btn-themes magaiees-theme-btn-top" onclick="if(typeof window.openUniversalErpTonePopover==='function'){window.openUniversalErpTonePopover(this);}else{openMaster7ThemesModal();}" title="🎨 السمات ونغمات النظام المعتمدة والموحدة (13 لوناً + 4 نغمات)">
                    <span class="theme-icon-art" style="font-size: 0.9rem;">🎨</span>
                    <span class="theme-btn-text">السمات</span>
                    <span class="theme-count-badge">13</span>
                </button>
                <button id="magaiees-master-screen-ergo-btn" type="button" class="magaiees-stack-btn stack-btn-themes" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.openPopover(this)" title="📐 ضبط مقاسات أنواع الشاشات وراحة العين (تلقائي / لابتوب / كمبيوتر مكتبي / طولية — للشاشة أو البرنامج كله)">
                    <span style="font-size: 0.88rem;">🖥️</span>
                    <span class="theme-btn-text" id="magaies-ergo-hdr-lbl">مقاس الشاشة</span>
                </button>

                <button id="magaiees-master-back-btn" class="magaiees-stack-btn stack-btn-back magaiees-back-btn" onclick="if(typeof window.magaiesGoBackStepLIFO==='function'){window.magaiesGoBackStepLIFO();}else{magaieesGoBack();}" title="الرجوع للخلف خطوة واحدة مع حفظ الحالة (LIFO)">
                    <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: 13px; height: 13px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 19 12 12 5"></polyline>
                    </svg>
                    <span class="back-btn-text">رجوع</span>
                </button>
            </div>
        </div>
        <?php else: ?>
        <div id="magaiees-dashboard-header-stack" class="header-quad-matrix header-duo-matrix">
            <div class="quad-matrix-col quad-col-left">
                <button id="magaiees-master-themes-btn" type="button" class="magaiees-stack-btn stack-btn-themes magaiees-theme-btn-top" onclick="if(typeof window.openUniversalErpTonePopover==='function'){window.openUniversalErpTonePopover(this);}else{openMaster7ThemesModal();}" title="🎨 السمات ونغمات النظام المعتمدة والموحدة (13 لوناً + 4 نغمات)">
                    <span class="theme-icon-art" style="font-size: 0.9rem;">🎨</span>
                    <span class="theme-btn-text">السمات</span>
                    <span class="theme-count-badge">13</span>
                </button>
                <button id="magaiees-master-screen-ergo-btn" type="button" class="magaiees-stack-btn stack-btn-themes" onclick="window.MagaiesScreenErgonomics&&window.MagaiesScreenErgonomics.openPopover(this)" title="📐 ضبط مقاسات أنواع الشاشات وراحة العين (تلقائي / لابتوب / كمبيوتر مكتبي / طولية — للشاشة أو البرنامج كله)">
                    <span style="font-size: 0.88rem;">🖥️</span>
                    <span class="theme-btn-text" id="magaies-ergo-hdr-lbl">مقاس الشاشة</span>
                </button>

                <button id="magaiees-master-back-btn" class="magaiees-stack-btn stack-btn-back magaiees-back-btn" onclick="if(typeof window.magaiesGoBackStepLIFO==='function'){window.magaiesGoBackStepLIFO();}else{magaieesGoBack();}" title="الرجوع للخلف خطوة واحدة مع حفظ الحالة (LIFO)">
                    <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: 13px; height: 13px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 19 12 12 5"></polyline>
                    </svg>
                    <span class="back-btn-text">رجوع</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
        <div id="magaies-quartet-header-slot" style="display:flex; align-items:center;"></div>
        <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error("Error enabling full-screen:", err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
        
        // Theme initialization and toggle logic
        if (localStorage.getItem('erp_theme') === 'light') {
            document.documentElement.classList.add('light-theme');
        }
        function toggleTheme() {
            var isCurrentlyLight = document.documentElement.classList.contains('light-theme');
            var nextTheme = isCurrentlyLight ? 'midnight_obsidian' : 'warm_cream';
            if (typeof applyMasterTheme === 'function') {
                applyMasterTheme(nextTheme);
            } else {
                document.documentElement.classList.toggle('light-theme');
                localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
                window.dispatchEvent(new Event('themeChanged'));
            }
        }
        </script>
    </div>
</header>

<!-- 🌟 الشريط السفلي الموحد لمعلومات النظام والأدمن والمتصلين وبروتوكول الواتساب (ثابت داخل الشاشة وليس عائماً فوق الأشرطة) -->
<footer id="magaiees-bottom-protocol-bar" class="no-print" style="position:relative; width:100%; height:26px; margin-top:12px; border-radius:8px; background:linear-gradient(90deg, rgba(2, 6, 23, 0.92) 0%, rgba(15, 23, 42, 0.95) 50%, rgba(2, 6, 23, 0.92) 100%); border:1px solid rgba(255,255,255,0.08); z-index:1; display:flex; align-items:center; justify-content:space-between; padding:0 14px; font-family:'Tajawal', sans-serif; font-size:11px; color:#94a3b8; direction:rtl; user-select:none; box-sizing:border-box;">
    <!-- اليمين: معلومات الأدمن والمستخدم -->
    <div style="display:flex; align-items:center; gap:8px;">
        <span onclick="var p=document.getElementById('profile-modal'); if(p) p.style.display='flex';" style="cursor:pointer; display:inline-flex; align-items:center; gap:5px; color:#e2e8f0; font-weight:800;" title="الملف الشخصي وصلاحيات الحساب">
            <span>👤</span>
            <span id="header-user-name"><script>try{var _u=JSON.parse(localStorage.getItem('user')||'{}');document.write(_u.fullName||_u.username||'مستخدم')}catch(e){document.write('مستخدم')}</script></span>
            <span id="header-user-role" style="font-size:9.5px; font-weight:900; color:#fde047; background:rgba(245,158,11,0.2); border:1px solid rgba(251,191,36,0.45); padding:1px 5px; border-radius:4px;">مدير النظام</span>
        </span>
    </div>

    <!-- الوسط: حالة الاتصال وعدد المتصلين وبروتوكول المزامنة -->
    <div style="display:flex; align-items:center; gap:10px;">
        <div id="online-users-badge" onclick="alert('المستخدمين المتصلين حالياً: \n' + (window.onlineUsersList || []).join('\n'))" style="cursor:pointer; display:inline-flex; align-items:center; gap:5px; color:#34d399; font-weight:800;" title="المتصلون الآن">
            <span class="online-pulse-dot" style="width:6px; height:6px; background:#10b981; border-radius:50%; box-shadow:0 0 6px #10b981; display:inline-block;"></span>
            <span class="online-label-txt">متصل الآن:</span>
            <span id="online-users-count" style="color:#ffffff; background:rgba(16,185,129,0.25); border:1px solid #10b981; padding:0 5px; border-radius:4px; font-weight:900;">1</span>
        </div>
        <span style="color:rgba(255,255,255,0.18);">|</span>
        <span style="color:#cbd5e1; font-weight:700;">🌐 بروتوكول السيطرة السحابي الموحد (Cloud Sync 24/7)</span>
    </div>

    <!-- اليسار: بروتوكول واتساب المالي الذكي -->
    <div style="display:flex; align-items:center; gap:8px;">
        <span style="color:#38bdf8; font-weight:800;">💬 بروتوكول واتساب المالي الذكي (WhatsApp Suite v2026)</span>
    </div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var f = document.getElementById('magaiees-bottom-protocol-bar');
    var m = document.querySelector('.dash-main') || document.querySelector('main') || document.body;
    if (f && m) m.appendChild(f);
});
</script>

<!-- 🌟 زر عائم ذكي لإظهار الترويسة العلوية في أي وقت لجميع الشاشات -->
<div id="auto-scroll-header-pill" class="auto-scroll-return-pill" onclick="if(window.AutoHeaderScroll) window.AutoHeaderScroll.scrollToHeader()" title="الرجوع إلى أعلى الصفحة وإظهار الترويسة">
    <span class="pill-arrow">▲</span>
    <span>إظهار الترويسة والمعلومات</span>
</div>

<!-- 📺 SACRED TV & QURAN POPUP MODAL (ON SCREEN FACE - NOT HIDDEN FOR ANY REASON) -->
<div id="header-media-backdrop" onclick="closeHeaderMediaPopover(event)" style="display: none;"></div>
<div id="header-media-popover" class="header-tv-popover" onclick="event.stopPropagation()" style="display: none;">
    <!-- Popover Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.12); padding-bottom: 8px; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 13px; color: #fde047;">
            <span id="popover-status-dot" class="header-tv-status-dot"></span>
            <span>📺 تحكم البث المباشر (فوق الشاشة)</span>
        </div>
        <button type="button" onclick="closeHeaderMediaPopover(event)" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); color: #f8fafc; border-radius: 8px; width: 26px; height: 26px; font-size: 13px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="إخفاء النافذة (مع استمرار البث بالخلفية)">✕</button>
    </div>

    <!-- 🌟 PROMINENT CURRENT BROADCAST STATUS CARD (البث الحالي واضح وغير مختفي) 🌟 -->
    <div id="popover-broadcast-status-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(15, 23, 42, 0.98)); border: 1.5px solid rgba(245, 158, 11, 0.55); border-radius: 14px; padding: 10px 12px; margin-bottom: 12px; box-shadow: inset 0 0 15px rgba(245, 158, 11, 0.15);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
            <span style="font-size: 11px; font-weight: 800; color: #94a3b8;">📡 حالة البث الحالية:</span>
            <span id="popover-status-badge" style="font-size: 10.5px; font-weight: 900; padding: 2px 8px; border-radius: 6px; background: rgba(16, 185, 129, 0.25); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.5);">
                🟢 شغال الآن
            </span>
        </div>
        <div id="popover-media-title" style="font-size: 13.5px; font-weight: 900; color: #ffffff; line-height: 1.35; text-shadow: 0 0 12px rgba(245, 158, 11, 0.6); word-break: break-word;">
            البث المباشر يعمل بالخلفية
        </div>
    </div>

    <!-- 12 Sacred & News Channel Quick Buttons in 2 Rows of 6 (صفين كل صف به 6 أزرار عصرية) -->
    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 4px; margin-bottom: 12px;">
        <!-- Row 1: مكة · المدينة · المجد · القرآن · الجزيرة · BBC -->
        <button type="button" id="hdr-ch-makkah" onclick="quickHeaderPlay('makkah')" class="header-pop-ch-btn" style="--ch-c: #f59e0b;" title="تشغيل مكة المكرمة">
            <span style="font-size: 14px;">🕋</span>
            <span>مكة</span>
        </button>
        <button type="button" id="hdr-ch-madinah" onclick="quickHeaderPlay('madinah')" class="header-pop-ch-btn" style="--ch-c: #10b981;" title="تشغيل المدينة المنورة">
            <span style="font-size: 14px;">🕌</span>
            <span>المدينة</span>
        </button>
        <button type="button" id="hdr-ch-majd" onclick="quickHeaderPlay('majd')" class="header-pop-ch-btn" style="--ch-c: #fbbf24;" title="تشغيل قناة المجد">
            <span style="font-size: 14px;">📺</span>
            <span>المجد</span>
        </button>
        <button type="button" id="hdr-ch-quran_mp3" onclick="quickHeaderPlay('quran_mp3')" class="header-pop-ch-btn" style="--ch-c: #06b6d4;" title="تشغيل القرآن MP3">
            <span style="font-size: 14px;">🎧</span>
            <span>القرآن</span>
        </button>
        <button type="button" id="hdr-ch-aljazeera" onclick="quickHeaderPlay('aljazeera')" class="header-pop-ch-btn" style="--ch-c: #ef4444;" title="قناة الجزيرة - البث الصوتي المباشر">
            <span style="font-size: 14px;">🔴</span>
            <span>الجزيرة</span>
        </button>
        <button type="button" id="hdr-ch-bbc_arabic" onclick="quickHeaderPlay('bbc_arabic')" class="header-pop-ch-btn" style="--ch-c: #f43f5e;" title="قناة وإذاعة بي بي سي عربي - بث مباشر باللغة العربية 24/7">
            <span style="font-size: 14px;">🌐</span>
            <span>BBC</span>
        </button>

        <!-- Row 2: البخاري · مسلم · رياض الصالحين · الشمائل · السيرة · الصحابة -->
        <button type="button" id="hdr-ch-hadith_bukhari" onclick="quickHeaderPlay('hadith_bukhari')" class="header-pop-ch-btn" style="--ch-c: #a855f7;" title="صحيح البخاري">
            <span style="font-size: 14px;">📜</span>
            <span>البخاري</span>
        </button>
        <button type="button" id="hdr-ch-hadith_muslim" onclick="quickHeaderPlay('hadith_muslim')" class="header-pop-ch-btn" style="--ch-c: #10b981;" title="صحيح مسلم">
            <span style="font-size: 14px;">📗</span>
            <span>مسلم</span>
        </button>
        <button type="button" id="hdr-ch-hadith_riyad" onclick="quickHeaderPlay('hadith_riyad')" class="header-pop-ch-btn" style="--ch-c: #059669;" title="رياض الصالحين">
            <span style="font-size: 14px;">🌿</span>
            <span>الرياض</span>
        </button>
        <button type="button" id="hdr-ch-hadith_shmaeel" onclick="quickHeaderPlay('hadith_shmaeel')" class="header-pop-ch-btn" style="--ch-c: #c084fc;" title="الشمائل المحمدية">
            <span style="font-size: 14px;">✨</span>
            <span>الشمائل</span>
        </button>
        <button type="button" id="hdr-ch-hadith_siyra" onclick="quickHeaderPlay('hadith_siyra')" class="header-pop-ch-btn" style="--ch-c: #f59e0b;" title="في ظلال السيرة النبوية">
            <span style="font-size: 14px;">🕌</span>
            <span>السيرة</span>
        </button>
        <button type="button" id="hdr-ch-hadith_sahabah" onclick="quickHeaderPlay('hadith_sahabah')" class="header-pop-ch-btn" style="--ch-c: #38bdf8;" title="صور من حياة الصحابة">
            <span style="font-size: 14px;">👥</span>
            <span>الصحابة</span>
        </button>
    </div>

    <!-- Volume & Stop Controls -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; background: rgba(0,0,0,0.5); padding: 8px 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 7px;">
            <span style="font-size: 14px; color: #94a3b8;">🔊</span>
            <input id="header-media-vol" type="range" min="0" max="1" step="0.05" value="1" oninput="setHeaderMediaVolume(this.value)" style="width: 85px; height: 5px; accent-color: #f59e0b; cursor: pointer;">
        </div>
        <button type="button" onclick="quickHeaderStop(event)" style="background: rgba(225, 29, 72, 0.35); border: 1.5px solid rgba(244, 63, 94, 0.6); color: #ffffff; padding: 5px 12px; border-radius: 9px; font-size: 12px; font-weight: 900; cursor: pointer; transition: 0.2s;" title="إيقاف البث تماماً">⏹️ إيقاف</button>
    </div>

    <!-- Companion Window & Full Studio Links -->
    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 2px;">
        <button type="button" onclick="openBackgroundPopout()" style="width: 100%; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; border-radius: 9px; padding: 6px 10px; font-size: 11.5px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: 0.2s;" title="تشغيل في نافذة منفصلة مستقلة تماماً بدون أي انقطاع">
            <span>🗗</span>
            <span>تشغيل بنافذة مستقلة (صفر انقطاع نهائياً)</span>
        </button>
        <a href="engineering_tools.php?tab=quran_mp3" style="display: block; text-align: center; font-size: 11.5px; font-weight: 900; color: #a78bfa; text-decoration: none; padding-top: 2px;" class="hover:underline">
            📖 استوديو القرآن الكريم والشاشات الكاملة ↗
        </a>
    </div>
</div>
<script src="js/auto-charts-domains-2027.js?v=<?php echo file_exists(__DIR__ . '/../js/auto-charts-domains-2027.js') ? filemtime(__DIR__ . '/../js/auto-charts-domains-2027.js') : '2027'; ?>" defer></script>
<script src="js/auto-charts.js?v=<?php echo file_exists(__DIR__ . '/../js/auto-charts.js') ? filemtime(__DIR__ . '/../js/auto-charts.js') : '2027'; ?>" defer></script>
<?php if (basename($_SERVER['PHP_SELF'] ?? '') === 'engineering_tools.php'): ?>
<script src="js/quran_reciters_data.js?v=<?php echo file_exists(__DIR__ . '/../js/quran_reciters_data.js') ? filemtime(__DIR__ . '/../js/quran_reciters_data.js') : '2027'; ?>" defer></script>
<?php endif; ?>
<script>
(function(){
    var bgmVer = "<?php echo file_exists(__DIR__ . '/../js/global_background_media.js') ? filemtime(__DIR__ . '/../js/global_background_media.js') : '2027'; ?>";
    var bgmLoaded = false, bgmQueue = [];
    window.ensureGlobalBgMedia = function(cb) {
        if (bgmLoaded && typeof window.quickHeaderPlay === 'function') { if (cb) cb(); return; }
        if (cb) bgmQueue.push(cb);
        if (document.querySelector('script[data-lazy-bgm="1"]')) return;
        var s = document.createElement('script');
        s.src = 'js/global_background_media.js?v=' + bgmVer;
        s.dataset.lazyBgm = '1';
        s.onload = function() {
            bgmLoaded = true;
            var q = bgmQueue.splice(0);
            for (var i = 0; i < q.length; i++) { try { q[i](); } catch(_) {} }
        };
        document.body.appendChild(s);
    };
    ['quickHeaderPlay','setHeaderMediaVolume','quickHeaderStop','openBackgroundPopout','toggleHeaderMediaDropdown','playPersistentMedia'].forEach(function(fn){
        if (typeof window[fn] !== 'function') {
            window[fn] = function() {
                var args = arguments;
                window.ensureGlobalBgMedia(function(){ if (typeof window[fn] === 'function') window[fn].apply(window, args); });
            };
        }
    });
    try {
        if (localStorage.getItem('erp_bg_media_playing') === '1' || localStorage.getItem('magaies_live_tv_active') === '1') {
            window.addEventListener('DOMContentLoaded', function(){ setTimeout(function(){ window.ensureGlobalBgMedia(); }, 300); }, { once: true });
        }
    } catch(_) {}
})();
</script>
<script src="js/magaiees_back.js?v=<?php echo file_exists(__DIR__ . '/../js/magaiees_back.js') ? filemtime(__DIR__ . '/../js/magaiees_back.js') : time(); ?>" defer></script>
<script src="js/md-delayed-tooltips.js?v=<?php echo file_exists(__DIR__ . '/../js/md-delayed-tooltips.js') ? filemtime(__DIR__ . '/../js/md-delayed-tooltips.js') : time(); ?>" defer></script>
<script>
(function(){
    var guideLoaded = false, guideVer = "<?php echo file_exists(__DIR__ . '/../js/magaies-smart-guide.js') ? filemtime(__DIR__ . '/../js/magaies-smart-guide.js') : '2027'; ?>";
    window.loadMagaiesSmartGuide = function(cb) {
        if (guideLoaded) { if (cb) cb(); return; }
        guideLoaded = true;
        var s = document.createElement('script');
        s.src = 'js/magaies-smart-guide.js?v=' + guideVer;
        s.onload = function() { if (cb) cb(); };
        document.body.appendChild(s);
    };
    window.addEventListener('keydown', function(e) { if (e.key === 'F1') { e.preventDefault(); window.loadMagaiesSmartGuide(); } });
    window.addEventListener('contextmenu', function() { window.loadMagaiesSmartGuide(); }, { once: true, passive: true });
})();
</script>
<script src="js/magaiees-royal-buttons.js?v=<?php echo file_exists(__DIR__ . '/../js/magaiees-royal-buttons.js') ? filemtime(__DIR__ . '/../js/magaiees-royal-buttons.js') : time(); ?>" defer></script>
<script src="js/auto-header-scroll.js?v=<?php echo file_exists(__DIR__ . '/../js/auto-header-scroll.js') ? filemtime(__DIR__ . '/../js/auto-header-scroll.js') : time(); ?>" defer></script>
<script src="js/magaies-guardian-vault.js?v=<?php echo file_exists(__DIR__ . '/../js/magaies-guardian-vault.js') ? filemtime(__DIR__ . '/../js/magaies-guardian-vault.js') : '2027'; ?>" defer></script>


<!-- ═══════════════════════════════════════════════════════════════════
     🎨 MASTER 7 THEMES MODAL (نافذة السمات السبعة المعتمدة الموحدة)
     ═══════════════════════════════════════════════════════════════════ -->
<div id="master7ThemesModal" class="m7-modal-backdrop" onclick="if(event.target===this) closeMaster7ThemesModal();" style="display:none;">
    <div class="m7-modal-dialog" onclick="event.stopPropagation();">
        
        <!-- Header -->
        <div class="m7-modal-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="m7-header-icon-box">🎨</div>
                <div>
                    <h2 class="m7-modal-title">السمات السبعة المعتمدة والموحدة</h2>
                    <p class="m7-modal-subtitle">7 سمات رئيسية مدروسة بعناية لأعلى تباين وراحة بصرية • مطبقة بالتطابق على كافة الشاشات</p>
                </div>
            </div>
            <button type="button" class="m7-close-btn" onclick="closeMaster7ThemesModal()" title="إغلاق">✕</button>
        </div>

        <!-- Filter Tabs -->
        <div class="m7-filter-bar">
            <button type="button" class="m7-tab-btn active" data-cat="all" onclick="filterMaster7ThemeCategory('all')">
                <span>الكل</span>
                <span class="m7-badge">7</span>
            </button>
            <button type="button" class="m7-tab-btn" data-cat="light" onclick="filterMaster7ThemeCategory('light')">
                <span>☀️ الفاتحة والنهارية</span>
                <span class="m7-badge">3</span>
            </button>
            <button type="button" class="m7-tab-btn" data-cat="dark" onclick="filterMaster7ThemeCategory('dark')">
                <span>🌙 الداكنة والمسائية</span>
                <span class="m7-badge">4</span>
            </button>
        </div>

        <!-- Themes Grid (The 7 Master Themes) -->
        <div class="m7-themes-grid">

            <!-- 1. Warm Paper Beige (الفاتح البيج) -->
            <div class="m7-theme-card" data-cat="light" data-key="warm_cream" onclick="applyMasterTheme('warm_cream')" style="--t-border:#b45309; --t-bg:#fbf7ee; --t-text:#1c1917;">
                <div class="m7-card-head">
                    <span class="m7-card-name">📜 ورق سيبيا وبيج دافئ (Beige)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#fbf7ee; border:1px solid #b45309;"></span>
                        <span style="background:#b45309;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">لون بيج عاجي وورقي مريح جداً للأعين يمتص الوهج الأزرق. الخطوط باللون البني الداكن الحاد لأعلى تباين ومطابقة.</p>
                <div class="m7-card-foot">Warm Beige • فاتح نهاري مريح للعين</div>
            </div>

            <!-- 2. Ultra-Clean White -->
            <div class="m7-theme-card" data-cat="light" data-key="ultra_white" onclick="applyMasterTheme('ultra_white')" style="--t-border:#2563eb; --t-bg:#ffffff; --t-text:#0f172a;">
                <div class="m7-card-head">
                    <span class="m7-card-name">⚪ أبيض مكتبي ناصع (Ultra White)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#ffffff; border:1px solid #2563eb;"></span>
                        <span style="background:#2563eb;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">إضاءة مكتبية ناصعة بأعلى نقاء مكتبي، مع خطوط كحلية داكنة شديدة الوضوح والحدة.</p>
                <div class="m7-card-foot">Ultra White • نهاري مكتبي ناصع</div>
            </div>

            <!-- 3. Cool Executive Gray -->
            <div class="m7-theme-card" data-cat="light" data-key="exec_gray" onclick="applyMasterTheme('exec_gray')" style="--t-border:#1d4ed8; --t-bg:#f1f5f9; --t-text:#0f172a;">
                <div class="m7-card-head">
                    <span class="m7-card-name">💼 رمادي تنفيذي بارد (Exec Gray)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#f1f5f9; border:1px solid #1d4ed8;"></span>
                        <span style="background:#1d4ed8;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">أسطح رمادية بلاتينية هادئة تعكس أناقة البنوك والشركات الكبرى بدون انعكاسات ضوئية.</p>
                <div class="m7-card-foot">Executive Gray • رمادي تنفيذي مصرفي</div>
            </div>

            <!-- 4. Royal Midnight Navy (الكحلي) -->
            <div class="m7-theme-card" data-cat="dark" data-key="midnight_obsidian" onclick="applyMasterTheme('midnight_obsidian')" style="--t-border:#00f2fe; --t-bg:#0a1128; --t-text:#ffffff;">
                <div class="m7-card-head">
                    <span class="m7-card-name">💎 كحلي فاحم ملكي (Midnight Navy)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#0a1128; border:1px solid #00f2fe;"></span>
                        <span style="background:#00f2fe;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">السمة المصرفية الفخمة باللون الكحلي السحيق، مع خطوط بيضاء وسماوية مضيئة متباينة بوضوح.</p>
                <div class="m7-card-foot">Midnight Navy • كحلي ملكي فخم</div>
            </div>

            <!-- 5. Mocha Royal Brown (البني) -->
            <div class="m7-theme-card" data-cat="dark" data-key="mocha_brown" onclick="applyMasterTheme('mocha_brown')" style="--t-border:#f59e0b; --t-bg:#1a0f0a; --t-text:#fffbeb;">
                <div class="m7-card-head">
                    <span class="m7-card-name">☕ بني ملكي دافئ (Mocha Brown)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#1a0f0a; border:1px solid #f59e0b;"></span>
                        <span style="background:#f59e0b;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">بني شوكولاتة داكن ودافئ فاخر جداً، مع خطوط عاجية وذهبية واضحة ومتباينة تماماً لمنع أي عتمة.</p>
                <div class="m7-card-foot">Mocha Brown • بني ملكي دافئ فاخر</div>
            </div>

            <!-- 6. Pure OLED Black (الأسود) -->
            <div class="m7-theme-card" data-cat="dark" data-key="pure_oled" onclick="applyMasterTheme('pure_oled')" style="--t-border:#10b981; --t-bg:#000000; --t-text:#ffffff;">
                <div class="m7-card-head">
                    <span class="m7-card-name">🖤 سواد مطلق فاحم (Pure OLED Black)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#000000; border:1px solid #10b981;"></span>
                        <span style="background:#10b981;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">سواد حالك 100% لإطفاء بكسلات الشاشة وتوفير طاقة بطارية اللابتوب والجوال بنسبة 30% مع خطوط بيضاء نقية.</p>
                <div class="m7-card-foot">Pure OLED • سواد مطلق وتوفير طاقة</div>
            </div>

            <!-- 7. Twilight Slate -->
            <div class="m7-theme-card" data-cat="dark" data-key="slate_twilight" onclick="applyMasterTheme('slate_twilight')" style="--t-border:#38bdf8; --t-bg:#0f172a; --t-text:#f8fafc;">
                <div class="m7-card-head">
                    <span class="m7-card-name">🌌 سليت فحمي مسائي (Twilight Slate)</span>
                    <span class="m7-active-tag" style="display:none;">✓ مفعلة</span>
                    <div class="m7-swatches">
                        <span style="background:#0f172a; border:1px solid #38bdf8;"></span>
                        <span style="background:#38bdf8;"></span>
                    </div>
                </div>
                <p class="m7-card-desc">رمادي فحمي داكن مائل للزرقة هادئ جداً لأوقات المساء والراحة البصرية بدون سواد تام.</p>
                <div class="m7-card-foot">Twilight Slate • رمادي مسائي هادئ</div>
            </div>

        </div>

        <!-- Footer -->
        <div class="m7-modal-footer">
            <div style="font-size:13px; color:#94a3b8;">
                السمة النشطة حالياً: <strong id="m7CurrentThemeLabel" style="color:#38bdf8;">ورق سيبيا وبيج دافئ</strong>
            </div>
            <button type="button" class="m7-confirm-btn" onclick="closeMaster7ThemesModal()">
                ✓ تم والاحتفاظ بالسمة
            </button>
        </div>

    </div>
</div>

<link rel="stylesheet" id="master-7-themes-final-override" href="css/header-enterprise-bundle.css?v=<?= @filemtime(__DIR__ . '/../css/header-enterprise-bundle.css') ?: '2027' ?>">

<script>
// ═══════════════════════════════════════════════════════════════════
// 🎨 MASTER 7 THEMES JAVASCRIPT CONTROLLER & CASCADE GUARD
// ═══════════════════════════════════════════════════════════════════
const MASTER_7_THEMES_INFO = {
    'warm_cream': { name: 'ورق سيبيا وبيج دافئ (Beige)', isLight: true },
    'ultra_white': { name: 'أبيض مكتبي ناصع (Ultra White)', isLight: true },
    'exec_gray': { name: 'رمادي تنفيذي بارد (Exec Gray)', isLight: true },
    'midnight_obsidian': { name: 'كحلي فاحم ملكي (Midnight Navy)', isLight: false },
    'mocha_brown': { name: 'بني ملكي دافئ (Mocha Brown)', isLight: false },
    'pure_oled': { name: 'سواد مطلق فاحم (Pure OLED Black)', isLight: false },
    'slate_twilight': { name: 'سليت فحمي مسائي (Twilight Slate)', isLight: false }
};

const MASTER_7_THEME_ALIASES = {
    "dark_midnight": "midnight_obsidian", "dark_mocha": "mocha_brown", "dark_oled": "pure_oled", "dark_slate": "slate_twilight",
    "ivory_default": "warm_cream", "royal_bronze": "warm_cream", "platinum_gray": "exec_gray", "crystal_silver": "ultra_white",
    "sage_pearl": "warm_cream", "sky_alabaster": "ultra_white", "rose_blossom": "warm_cream", "warm_mocha_dark": "warm_cream", "deep_slate_dark": "exec_gray",
    "warm_cream": "warm_cream", "beige": "warm_cream", "warm-alabaster": "warm_cream", "light": "warm_cream",
    "ultra_white": "ultra_white", "pure-platinum": "ultra_white", "white": "ultra_white",
    "exec_gray": "exec_gray", "pearl-platinum": "exec_gray",
    "midnight_obsidian": "midnight_obsidian", "midnight-executive": "midnight_obsidian", "navy-kohl": "midnight_obsidian", "navy": "midnight_obsidian", "dark": "midnight_obsidian",
    "mocha_brown": "mocha_brown", "sepia-brown": "mocha_brown", "desert-mocha": "mocha_brown", "brown": "mocha_brown",
    "pure_oled": "pure_oled", "obsidian-charcoal": "pure_oled", "midnight": "pure_oled", "black": "pure_oled",
    "slate_twilight": "slate_twilight", "twilight-indigo": "slate_twilight", "slate": "slate_twilight"
};

function normalizeMaster7ThemeKey(k) {
    if (!k) return 'warm_cream';
    return MASTER_7_THEME_ALIASES[k] || (MASTER_7_THEMES_INFO[k] ? k : 'warm_cream');
}

// Ensure #master-7-themes-final-override stays at the very end of the document cascade
function ensureMaster7OverrideLast() {
    var st = document.getElementById('master-7-themes-final-override');
    if (st && document.body && st.parentElement !== document.body) {
        document.body.appendChild(st);
    }
    var purSt = document.getElementById('pur-clean-theme-style');
    var grnSt = document.getElementById('grn-purchase-constitution-2027-style');
    if (purSt && document.body && document.body.lastElementChild !== purSt) {
        document.body.appendChild(purSt);
    }
    if (grnSt && document.body && document.body.lastElementChild !== grnSt) {
        document.body.appendChild(grnSt);
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        ensureMaster7OverrideLast();
        var ckT = (document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/)||[])[1] || "";
        var cur = normalizeMaster7ThemeKey(decodeURIComponent(ckT) || document.documentElement.getAttribute("data-light-tone") || document.documentElement.getAttribute("data-theme") || localStorage.getItem("magaies_active_theme"));
        applyMasterTheme(cur, false);
    });
} else {
    ensureMaster7OverrideLast();
}

function openMaster7ThemesModal() {
    var modal = document.getElementById('master7ThemesModal');
    if (!modal) return;
    modal.style.display = 'flex';
    syncMaster7ThemeActiveUI();
}

function closeMaster7ThemesModal() {
    var modal = document.getElementById('master7ThemesModal');
    if (modal) modal.style.display = 'none';
}

function filterMaster7ThemeCategory(cat) {
    document.querySelectorAll('.m7-tab-btn').forEach(b => b.classList.remove('active'));
    var activeBtn = document.querySelector('.m7-tab-btn[data-cat="' + cat + '"]');
    if (activeBtn) activeBtn.classList.add('active');

    document.querySelectorAll('.m7-theme-card').forEach(card => {
        if (cat === 'all' || card.getAttribute('data-cat') === cat) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function applyMasterTheme(themeKey, notify = true) {
    var rawInput = themeKey;
    themeKey = normalizeMaster7ThemeKey(themeKey);
    var isLight = MASTER_7_THEMES_INFO[themeKey].isLight;
    var validLightTones = ['ivory_default', 'royal_bronze', 'platinum_gray', 'crystal_silver', 'sage_pearl', 'sky_alabaster', 'rose_blossom', 'warm_mocha_dark', 'deep_slate_dark'];
    var directToneMap = {
        'exec_gray': 'platinum_gray', 'executive-gray': 'platinum_gray', 'gray': 'platinum_gray',
        'ultra_white': 'crystal_silver', 'ultra-white': 'crystal_silver', 'light': 'crystal_silver',
        'warm_cream': 'ivory_default', 'warm-cream': 'ivory_default', 'cream': 'ivory_default'
    };
    var ckTone = (document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/)||[])[1] || "";
    var storedTone = decodeURIComponent(ckTone) || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
    var activeTone = validLightTones.includes(rawInput)
        ? rawInput
        : (isLight
            ? ((notify && directToneMap[rawInput]) ? directToneMap[rawInput] : (validLightTones.includes(storedTone) ? storedTone : (directToneMap[themeKey] || 'ivory_default')))
            : 'dark_midnight');

    // Apply to DOM
    document.documentElement.setAttribute('data-theme', themeKey);
    document.documentElement.setAttribute('data-light-tone', activeTone);
    if (document.body) {
        document.body.setAttribute('data-theme', themeKey);
        document.body.setAttribute('data-light-tone', activeTone);
    }

    if (isLight) {
        document.documentElement.classList.add('light-theme');
        document.documentElement.classList.remove('dark-theme');
        if (document.body) {
            document.body.classList.add('light-theme');
            document.body.classList.remove('dark-theme');
        }
    } else {
        document.documentElement.classList.remove('light-theme');
        document.documentElement.classList.add('dark-theme');
        if (document.body) {
            document.body.classList.remove('light-theme');
            document.body.classList.add('dark-theme');
        }
    }

    ensureMaster7OverrideLast();

    // Save across all unified keys for complete ecosystem consistency (Cookie first + Quota-safe localStorage)
    try {
        document.cookie = 'magaies_light_tone=' + encodeURIComponent(activeTone) + '; path=/; max-age=315360000; SameSite=Lax';
        document.cookie = 'magaies_active_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=315360000; SameSite=Lax';
    } catch(e) {}
    var _saveKey = function(k, v) {
        try { localStorage.setItem(k, v); } catch(_e) {
            try { ['erp_items_v1','erp_global_units_v1','erp_auto_backups','erp_audit_logs'].forEach(function(rk){ localStorage.removeItem(rk); }); localStorage.setItem(k, v); } catch(_e2) {}
        }
    };
    _saveKey('magaies_light_tone', activeTone);
    _saveKey('pur_light_tone_palette', activeTone);
    _saveKey('magaies_active_theme', themeKey);
    _saveKey('magaiees_global_theme', themeKey);
    _saveKey('magaiees_mobile_theme', themeKey);
    _saveKey('ui_theme', themeKey);
    _saveKey('erp_theme', isLight ? 'light' : 'dark');

    syncMaster7ThemeActiveUI();

    // Broadcast event
    if (notify) {
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeKey, tone: activeTone, isLight: isLight } }));
    }
}

// 1-Step Return Pill when navigated from purchase-receipt.php (?from=purchase-receipt.php)
document.addEventListener('DOMContentLoaded', function() {
    try {
        var params = new URLSearchParams(window.location.search);
        var fromGrn = params.get('from') === 'purchase-receipt.php';
        if (fromGrn && !document.getElementById('magaies-grn-return-one-step-btn')) {
            var retBtn = document.createElement('button');
            retBtn.type = 'button';
            retBtn.id = 'magaies-grn-return-one-step-btn';
            retBtn.innerHTML = '↩️ رجوع خطوة واحدة إلى شاشة استلام البضائع';
            retBtn.style.cssText = 'position:fixed; bottom:20px; left:24px; z-index:999990; padding:10px 18px; border-radius:14px; font-family:Cairo,Tajawal,sans-serif; font-size:0.95rem; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color:#000000; -webkit-text-fill-color:#000000; border:2.5px solid #78350f; box-shadow:0 10px 26px rgba(0,0,0,0.35), inset 0 2px 0 rgba(255,255,255,0.65); transition:transform 0.18s ease;';
            retBtn.onclick = function() {
                if (window.history.length > 1 && document.referrer && document.referrer.indexOf('purchase-receipt.php') !== -1) {
                    window.history.back();
                } else {
                    window.location.href = 'purchase-receipt.php';
                }
            };
            document.body.appendChild(retBtn);
        }
    } catch (_e) {}
});

function syncMaster7ThemeActiveUI() {
    var cur = normalizeMaster7ThemeKey(
        document.documentElement.getAttribute('data-theme') || 
        localStorage.getItem('magaies_active_theme') || 
        'warm_cream'
    );

    var info = MASTER_7_THEMES_INFO[cur] || MASTER_7_THEMES_INFO['warm_cream'];
    var lbl = document.getElementById('m7CurrentThemeLabel');
    if (lbl) lbl.textContent = info.name;

    document.querySelectorAll('.m7-theme-card').forEach(card => {
        var k = card.getAttribute('data-key');
        var tag = card.querySelector('.m7-active-tag');
        if (k === cur) {
            card.classList.add('active-card');
            if (tag) tag.style.display = 'inline-block';
        } else {
            card.classList.remove('active-card');
            if (tag) tag.style.display = 'none';
        }
    });
}

// Global keyboard shortcut: Alt + T opens Themes Modal
window.addEventListener('keydown', function(e) {
    if (e.altKey && (e.key === 't' || e.key === 'T' || e.key === 'ف')) {
        e.preventDefault();
        openMaster7ThemesModal();
    }
});
</script>
<?php
// ═══════════════════════════════════════════════════════════════════════════
// 🔄 المزامنة الفورية لإشعارات الواتساب البنكية والعملاء والبنوك مع كشف الحساب
// ═══════════════════════════════════════════════════════════════════════════
$_hdrSyncCustomers = [];
$_hdrSyncBanks = [];
$_hdrSyncReceipts = [];
$_hdrTid = 0;
$_hdrSyncSig = '0';
$_hdrNeedInlineDump = (basename($_SERVER['PHP_SELF'] ?? '') === 'account-statement.php');
try {
    if (function_exists('getDBConnection') && (!isset($pdo) || !$pdo || empty($GLOBAL_TENANT_ID))) {
        $pdo = getDBConnection();
    }
    $_hdrTid = function_exists('resolveCurrentTenantId') ? resolveCurrentTenantId($pdo ?? null) : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 0);
    if (isset($pdo) && $pdo && $_hdrTid > 0) {
        if ($_hdrNeedInlineDump) {
            $stmtC = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmtC->execute([$_hdrTid]);
            while ($rc = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                $dc = json_decode($rc['data'] ?? '', true);
                if ($dc && !empty($dc['id']) && empty($dc['deleted'])) $_hdrSyncCustomers[$dc['id']] = $dc;
            }
            $stmtB = $pdo->prepare("SELECT data FROM banks WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmtB->execute([$_hdrTid]);
            while ($rb = $stmtB->fetch(PDO::FETCH_ASSOC)) {
                $db = json_decode($rb['data'] ?? '', true);
                if ($db && !empty($db['id']) && empty($db['deleted'])) $_hdrSyncBanks[$db['id']] = $db;
            }
            $stmtR = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmtR->execute([$_hdrTid]);
            while ($rr = $stmtR->fetch(PDO::FETCH_ASSOC)) {
                $dr = json_decode($rr['data'] ?? '', true);
                if ($dr && empty($dr['deleted']) && (!empty($dr['voucherNo']) || !empty($dr['id']))) {
                    $k = $dr['voucherNo'] ?? $dr['id'];
                    $_hdrSyncReceipts[$k] = $dr;
                }
            }
            $_hdrSyncSig = md5(count($_hdrSyncCustomers) . ':' . count($_hdrSyncBanks) . ':' . count($_hdrSyncReceipts) . ':' . implode(',', array_keys($_hdrSyncReceipts)));
        } else {
            $stmtSig = $pdo->prepare("SELECT (SELECT COUNT(*) FROM customers WHERE tenant_id = ? AND COALESCE(is_deleted,0)=0) AS cc, (SELECT COUNT(*) FROM banks WHERE tenant_id = ? AND COALESCE(is_deleted,0)=0) AS bc, (SELECT COUNT(*) FROM receipts WHERE tenant_id = ? AND COALESCE(is_deleted,0)=0) AS rc, (SELECT COALESCE(MAX(id),0) FROM receipts WHERE tenant_id = ?) AS rmax");
            $stmtSig->execute([$_hdrTid, $_hdrTid, $_hdrTid, $_hdrTid]);
            $sigRow = $stmtSig->fetch(PDO::FETCH_ASSOC) ?: [];
            $_hdrSyncSig = md5(($sigRow['cc'] ?? 0) . ':' . ($sigRow['bc'] ?? 0) . ':' . ($sigRow['rc'] ?? 0) . ':' . ($sigRow['rmax'] ?? 0));
        }
    }
} catch (Throwable $_eSync) {}
?>
<script id="erp-live-whatsapp-receipts-sync">
(function() {
    var srvTenantId  = <?= (int)$_hdrTid ?>;
    var srvSyncSig   = <?= json_encode($_hdrSyncSig) ?>;
    var srvCustomers = <?= json_encode(array_values($_hdrSyncCustomers), JSON_UNESCAPED_UNICODE) ?>;
    var srvBanks     = <?= json_encode(array_values($_hdrSyncBanks), JSON_UNESCAPED_UNICODE) ?>;
    var srvReceipts  = <?= json_encode(array_values($_hdrSyncReceipts), JSON_UNESCAPED_UNICODE) ?>;

    function mergeArrayIntoLocal(baseKey, serverItems, idField, altIdField) {
        if (!Array.isArray(serverItems) || serverItems.length === 0) return;
        var tId = '';
        try {
            var u = JSON.parse(localStorage.getItem('user') || '{}');
            tId = String(u.tenant_id || localStorage.getItem('currentTenantId') || '').trim();
        } catch(e) {}
        if (!tId || !srvTenantId || tId !== String(srvTenantId)) return;

        var tenantKey = 'tenant_' + tId + '_' + baseKey;
        var localArr = [];
        try {
            localArr = JSON.parse(localStorage.getItem(tenantKey) || '[]');
            if (!Array.isArray(localArr)) localArr = [];
        } catch(e) { localArr = []; }

        var indexMap = {};
        for (var i = 0; i < localArr.length; i++) {
            var item = localArr[i];
            if (!item) continue;
            var k1 = item[idField] ? String(item[idField]) : '';
            var k2 = (altIdField && item[altIdField]) ? String(item[altIdField]) : '';
            if (k1) indexMap[k1] = i;
            if (k2) indexMap[k2] = i;
        }

        var changed = false;
        for (var j = 0; j < serverItems.length; j++) {
            var sItem = serverItems[j];
            if (!sItem) continue;
            var sk1 = sItem[idField] ? String(sItem[idField]) : '';
            var sk2 = (altIdField && sItem[altIdField]) ? String(sItem[altIdField]) : '';
            var existingIdx = (sk1 && indexMap[sk1] !== undefined) ? indexMap[sk1] : ((sk2 && indexMap[sk2] !== undefined) ? indexMap[sk2] : -1);
            if (existingIdx > -1) {
                localArr[existingIdx] = Object.assign({}, localArr[existingIdx], sItem);
                changed = true;
            } else {
                indexMap[sk1 || sk2] = localArr.length;
                localArr.push(sItem);
                changed = true;
            }
        }

        if (changed) {
            var jsonStr = JSON.stringify(localArr);
            try { localStorage.setItem(tenantKey, jsonStr); } catch(e) {}
        }
    }

    var lastSig = '';
    var hasLocalReceipts = false;
    try {
        lastSig = sessionStorage.getItem('erp_hdr_sync_sig') || '';
        hasLocalReceipts = !!localStorage.getItem('erp_receipts_v1');
    } catch(e) {}

    var needsSync = (lastSig !== srvSyncSig) || !hasLocalReceipts || /account-statement\.php/i.test(window.location.pathname);
    if (needsSync) {
        mergeArrayIntoLocal('erp_customers_v1', srvCustomers, 'id', 'name');
        mergeArrayIntoLocal('erp_banks_v1', srvBanks, 'id', 'name');
        mergeArrayIntoLocal('erp_receipts_v1', srvReceipts, 'voucherNo', 'id');
        try { sessionStorage.setItem('erp_hdr_sync_sig', srvSyncSig); } catch(e) {}
    }

    document.addEventListener('DOMContentLoaded', function() {
        var runRebuild = function() {
            if (typeof window.rebuildGLFromAllTables === 'function') {
                try { window.rebuildGLFromAllTables(false); } catch(e) {}
            }
            if (typeof window.buildAccountsList === 'function') {
                try { window.buildAccountsList(); } catch(e) {}
            }
            if (typeof window.extractFilters === 'function') {
                try { window.extractFilters(); } catch(e) {}
            }
        };
        if (!window.__elsajanaC15HeaderInheritanceBound) {
            window.__elsajanaC15HeaderInheritanceBound = true;
            try {
                // 1. حقن القالب الهندسي السيادي الموحد لتنصيف الشاشة بالليزر بعد الشريط الجانبي (الهامش الأيمن === الهامش الأيسر) وتوحيد الخطوط (+2)
                if (!document.getElementById('magaies-sovereign-edge-to-edge-css')) {
                    var sovStyle = document.createElement('style');
                    sovStyle.id = 'magaies-sovereign-edge-to-edge-css';
                    var rootSel = '#pur-proportional-root, #sales-proportional-root, #grn-proportional-root, #scp-proportional-root, #mcp-proportional-root, #pa-proportional-root, #mb-proportional-root, #dmr-proportional-root, #gl-proportional-root, #md-proportional-root, .dash-main, .pos-wrapper, .sgl-master-container, .report-master-container, main.md-content-area, #sl-main-wrapper, .main-container, .page-container, .dashboard-container, .alarm-master-wrapper, .vouchers-container, .hub-container, .analytics-container, .eng-container, .settings-container, body.has-sidebar > main, body.has-sidebar > .container';
                    sovStyle.textContent =
                        ':root { --global-master-scale: 1; --global-master-font-px: 16px; --sb-collapsed: 62px; --sb-expanded: 240px; --magaies-100w-px: 100%; --ophth-unified-w: var(--magaies-100w-px, 100%); --erp-ergo-max-w: var(--magaies-100w-px, 100%); --erp-ergo-wide-w: var(--magaies-100w-px, 100%); --erp-ergo-vpad: 2px; --erp-ergo-grid-min-h: auto; }\n' +
                        '#dash-fc1-smart-ai-command-bar, #curtain-drawer, #desktop-sync-shield-btn, #header-mobile-quick-btn, #magaiees-top-mobile-btn, .msg-quartet-shield, .msg-quartet-mobile { display: none !important; }\n' +
                        'html { box-sizing: border-box !important; width: 100% !important; max-width: 100% !important; min-width: 0 !important; margin: 0 !important; padding: 0 !important; overflow-x: auto !important; }\n' +
                        'body { box-sizing: border-box !important; width: auto !important; min-width: 100% !important; max-width: none !important; min-height: 100vh !important; margin: 0 !important; padding: 0 !important; display: flex !important; flex-direction: column !important; align-items: stretch !important; justify-content: flex-start !important; overflow-x: visible !important; }\n' +
                        'html.has-sidebar body:not(.no-sidebar), body.has-sidebar:not(.no-sidebar), html.has-sidebar body.sidebar-open:not(.no-sidebar), body.has-sidebar.sidebar-open:not(.no-sidebar) { margin-right: var(--sb-collapsed, 62px) !important; margin-left: 0 !important; margin-inline-start: var(--sb-collapsed, 62px) !important; margin-inline-end: 0 !important; padding-inline: 0 !important; width: auto !important; min-width: calc(100% - var(--sb-collapsed, 62px)) !important; max-width: none !important; transition: none !important; }\n' +
                        rootSel + ' { box-sizing: border-box !important; width: var(--magaies-100w-px, 100%) !important; min-width: var(--magaies-100w-px, 0) !important; max-width: var(--magaies-100w-px, 100%) !important; margin-left: auto !important; margin-right: auto !important; margin-inline: auto !important; padding-left: 0 !important; padding-right: 0 !important; left: 0 !important; right: 0 !important; transform-origin: top center !important; }\n' +
                        'html[data-ergo-resolved="desktop_comfort"] #purchase-editor-view, html[data-ergo-resolved="portrait_tall"] #purchase-editor-view { min-height: calc(100dvh - 38px) !important; height: auto !important; }\n' +
                        'html[data-ergo-resolved="desktop_comfort"] #items-grid-wrapper, html[data-ergo-resolved="desktop_comfort"] .table-responsive, html[data-ergo-resolved="desktop_comfort"] .master-pane, html[data-ergo-resolved="desktop_comfort"] #print-container { min-height: var(--erp-ergo-grid-min-h, 58dvh) !important; }\n' +
                        'html[data-ergo-resolved="portrait_tall"] #items-grid-wrapper, html[data-ergo-resolved="portrait_tall"] .table-responsive, html[data-ergo-resolved="portrait_tall"] .master-pane, html[data-ergo-resolved="portrait_tall"] #print-container { min-height: var(--erp-ergo-grid-min-h, 70dvh) !important; }\n' +
                        'html[data-ergo-resolved="desktop_comfort"] tr td, html[data-ergo-resolved="desktop_comfort"] .account-block, html[data-ergo-resolved="desktop_comfort"] .section-block-card { padding-top: calc(10px + var(--erp-ergo-vpad, 0px)); padding-bottom: calc(10px + var(--erp-ergo-vpad, 0px)); }\n' +
                        '#purchase-editor-view, #purchase-hub-view, #new-unified-dashboard-design { width: 100% !important; max-width: 100% !important; margin-inline: auto !important; }\n' +
                        '@media screen and (max-height: 500px) and (orientation: landscape) { body.landscape-clean-100dvh header.premium-header { display: none !important; height: 0 !important; } body.landscape-clean-100dvh .dash-main { min-height: 100dvh !important; padding-top: 2px !important; } }\n';
                    (document.body || document.head).appendChild(sovStyle);
                    try {
                        var _isMob = window.matchMedia && window.matchMedia('(max-width: 768px) and (pointer: coarse)').matches;
                        if (!_isMob) {
                            var _sbW = (document.documentElement.classList.contains('has-sidebar') || (document.body && document.body.classList.contains('has-sidebar') && !document.body.classList.contains('no-sidebar'))) ? 62 : 0;
                            var _avail = (window.screen && window.screen.availWidth) ? window.screen.availWidth : 1366;
                            var _baseW = Math.min((window.outerWidth && window.outerWidth > 300) ? window.outerWidth : _avail, _avail);
                            var _ck = 'magaies_exact_100w_v17_' + _avail + '_' + _sbW;
                            var _w100 = 0;
                            if (Math.abs((window.innerWidth || _baseW) - _baseW) <= 24) {
                                _w100 = Math.max(960, Math.floor((document.documentElement.clientWidth || window.innerWidth) - _sbW));
                                localStorage.setItem(_ck, String(_w100));
                            } else {
                                _w100 = parseInt(localStorage.getItem(_ck) || '0', 10) || Math.max(960, Math.floor(_baseW - 17 - _sbW));
                            }
                            document.documentElement.style.setProperty('--magaies-100w-px', _w100 + 'px');
                        }
                    } catch (_) {}
                }

                var MASTER_SCALE_KEY = 'magaies_global_master_scale_v1';
                var c15Key = 'elsajana_global_screen_scale_v15';
                var mdScaleKey = 'magaies_erp_global_proportional_scale_v15';
                var grnScaleKey = 'elsajana_grn_zoom_scale';
                var slFc1Key = 'magaies_sl_fc1_scale_6_8';
                var GLOBAL_FONT_KEY = 'magaies_global_font_px';
                var viewportBc = ('BroadcastChannel' in window) ? new BroadcastChannel('magaies_global_viewport_font_singleton_v1') : null;

                window.applyGlobalSovereignMasterScale = function(s, skipSave) {
                    var val = parseFloat(s);
                    if (isNaN(val) || val < 0.25 || val > 10.0) return;
                    val = Math.round(val * 100) / 100;
                    document.documentElement.style.setProperty('--global-master-scale', String(val));
                    document.documentElement.style.setProperty('--grn-zoom-scale', String(val));
                    document.documentElement.style.setProperty('--elsajana-global-scale', String(val));
                    document.documentElement.style.setProperty('--md-c1-zoom-scale', String(val));

                    if (!skipSave) {
                        try {
                            localStorage.setItem(MASTER_SCALE_KEY, String(val));
                            localStorage.setItem(c15Key, String(val));
                            localStorage.setItem(mdScaleKey, String(val));
                            localStorage.setItem(grnScaleKey, String(val));
                            localStorage.setItem(slFc1Key, String(val));
                            if (viewportBc) viewportBc.postMessage({ type: 'SCALE_SYNC', scale: val });
                        } catch (e) {}
                    }

                    if (document.body) {
                        if (val > 1.02) document.body.setAttribute('data-zoomed-high', '1');
                        else document.body.removeAttribute('data-zoomed-high');
                    }

                    // إذا كانت الشاشة تحتوي على محركها الخاص (مثل master-data.php) يتم استدعاؤه بتناغم
                    if (typeof window.setMdProportionalZoom === 'function' && !window.__inMdZoomSync) {
                        window.__inMdZoomSync = true;
                        try { window.setMdProportionalZoom(val, 0, true); } catch (e) {}
                        window.__inMdZoomSync = false;
                        return;
                    }

                    var mainEl = document.querySelector('#pur-proportional-root, #sales-proportional-root, #grn-proportional-root, #scp-proportional-root, #mcp-proportional-root, #pa-proportional-root, #mb-proportional-root, #dmr-proportional-root, #gl-proportional-root, #md-proportional-root, .dash-main, #purchase-editor-view, #sl-main-wrapper, .dashboard-container, .alarm-master-wrapper, .vouchers-container, .hub-container, .analytics-container, .eng-container, .settings-container, .main-container, .page-container, main');
                    if (mainEl) {
                        mainEl.style.boxSizing = 'border-box';
                        mainEl.style.transformOrigin = 'top center';
                        mainEl.style.marginInline = 'auto';
                        mainEl.style.marginLeft = 'auto';
                        mainEl.style.marginRight = 'auto';
                        if (val < 0.995) {
                            mainEl.style.zoom = '';
                            mainEl.style.transform = 'scale(' + val + ')';
                            mainEl.style.width = 'var(--erp-ergo-max-w, calc(100% - 28px))';
                            mainEl.style.maxWidth = 'var(--erp-ergo-max-w, calc(100% - 28px))';
                        } else {
                            mainEl.style.transform = '';
                            mainEl.style.zoom = Math.abs(val - 1) < 0.01 ? '' : String(val);
                            mainEl.style.width = 'var(--erp-ergo-max-w, calc(100% - 28px))';
                            mainEl.style.maxWidth = 'var(--erp-ergo-max-w, calc(100% - 28px))';
                        }
                    }
                    if (!skipSave && document.body) {
                        var zb = document.getElementById('magaies-telescopic-zoom-hud');
                        if (!zb) {
                            zb = document.createElement('button');
                            zb.id = 'magaies-telescopic-zoom-hud';
                            zb.type = 'button';
                            zb.title = 'انقر لإعادة مقاس الشاشة الطبيعي (100% = 0)';
                            zb.style.cssText = 'position:fixed;bottom:18px;right:82px;z-index:2147483000;padding:6px 14px;border-radius:999px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fde047;border:2px solid #38bdf8;font-family:Tajawal,Cairo,sans-serif;font-size:0.86rem;font-weight:900;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,0.65);transition:opacity 0.25s;';
                            zb.onclick = function() { window.setMagaiesScreenZoomStep(0); zb.style.display = 'none'; };
                            document.body.appendChild(zb);
                        }
                        zb.textContent = '🖥️ مقاس الشاشة: ' + Math.round(val * 100) + '% ↺';
                        zb.style.display = Math.abs(val - 1) < 0.01 ? 'none' : 'inline-flex';
                    }
                };

                window.setMagaiesScreenZoomStep = function(stepInt, skipToast) {
                    var st = Math.max(-15, Math.min(10, parseInt(stepInt, 10) || 0));
                    try { localStorage.setItem('magaies_screen_zoom_step_v1', String(st)); } catch (_) {}
                    var scaleVal = Math.round((1 + (st * 0.02)) * 1000) / 1000;
                    window.applyGlobalSovereignMasterScale(scaleVal, false);
                    var badge = document.getElementById('cfg-screen-step-badge');
                    var rng = document.getElementById('cfg-screen-step-range');
                    if (rng && String(rng.value) !== String(st)) rng.value = String(st);
                    if (badge) badge.textContent = (st > 0 ? ('+' + st + ' تكبير') : (st < 0 ? (st + ' تصغير') : '0 (الوضع الطبيعي 100%)')) + ' — ' + Math.round(scaleVal * 100) + '%';
                    if (!skipToast && typeof window.showToast === 'function') window.showToast('🖥️ تم ضبط إعدادات الشاشة: ' + (st > 0 ? '+' + st : st) + ' (' + Math.round(scaleVal * 100) + '%)', 'success', 1800);
                };

                try {
                    if (!localStorage.getItem('magaies_ophthalmic_gold_v31_clean')) {
                        [MASTER_SCALE_KEY, c15Key, mdScaleKey, grnScaleKey, slFc1Key, GLOBAL_FONT_KEY, 'magaies_screen_zoom_step_v1', 'magaies_dim_global', 'magaies_ergo_global_mode', 'magaies_ergo_vboost_px', 'magaiees_font_scale', 'magaies_font_scale', 'magaiees_typography_hierarchy_v1', 'magaies_telescopic_zoom_scale'].forEach(function(k) { localStorage.removeItem(k); });
                        Object.keys(localStorage).forEach(function(k) { if (k.indexOf('magaies_dim_page_') === 0 || k.indexOf('magaies_ergo_page_') === 0 || /(_zoom_scale|_prop_zoom|_screen_scale|erp_zoom)/i.test(k)) localStorage.removeItem(k); });
                        localStorage.setItem('magaies_ophthalmic_gold_v31_clean', '1');
                        document.documentElement.style.removeProperty('font-size'); document.documentElement.style.zoom = '';
                        if (document.body) { document.body.style.zoom = ''; document.body.style.transform = ''; }
                        if ('caches' in window) caches.keys().then(function(ks) { ks.forEach(function(n) { caches.delete(n); }); });
                        if ('serviceWorker' in navigator) navigator.serviceWorker.getRegistrations().then(function(rs) { rs.forEach(function(r) { r.update(); }); });
                    }
                } catch (_) {}
                var savedZoomStep = 0;
                try { savedZoomStep = Math.max(-15, Math.min(10, parseInt(localStorage.getItem('magaies_screen_zoom_step_v1') || '0', 10) || 0)); } catch (_) {}
                window.applyGlobalSovereignMasterScale(Math.round((1 + (savedZoomStep * 0.02)) * 1000) / 1000, true);

                if (!window.ElsajanaUniversalUI2027 && !document.querySelector('script[src*="elsajana-universal-ui-2027.js"]')) {
                    var _ergoScr = document.createElement('script');
                    _ergoScr.defer = true;
                    _ergoScr.src = 'js/elsajana-universal-ui-2027.js?v=2027_ophthalmic_gold_v18';
                    (document.head || document.documentElement).appendChild(_ergoScr);
                }
            } catch (e) {}
        }
        if (/account-statement\.php/i.test(window.location.pathname)) {
            runRebuild();
        } else if (needsSync) {
            if (typeof window.requestIdleCallback === 'function') {
                window.requestIdleCallback(runRebuild, { timeout: 400 });
            } else {
                setTimeout(runRebuild, 80);
            }
        }
                if (!window.__magaiesUniversalPermGuard) {
            window.__magaiesUniversalPermGuard = true;
            try {
                var _curPg = (window.location.pathname.split('/').pop() || '').trim();
                if (_curPg && _curPg !== 'login.php' && _curPg !== 'index.php' && _curPg !== 'magaiees_unified_dashboard.php' && window.MagaieesPermissions && typeof window.MagaieesPermissions.getCurrentUser === 'function') {
                    var _uPerm = window.MagaieesPermissions.getCurrentUser();
                    if (_uPerm && typeof window.MagaieesPermissions.canUserAccessScreen === 'function' && !window.MagaieesPermissions.canUserAccessScreen(_curPg, _uPerm)) {
                        alert('⛔ عفواً، ليس لديك صلاحية معتمدة للدخول إلى هذه الشاشة أو التقرير (' + _curPg + ').');
                        window.location.replace('magaiees_unified_dashboard.php');
                        return;
                    }
                    if (_uPerm && typeof window.MagaieesPermissions.enforceScreenSecurity === 'function') {
                        window.MagaieesPermissions.enforceScreenSecurity(_uPerm);
                    }
                }
            } catch (_ep) {}
        }
        // Global Magaiees Bourse Ticker across all screens (when enabled) — 60s sessionStorage cache
        window.renderGlobalScreensBrokerBar = function(forceRefresh) {
            var existing = document.getElementById('global-screens-broker-ticker');
            if (existing) existing.remove();
            if (document.getElementById('market-broker-ticker-wrap')) return;
            if (localStorage.getItem('magaiees_hide_broker_ticker') === '1') return;
            var renderItems = function(res) {
                if (!res || !Array.isArray(res.items) || !res.items.length) return;
                var dock = localStorage.getItem('magaiees_broker_dock_mode') === 'bottom' ? 'bottom' : 'top';
                var bar = document.createElement('div');
                bar.id = 'global-screens-broker-ticker';
                bar.style.cssText = dock === 'bottom'
                    ? 'position:fixed;bottom:2px;left:6px;right:calc(var(--sb-collapsed, 62px) + 6px);z-index:9990;background:#0c1d47;border:1px solid rgba(253,224,71,0.55);border-radius:7px;padding:4px 10px;display:flex;align-items:center;justify-content:space-between;gap:8px;direction:rtl;color:#fde047;font-size:12.5px;font-weight:900;box-shadow:0 4px 14px rgba(0,0,0,0.65);'
                    : 'margin:4px auto;width:calc(100% - 28px);background:#0c1d47;border:1px solid rgba(253,224,71,0.55);border-radius:7px;padding:4px 10px;display:flex;align-items:center;justify-content:space-between;gap:8px;direction:rtl;color:#fde047;font-size:12.5px;font-weight:900;box-shadow:0 4px 14px rgba(0,0,0,0.55);';
                var txt = res.items.slice(0, 8).map(function(it, idx){
                    var clr = (idx % 2 === 0) ? '#fb923c' : '#fde047';
                    var ageM = (it.ts && Number(it.ts) > 1000000000) ? Math.max(0, (Date.now() - Number(it.ts) * 1000) / 60000) : 999;
                    var nBadgeStyle = ageM <= 90
                        ? 'background:#dc2626!important;border:1.5px solid #fca5a5!important;'
                        : (ageM <= 180 ? 'background:#f97316!important;border:1.5px solid #fdba74!important;' : 'background:#16a34a!important;border:1.5px solid #86efac!important;');
                    var nBadge = ageM <= 270 ? '<span style="' + nBadgeStyle + 'color:#fff!important;-webkit-text-fill-color:#fff!important;padding:1px 8px;border-radius:999px;font-size:11px;font-weight:900;margin-left:5px;display:inline-flex;align-items:center;">جديد</span>' : '';
                    var lbl = it.type === 'order' ? '<span style="color:#fb923c;">🟠 طلب:</span> ' : '<span style="color:#4ade80;">🟢 عرض:</span> ';
                    return '<span style="color:' + clr + ';">' + nBadge + lbl + (it.text || '') + '</span>';
                }).join(' <span style="color:#fb923c;">❖</span> ');
                if (!document.getElementById('mkt-global-marquee-kf')) {
                    var stKf = document.createElement('style'); stKf.id = 'mkt-global-marquee-kf';
                    stKf.textContent = '@keyframes mktGlobalMarquee{0%{transform:translate3d(0,0,0)}100%{transform:translate3d(50%,0,0)}}#global-screens-broker-ticker-track{display:inline-flex!important;width:max-content!important;gap:22px!important;white-space:nowrap!important;animation:mktGlobalMarquee 62s linear infinite!important;}#global-screens-broker-ticker:hover #global-screens-broker-ticker-track{animation-play-state:paused!important;}';
                    document.head.appendChild(stKf);
                }
                bar.innerHTML = '<span style="color:#fde047;flex-shrink:0;">🏛️ بورصة السيطرة التجارية:</span><div style="overflow:hidden;white-space:nowrap;flex:1;direction:rtl;"><div id="global-screens-broker-ticker-track"><span>' + txt + '</span><span style="color:#fb923c;">❖</span><span>' + txt + '</span></div></div>'
                    + '<div style="display:flex;gap:4px;flex-shrink:0;">'
                    + '<button type="button" onclick="var d=localStorage.getItem(\'magaiees_broker_dock_mode\')===\'bottom\'?\'top\':\'bottom\';localStorage.setItem(\'magaiees_broker_dock_mode\',d);window.renderGlobalScreensBrokerBar();" style="padding:1px 6px;border-radius:5px;background:#1e293b;color:#fde047;border:1px solid #f59e0b;font-size:10.5px;font-weight:900;cursor:pointer;" title="تبديل الموقع أعلى/أسفل">📍 أعلى/أسفل</button>'
                    + '<button type="button" onclick="window.toggleMagaieesBourseGlobal();" style="padding:1px 6px;border-radius:5px;background:#881337;color:#fecdd3;border:1px solid #f43f5e;font-size:10.5px;font-weight:900;cursor:pointer;" title="إخفاء البورصة">🙈 إخفاء</button>'
                    + '</div>';
                var hdr = document.querySelector('.unified-header');
                if (dock === 'top' && hdr && hdr.parentNode) hdr.parentNode.insertBefore(bar, hdr.nextSibling);
                else document.body.appendChild(bar);
            };
            try {
                var cRaw = !forceRefresh && sessionStorage.getItem('magaies_broker_ticker_cache_v1');
                if (cRaw) {
                    var cObj = JSON.parse(cRaw);
                    if (cObj && cObj.ts && (Date.now() - cObj.ts) < 60000 && cObj.res) { renderItems(cObj.res); return; }
                }
            } catch(_) {}
            fetch('api/market_broker_ticker.php?action=list').then(function(r){ return r.json(); }).then(function(res){
                try { sessionStorage.setItem('magaies_broker_ticker_cache_v1', JSON.stringify({ ts: Date.now(), res: res })); } catch(_) {}
                renderItems(res);
            }).catch(function(){});
        };
        if (!window.toggleMagaieesBourseGlobal) {
            window.toggleMagaieesBourseGlobal = function() {
                var gBar = document.getElementById('global-screens-broker-ticker');
                if (gBar) {
                    localStorage.setItem('magaiees_hide_broker_ticker', '1');
                    gBar.remove();
                } else {
                    localStorage.setItem('magaiees_hide_broker_ticker', '0');
                    window.renderGlobalScreensBrokerBar();
                }
            };
        }
        if (!document.getElementById('market-broker-ticker-wrap') && localStorage.getItem('magaiees_broker_all_screens') !== '0' && localStorage.getItem('magaiees_hide_broker_ticker') !== '1') {
            window.renderGlobalScreensBrokerBar();
        }
    });
})();

        try { if (!localStorage.getItem('magaies_purge_sw_20261119_gold_v9')) { localStorage.setItem('magaies_purge_sw_20261119_gold_v9', '1'); if ('caches' in window) { caches.keys().then(ks => ks.forEach(k => caches.delete(k))); } if ('serviceWorker' in navigator) { navigator.serviceWorker.getRegistrations().then(rs => rs.forEach(r => r.update())); } } } catch(e) {}
</script>
<script defer src="js/global-professor-ai-chat.js?v=<?= @filemtime(__DIR__ . '/../js/global-professor-ai-chat.js') ?: '2027.300' ?>"></script>


