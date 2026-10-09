<link rel="stylesheet" href="css/cairo.css">
<link rel="stylesheet" href="css/tajawal.css">
<link rel="stylesheet" href="css/all.min.css">
<link rel="stylesheet" href="css/theme.css?v=20261008_v21themes_elegant">
<script src="js/offline-sync-engine.js?v=20261009_v1"></script>
<script src="js/desktop-theme-switcher.js?v=20261008_v21themes_elegant"></script>
<script src="js/hls.light.min.js"></script>
<script src="js/bban_auto_helper.js"></script>
<script src="js/master-banks-helper.js?v=20260924_v1"></script>
<script src="js/permissions-manager.js"></script>
<script src="js/audit-tracker.js?v=20260927"></script>
<script>
    window.magaieesDeferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        window.magaieesDeferredInstallPrompt = e;
    });

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

    // ═══════════════════════════════════════════════════════════════════
    // 🎨 UNIVERSAL 21 THEMES INITIALIZATION FOR DESKTOP & LAPTOP
    // ═══════════════════════════════════════════════════════════════════
    (function initErpSystemTheme() {
        try {
            var savedTheme = localStorage.getItem('magaies_active_theme') || 
                             localStorage.getItem('magaiees_mobile_theme') || 
                             localStorage.getItem('magaiees_theme') || 
                             localStorage.getItem('magaiees_global_theme');
            if (!savedTheme) {
                var m = document.cookie.match(/(?:^|;\s*)(?:magaies_active_theme|magaiees_mobile_theme|magaiees_theme)=([^;]+)/);
                if (m) savedTheme = decodeURIComponent(m[1]);
            }
            if (!savedTheme) savedTheme = 'warm-alabaster';
            
            var aliasMap = {
                'pure-platinum': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white', 'offwhite': 'ultra_white',
                'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
                'nordic-paper': 'warm_cream', 'sepia-brown': 'warm_cream', 'pale-gold': 'warm_cream', 'desert-mocha': 'warm_cream',
                'theme-imperial': 'warm_cream', 'cream': 'warm_cream',
                'pearl-platinum': 'exec_gray',
                'sunlight-titanium': 'solar_contrast', 'solar': 'solar_contrast',
                'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight',
                'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian',
                'ocean-navy': 'midnight_obsidian', 'petrol-cyan': 'midnight_obsidian', 'navy': 'midnight_obsidian', 'dark': 'midnight_obsidian',
                'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'oled': 'pure_oled'
            };
            if (aliasMap[savedTheme]) savedTheme = aliasMap[savedTheme];
            
            document.documentElement.setAttribute('data-theme', savedTheme);
            var lightThemes = ['ultra_white', 'warm_cream', 'exec_gray', 'solar_contrast'];
            if (lightThemes.indexOf(savedTheme) !== -1) {
                document.documentElement.classList.add('light-theme');
            } else {
                document.documentElement.classList.remove('light-theme');
            }
        } catch(e) {}
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
        var t = document.documentElement.getAttribute('data-theme') || 'warm-alabaster';
        if (document.body) {
            document.body.setAttribute('data-theme', t);
            var lightThemes = ['ultra_white', 'warm_cream', 'exec_gray', 'solar_contrast'];
            if (lightThemes.indexOf(t) !== -1) {
                document.body.classList.add('light-theme');
            } else {
                document.body.classList.remove('light-theme');
            }
            if (document.documentElement.classList.contains('has-sidebar')) {
                document.body.classList.add('has-sidebar');
            }
        }
    });
</script>
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
        brand: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
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
        ['settings.php','الإعدادات','#a3e635','set']
    ];
    var mk = function(ic,c){return '<svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">'+icons[ic]+'</svg>';};
    var nav = '';
    for(var i=0;i<items.length;i++){
        var it=items[i], ac=(p===it[0]?' active':'');
        nav+='<a style="cursor:pointer" data-href="'+it[0]+'" onclick="window.location.href=\''+it[0]+'\'" class="sidebar-item'+ac+'"><span class="sidebar-icon" style="color:'+it[2]+'">'+mk(it[3],it[2])+'</span><span class="sidebar-text">'+it[1]+'</span></a>';
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
    background: var(--hdr-bg, linear-gradient(135deg, rgba(20, 10, 35, 0.65), rgba(35, 15, 60, 0.45)));
    backdrop-filter: blur(28px) saturate(220%);
    -webkit-backdrop-filter: blur(28px) saturate(220%);
    border: 1px solid var(--hdr-border, rgba(192, 132, 252, 0.4));
    border-top: 2px solid var(--hdr-border, rgba(192, 132, 252, 0.9));
    border-radius: 20px;
    margin: 15px 25px;
    padding: 12px 25px;
    animation: neonPulseHeader 4s infinite alternate ease-in-out;
    position: relative;
    z-index: 1000;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: visible !important;
}
.magical-glass-header:hover {
    transform: translateY(-3px) scale(1.005);
    box-shadow: 0 15px 45px rgba(192, 132, 252, 0.6), inset 0 0 25px rgba(192, 132, 252, 0.4);
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
    background: var(--surface, rgba(20, 10, 40, 0.6));
    border: 1px solid rgba(192, 132, 252, 0.25);
    backdrop-filter: blur(12px);
    border-radius: 14px;
    transition: all 0.3s ease;
}
.magical-glass-header .clock-container:hover, 
.magical-glass-header .user-info:hover, 
.magical-glass-header #online-users-badge:hover,
.magical-glass-header #global-exchange-rate-box:hover {
    border-color: rgba(192, 132, 252, 0.8);
    box-shadow: 0 0 20px rgba(192, 132, 252, 0.4), inset 0 0 10px rgba(192, 132, 252, 0.2);
    transform: translateY(-2px);
}
.magical-glass-header .header-page-title {
    text-shadow: 0 0 15px rgba(192, 132, 252, 0.8), 0 0 30px rgba(192, 132, 252, 0.4);
    color: var(--text-primary, #fff);
}
.magical-glass-header button.fullscreen-toggle-btn,
.magical-glass-header button.theme-toggle-btn {
    background: rgba(168, 85, 247, 0.15) !important;
    border: 1px solid rgba(168, 85, 247, 0.4) !important;
    color: #e9d5ff !important;
    border-radius: 50% !important;
}
.magical-glass-header button.fullscreen-toggle-btn:hover,
.magical-glass-header button.theme-toggle-btn:hover {
    background: rgba(168, 85, 247, 0.4) !important;
    box-shadow: 0 0 15px rgba(168, 85, 247, 0.6) !important;
    transform: scale(1.15) rotate(8deg);
    color: #fff !important;
}
.magical-glass-header #header-clock {
    color: #d8b4fe !important;
    text-shadow: 0 0 12px rgba(192, 132, 252, 0.6) !important;
}
@media (max-width: 768px) {
    .magical-glass-header {
        margin: 10px !important;
        padding: 10px !important;
        border-radius: 16px !important;
    }
/* 🌟 2-Tier Stacked Controls Cluster (فوق بعض) */
.header-action-cluster {
    display: flex;
    flex-direction: column;
    gap: 5px;
    justify-content: center;
}
.header-cluster-top-row,
.header-cluster-bottom-row {
    display: flex;
    align-items: center;
    gap: 5px;
}
.desktop-theme-palette-btn {
    height: 29px !important;
    padding: 0 10px !important;
    font-size: 0.78rem !important;
    border-radius: 9px !important;
    gap: 5px !important;
    white-space: nowrap !important;
}
.desktop-theme-palette-btn .theme-btn-badge {
    padding: 1px 5px !important;
    font-size: 0.65rem !important;
}
.desktop-mobile-switch-btn {
    height: 29px !important;
    padding: 0 10px !important;
    font-size: 0.75rem !important;
    border-radius: 9px !important;
    gap: 5px !important;
    white-space: nowrap !important;
}
.desktop-mobile-switch-btn:hover {
    transform: translateY(-2px);
    border-color: #10b981 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.35), rgba(6, 182, 212, 0.35)) !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4) !important;
    color: #a7f3d0 !important;
}

.desktop-pwa-install-btn {
    height: 29px !important;
    padding: 0 10px !important;
    font-size: 0.75rem !important;
    border-radius: 9px !important;
    gap: 5px !important;
    white-space: nowrap !important;
}
.desktop-pwa-install-btn:hover {
    transform: translateY(-2px);
    border-color: #38bdf8 !important;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.35), rgba(99, 102, 241, 0.35)) !important;
    box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4) !important;
    color: #e0f2fe !important;
}

.header-tv-mini-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 29px !important;
    padding: 0 12px !important;
    font-size: 0.76rem !important;
    border-radius: 9px !important;
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
<header class="premium-header magical-glass-header <?php echo isset($header_theme_class) ? $header_theme_class : ''; ?>" style="display:flex; justify-content:space-between; align-items:center; gap:15px;">
    <!-- Left: Logo / Brand -->
    <div style="display:flex; align-items:center; gap:12px; flex:0 0 auto;">
        <div style="background:linear-gradient(135deg,#10b981,#34d399); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">📦</div>
        <div>
            <div style="color:#fff; font-weight:800; font-size:1rem; letter-spacing:0.5px;" id="header-company-name-top" data-i18n="sidebar.brand">مقاييس (MAGAIEES)</div>
            <div style="color:var(--text-secondary); font-size:0.7rem;" data-i18n="header.systemName">نظام إدارة الأعمال</div>
        </div>
    </div>

    <!-- Center: Page Title -->
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
    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; min-width:0; padding:0 12px; overflow:hidden;">
        <h2 class="header-page-title">
            <span class="header-page-title-icon"><?php echo $dynamic_icon; ?></span>
            <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'مقاييس (MAGAIEES)'; ?>
        </h2>
        <div id="dashboard-athkar-container" class="islamic-ticker-container" style="display: none;">
            <div class="islamic-ticker">
                سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
            </div>
        </div>
    </div>

    <!-- Right: Controls -->
    <div style="display:flex; align-items:center; gap:10px; flex:0 0 auto; justify-content:flex-end;">
        <!-- User Info & Online Users -->
        <div style="display:flex; flex-direction:column; align-items:flex-end; justify-content:center; gap:6px; margin:6px 0;">
            <div class="user-info" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); padding: 5px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); cursor: pointer; min-width: 90px;" onclick="document.getElementById('profile-modal').style.display='flex'">
                <div id="header-user-name" style="font-size: 0.95rem; font-weight: 800; color: #fff; text-align: center;"><script>try{var _u=JSON.parse(localStorage.getItem('user')||'{}');document.write(_u.fullName||_u.username||'مستخدم')}catch(e){document.write('مستخدم')}</script></div>
                <div id="header-user-role" style="font-size: 0.75rem; color: #38bdf8; text-align: center;" data-i18n="header.sysAdmin">مدير النظام</div>
            </div>
            <div id="online-users-badge" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:3px 12px; min-width:90px; text-align:center; box-shadow:inset 0 0 10px rgba(16,185,129,0.1); cursor:pointer;" onclick="alert('المستخدمين المتصلين حالياً: \n' + (window.onlineUsersList || []).join('\n'))">
                <span style="color:#6ee7b7; font-size:0.7rem; font-weight:800;"><span data-i18n="header.onlineNow">متصل الآن:</span> <span id="online-users-count" style="color:#fff; font-size:0.8rem;">1</span> <span style="font-size:0.6rem;">🟢</span></span>
            </div>
        </div>

        <!-- Clock & Exchange Rate -->
        <div class="header-time-stack" style="display:flex; flex-direction:column; gap:6px; align-items:center; margin:6px 0;">
            <div class="clock-container" style="display:flex; flex-direction:column; align-items:center; background:rgba(15,23,42,0.6); padding:4px 14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); min-width:110px; width:100%; box-sizing:border-box;">
                <span id="header-clock" style="color:#34d399; font-size:1.1rem; font-weight:800; font-variant-numeric:tabular-nums; letter-spacing:1px; text-shadow: 0 0 10px rgba(16,185,129,0.5);"><script>var _n=new Date();document.write(_n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}))</script></span>
                <span id="header-date-mini" style="color:var(--text-secondary); font-size:0.65rem; margin-top:1px;"><script>document.write(new Date().toLocaleDateString('ar-EG-u-nu-latn',{weekday:'short',day:'numeric',month:'short'}))</script></span>
            </div>
            <div id="global-exchange-rate-box" style="display:none; position:relative; cursor:pointer; background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3); border-radius:10px; padding:3px 12px; min-width:110px; text-align:center; box-shadow:inset 0 0 10px rgba(245,158,11,0.1); width:100%; box-sizing:border-box;" onclick="toggleExchangeHistory(event)">
                <span style="color:#fbd38d; font-size:0.75rem; font-weight:800;">
                    <span id="global-exchange-rate-trend" style="margin-left:4px;"></span>
                    <span data-i18n="header.exchangeRate">سعر الصرف:</span> 
                    <span id="global-exchange-rate-val" style="color:#fff; font-size:0.85rem;">-</span>
                </span>
            </div>
        </div>
        <!-- Theme & Actions Area: Mini Controls + 2-Tier Stacked Actions (فوق بعض) -->
        <div style="display:flex; align-items:center; gap:8px;">
            <?php
            $current_page_name = basename($_SERVER['PHP_SELF']);
            $show_header_currency = false; // Disabled by user request
            if ($show_header_currency):
                $header_curr = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
            ?>
            <?php endif; ?>

            <!-- Mini Utility Controls: Audit Tracker & Fullscreen & Quick Theme Toggle -->
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;">
                <div style="display:flex; align-items:center; gap:4px;">
                    <button class="audit-tracker-btn" type="button" onclick="window.openAuditTrackerModal()" style="background:rgba(56,189,248,0.18); border:1.5px solid rgba(56,189,248,0.4); border-radius:50%; width:29px; height:29px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:0.85rem; color:#38bdf8; transition:0.2s; box-shadow:0 0 8px rgba(56,189,248,0.2);" title="متعقب المستندات وسجل تتبع المستخدمين (من فعل ماذا) - اضغط Alt+T">🔍</button>
                    <button class="fullscreen-toggle-btn" type="button" onclick="toggleFullScreen()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:29px; height:29px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:0.95rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>
                    <button class="theme-toggle-btn" type="button" onclick="toggleTheme()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:29px; height:29px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:0.92rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.toggleTheme" title="تبديل السمة السريع (فاتح/داكن)">🌓</button>
                </div>
            </div>

            <!-- 🌟 2-Tier Stacked Action Controls Cluster (الأزرار فوق بعض: صف علوي وتحته صف سفلي) -->
            <div class="header-action-cluster" style="display:flex; flex-direction:column; gap:5px; justify-content:center;">
                
                <!-- Tier 1: الصف العلوي (فوق): سمات | نسخة الجوال | تثبيت التطبيق -->
                <div class="header-cluster-top-row" style="display:flex; align-items:center; gap:5px;">
                    <!-- 🎨 زر السمات -->
                    <button class="desktop-theme-palette-btn" id="header-desktop-theme-btn" type="button" onclick="openDesktopThemePaletteModal()" title="السمات السبعة المعتمدة والموحدة (7 سمات رئيسية) 🎨">
                        <span class="theme-btn-icon">🎨</span>
                        <span class="theme-btn-text">السمات</span>
                        <span class="theme-btn-badge">7</span>
                    </button>

                    <!-- 📱 زر نسخة الجوال -->
                    <button class="desktop-mobile-switch-btn" type="button" onclick="switchToMobileEdition()" style="background:linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(6, 182, 212, 0.2)); border:1.5px solid #10b981; border-radius:9px; height:29px; padding:0 10px; display:inline-flex; align-items:center; gap:5px; cursor:pointer; color:#34d399; font-weight:800; font-size:0.75rem; text-decoration:none; transition:0.2s; white-space:nowrap;" title="التبديل إلى نسخة الهاتف المحمول (الجوال)">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>نسخة الجوال 📱</span>
                    </button>

                    <!-- 📲 زر تثبيت التطبيق -->
                    <button class="desktop-pwa-install-btn" id="header-desktop-install-btn" type="button" onclick="magaieesTriggerInstall()" style="background:linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(99, 102, 241, 0.2)); border:1.5px solid #38bdf8; border-radius:9px; height:29px; padding:0 10px; display:inline-flex; align-items:center; gap:5px; cursor:pointer; color:#38bdf8; font-weight:800; font-size:0.75rem; text-decoration:none; transition:0.2s; white-space:nowrap;" title="تثبيت تطبيق مقاييس على جهازك كبرنامج مستقل">
                        <i class="fa-solid fa-download"></i>
                        <span>تثبيت التطبيق 📲</span>
                    </button>
                </div>

                <!-- Tier 2: الصف السفلي (تحته): التلفاز | الرجوع للخلف -->
                <div class="header-cluster-bottom-row" style="display:flex; align-items:center; gap:5px;">
                    <!-- 📺 SACRED TV & QURAN MINI CONTROLLER -->
                    <div id="header-media-control-wrap" style="position:relative; display:flex; align-items:center; flex:1;">
                        <button id="header-media-btn" type="button" onclick="toggleHeaderMediaPopover(event)" class="header-tv-mini-btn" style="width:100%; height:29px; padding:0 12px; font-size:0.76rem; border-radius:9px; justify-content:center; display:flex; align-items:center; gap:5px;" title="قنوات التلفاز والقرآن الكريم (بث حي في الخلفية)">
                            <span id="header-media-icon" class="header-tv-icon">📺</span>
                            <span id="header-media-label" class="header-tv-label">التلفاز</span>
                            <span id="header-media-dot" class="header-tv-status-dot" style="display: none;"></span>
                        </button>
                    </div>

                    <!-- ↩️ زر الرجوع للخلف (رويو) -->
                    <button id="magaiees-master-back-btn" class="magaiees-back-btn" onclick="magaieesGoBack()" style="flex:1; height:29px; padding:0 12px; font-size:0.76rem; border-radius:9px; margin:0 !important; justify-content:center; display:flex; align-items:center; gap:5px;" title="الرجوع للخلف (الشاشة السابقة)">
                        <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px;">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 19 12 12 5"></polyline>
                        </svg>
                        <span class="back-btn-text">الرجوع للخلف</span>
                    </button>
                </div>

            </div>
        </div>
        <script>
        if (!window.magaieesTriggerInstall) {
            window.magaieesTriggerInstall = function() {
                if (window.magaieesDeferredInstallPrompt) {
                    window.magaieesDeferredInstallPrompt.prompt();
                    window.magaieesDeferredInstallPrompt.userChoice.then(function(choiceResult) {
                        if (choiceResult.outcome === 'accepted') {
                            try { localStorage.setItem('magaiees_pwa_installed', '1'); } catch (e) {}
                        }
                        window.magaieesDeferredInstallPrompt = null;
                    });
                } else if (window.PWAManager && typeof PWAManager.triggerInstall === 'function') {
                    PWAManager.triggerInstall();
                } else {
                    alert('لتثبيت تطبيق مقاييس كبرنامج مستقل على جهازك:\n- اضغط على أيقونة التثبيت (⊕ أو تثبيت) في شريط عنوان المتصفح بالأعلى');
                }
            };
        }
        if (!window.switchToMobileEdition) {
            window.switchToMobileEdition = function() {
                try {
                    localStorage.setItem('magaiees_view_preference', 'mobile');
                    document.cookie = "magaiees_view_preference=mobile; path=/; max-age=31536000; SameSite=Lax";
                } catch(e) {}
                window.location.href = 'mobile-dashboard.php?force_mobile=1';
            };
        }
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
        
        // ═══════════════════════════════════════════════════════════════════
        // 🎨 DESKTOP & LAPTOP 21 ERGONOMIC THEMES CONTROLLER
        // ═══════════════════════════════════════════════════════════════════
        const DESKTOP_LIGHT_THEMES = ['ultra_white', 'warm_cream', 'exec_gray', 'solar_contrast'];
        const DESKTOP_THEME_NAMES = {
            'ultra_white': 'أبيض فائق النقاء Ultra-Clean White',
            'warm_cream': 'ورق سيبيا دافئ Warm Paper Sepia',
            'exec_gray': 'رمادي تنفيذي بارد Cool Executive Gray',
            'solar_contrast': 'شمس ساطعة عالي التباين Solar Sun',
            'slate_twilight': 'سليت مسائي هادئ Twilight Slate',
            'midnight_obsidian': 'كحلي فاحم ملكي Midnight Obsidian',
            'pure_oled': 'سواد مطلق Pure OLED Black'
        };

        window.MASTER_THEMES = [
            { key: 'ultra_white', name: 'أبيض فائق النقاء Ultra-Clean White', icon: '⚪', dot: '#2563eb', isLight: true, title: 'أبيض فائق النقاء - تباين أسود ناصع' },
            { key: 'warm_cream', name: 'ورق سيبيا دافئ Warm Paper Sepia', icon: '📜', dot: '#b45309', isLight: true, title: 'ورق سيبيا دافئ مريح للعين' },
            { key: 'exec_gray', name: 'رمادي تنفيذي بارد Cool Executive Gray', icon: '💼', dot: '#1d4ed8', isLight: true, title: 'رمادي تنفيذي بارد وأناقة مصرفية' },
            { key: 'solar_contrast', name: 'شمس ساطعة عالي التباين Solar Sun', icon: '☀️', dot: '#000000', isLight: true, title: 'شمس ساطعة عالي التباين WCAG AAA' },
            { key: 'slate_twilight', name: 'سليت مسائي هادئ Twilight Slate', icon: '🌌', dot: '#38bdf8', isLight: false, title: 'سليت مسائي هادئ' },
            { key: 'midnight_obsidian', name: 'كحلي فاحم ملكي Midnight Obsidian', icon: '💎', dot: '#00f2fe', isLight: false, title: 'كحلي فاحم ملكي - نيون فسفوري' },
            { key: 'pure_oled', name: 'سواد مطلق Pure OLED Black', icon: '🖤', dot: '#10b981', isLight: false, title: 'سواد مطلق OLED - تباين كامل 100%' }
        ];

        function openDesktopThemePaletteModal() {
            let modal = document.getElementById('desktopThemePaletteModal');
            if (!modal) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            modal.style.position = 'fixed';
            modal.style.inset = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.zIndex = '2147483647';
            document.body.style.overflow = 'hidden';
            filterDesktopThemeCategory('all');
            highlightActiveDesktopThemeCard();
        }

        function closeDesktopThemePaletteModal() {
            const modal = document.getElementById('desktopThemePaletteModal');
            if (!modal) return;
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function highlightActiveDesktopThemeCard() {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'warm-alabaster';
            const cards = document.querySelectorAll('.desktop-theme-card');
            cards.forEach(card => {
                const key = card.getAttribute('data-theme-key');
                const badge = card.querySelector('.desktop-active-indicator');
                if (key === activeTheme) {
                    card.classList.add('is-current-active');
                    if (badge) badge.style.display = 'inline-flex';
                } else {
                    card.classList.remove('is-current-active');
                    if (badge) badge.style.display = 'none';
                }
            });
            const lbl = document.getElementById('desktopCurrentThemeLabel');
            if (lbl) {
                lbl.textContent = DESKTOP_THEME_NAMES[activeTheme] || activeTheme;
            }
        }

        function filterDesktopThemeCategory(category) {
            const tabs = document.querySelectorAll('.desktop-theme-tab-btn');
            tabs.forEach(tab => {
                if (tab.getAttribute('data-cat') === category) {
                    tab.classList.add('active-tab');
                } else {
                    tab.classList.remove('active-tab');
                }
            });

            const cards = document.querySelectorAll('.desktop-theme-card');
            cards.forEach(card => {
                const cats = (card.getAttribute('data-category') || '').split(' ');
                if (category === 'all' || cats.includes(category)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function applyDesktopTheme(themeKey, showToast = true) {
            window.setUniversalTheme(themeKey, showToast);
        }

        window.openDesktopThemePaletteModal = openDesktopThemePaletteModal;
        window.closeDesktopThemePaletteModal = closeDesktopThemePaletteModal;
        window.applyDesktopTheme = applyDesktopTheme;
        window.filterDesktopThemeCategory = filterDesktopThemeCategory;

        window.setUniversalTheme = function(themeKey, showToast = true) {
            if (!themeKey) return;
            const aliasMap = {
                'pure-platinum': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white', 'offwhite': 'ultra_white',
                'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream',
                'nordic-paper': 'warm_cream', 'sepia-brown': 'warm_cream', 'pale-gold': 'warm_cream', 'desert-mocha': 'warm_cream',
                'theme-imperial': 'warm_cream', 'cream': 'warm_cream',
                'pearl-platinum': 'exec_gray',
                'sunlight-titanium': 'solar_contrast', 'solar': 'solar_contrast',
                'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight',
                'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian',
                'ocean-navy': 'midnight_obsidian', 'petrol-cyan': 'midnight_obsidian', 'navy': 'midnight_obsidian', 'dark': 'midnight_obsidian',
                'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'oled': 'pure_oled'
            };
            if (aliasMap[themeKey]) themeKey = aliasMap[themeKey];
            if (!DESKTOP_THEME_NAMES[themeKey]) themeKey = 'midnight_obsidian';

            const isLight = DESKTOP_LIGHT_THEMES.indexOf(themeKey) !== -1;

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

            try {
                localStorage.setItem('magaies_active_theme', themeKey);
                localStorage.setItem('magaiees_mobile_theme', themeKey);
                localStorage.setItem('magaiees_theme', themeKey);
                localStorage.setItem('magaiees_global_theme', themeKey);
                localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
                document.cookie = "magaies_active_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "magaiees_mobile_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "magaiees_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "erp_theme=" + (isLight ? 'light' : 'dark') + "; path=/; max-age=31536000; SameSite=Lax";
            } catch(e) {}

            highlightActiveDesktopThemeCard();

            const themeObj = (window.MASTER_THEMES && window.MASTER_THEMES.find(t => t.key === themeKey)) || null;

            const btn = document.querySelector('.theme-toggle-btn');
            if (btn) {
                const icon = themeObj ? themeObj.icon : (isLight ? '☀️' : '🌌');
                const name = DESKTOP_THEME_NAMES[themeKey] || (themeObj ? themeObj.name : themeKey);
                btn.innerHTML = `<span style="font-size:1.15rem; filter:drop-shadow(0 0 5px ${isLight ? 'rgba(0,0,0,0.3)' : 'rgba(56,189,248,0.5)'});">${icon}</span>`;
                btn.setAttribute('title', `${name} (انقر للتبديل السريع)`);
            }

            if (typeof initCharts === 'function') { try { initCharts(); } catch(err) {} }
            if (typeof initProCharts === 'function') { try { initProCharts(); } catch(err) {} }

            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeKey, isLight: isLight } }));

            if (showToast) {
                const displayName = DESKTOP_THEME_NAMES[themeKey] || (themeObj ? themeObj.name : themeKey);
                showThemeToast(displayName, isLight, themeObj ? themeObj.icon : (isLight ? '☀️' : '🌌'));
            }
        };

        window.toggleTheme = function() {
            const cur = document.documentElement.getAttribute('data-theme') || localStorage.getItem('magaiees_mobile_theme') || (localStorage.getItem('erp_theme') === 'light' ? 'ultra_white' : 'midnight_obsidian');
            let idx = window.MASTER_THEMES.findIndex(t => t.key === cur);
            if (idx === -1) {
                const isLight = DESKTOP_LIGHT_THEMES.indexOf(cur) !== -1;
                idx = isLight ? 0 : 2;
            }
            const nextIdx = (idx + 1) % window.MASTER_THEMES.length;
            const nextTheme = window.MASTER_THEMES[nextIdx];
            window.setUniversalTheme(nextTheme.key, true);
        };

        function showThemeToast(themeName, isLight, icon) {
            let toast = document.getElementById('universal-theme-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'universal-theme-toast';
                toast.style.cssText = `
                    position: fixed; top: 80px; left: 50%; transform: translateX(-50%) translateY(-20px);
                    background: rgba(15, 23, 42, 0.95); color: #ffffff; padding: 10px 22px;
                    border-radius: 20px; font-weight: 800; font-size: 0.92rem;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1.5px solid rgba(56,189,248,0.4);
                    z-index: 999999; display: flex; align-items: center; gap: 10px;
                    pointer-events: none; opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                    backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); direction: rtl;
                `;
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span style="font-size:1.3rem;">${icon || '🎨'}</span> <span>تم تفعيل: <strong>${themeName}</strong></span>`;
            if (isLight) {
                toast.style.background = '#ffffff';
                toast.style.color = '#0f172a';
                toast.style.borderColor = 'rgba(0,0,0,0.15)';
                toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            } else {
                toast.style.background = 'rgba(15, 23, 42, 0.96)';
                toast.style.color = '#ffffff';
                toast.style.borderColor = 'rgba(56,189,248,0.4)';
                toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.5)';
            }
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
            clearTimeout(window._themeToastTimer);
            window._themeToastTimer = setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-20px)';
            }, 1800);
        }

        // Close on Escape
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDesktopThemePaletteModal();
            }
        });

        // Apply immediately on load
        (function() {
            let initial = localStorage.getItem('magaies_active_theme') || localStorage.getItem('magaiees_mobile_theme') || localStorage.getItem('erp_theme') || 'midnight_obsidian';
            if (initial === 'light') initial = 'ultra_white';
            if (initial === 'dark') initial = 'midnight_obsidian';
            window.setUniversalTheme(initial, false);
        })();
        </script>
    </div>
</header>

<!-- ═══════════════════════════════════════════════════════════════════
     🎨 DESKTOP & LAPTOP 7 MASTER THEMES PALETTE MODAL
     ═══════════════════════════════════════════════════════════════════ -->
<div id="desktopThemePaletteModal" class="desktop-theme-modal-backdrop" onclick="if(event.target === this) closeDesktopThemePaletteModal();" style="display: none;">
    <div class="desktop-theme-modal-dialog" onclick="event.stopPropagation();" style="max-width: 780px;">
        
        <!-- Modal Top Bar -->
        <div class="desktop-theme-modal-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="desktop-theme-header-icon-box">🎨</div>
                <div>
                    <h2 class="desktop-theme-modal-title">السمات السبعة المعتمدة والموحدة (Magaiees 7 Master Themes)</h2>
                    <p class="desktop-theme-modal-subtitle">سمات مدروسة علمياً لراحة العين وأعلى تباين • تتزامن فورياً على كافة شاشات الكمبيوتر واللابتوب والجوال</p>
                </div>
            </div>
            <button type="button" class="desktop-theme-close-btn" onclick="closeDesktopThemePaletteModal()" title="إغلاق النافذة">✕</button>
        </div>

        <!-- Guidance Banner -->
        <div class="desktop-theme-guidance-banner">
            <span style="font-size:16px;">💡</span>
            <div><strong>السمات السبعة القياسية الموحدة:</strong> تم اعتماد 7 سمات رئيسية تحقق أعلى درجات الراحة البصرية وتوفر استهلاك البطارية بدون تشتيت مفرط.</div>
        </div>

        <!-- Filter Tabs -->
        <div class="desktop-theme-filter-row">
            <button type="button" class="desktop-theme-tab-btn active-tab" data-cat="all" onclick="filterDesktopThemeCategory('all')">
                <span>الكل</span>
                <span class="desktop-tab-count">7</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="light" onclick="filterDesktopThemeCategory('light')">
                <span>☀️ الفاتحة والنهارية</span>
                <span class="desktop-tab-count">4</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="dark" onclick="filterDesktopThemeCategory('dark')">
                <span>🌙 الداكنة والمسائية</span>
                <span class="desktop-tab-count">3</span>
            </button>
        </div>

        <!-- Themes Grid (The 7 Master Themes) -->
        <div class="desktop-theme-grid-container" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">

            <!-- 1. Ultra-Clean White -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="ultra_white" onclick="applyDesktopTheme('ultra_white')" style="--card-border:#2563eb; --card-bg:#ffffff; --card-text:#0f172a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">⚪ أبيض فائق النقاء Ultra-Clean White</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#ffffff; border:1px solid #2563eb;"></span>
                        <span class="desktop-swatch" style="background:#2563eb;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">إضاءة نهارية مكتبية ناصعة مع نصوص سوداء حادة لأعلى وضوح وتباين مكتبي.</p>
                <div class="desktop-theme-card-foot">Ultra White • نهاري مكتبي ناصع</div>
            </div>

            <!-- 2. Warm Paper Sepia -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="warm_cream" onclick="applyDesktopTheme('warm_cream')" style="--card-border:#b45309; --card-bg:#fbf7ee; --card-text:#1c1917;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">📜 ورق سيبيا دافئ Warm Paper Sepia</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#fbf7ee; border:1px solid #b45309;"></span>
                        <span class="desktop-swatch" style="background:#b45309;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">مستوحى من ورق الدفاتر الفاخرة لامتصاص الوهج الأزرق ومنع جفاف وإجهاد العين أثناء العمل الطويل.</p>
                <div class="desktop-theme-card-foot">Warm Cream • عاجي وورقي مريح للعين</div>
            </div>

            <!-- 3. Cool Executive Gray -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="exec_gray" onclick="applyDesktopTheme('exec_gray')" style="--card-border:#1d4ed8; --card-bg:#f1f5f9; --card-text:#0f172a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">💼 رمادي تنفيذي بارد Cool Executive Gray</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#f1f5f9; border:1px solid #1d4ed8;"></span>
                        <span class="desktop-swatch" style="background:#1d4ed8;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أسطح رمادية هادئة تعكس أناقة الشركات الكبرى والبنوك العالمية لتقليل الانعكاسات.</p>
                <div class="desktop-theme-card-foot">Executive Gray • رمادي تنفيذي مصرفي</div>
            </div>

            <!-- 4. High-Contrast Solar Sun -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="solar_contrast" onclick="applyDesktopTheme('solar_contrast')" style="--card-border:#000000; --card-bg:#ffff00; --card-text:#000000;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">☀️ شمس ساطعة عالي التباين Solar Sun</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#ffff00; border:1px solid #000000;"></span>
                        <span class="desktop-swatch" style="background:#000000;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أعلى تباين معتمد طبياً (WCAG AAA) للقراءة الفورية تحت أشعة الشمس المباشرة أو المخازن المفتوحة.</p>
                <div class="desktop-theme-card-foot">Solar Contrast • فائق التباين تحت الشمس</div>
            </div>

            <!-- 5. Twilight Slate -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="slate_twilight" onclick="applyDesktopTheme('slate_twilight')" style="--card-border:#38bdf8; --card-bg:#0f172a; --card-text:#f8fafc;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌌 سليت مسائي هادئ Twilight Slate</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0f172a; border:1px solid #38bdf8;"></span>
                        <span class="desktop-swatch" style="background:#38bdf8;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">رمادي داكن مائل للزرقة هادئ جداً لأوقات المساء والسكون البصري دون سواد تام.</p>
                <div class="desktop-theme-card-foot">Twilight Slate • رمادي مسائي هادئ</div>
            </div>

            <!-- 6. Midnight Obsidian -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="midnight_obsidian" onclick="applyDesktopTheme('midnight_obsidian')" style="--card-border:#00f2fe; --card-bg:#0a1128; --card-text:#f8fafc;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">💎 كحلي فاحم ملكي Midnight Obsidian</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0a1128; border:1px solid #00f2fe;"></span>
                        <span class="desktop-swatch" style="background:#00f2fe;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">السمة المصرفية الفخمة باللون الكحلي السحيق والنيون والتركيز المالي العالي.</p>
                <div class="desktop-theme-card-foot">Midnight Obsidian • كحلي مصرفي ملكي</div>
            </div>

            <!-- 7. Pure OLED Black -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="pure_oled" onclick="applyDesktopTheme('pure_oled')" style="--card-border:#10b981; --card-bg:#000000; --card-text:#ffffff;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🖤 سواد مطلق Pure OLED Black</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#000000; border:1px solid #10b981;"></span>
                        <span class="desktop-swatch" style="background:#10b981;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">سواد حالك 100% لإطفاء بكسلات الشاشة وتوفير طاقة بطارية اللابتوب والجوال بنسبة 30%.</p>
                <div class="desktop-theme-card-foot">Pure OLED • سواد مطلق وتوفير طاقة</div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="desktop-theme-modal-footer">
            <div class="desktop-theme-footer-status">
                <span>السمة النشطة حالياً:</span>
                <strong id="desktopCurrentThemeLabel" style="color:var(--primary-accent, #38bdf8);">كحلي فاحم ملكي</strong>
            </div>
            <button type="button" class="desktop-theme-footer-close-btn" onclick="closeDesktopThemePaletteModal()">
                ✓ تم والاحتفاظ بالسمة
            </button>
        </div>

    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════════
   🎨 STYLING FOR DESKTOP THEMES MODAL (WCAG CONTRAST ENGINE)
   ═══════════════════════════════════════════════════════════════════ */
.desktop-theme-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    animation: desktopThemeFadeIn 0.2s ease-out;
}
@keyframes desktopThemeFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.desktop-theme-modal-dialog {
    background: #0f172a;
    color: #f8fafc;
    border: 2px solid #334155;
    border-radius: 24px;
    width: 95%;
    max-width: 940px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    overflow: hidden;
    position: relative;
    font-family: 'Tajawal', system-ui, sans-serif;
}
html.light-theme .desktop-theme-modal-dialog {
    background: #ffffff;
    color: #0f172a;
    border-color: #cbd5e1;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.desktop-theme-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-bottom: 1.5px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
}
html.light-theme .desktop-theme-modal-header {
    border-bottom: 1.5px solid #e2e8f0;
    background: #f8fafc;
}
.desktop-theme-header-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(16, 185, 129, 0.2));
    border: 1.5px solid rgba(245, 158, 11, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.desktop-theme-modal-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 900;
    letter-spacing: -0.01em;
}
.desktop-theme-modal-subtitle {
    margin: 3px 0 0 0;
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
}
html.light-theme .desktop-theme-modal-subtitle {
    color: #64748b;
}
.desktop-theme-close-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #cbd5e1;
    font-size: 14px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.2s;
}
.desktop-theme-close-btn:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
}
.desktop-theme-guidance-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 20px;
    margin: 14px 24px 8px 24px;
    border-radius: 14px;
    background: rgba(245, 158, 11, 0.1);
    border: 1.5px solid rgba(245, 158, 11, 0.35);
    font-size: 0.8rem;
    line-height: 1.45;
    color: #fde68a;
}
html.light-theme .desktop-theme-guidance-banner {
    background: #fefce8;
    border-color: #fde047;
    color: #713f12;
}
.desktop-theme-filter-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 24px;
    overflow-x: auto;
}
.desktop-theme-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
    white-space: nowrap;
}
html.light-theme .desktop-theme-tab-btn {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #334155;
}
.desktop-theme-tab-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
}
html.light-theme .desktop-theme-tab-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.desktop-theme-tab-btn.active-tab {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #000000;
    border-color: #f59e0b;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}
.desktop-tab-count {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 20px;
    padding: 2px 7px;
    font-size: 0.72rem;
    font-weight: 900;
}
.desktop-theme-grid-container {
    flex: 1;
    overflow-y: auto;
    padding: 14px 24px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
@media (max-width: 900px) {
    .desktop-theme-grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .desktop-theme-grid-container {
        grid-template-columns: 1fr;
    }
}
.desktop-theme-card {
    background: var(--card-bg, #1e293b);
    color: var(--card-text, #ffffff);
    border: 2px solid var(--card-border, #334155);
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 8px;
    transition: all 0.2s ease;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.desktop-theme-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    filter: brightness(1.05);
}
.desktop-theme-card.is-current-active {
    outline: 3px solid #f59e0b;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.5) !important;
}
.desktop-theme-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    flex-wrap: wrap;
}
.desktop-theme-card-title {
    font-size: 0.88rem;
    font-weight: 900;
}
.desktop-rec-badge {
    background: #f59e0b;
    color: #000000;
    font-size: 0.65rem;
    font-weight: 900;
    padding: 2px 7px;
    border-radius: 6px;
}
.desktop-active-indicator {
    background: #10b981;
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 900;
    padding: 2px 7px;
    border-radius: 6px;
}
.desktop-swatch-group {
    display: flex;
    align-items: center;
    gap: 4px;
}
.desktop-swatch {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
.desktop-theme-card-desc {
    font-size: 0.75rem;
    line-height: 1.4;
    opacity: 0.9;
    margin: 0;
    font-weight: 500;
}
.desktop-theme-card-foot {
    font-size: 0.68rem;
    font-weight: 800;
    opacity: 0.85;
    border-top: 1px dashed rgba(125, 125, 125, 0.25);
    padding-top: 6px;
}
.desktop-theme-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-top: 1.5px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.02);
}
html.light-theme .desktop-theme-modal-footer {
    border-top: 1.5px solid #e2e8f0;
    background: #f8fafc;
}
.desktop-theme-footer-status {
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
}
.desktop-theme-footer-close-btn {
    padding: 8px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 900;
    cursor: pointer;
    transition: 0.2s;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.desktop-theme-footer-close-btn:hover {
    filter: brightness(1.1);
    transform: scale(1.02);
}
</style>

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
<script src="js/magaiees_back.js?v=<?php echo file_exists(__DIR__ . '/../js/magaiees_back.js') ? filemtime(__DIR__ . '/../js/magaiees_back.js') : time(); ?>" defer></script>
<script src="js/auto-header-scroll.js?v=<?php echo file_exists(__DIR__ . '/../js/auto-header-scroll.js') ? filemtime(__DIR__ . '/../js/auto-header-scroll.js') : time(); ?>" defer></script>
