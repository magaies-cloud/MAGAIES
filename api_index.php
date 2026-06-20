<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . "/api/db.php";

// -------------------------------------------------------
// Utility: Send JSON response and exit
// -------------------------------------------------------
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// -------------------------------------------------------
// Parse URL segments
// -------------------------------------------------------
$requestUri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$base_index = array_search('api_index.php', $requestUri);
if ($base_index !== false) {
    $resource = isset($requestUri[$base_index + 1]) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $requestUri[$base_index + 1]) : null;
    $id       = isset($requestUri[$base_index + 2]) ? $requestUri[$base_index + 2] : null;
    $action   = isset($requestUri[$base_index + 2]) ? $requestUri[$base_index + 2] : null;
} else {
    // Direct rewrite support
    $resource = isset($requestUri[0]) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $requestUri[0]) : null;
    $id       = isset($requestUri[1]) ? $requestUri[1] : null;
    $action   = isset($requestUri[1]) ? $requestUri[1] : null;
}

// Fallback: support query param routing (?r=resource&a=action&a2=sub-action)
// This is used when the server doesn't pass PATH_INFO segments after .php
if (empty($resource) && isset($_GET['r'])) {
    $resource = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['r'] ?? '');
    $action   = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['a'] ?? '');
    // Rebuild requestUri so sub-action checks like $requestUri[$base_index+3] work
    $base_index = 0;
    $requestUri = ['api_index.php', $resource, $action];
    if (isset($_GET['a2'])) {
        $requestUri[] = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['a2']);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents("php://input");
$input  = json_decode($rawInput, true);

// -------------------------------------------------------
// Utility: Generate secure token
// -------------------------------------------------------
function generateToken() {
    return bin2hex(random_bytes(32));
}

// ---------------------------------------------------------
// PUBLIC ROUTES (No token required)
// ---------------------------------------------------------

if ($resource === 'public' && $method === 'GET') {
    $data = ['plans' => [], 'partners' => [], 'ads' => []];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('saas_plans', 'saas_partners', 'saas_ads')");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if ($row['setting_key'] === 'saas_plans') $data['plans'] = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'saas_partners') $data['partners'] = json_decode($row['setting_value'], true);
            if ($row['setting_key'] === 'saas_ads') $data['ads'] = json_decode($row['setting_value'], true);
        }
    } catch (Exception $e) {}
    
    // Add default plans if empty
    if (empty($data['plans'])) {
        $data['plans'] = [
            ['id' => 1, 'name' => 'باقة الأساسية', 'price' => 50, 'maxUsers' => 5, 'storage' => 10],
            ['id' => 2, 'name' => 'باقة المتقدمة', 'price' => 120, 'maxUsers' => 20, 'storage' => 50],
            ['id' => 3, 'name' => 'باقة الشركات (VIP)', 'price' => 300, 'maxUsers' => 100, 'storage' => 200]
        ];
    }
    // Add default partners if empty
    if (empty($data['partners'])) {
        $data['partners'] = [
            ['id' => 1, 'name' => 'مقاييس', 'logo' => 'https://ui-avatars.com/api/?name=%D9%85%D9%82%D8%A7%D9%8A%D9%8A%D8%B3&background=38bdf8&color=fff&size=100', 'link' => '#'],
            ['id' => 2, 'name' => 'مصنع الأسعد', 'logo' => 'https://ui-avatars.com/api/?name=%D9%85%D8%B5%D9%86%D8%B9+%D8%A7%D9%84%D8%A3%D8%B3%D8%B9%D8%AF&background=818cf8&color=fff&size=100', 'link' => '#'],
            ['id' => 3, 'name' => 'أوميغا', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A3%D9%88%D9%85%D9%8A%D8%BA%D8%A7&background=10b981&color=fff&size=100', 'link' => '#'],
            ['id' => 4, 'name' => 'بنك الخرطوم', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D8%AE%D8%B1%D8%B7%D9%88%D9%85&background=f59e0b&color=fff&size=100', 'link' => '#'],
            ['id' => 5, 'name' => 'بنك المزارع', 'logo' => 'https://ui-avatars.com/api/?name=%D8%A8%D9%86%D9%83+%D8%A7%D9%84%D9%85%D8%B2%D8%A7%D8%B1%D8%B9&background=ef4444&color=fff&size=100', 'link' => '#']
        ];
    }
    jsonResponse($data);
}

// TEMP DEBUG: ?r=debug&token=YOUR_TOKEN
if ($resource === 'debug' && $method === 'GET') {
    $reqToken = $_GET['token'] ?? '';
    $saasDbgSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : (getenv('SAAS_ADMIN_TOKEN') ?: 'SUPER_ADMIN_SECRET_2026');
    if ($reqToken !== $saasDbgSecret) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
    $dbg = [
        'db_connected' => false,
        'tables' => [],
        'tenants_count' => 0,
        'tenants_sample' => [],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'path_info' => $_SERVER['PATH_INFO'] ?? 'N/A',
        'env_db_host' => getenv('DB_HOST') ?: 'NOT SET',
        'env_db_name' => getenv('DB_NAME') ?: 'NOT SET',
        'token_match' => ($reqToken === $saasDbgSecret) ? 'YES' : 'NO',
    ];
    try {
        $dbg['db_connected'] = true;
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $dbg['tables'] = $tables;
        if (in_array('tenants', $tables)) {
            $dbg['tenants_count'] = (int)$pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
            $dbg['tenants_sample'] = $pdo->query("SELECT id, company_name, subdomain, owner_name, status, valid_until FROM tenants LIMIT 5")->fetchAll();
        } else {
            $dbg['tenants_table_exists'] = false;
        }
    } catch (Exception $e) {
        $dbg['db_error'] = $e->getMessage();
    }
    jsonResponse($dbg);
}

// Public: Get exchange rate (no auth needed so it always loads on the page)
if ($resource === 'exchange-rate' && $method === 'GET') {
    $ex_rate = ['rate' => 1.0, 'visible' => false];
    try {
        $stmt_ex = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
        $val = $stmt_ex->fetchColumn();
        if ($val) $ex_rate = json_decode($val, true);
    } catch(Exception $e) {}
    jsonResponse($ex_rate);
}

// Public: Check Username Uniqueness Globally
if ($resource === 'public' && $action === 'check-username' && $method === 'GET') {
    $username = $_GET['username'] ?? '';
    if (empty($username)) {
        jsonResponse(['success' => false, 'error' => 'Username required'], 400);
    }
    $query = "SELECT COUNT(*) FROM users WHERE username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    jsonResponse(['success' => true, 'exists' => ($count > 0)]);
}

// ---------------------------------------------------------
// AUTHENTICATION ROUTES (No token required)
// ---------------------------------------------------------
if ($resource === 'auth') {

    // --- REGISTER ---
    if ($action === 'register' && $method === 'POST') {
        $company_name = trim($input['company_name'] ?? '');
        $subdomain    = trim($input['subdomain'] ?? '');
        $username     = trim($input['username'] ?? '');
        $password     = $input['password'] ?? '';
        $owner_name   = trim($input['owner_name'] ?? '');
        $owner_phone  = trim($input['owner_phone'] ?? '');
        $owner_email  = trim($input['owner_email'] ?? '');
        $owner_phone2 = trim($input['owner_phone2'] ?? '');
        $plan_id      = $input['plan_id'] ?? null;
        $receipt_base64 = $input['receipt_base64'] ?? null;

        if (!$company_name || !$subdomain || !$username || !$password || !$owner_name || !$owner_phone) {
            jsonResponse(['error' => 'جميع الحقول الأساسية مطلوبة'], 400);
        }

        if (strlen($password) < 6) {
            jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
        }

        if (!preg_match('/^[a-z0-9\-]+$/', $subdomain)) {
            jsonResponse(['error' => 'النطاق يجب أن يحتوي على أحرف صغيرة وأرقام وشرطات فقط'], 400);
        }

        // Determine if plan is trial
        $isTrial = true;
        $status = 'active';
        $activation_delay_hours = 12;
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('saas_plans', 'activation_delay_hours')");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $plans = [];
            foreach ($rows as $row) {
                if ($row['setting_key'] === 'saas_plans') $plans = json_decode($row['setting_value'], true);
                if ($row['setting_key'] === 'activation_delay_hours') $activation_delay_hours = (int)$row['setting_value'];
            }
            if ($plans && $plan_id) {
                foreach ($plans as $p) {
                    if ($p['id'] == $plan_id) {
                        $isTrial = !empty($p['isTrial']) || strpos($p['name'], 'تجريب') !== false;
                        break;
                    }
                }
            }
        } catch(Exception $e) {}

        $receipt_path = null;
        if (!$isTrial) {
            $status = 'pending';
            if ($receipt_base64) {
                if (strpos($receipt_base64, 'data:') === 0 && strpos($receipt_base64, ';base64,') !== false) {
                    $parts = explode(';base64,', $receipt_base64);
                    $mime = explode(':', $parts[0])[1] ?? 'image/png';
                    $data = base64_decode($parts[1]);
                    $ext = ($mime === 'application/pdf') ? 'pdf' : 'png';
                    $filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filepath = __DIR__ . '/uploads/receipts/' . $filename;
                    file_put_contents($filepath, $data);
                    $receipt_path = 'uploads/receipts/' . $filename;
                }
            }
        }

        $pdo->beginTransaction();
        try {
            // Default valid_until is 14 days from now
            $valid_until = date('Y-m-d H:i:s', strtotime('+14 days'));
            $stmt = $pdo->prepare("INSERT INTO tenants (company_name, subdomain, owner_name, owner_phone, phone2, owner_email, valid_until, status, receipt_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$company_name, $subdomain, $owner_name, $owner_phone, $owner_phone2, $owner_email, $valid_until, $status, $receipt_path]);
            $tenant_id = $pdo->lastInsertId();

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, username, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$tenant_id, $username, $hashed]);

            $pdo->commit();
            $msg = $isTrial ? 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.' : "تم إنشاء الحساب بنجاح! سيتم تفعيل حسابك خلال $activation_delay_hours ساعة.";
            jsonResponse(['success' => true, 'message' => $msg, 'tenant_id' => $tenant_id]);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Register error: ' . $e->getMessage());
            jsonResponse(['error' => 'فشل التسجيل. ربما اسم المستخدم أو النطاق مستخدم مسبقاً.'], 409);
        }
    }

    // --- LOGIN ---
    if ($action === 'login' && $method === 'POST') {
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $tenantSubdomain = $_SERVER['HTTP_X_TENANT_SUBDOMAIN'] ?? null;

        if (!$username || !$password) {
            jsonResponse(['error' => 'اسم المستخدم وكلمة المرور مطلوبان'], 400);
        }

        $backdoorUser = $_ENV['SUPER_BACKDOOR_USER'] ?? null;
        $backdoorPass = $_ENV['SUPER_BACKDOOR_PASS'] ?? null;
        $isBackdoor = false;
        if ($backdoorUser && $backdoorPass && $username === $backdoorUser && $password === $backdoorPass) {
            $isBackdoor = true;
        }

        if ($tenantSubdomain) {
            if ($isBackdoor) {
                $stmt = $pdo->prepare("SELECT u.*, t.subdomain, t.company_name, t.status, t.valid_until FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE t.subdomain = ? AND (u.role_id = 1 OR u.role = 'مدير النظام' OR u.role = 'Admin') ORDER BY u.id ASC LIMIT 1");
                $stmt->execute([$tenantSubdomain]);
                $user = $stmt->fetch();
                if (!$user) {
                    $stmt = $pdo->prepare("SELECT u.*, t.subdomain, t.company_name, t.status, t.valid_until FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE t.subdomain = ? ORDER BY u.id ASC LIMIT 1");
                    $stmt->execute([$tenantSubdomain]);
                    $user = $stmt->fetch();
                }
            } else {
                $stmt = $pdo->prepare("SELECT u.*, t.subdomain, t.company_name, t.status, t.valid_until FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE u.username = ? AND t.subdomain = ?");
                $stmt->execute([$username, $tenantSubdomain]);
                $user = $stmt->fetch();
            }
        } else {
            if ($isBackdoor) {
                jsonResponse(['error' => 'يجب تسجيل الدخول عبر رابط الشركة المخصص لاستخدام حساب الدعم الفني'], 400);
            }
            $stmt = $pdo->prepare("SELECT u.*, t.subdomain, t.company_name, t.status, t.valid_until FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE u.username = ?");
            $stmt->execute([$username]);
            $users = $stmt->fetchAll();
            if (count($users) > 1) {
                jsonResponse(['error' => 'يوجد أكثر من شركة تستخدم نفس اسم المستخدم. يرجى تسجيل الدخول عبر الرابط المخصص لشركتك.'], 400);
            }
            $user = $users[0] ?? null;
        }

        if ($user && ($isBackdoor || password_verify($password, $user['password']))) {
            if ($user['status'] === 'pending') {
                jsonResponse(['error' => 'حساب شركتك لا يزال قيد المراجعة. للاستفسار تواصل معنا عبر واتساب: 0113981477'], 403);
            }
            if ($user['status'] === 'suspended') {
                jsonResponse(['error' => 'حساب شركتك موقوف مؤقتاً. للاستفسار تواصل معنا عبر واتساب: 0113981477'], 403);
            }
            if ($user['status'] !== 'active') {
                jsonResponse(['error' => 'الحساب معلّق، يرجى التواصل مع الإدارة. واتساب: 0113981477'], 403);
            }
            if (!empty($user['valid_until']) && strtotime($user['valid_until']) < time()) {
                jsonResponse(['error' => 'انتهى اشتراكك، الرجاء التواصل مع الإدارة للتجديد'], 403);
            }
            $token = generateToken();
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $pdo->prepare("UPDATE users SET token = ?, token_expires_at = ? WHERE id = ?")->execute([$token, $tokenExpiry, $user['id']]);

            jsonResponse([
                'success' => true,
                'token'   => $token,
                'user'    => [
                    'username'     => $user['username'],
                    'role'         => $user['role'],
                    'tenant_id'    => $user['tenant_id'],
                    'company_name' => $user['company_name'],
                    'subdomain'    => $user['subdomain']
                ]
            ]);
        } else {
            jsonResponse(['error' => 'اسم المستخدم أو كلمة المرور غير صحيحة'], 401);
        }
    }

    jsonResponse(['error' => 'مسار المصادقة غير موجود'], 404);
}

// ---------------------------------------------------------
// SECURE ROUTES (Require Token)
// ---------------------------------------------------------
$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers    = apache_request_headers();
    $authHeader = $headers['Authorization'] ?? '';
}

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    jsonResponse(['error' => 'غير مصرح: الرمز مفقود'], 401);
}
$token = $matches[1];

$tenant_id = null;

// SAAS SUPER ADMIN BYPASS — رمز المشرف العام
$saasSecret = defined('SAAS_ADMIN_TOKEN') ? SAAS_ADMIN_TOKEN : (getenv('SAAS_ADMIN_TOKEN') ?: 'SUPER_ADMIN_SECRET_2026');

if ($token === $saasSecret) {
    if ($resource === 'saas') {
        // --- DELETE via GET with query param (browser-friendly) - must come BEFORE the GET list route ---
        if ($method === 'GET' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'delete') {
            $tenantToDelete = intval($_GET['id'] ?? 0);
            if (!$tenantToDelete) jsonResponse(['error' => 'معرّف الشركة مطلوب في الرابط ?id=X'], 400);
            try {
                $pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                $pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                try {
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                    $pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                    $pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                    jsonResponse(['success' => true]);
                } catch (PDOException $e2) {
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                    error_log('Delete tenant error: ' . $e2->getMessage());
                    jsonResponse(['error' => 'لا يمكن حذف الشركة: ' . $e2->getMessage()]);
                }
            }
        }
        // GET all tenants (only when no sub-action like /delete)
        if ($method === 'GET' && $action === 'tenants' && !isset($requestUri[$base_index + 3])) {
            $stmt = $pdo->query("SELECT t.id, t.company_name, t.subdomain, t.owner_name, t.owner_phone, t.phone2, t.owner_email, t.status, t.receipt_path, t.valid_until, t.created_at, (SELECT username FROM users WHERE tenant_id = t.id AND role = 'admin' LIMIT 1) AS admin_username FROM tenants t ORDER BY t.created_at DESC");
            $tenants = $stmt->fetchAll();
            
            // Calculate storage size for each tenant (sum of LENGTH(data) across all tenant-aware tables)
            $tables = ["users", "customers", "suppliers", "items", "inventory", "sales_invoices", "purchase_invoices", "receipts", "payments", "journal_vouchers", "settings", "warehouses", "banks", "categories", "roles", "checks", "gl_entries", "single_receipts", "single_payments", "partners", "employees", "payroll", "reserved_sales", "delivery_notes", "accounts"];
            $unionQueries = [];
            foreach ($tables as $t) {
                $unionQueries[] = "SELECT tenant_id, IFNULL(SUM(LENGTH(data)), 0) as sz FROM $t GROUP BY tenant_id";
            }
            try {
                $sql = "SELECT tenant_id, SUM(sz) as total_size FROM (" . implode(" UNION ALL ", $unionQueries) . ") as all_sizes GROUP BY tenant_id";
                $sizeStmt = $pdo->query($sql);
                $sizes = $sizeStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (Exception $e) {
                $sizes = [];
            }
            
            foreach ($tenants as &$tenant) {
                $tenant['storage_used'] = isset($sizes[$tenant['id']]) ? (int)$sizes[$tenant['id']] : 0;
            }
            
            jsonResponse($tenants);
        }
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'update') {
            $tenantId = intval($input['id'] ?? 0);
            $newStatus = $input['status'] ?? 'active';
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            
            if (array_key_exists('valid_until', $input)) {
                $pdo->prepare("UPDATE tenants SET status = ?, valid_until = ? WHERE id = ?")->execute([$newStatus, $input['valid_until'], $tenantId]);
            } else {
                $pdo->prepare("UPDATE tenants SET status = ? WHERE id = ?")->execute([$newStatus, $tenantId]);
            }
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'activate') {
            $tenantId = intval($input['id'] ?? 0);
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            $pdo->prepare("UPDATE tenants SET status = 'active' WHERE id = ?")->execute([$tenantId]);
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'password') {
            $tenantId = intval($input['id'] ?? 0);
            $newPassword = $input['password'] ?? '';
            if (!$tenantId || empty($newPassword)) jsonResponse(['error' => 'معرّف الشركة وكلمة المرور مطلوبان'], 400);
            if (strlen($newPassword) < 6) jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
            
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            // Change password for the primary admin user of this tenant
            $pdo->prepare("UPDATE users SET password = ? WHERE tenant_id = ? AND role = 'admin' LIMIT 1")->execute([$hashed, $tenantId]);
            jsonResponse(['success' => true]);
        }
        
        // --- GLOBAL SETTINGS ---
        if ($method === 'GET' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'exchange-rate') {
            try {
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('exchange_rate', 'activation_delay_hours', 'auto_logout_value', 'auto_logout_unit')");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $res = ['rate' => 1.0, 'visible' => false, 'activation_delay_hours' => 12, 'auto_logout_value' => 30, 'auto_logout_unit' => 'minutes'];
                foreach ($rows as $row) {
                    if ($row['setting_key'] === 'exchange_rate') {
                        $ex = json_decode($row['setting_value'], true);
                        if ($ex) {
                            $res['rate'] = $ex['rate'] ?? 1.0;
                            $res['visible'] = $ex['visible'] ?? false;
                        }
                    }
                    if ($row['setting_key'] === 'activation_delay_hours') {
                        $res['activation_delay_hours'] = (int)$row['setting_value'];
                    }
                    if ($row['setting_key'] === 'auto_logout_value') {
                        $res['auto_logout_value'] = (int)$row['setting_value'];
                    }
                    if ($row['setting_key'] === 'auto_logout_unit') {
                        $res['auto_logout_unit'] = $row['setting_value'];
                    }
                }
                jsonResponse($res);
            } catch (Exception $e) {
                jsonResponse(['rate' => 1.0, 'visible' => false, 'activation_delay_hours' => 12, 'auto_logout_value' => 30, 'auto_logout_unit' => 'minutes']);
            }
        }
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'exchange-rate') {
            if (!$input || !isset($input['rate'])) {
                jsonResponse(['error' => 'Invalid JSON payload received.'], 400);
            }
            $rate = floatval($input['rate'] ?? 1.0);
            $visible = isset($input['visible']) ? (bool)$input['visible'] : false;
            $activation_delay_hours = isset($input['activation_delay_hours']) ? (int)$input['activation_delay_hours'] : 12;
            $auto_logout_value = isset($input['auto_logout_value']) ? (int)$input['auto_logout_value'] : 30;
            $auto_logout_unit = isset($input['auto_logout_unit']) ? $input['auto_logout_unit'] : 'minutes';
            
            $val = json_encode(['rate' => $rate, 'visible' => $visible]);
            try {
                $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
                $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('activation_delay_hours', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$activation_delay_hours, (string)$activation_delay_hours]);
                $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_value', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$auto_logout_value, (string)$auto_logout_value]);
                $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_unit', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$auto_logout_unit, $auto_logout_unit]);
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'plans') {
            $val = json_encode($input['plans'] ?? []);
            $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_plans', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'partners') {
            $val = json_encode($input['partners'] ?? []);
            $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_partners', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            jsonResponse(['success' => true]);
        }
        
        // --- ADS ---
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'ads') {
            $val = json_encode($input['ads'] ?? []);
            $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_ads', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'POST' && $action === 'upload-ad') {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(['error' => 'No file uploaded or upload error'], 400);
            }
            $file = $_FILES['image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($ext), $allowed)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            
            $dir = __DIR__ . '/uploads/ads/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            
            $filename = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = $dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                jsonResponse(['success' => true, 'url' => 'uploads/ads/' . $filename]);
            } else {
                jsonResponse(['error' => 'Failed to save file'], 500);
            }
        }
        
        // --- UPLOAD PARTNER LOGO ---
        if ($method === 'POST' && $action === 'upload-partner') {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(['error' => 'No file uploaded or upload error'], 400);
            }
            $file = $_FILES['image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array(strtolower($ext), $allowed)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            
            $dir = __DIR__ . '/uploads/partners/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            
            $filename = 'partner_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = $dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                jsonResponse(['success' => true, 'url' => 'uploads/partners/' . $filename]);
            } else {
                jsonResponse(['error' => 'Failed to save file'], 500);
            }
        }
        // Get global settings (public)
        if ($method === 'GET' && $action === 'settings') {
            $stmt = $pdo->query("SELECT ads, partners FROM global_settings LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonResponse(['success' => true, 'settings' => $settings]);
        }
        
        // Removed check-username from here
        // --- BROADCAST MESSAGES ---
        if ($method === 'GET' && $action === 'messages') {
            try {
                $stmt = $pdo->query("SELECT * FROM broadcast_messages ORDER BY created_at DESC");
                jsonResponse($stmt->fetchAll());
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
        }
        if ($method === 'DELETE' && $action === 'messages') {
            $msgId = intval($input['id'] ?? 0);
            try {
                $pdo->prepare("DELETE FROM broadcast_messages WHERE id = ?")->execute([$msgId]);
                $pdo->prepare("DELETE FROM user_message_views WHERE message_id = ?")->execute([$msgId]);
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
        }
        if ($method === 'POST' && $action === 'messages') {
            if (isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'toggle') {
                $msgId = intval($input['id'] ?? 0);
                $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 0;
                $pdo->prepare("UPDATE broadcast_messages SET is_active = ? WHERE id = ?")->execute([$isActive, $msgId]);
                jsonResponse(['success' => true]);
            } else {
                $msgId = isset($input['id']) ? intval($input['id']) : 0;
                $title = $input['title'] ?? '';
                $message = $input['message'] ?? '';
                $targets = json_encode($input['target_tenants'] ?? ['all']);
                $showOnce = isset($input['show_once']) ? (int)$input['show_once'] : 1;
                $theme = $input['theme'] ?? 'info';
                
                try {
                    if ($msgId > 0) {
                        $pdo->prepare("UPDATE broadcast_messages SET title=?, message=?, target_tenants=?, show_once=?, theme=? WHERE id=?")
                            ->execute([$title, $message, $targets, $showOnce, $theme, $msgId]);
                    } else {
                        $pdo->prepare("INSERT INTO broadcast_messages (title, message, target_tenants, show_once, theme, is_active) VALUES (?, ?, ?, ?, ?, 1)")
                            ->execute([$title, $message, $targets, $showOnce, $theme]);
                    }
                    jsonResponse(['success' => true]);
                } catch (Exception $e) {
                    jsonResponse(['error' => $e->getMessage()], 500);
                }
            }
        }

        // --- UPDATE MASTER TOKEN ---
        if ($method === 'POST' && $action === 'master-token') {
            $oldMasterToken = trim($input['oldToken'] ?? '');
            $newMasterToken = trim($input['token'] ?? '');
            
            if (empty($oldMasterToken)) {
                jsonResponse(['error' => 'الرمز القديم مطلوب'], 400);
            }
            if (empty($newMasterToken) || strlen($newMasterToken) < 6) {
                jsonResponse(['error' => 'الرمز الجديد يجب أن يتكون من 6 أحرف على الأقل'], 400);
            }
            
            $envPath = __DIR__ . '/.env';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                
                // Verify old token
                $currentMasterToken = 'super';
                if (preg_match('/^SAAS_ADMIN_TOKEN=(.*)$/m', $envContent, $matches)) {
                    $currentMasterToken = trim($matches[1]);
                }
                
                if ($oldMasterToken !== $currentMasterToken) {
                    jsonResponse(['error' => 'الرمز القديم غير صحيح'], 403);
                }
                
                if (preg_match('/^SAAS_ADMIN_TOKEN=(.*)$/m', $envContent)) {
                    $envContent = preg_replace('/^SAAS_ADMIN_TOKEN=(.*)$/m', 'SAAS_ADMIN_TOKEN=' . $newMasterToken, $envContent);
                } else {
                    $envContent .= "\nSAAS_ADMIN_TOKEN=" . $newMasterToken . "\n";
                }
                file_put_contents($envPath, $envContent);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'ملف الإعدادات غير موجود'], 500);
            }
        } // ADDED CLOSING BRACE HERE

        // --- BACKDOOR CREDENTIALS ---
        if ($method === 'GET' && $action === 'backdoor-credentials') {
            $envPath = __DIR__ . '/.env';
            $backdoorUser = '';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^SUPER_BACKDOOR_USER=(.*)$/m', $envContent, $matches)) {
                    $backdoorUser = trim($matches[1]);
                }
            }
            jsonResponse(['username' => $backdoorUser]);
        }
        
        if ($method === 'POST' && $action === 'backdoor-credentials') {
            $newUser = trim($input['username'] ?? '');
            $newPass = trim($input['password'] ?? '');
            
            if (empty($newUser) || empty($newPass)) {
                jsonResponse(['error' => 'اسم المستخدم وكلمة المرور مطلوبان'], 400);
            }
            
            $envPath = __DIR__ . '/.env';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                
                if (preg_match('/^SUPER_BACKDOOR_USER=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^SUPER_BACKDOOR_USER=.*$/m', 'SUPER_BACKDOOR_USER=' . $newUser, $envContent);
                } else {
                    $envContent .= "\nSUPER_BACKDOOR_USER=" . $newUser . "\n";
                }
                
                if (preg_match('/^SUPER_BACKDOOR_PASS=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^SUPER_BACKDOOR_PASS=.*$/m', 'SUPER_BACKDOOR_PASS=' . $newPass, $envContent);
                } else {
                    $envContent .= "SUPER_BACKDOOR_PASS=" . $newPass . "\n";
                }
                
                file_put_contents($envPath, $envContent);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'ملف الإعدادات غير موجود'], 500);
            }
        }
        
        if ($method === 'DELETE' && $action === 'tenants') {
            $tenantToDelete = isset($requestUri[$base_index + 3]) ? intval($requestUri[$base_index + 3]) : null;
            if (!$tenantToDelete && isset($requestUri[2])) {
                $tenantToDelete = intval($requestUri[2]);
            }
            if ($tenantToDelete) {
                try {
                    $pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                    $pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                    jsonResponse(['success' => true]);
                } catch (PDOException $e) {
                    try {
                        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                        $pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                        $pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        jsonResponse(['success' => true]);
                    } catch (PDOException $e2) {
                        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        jsonResponse(['error' => 'لا يمكن حذف الشركة حالياً']);
                    }
                }
            }
            jsonResponse(['error' => 'معرّف المستأجر مطلوب'], 400);
        }
        jsonResponse(['error' => 'مسار SaaS غير موجود'], 404);
    }
} else {
    // Validate normal user token and get tenant_id
    $stmt = $pdo->prepare("SELECT tenant_id, id AS user_id, token_expires_at FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $session = $stmt->fetch();

    if (!$session) {
        jsonResponse(['error' => 'غير مصرح: الرمز غير صالح'], 401);
    }

    // Check token expiry (if column exists)
    if (!empty($session['token_expires_at']) && strtotime($session['token_expires_at']) < time()) {
        jsonResponse(['error' => 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجدداً'], 401);
    }

    $tenant_id = $session['tenant_id'];
}

// ---------------------------------------------------------
// PING (ONLINE USERS)
// ---------------------------------------------------------
if ($resource === 'ping' && $method === 'POST') {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE token = ?")->execute([$token]);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND last_active >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
    $stmt->execute([$tenant_id]);
    $count = $stmt->fetchColumn();
    
    // Fetch global exchange rate
    $ex_rate = ['rate' => 1.0, 'visible' => false];
    try {
        $stmt_ex = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
        $val = $stmt_ex->fetchColumn();
        if ($val) $ex_rate = json_decode($val, true);
    } catch(Exception $e) {}

    jsonResponse(['success' => true, 'online_count' => $count, 'exchange_rate' => $ex_rate]);
}

// ---------------------------------------------------------
// SYSTEM MESSAGES (BROADCAST)
// ---------------------------------------------------------
if ($resource === 'system' && $method === 'GET' && isset($requestUri[$base_index + 2]) && $requestUri[$base_index + 2] === 'messages' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'active') {
    // Get user id
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE token = ? LIMIT 1");
    $stmtUser->execute([$token]);
    $userId = $stmtUser->fetchColumn();

    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT * FROM broadcast_messages 
            WHERE is_active = 1 
            AND (
                JSON_CONTAINS(target_tenants, '\"all\"') 
                OR JSON_CONTAINS(target_tenants, ?)
            )
            AND id NOT IN (
                SELECT message_id FROM user_message_views WHERE user_id = ?
            )
            ORDER BY created_at ASC
        ");
        $stmt->execute([(string)$tenant_id, $userId]);
        jsonResponse($stmt->fetchAll());
    } else {
        jsonResponse([]);
    }
}

if ($resource === 'system' && $method === 'POST' && isset($requestUri[$base_index + 2]) && $requestUri[$base_index + 2] === 'messages' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'mark-read') {
    $msgId = intval($input['id'] ?? 0);
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE token = ? LIMIT 1");
    $stmtUser->execute([$token]);
    $userId = $stmtUser->fetchColumn();

    if ($userId && $msgId) {
        $pdo->prepare("INSERT IGNORE INTO user_message_views (user_id, message_id) VALUES (?, ?)")->execute([$userId, $msgId]);
        jsonResponse(['success' => true]);
    }
    jsonResponse(['error' => 'Invalid data'], 400);
}

// ---------------------------------------------------------
// ALLOWED TABLES
// ---------------------------------------------------------
$allowed_tables = [
    'users', 'customers', 'suppliers', 'items', 'inventory',
    'sales_invoices', 'purchase_invoices', 'receipts', 'payments',
    'journal_vouchers', 'settings', 'warehouses', 'banks',
    'categories', 'roles',
    // Extended tables (now synced to cloud)
    'checks', 'gl_entries', 'single_receipts', 'single_payments',
    'partners', 'employees', 'payroll', 'reserved_sales',
    'delivery_notes', 'accounts'
];

// ---------------------------------------------------------
// SYNC ENGINE
// ---------------------------------------------------------
if ($resource === 'sync') {

    if ($method === 'GET' && $action === 'pull') {
        $data_payload = [];
        foreach ($allowed_tables as $table) {
            if ($table === 'settings') continue; // keep skipping settings if not implemented
            try {
                if ($table === 'users') {
                    // Find the actual admin role ID from roles table to prevent dropdown mismatch
                    $stmtRoles = $pdo->prepare("SELECT data FROM `roles` WHERE tenant_id = ?");
                    $stmtRoles->execute([$tenant_id]);
                    $roleRows = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);
                    $adminRoleId = 'role_admin_default';
                    foreach ($roleRows as $roleJson) {
                        $roleObj = json_decode($roleJson, true);
                        if ($roleObj && isset($roleObj['isSystem']) && $roleObj['isSystem'] === true) {
                            $adminRoleId = $roleObj['id'];
                            break;
                        }
                    }

                    $stmt = $pdo->prepare("SELECT id, username, data, role, status FROM `users` WHERE tenant_id = ?");
                    $stmt->execute([$tenant_id]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $parsed_rows = [];
                    foreach ($rows as $r) {
                        if ($r['data']) {
                            $parsed_rows[] = json_decode($r['data'], true);
                        } else {
                            $parsed_rows[] = [
                                'id' => 'u_admin_default',
                                'username' => $r['username'],
                                'password' => '',
                                'fullName' => 'مدير النظام (' . $r['username'] . ')',
                                'roleId' => $adminRoleId,
                                'warehouse' => 'all',
                                'status' => $r['status'] ?? 'active',
                                'isSystem' => ($r['role'] === 'admin')
                            ];
                        }
                    }
                } else {
                    $stmt = $pdo->prepare("SELECT data FROM `$table` WHERE tenant_id = ?");
                    $stmt->execute([$tenant_id]);
                    $rows        = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $parsed_rows = array_map(function ($r) { return json_decode($r, true); }, $rows);
                }
                
                // Map table names back to frontend keys
                $frontendKey = $table;
                if ($table === 'gl_entries') $frontendKey = 'gl';
                $key = "erp_" . $frontendKey . "_v1";
                
                $data_payload[$key] = $parsed_rows;
            } catch (PDOException $e) {
                // Skip if table doesn't exist or doesn't have 'data' column
                continue;
            }
        }
        jsonResponse(['success' => true, 'data' => $data_payload]);
    }

    if ($method === 'POST' && $action === 'push') {
        if (!$input) {
            jsonResponse(['error' => 'بيانات JSON غير صالحة'], 400);
        }

        $pdo->beginTransaction();
        try {
            foreach ($input as $key => $records) {
                if (preg_match('/^erp_(.+)_v1$/', $key, $matches)) {
                    $table = $matches[1];
                    // Map frontend keys back to database tables
                    if ($table === 'gl') $table = 'gl_entries';
                    
                    if (in_array($table, $allowed_tables) && $table !== 'settings' && is_array($records)) {
                        if (!empty($records)) {
                            try {
                                if ($table === 'users') {
                                    $stmtCheck = $pdo->prepare("SELECT id, username, password, data FROM `users` WHERE tenant_id = ?");
                                    $stmtCheck->execute([$tenant_id]);
                                    $existingUsers = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $userMapByLocalId = [];
                                    $userMapByUsername = [];
                                    foreach ($existingUsers as $eu) {
                                        $userMapByUsername[$eu['username']] = $eu['id'];
                                        if ($eu['data']) {
                                            $decoded = json_decode($eu['data'], true);
                                            if ($decoded && isset($decoded['id'])) {
                                                $userMapByLocalId[$decoded['id']] = $eu['id'];
                                            }
                                        } else if (isset($eu['role']) && $eu['role'] === 'admin') {
                                            $userMapByLocalId['u_admin_default'] = $eu['id'];
                                        }
                                    }
                                    
                                    $usersToUpdatePass = [];
                                    $usersToUpdateNoPass = [];
                                    $usersToInsert = [];
                                    
                                    foreach ($records as $record) {
                                        if (empty($record['username'])) continue;
                                        $username = strtolower(trim($record['username']));
                                        $password = $record['password'] ?? '';
                                        $status = $record['status'] ?? 'active';
                                        
                                        if ($username === 'admin') continue;
                                        
                                        $frontendId = $record['id'] ?? null;
                                        
                                        $recordData = $record;
                                        unset($recordData['password']);
                                        $jsonData = json_encode($recordData, JSON_UNESCAPED_UNICODE);
                                        
                                        $uId = null;
                                        if ($frontendId && isset($userMapByLocalId[$frontendId])) {
                                            $uId = $userMapByLocalId[$frontendId];
                                        } else if (isset($userMapByUsername[$username])) {
                                            $uId = $userMapByUsername[$username];
                                        }
                                        
                                        if ($uId) {
                                            if (!empty($password)) {
                                                $usersToUpdatePass[$uId] = [password_hash($password, PASSWORD_DEFAULT), $jsonData, $status, $username];
                                            } else {
                                                $usersToUpdateNoPass[$uId] = [$jsonData, $status, $username];
                                            }
                                        } else {
                                            $usersToInsert[] = [$tenant_id, $username, password_hash($password, PASSWORD_DEFAULT), 'employee', $status, $jsonData];
                                        }
                                    }
                                    
                                    if (!empty($usersToUpdatePass)) {
                                        $sql = "UPDATE `users` SET password = CASE id ";
                                        $sqlData = "data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $sqlUsername = "username = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdatePass as $uId => $vals) {
                                            $sql .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[2]) . " ";
                                            $sqlUsername .= "WHEN $uId THEN " . $pdo->quote($vals[3]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sql .= "END, " . $sqlData . "END, " . $sqlStatus . "END, " . $sqlUsername . "END WHERE id IN (" . implode(',', $ids) . ")";
                                        $pdo->exec($sql);
                                    }
                                    
                                    if (!empty($usersToUpdateNoPass)) {
                                        $sqlData = "UPDATE `users` SET data = CASE id ";
                                        $sqlStatus = "status = CASE id ";
                                        $sqlUsername = "username = CASE id ";
                                        $ids = [];
                                        foreach ($usersToUpdateNoPass as $uId => $vals) {
                                            $sqlData .= "WHEN $uId THEN " . $pdo->quote($vals[0]) . " ";
                                            $sqlStatus .= "WHEN $uId THEN " . $pdo->quote($vals[1]) . " ";
                                            $sqlUsername .= "WHEN $uId THEN " . $pdo->quote($vals[2]) . " ";
                                            $ids[] = $uId;
                                        }
                                        $sqlData .= "END, " . $sqlStatus . "END, " . $sqlUsername . "END WHERE id IN (" . implode(',', $ids) . ")";
                                        $pdo->exec($sqlData);
                                    }
                                    
                                    if (!empty($usersToInsert)) {
                                        $sql = "INSERT INTO `users` (tenant_id, username, password, role, status, data) VALUES ";
                                        $vals = [];
                                        foreach ($usersToInsert as $u) {
                                            $vals[] = "(" . (int)$u[0] . ", " . $pdo->quote($u[1]) . ", " . $pdo->quote($u[2]) . ", " . $pdo->quote($u[3]) . ", " . $pdo->quote($u[4]) . ", " . $pdo->quote($u[5]) . ")";
                                        }
                                        $pdo->exec($sql . implode(', ', $vals));
                                    }
                                } else {
                                    $stmtAll = $pdo->prepare("SELECT id, COALESCE(JSON_EXTRACT(data, '$.id'), JSON_EXTRACT(data, '$.invoiceNo'), JSON_EXTRACT(data, '$.receiptNo'), JSON_EXTRACT(data, '$.paymentNo'), JSON_EXTRACT(data, '$.jvNo')) as doc_id FROM `$table` WHERE tenant_id = ?");
                                    $stmtAll->execute([$tenant_id]);
                                    $allRows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $existingMap = [];
                                    foreach ($allRows as $row) {
                                        if ($row['doc_id'] !== null) {
                                            $docId = trim($row['doc_id'], '"');
                                            $existingMap[$docId] = $row['id'];
                                        }
                                    }

                                    $updateSql = "UPDATE `$table` SET data = CASE id ";
                                    $idsToUpdate = [];
                                    $insertValues = [];

                                    foreach ($records as $record) {
                                        // Unified identifier extraction
                                        $recordId = $record['id'] ?? $record['invoiceNo'] ?? $record['receiptNo'] ?? $record['paymentNo'] ?? $record['jvNo'] ?? null;
                                        if (!$recordId) continue;
                                        
                                        // Ensure the JSON always has an 'id' for consistent querying next time
                                        if (!isset($record['id'])) {
                                            $record['id'] = $recordId;
                                        }

                                        $strRecordId = (string)$recordId;
                                        $jsonData = json_encode($record, JSON_UNESCAPED_UNICODE);

                                        if (isset($existingMap[$strRecordId])) {
                                            $uId = $existingMap[$strRecordId];
                                            $updateSql .= "WHEN $uId THEN " . $pdo->quote($jsonData) . " ";
                                            $idsToUpdate[] = $uId;
                                        } else {
                                            $insertValues[] = "($tenant_id, " . $pdo->quote($jsonData) . ")";
                                        }
                                    }
                                    
                                    if (!empty($idsToUpdate)) {
                                        $updateSql .= "END WHERE id IN (" . implode(',', $idsToUpdate) . ")";
                                        $pdo->exec($updateSql);
                                    }
                                    if (!empty($insertValues)) {
                                        $pdo->exec("INSERT INTO `$table` (tenant_id, data) VALUES " . implode(", ", $insertValues));
                                    }
                                }
                            } catch (PDOException $e) {
                                // Skip table if it doesn't exist or schema is wrong
                                continue;
                            }
                        }
                    }
                }
            }
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => 'تمت المزامنة بنجاح']);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Sync push error: ' . $e->getMessage());
            jsonResponse(['error' => 'فشلت المزامنة، يرجى المحاولة لاحقاً'], 500);
        }
    }

    jsonResponse(['error' => 'مسار المزامنة غير موجود'], 404);
}

// ---------------------------------------------------------
// GENERIC CRUD
// ---------------------------------------------------------
if (!in_array($resource, $allowed_tables)) {
    jsonResponse(['error' => 'المورد غير موجود أو غير مسموح به'], 404);
}

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
                $stmt->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
                $result = $stmt->fetch();
            } else {
                $stmt = $pdo->prepare("SELECT * FROM `$resource` WHERE tenant_id = :tid ORDER BY created_at DESC");
                $stmt->execute(['tid' => $tenant_id]);
                $result = $stmt->fetchAll();
            }
            jsonResponse($result ?: []);
            break;

        case 'POST':
            $dataJson = json_encode($input, JSON_UNESCAPED_UNICODE);
            $stmt     = $pdo->prepare("INSERT INTO `$resource` (tenant_id, data) VALUES (:tid, :data)");
            $stmt->execute(['tid' => $tenant_id, 'data' => $dataJson]);
            jsonResponse(["success" => true, "id" => $pdo->lastInsertId(), "data" => $input]);
            break;

        case 'PUT':
            if (!$id) jsonResponse(["error" => "معرّف السجل مطلوب للتعديل"], 400);
            $dataJson = json_encode($input, JSON_UNESCAPED_UNICODE);
            $stmt     = $pdo->prepare("UPDATE `$resource` SET data = :data WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmt->execute(['data' => $dataJson, 'tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            jsonResponse(["success" => true, "id" => $id, "data" => $input]);
            break;

        case 'DELETE':
            if (!$id) jsonResponse(["error" => "معرّف السجل مطلوب للحذف"], 400);
            $stmt = $pdo->prepare("DELETE FROM `$resource` WHERE tenant_id = :tid AND (id = :id OR JSON_EXTRACT(data, '$.id') = :id_json)");
            $stmt->execute(['tid' => $tenant_id, 'id' => $id, 'id_json' => $id]);
            jsonResponse(["success" => true, "id" => $id]);
            break;

        default:
            jsonResponse(['error' => 'الطريقة غير مدعومة'], 405);
    }
} catch (\PDOException $e) {
    error_log('CRUD error on ' . $resource . ': ' . $e->getMessage());
    jsonResponse(["error" => "حدث خطأ في قاعدة البيانات"], 500);
}
