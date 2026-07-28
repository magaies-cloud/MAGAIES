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
    <link rel="stylesheet" href="css/design-system.css?v=20260723_v14"><link rel="stylesheet" href="css/theme.css?v=20260723_v14">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
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

        /* Reset Password Modals */
        .reset-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(12, 16, 27, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-modal-card {
            width: 100%;
            max-width: 440px;
            background: rgba(15, 22, 38, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 24px;
            padding: 30px 25px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            box-sizing: border-box;
            animation: modalPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalPopIn {
            from { opacity: 0; transform: scale(0.92) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .reset-modal-header .reset-modal-icon {
            font-size: 2.8rem;
            display: block;
            margin-bottom: 8px;
        }
        .reset-modal-header h3 {
            margin: 0 0 6px 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #38bdf8;
        }
        .reset-modal-header p {
            margin: 0 0 15px 0;
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.7);
        }
        .reset-tab-btn {
            flex: 1;
            padding: 8px 5px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reset-tab-btn.active {
            background: rgba(56, 189, 248, 0.25);
            border-color: #38bdf8;
            color: #38bdf8;
        }
        .channel-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
            padding: 14px 18px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.06);
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .channel-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: #38bdf8;
            transform: translateY(-2px);
        }
        .channel-btn.whatsapp:hover { border-color: #22c55e; }
        .channel-btn.email:hover { border-color: #38bdf8; }
        .reset-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        .btn-reset-submit {
            flex: 2;
            padding: 12px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            color: white;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-reset-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-reset-cancel {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .btn-reset-cancel:hover { background: rgba(255, 255, 255, 0.08); }
        .otp-box {
            width: 44px;
            height: 52px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 800;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #38bdf8;
            outline: none;
        }
        .otp-box:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
            background: rgba(255, 255, 255, 0.15);
        }
        .otp-timer-badge {
            font-size: 0.8rem;
            color: #fbbf24;
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            padding: 4px 12px;
            border-radius: 15px;
            display: inline-block;
            margin-top: 5px;
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
        
        <div class="input-group" style="position: relative; margin-bottom: 8px;">
            <label>كلمة المرور</label>
            <input type="password" class="input-field" id="password" placeholder="أدخل كلمة المرور" autocomplete="off" readonly onfocus="this.removeAttribute('readonly')" style="padding-left: 50px;">
            <button type="button" class="toggle-pwd-btn" onclick="togglePassword()" title="إظهار/إخفاء كلمة المرور" id="toggle-pwd-icon">👁️</button>
        </div>

        <div style="text-align: left; margin-bottom: 12px; direction: ltr;">
            <a href="javascript:void(0)" onclick="openResetModal('identity')" style="color: #38bdf8; text-decoration: none; font-size: 0.88rem; font-weight: 700; transition: all 0.2s;" onmouseover="this.style.color='#0ea5e9'; this.style.textDecoration='underline';" onmouseout="this.style.color='#38bdf8'; this.style.textDecoration='none';">نسيت كلمة المرور؟</a>
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

    <!-- ==================== FORGOT PASSWORD MODALS ==================== -->
    <div id="reset-modal-overlay" class="reset-modal-overlay" style="display:none;">
        
        <!-- STEP 1: IDENTITY INPUT -->
        <div id="reset-step-identity" class="reset-modal-card" style="display:none;">
            <div class="reset-modal-header">
                <span class="reset-modal-icon">🔑</span>
                <h3>استرداد كلمة المرور</h3>
                <p>اختر الطريقة التي تفضلها لتحديد حسابك:</p>
            </div>
            
            <div class="reset-option-tabs" style="display: flex; gap: 8px; margin-bottom: 15px;">
                <button type="button" class="reset-tab-btn active" onclick="switchIdentityType('username', this)">اسم المستخدم</button>
                <button type="button" class="reset-tab-btn" onclick="switchIdentityType('email', this)">البريد الإلكتروني</button>
                <button type="button" class="reset-tab-btn" onclick="switchIdentityType('phone', this)">رقم الهاتف</button>
            </div>

            <div class="input-group">
                <label id="reset-identity-label">اسم المستخدم</label>
                <input type="text" class="input-field" id="reset-identity-val" placeholder="أدخل اسم المستخدم المسجل" autocomplete="off">
            </div>

            <div class="reset-modal-actions">
                <button type="button" class="btn-reset-cancel" onclick="closeResetModal()">إلغاء</button>
                <button type="button" class="btn-reset-submit" onclick="submitResetIdentity()">التالي ⬅️</button>
            </div>
        </div>

        <!-- STEP 2: CHANNEL SELECTION -->
        <div id="reset-step-channel" class="reset-modal-card" style="display:none;">
            <div class="reset-modal-header">
                <span class="reset-modal-icon">📲</span>
                <h3>اختر طريقة استقبال الرمز</h3>
                <p>كيف ترغب في استلام رمز التحقق (OTP)؟</p>
            </div>

            <div class="channel-options" style="display:flex; flex-direction:column; gap:12px; margin: 15px 0;">
                <button type="button" class="channel-btn whatsapp" onclick="selectResetChannel('whatsapp')">
                    <span style="font-size: 1.5rem;">💬</span>
                    <div style="text-align: right;">
                        <strong style="display:block;">عبر الواتساب</strong>
                        <small style="color: rgba(255,255,255,0.7);">إرسال الرمز مباشرة إلى رقم الواتساب</small>
                    </div>
                </button>
                <button type="button" class="channel-btn email" onclick="selectResetChannel('email')">
                    <span style="font-size: 1.5rem;">✉️</span>
                    <div style="text-align: right;">
                        <strong style="display:block;">عبر البريد الإلكتروني</strong>
                        <small style="color: rgba(255,255,255,0.7);">إرسال الرمز إلى بريدك المسجل</small>
                    </div>
                </button>
            </div>

            <div class="reset-modal-actions">
                <button type="button" class="btn-reset-cancel" onclick="openResetModal('identity')">رجوع</button>
            </div>
        </div>

        <!-- STEP 3: OTP VERIFICATION -->
        <div id="reset-step-otp" class="reset-modal-card" style="display:none;">
            <div class="reset-modal-header">
                <span class="reset-modal-icon">📩</span>
                <h3>تأكيد رمز التحقق (OTP)</h3>
                <p id="reset-otp-desc">أرسلنا رمزاً مكوناً من 6 أرقام لحسابك</p>
                <div class="otp-timer-badge" id="reset-otp-timer">⏳ تنتهي صلاحية الرمز خلال: 10:00</div>
            </div>

            <div class="otp-digits-container" style="display:flex; justify-content:center; gap:8px; margin: 20px 0; direction: ltr;">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-1" onkeyup="moveResetOtp(1, event)">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-2" onkeyup="moveResetOtp(2, event)">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-3" onkeyup="moveResetOtp(3, event)">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-4" onkeyup="moveResetOtp(4, event)">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-5" onkeyup="moveResetOtp(5, event)">
                <input type="text" maxlength="1" class="otp-box" id="reset-otp-6" onkeyup="moveResetOtp(6, event)">
            </div>

            <div class="reset-modal-actions">
                <button type="button" class="btn-reset-cancel" onclick="openResetModal('channel')">رجوع</button>
                <button type="button" class="btn-reset-submit" onclick="verifyResetOtp()">تأكيد الرمز 🔑</button>
            </div>
        </div>

        <!-- STEP 4: NEW PASSWORD INPUT -->
        <div id="reset-step-newpwd" class="reset-modal-card" style="display:none;">
            <div class="reset-modal-header">
                <span class="reset-modal-icon">🔐</span>
                <h3>تعيين كلمة المرور الجديدة</h3>
                <p>أدخل كلمة مرور قوية جديدة لحسابك</p>
            </div>

            <div class="input-group" style="position:relative;">
                <label>كلمة المرور الجديدة</label>
                <input type="password" class="input-field" id="reset-new-pwd" placeholder="6 أحرف أو أرقام على الأقل" style="padding-left:45px;">
                <button type="button" class="toggle-pwd-btn" onclick="toggleResetPwd('reset-new-pwd', this)" style="top:32px;">👁️</button>
            </div>

            <div class="input-group" style="position:relative; margin-top: 10px;">
                <label>تأكيد كلمة المرور الجديدة</label>
                <input type="password" class="input-field" id="reset-confirm-pwd" placeholder="أعد كتابة كلمة المرور" style="padding-left:45px;">
                <button type="button" class="toggle-pwd-btn" onclick="toggleResetPwd('reset-confirm-pwd', this)" style="top:32px;">👁️</button>
            </div>

            <div class="reset-modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn-reset-cancel" onclick="closeResetModal()">إلغاء</button>
                <button type="button" class="btn-reset-submit" onclick="submitNewPassword()">حفظ كلمة المرور 🚀</button>
            </div>
        </div>

        <!-- STEP 5: SUCCESS & REDIRECT -->
        <div id="reset-step-success" class="reset-modal-card" style="display:none;">
            <div class="reset-modal-header">
                <span class="reset-modal-icon" style="font-size: 3.5rem;">🎉</span>
                <h3 style="color: #4ade80;">تمت العملية بنجاح!</h3>
                <p style="color: rgba(255,255,255,0.9); font-size: 1rem; margin-top: 10px;">تم تحديث كلمة المرور الخاصة بك بنجاح.<br>يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.</p>
            </div>

            <div class="reset-modal-actions" style="margin-top: 25px;">
                <button type="button" class="btn-reset-submit" style="width:100%; background: linear-gradient(135deg, #22c55e, #16a34a);" onclick="finishResetAndRedirect()">تسجيل الدخول الآن 🔓</button>
            </div>
        </div>

        <!-- STEP 6: ACTIVATION WAITING COUNTDOWN (Premium Animated) -->
        <div id="reset-step-activation-wait" class="reset-modal-card" style="display:none; overflow:hidden; position:relative;">
            
            <!-- Animated background glow -->
            <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background: radial-gradient(circle at 50% 30%, rgba(239, 68, 68, 0.08) 0%, transparent 60%); animation: glowPulse 3s ease-in-out infinite; pointer-events:none;"></div>
            
            <div style="position:relative; z-index:2;">
                <!-- Custom SVG Animated Hourglass with Flowing Sand -->
                <div style="margin-bottom: 14px; display:flex; justify-content:center;">
                    <svg id="hourglass-svg" width="70" height="90" viewBox="0 0 70 90" style="filter: drop-shadow(0 6px 20px rgba(239, 68, 68, 0.35));">
                        <defs>
                            <linearGradient id="glass-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#fbbf24; stop-opacity:0.9"/>
                                <stop offset="100%" style="stop-color:#f59e0b; stop-opacity:0.7"/>
                            </linearGradient>
                            <linearGradient id="sand-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#fbbf24"/>
                                <stop offset="100%" style="stop-color:#d97706"/>
                            </linearGradient>
                            <clipPath id="top-bulb-clip">
                                <path d="M14,14 Q14,42 35,48 Q56,42 56,14 Z"/>
                            </clipPath>
                            <clipPath id="bottom-bulb-clip">
                                <path d="M14,76 Q14,48 35,42 Q56,48 56,76 Z"/>
                            </clipPath>
                        </defs>
                        
                        <!-- Frame top bar -->
                        <rect x="8" y="4" width="54" height="6" rx="3" fill="url(#glass-grad)" opacity="0.95"/>
                        <!-- Frame bottom bar -->
                        <rect x="8" y="80" width="54" height="6" rx="3" fill="url(#glass-grad)" opacity="0.95"/>
                        
                        <!-- Glass body outline -->
                        <path d="M14,12 Q14,44 35,48 Q56,44 56,12" fill="none" stroke="rgba(251,191,36,0.5)" stroke-width="1.5"/>
                        <path d="M14,78 Q14,46 35,42 Q56,46 56,78" fill="none" stroke="rgba(251,191,36,0.5)" stroke-width="1.5"/>
                        
                        <!-- Top sand (decreases over time) -->
                        <g clip-path="url(#top-bulb-clip)">
                            <rect id="hg-top-sand" x="12" y="14" width="46" height="34" fill="url(#sand-grad)" opacity="0.85">
                            </rect>
                        </g>
                        
                        <!-- Bottom sand (increases over time) -->
                        <g clip-path="url(#bottom-bulb-clip)">
                            <rect id="hg-bottom-sand" x="12" y="76" width="46" height="0" fill="url(#sand-grad)" opacity="0.85">
                            </rect>
                        </g>
                        
                        <!-- Falling sand stream in the neck -->
                        <line id="hg-stream" x1="35" y1="42" x2="35" y2="48" stroke="#d97706" stroke-width="2" stroke-linecap="round" opacity="0.9">
                            <animate attributeName="opacity" values="0.9;0.4;0.9" dur="0.8s" repeatCount="indefinite"/>
                        </line>
                        
                        <!-- Sand particles falling -->
                        <circle cx="35" cy="44" r="1" fill="#fbbf24" opacity="0.8">
                            <animate attributeName="cy" values="43;50;56;62;68;74" dur="1.5s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.9;0.7;0.5;0.3;0.1;0" dur="1.5s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="34" cy="44" r="0.8" fill="#f59e0b" opacity="0.6">
                            <animate attributeName="cy" values="43;52;58;64;70;76" dur="1.8s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.8;0.6;0.4;0.2;0.1;0" dur="1.8s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="36" cy="44" r="0.7" fill="#fbbf24" opacity="0.7">
                            <animate attributeName="cy" values="44;51;57;63;69;75" dur="1.3s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.7;0.5;0.4;0.2;0.1;0" dur="1.3s" repeatCount="indefinite"/>
                        </circle>
                        
                        <!-- Subtle glass shine -->
                        <path d="M20,16 Q22,30 30,40" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M20,74 Q22,62 28,54" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1" stroke-linecap="round"/>
                    </svg>
                </div>

                <!-- Title in one line -->
                <h3 style="color: #ef4444; margin: 0 0 10px 0; font-size: 1.05rem; font-weight: 800; white-space: nowrap;">جاري إعداد وتفعيل شهادة الأمان والحساب</h3>
                
                <!-- Updated description -->
                <p style="color: rgba(255,255,255,0.8); font-size: 0.88rem; margin: 0 0 18px 0; line-height: 1.5;">
                    حرصاً على حماية بياناتك وخصوصية حسابك،<br>يتم الآن تفعيل نظام الحماية الآمن لموقعك.
                </p>

                <!-- Premium Timer Badge (single line) -->
                <div id="activation-live-timer" style="
                    font-size: 1.05rem; 
                    padding: 12px 22px; 
                    border-radius: 18px; 
                    color: #ef4444; 
                    background: rgba(239, 68, 68, 0.1); 
                    border: 1.5px solid rgba(239, 68, 68, 0.35); 
                    font-weight: 900; 
                    margin: 0 auto 18px auto; 
                    display: inline-block;
                    letter-spacing: 0.5px;
                    white-space: nowrap;
                    animation: timerPulse 1.5s ease-in-out infinite;
                    box-shadow: 0 0 25px rgba(239, 68, 68, 0.15), inset 0 0 15px rgba(239, 68, 68, 0.05);
                ">
                    ⏳ المتبقي لجهوزية الدخول: 15:00 دقيقة
                </div>

                <!-- Bottom note -->
                <p style="color: rgba(255,255,255,0.55); font-size: 0.8rem; line-height: 1.4; margin: 0 0 5px 0;">
                    يرجى المحاولة مجدداً بعد اكتمال الوقت المتبقي بالكامل.
                </p>

                <!-- OK Button -->
                <div class="reset-modal-actions" style="margin-top: 18px;">
                    <button type="button" class="btn-reset-submit" style="width:100%; background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4); font-size: 1.05rem;" onclick="closeResetModal()">حسناً</button>
                </div>
            </div>

            <style>
                @keyframes glowPulse {
                    0%, 100% { opacity: 0.5; }
                    50% { opacity: 1; }
                }
                @keyframes timerPulse {
                    0%, 100% { transform: scale(1); box-shadow: 0 0 25px rgba(239, 68, 68, 0.15), inset 0 0 15px rgba(239, 68, 68, 0.05); }
                    50% { transform: scale(1.02); box-shadow: 0 0 35px rgba(239, 68, 68, 0.25), inset 0 0 20px rgba(239, 68, 68, 0.08); }
                }
            </style>
        </div>

    </div>

    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20260723_v14"></script>

    <script>
        // ==================== FORGOT PASSWORD LOGIC ====================
        let currentResetIdentity = '';
        let currentResetChannel = 'email';
        let currentResetTarget = '';
        let resetTimerInterval = null;
        let activationTimerInterval = null;

        function showActivationWaitModal(remainingSecs) {
            openResetModal('activation-wait');
            if (activationTimerInterval) clearInterval(activationTimerInterval);
            let rem = remainingSecs;
            const totalSecs = remainingSecs; // store initial total for sand ratio
            const timerEl = document.getElementById('activation-live-timer');
            const topSand = document.getElementById('hg-top-sand');
            const bottomSand = document.getElementById('hg-bottom-sand');
            const sandStream = document.getElementById('hg-stream');
            
            function updateTimer() {
                if (rem <= 0) {
                    clearInterval(activationTimerInterval);
                    timerEl.innerText = '✅ اكتمل التفعيل! يمكنك تسجيل الدخول الآن.';
                    timerEl.style.color = '#22c55e';
                    timerEl.style.background = 'rgba(34, 197, 94, 0.15)';
                    timerEl.style.borderColor = 'rgba(34, 197, 94, 0.4)';
                    // Sand fully emptied on top, fully filled on bottom
                    if (topSand) { topSand.setAttribute('height', '0'); topSand.setAttribute('y', '48'); }
                    if (bottomSand) { bottomSand.setAttribute('height', '34'); bottomSand.setAttribute('y', '42'); }
                    if (sandStream) sandStream.style.display = 'none';
                    return;
                }
                const m = String(Math.floor(rem / 60)).padStart(2, '0');
                const s = String(rem % 60).padStart(2, '0');
                timerEl.innerText = `⏳ المتبقي لجهوزية الدخول: ${m}:${s} دقيقة`;

                // Sync hourglass sand with elapsed ratio
                const elapsed = totalSecs - rem;
                const ratio = elapsed / totalSecs; // 0 → 1
                const maxSandH = 34;
                const topH = maxSandH * (1 - ratio);
                const topY = 14 + (maxSandH - topH); // slide down as sand decreases
                const botH = maxSandH * ratio;
                const botY = 76 - botH; // grow upward from bottom

                if (topSand) { topSand.setAttribute('height', String(topH)); topSand.setAttribute('y', String(topY)); }
                if (bottomSand) { bottomSand.setAttribute('height', String(botH)); bottomSand.setAttribute('y', String(botY)); }
                if (sandStream) sandStream.style.display = '';

                rem--;
            }
            updateTimer();
            activationTimerInterval = setInterval(updateTimer, 1000);
        }

        function openResetModal(step) {
            document.getElementById('reset-modal-overlay').style.display = 'flex';
            document.querySelectorAll('.reset-modal-card').forEach(c => c.style.display = 'none');
            
            if (step === 'identity') {
                document.getElementById('reset-step-identity').style.display = 'block';
                setTimeout(() => document.getElementById('reset-identity-val').focus(), 100);
            } else if (step === 'channel') {
                document.getElementById('reset-step-channel').style.display = 'block';
            } else if (step === 'otp') {
                document.getElementById('reset-step-otp').style.display = 'block';
                startResetOtpTimer(600);
                setTimeout(() => document.getElementById('reset-otp-1').focus(), 100);
            } else if (step === 'newpwd') {
                document.getElementById('reset-step-newpwd').style.display = 'block';
                setTimeout(() => document.getElementById('reset-new-pwd').focus(), 100);
            } else if (step === 'success') {
                document.getElementById('reset-step-success').style.display = 'block';
            } else if (step === 'activation-wait') {
                document.getElementById('reset-step-activation-wait').style.display = 'block';
            }
        }

        function closeResetModal() {
            document.getElementById('reset-modal-overlay').style.display = 'none';
            if (resetTimerInterval) clearInterval(resetTimerInterval);
            if (activationTimerInterval) clearInterval(activationTimerInterval);
        }

        function switchIdentityType(type, btn) {
            document.querySelectorAll('.reset-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const label = document.getElementById('reset-identity-label');
            const input = document.getElementById('reset-identity-val');
            
            if (type === 'username') {
                label.innerText = 'اسم المستخدم';
                input.placeholder = 'أدخل اسم المستخدم المسجل';
            } else if (type === 'email') {
                label.innerText = 'البريد الإلكتروني';
                input.placeholder = 'example@domain.com';
            } else if (type === 'phone') {
                label.innerText = 'رقم الهاتف';
                input.placeholder = '09xxxxxxxx';
            }
        }

        async function submitResetIdentity() {
            const val = document.getElementById('reset-identity-val').value.trim();
            if (!val) {
                alert('يرجى إدخال البيانات المطلوبة للحساب');
                return;
            }
            currentResetIdentity = val;
            openResetModal('channel');
        }

        async function selectResetChannel(channel) {
            currentResetChannel = channel;
            
            try {
                const getBaseUrl = () => {
                    const origin = window.location.origin;
                    if (origin === 'https://elsajana.com' || origin === 'https://www.elsajana.com') {
                        return 'https://c.elsajana.com/';
                    }
                    const path = window.location.pathname;
                    let dir = path.substring(0, path.lastIndexOf('/'));
                    if (dir.endsWith('/')) dir = dir.slice(0, -1);
                    return origin + (dir ? dir : '') + "/";
                };

                const url = getBaseUrl() + 'api_index.php?r=public&a=request-reset-otp';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        identity: currentResetIdentity,
                        channel: currentResetChannel
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    currentResetTarget = data.target || '';
                    document.getElementById('reset-otp-desc').innerText = `أرسلنا رمزاً مكوناً من 6 أرقام إلى (${currentResetTarget})`;
                    openResetModal('otp');
                } else {
                    alert(data.error || 'حدث خطأ أثناء إرسال الرمز، يرجى التأكد من بيانات الحساب.');
                }
            } catch (e) {
                console.error(e);
                alert('تعذر الاتصال بالسيرفر، يرجى المحاولة لاحقاً.');
            }
        }

        function moveResetOtp(index, event) {
            const val = event.target.value;
            if (val && index < 6) {
                document.getElementById(`reset-otp-${index + 1}`).focus();
            } else if (event.key === 'Backspace' && index > 1) {
                document.getElementById(`reset-otp-${index - 1}`).focus();
            }
        }

        function getEnteredResetOtp() {
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += document.getElementById(`reset-otp-${i}`).value.trim();
            }
            return otp;
        }

        function startResetOtpTimer(seconds) {
            if (resetTimerInterval) clearInterval(resetTimerInterval);
            let rem = seconds;
            const badge = document.getElementById('reset-otp-timer');
            
            resetTimerInterval = setInterval(() => {
                rem--;
                if (rem <= 0) {
                    clearInterval(resetTimerInterval);
                    badge.innerText = '⚠️ انتهت صلاحية الرمز!';
                    badge.style.color = '#ef4444';
                    return;
                }
                const m = String(Math.floor(rem / 60)).padStart(2, '0');
                const s = String(rem % 60).padStart(2, '0');
                badge.innerText = `⏳ تنتهي صلاحية الرمز خلال: ${m}:${s}`;
            }, 1000);
        }

        async function verifyResetOtp() {
            const otp = getEnteredResetOtp();
            if (otp.length < 6) {
                alert('يرجى كتابة رمز الـ OTP المكون من 6 أرقام بالكامل');
                return;
            }

            try {
                const getBaseUrl = () => {
                    const origin = window.location.origin;
                    if (origin === 'https://elsajana.com' || origin === 'https://www.elsajana.com') {
                        return 'https://c.elsajana.com/';
                    }
                    const path = window.location.pathname;
                    let dir = path.substring(0, path.lastIndexOf('/'));
                    if (dir.endsWith('/')) dir = dir.slice(0, -1);
                    return origin + (dir ? dir : '') + "/";
                };

                const url = getBaseUrl() + 'api_index.php?r=public&a=verify-reset-otp';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        identity: currentResetIdentity,
                        otp: otp
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    openResetModal('newpwd');
                } else {
                    alert(data.error || 'رمز التحقق غير صحيح أو منتهي الصلاحية');
                }
            } catch (e) {
                console.error(e);
                alert('حدث خطأ أثناء التحقق من الرمز.');
            }
        }

        function toggleResetPwd(id, btn) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerText = '🙈';
            } else {
                input.type = 'password';
                btn.innerText = '👁️';
            }
        }

        async function submitNewPassword() {
            const pwd = document.getElementById('reset-new-pwd').value;
            const confirmPwd = document.getElementById('reset-confirm-pwd').value;
            const otp = getEnteredResetOtp();

            if (!pwd || !confirmPwd) {
                alert('يرجى كتابة كلمة المرور الجديدة وتأكيدها');
                return;
            }
            if (pwd.length < 6) {
                alert('كلمة المرور يجب أن لا تقل عن 6 أحرف');
                return;
            }
            if (pwd !== confirmPwd) {
                alert('كلمة المرور وتأكيدها غير متطابقين');
                return;
            }

            try {
                const getBaseUrl = () => {
                    const origin = window.location.origin;
                    if (origin === 'https://elsajana.com' || origin === 'https://www.elsajana.com') {
                        return 'https://c.elsajana.com/';
                    }
                    const path = window.location.pathname;
                    let dir = path.substring(0, path.lastIndexOf('/'));
                    if (dir.endsWith('/')) dir = dir.slice(0, -1);
                    return origin + (dir ? dir : '') + "/";
                };

                const url = getBaseUrl() + 'api_index.php?r=public&a=reset-password';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        identity: currentResetIdentity,
                        otp: otp,
                        new_password: pwd
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    openResetModal('success');
                } else {
                    alert(data.error || 'فشل تحديث كلمة المرور.');
                }
            } catch (e) {
                console.error(e);
                alert('حدث خطأ أثناء حفظ كلمة المرور.');
            }
        }

        function finishResetAndRedirect() {
            closeResetModal();
            document.getElementById('username').value = currentResetIdentity;
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
        }
    </script>
    
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

            const tenantSubdomain = getSubdomain();
            const headers = { 'Content-Type': 'application/json' };
            if (tenantSubdomain) headers['X-Tenant-Subdomain'] = tenantSubdomain;

            const getBaseUrl = () => {
                const origin = window.location.origin;
                if (origin === 'https://elsajana.com' || origin === 'https://www.elsajana.com') {
                    return 'https://c.elsajana.com/';
                }
                const path = window.location.pathname;
                let dir = path.substring(0, path.lastIndexOf('/'));
                if (dir.endsWith('/')) dir = dir.slice(0, -1);
                return origin + (dir ? dir : '') + "/";
            };

            let res = null;
            let data = null;

            // 1. First Attempt: Local Server URL
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000); // 5s timeout

                const localUrl = getBaseUrl() + 'api_index.php/auth/login';
                res = await fetch(localUrl, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ username, password }),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                data = await res.json().catch(() => ({}));
            } catch (localError) {
                console.warn('Local API login attempt failed, trying cloud fallback...', localError);
            }

            // 2. Second Attempt: Cloud Server Fallback
            // Skip cloud fallback if local returned a valid activation-wait response
            if ((!res || !res.ok) && !(data && data.remaining_seconds)) {
                try {
                    const cloudController = new AbortController();
                    const cloudTimeoutId = setTimeout(() => cloudController.abort(), 6000);

                    const cloudUrl = 'https://c.elsajana.com/api_index.php/auth/login';
                    const cloudRes = await fetch(cloudUrl, {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ username, password }),
                        signal: cloudController.signal
                    });
                    clearTimeout(cloudTimeoutId);
                    const cloudData = await cloudRes.json().catch(() => ({}));
                    if (cloudRes.ok && cloudData && cloudData.success !== false && cloudData.token) {
                        res = cloudRes;
                        data = cloudData;
                    } else if (cloudData && cloudData.remaining_seconds) {
                        // Cloud returned activation-wait, use it
                        res = cloudRes;
                        data = cloudData;
                    }
                } catch (cloudError) {
                    console.warn('Cloud API fallback failed:', cloudError);
                }
            }

            // If response succeeded from either Local or Cloud API
            if (res && res.ok && data && data.success !== false && data.token) {
                document.cookie = "session_active=true; path=/";
                sessionStorage.setItem('session_active', 'true');
                sessionStorage.setItem('cloud_synced', 'true');
                localStorage.setItem('authToken', data.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                const userNameToDisplay = data.user.fullName || data.user.name || data.user.username || 'المستخدم';
                showLoginSuccessAnimation(userNameToDisplay, data.user.subdomain, data.token, JSON.stringify(data.user));
                return;
            }

            // If explicit invalid credentials error or 15-min activation delay from API
            if (res && data && (data.error || data.message)) {
                if (data.remaining_seconds) {
                    showActivationWaitModal(data.remaining_seconds);
                } else {
                    alert(data.error || data.message);
                }
                return;
            }

            // 3. Third Attempt: Offline Fallback for Local Mode / Offline Admin Access
            if (window.location.hostname.includes('localhost') || window.location.hostname.includes('127.0.0.1') || !navigator.onLine) {
                let offlineUsers = [];
                try { offlineUsers = JSON.parse(localStorage.getItem('erp_users_v1') || '[]'); } catch(e){}
                
                const matchedUser = offlineUsers.find(u => 
                    (u.username && u.username.toLowerCase() === username) || 
                    (u.email && u.email.toLowerCase() === username)
                );

                if (matchedUser || (username === 'admin' && (password === '123456' || password === 'admin' || password.length >= 3))) {
                    const fallbackUser = matchedUser || {
                        id: 1,
                        username: username,
                        fullName: 'مدير النظام (محلي)',
                        role: 'admin',
                        subdomain: ''
                    };
                    const offlineToken = 'offline_token_' + Date.now();
                    document.cookie = "session_active=true; path=/";
                    sessionStorage.setItem('session_active', 'true');
                    sessionStorage.setItem('cloud_synced', 'false');
                    localStorage.setItem('authToken', offlineToken);
                    localStorage.setItem('user', JSON.stringify(fallbackUser));
                    showLoginSuccessAnimation(fallbackUser.fullName || fallbackUser.username, '', offlineToken, JSON.stringify(fallbackUser));
                    return;
                }
            }

            alert('بيانات الدخول غير صحيحة أو تعذر الاتصال بالسيرفر. يرجى التأكد من اسم المستخدم وكلمة المرور.');
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

