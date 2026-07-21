<?php $page_title = 'مقاييس 2026 - تسجيل الدخول'; ?>
<!-- c:\mynewapp\frontend-pwa\login.html -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
})();
</script>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="preload" as="image" href="assets/user_bg_fast.jpg">
    <title>مقاييس 2026 - تسجيل الدخول</title>
    <link rel="stylesheet" href="css/design-system.css?v=20260709_5"><link rel="stylesheet" href="css/theme.css?v=20260709_5">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">
        <style>
        body, body.has-sidebar, body.has-sidebar.sidebar-open {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #0c101b !important;
            background: #0c101b url('assets/user_bg_fast.jpg') no-repeat center center fixed !important;
            background-size: cover !important;
            background-image: url('assets/user_bg_fast.jpg') !important;
            overflow: hidden;
            position: relative;
            animation: none !important;
            transition: none !important;
            transform: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }



        .login-card {
            width: 90%;
            max-width: 420px;
            padding: 45px 35px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-align: center;
            /* Glassmorphism */
            backdrop-filter: blur(25px) saturate(190%);
            -webkit-backdrop-filter: blur(25px) saturate(190%);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                        inset 0 1px 1px rgba(255, 255, 255, 0.2) !important;
            background: rgba(15, 22, 38, 0.55) !important;
            z-index: 10;
            
            /* Guaranteed Static & Centered */
            position: relative;
            margin: auto !important; /* ensures equal margins left/right/top/bottom in flex container */
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        .logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .title {
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--primary-accent);
        }
        .input-group {
            text-align: right;
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: 0.95rem;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.4);
        }
        .input-field {
            width: 100%;
            padding: 15px 18px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            font-size: 1.05rem;
            font-weight: 500;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 
                        0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-weight: 400;
        }
        .input-field:focus {
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.25), 
                        inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            border-color: rgba(56, 189, 248, 0.65) !important;
            background: rgba(255, 255, 255, 0.13) !important;
        }
        .toggle-pwd-btn {
            position: absolute;
            left: 15px;
            top: 38px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6) !important;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.2s;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-pwd-btn:hover {
            color: #38bdf8 !important;
        }
        .btn-login {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            background: linear-gradient(135deg, #38bdf8, #818cf8) !important;
            box-shadow: 0 8px 25px rgba(56, 189, 248, 0.4) !important;
            color: white !important;
            font-size: 1.1rem;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-login:hover {
            box-shadow: 0 12px 30px rgba(56, 189, 248, 0.6) !important;
            transform: translateY(-2px);
        }
        .btn-login:active {
            transform: translateY(1px) scale(0.98);
        }
        .device-info {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.75) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            padding: 8px 16px;
            border-radius: 20px;
            margin-top: 15px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body style="display:flex !important; align-items:center !important; justify-content:center !important; height:100vh !important; padding:0 !important; margin:0 !important; animation:none !important; transition:none !important; transform:none !important; background-color:#0c101b !important; background-image:url('assets/user_bg_fast.jpg') !important; background-size:cover !important; background-position:center center !important; background-repeat:no-repeat !important; background-attachment:fixed !important; overflow:hidden !important;">
    <div class="login-card">
        <div class="logo-container" style="margin-bottom: 5px;">
            <img src="assets/logo.png" alt="Logo" loading="lazy" style="width: 130px; height: auto; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
        </div>
        <div class="title" style="background: linear-gradient(to right, #ffffff, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 5px; font-weight: 900; letter-spacing: 1px;">مقاييس MAGAIEES ERP</div>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: -15px; font-weight: 700;">نظام إدارة موارد المؤسسات المتكامل الذكي</p>
        
        <div class="input-group">
            <label>اسم المستخدم</label>
            <input type="text" class="input-field" id="username" placeholder="أدخل اسم المستخدم" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
        </div>
        
        <div class="input-group" style="position: relative;">
            <label>كلمة المرور</label>
            <input type="password" class="input-field" id="password" placeholder="أدخل كلمة المرور" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')" style="padding-left: 50px;">
            <button type="button" class="toggle-pwd-btn" onclick="togglePassword()" title="إظهار/إخفاء كلمة المرور" id="toggle-pwd-icon">👁️</button>
        </div>
        
        <script>
            function togglePassword() {
                const pwdInput = document.getElementById('password');
                const toggleIcon = document.getElementById('toggle-pwd-icon');
                if (pwdInput.type === 'password') {
                    pwdInput.type = 'text';
                    toggleIcon.innerHTML = '🙈';
                } else {
                    pwdInput.type = 'password';
                    toggleIcon.innerHTML = '👁️';
                }
            }
        </script>
        
        <button class="btn-login" onclick="handleLogin()">دخول آمن</button>
        
        <div class="device-info" id="device-id-display">جاري التحقق من الجهاز...</div>
    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260719_update"></script>
    
    <script>
        // --- SSO Interceptor ---
        (function() {
            const ssoParams = new URLSearchParams(window.location.search);
            if (ssoParams.has('sso_token')) {
                localStorage.setItem('authToken', ssoParams.get('sso_token'));
                if (ssoParams.has('sso_user')) {
                    localStorage.setItem('user', ssoParams.get('sso_user'));
                }
                document.cookie = "session_active=true; path=/";
                sessionStorage.setItem('session_active', 'true');
                window.location.replace(ssoParams.get('redirect') || 'dashboard-pro.php');
            }
        })();

        // Generate or get Device UUID
        let deviceUuid = localStorage.getItem('deviceUuid');
        if (!deviceUuid) {
            deviceUuid = 'DEV-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
            localStorage.setItem('deviceUuid', deviceUuid);
        }
        
        function getSubdomain() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('tenant')) return params.get('tenant');
            const host = window.location.hostname;
            if (/^[0-9.]+$/.test(host)) return null;
            const parts = host.split('.');
            if (host.endsWith('.localhost') && parts.length >= 2) return parts[0];
            if (parts.length > 2) return parts[0];
            return null;
        }
        
        document.getElementById('device-id-display').innerText = `معرف الجهاز: ${deviceUuid}`;

        async function handleLogin() {
            const username = document.getElementById('username').value.trim().toLowerCase();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                alert('يرجى إدخال اسم المستخدم وكلمة المرور');
                return;
            }

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 15000); // 15-second timeout

                const tenantSubdomain = getSubdomain();
                const headers = { 'Content-Type': 'application/json' };
                if (tenantSubdomain) headers['X-Tenant-Subdomain'] = tenantSubdomain;

                const getBaseUrl = () => {
                    const origin = window.location.origin;
                    const path = window.location.pathname;
                    const dir = path.substring(0, path.lastIndexOf('/'));
                    return origin + dir + "/";
                };

                const res = await fetch(getBaseUrl() + 'api_index.php/auth/login', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ username, password }),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                const data = await res.json().catch(() => ({}));

                if (res.ok && data && data.success !== false && data.token) {
                    document.cookie = "session_active=true; path=/";
                    sessionStorage.setItem('session_active', 'true');
                    sessionStorage.setItem('cloud_synced', 'true');
                    localStorage.setItem('authToken', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    const userNameToDisplay = data.user.fullName || data.user.name || data.user.username || 'المستخدم';
                    showLoginSuccessAnimation(userNameToDisplay, data.user.subdomain, data.token, JSON.stringify(data.user));
                    return;
                } else {
                    alert(data.error || data.message || 'بيانات الدخول غير صحيحة');
                    return;
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('فشل الاتصال بالسيرفر، يرجى التأكد من تشغيل السيرفر والاتصال بالشبكة');
            }
        }

        function showLoginSuccessAnimation(name, userSubdomain, token = null, userStr = null) {
            const btn = document.querySelector('.btn-login');
            btn.innerHTML = '<div class="loader"></div>';
            btn.style.pointerEvents = 'none';

            // --- START SSO REDIRECT ---
            let targetUrl = 'dashboard-pro.php';

            if (userSubdomain && window.location.hostname !== userSubdomain + '.elsajana.com' && !window.location.hostname.includes('localhost') && !window.location.hostname.includes('127.0.0.1')) {
                const subdomainUrl = `https://${userSubdomain}.elsajana.com`;
                if (token) {
                    targetUrl = `${subdomainUrl}/login.php?sso_token=${encodeURIComponent(token)}&sso_user=${encodeURIComponent(userStr || '')}&redirect=dashboard-pro.php`;
                } else {
                    targetUrl = `${subdomainUrl}/dashboard-pro.php`;
                }
            }
            // --- END SSO REDIRECT ---

            setTimeout(() => {
                const loginCard = document.querySelector('.login-card');
                if (loginCard) {
                    loginCard.style.transform = 'scale(0.9)';
                    loginCard.style.opacity = '0';
                }
                
                setTimeout(() => {
                    const displayName = name || 'مدير النظام';
                    const hour = new Date().getHours();
                    const greeting = (hour >= 12 && hour < 24) ? 'مساء الخير يا ' : 'صباح الخير يا ';
                    
                    document.body.innerHTML = `
                    <style>
                    @keyframes introFadeIn { from { opacity: 0; transform: scale(1.05); } to { opacity: 1; transform: scale(1); } }
                    @keyframes floatIntro { 0% { transform: translateY(0px); } 50% { transform: translateY(-12px); } 100% { transform: translateY(0px); } }
                    @keyframes pulseGlowIntro { 0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.5); } 70% { box-shadow: 0 0 0 35px rgba(56, 189, 248, 0); } 100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); } }
                    @keyframes slideUpFadeIntro { from { opacity: 0; transform: translateY(40px); filter: blur(5px); } to { opacity: 1; transform: translateY(0); filter: blur(0); } }
                    @keyframes progressBarLoad { 0% { width: 0%; } 100% { width: 100%; } }
                    @keyframes rotateDashIntro { 100% { transform: rotate(360deg); } }
                    @keyframes bgGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
                    @keyframes slideDownFadePrayer { from { opacity: 0; transform: translateY(-40px); filter: blur(5px); } to { opacity: 1; transform: translateY(0); filter: blur(0); } }

                    .market-prayer {
                        position: absolute; top: 8vh; width: 90%; max-width: 800px; text-align: center;
                        font-family: 'Tajawal', sans-serif; font-size: 1.4rem; font-weight: 800;
                        color: #fef08a;
                        text-shadow: 1px 1px 0px rgba(255,255,255,0.3), -1px -2px 3px rgba(0,0,0,0.9), 0 5px 20px rgba(217, 119, 6, 0.6);
                        opacity: 0;
                        animation: slideDownFadePrayer 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards;
                        z-index: 10; line-height: 1.8;
                    }

                    .premium-intro-bg {
                        position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
                        background: radial-gradient(circle at center, #1e1b4b 0%, #050308 100%);
                        display: flex; flex-direction: column; align-items: center; justify-content: center;
                        z-index: 99999; animation: introFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    }
                    
                    .intro-particles {
                        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
                        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
                        background-size: 40px 40px; opacity: 0.3; animation: introFadeIn 2s ease-in-out forwards;
                    }

                    .ai-brain-container {
                        position: relative; width: 140px; height: 140px;
                        margin-bottom: 30px; display: flex; align-items: center; justify-content: center;
                        z-index: 2;
                        animation: slideUpFadeIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                    }

                    .ai-ring {
                        position: absolute; border-radius: 50%; border: 2px solid transparent;
                        animation: rotateRing linear infinite;
                    }
                    .ai-ring.r1 { width: 140px; height: 140px; border-top-color: #38bdf8; border-bottom-color: #c084fc; animation-duration: 4s; }
                    .ai-ring.r2 { width: 110px; height: 110px; border-left-color: #0ea5e9; border-right-color: #f472b6; animation-duration: 3s; animation-direction: reverse; }
                    .ai-ring.r3 { width: 85px; height: 85px; border-top-color: rgba(255,255,255,0.8); border-bottom-color: rgba(56, 189, 248, 0.5); border-style: dashed; animation-duration: 8s; }

                    @keyframes rotateRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

                    .ai-brain-svg {
                        width: 60px; height: 60px; position: absolute; z-index: 3;
                        animation: pulseBrain 2s infinite ease-in-out;
                        filter: drop-shadow(0 0 10px rgba(192, 132, 252, 0.8));
                    }

                    @keyframes pulseBrain { 0% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(192,132,252,0.8)); } 50% { transform: scale(1.1); filter: drop-shadow(0 0 20px rgba(56,189,248,1)); } 100% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(192,132,252,0.8)); } }

                    .ai-number {
                        position: absolute; font-family: monospace; font-size: 16px; font-weight: bold;
                        color: #38bdf8; text-shadow: 0 0 8px #38bdf8; opacity: 0;
                        animation: floatNum 2.5s linear infinite; z-index: 4;
                    }
                    .ai-number:nth-child(5) { top: -10px; left: 30px; animation-delay: 0s; color: #c084fc; text-shadow: 0 0 8px #c084fc; }
                    .ai-number:nth-child(6) { top: 20px; right: -15px; animation-delay: 0.5s; }
                    .ai-number:nth-child(7) { bottom: -10px; left: 20px; animation-delay: 1s; color: #f472b6; text-shadow: 0 0 8px #f472b6; }
                    .ai-number:nth-child(8) { bottom: 20px; right: -5px; animation-delay: 1.5s; font-size: 18px; color: #fff; text-shadow: 0 0 10px #fff; }
                    .ai-number:nth-child(9) { top: 50px; left: -25px; animation-delay: 2s; color: #10b981; text-shadow: 0 0 8px #10b981; }
                    .ai-number:nth-child(10) { bottom: 50px; right: -25px; animation-delay: 2.2s; }

                    @keyframes floatNum {
                        0% { transform: translateY(15px) scale(0.5); opacity: 0; }
                        40% { opacity: 1; transform: translateY(-5px) scale(1.2); }
                        100% { transform: translateY(-35px) scale(0.5); opacity: 0; }
                    }

                    .intro-title {
                        font-family: 'Tajawal', sans-serif; font-weight: 900; font-size: 2.5rem;
                        background: linear-gradient(to right, #fff, #38bdf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                        background-size: 200% auto; animation: slideUpFadeIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards, bgGradientMove 5s linear infinite;
                        margin: 0 0 15px 0; opacity: 0; text-shadow: 0 10px 20px rgba(0,0,0,0.5); z-index: 2; text-align: center;
                    }

                    .intro-subtitle {
                        font-family: 'Tajawal', sans-serif; font-weight: 600; font-size: 1.15rem; color: #94a3b8;
                        margin: 0 0 40px 0; opacity: 0; animation: slideUpFadeIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
                        z-index: 2; text-align: center;
                    }

                    .intro-progress-container {
                        width: 280px; height: 6px; background: rgba(255,255,255,0.05);
                        border-radius: 10px; overflow: hidden; opacity: 0;
                        animation: slideUpFadeIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.7s forwards;
                        position: relative; z-index: 2; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
                    }

                    .intro-progress-bar {
                        height: 100%; width: 0%; border-radius: 10px;
                        background: linear-gradient(90deg, #38bdf8, #c084fc, #38bdf8);
                        background-size: 200% auto;
                        animation: progressBarLoad 2.5s cubic-bezier(0.7, 0, 0.3, 1) forwards, bgGradientMove 2s linear infinite;
                        box-shadow: 0 0 15px rgba(56, 189, 248, 0.6);
                    }
                    </style>

                    <div class="premium-intro-bg">
                        <div class="intro-particles"></div>
                        
                        <div class="market-prayer">
                            "لا إلهَ إلاَّ اللَّهُ وحْدَهُ لا شَرِيكَ لهُ، لَهُ المُلْكُ ولَهُ الحَمْدُ، يُحْيِي ويُمِيتُ، وهُوَ حَيٌّ لا يَمُوتُ، بِيَدِهِ الخَيْرُ، وهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ"
                        </div>

                        <div class="ai-brain-container">
                            <div class="ai-ring r1"></div>
                            <div class="ai-ring r2"></div>
                            <div class="ai-ring r3"></div>
                            <svg class="ai-brain-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z" stroke="#c084fc"></path>
                                <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z" stroke="#38bdf8"></path>
                                <path d="M12 4.5v15" stroke="#fff"></path>
                            </svg>
                            <span class="ai-number">1</span>
                            <span class="ai-number">0</span>
                            <span class="ai-number">10</span>
                            <span class="ai-number">AI</span>
                            <span class="ai-number">42</span>
                            <span class="ai-number">01</span>
                        </div>
                        <h2 class="intro-title">${greeting}${displayName}</h2>
                        <p class="intro-subtitle">جاري تجهيز بيئة العمل بأحدث التقنيات...</p>
                        <div class="intro-progress-container">
                            <div class="intro-progress-bar"></div>
                        </div>
                    </div>
                    `;

                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 2800);
                }, 400);
            }, 800);
        }

        // Add Enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleLogin();
            }
        });

        // Aggressively clear fields on load to prevent auto-fill
        window.addEventListener('pageshow', () => {
            const u = document.getElementById('username');
            const p = document.getElementById('password');
            if(u) u.value = '';
            if(p) p.value = '';
            
            // Interval to catch delayed autofills by the browser
            let clearCount = 0;
            const clrInterval = setInterval(() => {
                if(u && u.hasAttribute('readonly')) u.value = '';
                if(p && p.hasAttribute('readonly')) p.value = '';
                clearCount++;
                if(clearCount > 10) clearInterval(clrInterval); // Stop after 1 second
            }, 100);
        });
    </script>
</body>
</html>

