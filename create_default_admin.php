<?php
header('Content-Type: text/plain; charset=UTF-8');
require_once __DIR__ . '/api/db.php';

$expectedAdminToken = $_ENV['SAAS_ADMIN_TOKEN'] ?? getenv('SAAS_ADMIN_TOKEN') ?: '';
$providedToken = $_GET['admin_token'] ?? $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
if (PHP_SAPI !== 'cli' && ($expectedAdminToken === '' || !hash_equals($expectedAdminToken, (string)$providedToken))) {
    http_response_code(403);
    exit("Forbidden: CLI or valid SAAS_ADMIN_TOKEN required.\n");
}

try {
    // 1. Ensure active tenant exists
    $stmt = $pdo->query("SELECT id FROM tenants WHERE subdomain = 'demo' OR id = 1 LIMIT 1");
    $tenant = $stmt->fetch();
    if (!$tenant) {
        $stmtIns = $pdo->prepare("INSERT INTO tenants (company_name, subdomain, owner_name, owner_phone, owner_email, valid_until, status) VALUES ('شركة السجانة المحلية', 'demo', 'المدير', '249912345678', 'admin@elsajana.com', '2035-12-31 23:59:59', 'active')");
        $stmtIns->execute();
        $tenantId = $pdo->lastInsertId();
    } else {
        $tenantId = $tenant['id'];
        $pdo->prepare("UPDATE tenants SET status = 'active', valid_until = '2035-12-31 23:59:59' WHERE id = ?")->execute([$tenantId]);
    }

    // 2. Hash password "admin"
    $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);

    // 3. Insert or update user 'admin'
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' AND tenant_id = ? LIMIT 1");
    $stmtUser->execute([$tenantId]);
    $existingUser = $stmtUser->fetch();

    if ($existingUser) {
        $pdo->prepare("UPDATE users SET tenant_id = ?, password = ?, role = 'admin', status = 'active' WHERE id = ?")
            ->execute([$tenantId, $hashedPassword, $existingUser['id']]);
        echo "✅ User 'admin' updated with password 'admin' (Tenant ID: $tenantId)\n";
    } else {
        $pdo->prepare("INSERT INTO users (tenant_id, username, password, role, status) VALUES (?, 'admin', ?, 'admin', 'active')")
            ->execute([$tenantId, $hashedPassword]);
        echo "✅ User 'admin' created with password 'admin' (Tenant ID: $tenantId)\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
