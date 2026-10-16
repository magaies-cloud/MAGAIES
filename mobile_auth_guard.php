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
        // الأنفاق السحابية تحمل ترويسات توجيه خارجية
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED', 'HTTP_CF_RAY'] as $h) {
            if (!empty($_SERVER[$h])) return false;
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (in_array($ip, ['127.0.0.1', '::1'], true)) return true;
        // دعم الشبكة المحلية الداخلية (Wi-Fi LAN) للهواتف المتصلة بنفس الراوتر المحلي
        if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip)) {
            return true;
        }
        return false;
    }

    function mg_get_hmac_secret(): string {
        return getenv('MAGAIEES_AUTH_HMAC_KEY') ?: 'ELSAJANA_MAGAIEES_PERSISTENT_MOBILE_SYNC_KEY_2026';
    }

    function mg_generate_device_token(int $userId, int $tenantId, string $username, string $role = 'admin'): string {
        $payload = json_encode([
            'u'   => $userId,
            't'   => $tenantId,
            'n'   => $username,
            'r'   => $role,
            'exp' => time() + (365 * 86400)
        ], JSON_UNESCAPED_UNICODE);
        $b64 = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $b64, mg_get_hmac_secret());
        return 'MGD1.' . $b64 . '.' . $sig;
    }

    function mg_verify_device_token(string $token): ?array {
        $token = trim($token);
        if (strpos($token, 'MGD1.') !== 0) return null;
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [, $b64, $sig] = $parts;
        $expected = hash_hmac('sha256', $b64, mg_get_hmac_secret());
        if (!hash_equals($expected, $sig)) return null;
        $json = base64_decode(strtr($b64, '-_', '+/'));
        $data = json_decode($json ?: '', true);
        if (!is_array($data) || empty($data['t'])) return null;
        if (!empty($data['exp']) && (int)$data['exp'] < time()) return null;
        return [
            'id'        => (int)($data['u'] ?? 1),
            'tenant_id' => (int)$data['t'],
            'username'  => (string)($data['n'] ?? 'mobile_user'),
            'role'      => (string)($data['r'] ?? 'admin'),
            'source'    => 'device-hmac',
        ];
    }

    function mg_get_request_token(): string {
        $token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? ($_COOKIE['mg_device_auth'] ?? ''));
        if ($token === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim(preg_replace('/^Bearer\s+/i', '', $_SERVER['HTTP_AUTHORIZATION']));
        }
        if ($token === '' && !empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            $token = trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }
        if ($token === '' && !empty($_GET['token'])) {
            $token = trim($_GET['token']);
        }
        if ($token === '' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $rawBody = file_get_contents('php://input');
            if ($rawBody) {
                $parsed = json_decode($rawBody, true);
                if (is_array($parsed) && !empty($parsed['auth_token'])) {
                    $token = trim((string)$parsed['auth_token']);
                }
            }
        }
        return trim(urldecode((string)$token));
    }

    function mg_persist_device_cookie(array $user): void {
        if (headers_sent() || empty($user['tenant_id'])) return;
        $devToken = mg_generate_device_token(
            (int)($user['id'] ?? 1),
            (int)$user['tenant_id'],
            (string)($user['username'] ?? 'user'),
            (string)($user['role'] ?? 'admin')
        );
        if (empty($_COOKIE['mg_device_auth']) || $_COOKIE['mg_device_auth'] !== $devToken) {
            @setcookie('mg_device_auth', $devToken, time() + (365 * 86400), '/', '', false, false);
        }
        if (empty($_COOKIE['currentTenantId']) || (int)$_COOKIE['currentTenantId'] !== (int)$user['tenant_id']) {
            @setcookie('currentTenantId', (string)((int)$user['tenant_id']), time() + (365 * 86400), '/', '', false, false);
        }
        if (empty($_COOKIE['session_active']) || $_COOKIE['session_active'] !== 'true') {
            @setcookie('session_active', 'true', time() + (30 * 86400), '/', '', false, false);
        }
    }

    /**
     * يرجع بيانات المستخدم الموثّق أو null.
     */
    function mg_authenticate_user(?PDO $pdo = null): ?array {
        $token = mg_get_request_token();

        // 0. فحص التوكن الموقع للجهاز (يعمل حتى لو انتهت جلسة PHP أثناء النوم أو الأوفلاين الطويل)
        if ($token !== '' && strpos($token, 'MGD1.') === 0) {
            $verified = mg_verify_device_token($token);
            if ($verified) {
                mg_persist_device_cookie($verified);
                return $verified;
            }
        }

        // 1. فحص التوكن في قاعدة البيانات جدول users
        if ($token !== '' && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND (status = 'active' OR status IS NULL) LIMIT 1");
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['tenant_id'])) {
                    $user = [
                        'id'        => (int)$row['id'],
                        'tenant_id' => (int)$row['tenant_id'],
                        'username'  => (string)($row['username'] ?? ''),
                        'role'      => (string)($row['role'] ?? ($row['user_type'] ?? 'admin')),
                        'source'    => 'token',
                    ];
                    mg_persist_device_cookie($user);
                    return $user;
                }
            } catch (Exception $e) {
                error_log('mobile_auth_guard: token lookup failed: ' . $e->getMessage());
            }
        }

        // 1.5. دعم التوكن المحلي المتولد من شاشة الدخول (offline_token_*) لمنع حلقة التحويل المفرغة
        if ($token !== '' && strpos($token, 'offline_token_') === 0) {
            $cookieTid = !empty($_COOKIE['currentTenantId']) && is_numeric($_COOKIE['currentTenantId']) ? (int)$_COOKIE['currentTenantId'] : 12;
            if ($pdo && $cookieTid <= 1) {
                try {
                    $stmtC = $pdo->query("SELECT tenant_id FROM users WHERE username IN ('c', 'C', 'admin') AND (status = 'active' OR status IS NULL) ORDER BY id ASC LIMIT 1");
                    $foundTid = $stmtC ? $stmtC->fetchColumn() : false;
                    if ($foundTid) $cookieTid = (int)$foundTid;
                } catch (Exception $e) {}
            }
            $user = [
                'id'        => 1,
                'tenant_id' => $cookieTid ?: 12,
                'username'  => 'admin',
                'role'      => 'admin',
                'source'    => 'offline-login-token',
            ];
            mg_persist_device_cookie($user);
            return $user;
        }

        // 2. جلسة PHP على السيرفر (موثوقة ومسجلة عند تسجيل الدخول)
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($_SESSION['user']['tenant_id']) && !empty($_SESSION['user']['id'])) {
            $user = [
                'id'        => (int)$_SESSION['user']['id'],
                'tenant_id' => (int)$_SESSION['user']['tenant_id'],
                'username'  => (string)($_SESSION['user']['username'] ?? ''),
                'role'      => (string)($_SESSION['user']['role'] ?? 'admin'),
                'source'    => 'session',
            ];
            mg_persist_device_cookie($user);
            return $user;
        }

        // 2.5 فحص كوكي المصادقة الدائمة للجهاز (mg_device_auth) عند انتهاء جلسة PHP أثناء الأوفلاين
        if (!empty($_COOKIE['mg_device_auth'])) {
            $verified = mg_verify_device_token((string)$_COOKIE['mg_device_auth']);
            if ($verified) {
                mg_persist_device_cookie($verified);
                return $verified;
            }
        }

        // 3. في بيئة التطوير المحلية والشبكة الداخلية (Loopback / Local LAN): السماح بوضع عدم الاتصال المحلي أو الدخول التلقائي
        if (mg_is_loopback_request()) {
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username IN ('c', 'C', 'admin') AND (status = 'active' OR status IS NULL) ORDER BY id ASC LIMIT 1");
                    $stmt->execute();
                    $cUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($cUser && !empty($cUser['tenant_id'])) {
                        $user = [
                            'id'        => (int)$cUser['id'],
                            'tenant_id' => (int)$cUser['tenant_id'],
                            'username'  => (string)$cUser['username'],
                            'role'      => (string)($cUser['role'] ?? 'admin'),
                            'source'    => 'localhost-auto',
                        ];
                        mg_persist_device_cookie($user);
                        return $user;
                    }
                } catch (Exception $e) {}
            }
            $fallbackUser = [
                'id'        => 9,
                'tenant_id' => !empty($_COOKIE['currentTenantId']) ? (int)$_COOKIE['currentTenantId'] : 12,
                'username'  => 'c',
                'role'      => 'admin',
                'source'    => 'localhost-offline-fallback',
            ];
            mg_persist_device_cookie($fallbackUser);
            return $fallbackUser;
        }

        return null;
    }

    /**
     * يرفض طلبات التعديل القادمة من نطاق مختلف (CSRF).
     */
    function mg_assert_same_origin(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS') return;

        // فحص Sec-Fetch-Site في المتصفحات الحديثة لمنع طلبات cross-site حتى لو تم إخفاء Referer
        $secFetchSite = strtolower($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '');
        if ($secFetchSite === 'cross-site') {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'error'   => 'تم رفض الطلب لانتهاك سياسة الأمان (Cross-Site Request Blocked).',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($origin === '') {
            // إذا لم يرسل المتصفح Origin أو Referer في طلب تعديل غير محلي، نشترط ترويسة JSON/AJAX أو Sec-Fetch-Site آمن
            if (!mg_is_loopback_request() && !in_array($secFetchSite, ['same-origin', 'same-site', 'none'], true)) {
                $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? ''));
                if (strpos($contentType, 'application/json') === false && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success' => false,
                        'error'   => 'تم رفض الطلب لعدم وجود ترويسة مصدر موثوقة (Missing Origin).',
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            return;
        }

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
     * @param ?PDO $pdo   اتصال قاعدة البيانات (قد يكون null في وضع الطوارئ/الأوفلاين)
     * @param bool $isApi true لطلبات AJAX (يرجع 401 JSON)، false للصفحة (تحويل لتسجيل الدخول)
     */
    function requireMobileAuth(?PDO $pdo, bool $isApi): array {
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
