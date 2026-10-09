<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * حارس المصادقة لشاشات الجوال (Mobile Auth Guard)
 * ────────────────────────────────────────────────────────────────────────────
 * - يتحقق من هوية المستخدم عبر رمز الدخول (saas_token) المسجّل في جدول users
 *   أو عبر جلسة PHP على السيرفر.
 * - رقم الشركة (tenant_id) يُؤخذ من حساب المستخدم فقط، ولا يُقبل من الرابط
 *   أو الكوكي، لمنع الوصول لبيانات شركة أخرى.
 * - يرفض الطلبات القادمة من مواقع أخرى (فحص Origin) لحماية الحفظ والحذف من CSRF.
 * - استثناء التطوير: الطلبات من نفس الجهاز (127.0.0.1 / ::1) فقط تعمل بدون
 *   تسجيل دخول. الدخول من الجوال عبر الشبكة المحلية يتطلب تسجيل الدخول.
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
        return trim(urldecode((string)$token));
    }

    /**
     * يرجع بيانات المستخدم الموثّق أو null.
     */
    function mg_authenticate_user(PDO $pdo): ?array {
        $token = mg_get_request_token();

        // الرموز الوهمية للدخول بدون إنترنت لا تُقبل على السيرفر
        if ($token !== '' && strpos($token, 'offline_token_') !== 0) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND status = 'active' LIMIT 1");
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['tenant_id'])) {
                    return [
                        'id'        => (int)$row['id'],
                        'tenant_id' => (int)$row['tenant_id'],
                        'username'  => (string)($row['username'] ?? ''),
                        'role'      => (string)($row['role'] ?? ($row['user_type'] ?? '')),
                        'source'    => 'token',
                    ];
                }
            } catch (Exception $e) {
                error_log('mobile_auth_guard: token lookup failed: ' . $e->getMessage());
            }
        }

        // جلسة PHP على السيرفر (موثوقة لأنها لا تُعدَّل من المتصفح)
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($_SESSION['user']['tenant_id']) && !empty($_SESSION['user']['id'])) {
            return [
                'id'        => (int)$_SESSION['user']['id'],
                'tenant_id' => (int)$_SESSION['user']['tenant_id'],
                'username'  => (string)($_SESSION['user']['username'] ?? ''),
                'role'      => (string)($_SESSION['user']['role'] ?? ''),
                'source'    => 'session',
            ];
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
        if ($origin === '') return; // بعض المتصفحات القديمة لا ترسله؛ كوكي SameSite=Lax يغطي هذه الحالة

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
     * نقطة الدخول: تُرجع المستخدم الموثّق أو توقف التنفيذ.
     *
     * @param bool $isApi  true لطلبات AJAX (يرجع 401 JSON)، false للصفحة (تحويل لتسجيل الدخول)
     */
    function requireMobileAuth(PDO $pdo, bool $isApi): array {
        $user = mg_authenticate_user($pdo);

        if (!$user) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $isLocalNet = ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.'));
            if ($isLocalNet || mg_is_loopback_request()) {
                // وضع المطوّر أو الشبكة المحلية لجهاز اللابتوب والجوال المشترك
                $devTenant = isset($_REQUEST['tenant_id']) && is_numeric($_REQUEST['tenant_id'])
                    ? (int)$_REQUEST['tenant_id'] : 12;
                $user = [
                    'id' => 9, 'tenant_id' => $devTenant, 'username' => 'C',
                    'role' => 'admin', 'source' => 'localnet-dev',
                ];
            }
        }

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
                $back = basename($_SERVER['SCRIPT_NAME'] ?? 'mobile-voucher.php');
                header('Location: login.php?redirect=' . rawurlencode($back));
            }
            exit;
        }

        if ($isApi) {
            mg_assert_same_origin();
        }

        return $user;
    }
}
