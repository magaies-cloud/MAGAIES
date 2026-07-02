<?php
class TenantController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleRequest($action, $method, $input, $requestUri, $base_index) {
        // --- DELETE via GET with query param (browser-friendly) - must come BEFORE the GET list route ---
        if ($method === 'GET' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'delete') {
            $tenantToDelete = intval($_GET['id'] ?? 0);
            if (!$tenantToDelete) jsonResponse(['error' => 'معرّف الشركة مطلوب في الرابط ?id=X'], 400);
            try {
                $this->pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                $this->pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                try {
                    $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                    $this->pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                    $this->pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                    $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                    jsonResponse(['success' => true]);
                } catch (PDOException $e2) {
                    $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                    error_log('Delete tenant error: ' . $e2->getMessage());
                    jsonResponse(['error' => 'لا يمكن حذف الشركة: ' . $e2->getMessage()]);
                }
            }
        }
        // GET all tenants (only when no sub-action like /delete)
        if ($method === 'GET' && $action === 'tenants' && !isset($requestUri[$base_index + 3])) {
            $stmt = $this->pdo->query("SELECT t.id, t.company_name, t.subdomain, t.owner_name, t.owner_phone, t.phone2, t.owner_email, t.status, t.receipt_path, t.valid_until, t.max_users, t.plan_id, t.created_at, (SELECT username FROM users WHERE tenant_id = t.id AND role = 'admin' LIMIT 1) AS admin_username FROM tenants t ORDER BY t.created_at DESC");
            $tenants = $stmt->fetchAll();
            
            // Calculate storage size for each tenant (sum of LENGTH(data) across all tenant-aware tables)
            $tables = ["users", "customers", "suppliers", "items", "inventory", "sales_invoices", "purchase_invoices", "receipts", "payments", "journal_vouchers", "settings", "warehouses", "banks", "categories", "roles", "checks", "gl_entries", "single_receipts", "single_payments", "partners", "employees", "payroll", "reserved_sales", "delivery_notes", "accounts"];
            $unionQueries = [];
            foreach ($tables as $t) {
                $unionQueries[] = "SELECT tenant_id, IFNULL(SUM(LENGTH(data)), 0) as sz FROM $t GROUP BY tenant_id";
            }
            try {
                $sql = "SELECT tenant_id, SUM(sz) as total_size FROM (" . implode(" UNION ALL ", $unionQueries) . ") as all_sizes GROUP BY tenant_id";
                $sizeStmt = $this->pdo->query($sql);
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
            $maxUsers = isset($input['max_users']) && $input['max_users'] !== '' ? intval($input['max_users']) : null;
            $planId = isset($input['plan_id']) && $input['plan_id'] !== '' ? intval($input['plan_id']) : null;
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            
            if (array_key_exists('valid_until', $input)) {
                $this->pdo->prepare("UPDATE tenants SET status = ?, valid_until = ?, max_users = ?, plan_id = ? WHERE id = ?")->execute([$newStatus, $input['valid_until'], $maxUsers, $planId, $tenantId]);
            } else {
                $this->pdo->prepare("UPDATE tenants SET status = ?, max_users = ?, plan_id = ? WHERE id = ?")->execute([$newStatus, $maxUsers, $planId, $tenantId]);
            }
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'activate') {
            $tenantId = intval($input['id'] ?? 0);
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            $this->pdo->prepare("UPDATE tenants SET status = 'active' WHERE id = ?")->execute([$tenantId]);
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'password') {
            $tenantId = intval($input['id'] ?? 0);
            $newPassword = $input['password'] ?? '';
            if (!$tenantId || empty($newPassword)) jsonResponse(['error' => 'معرّف الشركة وكلمة المرور مطلوبان'], 400);
            if (strlen($newPassword) < 6) jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
            
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            // Change password for the primary admin user of this tenant
            $this->pdo->prepare("UPDATE users SET password = ? WHERE tenant_id = ? AND role = 'admin' LIMIT 1")->execute([$hashed, $tenantId]);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'GET' && $action === 'users') {
            $tenantId = intval($_GET['tenant_id'] ?? 0);
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            $stmt = $this->pdo->prepare("SELECT id, username, JSON_UNQUOTE(JSON_EXTRACT(data, '$.fullName')) as fullName, role FROM users WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            jsonResponse($stmt->fetchAll());
        }
        if ($method === 'POST' && $action === 'users' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'add') {
            $tenantId = intval($input['tenant_id'] ?? 0);
            $username = trim($input['username'] ?? '');
            $fullName = trim($input['fullName'] ?? '');
            $password = $input['password'] ?? '';
            $role = $input['role'] ?? 'user';
            if (!$tenantId || !$username || !$password) jsonResponse(['error' => 'البيانات غير مكتملة'], 400);
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $data = json_encode(['fullName' => $fullName]);
            $frontend_id = 'u_' . uniqid();
            
            $stmt = $this->pdo->prepare("INSERT INTO users (tenant_id, username, password, role, frontend_id, data) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $username, $hashed, $role, $frontend_id, $data]);
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'users' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'password') {
            $userId = intval($input['user_id'] ?? 0);
            $newPassword = $input['password'] ?? '';
            if (!$userId || empty($newPassword)) jsonResponse(['error' => 'المستخدم وكلمة المرور مطلوبان'], 400);
            if (strlen($newPassword) < 6) jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $userId]);
            jsonResponse(['success' => true]);
        }
        
        // --- GLOBAL SETTINGS ---
        if ($method === 'GET' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'exchange-rate') {
            try {
                $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('exchange_rate', 'activation_delay_hours', 'auto_logout_value', 'auto_logout_unit')");
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
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('activation_delay_hours', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$activation_delay_hours, (string)$activation_delay_hours]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_value', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$auto_logout_value, (string)$auto_logout_value]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_unit', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$auto_logout_unit, $auto_logout_unit]);
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'plans') {
            $val = json_encode($input['plans'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_plans', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            jsonResponse(['success' => true]);
        }
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'partners') {
            $val = json_encode($input['partners'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_partners', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            jsonResponse(['success' => true]);
        }
        
        // --- ADS ---
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'ads') {
            $val = json_encode($input['ads'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_ads', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
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
            $stmt = $this->pdo->query("SELECT ads, partners FROM global_settings LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonResponse(['success' => true, 'settings' => $settings]);
        }
        
        // Removed check-username from here
        // --- BROADCAST MESSAGES ---
        if ($method === 'GET' && $action === 'messages') {
            try {
                $stmt = $this->pdo->query("SELECT * FROM broadcast_messages ORDER BY created_at DESC");
                jsonResponse($stmt->fetchAll());
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
        }
        if ($method === 'DELETE' && $action === 'messages') {
            $msgId = intval($input['id'] ?? 0);
            try {
                $this->pdo->prepare("DELETE FROM broadcast_messages WHERE id = ?")->execute([$msgId]);
                $this->pdo->prepare("DELETE FROM user_message_views WHERE message_id = ?")->execute([$msgId]);
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
        }
        if ($method === 'POST' && $action === 'messages') {
            if (isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'toggle') {
                $msgId = intval($input['id'] ?? 0);
                $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 0;
                $this->pdo->prepare("UPDATE broadcast_messages SET is_active = ? WHERE id = ?")->execute([$isActive, $msgId]);
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
                        $this->pdo->prepare("UPDATE broadcast_messages SET title=?, message=?, target_tenants=?, show_once=?, theme=? WHERE id=?")
                            ->execute([$title, $message, $targets, $showOnce, $theme, $msgId]);
                    } else {
                        $this->pdo->prepare("INSERT INTO broadcast_messages (title, message, target_tenants, show_once, theme, is_active) VALUES (?, ?, ?, ?, ?, 1)")
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
                    $this->pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                    $this->pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                    jsonResponse(['success' => true]);
                } catch (PDOException $e) {
                    try {
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                        $this->pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                        $this->pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        jsonResponse(['success' => true]);
                    } catch (PDOException $e2) {
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        jsonResponse(['error' => 'لا يمكن حذف الشركة حالياً']);
                    }
                }
            }
            jsonResponse(['error' => 'معرّف المستأجر مطلوب'], 400);
        }
        jsonResponse(['error' => 'مسار SaaS غير موجود'], 404);

    }
}
