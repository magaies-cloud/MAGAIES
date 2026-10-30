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
            const subdomain = (params.get('subdomain') || '').trim().toLowerCase();
            const tenantId = params.get('tenant_id') || '';
            const rawUser = params.get('user');
            
            if (!token) {
                alert('خطأ: لم يتم توفير رمز الدخول.');
                window.location.href = 'login.php';
                return;
            }
            
            // Clear any previous tenant cached data if switching companies
            const prevTenantId = localStorage.getItem('currentTenantId');
            if (tenantId && prevTenantId && String(prevTenantId) !== String(tenantId)) {
                const keysToKeep = ['saas_admin_token', 'device_uuid', 'theme', 'ui_lang'];
                const saved = {};
                keysToKeep.forEach(k => {
                    const v = localStorage.getItem(k);
                    if (v !== null) saved[k] = v;
                });
                localStorage.clear();
                Object.keys(saved).forEach(k => localStorage.setItem(k, saved[k]));
            }
            
            // Set auth token with the correct key name used by the system
            localStorage.setItem('authToken', token);
            
            if (rawUser) {
                try {
                    const parsedUser = JSON.parse(rawUser);
                    localStorage.setItem('user', JSON.stringify(parsedUser));
                    if (parsedUser.tenant_id) {
                        localStorage.setItem('currentTenantId', String(parsedUser.tenant_id));
                        document.cookie = 'currentTenantId=' + encodeURIComponent(String(parsedUser.tenant_id)) + '; path=/; max-age=86400; SameSite=Lax';
                    }
                    if (parsedUser.subdomain) {
                        localStorage.setItem('currentSubdomain', String(parsedUser.subdomain));
                        document.cookie = 'currentSubdomain=' + encodeURIComponent(String(parsedUser.subdomain)) + '; path=/; max-age=86400; SameSite=Lax';
                    }
                    if (parsedUser.company_name) {
                        localStorage.setItem('companyName', String(parsedUser.company_name));
                    }
                } catch (e) {}
            }
            if (tenantId) {
                localStorage.setItem('currentTenantId', String(tenantId));
                document.cookie = 'currentTenantId=' + encodeURIComponent(String(tenantId)) + '; path=/; max-age=86400; SameSite=Lax';
            }
            if (subdomain) {
                localStorage.setItem('currentSubdomain', subdomain);
                document.cookie = 'currentSubdomain=' + encodeURIComponent(subdomain) + '; path=/; max-age=86400; SameSite=Lax';
            }
            
            // Set session cookies expected by the system
            document.cookie = 'session_active=true; path=/; max-age=86400; SameSite=Lax';
            document.cookie = 'saas_token=' + token + '; path=/; max-age=86400; SameSite=Lax';
            sessionStorage.setItem('session_active', 'true');
            
            // Mark as impersonation session for easy identification
            sessionStorage.setItem('is_impersonation', 'true');
            sessionStorage.setItem('impersonation_subdomain', subdomain);
            
            // Redirect to dashboard after a brief delay
            setTimeout(function() {
                window.location.href = 'magaiees_unified_dashboard.php';
            }, 600);
        })();
    </script>
<script src="js/global_background_media.js?v=2026_seamless_v5" defer></script>
</body>
</html>
