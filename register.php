<?php $page_title = 'تسجيل شركة جديدة - السجانة ERP SaaS'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل شركة جديدة - السجانة ERP SaaS</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <style>
        .otp-digit:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.5);
            background: rgba(0, 0, 0, 0.4) !important;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }
        .register-card {
            width: 90%;
            max-width: 800px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .input-group {
            position: relative;
            text-align: right;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .input-group label {
            position: absolute;
            top: 14px;
            right: 12px;
            color: #94a3b8;
            font-size: 1rem;
            transition: 0.2s ease all;
            pointer-events: none;
            background: #1e293b;
            padding: 0 4px;
            z-index: 10;
        }
        .input-field {
            width: 100%;
            padding: 14px 12px;
            border-radius: 8px;
            border: 1px solid #334155;
            background: transparent;
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            position: relative;
            z-index: 5;
        }
        .input-field:focus {
            border-color: #38bdf8;
            outline: none;
        }
        .input-field:focus ~ label, .input-field:not(:placeholder-shown) ~ label {
            top: -10px;
            font-size: 0.8rem;
            color: #38bdf8;
        }
        .subdomain-group {
            display: flex;
            align-items: center;
        }
        .subdomain-group .input-field {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            text-align: left;
            direction: ltr;
        }
        .subdomain-addon {
            background: #334155;
            padding: 12px;
            border: 1px solid #334155;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            color: #94a3b8;
            direction: ltr;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #38bdf8;
            color: #0f172a;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #0ea5e9;
        }
        .link {
            color: #38bdf8;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
            margin-top: 15px;
        }

        /* Steps CSS */
        .step { display: none; }
        .step.active { display: block; animation: fadeIn 0.4s ease; }
        .step-indicators { display: flex; justify-content: center; margin-bottom: 25px; gap: 10px; }
        .step-dot { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid #334155; }
        .step-dot.active { background: #38bdf8; color: #0f172a; border-color: #38bdf8; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Password Strength Indicator */
        .strength-bar-container {
            height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; margin-top: 8px; overflow: hidden;
            display: flex; gap: 2px;
        }
        .strength-segment {
            flex: 1; height: 100%; background: transparent; transition: background 0.3s;
        }
        .strength-text {
            font-size: 0.8rem; margin-top: 5px; text-align: left; height: 14px;
        }

        /* Custom Modal Styles */
        .custom-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .custom-modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        .custom-modal {
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            transform: translateY(20px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .custom-modal-overlay.active .custom-modal {
            transform: translateY(0) scale(1);
        }
        .custom-modal-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .custom-modal-icon.success {
            color: #10b981;
            animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .custom-modal-icon.error {
            color: #ef4444;
            animation: shake 0.5s;
        }
        .custom-modal-title {
            color: #f8fafc;
            font-size: 1.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .custom-modal-message {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 15px;
            width: 100%;
        }
        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
            color: white;
        }
        .whatsapp-icon {
            margin-left: 10px;
            font-size: 1.2rem;
        }
        .modal-action-btn {
            background: transparent;
            color: #38bdf8;
            border: 1px solid #38bdf8;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
            width: 100%;
        }
        .modal-action-btn:hover {
            background: rgba(56, 189, 248, 0.1);
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            75% { transform: translateX(-10px); }
        }
    </style>
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>


    <div class="register-card">
        <h2 style="margin-top:0;">🚀 إنشاء حساب جديد</h2>
        <p style="color: #94a3b8; font-size:0.9rem;">أنشئ حساب شركتك في ثوانٍ لاستخدام السجانة ERP</p>
        
        <div id="plan-info" style="display:none; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.3); padding:10px; border-radius:10px; margin-bottom:15px; color:#38bdf8; font-weight:bold;">
            الباقة المختارة: <span id="plan-name-display">جاري التحميل...</span>
        </div>

        <!-- Step Indicators -->
        <div class="step-indicators">
            <div class="step-dot active" id="dot-1">1</div>
            <div class="step-dot" id="dot-2">2</div>
            <div class="step-dot" id="dot-3">3</div>
            <div class="step-dot" id="dot-4" style="font-size: 14px;">✉️</div>
        </div>

        <form autocomplete="off" onsubmit="event.preventDefault();">
            <div class="step active" id="step-1">
                <h3 style="margin-top:0; color:#38bdf8;">بيانات الشركة</h3>
                <div class="input-group">
                    <input type="text" class="input-field step-1-input" id="company_name" placeholder=" ">
                    <label>اسم الشركة (عربي أو إنجليزي)</label>
                </div>

                <div class="input-group" style="margin-bottom: 25px; text-align: right;">
                    <label style="position: static; font-size: 1rem; color: #0ea5e9; background: none; padding: 0; margin-bottom: 5px; display: block; pointer-events: auto;">
                        الرابط المخصص للشركة
                    </label>
                    <p style="color: #64748b; font-size: 0.85rem; margin-top: 0; margin-bottom: 10px; line-height: 1.4;">
                        (يجب أن يكون <span style="color: #f97316; font-weight: bold;">باللغة الإنجليزية</span> وبدون مسافات. هذا هو الرابط الفريد الذي ستدخل من خلاله أنت وموظفيك إلى النظام)
                    </p>
                    <div class="subdomain-group">
                        <input type="text" class="input-field step-1-input" id="subdomain" placeholder="مثال: alofoq" style="text-align:right;" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, ''); debounceCheckUnique('subdomain', 'subdomain', 'هذا الرابط مستخدم مسبقاً، يرجى اختيار رابط آخر')">
                        <div class="subdomain-addon">.elsajana.com</div>
                    </div>
                </div>

                <button type="button" class="btn-submit" onclick="nextStep(2)">التالي ⬅️</button>
            </div>

            <!-- الخطوة 2: معلومات التواصل -->
            <div class="step" id="step-2">
                <h3 style="margin-top:0; color:#fff; text-align:right; font-size:1.3rem;">بيانات التواصل</h3>
                <p style="color:#94a3b8; text-align:right; font-size:0.9rem; margin-bottom:20px;">هذه البيانات ضرورية لكي نتمكن من التواصل معك</p>

                <div class="input-group">
                    <input type="text" class="input-field step-2-input" id="owner_name" placeholder=" ">
                    <label>الاسم الكامل لمالك الحساب</label>
                </div>
                <div class="input-group">
                    <input type="email" class="input-field step-2-input" id="owner_email" placeholder=" " oninput="debounceCheckUnique('owner_email', 'email', 'البريد الإلكتروني هذا مستخدم مسبقاً')" onblur="checkUnique('owner_email', 'email', 'البريد الإلكتروني هذا مستخدم مسبقاً')">
                    <label>البريد الإلكتروني للإدارة</label>
                </div>
                <div class="input-group" style="position: relative;">
                    <input type="tel" class="input-field step-2-input" id="owner_phone" placeholder=" " oninput="debounceCheckUnique('owner_phone', 'phone', 'رقم الهاتف هذا مسجل لدينا مسبقاً')" onblur="checkUnique('owner_phone', 'phone', 'رقم الهاتف هذا مسجل لدينا مسبقاً')" style="padding-left: 45px;">
                    <label>رقم الواتساب لاستلام الـ OTP (مثال: 05xxxxxxx)</label>
                    <span style="position: absolute; left: 15px; top: 12px; font-size: 1.4rem; z-index: 15; pointer-events: none; display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#25D366"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.436 0 9.86-4.418 9.863-9.848.002-2.63-1.019-5.101-2.874-6.958C16.405 1.982 13.926.96 11.3.96 5.86.96 1.436 5.38 1.433 10.81c-.001 1.564.417 3.09 1.21 4.453l-.994 3.63 3.733-.979zm11.433-5.234c-.22-.11-1.306-.644-1.506-.719-.2-.075-.346-.112-.492.11-.146.22-.566.719-.694.869-.128.15-.256.169-.476.059-.22-.11-.93-.343-1.77-1.093-.655-.584-1.096-1.305-1.225-1.525-.128-.22-.014-.339.096-.449.1-.1.22-.256.33-.385.11-.128.146-.22.22-.366.075-.147.037-.275-.018-.385-.056-.11-.492-1.186-.674-1.62-.177-.428-.355-.37-.492-.377-.127-.007-.273-.008-.42-.008-.146 0-.384.055-.585.275-.2.22-.766.75-.766 1.83 0 1.078.783 2.119.892 2.269.11.15 1.542 2.355 3.736 3.3.522.226.93.36 1.246.46.524.167 1.002.143 1.379.087.42-.063 1.306-.534 1.489-1.05.183-.515.183-.956.128-1.05-.055-.094-.2-.15-.42-.26z"/></svg>
                    </span>
                </div>
                <div class="input-group" style="margin-bottom: 30px;">
                    <input type="tel" class="input-field step-2-input" id="owner_phone2" placeholder=" " oninput="debounceCheckUnique('owner_phone2', 'phone', 'رقم الهاتف هذا مسجل لدينا مسبقاً')" onblur="checkUnique('owner_phone2', 'phone', 'رقم الهاتف هذا مسجل لدينا مسبقاً')">
                    <label>رقم هاتف إضافي (اختياري)</label>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-submit" style="background:#334155; color:white;" onclick="prevStep(1)">➡️ السابق</button>
                    <button type="button" class="btn-submit" onclick="nextStep(3)">التالي ⬅️</button>
                </div>
            </div>

            <!-- الخطوة 3: بيانات الحماية والتأمين -->
            <div class="step" id="step-3">
                <h3 style="margin-top:0; color:#fff; text-align:right; font-size:1.3rem;">بيانات الدخول والأمان</h3>
                <p style="color:#94a3b8; text-align:right; font-size:0.9rem; margin-bottom:20px;">يرجى حفظ هذه البيانات جيداً لتتمكن من الدخول لنظامك</p>

                <div class="input-group">
                    <input type="text" class="input-field step-3-input" id="username" placeholder=" " oninput="debounceCheckUnique('username', 'username', 'اسم المستخدم هذا غير متاح، يرجى اختيار اسم آخر')" onblur="checkUnique('username', 'username', 'اسم المستخدم هذا غير متاح، يرجى اختيار اسم آخر')" autocomplete="new-password">
                    <label>اسم المستخدم (للدخول كمدير)</label>
                </div>

                <div class="input-group" style="margin-bottom: 10px;">
                    <input type="password" class="input-field step-3-input" id="password" placeholder=" " oninput="checkPasswordStrength(this.value)" autocomplete="new-password">
                    <label>كلمة المرور (يجب أن تكون قوية)</label>
                    <div class="strength-bar-container" id="strength-bar">
                        <div class="strength-segment" id="seg-1"></div>
                        <div class="strength-segment" id="seg-2"></div>
                        <div class="strength-segment" id="seg-3"></div>
                        <div class="strength-segment" id="seg-4"></div>
                    </div>
                    <div class="strength-text" id="strength-text"></div>
                </div>

                <div class="input-group">
                    <input type="password" class="input-field" id="confirm_password" placeholder=" " autocomplete="new-password">
                    <label>تأكيد كلمة المرور <span style="color: #ef4444;">*</span></label>
                </div>

                <div class="input-group" id="receipt-group" style="display: none;">
                    <input type="file" class="input-field" id="receipt_file" accept="image/*,.pdf" style="padding: 9px;">
                    <label style="top: -10px; font-size: 0.8rem; background:transparent;">إشعار الدفع (اختياري)</label>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-submit" style="background:#334155; color:white;" onclick="prevStep(2)">➡️ السابق</button>
                    <button type="button" class="btn-submit" onclick="sendOtpAndGoToStep4()">التحقق وإرسال الكود ✉️</button>
                </div>
            </div>

            <div class="step" id="step-4">
                <h3 style="margin-top:0; color:#10b981; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 1.4rem;" id="otp-header">
                    <span id="otp-header-icon" style="display: flex; align-items: center;"></span>
                    <span id="otp-header-text">تأكيد البريد الإلكتروني</span>
                </h3>
                <p id="otp-description" style="font-size: 1.05rem; font-weight: bold; margin-bottom: 20px; line-height: 1.6; font-family: Tajawal, sans-serif; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                    أرسلنا رمز تحقق (OTP) مكون من 6 أرقام إلى بريدك الإلكتروني.
                </p>
                
                <!-- Count-down Timer -->
                <div id="otp-timer-container" style="margin-top: 5px; margin-bottom: 20px; font-size: 0.95rem; color: #f43f5e; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 8px; font-family: Tajawal, sans-serif;">
                    <span>⏳ تنتهي صلاحية الرمز خلال:</span>
                    <span id="otp-timer" style="background: rgba(244,63,94,0.15); padding: 4px 8px; border-radius: 6px; font-size: 1.1rem; letter-spacing: 1px; border: 1px solid rgba(244,63,94,0.2);">10:00</span>
                </div>
                <div style="direction: ltr; display: flex; justify-content: center; gap: 10px; margin-bottom: 25px;">
                    <input type="text" class="otp-digit" id="otp-1" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                    <input type="text" class="otp-digit" id="otp-2" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                    <input type="text" class="otp-digit" id="otp-3" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                    <input type="text" class="otp-digit" id="otp-4" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                    <input type="text" class="otp-digit" id="otp-5" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                    <input type="text" class="otp-digit" id="otp-6" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 52px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; text-align: center; font-size: 1.5rem; font-weight: bold; transition: all 0.2s ease; outline: none; font-family: Tajawal;">
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-submit" style="background:#334155; color:white;" onclick="prevStep(3)">➡️ تعديل البيانات</button>
                    <button type="button" class="btn-submit" onclick="handleRegister()">تأكيد وإنشاء الحساب 🚀</button>
                </div>
            </div>
        </form>
        <a data-href="login.php" onclick="window.location.href='login.php'" class="link">لديك حساب بالفعل؟ تسجيل الدخول</a>
    </div>

    <!-- Custom Modal -->
    <div class="custom-modal-overlay" id="custom-modal">
        <div class="custom-modal">
            <div class="custom-modal-icon" id="modal-icon">✅</div>
            <div class="custom-modal-title" id="modal-title">نجاح</div>
            <div class="custom-modal-message" id="modal-message">تمت العملية بنجاح.</div>
            
            <a href="javascript:void(0)" onclick="showInquiryForm()" class="whatsapp-btn" id="modal-whatsapp" style="display:none;">
                <span class="whatsapp-icon">💬</span>
                للاستفسار: 0113981477
            </a>
            
            <!-- Inline Inquiry Form Container -->
            <div id="modal-inquiry-container" style="display:none; margin-top: 15px; text-align: right; width: 100%;">
                <label style="color: #fff; font-size: 0.95rem; font-weight: bold; display: block; margin-bottom: 8px; font-family: Tajawal;">اكتب استفسارك هنا للدعم الفني:</label>
                <textarea id="inquiry-message" style="width: 100%; height: 90px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.3); color: #fff; padding: 12px; font-family: Tajawal; font-size: 0.9rem; resize: none; box-sizing: border-box; outline: none; transition: border 0.3s ease; line-height: 1.5;" placeholder="اكتب استفسارك بالتفصيل..."></textarea>
                <div style="display: flex; gap: 8px; margin-top: 12px; direction: rtl;">
                    <button class="modal-action-btn" style="background:#334155; margin:0; flex: 1;" onclick="hideInquiryForm()">تراجع</button>
                    <button class="modal-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); margin:0; flex: 2; box-shadow: 0 4px 12px rgba(16,185,129,0.25);" onclick="submitInquiryForm()">إرسال الاستفسار 💬</button>
                </div>
            </div>
            
            <button class="modal-action-btn" id="modal-action-btn" onclick="closeModal()">حسناً</button>
        </div>
    </div>

    <!-- Verification Channel Selection Modal -->
    <div class="custom-modal-overlay" id="otp-channel-modal">
        <div class="custom-modal" style="max-width: 400px; text-align: center; padding: 25px;">
            <h3 style="color: #fff; margin-top: 0; margin-bottom: 10px; font-size: 1.3rem;">طريقة استلام الرمز (OTP)</h3>
            <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; text-align: center;">
                اختر طريقة استلام رمز التحقق لتأكيد حسابك:
            </p>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 15px;">
                <button type="button" onclick="requestOtpViaChannel('whatsapp')" class="modal-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16,185,129,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; font-weight: bold; color: #fff; border-radius: 8px; cursor: pointer; font-family: Tajawal;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.436 0 9.86-4.418 9.863-9.848.002-2.63-1.019-5.101-2.874-6.958C16.405 1.982 13.926.96 11.3.96 5.86.96 1.436 5.38 1.433 10.81c-.001 1.564.417 3.09 1.21 4.453l-.994 3.63 3.733-.979zm11.433-5.234c-.22-.11-1.306-.644-1.506-.719-.2-.075-.346-.112-.492.11-.146.22-.566.719-.694.869-.128.15-.256.169-.476.059-.22-.11-.93-.343-1.77-1.093-.655-.584-1.096-1.305-1.225-1.525-.128-.22-.014-.339.096-.449.1-.1.22-.256.33-.385.11-.128.146-.22.22-.366.075-.147.037-.275-.018-.385-.056-.11-.492-1.186-.674-1.62-.177-.428-.355-.37-.492-.377-.127-.007-.273-.008-.42-.008-.146 0-.384.055-.585.275-.2.22-.766.75-.766 1.83 0 1.078.783 2.119.892 2.269.11.15 1.542 2.355 3.736 3.3.522.226.93.36 1.246.46.524.167 1.002.143 1.379.087.42-.063 1.306-.534 1.489-1.05.183-.515.183-.956.128-1.05-.055-.094-.2-.15-.42-.26z"/></svg>
                    <span>عبر الواتساب</span>
                </button>
                <button type="button" onclick="requestOtpViaChannel('email')" class="modal-action-btn" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; font-weight: bold; color: #fff; border-radius: 8px; cursor: pointer; font-family: Tajawal;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm5.436-.56L16 11.801V4.697z"/></svg>
                    <span>عبر البريد الإلكتروني</span>
                </button>
            </div>
            <button type="button" onclick="closeOtpChannelModal()" class="modal-action-btn" style="background: #334155; margin-top: 10px; width: 100%; padding: 10px; border-radius: 8px; cursor: pointer; font-family: Tajawal;">إلغاء</button>
        </div>
    </div>

    <script src="js/api.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        let selectedPlanId = urlParams.get('plan');

        async function loadPlanInfo() {
            try {
                const r = await fetch('api_index.php?r=public&_=' + Date.now());
                const data = await r.json();
                if(data.plans) {
                    window.allPlans = data.plans;
                    if (selectedPlanId) {
                        const p = data.plans.find(x => x.id == selectedPlanId);
                        if(p) {
                            document.getElementById('plan-name-display').innerText = p.name;
                            const isTrial = p.isTrial || p.name.includes('تجريب');
                            if (!isTrial) document.getElementById('receipt-group').style.display = 'block';
                            else document.getElementById('receipt-group').style.display = 'none';
                        }
                    } else {
                        // Create a dropdown if no plan was selected
                        let selectHTML = '<select id="manual-plan-select" class="input-field" onchange="changeManualPlan(this.value)" style="margin-top:10px; padding:5px;">';
                        selectHTML += '<option value="">-- يرجى اختيار الباقة --</option>';
                        data.plans.forEach(plan => {
                            selectHTML += `<option value="${plan.id}">${plan.name} - ${plan.price}$</option>`;
                        });
                        selectHTML += '</select>';
                        document.getElementById('plan-name-display').innerHTML = selectHTML;
                    }
                }
            } catch (e) {
                document.getElementById('plan-name-display').innerHTML = '<span style="color:#ef4444;">حدث خطأ في تحميل الباقة. يرجى تحديث الصفحة.</span>';
                document.getElementById('receipt-group').style.display = 'block'; // Fallback to show it just in case
            }
        }
        
        window.changeManualPlan = function(val) {
            selectedPlanId = val;
            if(!val) {
                document.getElementById('receipt-group').style.display = 'none';
                return;
            }
            const p = window.allPlans.find(x => x.id == val);
            if(p) {
                const isTrial = p.isTrial || p.name.includes('تجريب');
                if (!isTrial) document.getElementById('receipt-group').style.display = 'block';
                else document.getElementById('receipt-group').style.display = 'none';
            }
        };

        // Always show plan-info block to either show selected plan or the dropdown
        document.getElementById('plan-info').style.display = 'block';
        loadPlanInfo();

        function checkPasswordStrength(val) {
            let strength = 0;
            if(val.length > 5) strength += 1;
            if(val.match(/[a-z]+/)) strength += 1;
            if(val.match(/[0-9]+/)) strength += 1;
            if(val.match(/[$@#&!]+/)) strength += 1;
            
            for(let i=1; i<=4; i++) document.getElementById('seg-'+i).style.background = 'transparent';
            
            let color = ''; let text = '';
            if(strength === 1) { color = '#ef4444'; text = 'ضعيفة'; }
            else if(strength === 2) { color = '#eab308'; text = 'متوسطة'; }
            else if(strength === 3) { color = '#3b82f6'; text = 'جيدة'; }
            else if(strength === 4) { color = '#22c55e'; text = 'قوية'; }
            else { text = ''; }
            
            for(let i=1; i<=strength; i++) document.getElementById('seg-'+i).style.background = color;
            document.getElementById('strength-text').innerText = text;
            document.getElementById('strength-text').style.color = color;
        }

        async function nextStep(step) {
            if (step === 2) {
                if (!document.getElementById('company_name').value || !document.getElementById('subdomain').value) {
                    showModal('error', 'نقص البيانات', 'يرجى إدخال اسم الشركة والرابط المخصص');
                    return;
                }
                
                const subdomain = document.getElementById('subdomain').value.trim();
                const subReg = /^[a-z0-9][a-z0-9\-]{1,18}[a-z0-9]$/;
                if (!subReg.test(subdomain)) {
                    showModal('error', 'خطأ في الرابط المخصص', 'الرابط المخصص يجب أن يكون بين 3 و 20 حرفاً، يحتوي على أحرف صغيرة وأرقام فقط، ولا يبدأ أو ينتهي بشرطة');
                    return;
                }
                
                document.querySelector('#step-1 .btn-submit').innerText = 'جاري التحقق...';
                const isSubUnique = await checkUnique('subdomain', 'subdomain', 'هذا الرابط مستخدم مسبقاً، يرجى اختيار رابط آخر');
                document.querySelector('#step-1 .btn-submit').innerText = 'التالي ⬅️';
                if (!isSubUnique) {
                    showModal('error', 'الرابط محجوز', 'الرابط المخصص الذي أدخلته محجوز لشركة أخرى. يرجى كتابة رابط مختلف.');
                    return;
                }
            } else if (step === 3) {
                if (!document.getElementById('owner_name').value || !document.getElementById('owner_phone').value || !document.getElementById('owner_email').value) {
                    showModal('error', 'نقص البيانات', 'يرجى إكمال بيانات التواصل الأساسية');
                    return;
                }
                
                document.querySelector('#step-2 .btn-submit:nth-child(2)').innerText = 'جاري التحقق...';
                const uPhone = await checkUnique('owner_phone', 'phone', 'رقم الهاتف هذا مسجل لدينا مسبقاً');
                const uPhone2 = await checkUnique('owner_phone2', 'phone', 'رقم الهاتف مسجل لدينا مسبقاً');
                const uEmail = await checkUnique('owner_email', 'email', 'البريد الإلكتروني مستخدم مسبقاً');
                document.querySelector('#step-2 .btn-submit:nth-child(2)').innerText = 'التالي ⬅️';
                
                let p2Val = document.getElementById('owner_phone2').value;
                if (!uPhone || (!uPhone2 && p2Val) || !uEmail) {
                    showModal('error', 'بيانات مكررة', 'بعض البيانات المدخلة (كالهاتف أو الإيميل) مسجلة مسبقاً باللون الأحمر. يرجى تصحيحها.');
                    return;
                }
            }
            
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step-dot').forEach(d => d.classList.remove('active'));
            
            document.getElementById('step-' + step).classList.add('active');
            for(let i=1; i<=step; i++) {
                if(document.getElementById('dot-' + i)) document.getElementById('dot-' + i).classList.add('active');
            }
            
            if (step === 4) {
                for (let i = 1; i <= 6; i++) {
                    const el = document.getElementById(`otp-${i}`);
                    if (el) el.value = '';
                }
                setTimeout(() => {
                    const firstInput = document.getElementById('otp-1');
                    if (firstInput) firstInput.focus();
                }, 100);
            }
        }

        function prevStep(step) {
            if (step === 3) {
                clearInterval(otpInterval);
            }
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step-dot').forEach(d => d.classList.remove('active'));
            
            document.getElementById('step-' + step).classList.add('active');
            for(let i=1; i<=step; i++) {
                if(document.getElementById('dot-' + i)) document.getElementById('dot-' + i).classList.add('active');
            }
        }

        async function sendOtpAndGoToStep4() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('confirm_password').value;
            if (!document.getElementById('username').value || !p1) {
                showModal('error', 'نقص البيانات', 'يرجى إدخال اسم المستخدم وكلمة المرور');
                return;
            }
            if(p1 !== p2) {
                showModal('error', 'خطأ', 'كلمة المرور غير متطابقة');
                return;
            }
            
            // Re-verify uniqueness quickly
            const uSub = await checkUnique('subdomain', 'subdomain', '');
            const uPhone = await checkUnique('owner_phone', 'phone', '');
            const uEmail = await checkUnique('owner_email', 'email', '');
            const uUser = await checkUnique('username', 'username', '');
            if(!uSub || !uPhone || !uEmail || !uUser) {
                showModal('error', 'خطأ', 'يرجى تصحيح الأخطاء (بيانات مكررة)');
                return;
            }
            
            // Show the channel selection modal
            document.getElementById('otp-channel-modal').classList.add('active');
        }

        function closeOtpChannelModal() {
            document.getElementById('otp-channel-modal').classList.remove('active');
        }

        async function requestOtpViaChannel(channel) {
            closeOtpChannelModal();
            
            const btn = document.querySelector('#step-3 .btn-submit:nth-child(2)');
            const originalText = btn.innerText;
            btn.innerText = 'جاري الإرسال...';
            btn.disabled = true;
            
            try {
                const res = await fetch('api_index.php?r=public&a=send-otp', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ 
                        email: document.getElementById('owner_email').value,
                        phone: document.getElementById('owner_phone').value,
                        channel: channel
                    })
                });
                const data = await res.json();
                if(data.success) {
                    const headerIcon = document.getElementById('otp-header-icon');
                    const headerText = document.getElementById('otp-header-text');
                    const otpDesc = document.getElementById('otp-description');
                    
                    if (channel === 'whatsapp') {
                        if (headerIcon) {
                            headerIcon.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="#10b981" style="display: block;">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.436 0 9.86-4.418 9.863-9.848.002-2.63-1.019-5.101-2.874-6.958C16.405 1.982 13.926.96 11.3.96 5.86.96 1.436 5.38 1.433 10.81c-.001 1.564.417 3.09 1.21 4.453l-.994 3.63 3.733-.979zm11.433-5.234c-.22-.11-1.306-.644-1.506-.719-.2-.075-.346-.112-.492.11-.146.22-.566.719-.694.869-.128.15-.256.169-.476.059-.22-.11-.93-.343-1.77-1.093-.655-.584-1.096-1.305-1.225-1.525-.128-.22-.014-.339.096-.449.1-.1.22-.256.33-.385.11-.128.146-.22.22-.366.075-.147.037-.275-.018-.385-.056-.11-.492-1.186-.674-1.62-.177-.428-.355-.37-.492-.377-.127-.007-.273-.008-.42-.008-.146 0-.384.055-.585.275-.2.22-.766.75-.766 1.83 0 1.078.783 2.119.892 2.269.11.15 1.542 2.355 3.736 3.3.522.226.93.36 1.246.46.524.167 1.002.143 1.379.087.42-.063 1.306-.534 1.489-1.05.183-.515.183-.956.128-1.05-.055-.094-.2-.15-.42-.26z"/>
                                </svg>`;
                        }
                        if (headerText) {
                            headerText.innerText = 'تأكيد حساب الواتساب';
                            headerText.parentElement.style.color = '#10b981';
                        }
                        if (otpDesc) {
                            otpDesc.innerText = `أرسلنا رمز تحقق (OTP) مكون من 6 أرقام إلى حساب الواتساب الخاص بك: ${document.getElementById('owner_phone').value}`;
                            otpDesc.style.color = '#10b981';
                        }
                    } else {
                        if (headerIcon) {
                            headerIcon.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#3b82f6" class="bi bi-envelope-fill" viewBox="0 0 16 16" style="display: block;">
                                    <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm5.436-.56L16 11.801V4.697z"/>
                                </svg>`;
                        }
                        if (headerText) {
                            headerText.innerText = 'تأكيد البريد الإلكتروني';
                            headerText.parentElement.style.color = '#38bdf8';
                        }
                        if (otpDesc) {
                            otpDesc.innerText = `أرسلنا رمز تحقق (OTP) مكون من 6 أرقام إلى بريدك الإلكتروني: ${document.getElementById('owner_email').value}`;
                            otpDesc.style.color = '#38bdf8';
                        }
                    }
                    
                    nextStep(4);
                    startOtpCountdown();
                    if(data.dev_otp) {
                        console.log("DEV OTP:", data.dev_otp);
                    }
                } else {
                    showModal('error', 'فشل الإرسال', data.error || 'حدث خطأ أثناء إرسال الرمز');
                }
            } catch (e) {
                showModal('error', 'خطأ في الاتصال', 'يرجى المحاولة مرة أخرى');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        // Setup OTP auto-tabbing and paste events
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.otp-digit');
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value && !/^[0-9]$/.test(e.target.value)) {
                        e.target.value = '';
                        return;
                    }
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });
                
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace') {
                        if (e.target.value === '' && index > 0) {
                            inputs[index - 1].focus();
                            inputs[index - 1].value = '';
                        } else {
                            e.target.value = '';
                        }
                    }
                });
                
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text').trim();
                    if (/^\d{6}$/.test(pasteData)) {
                        inputs.forEach((inp, i) => {
                            inp.value = pasteData[i];
                        });
                        inputs[5].focus();
                    }
                });
            });
        });

        const checkTimeouts = {};
        function debounceCheckUnique(fieldId, fieldType, errorMsg) {
            clearTimeout(checkTimeouts[fieldId]);
            checkTimeouts[fieldId] = setTimeout(() => {
                checkUnique(fieldId, fieldType, errorMsg);
            }, 450); // 450ms delay
        }

        async function checkUnique(fieldId, fieldType, errorMsg) {
            const el = document.getElementById(fieldId);
            
            // دائماً احذف رسالة الخطأ السابقة (إن وجدت) قبل البدء بفحص جديد
            const existingError = document.getElementById('error-' + fieldId);
            if (existingError) {
                existingError.remove();
            }

            if (!el || !el.value.trim()) {
                if (el) {
                    el.style.borderColor = '#334155';
                    el.style.backgroundColor = '';
                }
                return true;
            }
            
            // Local format checks for subdomain
            if (fieldId === 'subdomain') {
                const subVal = el.value.trim();
                const subReg = /^[a-z0-9][a-z0-9\-]{1,18}[a-z0-9]$/;
                if (!subReg.test(subVal)) {
                    el.style.borderColor = '#ef4444';
                    el.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
                    const err = document.createElement('div');
                    err.id = 'error-' + fieldId;
                    err.style.color = '#ef4444';
                    err.style.fontWeight = 'bold';
                    err.style.fontSize = '0.85rem';
                    err.style.marginTop = '5px';
                    err.style.textAlign = 'right';
                    err.innerText = 'الرابط المخصص يجب أن يكون بين 3 و 20 حرفاً، يحتوي على أحرف صغيرة وأرقام فقط، ولا يبدأ أو ينتهي بشرطة';
                    
                    el.parentElement.parentElement.appendChild(err);
                    return false;
                }
            }
            
            try {
                const res = await fetch(`api_index.php?r=public&a=check-unique&field=${fieldType}&value=${encodeURIComponent(el.value.trim())}`);
                const data = await res.json();
                
                // للحماية الإضافية، تأكد من إزالة أي رسالة خطأ ظهرت أثناء الانتظار
                const doubleCheckError = document.getElementById('error-' + fieldId);
                if (doubleCheckError) {
                    doubleCheckError.remove();
                }

                if (data.exists) {
                    el.style.borderColor = '#ef4444';
                    el.style.backgroundColor = 'rgba(239, 68, 68, 0.15)'; // خلفية حمراء داكنة شفافة تناسب الثيم المظلم
                    const err = document.createElement('div');
                    err.id = 'error-' + fieldId;
                    err.style.color = '#ef4444';
                    err.style.fontWeight = 'bold';
                    err.style.fontSize = '0.85rem';
                    err.style.marginTop = '5px';
                    err.style.textAlign = 'right';
                    err.innerText = errorMsg;
                    
                    if (fieldId === 'subdomain') {
                        el.parentElement.parentElement.appendChild(err);
                    } else {
                        el.parentElement.appendChild(err);
                    }
                    
                    return false;
                } else {
                    el.style.borderColor = '#22c55e';
                    el.style.backgroundColor = '';
                    return true;
                }
            } catch (e) {
                console.error("Uniqueness check failed", e);
                return true;
            }
        }

        function getOtpValue() {
            let code = '';
            for (let i = 1; i <= 6; i++) {
                const el = document.getElementById(`otp-${i}`);
                if (el) code += el.value.trim();
            }
            return code;
        }

        let otpInterval;
        function startOtpCountdown() {
            clearInterval(otpInterval);
            let duration = 600; // 10 minutes in seconds
            const timerDisplay = document.getElementById('otp-timer');
            
            function updateTimer() {
                let minutes = Math.floor(duration / 60);
                let seconds = duration % 60;
                
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                
                if (timerDisplay) {
                    timerDisplay.innerText = `${minutes}:${seconds}`;
                }
                
                if (duration <= 0) {
                    clearInterval(otpInterval);
                    if (timerDisplay) {
                        timerDisplay.innerText = 'منتهي الصلاحية ❌';
                    }
                    showModal('error', 'انتهت صلاحية الرمز', 'انتهت صلاحية رمز التحقق. يرجى العودة للخلف وإعادة إرسال رمز جديد.');
                }
                duration--;
            }
            
            updateTimer();
            otpInterval = setInterval(updateTimer, 1000);
        }

        async function handleRegister() {
            const company_name = document.getElementById('company_name').value;
            const subdomain = document.getElementById('subdomain').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const owner_name = document.getElementById('owner_name').value;
            const owner_phone = document.getElementById('owner_phone').value;
            const owner_email = document.getElementById('owner_email').value;
            const owner_phone2 = document.getElementById('owner_phone2') ? document.getElementById('owner_phone2').value : '';
            const hp_field = document.getElementById('hp_field') ? document.getElementById('hp_field').value : '';
            const otp = getOtpValue();

            if(otp.length < 6) {
                showModal('error', 'نقص البيانات', 'يرجى إدخال رمز التحقق كاملاً (6 أرقام)');
                return;
            }

            const btnSubmit = document.querySelector('#step-4 .btn-submit:nth-child(2)');
            btnSubmit.innerHTML = 'جاري الإنشاء... 🚀';
            btnSubmit.disabled = true;

            // قراءة الإشعار كـ Base64
            let receipt_base64 = null;
            const fileInput = document.getElementById('receipt_file');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                receipt_base64 = await new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => resolve(e.target.result);
                    reader.readAsDataURL(file);
                });
            }

            try {
                const res = await apiFetch('?r=auth&a=register', 'POST', {
                    company_name, subdomain, username, password, owner_name, owner_phone, owner_phone2, owner_email, plan_id: selectedPlanId, receipt_base64, hp_field, otp
                });

                if(res.success) {
                    if (selectedPlanId && res.tenant_id) {
                        localStorage.setItem('tenant_plan_' + res.tenant_id, selectedPlanId);
                    }
                    showModal('success', 'تهانينا! 🎉', res.message || 'تم إنشاء حساب شركتك بنجاح! سيتم تفعيل حسابك قريباً.', true, () => {
                        window.location.href = 'login.php';
                    });
                }
            } catch(e) {
                showModal('error', 'حدث خطأ', e.message);
                btnSubmit.innerHTML = 'تأكيد وإنشاء الحساب 🚀';
                btnSubmit.disabled = false;
            }
        }

        // Modal Logic
        function showModal(type, title, message, showWhatsapp = false, onAction = null) {
            const modal = document.getElementById('custom-modal');
            const icon = document.getElementById('modal-icon');
            const titleEl = document.getElementById('modal-title');
            const msgEl = document.getElementById('modal-message');
            const waBtn = document.getElementById('modal-whatsapp');
            const actionBtn = document.getElementById('modal-action-btn');

            if (type === 'success') {
                icon.innerHTML = '✨';
                icon.className = 'custom-modal-icon success';
            } else {
                icon.innerHTML = '⚠️';
                icon.className = 'custom-modal-icon error';
            }

            titleEl.innerText = title;
            msgEl.innerText = message;
            waBtn.style.display = showWhatsapp ? 'inline-flex' : 'none';

            window.modalActionCallback = onAction;
            if (onAction) {
                actionBtn.innerText = 'الانتقال لتسجيل الدخول';
            } else {
                actionBtn.innerText = 'حسناً';
            }

            modal.classList.add('active');
        }

        function closeModal() {
            const modal = document.getElementById('custom-modal');
            modal.classList.remove('active');
            // Restore default modal state when closed
            hideInquiryForm();
            if (window.modalActionCallback) {
                window.modalActionCallback();
            }
        }

        function showInquiryForm() {
            document.getElementById('modal-whatsapp').style.display = 'none';
            document.getElementById('modal-action-btn').style.display = 'none';
            document.getElementById('modal-message').style.display = 'none';
            document.getElementById('modal-inquiry-container').style.display = 'block';
            document.getElementById('inquiry-message').focus();
        }

        function hideInquiryForm() {
            const container = document.getElementById('modal-inquiry-container');
            if (container) {
                container.style.display = 'none';
                // Reset content inside in case it was a success message
                container.innerHTML = `
                    <label style="color: #fff; font-size: 0.95rem; font-weight: bold; display: block; margin-bottom: 8px; font-family: Tajawal;">اكتب استفسارك هنا للدعم الفني:</label>
                    <textarea id="inquiry-message" style="width: 100%; height: 90px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.3); color: #fff; padding: 12px; font-family: Tajawal; font-size: 0.9rem; resize: none; box-sizing: border-box; outline: none; transition: border 0.3s ease; line-height: 1.5;" placeholder="اكتب استفسارك بالتفصيل..."></textarea>
                    <div style="display: flex; gap: 8px; margin-top: 12px; direction: rtl;">
                        <button class="modal-action-btn" style="background:#334155; margin:0; flex: 1;" onclick="hideInquiryForm()">تراجع</button>
                        <button class="modal-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); margin:0; flex: 2; box-shadow: 0 4px 12px rgba(16,185,129,0.25);" onclick="submitInquiryForm()">إرسال الاستفسار 💬</button>
                    </div>
                `;
            }
            const waBtn = document.getElementById('modal-whatsapp');
            const actionBtn = document.getElementById('modal-action-btn');
            const msgEl = document.getElementById('modal-message');
            
            if (waBtn) waBtn.style.display = 'inline-flex';
            if (actionBtn) actionBtn.style.display = 'inline-block';
            if (msgEl) msgEl.style.display = 'block';
        }

        async function submitInquiryForm() {
            const msg = document.getElementById('inquiry-message').value.trim();
            if (!msg) {
                alert('يرجى كتابة نص الاستفسار أولاً');
                return;
            }

            const sendBtn = document.querySelector('#modal-inquiry-container button[onclick="submitInquiryForm()"]');
            const originalText = sendBtn ? sendBtn.innerText : 'إرسال الاستفسار 💬';
            if (sendBtn) {
                sendBtn.innerText = 'جاري الإرسال...';
                sendBtn.disabled = true;
            }

            const company_name = document.getElementById('company_name').value;
            const owner_name = document.getElementById('owner_name').value;
            const owner_phone = document.getElementById('owner_phone').value;

            try {
                const res = await fetch('api_index.php?r=public&a=send-inquiry', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        message: msg,
                        company_name: company_name,
                        owner_name: owner_name,
                        owner_phone: owner_phone
                    })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('modal-inquiry-container').innerHTML = `
                        <div style="text-align:center; color:#10b981; font-weight:bold; margin-top:15px; font-size:1rem; font-family: Tajawal;">
                            ✅ تم إرسال استفسارك بنجاح! وسيتواصل معك الدعم الفني قريباً.
                        </div>
                        <button class="modal-action-btn" style="margin-top:15px; width:100%;" onclick="closeModal()">حسناً</button>
                    `;
                } else {
                    alert(data.error || 'فشل إرسال الاستفسار');
                    if (sendBtn) {
                        sendBtn.innerText = originalText;
                        sendBtn.disabled = false;
                    }
                }
            } catch(e) {
                alert('حدث خطأ في الاتصال بالخادم');
                if (sendBtn) {
                    sendBtn.innerText = originalText;
                    sendBtn.disabled = false;
                }
            }
        }
    </script>
</body>
</html>
