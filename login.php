<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - شاشة الدخول الموحدة بالخلفية الحية
// المرحلة 1: فيديو متحرك خلف الباسورد | المرحلة 2: انبثاق الشعار 3D من العمق
// =============================================================

$login_success = false;
if (isset($_POST['submit_password'])) {
    $password = $_POST['password'];
    if ($password == "123") {
        $login_success = true;
        setcookie("session_active", "true", time() + 3600 * 24, "/");
    } else {
        echo "<script>alert('كلمة المرور غير صحيحة، حاول مجدداً يا باشمهندس');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>تسجيل الدخول - MAGAIEES ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Inter:wght@900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        html, body { 
            margin: 0; 
            padding: 0; 
            width: 100%; 
            height: 100%; 
            overflow: hidden; 
            background-color: #0c101b; 
            background-image: url('assets/user_bg_fast.jpg'); 
            background-size: cover; 
            background-position: center center; 
            background-repeat: no-repeat; 
            font-family: 'Cairo', sans-serif; 
        }
        
        /* 🎥 محرك تشغيل الفيديو الأصلي كخلفية حية ممتدة */
        .bg-video { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            pointer-events: none; 
            filter: brightness(0.85) contrast(1.1); 
            z-index: 1; 
        }
        
        /* طبقة الحماية الداكنة لمنع تشتت العين وتوضيح خانة الكتابة */
        .overlay { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: radial-gradient(circle, rgba(3,5,8,0.2) 0%, rgba(2,4,6,0.85) 100%); 
            z-index: 2; 
            pointer-events: none; 
        }
        
        /* 🔒 صندوق الباسورد (المرحلة الأولى) */
        .login-box { 
            position: relative; 
            z-index: 10; 
            background: rgba(11, 15, 25, 0.7); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.12); 
            padding: 40px; 
            border-radius: 24px; 
            width: 360px; 
            text-align: center; 
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), inset 0 1px 1px rgba(255, 255, 255, 0.15); 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        .login-box h2 { margin: 0 0 10px 0; font-size: 22px; color: #fff; font-weight: 900; }
        .login-box p { margin: 0 0 25px 0; font-size: 12px; color: #94a3b8; font-weight: bold; }
        
        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i { position: absolute; right: 15px; top: 16px; color: #38bdf8; font-size: 1.1rem; }
        .input-style { 
            width: 100%; 
            padding: 14px 45px 14px 15px; 
            background: rgba(0, 0, 0, 0.5); 
            border: 1px solid rgba(56, 189, 248, 0.3); 
            border-radius: 12px; 
            color: #fff; 
            font-size: 18px; 
            font-family: monospace; 
            text-align: center; 
            box-sizing: border-box; 
            outline: none; 
            transition: all 0.3s; 
        }
        .input-style:focus { border-color: #00ffcc; box-shadow: 0 0 15px rgba(0,255,204,0.4); }
        
        .btn-submit { 
            width: 100%; 
            padding: 14px; 
            background: linear-gradient(90deg, #f59e0b, #10b981); 
            color: #020617; 
            border: none; 
            border-radius: 12px; 
            font-weight: 900; 
            font-size: 15px; 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        .btn-submit:hover { transform: scale(1.02); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }

        /* 👑 كتلة الشعار ثلاثي الأبعاد (المرحلة الثانية) */
        #splash-container { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            z-index: 9999; 
            background: transparent; 
            display: none; 
        }
        
        .neon-ring { 
            position: absolute; 
            width: 420px; 
            height: 420px; 
            border-radius: 50%; 
            border: 4px solid transparent; 
            border-top: 4px solid #00ffcc; 
            border-bottom: 4px solid #ff00ff; 
            border-left: 4px solid #fbbf24; 
            animation: spin 2s linear infinite; 
            filter: drop-shadow(0 0 30px rgba(0,255,204,0.8)); 
        }
        
        .logo-3d { 
            text-align: center; 
            animation: emerge 4s forwards cubic-bezier(0.1, 1, 0.1, 1); 
            transform-style: preserve-3d; 
            perspective: 1000px; 
        }
        .logo-text { 
            font-size: 85px; 
            font-weight: 900; 
            font-family: 'Inter', 'Cairo', sans-serif; 
            margin: 0; 
            background: linear-gradient(135deg, #ffffff 0%, #00ffcc 50%, #ff00ff 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.8)) drop-shadow(0 0 50px rgba(255, 0, 255, 0.6)); 
            letter-spacing: 4px; 
        }
        .logo-sub { 
            font-size: 15px; 
            font-weight: bold; 
            color: #38bdf8; 
            letter-spacing: 8px; 
            margin-top: 15px; 
            text-transform: uppercase; 
            text-shadow: 0 2px 10px rgba(56, 189, 248, 0.5);
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes emerge {
            0% { transform: scale(0.2) translateZ(-600px); filter: blur(12px) brightness(0); opacity: 0; }
            30% { transform: scale(1.1) translateZ(120px); filter: blur(0px) brightness(1.6); opacity: 1; }
            45% { transform: scale(1) translateZ(0); }
            100% { transform: scale(1.04) translateZ(30px); }
        }
        
        .fade-out { opacity: 0; visibility: hidden; transition: opacity 1s ease, visibility 1s ease; }
        .hidden-box { opacity: 0; transform: scale(0.8); pointer-events: none; }
    </style>
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh;">

    <!-- Live Background Video -->
    <video autoplay loop muted playsinline class="bg-video">
        <source src="271262.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <div class="login-box" id="loginBox">
        <h2>نظام مقاييس المحاسبي</h2>
        <p>MAGAIEES ERP — بوابة الدخول الآمنة</p>
        
        <form method="POST" action="" onsubmit="return handleLogin(event)">
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="passInput" class="input-style" placeholder="أدخل كلمة المرور السرية" required autocomplete="off">
            </div>
            <button type="submit" name="submit_password" class="btn-submit">موافق وتأثير الدخول</button>
        </form>
    </div>

    <div id="splash-container">
        <div class="neon-ring"></div>
        <div class="logo-3d">
            <h1 class="logo-text">مقاييس ERP</h1>
            <div class="logo-sub">غرفة القيادة المركزية العالمية</div>
        </div>
    </div>

    <script>
        // دالة التحكم الجمالي والانتقال بين المرحلتين
        function handleLogin(e) {
            const passInput = document.getElementById('passInput').value;
            
            if (passInput === "123") {
                e.preventDefault(); // إيقاف التحديث اللحظي للمتصفح لتشغيل السينمائيات أولاً
                
                // حفظ التوكن وبيانات المستخدم في localStorage و cookies
                localStorage.setItem('authToken', 'token_123');
                localStorage.setItem('user', JSON.stringify({ username: 'admin', fullName: 'الباشمهندس' }));
                document.cookie = "session_active=true; path=/";
                sessionStorage.setItem('session_active', 'true');

                // 1. إخفاء صندوق الباسورد بسلاسة
                document.getElementById('loginBox').classList.add('hidden-box');
                
                // 2. إظهار شاشة انبثاق شعار مقاييس الفخم من عمق نفس الفيديو
                setTimeout(() => {
                    const splash = document.getElementById('splash-container');
                    splash.style.display = 'flex';
                    
                    // 3. مهلة 4 ثوانٍ للشعار وهو مشع وبارز، ثم الانتقال لصفحة الواجهة الموحدة
                    setTimeout(() => {
                        splash.classList.add('fade-out');
                        setTimeout(() => {
                            // التوجيه التلقائي لغرفة القيادة الموحدة
                            window.location.href = 'dashboard-pro.php';
                        }, 1000);
                    }, 4000);
                }, 1000); // تقليل مهلة التجهيز لتكون أسرع وأكثر تفاعلية (1 ثانية بدلاً من 4 ثوانٍ فراغ)
                
                return false;
            } else {
                e.preventDefault();
                alert('كلمة المرور غير صحيحة، حاول مجدداً يا باشمهندس');
                return false;
            }
        }
    </script>

    <?php if ($login_success): ?>
    <script>
        // معالجة دخول PHP المباشر الاحتياطي
        localStorage.setItem('authToken', 'token_123');
        localStorage.setItem('user', JSON.stringify({ username: 'admin', fullName: 'الباشمهندس' }));
        sessionStorage.setItem('session_active', 'true');
        
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('loginBox').classList.add('hidden-box');
            const splash = document.getElementById('splash-container');
            splash.style.display = 'flex';
            setTimeout(() => {
                splash.classList.add('fade-out');
                setTimeout(() => {
                    window.location.href = 'dashboard-pro.php';
                }, 1000);
            }, 4000);
        });
    </script>
    <?php endif; ?>

</body>
</html>
