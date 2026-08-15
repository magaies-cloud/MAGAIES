<script>
    // قراءة الثيم الذي اختاره الباشمهندس مهدي من الشاشة الموحدة وتطبيقه طالما الصفحة تفتح
    const currentTheme = localStorage.getItem('magaiees_global_theme') || 'theme-imperial';
    document.documentElement.className = currentTheme;
    
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
            document.body.classList.add(currentTheme);
            if (document.documentElement.classList.contains('has-sidebar')) {
                document.body.classList.add('has-sidebar');
            }
        }
    });
</script>
<style>
    #nav-transition-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(3,4,9,0.92); z-index: 999999;
        opacity: 0; pointer-events: none;
        transition: opacity 0.12s ease;
    }
    #nav-transition-overlay.navigating {
        opacity: 1; pointer-events: all;
    }
</style>
<div id="nav-transition-overlay"></div>
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
        ['dashboard-pro.php','لوحة التحكم','#22d3ee','dash'],
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
    overflow: hidden;
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
    border-radius: 14px !important;
    transition: all 0.3s ease;
}
.magical-glass-header .clock-container:hover, 
.magical-glass-header .user-info:hover, 
.magical-glass-header #online-users-badge:hover,
.magical-glass-header #global-exchange-rate-box:hover {
    border-color: rgba(192, 132, 252, 0.8) !important;
    box-shadow: 0 0 20px rgba(192, 132, 252, 0.4), inset 0 0 10px rgba(192, 132, 252, 0.2) !important;
    transform: translateY(-2px);
    background: rgba(30, 15, 60, 0.7) !important;
}
.magical-glass-header .header-page-title {
    text-shadow: 0 0 15px rgba(192, 132, 252, 0.8), 0 0 30px rgba(192, 132, 252, 0.4);
    color: #fff !important;
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
</style>
<header class="premium-header magical-glass-header <?php echo isset($header_theme_class) ? $header_theme_class : ''; ?>" style="display:flex; justify-content:space-between; align-items:center; gap:15px;">
    <!-- Left: Logo / Brand -->
    <div style="display:flex; align-items:center; gap:12px; flex:1;">
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
    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;">
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
    <div style="display:flex; align-items:center; gap:12px; flex:1; justify-content:flex-end;">
        <!-- User Info & Online Users -->
        <div style="display:flex; flex-direction:column; align-items:flex-end; justify-content:center; gap:6px; margin:6px 0;">
            <div class="user-info" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); padding: 5px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); cursor: pointer; min-width: 90px;" onclick="document.getElementById('profile-modal').style.display='flex'">
                <div id="header-user-name" style="font-size: 0.95rem; font-weight: 800; color: #fff; text-align: center;">جاري التحميل...</div>
                <div id="header-user-role" style="font-size: 0.75rem; color: #38bdf8; text-align: center;" data-i18n="header.sysAdmin">مدير النظام</div>
            </div>
            <div id="online-users-badge" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:3px 12px; min-width:90px; text-align:center; box-shadow:inset 0 0 10px rgba(16,185,129,0.1); cursor:pointer;" onclick="alert('المستخدمين المتصلين حالياً: \n' + (window.onlineUsersList || []).join('\n'))">
                <span style="color:#6ee7b7; font-size:0.7rem; font-weight:800;"><span data-i18n="header.onlineNow">متصل الآن:</span> <span id="online-users-count" style="color:#fff; font-size:0.8rem;">1</span> <span style="font-size:0.6rem;">🟢</span></span>
            </div>
        </div>

        <!-- Clock & Exchange Rate -->
        <div class="header-time-stack" style="display:flex; flex-direction:column; gap:6px; align-items:center; margin:6px 0;">
            <div class="clock-container" style="display:flex; flex-direction:column; align-items:center; background:rgba(15,23,42,0.6); padding:4px 14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); min-width:110px; width:100%; box-sizing:border-box;">
                <span id="header-clock" style="color:#34d399; font-size:1.1rem; font-weight:800; font-variant-numeric:tabular-nums; letter-spacing:1px; text-shadow: 0 0 10px rgba(16,185,129,0.5);">00:00:00</span>
                <span id="header-date-mini" style="color:var(--text-secondary); font-size:0.65rem; margin-top:1px;">جاري التحميل...</span>
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

            <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                <button class="fullscreen-toggle-btn" onclick="toggleFullScreen()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.15rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>
                <button class="theme-toggle-btn" onclick="toggleTheme()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.toggleTheme" title="تبديل المظهر">🌓</button>
            </div>
        </div>
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
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
            window.dispatchEvent(new Event('themeChanged'));
        }

        // Global Backdrop Click to Go Back Interaction (Immediate, bypasses JS cache)
        document.addEventListener('click', function(e) {
            try {
                if (!e || !e.target) return;
                
                // Exclude home/dashboard and login screens
                const path = window.location.pathname.toLowerCase();
                if (path.includes('dashboard-pro') || path.includes('login') || path.includes('index') || path === '/' || path.endsWith('/magaees') || path.endsWith('/magaees/')) {
                    return;
                }

                const target = e.target;
                if (typeof target.closest !== 'function') return;

                // التقييد: تفعيل زر الرجوع للخلف عند الضغط في المساحات الفارغة داخل الترويسة والشريط الجانبي فقط
                if (!target.closest('.global-sidebar') && !target.closest('header.premium-header')) {
                    return; // تجاهل النقرات في أي مكان آخر (مثل شاشة التقارير أو الحاويات الرئيسية)
                }

                // Protect ONLY interactive elements. We want background/empty areas to trigger 'back'
                const interactiveSelectors = [
                    'button', 'input', 'select', 'textarea', 'a', 'label', 'table', 'form', 'li', 'ul',
                    '.modal-overlay', '.modal', '.custom-dropdown-list', '.toast-container', '.choices',
                    '.user-info', '#online-users-badge', '.clock-container', '.crystal-pill', '.bal-card',
                    '.autocomplete-list', '[onclick]' // Any element with an onclick attribute
                ];

                for (let i = 0; i < interactiveSelectors.length; i++) {
                    if (target.closest(interactiveSelectors[i])) {
                        return; // Ignored as click is in or on an interactive component
                    }
                }

                // If we get here, it's an empty space, margin, or layout container
                // Navigate back immediately
                e.preventDefault();
                e.stopPropagation();
                if (document.referrer) {
                    window.history.back();
                } else {
                    window.location.href = 'dashboard-pro.php';
                }
            } catch (err) {
                console.error('Error in backdrop click:', err);
            }
        }, true); // Use CAPTURE phase to intercept before any other script calls stopPropagation
        </script>
    </div>
</header>
<script src="js/auto-charts.js?v=<?php echo time(); ?>"></script>
