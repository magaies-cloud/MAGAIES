<?php
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
    try {
        fetch('api/mirror_sync.php?action=get_currency_rate&_t=' + Date.now())
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.success && parseFloat(data.rate) > 1) {
                    applyDbUsdRate(parseFloat(data.rate));
                }
            }).catch(function() {});
    } catch(e) {}
})();
</script>
<script src="js/hls.light.min.js"></script>
<script src="js/bban_auto_helper.js"></script>
<script src="js/master-banks-helper.js?v=20260924_v1"></script>
<script src="js/permissions-manager.js?v=<?= file_exists(__DIR__ . '/../js/permissions-manager.js') ? filemtime(__DIR__ . '/../js/permissions-manager.js') : '20261016_v28' ?>"></script>
<script src="js/audit-tracker.js?v=20260927"></script>
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
        var rawTheme = localStorage.getItem('magaies_active_theme') || 
                       localStorage.getItem('magaiees_global_theme') || 
                       localStorage.getItem('ui_theme') || 
                       'warm_cream';
        var aliasMap = {
            'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
            'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
            'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
            'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
            'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
            'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
            'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight',
            'theme-imperial': 'warm_cream'
        };
        var activeKey = aliasMap[rawTheme] || rawTheme;
        var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
        if (!valid7.includes(activeKey)) activeKey = 'warm_cream';

        var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);

        document.documentElement.setAttribute('data-theme', activeKey);
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
<link rel="stylesheet" href="css/theme.css?v=<?= file_exists(__DIR__ . '/../css/theme.css') ? filemtime(__DIR__ . '/../css/theme.css') : '12' ?>">
<link rel="stylesheet" href="css/magaiees-royal-buttons.css?v=<?= file_exists(__DIR__ . '/../css/magaiees-royal-buttons.css') ? filemtime(__DIR__ . '/../css/magaiees-royal-buttons.css') : time() ?>">
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
        mob: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
    };
    var items = [
        ['magaiees_unified_dashboard.php','لوحة التحكم','#22d3ee','dash'],
        ['purchase.php','المشتريات','#fb923c','pur'],
        ['direct-sales.php','المبيعات','#34d399','sal'],
        ['vouchers-hub.php','المعاملات المالية','#fbbf24','fin'],
        ['inventory-report.php','المخازن','#a78bfa','inv'],
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
    var html='<aside class="global-sidebar"><div class="sidebar-header"><span class="sidebar-logo-icon"><svg width="26" height="26" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">'+icons.brand+'</svg></span><span class="sidebar-logo-text">مقاييس (MAGAIEES)</span></div><nav class="sidebar-nav">'+nav+'</nav><div class="sidebar-footer"><a href="#" onclick="logout();return false;" class="sidebar-item sidebar-logout"><span class="sidebar-icon" style="color:#f87171"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">'+icons.out+'</svg></span><span class="sidebar-text">خروج</span></a></div></aside>';
    
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
<script defer src="js/sidebar-panoramic-flyout.js?v=<?= file_exists(__DIR__ . '/../js/sidebar-panoramic-flyout.js') ? filemtime(__DIR__ . '/../js/sidebar-panoramic-flyout.js') : time() ?>"></script>
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
    backdrop-filter: blur(28px) saturate(220%) !important;
    -webkit-backdrop-filter: blur(28px) saturate(220%) !important;
    border: 1px solid rgba(192, 132, 252, 0.4) !important;
    border-top: 2px solid rgba(192, 132, 252, 0.9) !important;
    border-radius: 20px !important;
    margin: 15px 25px !important;
    padding: 12px 25px !important;
    animation: neonPulseHeader 4s infinite alternate ease-in-out;
    position: relative;
    z-index: 1000;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
    animation: glassShine 6s infinite;
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
    backdrop-filter: blur(12px);
    border-radius: 8px !important;
    transition: all 0.25s ease;
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
    transition: all 0.25s ease;
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
    animation: header-tv-pulse-glow 1.8s infinite ease-in-out !important;
    color: #ffffff !important;
    font-weight: 900 !important;
}
.header-tv-mini-btn.is-playing .header-tv-icon {
    animation: bounce 1.5s infinite;
    filter: drop-shadow(0 0 8px #fbbf24);
}
.header-tv-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    box-shadow: 0 0 8px #ef4444;
    animation: headerTvPulse 1.4s infinite;
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
    backdrop-filter: blur(25px) !important;
    -webkit-backdrop-filter: blur(25px) !important;
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
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
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
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
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
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
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
    transition: all 0.25s;
}
.auto-scroll-return-pill:hover .pill-arrow {
    background: #38bdf8;
    color: #0f172a;
    transform: translateY(-2px);
}

</style>
<header class="premium-header magical-glass-header <?php echo isset($header_theme_class) ? $header_theme_class : ''; ?>" style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 14px !important; margin:8px 16px !important; zoom:1 !important;">
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
    <!-- 1. الركن الأيمن الملكي الفاخر (عنوان البرنامج العالمي + إدارة النظام + الأدمن + متصل + التاريخ والوقت) — بنفس الارتفاع الموحد مع زيادة الارتفاع وتقليل الهوامش -->
    <div class="header-compact-right-cluster header-flagship-identity-suite" style="display:flex; flex-direction:column; justify-content:space-between; gap:3px; flex:1.35 1 395px; min-width:345px; max-width:none; height:70px !important; min-height:70px !important; max-height:70px !important; box-sizing:border-box !important; background:linear-gradient(135deg, rgba(2, 6, 23, 0.96) 0%, rgba(15, 23, 42, 0.95) 55%, rgba(6, 78, 59, 0.55) 100%); border:1.5px solid rgba(251, 191, 36, 0.85); border-radius:13px; padding:4px 10px; box-shadow:0 7px 21px rgba(0, 0, 0, 0.8), 0 0 16px rgba(251, 191, 36, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.22); position:relative; overflow:hidden;">
        <!-- الصف الأول العالمي: أيقونة الهوية الملكية + عنوان البرنامج العالمي الكبير + شارة الشاشة النشطة -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; white-space:nowrap; height:31px; border-bottom:1px solid rgba(251, 191, 36, 0.22); padding-bottom:2px;">
            <div style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden; flex:1 1 auto;">
                <div style="background:linear-gradient(135deg, #f59e0b 0%, #10b981 55%, #0284c7 100%); width:27px; height:27px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14.5px; border:1.5px solid #fef08a; box-shadow:0 0 12px rgba(245, 158, 11, 0.5), inset 0 1px 1px rgba(255,255,255,0.45); flex-shrink:0;">👑</div>
                <div style="display:flex; flex-direction:column; min-width:0; overflow:hidden; line-height:1.15;">
                    <div style="display:flex; align-items:center; gap:7px;">
                        <span style="color:#ffffff; font-weight:900; font-size:15.5px; letter-spacing:0.2px; text-shadow:0 2px 6px rgba(0,0,0,0.95), 0 0 14px rgba(251, 191, 36, 0.45); overflow:hidden; text-overflow:ellipsis;" id="header-company-name-top" data-i18n="sidebar.brand">نظام إدارة الشركات</span>
                        <span style="background:linear-gradient(135deg, rgba(251, 191, 36, 0.28), rgba(16, 185, 129, 0.28)); border:1px solid rgba(254, 240, 138, 0.75); color:#fef08a; font-size:10px; font-weight:900; padding:2px 8px; border-radius:999px; letter-spacing:0.5px; flex-shrink:0; box-shadow:0 0 10px rgba(251, 191, 36, 0.25);">MAGAIEES ERP</span>
                    </div>
                </div>
            </div>
            <h2 class="header-page-title" style="margin:0 !important; height:26px !important; font-size:12px !important; font-weight:900 !important; background:linear-gradient(135deg, rgba(3, 105, 161, 0.55), rgba(30, 58, 138, 0.75)) !important; border:1.5px solid #38bdf8 !important; color:#fef08a !important; padding:0 10px !important; border-radius:7.5px !important; display:inline-flex; align-items:center; gap:5px; line-height:1; flex-shrink:0; box-shadow:0 0 12px rgba(56, 189, 248, 0.35);">
                <span class="header-page-title-icon" style="font-size:12.5px;"><?php echo $dynamic_icon; ?></span>
                <span style="color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; text-shadow:0 1px 3px rgba(0,0,0,0.9);"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'لوحة التحكم'; ?></span>
            </h2>
        </div>

        <!-- الصف الثاني الملكي المنسق عالمياً (بنفس ارتفاع الأزرار 28px وتقليل الهوامش): المدير + المتصل + الصرف + التاريخ والوقت -->
        <div class="header-enterprise-meta-pod" style="display:flex !important; flex-direction:row !important; align-items:center !important; justify-content:space-between !important; gap:5px !important; padding:0 !important; background:transparent !important; border:none !important; box-shadow:none !important; white-space:nowrap; width:100% !important; height:28px !important;">
            <div style="display:flex; align-items:center; gap:5px; flex-wrap:nowrap; height:28px;">
                <div class="user-info enterprise-user-pill" onclick="document.getElementById('profile-modal').style.display='flex'" title="الملف الشخصي وصلاحيات الحساب" style="height:28px !important; padding:0 10px !important; border-radius:8px !important; gap:5px !important; background:linear-gradient(135deg, rgba(30, 41, 59, 0.92), rgba(15, 23, 42, 0.98)) !important; border:1.5px solid rgba(56, 189, 248, 0.65) !important; box-shadow:0 2px 8px rgba(0,0,0,0.4) !important;">
                    <span class="user-pill-avatar" style="font-size:12px;">👤</span>
                    <span id="header-user-name" style="font-size:12px !important; font-weight:900 !important; color:#ffffff !important;"><script>try{var _u=JSON.parse(localStorage.getItem('user')||'{}');document.write(_u.fullName||_u.username||'مستخدم')}catch(e){document.write('مستخدم')}</script></span>
                    <span id="header-user-role" data-i18n="header.sysAdmin" style="font-size:10px !important; font-weight:900 !important; color:#fde047 !important; background:rgba(245, 158, 11, 0.25) !important; border:1px solid rgba(251, 191, 36, 0.55); padding:2px 6px !important; border-radius:5px !important;">مدير النظام</span>
                </div>

                <div id="online-users-badge" class="enterprise-online-pill" onclick="alert('المستخدمين المتصلين حالياً: \n' + (window.onlineUsersList || []).join('\n'))" title="المتصلون الآن" style="height:28px !important; padding:0 10px !important; border-radius:8px !important; gap:5px !important; background:linear-gradient(135deg, rgba(6, 78, 59, 0.9), rgba(4, 120, 87, 0.72)) !important; border:1.5px solid #34d399 !important; box-shadow:0 0 12px rgba(16, 185, 129, 0.3) !important;">
                    <span class="online-pulse-dot" style="width:7px; height:7px;"></span>
                    <span class="online-label-txt" data-i18n="header.onlineNow" style="font-size:11px !important; font-weight:900 !important; color:#a7f3d0 !important;">متصل</span>
                    <span id="online-users-count" style="font-size:12px !important; font-weight:900 !important; color:#ffffff !important; background:rgba(0,0,0,0.4); padding:1px 6px; border-radius:5px;">1</span>
                </div>

                <div id="global-exchange-rate-box" class="enterprise-fx-pill" style="display:none; height:28px !important; padding:0 9px !important; border-radius:8px !important; background:linear-gradient(135deg, rgba(120, 53, 15, 0.85), rgba(180, 83, 9, 0.6)) !important; border:1.5px solid #fbbf24 !important;" onclick="toggleExchangeHistory(event)" title="سعر الصرف">
                    <span class="fx-pill-inner" style="font-size:11px !important; font-weight:900 !important;">
                        <span id="global-exchange-rate-trend" style="margin-left:2px;"></span>
                        <span id="global-exchange-rate-val" style="font-size:11.5px !important; font-weight:900 !important;">-</span>
                    </span>
                </div>
            </div>

            <div class="clock-container enterprise-time-pill" style="height:28px !important; padding:0 11px !important; border-radius:8px !important; gap:6px !important; width:auto !important; background:linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.99)) !important; border:1.5px solid rgba(251, 191, 36, 0.65) !important; box-shadow:inset 0 1px 2px rgba(0,0,0,0.6), 0 2px 8px rgba(0,0,0,0.4) !important;">
                <span style="font-size:11.5px;">📅</span>
                <span id="header-date-mini" style="font-size:11.5px !important; font-weight:900 !important; color:#fef08a !important;"><script>document.write(new Date().toLocaleDateString('ar-EG-u-nu-latn',{weekday:'short',day:'numeric',month:'short'}))</script></span>
                <span class="time-pill-divider" style="font-size:11px; color:rgba(251, 191, 36, 0.5);">|</span>
                <span style="font-size:11.5px;">🕒</span>
                <span id="header-clock" style="font-size:12.5px !important; font-weight:900 !important; color:#34d399 !important; letter-spacing:0.9px !important;"><script>var _n=new Date();document.write(_n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}))</script></span>
            </div>
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
            animation: quranRoyalContinuousFlow 68s linear infinite !important;
        }
        .quran-seamless-segment {
            display: inline-flex !important;
            align-items: center !important;
            gap: 14px !important;
            padding-left: 28px !important; /* مسافة قصيرة جداً تعادل ثانية واحدة فقط قبل بداية الآية من جديد */
            font-family: 'Amiri', 'Traditional Arabic', 'Cairo', 'Tajawal', serif !important;
            font-size: 16.5px !important;
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
        .quran-ornament-star {
            color: #34d399 !important;
            -webkit-text-fill-color: #34d399 !important;
            font-size: 14px !important;
            filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.85));
        }
    </style>
    <div id="dashboard-athkar-container" class="islamic-ticker-container" style="display:flex !important; flex:0.65 1 195px !important; min-width:165px !important; max-width:320px !important; height:70px !important; min-height:70px !important; max-height:70px !important; box-sizing:border-box !important; margin:0 !important; padding:0 !important; border-radius:14px !important; background:radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.35), transparent 70%), linear-gradient(135deg, #022c22 0%, #064e3b 35%, #020617 70%, #1e1b4b 100%) !important; border:2px solid #fbbf24 !important; align-items:center !important; justify-content:center !important; overflow:hidden !important; position:relative !important; animation: quranMihrabBorderGlow 3.5s ease-in-out infinite alternate !important;" title="آيات قرآنية وأذكار مباركة — مرر الماوس لإيقاف الحركة مؤقتاً للقراءة">
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
        <div class="header-crystal-icon-dock">
            <button id="desktop-sync-shield-btn" type="button" class="royal-crystal-btn dock-icon-btn dock-icon-shield" onclick="window.openDesktopSyncAuditModal()" title="🛡️ درع الحماية وسجل المزامنة التلقائية (اضغط مطولاً للفحص والمزامنة الفورية)">
                🛡️
                <span id="desktop-sync-shield-badge" style="display:none; position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; font-size:9.5px; font-weight:900; min-width:16px; height:16px; border-radius:999px; align-items:center; justify-content:center; padding:0 3px; border:1.5px solid #0f172a;">0</span>
            </button>

            <button class="audit-tracker-btn dock-icon-btn dock-icon-search" onclick="window.openAuditTrackerModal()" title="متعقب المستندات وسجل تتبع المستخدمين (من فعل ماذا) - اضغط Alt+T">🔍</button>

            <button class="fullscreen-toggle-btn dock-icon-btn dock-icon-fullscreen" onclick="toggleFullScreen()" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>

            <button class="theme-toggle-btn dock-icon-btn dock-icon-theme" onclick="toggleTheme()" data-i18n-title="header.toggleTheme" title="تبديل المظهر السريع (فاتح / داكن)">🌓</button>

            <a href="mobile-dashboard.php?force_mobile=1" target="_blank" id="header-mobile-quick-btn" class="dock-icon-btn dock-icon-mobile" onclick="try{localStorage.setItem('magaiees_view_preference','mobile');document.cookie='magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax';}catch(e){}" style="<?php echo $is_dashboard_page ? 'display:none !important;' : ''; ?>" title="📱 فتح نسخة الجوال الذكية (Mobile Edition) في نافذة مستقلة">📱</a>

            <!-- 📺 SACRED TV & QURAN MINI CONTROLLER -->
            <div id="header-media-control-wrap" style="position:relative; display:flex; align-items:center;">
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
            <!-- العمود الأيمن: نسخة الموبايل وتحتها تثبيت التطبيق -->
            <div class="quad-matrix-col quad-col-right">
                <button id="magaiees-top-mobile-btn" type="button" onclick="if(typeof switchToMobileEdition==='function'){switchToMobileEdition();}else{try{localStorage.setItem('magaiees_view_preference','mobile');document.cookie='magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax';}catch(e){}window.location.href='mobile-dashboard.php?force_mobile=1';}" class="magaiees-stack-btn stack-btn-mobile" title="التبديل إلى نسخة الموبايل والتابلت السريعة">
                    <span class="stack-btn-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <span class="stack-btn-text">نسخة الموبايل 📱</span>
                </button>

                <button id="magaiees-top-install-btn" type="button" onclick="if(window.PWAManager && PWAManager.triggerInstall){PWAManager.triggerInstall();}else{alert('لتثبيت تطبيق مقاييس: اضغط على أيقونة التثبيت ⊕ في شريط عنوان المتصفح بالأعلى');}" class="magaiees-stack-btn stack-btn-install" title="تثبيت تطبيق مقاييس على جهازك كبرنامج مستقل">
                    <span class="stack-btn-icon"><i class="fa-solid fa-download"></i></span>
                    <span class="stack-btn-text">تثبيت التطبيق 📲</span>
                </button>
            </div>

            <!-- العمود الأيسر (يسارها): السمات وتحتها الرجوع -->
            <div class="quad-matrix-col quad-col-left">
                <button id="magaiees-master-themes-btn" type="button" class="magaiees-stack-btn stack-btn-themes magaiees-theme-btn-top" onclick="openMaster7ThemesModal()" title="السمات السبعة المعتمدة والموحدة (7 سمات رئيسية)">
                    <span class="theme-icon-art" style="font-size: 0.9rem;">🎨</span>
                    <span class="theme-btn-text">السمات</span>
                    <span class="theme-count-badge">7</span>
                </button>

                <button id="magaiees-master-back-btn" class="magaiees-stack-btn stack-btn-back magaiees-back-btn" onclick="magaieesGoBack()" title="الرجوع للخلف (الشاشة السابقة)">
                    <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: 13px; height: 13px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 19 12 12 5"></polyline>
                    </svg>
                    <span class="back-btn-text">الرجوع للخلف</span>
                </button>
            </div>
        </div>
        <?php else: ?>
        <div id="magaiees-dashboard-header-stack" class="header-quad-matrix header-duo-matrix">
            <div class="quad-matrix-col quad-col-left">
                <button id="magaiees-master-themes-btn" type="button" class="magaiees-stack-btn stack-btn-themes magaiees-theme-btn-top" onclick="openMaster7ThemesModal()" title="السمات السبعة المعتمدة والموحدة (7 سمات رئيسية)">
                    <span class="theme-icon-art" style="font-size: 0.9rem;">🎨</span>
                    <span class="theme-btn-text">السمات</span>
                    <span class="theme-count-badge">7</span>
                </button>

                <button id="magaiees-master-back-btn" class="magaiees-stack-btn stack-btn-back magaiees-back-btn" onclick="magaieesGoBack()" title="الرجوع للخلف (الشاشة السابقة)">
                    <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width: 13px; height: 13px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 19 12 12 5"></polyline>
                    </svg>
                    <span class="back-btn-text">الرجوع للخلف</span>
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
<script src="js/auto-charts.js?v=<?php echo time(); ?>"></script>
<script src="js/quran_reciters_data.js?v=<?php echo file_exists(__DIR__ . '/../js/quran_reciters_data.js') ? filemtime(__DIR__ . '/../js/quran_reciters_data.js') : time(); ?>"></script>
<script src="js/global_background_media.js?v=<?php echo file_exists(__DIR__ . '/../js/global_background_media.js') ? filemtime(__DIR__ . '/../js/global_background_media.js') : time(); ?>" defer></script>
<script src="js/magaiees_back.js?v=<?php echo file_exists(__DIR__ . '/../js/magaiees_back.js') ? filemtime(__DIR__ . '/../js/magaiees_back.js') : time(); ?>" defer></script><script src="js/magaies-smart-guide.js?v=<?php echo file_exists(__DIR__ . '/../js/magaies-smart-guide.js') ? filemtime(__DIR__ . '/../js/magaies-smart-guide.js') : time(); ?>" defer></script>
<script src="js/magaiees-royal-buttons.js?v=<?php echo file_exists(__DIR__ . '/../js/magaiees-royal-buttons.js') ? filemtime(__DIR__ . '/../js/magaiees-royal-buttons.js') : time(); ?>" defer></script>
<script src="js/auto-header-scroll.js?v=<?php echo file_exists(__DIR__ . '/../js/auto-header-scroll.js') ? filemtime(__DIR__ . '/../js/auto-header-scroll.js') : time(); ?>" defer></script>


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

<style>
/* ═══════════════════════════════════════════════════════════════════════════
   🌐 WORLD-CLASS ENTERPRISE HEADER CONTROLS ARCHITECTURE (Bespoke SaaS UI)
   ═══════════════════════════════════════════════════════════════════════════ */

/* 1. Compact Identity, Status & Time Capsule Pod */
.header-enterprise-meta-pod {
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    background: rgba(15, 23, 42, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 12px;
    padding: 4px 6px;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.05), 0 4px 14px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.meta-pod-row-top {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.enterprise-user-pill {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    height: 26px !important;
    padding: 0 9px !important;
    border-radius: 8px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    min-width: 0 !important;
}
.enterprise-user-pill:hover {
    background: rgba(56, 189, 248, 0.14) !important;
    border-color: rgba(56, 189, 248, 0.45) !important;
    transform: translateY(-1px);
}
.enterprise-user-pill .user-pill-avatar {
    font-size: 0.72rem;
    line-height: 1;
    opacity: 0.9;
}
.enterprise-user-pill #header-user-name {
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    color: #f8fafc !important;
    letter-spacing: 0.2px;
    line-height: 1 !important;
}
.enterprise-user-pill .user-pill-sep {
    color: rgba(148, 163, 184, 0.5);
    font-size: 0.7rem;
    line-height: 1;
}
.enterprise-user-pill #header-user-role {
    font-size: 0.68rem !important;
    font-weight: 800 !important;
    color: #38bdf8 !important;
    background: rgba(56, 189, 248, 0.14);
    padding: 2px 6px;
    border-radius: 5px;
    line-height: 1 !important;
    letter-spacing: 0.1px;
}

.enterprise-online-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    height: 26px !important;
    padding: 0 8px !important;
    border-radius: 8px !important;
    background: rgba(16, 185, 129, 0.13) !important;
    border: 1px solid rgba(16, 185, 129, 0.32) !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    min-width: 0 !important;
}
.enterprise-online-pill:hover {
    background: rgba(16, 185, 129, 0.24) !important;
    border-color: rgba(16, 185, 129, 0.6) !important;
    transform: translateY(-1px);
}
.online-pulse-dot {
    width: 6.5px;
    height: 6.5px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
    display: inline-block;
    flex-shrink: 0;
}
.enterprise-online-pill .online-label-txt {
    color: #6ee7b7;
    font-size: 0.69rem;
    font-weight: 800;
    line-height: 1;
}
.enterprise-online-pill #online-users-count {
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 900;
    line-height: 1;
}

.enterprise-fx-pill {
    position: relative;
    align-items: center !important;
    justify-content: center !important;
    height: 26px !important;
    padding: 0 8px !important;
    border-radius: 8px !important;
    background: rgba(245, 158, 11, 0.14) !important;
    border: 1px solid rgba(245, 158, 11, 0.35) !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    min-width: 0 !important;
    width: auto !important;
}
#global-exchange-rate-box[style*="block"] {
    display: inline-flex !important;
}
.enterprise-fx-pill .fx-pill-inner {
    color: #fde68a;
    font-size: 0.69rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    line-height: 1;
}
.enterprise-fx-pill #global-exchange-rate-val {
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 900;
}

.enterprise-time-pill {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    height: 25px !important;
    padding: 0 10px !important;
    border-radius: 7px !important;
    background: rgba(15, 23, 42, 0.65) !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}
.enterprise-time-pill #header-date-mini {
    color: #cbd5e1 !important;
    font-size: 0.69rem !important;
    font-weight: 700 !important;
    margin: 0 !important;
    white-space: nowrap;
    line-height: 1;
}
.enterprise-time-pill .time-pill-divider {
    color: rgba(148, 163, 184, 0.35);
    font-size: 0.7rem;
    line-height: 1;
}
.enterprise-time-pill #header-clock {
    color: #34d399 !important;
    font-size: 0.84rem !important;
    font-weight: 900 !important;
    font-variant-numeric: tabular-nums !important;
    letter-spacing: 0.8px !important;
    direction: ltr;
    line-height: 1;
    text-shadow: 0 0 8px rgba(16, 185, 129, 0.35);
}

/* 2. Unified Horizontal Crystal Icon Dock (Shield, Search, Fullscreen, Theme, TV) */
.header-crystal-icon-dock {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 7px;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.09);
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.05), 0 4px 14px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.header-crystal-icon-dock .dock-icon-btn {
    position: relative;
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    min-height: 34px !important;
    border-radius: 9px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    font-size: 0.96rem !important;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    box-sizing: border-box !important;
    padding: 0 !important;
    margin: 0 !important;
}
.header-crystal-icon-dock .dock-icon-btn:hover {
    transform: translateY(-2px) scale(1.06) !important;
}
.header-crystal-icon-dock .dock-icon-shield {
    background: rgba(16, 185, 129, 0.16) !important;
    border: 1.5px solid rgba(16, 185, 129, 0.5) !important;
    color: #34d399 !important;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2) !important;
}
.header-crystal-icon-dock .dock-icon-shield:hover {
    background: rgba(16, 185, 129, 0.28) !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.45) !important;
}
.header-crystal-icon-dock .dock-icon-search {
    background: rgba(56, 189, 248, 0.15) !important;
    border: 1.5px solid rgba(56, 189, 248, 0.45) !important;
    color: #38bdf8 !important;
    box-shadow: 0 2px 10px rgba(56, 189, 248, 0.18) !important;
}
.header-crystal-icon-dock .dock-icon-search:hover {
    background: rgba(56, 189, 248, 0.28) !important;
    box-shadow: 0 4px 14px rgba(56, 189, 248, 0.4) !important;
}
.header-crystal-icon-dock .dock-icon-fullscreen,
.header-crystal-icon-dock .dock-icon-theme {
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.16) !important;
    color: #f8fafc !important;
}
.header-crystal-icon-dock .dock-icon-fullscreen:hover,
.header-crystal-icon-dock .dock-icon-theme:hover {
    background: rgba(168, 85, 247, 0.25) !important;
    border-color: rgba(192, 132, 252, 0.6) !important;
    box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35) !important;
}
.header-crystal-icon-dock .dock-icon-mobile {
    background: rgba(56, 189, 248, 0.16) !important;
    border: 1.5px solid rgba(56, 189, 248, 0.45) !important;
    color: #38bdf8 !important;
}
.header-crystal-icon-dock .header-tv-mini-btn {
    height: 34px !important;
    padding: 0 10px !important;
    border-radius: 9px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
}

/* 3. Symmetrical 2x2 Paired Action Matrix:
      Right Column: نسخة الموبايل (top) + تثبيت التطبيق (bottom)
      Left Column (يسارها): السمات (top) + الرجوع (bottom) */
.header-quad-matrix {
    display: inline-flex !important;
    flex-direction: row !important;
    direction: rtl !important;
    align-items: stretch !important;
    gap: 5px !important;
    background: rgba(15, 23, 42, 0.45) !important;
    border: 1px solid rgba(255, 255, 255, 0.09) !important;
    border-radius: 12px !important;
    padding: 4px 5px !important;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.05), 0 4px 14px rgba(0, 0, 0, 0.2) !important;
}
.quad-matrix-col {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
    justify-content: center !important;
    min-width: 118px !important;
}
.magaiees-stack-btn,
header #magaiees-dashboard-header-stack #magaiees-master-back-btn.magaiees-back-btn:not(.floating-docked) {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    height: 26px !important;
    min-height: 26px !important;
    max-height: 26px !important;
    padding: 0 10px !important;
    margin: 0 !important;
    border-radius: 8px !important;
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    font-family: 'Tajawal', sans-serif !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    box-sizing: border-box !important;
    width: 100% !important;
    text-decoration: none !important;
    line-height: 1 !important;
}
.magaiees-stack-btn:hover,
header #magaiees-dashboard-header-stack #magaiees-master-back-btn.magaiees-back-btn:not(.floating-docked):hover {
    transform: translateY(-1px) scale(1.02) !important;
}
.stack-btn-mobile {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(6, 182, 212, 0.25)) !important;
    border: 1.5px solid #10b981 !important;
    color: #34d399 !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2) !important;
}
.stack-btn-mobile:hover {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.42), rgba(6, 182, 212, 0.42)) !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.45) !important;
    color: #ffffff !important;
}
.stack-btn-install {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(99, 102, 241, 0.25)) !important;
    border: 1.5px solid #38bdf8 !important;
    color: #38bdf8 !important;
    box-shadow: 0 2px 8px rgba(56, 189, 248, 0.2) !important;
}
.stack-btn-install:hover {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.42), rgba(99, 102, 241, 0.42)) !important;
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.45) !important;
    color: #ffffff !important;
}
.stack-btn-themes,
.magaiees-theme-btn-top {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.32)) !important;
    border: 1.5px solid #fbbf24 !important;
    color: #fde047 !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.22) !important;
}
.stack-btn-themes:hover,
.magaiees-theme-btn-top:hover {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.38), rgba(217, 119, 6, 0.48)) !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.45) !important;
}
.magaiees-theme-btn-top .theme-count-badge {
    background: #f59e0b;
    color: #000000;
    font-size: 10px;
    font-weight: 900;
    padding: 1px 5px;
    border-radius: 999px;
    line-height: 1.1;
}
.stack-btn-back,
header #magaiees-dashboard-header-stack #magaiees-master-back-btn.magaiees-back-btn:not(.floating-docked) {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    border: 1.5px solid #fef08a !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3) !important;
}
header #magaiees-dashboard-header-stack #magaiees-master-back-btn.magaiees-back-btn:not(.floating-docked) .back-icon-svg {
    width: 13px !important;
    height: 13px !important;
}

/* ── Light Mode Adaptations for Enterprise Header Controls ── */
html.light-theme .header-enterprise-meta-pod,
html.light-theme .header-crystal-icon-dock,
html.light-theme .header-quad-matrix {
    background: rgba(255, 255, 255, 0.78) !important;
    border-color: rgba(15, 23, 42, 0.12) !important;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06) !important;
}
html.light-theme .enterprise-user-pill {
    background: rgba(15, 23, 42, 0.05) !important;
    border-color: rgba(15, 23, 42, 0.14) !important;
}
html.light-theme .enterprise-user-pill #header-user-name {
    color: #0f172a !important;
}
html.light-theme .enterprise-user-pill #header-user-role {
    color: #0369a1 !important;
    background: rgba(2, 132, 199, 0.14) !important;
}
html.light-theme .enterprise-online-pill {
    background: rgba(16, 185, 129, 0.15) !important;
    border-color: rgba(5, 150, 105, 0.35) !important;
}
html.light-theme .enterprise-online-pill .online-label-txt {
    color: #065f46 !important;
}
html.light-theme .enterprise-online-pill #online-users-count {
    color: #047857 !important;
}
html.light-theme .enterprise-fx-pill {
    background: rgba(245, 158, 11, 0.16) !important;
    border-color: rgba(217, 119, 6, 0.4) !important;
}
html.light-theme .enterprise-fx-pill .fx-pill-inner,
html.light-theme .enterprise-fx-pill #global-exchange-rate-val {
    color: #92400e !important;
}
html.light-theme .enterprise-time-pill {
    background: rgba(15, 23, 42, 0.06) !important;
    border-color: rgba(15, 23, 42, 0.1) !important;
}
html.light-theme .enterprise-time-pill #header-date-mini {
    color: #334155 !important;
}
html.light-theme .enterprise-time-pill #header-clock {
    color: #047857 !important;
    text-shadow: none !important;
}
html.light-theme .header-crystal-icon-dock .dock-icon-fullscreen,
html.light-theme .header-crystal-icon-dock .dock-icon-theme {
    background: rgba(15, 23, 42, 0.06) !important;
    border-color: rgba(15, 23, 42, 0.15) !important;
    color: #0f172a !important;
}
html.light-theme .stack-btn-mobile {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0) !important;
    border-color: #059669 !important;
    color: #065f46 !important;
}
html.light-theme .stack-btn-install {
    background: linear-gradient(135deg, #e0f2fe, #bae6fd) !important;
    border-color: #0284c7 !important;
    color: #0369a1 !important;
}
html.light-theme .stack-btn-themes,
html.light-theme .magaiees-theme-btn-top {
    background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
    border-color: #d97706 !important;
    color: #92400e !important;
}
html.light-theme .stack-btn-back,
html.light-theme header #magaiees-dashboard-header-stack #magaiees-master-back-btn.magaiees-back-btn:not(.floating-docked) {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    color: #ffffff !important;
}

/* ── Modal Styles ── */
.m7-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: m7FadeIn 0.25s ease;
}
.m7-modal-dialog {
    background: #0f172a;
    color: #f8fafc;
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    width: 100%;
    max-width: 820px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.8), 0 0 35px rgba(245, 158, 11, 0.15);
}
.m7-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.6);
}
.m7-header-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(245, 158, 11, 0.18);
    border: 1px solid rgba(245, 158, 11, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.m7-modal-title {
    font-size: 1.15rem;
    font-weight: 900;
    margin: 0;
    color: #f8fafc;
}
.m7-modal-subtitle {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 2px 0 0 0;
}
.m7-close-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #94a3b8;
    border-radius: 10px;
    width: 32px;
    height: 32px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
}
.m7-close-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}
.m7-filter-bar {
    display: flex;
    gap: 8px;
    padding: 12px 24px;
    background: rgba(0, 0, 0, 0.2);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.m7-tab-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s;
}
.m7-tab-btn.active {
    background: #f59e0b;
    color: #000000;
    border-color: #f59e0b;
    font-weight: 900;
}
.m7-badge {
    background: rgba(0, 0, 0, 0.2);
    padding: 1px 6px;
    border-radius: 999px;
    font-size: 11px;
}
.m7-tab-btn.active .m7-badge {
    background: rgba(0, 0, 0, 0.3);
    color: #ffffff;
}
.m7-themes-grid {
    padding: 20px 24px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 12px;
    overflow-y: auto;
    flex: 1;
}
.m7-theme-card {
    background: var(--t-bg);
    color: var(--t-text);
    border: 2px solid var(--t-border);
    border-radius: 18px;
    padding: 14px 16px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
}
.m7-theme-card:hover {
    transform: translateY(-3px) scale(1.01);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}
.m7-theme-card.active-card {
    outline: 3px solid #38bdf8;
    box-shadow: 0 0 25px rgba(56, 189, 248, 0.4);
}
.m7-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.m7-card-name {
    font-size: 0.95rem;
    font-weight: 900;
}
.m7-active-tag {
    background: #10b981;
    color: #ffffff;
    font-size: 11px;
    font-weight: 900;
    padding: 2px 8px;
    border-radius: 999px;
}
.m7-swatches {
    display: flex;
    gap: 4px;
}
.m7-swatches span {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}
.m7-card-desc {
    font-size: 0.78rem;
    line-height: 1.4;
    opacity: 0.85;
    margin: 0;
}
.m7-card-foot {
    font-size: 0.72rem;
    font-weight: 800;
    opacity: 0.7;
    margin-top: 4px;
}
.m7-modal-footer {
    padding: 14px 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(15, 23, 42, 0.7);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.m7-confirm-btn {
    background: #10b981;
    color: #ffffff;
    border: none;
    padding: 8px 20px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 0.9rem;
    cursor: pointer;
    transition: 0.2s;
}
.m7-confirm-btn:hover {
    background: #059669;
}
@keyframes m7FadeIn {
    from { opacity: 0; transform: scale(0.97); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<style id="master-7-themes-final-override">
/* ═══════════════════════════════════════════════════════════════════════════════
   🛡️ MASTER 7 THEMES FINAL UNIFICATION & WCAG AAA CONTRAST ENGINE (ALL SCREENS)
   Guarantees 100% visual consistency across Dashboard, Sales, Purchases, Vouchers,
   Single Cash Payment, Multi Cash Payment, Reports, and Modals on Laptop & Mobile
   ═══════════════════════════════════════════════════════════════════════════════ */

/* ── 1. Exact Theme Variable Lock for All 7 Themes (Light & Dark + Aliases) ── */
html[data-theme="warm_cream"], html[data-theme="warm-alabaster"], html[data-theme="beige"] {
    --bg-color: #fbf7ee !important;
    --bg-main: #fbf7ee !important;
    --bg-dark: #fbf7ee !important;
    --bg-secondary: #f5eedd !important;
    --surface: #ffffff !important;
    --surface-color: #ffffff !important;
    --surface-hover: #f5eedd !important;
    --panel-bg: #fdfbf7 !important;
    --border: #dcd1be !important;
    --border-color: #dcd1be !important;
    --text-primary: #1c1917 !important;
    --text-secondary: #382e25 !important;
    --text-muted: #5c4d40 !important;
    --text-main: #1c1917 !important;
    --text-dim: #7c6858 !important;
    --primary-accent: #b45309 !important;
    --amber: #b45309 !important;
    --green: #15803d !important;
    --red: #b91c1c !important;
    --blue: #0369a1 !important;
}
html[data-theme="ultra_white"], html[data-theme="pure-platinum"], html[data-theme="white"] {
    --bg-color: #f8fafc !important;
    --bg-main: #f8fafc !important;
    --bg-dark: #f8fafc !important;
    --bg-secondary: #f1f5f9 !important;
    --surface: #ffffff !important;
    --surface-color: #ffffff !important;
    --surface-hover: #f1f5f9 !important;
    --panel-bg: #ffffff !important;
    --border: #cbd5e1 !important;
    --border-color: #cbd5e1 !important;
    --text-primary: #0f172a !important;
    --text-secondary: #1e293b !important;
    --text-muted: #475569 !important;
    --text-main: #0f172a !important;
    --text-dim: #64748b !important;
    --primary-accent: #2563eb !important;
    --amber: #b45309 !important;
    --green: #047857 !important;
    --red: #dc2626 !important;
    --blue: #2563eb !important;
}
html[data-theme="exec_gray"], html[data-theme="pearl-platinum"] {
    --bg-color: #f1f5f9 !important;
    --bg-main: #f1f5f9 !important;
    --bg-dark: #f1f5f9 !important;
    --bg-secondary: #e2e8f0 !important;
    --surface: #ffffff !important;
    --surface-color: #ffffff !important;
    --surface-hover: #e2e8f0 !important;
    --panel-bg: #ffffff !important;
    --border: #cbd5e1 !important;
    --border-color: #cbd5e1 !important;
    --text-primary: #0f172a !important;
    --text-secondary: #1e293b !important;
    --text-muted: #475569 !important;
    --text-main: #0f172a !important;
    --text-dim: #64748b !important;
    --primary-accent: #1d4ed8 !important;
    --amber: #b45309 !important;
    --green: #047857 !important;
    --red: #b91c1c !important;
    --blue: #1d4ed8 !important;
}
html[data-theme="midnight_obsidian"], html[data-theme="midnight-executive"], html[data-theme="navy"] {
    --bg-color: #0a1128 !important;
    --bg-main: #0a1128 !important;
    --bg-dark: #0a1128 !important;
    --bg-secondary: #0b132b !important;
    --surface: #111c44 !important;
    --surface-color: #111c44 !important;
    --surface-hover: #1c2a5e !important;
    --panel-bg: #111c44 !important;
    --border: rgba(56, 189, 248, 0.25) !important;
    --border-color: rgba(56, 189, 248, 0.25) !important;
    --text-primary: #ffffff !important;
    --text-secondary: #e0f2fe !important;
    --text-muted: #93c5fd !important;
    --text-main: #ffffff !important;
    --text-dim: #60a5fa !important;
    --primary-accent: #00f2fe !important;
    --amber: #fbbf24 !important;
    --green: #10b981 !important;
    --red: #ef4444 !important;
    --blue: #38bdf8 !important;
}
html[data-theme="mocha_brown"], html[data-theme="sepia-brown"], html[data-theme="brown"] {
    --bg-color: #1a0f0a !important;
    --bg-main: #1a0f0a !important;
    --bg-dark: #1a0f0a !important;
    --bg-secondary: #26150e !important;
    --surface: #2e1a12 !important;
    --surface-color: #2e1a12 !important;
    --surface-hover: #3d2319 !important;
    --panel-bg: #2e1a12 !important;
    --border: #78350f !important;
    --border-color: #78350f !important;
    --text-primary: #fffbeb !important;
    --text-secondary: #fef3c7 !important;
    --text-muted: #fde68a !important;
    --text-main: #fffbeb !important;
    --text-dim: #d4a373 !important;
    --primary-accent: #f59e0b !important;
    --amber: #f59e0b !important;
    --green: #34d399 !important;
    --red: #f87171 !important;
    --blue: #38bdf8 !important;
}
html[data-theme="pure_oled"], html[data-theme="obsidian-charcoal"], html[data-theme="black"] {
    --bg-color: #000000 !important;
    --bg-main: #000000 !important;
    --bg-dark: #000000 !important;
    --bg-secondary: #050505 !important;
    --surface: #0a0a0a !important;
    --surface-color: #0a0a0a !important;
    --surface-hover: #171717 !important;
    --panel-bg: #0a0a0a !important;
    --border: #262626 !important;
    --border-color: #262626 !important;
    --text-primary: #ffffff !important;
    --text-secondary: #f5f5f5 !important;
    --text-muted: #a3a3a3 !important;
    --text-main: #ffffff !important;
    --text-dim: #737373 !important;
    --primary-accent: #10b981 !important;
    --amber: #f59e0b !important;
    --green: #10b981 !important;
    --red: #ef4444 !important;
    --blue: #38bdf8 !important;
}
html[data-theme="slate_twilight"], html[data-theme="twilight-indigo"], html[data-theme="slate"] {
    --bg-color: #0f172a !important;
    --bg-main: #0f172a !important;
    --bg-dark: #0f172a !important;
    --bg-secondary: #1e293b !important;
    --surface: #1e293b !important;
    --surface-color: #1e293b !important;
    --surface-hover: #334155 !important;
    --panel-bg: #1e293b !important;
    --border: #334155 !important;
    --border-color: #334155 !important;
    --text-primary: #f8fafc !important;
    --text-secondary: #e2e8f0 !important;
    --text-muted: #94a3b8 !important;
    --text-main: #f8fafc !important;
    --text-dim: #64748b !important;
    --primary-accent: #38bdf8 !important;
    --amber: #fbbf24 !important;
    --green: #34d399 !important;
    --red: #f87171 !important;
    --blue: #38bdf8 !important;
}

/* ── 2. Universal Canvas & Screen Unification Across All 7 Themes ── */
html[data-theme] body,
html[data-theme] .ambient-bg,
html[data-theme] .dash-main,
html[data-theme] .pos-wrapper,
html[data-theme] .app-container,
html[data-theme] .main-content,
html[data-theme] .vouchers-hub-container,
html[data-theme] #sales-hub-view,
html[data-theme] #invoice-editor-view,
html[data-theme] #split-invoice-view,
html[data-theme] #delivery-hub-view,
html[data-theme] #purchase-hub-view,
html[data-theme] #purchase-editor-view {
    background: var(--bg-color) !important;
    color: var(--text-primary) !important;
}

/* ── 3. Universal Top Header Unification (No Dark Purple Mismatch) ── */
html[data-theme] .magical-glass-header,
html[data-theme] .top-header,
html[data-theme] .premium-header,
html[data-theme] .header-area {
    background: var(--surface) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
}
html[data-theme] .magical-glass-header .header-screen-title {
    color: var(--text-primary) !important;
    -webkit-text-fill-color: var(--text-primary) !important;
    text-shadow: none !important;
}
html[data-theme] .header-flagship-identity-suite #header-company-name-top {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
html[data-theme] .header-flagship-identity-suite #header-clock {
    color: #34d399 !important;
    -webkit-text-fill-color: #34d399 !important;
}
html[data-theme] .header-flagship-identity-suite #header-date-mini {
    color: #fef08a !important;
    -webkit-text-fill-color: #fef08a !important;
}
html[data-theme] .header-flagship-identity-suite #header-user-name {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
html.light-theme .magical-glass-header .magical-icon-btn {
    background: var(--bg-secondary) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
}
html.light-theme .magical-glass-header .header-divider {
    background: var(--border) !important;
}

/* ── 4. Universal Card, Panel & Container Unification Across All Screens ── */
html[data-theme] .neo-card,
html[data-theme] .glass-panel,
html[data-theme] .glass-card,
html[data-theme] .summary-card,
html[data-theme] .filter-panel,
html[data-theme] .chart-card,
html[data-theme] .kpi-card,
html[data-theme] .premium-card,
html[data-theme] .recent-transactions-card,
html[data-theme] #form-fields-card,
html[data-theme] .sales-glass-card,
html[data-theme] .purchase-glass-card,
html[data-theme] .gold-obsidian-card,
html[data-theme] #items-panel,
html[data-theme] .table-container,
html[data-theme] .focus-zone,
html[data-theme] .single-unified-form,
html[data-theme] #payment-panel,
html[data-theme] .glass-container,
html[data-theme] .account-block,
html[data-theme] .qa-card-wrapper,
html[data-theme] .screen-popover {
    background: var(--surface) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
}

/* Secondary Bars & Strips */
html[data-theme] .panel-header-row,
html[data-theme] .items-toolbar,
html[data-theme] .notes-bar,
html[data-theme] .ds-notes-bar,
html[data-theme] #totals-calc-card,
html[data-theme] #inline-totals-container,
html[data-theme] .smart-filter-bar,
html[data-theme] .filter-time-group,
html[data-theme] #invoice-info-box,
html[data-theme] .pro-serial-container,
html[data-theme] .gold-serial-wrap,
html[data-theme] .sub-header-bar,
html[data-theme] #payment-methods-row,
html[data-theme] .crystal-pill,
html[data-theme] #grand-tafqeet-container,
html[data-theme] .block-tafqeet-row,
html[data-theme] .scp-top-amount-row,
html[data-theme] .mcp-voucher-desc-bar,
html[data-theme] #fin-strip > div,
html[data-theme] #customer-financial-card > div {
    background: var(--bg-secondary) !important;
    border-color: var(--border) !important;
    color: var(--text-primary) !important;
}

/* ── 5. Light Themes: Zero Dark Islands & Strict High-Contrast Text ── */
html.light-theme .v-input,
html.light-theme .ctrl-input,
html.light-theme .form-input,
html.light-theme .form-control,
html.light-theme .form-select,
html.light-theme .g-input,
html.light-theme .g-select,
html.light-theme .date-input,
html.light-theme #voucher-no-display,
html.light-theme #voucher-date,
html.light-theme #voucher-ref,
html.light-theme #voucher-desc,
html.light-theme #from-account-search,
html.light-theme #to-account-search,
html.light-theme #main-account-search,
html.light-theme .block-account-search,
html.light-theme input[id^="statement-"],
html.light-theme input.block-total-amount,
html.light-theme input.block-bank-amount,
html.light-theme input.block-cheque-amount,
html.light-theme input.block-transfer-no-input,
html.light-theme input.block-cheque-no-input,
html.light-theme input.block-bank-name-input,
html.light-theme input.block-bank-desc-input,
html.light-theme input.block-cheque-bank-input,
html.light-theme input.block-cheque-date-input,
html.light-theme input.block-cheque-desc-input,
html.light-theme .tot-row input.tot-input {
    background: #ffffff !important;
    color: var(--text-primary) !important;
    -webkit-text-fill-color: var(--text-primary) !important;
    border-color: var(--border) !important;
    text-shadow: none !important;
}

html.light-theme #hdr-customer-search {
    background: #ffffff !important;
    color: var(--primary-accent) !important;
    -webkit-text-fill-color: var(--primary-accent) !important;
    border-color: var(--border) !important;
}

html.light-theme .custom-dropdown-list,
html.light-theme #customer-dropdown-list,
html.light-theme #from-account-dropdown-list,
html.light-theme #to-account-dropdown-list,
html.light-theme #main-account-dropdown-list {
    background: var(--surface) !important;
    color: var(--text-primary) !important;
    border-color: var(--border) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12) !important;
}

html.light-theme .bal-label,
html.light-theme .badge-label,
html.light-theme .h-label,
html.light-theme .form-label,
html.light-theme .label,
html.light-theme label,
html.light-theme .v-title,
html.light-theme .pro-serial-number,
html.light-theme .pro-serial-btn,
html.light-theme #fin-ledger,
html.light-theme #grand-tafqeet-text,
html.light-theme .block-tafqeet-text,
html.light-theme #main-party-title {
    color: var(--text-primary) !important;
    text-shadow: none !important;
}

html.light-theme .bal-val,
html.light-theme .badge-val {
    color: var(--primary-accent) !important;
    text-shadow: none !important;
}

/* Payment Method Details & Totals in Light Themes */
html.light-theme #pay-bank-section,
html.light-theme .block-bank-details,
html.light-theme .total-glass-card.bank-glow {
    background: #ecfdf5 !important;
    border-color: #10b981 !important;
    color: #065f46 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08) !important;
}
html.light-theme #unified-bank-total {
    color: #065f46 !important;
    text-shadow: none !important;
}

html.light-theme #pay-cheque-section,
html.light-theme .block-cheque-details,
html.light-theme .total-glass-card.cheque-glow {
    background: #f5f3ff !important;
    border-color: #a855f7 !important;
    color: #5b21b6 !important;
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.08) !important;
}
html.light-theme #unified-cheque-total {
    color: #5b21b6 !important;
    text-shadow: none !important;
}

html.light-theme .total-glass-card.grand-glow {
    background: #f0f9ff !important;
    border-color: #0284c7 !important;
    color: #0c4a6e !important;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.12) !important;
}
html.light-theme #unified-grand-total {
    color: #0c4a6e !important;
    text-shadow: none !important;
}

/* Sales & Purchase Grand Total Row in Light Themes */
html.light-theme #row-grand-total {
    background: var(--bg-secondary) !important;
    border-top: 2px solid #10b981 !important;
    border-bottom: 2px solid #10b981 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
}
html.light-theme #lbl-grand-dynamic {
    color: #047857 !important;
    text-shadow: none !important;
}
html.light-theme #t-grand-dynamic-loc {
    background: #ecfdf5 !important;
    color: #065f46 !important;
    -webkit-text-fill-color: #065f46 !important;
    border: 2px solid #059669 !important;
    text-shadow: none !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15) !important;
}
html.light-theme #t-grand-currency-badge {
    background: #d1fae5 !important;
    color: #065f46 !important;
    border-color: #059669 !important;
}

/* POS Grid & Pro Table Contrast in Light Themes */
html.light-theme #pos-grid thead tr,
html.light-theme #pos-grid thead th,
html.light-theme table.pro-table thead tr,
html.light-theme table.pro-table thead th {
    background: var(--bg-secondary) !important;
    color: var(--text-primary) !important;
    border-bottom: 2px solid var(--border) !important;
}
html.light-theme #pos-grid tbody td,
html.light-theme #pos-grid tbody td input,
html.light-theme #pos-grid tbody td select,
html.light-theme #pos-grid tbody td span:not(.btn-icon):not(.add-row-btn),
html.light-theme #pos-grid tbody td .g-input,
html.light-theme #pos-grid tbody td .g-select,
html.light-theme #pos-grid tbody td .g-readonly,
html.light-theme #pos-grid tbody td .total-val,
html.light-theme #pos-grid tbody td .avail-val {
    color: var(--text-primary) !important;
}

/* Sticky Footers in Light Themes */
html.light-theme .sticky-footer,
html.light-theme .ds-footer,
html.light-theme .purchase-footer {
    background: var(--surface) !important;
    border-color: var(--border) !important;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08) !important;
}

/* ── 6. Small Solid/Dark Boxes Inside Light Themes Keep Bright Light Text ──
   ("ربما نجد لونها فاتح ولكن مربع صغير لونه غامق. في هذه الحالة يجب أن تكون الكتابة فاتحة") */
html.light-theme .m7-active-tag,
html.light-theme .m7-confirm-btn,
html.light-theme #inbound-receipts-badge-count,
html.light-theme #amount-currency-symbol,
html.light-theme #amount-foreign-currency-symbol,
html.light-theme #btn-auto-categorize,
html.light-theme #btn-auto-categorize *,
html.light-theme .btn-save-master,
html.light-theme .btn-save-master *,
html.light-theme .btn-submit,
html.light-theme .btn-submit *,
html.light-theme .v-arrow[style*="background: #"],
html.light-theme [style*="background: #ef4444"],
html.light-theme [style*="background:#ef4444"],
html.light-theme [style*="background: #10b981"],
html.light-theme [style*="background:#10b981"],
html.light-theme [style*="background: #3b82f6"],
html.light-theme [style*="background:#3b82f6"],
html.light-theme [style*="background: linear-gradient(135deg, #10b981"],
html.light-theme [style*="background: linear-gradient(135deg, #c084fc"],
html.light-theme [style*="background: linear-gradient(135deg, #ec4899"],
html.light-theme [style*="background: linear-gradient(135deg, #d97706"] {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
</style>

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
    'warm_cream': 'warm_cream', 'beige': 'warm_cream', 'warm-alabaster': 'warm_cream', 'light': 'warm_cream',
    'ultra_white': 'ultra_white', 'pure-platinum': 'ultra_white', 'white': 'ultra_white',
    'exec_gray': 'exec_gray', 'pearl-platinum': 'exec_gray',
    'midnight_obsidian': 'midnight_obsidian', 'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'navy': 'midnight_obsidian', 'dark': 'midnight_obsidian',
    'mocha_brown': 'mocha_brown', 'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'pure_oled': 'pure_oled', 'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled',
    'slate_twilight': 'slate_twilight', 'twilight-indigo': 'slate_twilight', 'slate': 'slate_twilight'
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
    } else if (st && document.body && document.body.lastElementChild !== st) {
        document.body.appendChild(st);
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        ensureMaster7OverrideLast();
        var cur = normalizeMaster7ThemeKey(document.documentElement.getAttribute('data-theme') || localStorage.getItem('magaies_active_theme'));
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
    themeKey = normalizeMaster7ThemeKey(themeKey);
    var isLight = MASTER_7_THEMES_INFO[themeKey].isLight;

    // Apply to DOM
    document.documentElement.setAttribute('data-theme', themeKey);
    if (document.body) document.body.setAttribute('data-theme', themeKey);

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

    // Save across all 5 unified keys for complete ecosystem consistency
    try {
        localStorage.setItem('magaies_active_theme', themeKey);
        localStorage.setItem('magaiees_global_theme', themeKey);
        localStorage.setItem('magaiees_mobile_theme', themeKey);
        localStorage.setItem('ui_theme', themeKey);
        localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
    } catch(e) {}

    syncMaster7ThemeActiveUI();

    // Broadcast event
    if (notify) {
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeKey, isLight: isLight } }));
    }
}

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
try {
    if (!isset($pdo) || !$pdo) {
        if (function_exists('getDBConnection')) {
            $pdo = getDBConnection();
        }
    }
    if (isset($pdo) && $pdo) {
        $_hdrTid = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '12';
        $stmtC = $pdo->prepare("SELECT data FROM customers WHERE tenant_id IN (?, '12', '1', 'default')");
        $stmtC->execute([$_hdrTid]);
        while ($rc = $stmtC->fetch(PDO::FETCH_ASSOC)) {
            $dc = json_decode($rc['data'] ?? '', true);
            if ($dc && !empty($dc['id'])) $_hdrSyncCustomers[$dc['id']] = $dc;
        }

        $stmtB = $pdo->prepare("SELECT data FROM banks WHERE tenant_id IN (?, '12', '1', 'default')");
        $stmtB->execute([$_hdrTid]);
        while ($rb = $stmtB->fetch(PDO::FETCH_ASSOC)) {
            $db = json_decode($rb['data'] ?? '', true);
            if ($db && !empty($db['id'])) $_hdrSyncBanks[$db['id']] = $db;
        }

        $stmtR = $pdo->prepare("SELECT data FROM receipts WHERE tenant_id IN (?, '12', '1', 'default') ORDER BY id ASC");
        $stmtR->execute([$_hdrTid]);
        while ($rr = $stmtR->fetch(PDO::FETCH_ASSOC)) {
            $dr = json_decode($rr['data'] ?? '', true);
            if ($dr && (!empty($dr['voucherNo']) || !empty($dr['id']))) {
                $k = $dr['voucherNo'] ?? $dr['id'];
                $_hdrSyncReceipts[$k] = $dr;
            }
        }
    }
} catch (Throwable $_eSync) {}
?>
<script id="erp-live-whatsapp-receipts-sync">
(function() {
    var srvCustomers = <?= json_encode(array_values($_hdrSyncCustomers), JSON_UNESCAPED_UNICODE) ?>;
    var srvBanks     = <?= json_encode(array_values($_hdrSyncBanks), JSON_UNESCAPED_UNICODE) ?>;
    var srvReceipts  = <?= json_encode(array_values($_hdrSyncReceipts), JSON_UNESCAPED_UNICODE) ?>;

    function mergeArrayIntoLocal(baseKey, serverItems, idField, altIdField) {
        if (!Array.isArray(serverItems) || serverItems.length === 0) return;
        var keys = [baseKey, 'tenant_12_' + baseKey];
        try {
            var u = JSON.parse(localStorage.getItem('user') || '{}');
            var tId = u.tenant_id || localStorage.getItem('currentTenantId') || '12';
            if (keys.indexOf('tenant_' + tId + '_' + baseKey) === -1) {
                keys.push('tenant_' + tId + '_' + baseKey);
            }
        } catch(e) {}

        var localArr = [];
        try {
            localArr = JSON.parse(localStorage.getItem(baseKey) || '[]');
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
            keys.forEach(function(k) {
                try { localStorage.setItem(k, jsonStr); } catch(e) {}
            });
        }
    }

    mergeArrayIntoLocal('erp_customers_v1', srvCustomers, 'id', 'name');
    mergeArrayIntoLocal('erp_banks_v1', srvBanks, 'id', 'name');
    mergeArrayIntoLocal('erp_receipts_v1', srvReceipts, 'voucherNo', 'id');

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.rebuildGLFromAllTables === 'function') {
            try { window.rebuildGLFromAllTables(false); } catch(e) {}
        }
        if (typeof window.buildAccountsList === 'function') {
            try { window.buildAccountsList(); } catch(e) {}
        }
        if (typeof window.extractFilters === 'function') {
            try { window.extractFilters(); } catch(e) {}
        }
    });
})();
</script>

