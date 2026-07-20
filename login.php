<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - شاشة الدخول المدمجة ذرياً وعضوياً
// الشعار ليس مركباً بل هو جزء من جزيئات السائل والإنفجار اللوني للفيديو
// =============================================================

$login_success = false;
if (isset($_POST['submit_password'])) {
    $password = $_POST['password'];
    if ($password == "123") {
        $login_success = true;
        setcookie("session_active", "true", time() + 3600 * 24, "/");
    } else {
        echo "<script>alert('كلمة المرور غير صحيحة يا باشمهندس مهدي!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>بوابة مقاييس العالمية - Organic Splash 8K</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@900&family=Inter:wght@900&display=swap" rel="stylesheet">
    
    <style>
        html, body { 
            margin: 0; 
            padding: 0; 
            width: 100%; 
            height: 100%; 
            overflow: hidden; 
            background-color: #010204; 
            background-image: url('assets/user_bg_fast.jpg'); 
            background-size: cover; 
            background-position: center center; 
            background-repeat: no-repeat; 
            font-family: 'Cairo', sans-serif; 
        }
        
        /* الحاوية المركزية الفاخرة */
        .viewport-container { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            z-index: 9999; 
        }
        
        /* 🎥 فيديو السوائل الكريستالية الأصلي (المحرك الأساسي للضوء) */
        .liquid-stream { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            pointer-events: none; 
            filter: saturate(1.4) contrast(1.2) brightness(1.1); 
            z-index: 1; 
        }
        
        /* طبقة حماية إضافية مدمجة */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(3,5,8,0.1) 0%, rgba(2,4,6,0.65) 100%);
            z-index: 1;
            pointer-events: none;
        }

        /* 🔒 صندوق الباسورد الهولوغرافي الشفاف (المرحلة الأولى) */
        .glass-auth-box { 
            position: relative; 
            z-index: 10; 
            background: rgba(5, 8, 15, 0.45); 
            border: 1px solid rgba(0, 255, 204, 0.2); 
            padding: 40px; 
            border-radius: 28px; 
            width: 350px; 
            text-align: center; 
            box-shadow: 0 30px 60px rgba(0,0,0,0.8), inset 0 1px 1px rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(25px); 
            -webkit-backdrop-filter: blur(25px); 
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .glass-auth-box h2 { margin: 0 0 8px 0; font-size: 22px; color: #fff; font-weight: 900; letter-spacing: 1px; }
        .glass-auth-box p { margin: 0 0 25px 0; font-size: 11px; color: #38bdf8; opacity: 0.8; letter-spacing: 1px; }
        
        .input-wrapper { position: relative; margin-bottom: 20px; }
        .input-field { 
            width: 100%; 
            padding: 15px; 
            background: rgba(0, 0, 0, 0.6); 
            border: 1px solid rgba(56, 189, 248, 0.35); 
            border-radius: 14px; 
            color: #00ffcc; 
            font-size: 18px; 
            font-family: monospace; 
            text-align: center; 
            box-sizing: border-box; 
            outline: none; 
            transition: all 0.3s; 
        }
        .input-field:focus { border-color: #ff00ff; box-shadow: 0 0 20px rgba(255,0,255,0.4); }
        
        .action-btn { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(90deg, #00ffcc, #ff00ff); 
            color: #000; 
            border: none; 
            border-radius: 14px; 
            font-weight: 900; 
            font-size: 14px; 
            cursor: pointer; 
            letter-spacing: 1px; 
            transition: all 0.3s; 
            box-shadow: 0 4px 15px rgba(0, 255, 204, 0.35);
        }
        .action-btn:hover { filter: brightness(1.25); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(255, 0, 255, 0.4); }

        /* =============================================================
           👑 الهندسة البصرية: جعل الشعار يذوب وينبثق كجزء عضوي من الفيديو 
           ============================================================= */
        .organic-logo-wrap { position: absolute; z-index: 2; display: none; text-align: center; pointer-events: none; width: 100%; }
        
        /* [السحر البرمجي]: mix-blend-mode يدمج أحرف الشعار مع طاقة السوائل الملونة تحته تماماً */
        .organic-text { 
            font-size: 100px; 
            font-weight: 900; 
            font-family: 'Inter', 'Cairo', sans-serif; 
            margin: 0; 
            color: #ffffff;
            /* التدميج الملوكي: يجعل ألوان السائل المتفجر تضيء وتتحرك من داخل وبنية حروف كلمة مقاييس */
            mix-blend-mode: overlay; 
            filter: drop-shadow(0 0 30px rgba(0,255,204,0.5));
            letter-spacing: 6px;
            animation: fluidExpansion 4s forwards cubic-bezier(0.1, 1, 0.1, 1);
        }
        
        .organic-sub { 
            font-size: 16px; 
            font-weight: bold; 
            color: #ffffff; 
            mix-blend-mode: color-dodge; 
            letter-spacing: 12px; 
            margin-top: 15px; 
            text-transform: uppercase; 
            opacity: 0.8; 
            animation: textGlowPulse 2s infinite ease-in-out; 
        }

        /* حركات التداخل الذري وتمدد السائل */
        @keyframes fluidExpansion {
            0% { transform: scale(0.6) rotate(-3deg); filter: blur(20px) contrast(2); opacity: 0; letter-spacing: -10px; }
            40% { transform: scale(1.05) rotate(1deg); filter: blur(0px) contrast(1.2); opacity: 1; letter-spacing: 10px; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; letter-spacing: 6px; }
        }
        @keyframes textGlowPulse { 0%, 100% { opacity: 0.6; filter: brightness(1); } 50% { opacity: 0.9; filter: brightness(1.4); } }
        
        .hide-box { opacity: 0; transform: scale(0.8) rotate(2deg); pointer-events: none; }
        .fade-out-all { opacity: 0; transition: opacity 1.2s ease; }
    </style>
</head>
<body>

    <div class="viewport-container" id="viewport">
        
        <!-- Live Video stream -->
        <video autoplay loop muted playsinline class="liquid-stream">
            <source src="271262.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>

        <div class="glass-auth-box" id="authBox">
            <h2>نظام مقاييس المحاسبي</h2>
            <p>MAGAIEES ERP — الجيل الذكي الموحد</p>
            
            <form method="POST" action="" onsubmit="executeProfessionalTransition(event)">
                <div class="input-wrapper">
                    <input type="password" name="password" id="securePass" class="input-field" placeholder="أدخل كلمة المرور" required autocomplete="off">
                </div>
                <button type="submit" name="submit_password" class="action-btn">موافق وتأثير الـ 8K</button>
            </form>
        </div>

        <div class="organic-logo-wrap" id="organicLogo">
            <h1 class="organic-text">مقاييس ERP</h1>
            <div class="organic-sub">MANAGEMENT PLATFORM</div>
        </div>
        
    </div>

    <script>
        function executeProfessionalTransition(event) {
            event.preventDefault();
            const pass = document.getElementById('securePass').value;
            
            if (pass === "123") {
                // حفظ التوكن وبيانات المستخدم في localStorage و cookies
                localStorage.setItem('authToken', 'token_123');
                localStorage.setItem('user', JSON.stringify({ username: 'admin', fullName: 'الباشمهندس' }));
                document.cookie = "session_active=true; path=/";
                sessionStorage.setItem('session_active', 'true');

                // 1. إخفاء صندوق الدخول بحركة تلاشي انسيابية دائرية
                document.getElementById('authBox').classList.add('hide-box');
                
                // 2. تفعيل انبثاق الشعار العضوي المدمج
                setTimeout(() => {
                    document.getElementById('authBox').style.display = 'none';
                    const logoWrap = document.getElementById('organicLogo');
                    logoWrap.style.display = 'block';
                    
                    // 3. استعراض الشعار وهو يمتص ألوان ودورانات الفيديو لمدة 4 ثوانٍ، ثم الانتقال للوحة التحكم
                    setTimeout(() => {
                        document.getElementById('viewport').classList.add('fade-out-all');
                        setTimeout(() => {
                            window.location.href = 'dashboard-pro.php';
                        }, 1200);
                    }, 4000);
                }, 600); 
            } else {
                alert('كلمة المرور غير صحيحة يا باشمهندس مهدي!');
            }
        }
    </script>

    <?php if ($login_success): ?>
    <script>
        // معالجة دخول PHP الاحتياطي في حال إرسال الفورم برمجياً
        localStorage.setItem('authToken', 'token_123');
        localStorage.setItem('user', JSON.stringify({ username: 'admin', fullName: 'الباشمهندس' }));
        sessionStorage.setItem('session_active', 'true');
        
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('authBox').classList.add('hide-box');
            setTimeout(() => {
                document.getElementById('authBox').style.display = 'none';
                const logoWrap = document.getElementById('organicLogo');
                logoWrap.style.display = 'block';
                setTimeout(() => {
                    document.getElementById('viewport').classList.add('fade-out-all');
                    setTimeout(() => {
                        window.location.href = 'dashboard-pro.php';
                    }, 1200);
                }, 4000);
            }, 600);
        });
    </script>
    <?php endif; ?>

</body>
</html>
