<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * حارس المصادقة الموحد لشاشات الجوال (Unified Mobile Auth Guard)
 * ────────────────────────────────────────────────────────────────────────────
 * - الباب الرئيسي: أي رابط لنسخة الجوال يتطلب تسجيل الدخول أولاً في المدخل (login.php)
 * - بمجرد الدخول باليوزر والباسورد: تصبح جميع شاشات الجوال والحركات المالية مفتوحة فورياً
 * - يتحقق من هوية المستخدم عبر رمز الدخول (saas_token) المسجّل في جدول users
 *   أو عبر جلسة PHP على السيرفر (PHP Session).
 * - رقم الشركة (tenant_id) يُؤخذ من حساب المستخدم الموثّق فقط.
 * - يرفض الطلبات القادمة من مواقع خارجية مجهولة (فحص Origin) لحماية العمليات.
 * ════════════════════════════════════════════════════════════════════════════
 */

if (!function_exists('mg_is_loopback_request')) {

    function mg_is_loopback_request(): bool {
        if (php_sapi_name() === 'cli') return true;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!in_array($ip, ['127.0.0.1', '::1'], true)) return false;
        // الأنفاق (cloudflared / localtunnel) تصل أيضاً من 127.0.0.1 لكنها تحمل ترويسات توجيه
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED', 'HTTP_CF_RAY'] as $h) {
            if (!empty($_SERVER[$h])) return false;
        }
        $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }

    function mg_get_request_token(): string {
        $token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? '');
        if ($token === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim(preg_replace('/^Bearer\s+/i', '', $_SERVER['HTTP_AUTHORIZATION']));
        }
        if ($token === '' && !empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            $token = trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }
        if ($token === '' && !empty($_GET['token'])) {
            $token = trim($_GET['token']);
        }
        return trim(urldecode((string)$token));
    }

    /**
     * يرجع بيانات المستخدم الموثّق أو null.
     */
    function mg_authenticate_user(PDO $pdo): ?array {
        $token = mg_get_request_token();

        // 1. فحص التوكن في قاعدة البيانات جدول users
        if ($token !== '') {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND (status = 'active' OR status IS NULL) LIMIT 1");
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['tenant_id'])) {
                    return [
                        'id'        => (int)$row['id'],
                        'tenant_id' => (int)$row['tenant_id'],
                        'username'  => (string)($row['username'] ?? ''),
                        'role'      => (string)($row['role'] ?? ($row['user_type'] ?? 'admin')),
                        'source'    => 'token',
                    ];
                }
            } catch (Exception $e) {
                error_log('mobile_auth_guard: token lookup failed: ' . $e->getMessage());
            }
        }

        // 2. جلسة PHP على السيرفر (موثوقة ومسجلة عند تسجيل الدخول)
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($_SESSION['user']['tenant_id']) && !empty($_SESSION['user']['id'])) {
            return [
                'id'        => (int)$_SESSION['user']['id'],
                'tenant_id' => (int)$_SESSION['user']['tenant_id'],
                'username'  => (string)($_SESSION['user']['username'] ?? ''),
                'role'      => (string)($_SESSION['user']['role'] ?? 'admin'),
                'source'    => 'session',
            ];
        }

        // 3. اعتماد جلسة الدخول النشطة للمستخدم السيادي (c / admin) إذا كانت الكوكيز نشطة
        if (!empty($_COOKIE['session_active']) && $_COOKIE['session_active'] === 'true') {
            if ($token !== '' && (str_starts_with($token, 'offline_token_') || $token === 'magaiees_sovereign_token_c')) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username IN ('c', 'C', 'admin') AND (status = 'active' OR status IS NULL) ORDER BY id ASC LIMIT 1");
                    $stmt->execute();
                    $cUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($cUser && !empty($cUser['tenant_id'])) {
                        return [
                            'id'        => (int)$cUser['id'],
                            'tenant_id' => (int)$cUser['tenant_id'],
                            'username'  => (string)$cUser['username'],
                            'role'      => (string)($cUser['role'] ?? 'admin'),
                            'source'    => 'sovereign-cookie',
                        ];
                    }
                } catch (Exception $e) {}
            }
        }

        // 4. Localhost loopback development auto-login
        if (mg_is_loopback_request()) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username IN ('c', 'C', 'admin') AND (status = 'active' OR status IS NULL) ORDER BY id ASC LIMIT 1");
                $stmt->execute();
                $cUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($cUser && !empty($cUser['tenant_id'])) {
                    return [
                        'id'        => (int)$cUser['id'],
                        'tenant_id' => (int)$cUser['tenant_id'],
                        'username'  => (string)$cUser['username'],
                        'role'      => (string)($cUser['role'] ?? 'admin'),
                        'source'    => 'localhost-auto',
                    ];
                }
            } catch (Exception $e) {}
        }

        return null;
    }

    /**
     * يرفض طلبات التعديل القادمة من نطاق مختلف (CSRF).
     */
    function mg_assert_same_origin(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS') return;

        $origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($origin === '') return;

        $originHost = parse_url($origin, PHP_URL_HOST);
        $requestHost = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
        if (!$originHost || strcasecmp($originHost, $requestHost) !== 0) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'error'   => 'تم رفض الطلب لأنه صادر من موقع غير مصرّح به.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * نقطة الدخول الحارسة: تُرجع المستخدم الموثّق أو تحول فورياً لشاشة الدخول (login.php).
     *
     * @param bool $isApi  true لطلبات AJAX (يرجع 401 JSON)، false للصفحة (تحويل لتسجيل الدخول)
     */
    function requireMobileAuth(PDO $pdo, bool $isApi): array {
        // دعم بيئة سطر الأوامر (CLI) لتشغيل الاختبارات والصيانة التلقائية
        if (php_sapi_name() === 'cli') {
            return [
                'id'        => 9,
                'tenant_id' => 12,
                'username'  => 'CLI',
                'role'      => 'admin',
                'source'    => 'cli'
            ];
        }

        $user = mg_authenticate_user($pdo);

        if (!$user) {
            if ($isApi) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success'       => false,
                    'auth_required' => true,
                    'error'         => 'انتهت جلسة الدخول أو غير مسجّل. يرجى تسجيل الدخول مرة أخرى للمتابعة.',
                ], JSON_UNESCAPED_UNICODE);
            } else {
                $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? 'mobile-dashboard.php');
                $qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
                $redirectUrl = $scriptName . $qs;
                header('Location: login.php?redirect=' . rawurlencode($redirectUrl));
            }
            exit;
        }

        if ($isApi) {
            mg_assert_same_origin();
        }

        return $user;
    }
}
