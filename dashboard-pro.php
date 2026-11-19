<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }

// =============================================================
// نظام MAGAIEES ERP 2026 - شاشة الدخول السينمائية العالمية (ASUS Lumina OLED)
// جودة فائقة النقاء | خلفية حية مستمرة | انبثاق دائري عضوي مدمج للشعار
// =============================================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - MAGAIEES ERP</title>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <link rel="stylesheet" href="css/all.min.css?v=2027">
    
    <style>
        /* تأسيس بيئة العرض الشاملة ومنع التشتت البصري */
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background-color: #020305; font-family: 'Cairo', sans-serif; }
        
        /* 🎥 محرك تشغيل فيديو شاشة توقف أسوس الفعلي كخلفية حية ممتدة */
        .bg-asus-stream { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none; filter: brightness(1.05) contrast(1.15) saturate(1.3); z-index: 1; }
        
        /* طبقة الحماية الضوئية والدمج المحيطي لراحة العين وتوضيح النصوص */
        .ambient-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(6,10,18,0.1) 0%, rgba(2,3,5,0.7) 100%); z-index: 2; pointer-events: none; }
        
        /* 🔒 نموذج واجهة المستخدم (اليوزر نيم والباسورد) - تصميم زجاجي كريستالي فخم */
        .auth-container { position: relative; z-index: 10; display: flex; justify-content: center; align-items: center; height: 100vh; transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.8s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.8s cubic-bezier(0.4, 0, 0.2, 1), color 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
        
        .glass-login-card { background: rgba(8, 12, 21, 0.5); /* gpu-opt */ /* gpu-opt */ border: 1px solid rgba(255, 255, 255, 0.08); padding: 45px; border-radius: 28px; width: 360px; text-align: center; box-shadow: 0 40px 80px rgba(0,0,0,0.7); transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.6s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.6s cubic-bezier(0.4, 0, 0.2, 1), color 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        
        .glass-login-card h2 { margin: 0 0 8px 0; font-size: 24px; color: #fff; font-weight: 900; }
        .glass-login-card p { margin: 0 0 30px 0; font-size: 11px; color: #38bdf8; letter-spacing: 1px; font-weight: bold; }
        
        /* الحقول المدعومة بالأيقونات والوضوح العالي */
        .input-group { position: relative; margin-bottom: 20px; text-align: right; }
        .input-group i { position: absolute; right: 16px; top: 16px; color: #38bdf8; font-size: 14px; }
        
        .input-control { width: 100%; padding: 14px 45px 14px 20px; background: rgba(0, 0, 0, 0.5); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 14px; color: #fff; font-size: 15px; box-sizing: border-box; outline: none; transition: transform 0.3s, opacity 0.3s, background-color 0.3s, border-color 0.3s, color 0.3s; }
        .input-control:focus { border-color: #00ffcc; box-shadow: 0 0 20px rgba(0,255,204,0.25); }
        .num-field { font-family: 'JetBrains Mono', monospace; font-size: 18px; text-align: center; letter-spacing: 2px; }
        
        .btn-prime { width: 100%; padding: 15px; background: linear-gradient(90deg, #00ffcc, #ff00ff); color: #000; border: none; border-radius: 14px; font-weight: 900; font-size: 15px; cursor: pointer; letter-spacing: 1px; transition: transform 0.3s, opacity 0.3s, background-color 0.3s, border-color 0.3s, color 0.3s; box-shadow: 0 4px 15px rgba(0,255,204,0.2); }
        .btn-prime:hover { filter: brightness(1.2); transform: translateY(-1px); }

        /* =============================================================
           👑 الانبثاق الدائري العضوي (المرحلة الثانية: شعار السيطرة المتداخل ذرياً)
           ============================================================= */
        .organic-circle-viewport { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; justify-content: center; align-items: center; z-index: 9999; pointer-events: none; }
        
        /* الدائرة المحاكية تماماً لحركة اللوجو المضيء في الفيديو الأصلي لأسوس */
        .luminous-neon-ring { 
            position: absolute; 
            width: 420px; 
            height: 420px; 
            border-radius: 50%; 
            border: 5px solid transparent; 
            border-top: 5px solid #00ffcc; 
            border-bottom: 5px solid #ff00ff; 
            border-left: 5px solid #fbbf24; 
            animation: rotateRing 2s linear infinite; 
            filter: drop-shadow(0 0 35px rgba(0,255,204,0.8)); 
        }

        /* كتلة النص: منبثقة ومدمجة بداخل الألوان الخلفية وليست مركبة فوقها */
        .organic-brand-block { text-align: center; animation: burstFromDepth 4s forwards cubic-bezier(0.1, 1, 0.1, 1); }
        
        .brand-text-epic { 
            font-size: 85px; 
            font-weight: 900; 
            font-family: 'Inter', 'Cairo', sans-serif; 
            margin: 0; 
            color: #ffffff; 
            /* اندماج ذري: حروف الاسم تمتص وتفرز ألوان الفيديو المتحرك تحتها فوراً */
            mix-blend-mode: overlay; 
            filter: drop-shadow(0 0 40px rgba(0,255,204,0.6)); 
            letter-spacing: 5px; 
        }
        
        .brand-sub-epic { font-size: 15px; font-weight: bold; color: #ffffff; mix-blend-mode: color-dodge; letter-spacing: 10px; margin-top: 15px; text-transform: uppercase; opacity: 0.8; }

        /* حركات الهندسة البصرية المتقدمة */
        @keyframes rotateRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes burstFromDepth {
            0% { transform: scale(0.2); filter: blur(25px) contrast(2); opacity: 0; }
            35% { transform: scale(1.08); filter: blur(0px) contrast(1.1); opacity: 1; }
            50% { transform: scale(1); }
            100% { transform: scale(1.04); opacity: 1; }
        }
        
        /* تدمير وتلاشي اللوحة الأصلية والتحويل الموحد للسيستم */
        .collapse-card { opacity: 0; transform: scale(0.85) rotate(-1deg); pointer-events: none; }
        .global-fade-out { opacity: 0; transition: opacity 1.2s ease; }
    </style>
    <script>
        // Check if already authenticated on load
        if (localStorage.getItem('authToken')) {
            window.location.replace('magaiees_unified_dashboard.php');
        }
    </script>
    <style id="global-zero-lag-css">.ambient-bg,.ambient-bg .orb,.preset-ribbon-btn::after,.preset-btn::after,.btn-action-hero::after,.btn-run::after,.action-tab.active::before,.sticky-footer::before{display:none!important;content:none!important}*,*::before,*::after{backdrop-filter:none!important;-webkit-backdrop-filter:none!important;animation:none!important}button,a,input,select,[role="button"]{touch-action:manipulation;-webkit-tap-highlight-color:transparent}button,.btn,.modern-btn,.preset-ribbon-btn,.preset-btn,.btn-action-hero,.btn-run,.action-tab,.fin-card,.pos-grid tbody tr{transition:background-color .08s ease,border-color .08s ease,opacity .08s ease!important}button:hover,.btn:hover,.modern-btn:hover,.preset-ribbon-btn:hover,.preset-btn:hover,.btn-action-hero:hover,.del-btn:hover,.modal-close:hover{transform:none!important}</style>
    <link id="magaies-ophthalmic-ergo-css" rel="stylesheet" href="css/ophthalmic-ergonomics-2027.css?v=2027_gold_v3">
</head>
<body>

    <video autoplay loop muted playsinline class="bg-asus-stream">
        <source src="271262.mp4" type="video/mp4">
    </video>
    <div class="ambient-overlay"></div>

    <div class="auth-container" id="authWrapper">
        <div class="glass-login-card" id="loginCard">
            <h2>لوحة تمكين السيطرة</h2>
            <p>MAGAIEES ERP — SECURE GATEWAY</p>
            
            <form onsubmit="initiateProfessionalAccess(event)">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" class="input-control" placeholder="اسم المستخدم أو المعرّف" required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <i class="fa-solid fa-shield-halved"></i>
                    <input type="password" id="password" class="input-control num-field" placeholder="••••••••" required autocomplete="off">
                </div>
                
                <button type="submit" class="btn-prime">موافق وتأثير الدخول</button>
            </form>
        </div>
    </div>

    <div class="organic-circle-viewport" id="organicSplash">
        <div class="luminous-neon-ring"></div>
        <div class="organic-brand-block">
            <h1 class="brand-text-epic">السيطرة ERP</h1>
            <div class="brand-sub-epic">ENTERPRISE SYSTEM</div>
        </div>
    </div>

    <script>
        function initiateProfessionalAccess(event) {
            event.preventDefault();
            const user = (document.getElementById('username').value || '').trim();
            const pass = (document.getElementById('password').value || '').trim();
            
            // قبول كلمة المرور وتأسيس جلسة إدارية كاملة الصلاحيات
            if (pass === "123" || pass === "123456" || pass === "admin" || pass.length >= 1) {
                const token = 'token_' + Date.now();
                // Save complete authentication data in localStorage & sessionStorage
                localStorage.setItem('authToken', token);
                localStorage.setItem('user', JSON.stringify({ 
                    id: 1,
                    username: user || 'admin', 
                    fullName: (user && user.toLowerCase() !== 'admin') ? user : 'مدير النظام (الباشمهندس مهدي)',
                    role: 'admin',
                    roleId: 'admin_fallback',
                    securityLevel: 10,
                    subdomain: ''
                }));
                document.cookie = "session_active=true; path=/";
                document.cookie = "saas_token=" + encodeURIComponent(token) + "; path=/; max-age=2592000; SameSite=Lax";
                sessionStorage.setItem('session_active', 'true');
                sessionStorage.setItem('dashboard_unlocked', '1');

                // 1. إخفاء صندوق الدخول (اليوزر والباسورد) بحركة تراجع انسيابية
                document.getElementById('loginCard').classList.add('collapse-card');
                
                // 2. سحب ستارة الانبثاق الدائري العضوي للشعار من عمق نفس الفيديو بعد 500 مللي ثانية
                setTimeout(() => {
                    document.getElementById('authWrapper').style.display = 'none';
                    const splashViewport = document.getElementById('organicSplash');
                    splashViewport.style.display = 'flex';
                    
                    // 3. يظل شعار السيطرة يلمع ويمتص حركة ألوان السوائل في دائرة نيونية، ثم يفتح السيستم
                    setTimeout(() => {
                        splashViewport.classList.add('global-fade-out');
                        setTimeout(() => {
                            // التوجيه التلقائي لغرفة القيادة المركزية الموحدة
                            window.location.replace('magaiees_unified_dashboard.php');
                        }, 800);
                    }, 2500);
                }, 400);
            } else {
                alert('عذراً يا باشمهندس مهدي، يرجى إدخال كلمة المرور للمتابعة!');
            }
        }
    </script>


<script>(function(){var l=0,q=[];window.ensureGlobalBgMedia=function(c){if(l&&typeof window.quickHeaderPlay==='function'){if(c)c();return;}if(c)q.push(c);if(document.querySelector('script[data-lazy-bgm]'))return;var s=document.createElement('script');s.src='js/global_background_media.js?v=<?= @filemtime(__DIR__."/js/global_background_media.js")?:2027 ?>';s.dataset.lazyBgm='1';s.onload=function(){l=1;q.splice(0).forEach(function(fn){try{fn();}catch(_){}});};document.body.appendChild(s);};['quickHeaderPlay','setHeaderMediaVolume','quickHeaderStop','openBackgroundPopout','toggleHeaderMediaDropdown','playPersistentMedia'].forEach(function(fn){if(typeof window[fn]!=='function')window[fn]=function(){var a=arguments;window.ensureGlobalBgMedia(function(){if(typeof window[fn]==='function')window[fn].apply(window,a);});};});try{if(localStorage.getItem('erp_bg_media_playing')==='1'||localStorage.getItem('magaies_live_tv_active')==='1')window.addEventListener('DOMContentLoaded',function(){setTimeout(function(){window.ensureGlobalBgMedia();},300);},{once:true});}catch(_){}})();</script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
<script>(function(){var l=0;window.loadMagaiesSmartGuide=function(c){if(l){if(c)c();return;}l=1;var s=document.createElement('script');s.src='js/magaies-smart-guide.js?v=<?= @filemtime(__DIR__."/js/magaies-smart-guide.js")?:2027 ?>';s.onload=function(){if(c)c();};document.body.appendChild(s);};window.addEventListener('keydown',function(e){if(e.key==='F1'){e.preventDefault();window.loadMagaiesSmartGuide();}});window.addEventListener('contextmenu',function(){window.loadMagaiesSmartGuide();},{once:true,passive:true});})();</script>
</body>
</html>
