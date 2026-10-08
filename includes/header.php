<link rel="stylesheet" href="css/theme.css?v=20261008_v21themes_elegant">
<script src="js/desktop-theme-switcher.js?v=20261008_v21themes_elegant"></script>
<script src="js/hls.light.min.js"></script>
<script src="js/bban_auto_helper.js"></script>
<script src="js/master-banks-helper.js?v=20260924_v1"></script>
<script src="js/permissions-manager.js"></script>
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
                'ultra_white': 'pure-platinum', 'warm_cream': 'warm-alabaster', 'exec_gray': 'pearl-platinum',
                'solar_contrast': 'sunlight-titanium', 'solar': 'sunlight-titanium',
                'slate_twilight': 'midnight-executive', 'midnight_obsidian': 'midnight-executive',
                'pure_oled': 'midnight', 'navy': 'navy-kohl', 'forest': 'emerald-forest',
                'theme-imperial': 'warm-alabaster'
            };
            if (aliasMap[savedTheme]) savedTheme = aliasMap[savedTheme];
            
            document.documentElement.setAttribute('data-theme', savedTheme);
            var lightThemes = ['warm-alabaster', 'pure-platinum', 'beige', 'ivory', 'offwhite', 'light-linen', 'pearl-platinum', 'nordic-paper'];
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
            var lightThemes = ['warm-alabaster', 'pure-platinum', 'beige', 'ivory', 'offwhite', 'light-linen', 'pearl-platinum', 'nordic-paper'];
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
        <!-- Theme & Currency -->
        <div style="display:flex; align-items:center; gap:8px;">
            <?php
            $current_page_name = basename($_SERVER['PHP_SELF']);
            $show_header_currency = false; // Disabled by user request
            if ($show_header_currency):
                $header_curr = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
            ?>
            <?php endif; ?>

            <!-- Mini Utility Controls: Audit Tracker & Fullscreen & Quick Theme Toggle -->
            <div style="display:flex; align-items:center; gap:5px;">
                <button class="audit-tracker-btn" type="button" onclick="window.openAuditTrackerModal()" style="background:rgba(56,189,248,0.18); border:1.5px solid rgba(56,189,248,0.4); border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:0.95rem; color:#38bdf8; transition:0.2s; box-shadow:0 0 8px rgba(56,189,248,0.2);" title="متعقب المستندات وسجل تتبع المستخدمين (من فعل ماذا) - اضغط Alt+T">🔍</button>
                <button class="fullscreen-toggle-btn" type="button" onclick="toggleFullScreen()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.05rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>
                <button class="theme-toggle-btn" type="button" onclick="toggleTheme()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.toggleTheme" title="تبديل السمة السريع (فاتح/داكن)">🌓</button>
            </div>

            <!-- 🎨 Standout Luxury Theme Switcher Button (Desktop & Laptop) -->
            <button class="desktop-theme-palette-btn" id="header-desktop-theme-btn" type="button" onclick="openDesktopThemePaletteModal()" title="مصفوفة سمات النظام وراحة العين (21 سمة علمية معتمدة)">
                <span class="theme-btn-icon">🎨</span>
                <span class="theme-btn-text">السمات</span>
                <span class="theme-btn-badge">21</span>
            </button>
        </div>

        <!-- 📺 SACRED TV & QURAN MINI CONTROLLER (1cm x 2.5cm - Non-Obtrusive) -->
        <div id="header-media-control-wrap" style="position:relative; display:flex; align-items:center;">
            <button id="header-media-btn" type="button" onclick="toggleHeaderMediaPopover(event)" class="header-tv-mini-btn" title="قنوات التلفاز والقرآن الكريم (بث حي في الخلفية)">
                <span id="header-media-icon" class="header-tv-icon">📺</span>
                <span id="header-media-label" class="header-tv-label">التلفاز</span>
                <span id="header-media-dot" class="header-tv-status-dot" style="display: none;"></span>
            </button>
        </div>

        <!-- Universal Go Back Master Button (Amber-Gold Luxury) -->
        <button id="magaiees-master-back-btn" class="magaiees-back-btn" onclick="magaieesGoBack()" title="الرجوع للخلف (الشاشة السابقة)">
            <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 19 12 12 5"></polyline>
            </svg>
            <span class="back-btn-text">الرجوع للخلف</span>
        </button>
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
        
        // ═══════════════════════════════════════════════════════════════════
        // 🎨 DESKTOP & LAPTOP 21 ERGONOMIC THEMES CONTROLLER
        // ═══════════════════════════════════════════════════════════════════
        const DESKTOP_LIGHT_THEMES = ['warm-alabaster', 'pure-platinum', 'beige', 'ivory', 'offwhite', 'light-linen', 'pearl-platinum', 'nordic-paper', 'ultra_white', 'warm_cream', 'exec_gray'];
        const DESKTOP_THEME_NAMES = {
            'warm-alabaster': 'العاجي الدافئ',
            'pure-platinum': 'الفضي النقي',
            'midnight-executive': 'كحلي منتصف الليل',
            'obsidian-charcoal': 'حجر السج الجرافيتي',
            'beige': 'البيج الدافئ المريح',
            'ivory': 'العاجي الملكي الكلاسيكي',
            'offwhite': 'الأوف وايت الحديث المطفأ',
            'light-linen': 'الكتان الطبيعي المريح',
            'pearl-platinum': 'اللؤلؤي الفضي المريح',
            'nordic-paper': 'الورقي النوردي المريح',
            'navy-kohl': 'الكحلي الملكي الليلي',
            'royal-blue': 'الأزرق الزفيري الحيوي',
            'ocean-navy': 'الأزرق المحيطي العميق',
            'petrol-cyan': 'الأزرق البترولي والتيل',
            'twilight-indigo': 'أزرق الشفق والإنديجو',
            'midnight': 'الليل الملكي الداكن',
            'sepia-brown': 'البني الإسبريسو والورق القديم',
            'pale-gold': 'الذهبي الباهت والفخامة',
            'emerald-forest': 'زمرد الغابات لصفاء الذهن',
            'desert-mocha': 'الرمال الصحراوية وموكا',
            'sunlight-titanium': 'التيتانيوم الشمسي فائق التباين',
            'ultra_white': 'السمة البيضاء الناصعة',
            'warm_cream': 'السمة الفاتحة (بيج دافئ)',
            'midnight_obsidian': 'السمة الكحلية الموحدة',
            'pure_oled': 'السمة السوداء الفاحمة'
        };

        window.MASTER_THEMES = [
            { key: 'ultra_white', name: 'السمة البيضاء الناصعة', icon: '⚪', dot: '#ffffff', isLight: true, title: 'أبيض فائق النقاء - تباين أسود ناصع' },
            { key: 'warm_cream', name: 'السمة الفاتحة (بيج دافئ)', icon: '📜', dot: '#fbf7ee', isLight: true, title: 'ورق سيبيا دافئ مريح للعين' },
            { key: 'midnight_obsidian', name: 'السمة الكحلية الزرقاء', icon: '🌌', dot: '#0b0f19', isLight: false, title: 'كحلي أزرق ملكي - نيون فسفوري' },
            { key: 'pure_oled', name: 'السمة السوداء الفاحمة', icon: '🖤', dot: '#000000', isLight: false, title: 'سواد مطلق OLED - تباين كامل 100%' }
        ];

        function openDesktopThemePaletteModal() {
            const modal = document.getElementById('desktopThemePaletteModal');
            if (!modal) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
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

        window.setUniversalTheme = function(themeKey, showToast = true) {
            if (!themeKey) return;
            const aliasMap = {
                'light': 'ultra_white',
                'white': 'ultra_white',
                'dark': 'midnight_obsidian',
                'navy': 'midnight_obsidian',
                'midnight': 'pure_oled',
                'oled': 'pure_oled',
                'cream': 'warm_cream',
                'solar': 'solar_contrast',
                'forest': 'slate_twilight'
            };
            if (aliasMap[themeKey]) themeKey = aliasMap[themeKey];

            let themeObj = window.MASTER_THEMES.find(t => t.key === themeKey);
            const isLight = DESKTOP_LIGHT_THEMES.indexOf(themeKey) !== -1 || (themeObj && themeObj.isLight);

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
                localStorage.setItem('erp_theme', isLight ? 'light' : 'dark');
                document.cookie = "magaies_active_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "magaiees_mobile_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "magaiees_theme=" + encodeURIComponent(themeKey) + "; path=/; max-age=31536000; SameSite=Lax";
                document.cookie = "erp_theme=" + (isLight ? 'light' : 'dark') + "; path=/; max-age=31536000; SameSite=Lax";
            } catch(e) {}

            highlightActiveDesktopThemeCard();

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
     🎨 DESKTOP & LAPTOP 21 ERGONOMIC THEMES PALETTE MODAL
     ═══════════════════════════════════════════════════════════════════ -->
<div id="desktopThemePaletteModal" class="desktop-theme-modal-backdrop" onclick="if(event.target === this) closeDesktopThemePaletteModal();" style="display: none;">
    <div class="desktop-theme-modal-dialog" onclick="event.stopPropagation();">
        
        <!-- Modal Top Bar -->
        <div class="desktop-theme-modal-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="desktop-theme-header-icon-box">🎨</div>
                <div>
                    <h2 class="desktop-theme-modal-title">مصفوفة سمات النظام وراحة العين (21 سمة علمية معتمدة)</h2>
                    <p class="desktop-theme-modal-subtitle">تصميم سويسري عالي التباين متوافق مع معايير WCAG AAA • حماية الشبكية ومنع إجهاد العين أثناء العمل المحاسبي الطويل</p>
                </div>
            </div>
            <button type="button" class="desktop-theme-close-btn" onclick="closeDesktopThemePaletteModal()" title="إغلاق النافذة">✕</button>
        </div>

        <!-- Guidance Banner -->
        <div class="desktop-theme-guidance-banner">
            <span style="font-size:16px;">💡</span>
            <div><strong>استشارة خبير علم النفس والألوان:</strong> الألوان الترابية والعاجية والبيج تمتص الوهج الأزرق بنسبة 95% وتريح البؤبؤ، ودرجات الأزرق والكحلي تحفز التركيز الذهني والتدقيق المالي، والداكنة توفر أقصى راحة في الإضاءة الخافتة.</div>
        </div>

        <!-- Filter Tabs -->
        <div class="desktop-theme-filter-row">
            <button type="button" class="desktop-theme-tab-btn active-tab" data-cat="recommended" onclick="filterDesktopThemeCategory('recommended')">
                <span>🌟 الموصى بها</span>
                <span class="desktop-tab-count">4</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="all" onclick="filterDesktopThemeCategory('all')">
                <span>الكل</span>
                <span class="desktop-tab-count">21</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="light" onclick="filterDesktopThemeCategory('light')">
                <span>☀️ الفاتحة والعاجي</span>
                <span class="desktop-tab-count">8</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="blue" onclick="filterDesktopThemeCategory('blue')">
                <span>🌊 درجات الأزرق</span>
                <span class="desktop-tab-count">6</span>
            </button>
            <button type="button" class="desktop-theme-tab-btn" data-cat="dark" onclick="filterDesktopThemeCategory('dark')">
                <span>🌙 الداكنة والملكية</span>
                <span class="desktop-tab-count">8</span>
            </button>
        </div>

        <!-- Themes Grid (All 21 Themes) -->
        <div class="desktop-theme-grid-container">

            <!-- 0.1 WARM ALABASTER -->
            <div class="desktop-theme-card" data-category="recommended light" data-theme-key="warm-alabaster" onclick="applyDesktopTheme('warm-alabaster')" style="--card-border:#8B6F4E; --card-bg:#F6F0E6; --card-text:#1C1917;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">📜 العاجي الدافئ</span>
                    <span class="desktop-rec-badge">🌟 موصى بها</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#F6F0E6; border:1px solid #8B6F4E;"></span>
                        <span class="desktop-swatch" style="background:#FFFFFF; border:1px solid #8B6F4E;"></span>
                        <span class="desktop-swatch" style="background:#8B6F4E;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أزرار بيضاء ناصعة بحواف بنية داكنة فوق أرضية عاجية بيج خفيفة، تباين مثالي فائق الراحة والرقي.</p>
                <div class="desktop-theme-card-foot">Warm Alabaster • قمة الرقي • تباين فائق وعاجي هادئ</div>
            </div>

            <!-- 0.2 PURE PLATINUM -->
            <div class="desktop-theme-card" data-category="recommended light" data-theme-key="pure-platinum" onclick="applyDesktopTheme('pure-platinum')" style="--card-border:#64748B; --card-bg:#EEF2F6; --card-text:#0F172A;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">❄️ الفضي النقي</span>
                    <span class="desktop-rec-badge">🌟 موصى بها</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#EEF2F6; border:1px solid #64748B;"></span>
                        <span class="desktop-swatch" style="background:#FFFFFF; border:1px solid #64748B;"></span>
                        <span class="desktop-swatch" style="background:#0F172A;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أزرار ناصعة البياض بحواف بلاتينية محددة على أرضية فضية نقية هادئة، دقة تحليلية سويسرية فائقة التباين.</p>
                <div class="desktop-theme-card-foot">Pure Platinum • قمة الرقي • تباين نقي وفضي هادئ</div>
            </div>

            <!-- 0.3 MIDNIGHT EXECUTIVE -->
            <div class="desktop-theme-card" data-category="recommended blue dark" data-theme-key="midnight-executive" onclick="applyDesktopTheme('midnight-executive')" style="--card-border:#1E293B; --card-bg:#0B0F17; --card-text:#F1F5F9;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌌 كحلي منتصف الليل</span>
                    <span class="desktop-rec-badge">🌟 موصى بها</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0B0F17; border:1px solid #1E293B;"></span>
                        <span class="desktop-swatch" style="background:#131B2A;"></span>
                        <span class="desktop-swatch" style="background:#38BDF8;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">كحلي ليلي سحيق يبعث الأمان المالي والهدوء العصبي، يمنع إجهاد العين في العمل الليلي.</p>
                <div class="desktop-theme-card-foot">Midnight Executive • قمة الرقي • داكن ملكي</div>
            </div>

            <!-- 0.4 OBSIDIAN CHARCOAL -->
            <div class="desktop-theme-card" data-category="recommended dark" data-theme-key="obsidian-charcoal" onclick="applyDesktopTheme('obsidian-charcoal')" style="--card-border:#2C2C30; --card-bg:#121214; --card-text:#EDEDED;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🪨 حجر السج الجرافيتي</span>
                    <span class="desktop-rec-badge">🌟 موصى بها</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#121214; border:1px solid #2C2C30;"></span>
                        <span class="desktop-swatch" style="background:#1C1C1F;"></span>
                        <span class="desktop-swatch" style="background:#EDEDED;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">فحم جرافيتي مطفأ وناعم مستوحى من الساعات السويسرية، راحة مطلقة لشاشات OLED.</p>
                <div class="desktop-theme-card-foot">Obsidian Charcoal • قمة الرقي • داكن مريح</div>
            </div>

            <!-- 1. BEIGE -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="beige" onclick="applyDesktopTheme('beige')" style="--card-border:#d6c5b0; --card-bg:#f5ebe0; --card-text:#23190e;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌾 البيج الدافئ المريح</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#f5ebe0; border:1px solid #d6c5b0;"></span>
                        <span class="desktop-swatch" style="background:#b45309;"></span>
                        <span class="desktop-swatch" style="background:#23190e;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">درجة بيج رملي طبيعي دافئ يمتص الوهج، مثالي لقراءة الدفاتر لساعات طويلة.</p>
                <div class="desktop-theme-card-foot">Warm Sandstone Beige • فاتح مريح</div>
            </div>

            <!-- 2. IVORY -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="ivory" onclick="applyDesktopTheme('ivory')" style="--card-border:#ded6c3; --card-bg:#faf7f0; --card-text:#1c1813;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🥛 العاجي الملكي الكلاسيكي</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#faf7f0; border:1px solid #ded6c3;"></span>
                        <span class="desktop-swatch" style="background:#d97706;"></span>
                        <span class="desktop-swatch" style="background:#1c1813;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">نعومة اللون العاجي الكريمي الهادئ، يريح البؤبؤ ولا يجهد العصب البصري.</p>
                <div class="desktop-theme-card-foot">Royal Cream Ivory • عاجي ناعم</div>
            </div>

            <!-- 3. OFF-WHITE -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="offwhite" onclick="applyDesktopTheme('offwhite')" style="--card-border:#d1d5db; --card-bg:#f9fafb; --card-text:#111827;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">📜 الأوف وايت الحديث المطفأ</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#f9fafb; border:1px solid #d1d5db;"></span>
                        <span class="desktop-swatch" style="background:#2563eb;"></span>
                        <span class="desktop-swatch" style="background:#111827;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أوف وايت ألاباستر عصري بدون بياض ساطع، وضوح حاد للأرقام والخطوط.</p>
                <div class="desktop-theme-card-foot">Modern Matte Off-White • مطفأ نقي</div>
            </div>

            <!-- 4. LIGHT LINEN -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="light-linen" onclick="applyDesktopTheme('light-linen')" style="--card-border:#cfc5b3; --card-bg:#ede7dc; --card-text:#211f1a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🧵 الكتان الطبيعي المريح</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#ede7dc; border:1px solid #cfc5b3;"></span>
                        <span class="desktop-swatch" style="background:#4d7c0f;"></span>
                        <span class="desktop-swatch" style="background:#211f1a;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">طابع قماش الكتان العضوي الهادئ، يمنح شعوراً بالسكينة أثناء ضغط الحسابات.</p>
                <div class="desktop-theme-card-foot">Soft Natural Linen • كتان هادئ</div>
            </div>

            <!-- 5. PEARL PLATINUM -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="pearl-platinum" onclick="applyDesktopTheme('pearl-platinum')" style="--card-border:#cbd3dc; --card-bg:#e9ecef; --card-text:#0f172a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🦪 اللؤلؤي الفضي المريح</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#e9ecef; border:1px solid #cbd3dc;"></span>
                        <span class="desktop-swatch" style="background:#0284c7;"></span>
                        <span class="desktop-swatch" style="background:#0f172a;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">رمادي لؤلؤي بلاتيني بارد وراقي جداً، يقلل انعكاس الضوء المكتبي.</p>
                <div class="desktop-theme-card-foot">Platinum Pearl Grey • لؤلؤي بارد</div>
            </div>

            <!-- 6. NORDIC PAPER -->
            <div class="desktop-theme-card" data-category="light" data-theme-key="nordic-paper" onclick="applyDesktopTheme('nordic-paper')" style="--card-border:#cbd5e1; --card-bg:#f8fafc; --card-text:#0f172a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">📄 الورقي النوردي المريح</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#f8fafc; border:1px solid #cbd5e1;"></span>
                        <span class="desktop-swatch" style="background:#2563eb;"></span>
                        <span class="desktop-swatch" style="background:#0f172a;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">ملمس الورق المحاسبي النوردي الفاخر، أعلى مقروئية في ضوء النهار الطبيعي.</p>
                <div class="desktop-theme-card-foot">Nordic Paper Light • نهار مشرق</div>
            </div>

            <!-- 7. NAVY KOHL -->
            <div class="desktop-theme-card" data-category="blue dark" data-theme-key="navy-kohl" onclick="applyDesktopTheme('navy-kohl')" style="--card-border:#1a2d5a; --card-bg:#050c1e; --card-text:#f8fafc;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌌 الكحلي الملكي الليلي</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#050c1e; border:1px solid #1a2d5a;"></span>
                        <span class="desktop-swatch" style="background:#38bdf8;"></span>
                        <span class="desktop-swatch" style="background:#fbbf24;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">كحلي عميق فاخر مع نصوص بيضاء ناصعة ولمسات ذهبية، هيبة رجال الأعمال.</p>
                <div class="desktop-theme-card-foot">Deep Midnight Navy • كحلي تنفيذي</div>
            </div>

            <!-- 8. ROYAL BLUE -->
            <div class="desktop-theme-card" data-category="blue" data-theme-key="royal-blue" onclick="applyDesktopTheme('royal-blue')" style="--card-border:#1e3e78; --card-bg:#0a1b38; --card-text:#ffffff;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🔷 الأزرق الزفيري الحيوي</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0a1b38; border:1px solid #1e3e78;"></span>
                        <span class="desktop-swatch" style="background:#60a5fa;"></span>
                        <span class="desktop-swatch" style="background:#34d399;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أزرق ملكي ساطع ينشط الذاكرة وسرعة البديهة في إدخال الفواتير السريعة.</p>
                <div class="desktop-theme-card-foot">Executive Royal Blue • أزرق ملكي</div>
            </div>

            <!-- 9. OCEAN NAVY -->
            <div class="desktop-theme-card" data-category="blue" data-theme-key="ocean-navy" onclick="applyDesktopTheme('ocean-navy')" style="--card-border:#1e3a8a; --card-bg:#0c1a2e; --card-text:#e0f2fe;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌊 الأزرق المحيطي العميق</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0c1a2e; border:1px solid #1e3a8a;"></span>
                        <span class="desktop-swatch" style="background:#38bdf8;"></span>
                        <span class="desktop-swatch" style="background:#bae6fd;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">يعزز التفكير المنطقي والتحليلي أثناء مراجعة الحسابات ومطابقة القيود.</p>
                <div class="desktop-theme-card-foot">Royal Oceanic Navy • منطق وتحليل</div>
            </div>

            <!-- 10. PETROL CYAN -->
            <div class="desktop-theme-card" data-category="blue" data-theme-key="petrol-cyan" onclick="applyDesktopTheme('petrol-cyan')" style="--card-border:#134658; --card-bg:#071c26; --card-text:#f0fdfa;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🐬 الأزرق البترولي والتيل</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#071c26; border:1px solid #134658;"></span>
                        <span class="desktop-swatch" style="background:#2dd4bf;"></span>
                        <span class="desktop-swatch" style="background:#38bdf8;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">مزيج من الأزرق البترولي والتيل البحري، يمنح هدوءاً بصرياً فائقاً.</p>
                <div class="desktop-theme-card-foot">Petrol Cyan Blue • بترولي مهدئ</div>
            </div>

            <!-- 11. TWILIGHT INDIGO -->
            <div class="desktop-theme-card" data-category="blue" data-theme-key="twilight-indigo" onclick="applyDesktopTheme('twilight-indigo')" style="--card-border:#2b356e; --card-bg:#0e122b; --card-text:#e0e7ff;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🔮 أزرق الشفق والإنديجو</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#0e122b; border:1px solid #2b356e;"></span>
                        <span class="desktop-swatch" style="background:#818cf8;"></span>
                        <span class="desktop-swatch" style="background:#f472b6;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">أزرق شفق نيلي مسائي يخفف التوتر العصبي ويمنع شرود الذهن.</p>
                <div class="desktop-theme-card-foot">Twilight Indigo • إنديجو مسائي</div>
            </div>

            <!-- 12. OLED MIDNIGHT -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="midnight" onclick="applyDesktopTheme('midnight')" style="--card-border:#334155; --card-bg:#020617; --card-text:#f8fafc;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌙 الليل الملكي الداكن (OLED)</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#020617; border:1px solid #334155;"></span>
                        <span class="desktop-swatch" style="background:#22d3ee;"></span>
                        <span class="desktop-swatch" style="background:#34d399;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">سواد عميق OLED موفر للطاقة، أقصى تباين في الإضاءة الليلية الضعيفة.</p>
                <div class="desktop-theme-card-foot">OLED Midnight • افتراضي</div>
            </div>

            <!-- 13. SEPIA BROWN -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="sepia-brown" onclick="applyDesktopTheme('sepia-brown')" style="--card-border:#78350f; --card-bg:#1c1917; --card-text:#fef3c7;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">📜 البني الإسبريسو والورق القديم</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#1c1917; border:1px solid #78350f;"></span>
                        <span class="desktop-swatch" style="background:#d97706;"></span>
                        <span class="desktop-swatch" style="background:#fef3c7;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">حجب كامل للضوء الأزرق، يريح العصب البصري ويمنع الصداع في العمل الطويل.</p>
                <div class="desktop-theme-card-foot">Warm Sepia • مهدئ للأعصاب</div>
            </div>

            <!-- 14. PALE GOLD -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="pale-gold" onclick="applyDesktopTheme('pale-gold')" style="--card-border:#854d0e; --card-bg:#181614; --card-text:#fef08a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">👑 الذهبي الباهت والفخامة</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#181614; border:1px solid #854d0e;"></span>
                        <span class="desktop-swatch" style="background:#eab308;"></span>
                        <span class="desktop-swatch" style="background:#fef08a;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">تناسق ذهبي كلاسيكي باهت وغير مزعج، يضفي هيبة الأرقام المحاسبية.</p>
                <div class="desktop-theme-card-foot">Pale Gold Luxury • فخامة مالية</div>
            </div>

            <!-- 15. EMERALD FOREST -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="emerald-forest" onclick="applyDesktopTheme('emerald-forest')" style="--card-border:#065f46; --card-bg:#032e24; --card-text:#a7f3d0;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🌲 زمرد الغابات لصفاء الذهن</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#032e24; border:1px solid #065f46;"></span>
                        <span class="desktop-swatch" style="background:#10b981;"></span>
                        <span class="desktop-swatch" style="background:#a7f3d0;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">اللون الأخضر يقع في منتصف شبكية العين، يخفض النبض ويمنع الخلط بالأرقام.</p>
                <div class="desktop-theme-card-foot">Cognitive Forest • تركيز ذهني فائق</div>
            </div>

            <!-- 16. DESERT MOCHA -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="desert-mocha" onclick="applyDesktopTheme('desert-mocha')" style="--card-border:#78350f; --card-bg:#26211d; --card-text:#fde68a;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">🏜️ الرمال الصحراوية وموكا</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#26211d; border:1px solid #78350f;"></span>
                        <span class="desktop-swatch" style="background:#f59e0b;"></span>
                        <span class="desktop-swatch" style="background:#fde68a;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">تدرج دافئ وسط بين الفاتح والداكن، مريح جداً للإضاءة المكتبية.</p>
                <div class="desktop-theme-card-foot">Desert Mocha • راحة يومية متوازنة</div>
            </div>

            <!-- 17. SUNLIGHT TITANIUM -->
            <div class="desktop-theme-card" data-category="dark" data-theme-key="sunlight-titanium" onclick="applyDesktopTheme('sunlight-titanium')" style="--card-border:#ffffff; --card-bg:#000000; --card-text:#ffffff;">
                <div class="desktop-theme-card-top">
                    <span class="desktop-theme-card-title">☀️ التيتانيوم الشمسي فائق التباين</span>
                    <span class="desktop-active-indicator" style="display:none;">✓ مفعلة</span>
                    <div class="desktop-swatch-group">
                        <span class="desktop-swatch" style="background:#000000; border:1px solid #ffffff;"></span>
                        <span class="desktop-swatch" style="background:#ffffff;"></span>
                        <span class="desktop-swatch" style="background:#facc15;"></span>
                    </div>
                </div>
                <p class="desktop-theme-card-desc">معيار WCAG AAA (21:1)، وضوح تام تحت شمس الظهيرة ولضعاف النظر.</p>
                <div class="desktop-theme-card-foot">Titanium High-Contrast • شمس وميدان</div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="desktop-theme-modal-footer">
            <div class="desktop-theme-footer-status">
                <span>السمة النشطة حالياً:</span>
                <strong id="desktopCurrentThemeLabel" style="color:var(--primary-accent, #38bdf8);">العاجي الدافئ</strong>
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
