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

        /* 🎥 Live Video Background & Overlays */
        .bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
            filter: brightness(0.55) contrast(1.1);
            z-index: 1;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(3,5,8,0.1) 0%, rgba(2,4,6,0.8) 100%);
            z-index: 2;
            pointer-events: none;
        }

        /* 👑 3D Logo Emerge Splash Overlay */
        #splash-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            background: transparent;
            opacity: 0;
            pointer-events: none;
            transition: opacity 1s ease, visibility 1s ease;
            visibility: hidden;
        }
        #splash-container.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        .neon-ring {
            position: absolute;
            width: 440px;
            height: 440px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top: 4px solid #00ffcc;
            border-bottom: 4px solid #ff00ff;
            border-left: 4px solid #fbbf24;
            animation: spin 2.5s linear infinite;
            filter: drop-shadow(0 0 25px rgba(0,255,204,0.75));
            pointer-events: none;
        }
        
        .logo-3d {
            text-align: center;
            transform-style: preserve-3d;
            perspective: 1000px;
            z-index: 100;
        }
        #splash-container.active .logo-3d {
            animation: emerge 3.8s forwards cubic-bezier(0.1, 1, 0.1, 1);
        }
        
        .logo-text {
            font-size: 78px;
            font-weight: 900;
            font-family: 'Cairo', 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #ffffff 0%, #00ffcc 50%, #ff00ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.85)) drop-shadow(0 0 50px rgba(0, 255, 204, 0.45));
            letter-spacing: 3px;
        }
        
        .logo-sub {
            font-size: 15px;
            font-weight: bold;
            color: #38bdf8;
            letter-spacing: 6px;
            margin-top: 15px;
            text-transform: uppercase;
            text-shadow: 0 2px 10px rgba(56, 189, 248, 0.5);
        }

        .splash-prayer {
            margin-bottom: 40px;
            width: 90%;
            max-width: 800px;
            text-align: center;
            font-family: 'Tajawal', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #fef08a;
            text-shadow: 0 2px 10px rgba(0,0,0,0.9), 0 0 25px rgba(217, 119, 6, 0.5);
            line-height: 1.8;
            z-index: 100;
        }

        .splash-progress-container {
            width: 320px;
            height: 6px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 50px;
            z-index: 100;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
        }
        .splash-progress-bar {
            height: 100%;
            width: 0%;
            border-radius: 10px;
            background: linear-gradient(90deg, #38bdf8, #c084fc, #38bdf8);
            background-size: 200% auto;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.6);
        }
        #splash-container.active .splash-progress-bar {
            animation: progressBarLoad 3.5s cubic-bezier(0.7, 0, 0.3, 1) forwards, bgGradientMove 2s linear infinite;
        }

        .hidden-box {
            opacity: 0 !important;
            transform: scale(0.85) !important;
            pointer-events: none !important;
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes emerge {
            0% { transform: scale(0.2) translateZ(-600px); filter: blur(12px) brightness(0); opacity: 0; }
            30% { transform: scale(1.1) translateZ(120px); filter: blur(0px) brightness(1.6); opacity: 1; }
            45% { transform: scale(1) translateZ(0); }
            100% { transform: scale(1.04) translateZ(30px); }
        }
        @keyframes progressBarLoad { 0% { width: 0%; } 100% { width: 100%; } }
        @keyframes bgGradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    </style>
</head>
<body style="display:flex !important; align-items:center !important; justify-content:center !important; height:100vh !important; padding:0 !important; margin:0 !important; animation:none !important; transition:none !important; transform:none !important; background-color:#0c101b !important; overflow:hidden !important;">

    <!-- 🎥 Live Video Background -->
    <video autoplay loop muted playsinline class="bg-video">
        <source src="271262.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

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

    <!-- 👑 3D Splash Screen Emerge (Phase 2) -->
    <div id="splash-container">
        <div class="splash-prayer">
            "لا إلهَ إلاَّ اللَّهُ وحْدَهُ لا شَرِيكَ لهُ، لَهُ المُلْكُ ولَهُ الحَمْدُ، يُحْيِي ويُمِيتُ، وهُوَ حَيٌّ لا يَمُوتُ، بِيَدِهِ الخَيْرُ، وهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ"
        </div>
        <div class="neon-ring"></div>
        <div class="logo-3d">
            <h1 class="logo-text">مقاييس ERP</h1>
            <div class="logo-sub">غرفة القيادة المركزية العالمية</div>
        </div>
        <div class="splash-progress-container">
            <div class="splash-progress-bar"></div>
        </div>
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

            // --- 1. Online Login Attempt ---
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

                if (res.ok) {
                    const data = await res.json();
                    if (data.error || data.success === false) {
                        alert(data.error || 'فشل تسجيل الدخول');
                        return;
                    }
                    document.cookie = "session_active=true; path=/";
                    sessionStorage.setItem('session_active', 'true');
                    localStorage.setItem('authToken', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    const userNameToDisplay = data.user.fullName || data.user.name || data.user.username || 'المستخدم';
                    showLoginSuccessAnimation(userNameToDisplay, data.user.subdomain, data.token, JSON.stringify(data.user));
                    return;
                } else {
                    const err = await res.json();
                    if (res.status === 403 || res.status === 400 || res.status === 401) {
                        alert(err.error || err.message);
                        return;
                    }
                    console.log("Online login failed, trying offline. Error:", err.error || err.message);
                    // Do not return here, let it fall through to offline check
                }
            } catch (error) {
                console.log('Online login failed or unreachable, checking local credentials...', error);
            }

            // --- 2. Offline Login Attempt (Scan all tenants) ---
            let foundOfflineUser = null;
            // First check DB_STORE if available
            if (window.DB_STORE) {
                for (let key in window.DB_STORE) {
                    if (key.includes('erp_users')) {
                        try {
                            const users = JSON.parse(window.DB_STORE[key]);
                            const u = users.find(x => btoa(x.username.toLowerCase()) === btoa(username) && btoa(x.password) === btoa(password));
                            if (u) {
                                foundOfflineUser = u;
                                break;
                            }
                        } catch(e) {}
                    }
                }
            }
            
            // Fallback to basic getItem if not found
            if (!foundOfflineUser) {
                const usersJson = localStorage.getItem('erp_users_v1') || localStorage.getItem('erp_users');
                if (usersJson) {
                    try {
                        const users = JSON.parse(usersJson);
                        foundOfflineUser = users.find(u => btoa(u.username.toLowerCase()) === btoa(username) && btoa(u.password) === btoa(password));
                    } catch(e) {}
                }
            }

            if (foundOfflineUser) {
                document.cookie = "session_active=true; path=/";
                sessionStorage.setItem('session_active', 'true');
                localStorage.setItem('authToken', 'offline_token_' + Date.now());
                localStorage.setItem('user', JSON.stringify(foundOfflineUser));
                
                // If it was found in a specific tenant prefix, we should set currentTenantId
                for (let key in window.DB_STORE) {
                    if (key.includes('erp_users') && window.DB_STORE[key].includes(username)) {
                        const match = key.match(/tenant_(.*?)_/);
                        if (match && match[1]) {
                            sessionStorage.setItem('currentTenantId', match[1]);
                        }
                    }
                }

                const offlineNameToDisplay = foundOfflineUser.fullName || foundOfflineUser.name || foundOfflineUser.username || 'المستخدم';
                showLoginSuccessAnimation(offlineNameToDisplay + ' (وضع الأوفلاين)');
                return;
            }


            alert('بيانات الدخول غير صحيحة أو السيرفر غير متاح');
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
                    loginCard.classList.add('hidden-box');
                }
                
                setTimeout(() => {
                    const splash = document.getElementById('splash-container');
                    if (splash) {
                        splash.classList.add('active');
                    }
                    
                    setTimeout(() => {
                        if (splash) {
                            splash.style.opacity = '0';
                        }
                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 1000);
                    }, 4000);
                }, 600);
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

