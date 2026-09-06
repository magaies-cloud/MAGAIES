<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جاري تسجيل الدخول...</title>
    <style>
        body { margin:0; display:flex; justify-content:center; align-items:center; min-height:100vh; background:#050505; color:#fff; font-family:'Tajawal',sans-serif; }
        .loader { text-align:center; }
        .spinner { width:50px; height:50px; border:4px solid rgba(255,255,255,0.1); border-top-color:#f59e0b; border-radius:50%; animation:spin 0.8s linear infinite; margin:0 auto 20px; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <h2>🔐 جاري تسجيل الدخول...</h2>
        <p style="color:#a1a1aa;">يتم تحويلك تلقائياً إلى لوحة التحكم</p>
    </div>
    <script>
        (function() {
            const params = new URLSearchParams(window.location.search);
            const token = params.get('token');
            const subdomain = params.get('subdomain') || '';
            
            if (!token) {
                alert('خطأ: لم يتم توفير رمز الدخول.');
                window.location.href = 'login.php';
                return;
            }
            
            // Set auth token with the correct key name used by the system
            localStorage.setItem('authToken', token);
            
            // Set session cookies expected by the system
            document.cookie = 'session_active=true; path=/; SameSite=Lax';
            document.cookie = 'saas_token=' + token + '; path=/; SameSite=Lax';
            sessionStorage.setItem('session_active', 'true');
            
            // Mark as impersonation session for easy identification
            sessionStorage.setItem('is_impersonation', 'true');
            sessionStorage.setItem('impersonation_subdomain', subdomain);
            
            // Redirect to dashboard after a brief delay
            setTimeout(function() {
                window.location.href = 'magaiees_unified_dashboard.php';
            }, 800);
        })();
    </script>
<script src="js/global_background_media.js?v=2026_seamless_v3" defer></script>
</body>
</html>
