<?php
/**
 * حارس الهوية المشترك لواجهات API المالية
 * يحدد المستخدم الموثّق من رمز الدخول (saas_token / authToken / Bearer) أو جلسة PHP.
 * لا يعتمد أبداً على tenant_id القادم من الرابط أو من كوكي يكتبه المتصفح.
 */

if (!function_exists('mvResolveAuthUser')) {
    function mvResolveAuthUser($pdo) {
        $token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? '');
        if ($token === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim(preg_replace('/^Bearer\s+/i', '', $_SERVER['HTTP_AUTHORIZATION']));
        }
        if ($token !== '') {
            try {
                $st = $pdo->prepare("SELECT id, tenant_id, username, role FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND (status = 'active' OR status IS NULL) LIMIT 1");
                $st->execute([$token]);
                $u = $st->fetch(PDO::FETCH_ASSOC);
                if ($u && !empty($u['tenant_id'])) {
                    return $u;
                }
            } catch (Exception $e) {}
        }
        if (session_status() === PHP_SESSION_NONE) { @session_start(); }
        if (!empty($_SESSION['user']['tenant_id'])) {
            return [
                'id' => $_SESSION['user']['id'] ?? null,
                'tenant_id' => (int)$_SESSION['user']['tenant_id'],
                'username' => $_SESSION['user']['username'] ?? '',
                'role' => $_SESSION['user']['role'] ?? ''
            ];
        }
        return null;
    }
}

if (!function_exists('mvIsLoopbackRequest')) {
    // تجاوز للمطور فقط عند الوصول من نفس الجهاز — لا ينطبق على السيرفر الحي
    function mvIsLoopbackRequest() {
        return php_sapi_name() === 'cli'
            || in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    }
}

if (!function_exists('mvDenyUnauthenticatedApi')) {
    function mvDenyUnauthenticatedApi() {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'auth_required' => true,
            'error' => 'انتهت جلسة الدخول أو لم يتم تسجيل الدخول. يرجى تسجيل الدخول مرة أخرى.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
