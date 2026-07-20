<?php
class TenantController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function initAuditLogsTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS `saas_audit_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `action` VARCHAR(255) NOT NULL,
                `details` TEXT NULL,
                `ip_address` VARCHAR(45) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
            
            try {
                $this->pdo->exec("ALTER TABLE `tenants` ADD COLUMN `max_storage` INT(11) DEFAULT NULL;");
            } catch (Exception $ex) {}
        } catch (Exception $e) {
            error_log("Failed to create saas_audit_logs: " . $e->getMessage());
        }
    }

    private function logSaaSAction($action, $details) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $stmt = $this->pdo->prepare("INSERT INTO `saas_audit_logs` (action, details, ip_address) VALUES (?, ?, ?)");
            $stmt->execute([$action, $details, $ip]);
        } catch (Exception $e) {
            error_log("Failed to log SaaS action: " . $e->getMessage());
        }
    }

    public function handleRequest($action, $method, $input, $requestUri, $base_index) {
        $this->initAuditLogsTable();

        // --- GET AUDIT LOGS ---
        if ($method === 'GET' && $action === 'audit-logs') {
            try {
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
                $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                
                if ($search !== '') {
                    $stmt = $this->pdo->prepare("SELECT * FROM `saas_audit_logs` WHERE action LIKE ? OR details LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
                    $stmt->execute(["%$search%", "%$search%", $limit, $offset]);
                    $logs = $stmt->fetchAll();
                    
                    $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM `saas_audit_logs` WHERE action LIKE ? OR details LIKE ?");
                    $stmtCount->execute(["%$search%", "%$search%"]);
                    $total = $stmtCount->fetchColumn();
                } else {
                    $stmt = $this->pdo->prepare("SELECT * FROM `saas_audit_logs` ORDER BY created_at DESC LIMIT ? OFFSET ?");
                    $stmt->execute([$limit, $offset]);
                    $logs = $stmt->fetchAll();
                    
                    $total = $this->pdo->query("SELECT COUNT(*) FROM `saas_audit_logs`")->fetchColumn();
                }
                
                jsonResponse(['success' => true, 'logs' => $logs, 'total' => intval($total)]);
            } catch (Exception $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }

        // GET all tenants (only when no sub-action like /delete)
        if ($method === 'GET' && $action === 'tenants' && !isset($requestUri[$base_index + 3])) {
            try {
                $stmt = $this->pdo->query("SELECT t.id, t.company_name, t.subdomain, t.owner_name, t.owner_phone, t.phone2, t.owner_email, t.status, t.receipt_path, t.valid_until, t.max_users, t.max_storage, t.plan_id, t.created_at, (SELECT username FROM users WHERE tenant_id = t.id AND role = 'admin' LIMIT 1) AS admin_username, (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) AS users_count FROM tenants t ORDER BY t.created_at DESC");
                $tenants = $stmt->fetchAll();
            } catch (PDOException $e) {
                jsonResponse(['error' => 'DB Error in tenants GET: ' . $e->getMessage()], 500);
                exit;
            }
            
            // Calculate storage size for each tenant (sum of LENGTH(data) across all tenant-aware tables)
            $tables = ["users", "customers", "suppliers", "items", "inventory", "sales_invoices", "purchase_invoices", "receipts", "payments", "journal_vouchers", "warehouses", "banks", "categories", "roles", "checks", "gl_entries", "single_receipts", "single_payments", "partners", "employees", "payroll", "reserved_sales", "delivery_notes", "accounts", "safes", "audit_logs", "financial_periods"];
            $unionQueries = [];
            foreach ($tables as $t) {
                $unionQueries[] = "SELECT tenant_id, IFNULL(SUM(LENGTH(data)), 0) as sz FROM `$t` GROUP BY tenant_id";
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
            $maxStorage = isset($input['max_storage']) && $input['max_storage'] !== '' ? intval($input['max_storage']) : null;
            $planId = isset($input['plan_id']) && $input['plan_id'] !== '' ? intval($input['plan_id']) : null;
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            
            if (array_key_exists('valid_until', $input)) {
                $this->pdo->prepare("UPDATE tenants SET status = ?, valid_until = ?, max_users = ?, max_storage = ?, plan_id = ? WHERE id = ?")->execute([$newStatus, $input['valid_until'], $maxUsers, $maxStorage, $planId, $tenantId]);
            } else {
                $this->pdo->prepare("UPDATE tenants SET status = ?, max_users = ?, max_storage = ?, plan_id = ? WHERE id = ?")->execute([$newStatus, $maxUsers, $maxStorage, $planId, $tenantId]);
            }
            $this->logSaaSAction('تعديل خطة المشترك', 'تعديل الباقة والحدود للمشترك معرّف ' . $tenantId . ' للحالة ' . $newStatus);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'activate') {
            $tenantId = intval($input['id'] ?? 0);
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            $this->pdo->prepare("UPDATE tenants SET status = 'active' WHERE id = ?")->execute([$tenantId]);
            $this->logSaaSAction('تفعيل مشترك', 'تفعيل الحساب للمشترك معرّف ' . $tenantId);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'sync-passwords') {
            $usersList = $input['users'] ?? [];
            $count = 0;
            foreach ($usersList as $entry) {
                $subdomain = trim($entry['subdomain'] ?? '');
                $username = trim($entry['username'] ?? '');
                $plainPassword = trim($entry['password'] ?? '');
                if (empty($subdomain) || empty($username) || empty($plainPassword)) continue;

                // Find tenant
                $tenantId = null;
                if (strpos($subdomain, 'tenant_') === 0) {
                    $tenantId = intval(substr($subdomain, 7));
                } else {
                    $tStmt = $this->pdo->prepare("SELECT id FROM tenants WHERE subdomain = ?");
                    $tStmt->execute([$subdomain]);
                    $tenant = $tStmt->fetch();
                    if ($tenant) {
                        $tenantId = $tenant['id'];
                    }
                }
                if (!$tenantId) continue;

                // Find user
                $uStmt = $this->pdo->prepare("SELECT id, data FROM users WHERE tenant_id = ? AND username = ?");
                $uStmt->execute([$tenantId, $username]);
                $user = $uStmt->fetch();
                if (!$user) continue;

                $dataArr = json_decode($user['data'], true) ?: [];
                $dataArr['password'] = $plainPassword;
                $updatedDataJson = json_encode($dataArr, JSON_UNESCAPED_UNICODE);

                $this->pdo->prepare("UPDATE users SET data = ? WHERE id = ?")->execute([$updatedDataJson, $user['id']]);
                $count++;
            }
            jsonResponse(['success' => true, 'updated_count' => $count]);
        }

        if ($method === 'POST' && $action === 'tenants' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'password') {
            $tenantId = intval($input['id'] ?? 0);
            $newPassword = $input['password'] ?? '';
            if (!$tenantId || empty($newPassword)) jsonResponse(['error' => 'معرّف الشركة وكلمة المرور مطلوبان'], 400);
            if (strlen($newPassword) < 6) jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
            
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Get admin user of this tenant to sync plain password in data column JSON
            $stmtAdmin = $this->pdo->prepare("SELECT id, data FROM users WHERE tenant_id = ? AND role = 'admin' LIMIT 1");
            $stmtAdmin->execute([$tenantId]);
            $adminUser = $stmtAdmin->fetch();
            if ($adminUser) {
                $dataArr = json_decode($adminUser['data'], true) ?: [];
                $dataArr['password'] = $newPassword;
                $updatedDataJson = json_encode($dataArr, JSON_UNESCAPED_UNICODE);
                $this->pdo->prepare("UPDATE users SET password = ?, data = ? WHERE id = ?")->execute([$hashed, $updatedDataJson, $adminUser['id']]);
            } else {
                $this->pdo->prepare("UPDATE users SET password = ? WHERE tenant_id = ? AND role = 'admin' LIMIT 1")->execute([$hashed, $tenantId]);
            }
            $this->logSaaSAction('تغيير كلمة مرور المشترك', 'تغيير كلمة مرور مدير الحساب للمشترك معرّف ' . $tenantId);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'GET' && $action === 'users') {
            $tenantId = intval($_GET['tenant_id'] ?? 0);
            if (!$tenantId) jsonResponse(['error' => 'معرّف الشركة مطلوب'], 400);
            $stmt = $this->pdo->prepare("SELECT id, username, password, JSON_UNQUOTE(JSON_EXTRACT(data, '$.password')) as plain_password, JSON_UNQUOTE(JSON_EXTRACT(data, '$.fullName')) as fullName, JSON_UNQUOTE(JSON_EXTRACT(data, '$.phone')) as phone, JSON_UNQUOTE(JSON_EXTRACT(data, '$.email')) as email, role, status, last_active, created_at FROM users WHERE tenant_id = ? ORDER BY FIELD(role, 'admin', 'manager', 'employee', 'user'), username ASC");
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
            $frontend_id = 'u_' . uniqid();
            $data = json_encode([
                'id' => $frontend_id,
                'username' => $username,
                'fullName' => $fullName,
                'password' => $password,
                'roleId' => $role === 'admin' ? 'role_admin_default' : 'role_user_default',
                'status' => 'active'
            ], JSON_UNESCAPED_UNICODE);
            
            $stmt = $this->pdo->prepare("INSERT INTO users (tenant_id, username, password, role, frontend_id, data) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $username, $hashed, $role, $frontend_id, $data]);
            $this->logSaaSAction('إضافة مستخدم لشركة', 'إضافة مستخدم جديد باسم ' . $username . ' للشركة معرّف ' . $tenantId);
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'POST' && $action === 'users' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'password') {
            $userId = intval($input['user_id'] ?? 0);
            $newPassword = $input['password'] ?? '';
            if (!$userId || empty($newPassword)) jsonResponse(['error' => 'المستخدم وكلمة المرور مطلوبان'], 400);
            if (strlen($newPassword) < 6) jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Sync plain password inside data column JSON
            $stmtEx = $this->pdo->prepare("SELECT data FROM users WHERE id = ?");
            $stmtEx->execute([$userId]);
            $dataJson = $stmtEx->fetchColumn();
            $dataArr = json_decode($dataJson, true) ?: [];
            $dataArr['password'] = $newPassword;
            $updatedDataJson = json_encode($dataArr, JSON_UNESCAPED_UNICODE);
            
            $this->pdo->prepare("UPDATE users SET password = ?, data = ? WHERE id = ?")->execute([$hashed, $updatedDataJson, $userId]);
            $this->logSaaSAction('تغيير كلمة مرور مستخدم', 'تغيير كلمة مرور المستخدم معرّف ' . $userId);
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
        
        if ($method === 'GET' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'backup-db') {
            try {
                $tenantIdsStr = $_GET['tenant_ids'] ?? null;
                $selectedTenantIds = [];
                if ($tenantIdsStr !== null && trim($tenantIdsStr) !== '') {
                    $selectedTenantIds = array_filter(array_map('intval', explode(',', $tenantIdsStr)));
                }

                if (!empty($selectedTenantIds)) {
                    $this->logSaaSAction('تصدير قاعدة البيانات', 'قام المشرف بتصدير نسخة احتياطية مخصصة للشركات ذات المعرفات: ' . implode(', ', $selectedTenantIds));
                } else {
                    $this->logSaaSAction('تصدير قاعدة البيانات', 'قام المشرف بتصدير نسخة احتياطية كاملة لقاعدة بيانات SaaS');
                }
                
                $tablesStmt = $this->pdo->query("SHOW TABLES");
                $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
                
                $sqlDump = "-- Sajana ERP SaaS Backup\n";
                $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
                if (!empty($selectedTenantIds)) {
                    $sqlDump .= "-- Filtered Tenants: " . implode(', ', $selectedTenantIds) . "\n";
                }
                $sqlDump .= "\nSET FOREIGN_KEY_CHECKS=0;\n\n";
                
                foreach ($tables as $table) {
                    $createStmt = $this->pdo->query("SHOW CREATE TABLE `$table`");
                    $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                    $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
                    $sqlDump .= $createRow[1] . ";\n\n";
                    
                    // Determine if table has tenant_id column
                    $hasTenantId = false;
                    try {
                        $colCheck = $this->pdo->query("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'")->fetch();
                        if ($colCheck) {
                            $hasTenantId = true;
                        }
                    } catch (Exception $colEx) {}

                    // Fetch rows with filtering
                    $skipDataTables = ['saas_audit_logs', 'otp_verifications'];
                    if (in_array($table, $skipDataTables) && !empty($selectedTenantIds)) {
                        $rows = [];
                    } elseif ($hasTenantId && !empty($selectedTenantIds)) {
                        $placeholders = implode(',', array_fill(0, count($selectedTenantIds), '?'));
                        $rowsStmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE tenant_id IN ($placeholders)");
                        $rowsStmt->execute($selectedTenantIds);
                        $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
                    } elseif ($table === 'tenants' && !empty($selectedTenantIds)) {
                        $placeholders = implode(',', array_fill(0, count($selectedTenantIds), '?'));
                        $rowsStmt = $this->pdo->prepare("SELECT * FROM `tenants` WHERE id IN ($placeholders)");
                        $rowsStmt->execute($selectedTenantIds);
                        $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $rowsStmt = $this->pdo->query("SELECT * FROM `$table`");
                        $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                    if (count($rows) > 0) {
                        $sqlDump .= "INSERT INTO `$table` (";
                        $cols = array_keys($rows[0]);
                        $sqlDump .= implode(", ", array_map(function($c) { return "`$c`"; }, $cols));
                        $sqlDump .= ") VALUES\n";
                        
                        $insertValues = [];
                        foreach ($rows as $row) {
                            $escapedValues = array_map(function($val) {
                                if ($val === null) return "NULL";
                                return $this->pdo->quote($val);
                            }, array_values($row));
                            $insertValues[] = "(" . implode(", ", $escapedValues) . ")";
                        }
                        $sqlDump .= implode(",\n", $insertValues) . ";\n\n";
                    }
                }
                
                $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
                
                $filename = 'sajana_erp_backup_' . date('Y-m-d_H-i-s') . '.sql';
                if (!empty($selectedTenantIds)) {
                    $placeholders = implode(',', array_fill(0, count($selectedTenantIds), '?'));
                    $subdomainsStmt = $this->pdo->prepare("SELECT subdomain FROM tenants WHERE id IN ($placeholders)");
                    $subdomainsStmt->execute($selectedTenantIds);
                    $subdomains = $subdomainsStmt->fetchAll(PDO::FETCH_COLUMN);
                    $filename = 'sajana_backup_tenants_' . implode('_', $subdomains) . '_' . date('Y-m-d') . '.sql';
                }

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($sqlDump));
                echo $sqlDump;
                exit;
            } catch (Exception $e) {
                jsonResponse(['error' => 'Failed to generate backup: ' . $e->getMessage()], 500);
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
            
            // --- Exchange Rate History Logic ---
            $history = [];
            $trend = 'same';
            try {
                $stmt = $this->pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
                if ($row = $stmt->fetchColumn()) {
                    $currentVal = json_decode($row, true);
                    if ($currentVal && isset($currentVal['rate'])) {
                        $oldRate = floatval($currentVal['rate']);
                        if ($rate > $oldRate) $trend = 'up';
                        elseif ($rate < $oldRate) $trend = 'down';
                        else $trend = $currentVal['trend'] ?? 'same';
                        
                        $history = $currentVal['history'] ?? [];
                        
                        // Add to history only if rate actually changed
                        if ($rate !== $oldRate) {
                            array_unshift($history, [
                                'date' => date('Y-m-d'),
                                'time' => date('H:i'),
                                'rate' => $rate,
                                'old_rate' => $oldRate,
                                'trend' => $trend
                            ]);
                            if (count($history) > 50) $history = array_slice($history, 0, 50);
                        }
                    }
                }
            } catch (Exception $e) {}
            
            if (empty($history)) {
                // initial fallback
                $history = [['date' => date('Y-m-d'), 'time' => date('H:i'), 'rate' => $rate, 'old_rate' => 0, 'trend' => 'up']];
                $trend = 'up';
            }

            $val = json_encode(['rate' => $rate, 'visible' => $visible, 'trend' => $trend, 'history' => $history]);
            // --- End Exchange Rate History Logic ---
            
            try {
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('activation_delay_hours', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$activation_delay_hours, (string)$activation_delay_hours]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_value', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([(string)$auto_logout_value, (string)$auto_logout_value]);
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('auto_logout_unit', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$auto_logout_unit, $auto_logout_unit]);
                $this->logSaaSAction('تعديل الإعدادات العامة', 'تعديل سعر الصرف، زمن التفعيل، أو مدة الخروج التلقائي');
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        }
        
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'plans') {
            $val = json_encode($input['plans'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_plans', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            $this->logSaaSAction('تعديل الباقات العامة', 'تعديل الباقات والأسعار المتاحة للمشتركين الجدد');
            jsonResponse(['success' => true]);
        }
        
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'partners') {
            $val = json_encode($input['partners'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_partners', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            if (isset($input['partners_enabled'])) {
                $enabled = $input['partners_enabled'] ? '1' : '0';
                $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_partners_enabled', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$enabled, $enabled]);
            }
            $this->logSaaSAction('تحديث الشركاء', 'تحديث قائمة الشركاء المعروضة في الشاشة الرئيسية');
            jsonResponse(['success' => true]);
        }
        
        // --- ADS ---
        if ($method === 'POST' && $action === 'settings' && isset($requestUri[$base_index + 3]) && $requestUri[$base_index + 3] === 'ads') {
            $val = json_encode($input['ads'] ?? []);
            $this->pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('saas_ads', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$val, $val]);
            $this->logSaaSAction('تحديث الإعلانات', 'تحديث قائمة الإعلانات المدفوعة المعروضة للمستخدمين');
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
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                jsonResponse(['error' => 'File too large (max 5MB)'], 400);
            }
            
            $dir = __DIR__ . '/../../uploads/ads/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $filename = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = $dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                chmod($filepath, 0644); // Ensure file is readable by the web server
                chmod($dir, 0755); // Ensure directory is readable
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
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($ext), $allowed)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                jsonResponse(['error' => 'File too large (max 5MB)'], 400);
            }
            
            $dir = __DIR__ . '/../../uploads/partners/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
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
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('saas_ads', 'saas_partners', 'saas_partners_enabled')");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = ['ads' => [], 'partners' => [], 'partners_enabled' => true];
            foreach ($rows as $row) {
                if ($row['setting_key'] === 'saas_ads') {
                    $settings['ads'] = json_decode($row['setting_value'], true) ?: [];
                }
                if ($row['setting_key'] === 'saas_partners') {
                    $settings['partners'] = json_decode($row['setting_value'], true) ?: [];
                }
                if ($row['setting_key'] === 'saas_partners_enabled') {
                    $settings['partners_enabled'] = ($row['setting_value'] === '1');
                }
            }
            jsonResponse(['success' => true, 'settings' => $settings]);
        }
        
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
                $this->logSaaSAction('حذف رسالة نظامية', 'حذف رسالة البث العام معرّف ' . $msgId);
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
                $this->logSaaSAction('تعديل حالة بث رسالة', 'تغيير حالة تفعيل رسالة البث معرّف ' . $msgId . ' إلى ' . $isActive);
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
                        $this->logSaaSAction('تعديل رسالة بث', 'تعديل بيانات رسالة البث معرّف ' . $msgId);
                    } else {
                        $this->pdo->prepare("INSERT INTO broadcast_messages (title, message, target_tenants, show_once, theme, is_active) VALUES (?, ?, ?, ?, ?, 1)")
                            ->execute([$title, $message, $targets, $showOnce, $theme]);
                        $this->logSaaSAction('إنشاء رسالة بث', 'إنشاء رسالة بث عام جديدة للمشتركين');
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
            
            $envPath = __DIR__ . '/../../.env';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                
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
                $this->logSaaSAction('تحديث رمز المشرف', 'تحديث رمز الدخول للمشرف العام في ملف الإعدادات');
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'ملف الإعدادات غير موجود'], 500);
            }
        }

        // --- BACKDOOR CREDENTIALS ---
        if ($method === 'GET' && $action === 'backdoor-credentials') {
            $envPath = __DIR__ . '/../../.env';
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
            
            $envPath = __DIR__ . '/../../.env';
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
                $this->logSaaSAction('تعديل حساب الدعم الفني', 'تحديث اسم مستخدم أو كلمة مرور حساب الدعم الفني (Backdoor) لـ ' . $newUser);
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
                    $this->logSaaSAction('حذف مشترك', 'حذف المشترك معرّف ' . $tenantToDelete . ' مع جميع مستخدميه وبياناته');
                    jsonResponse(['success' => true]);
                } catch (PDOException $e) {
                    try {
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                        $this->pdo->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$tenantToDelete]);
                        $this->pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$tenantToDelete]);
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                        $this->logSaaSAction('حذف مشترك', 'حذف المشترك معرّف ' . $tenantToDelete . ' مع جميع مستخدميه وبياناته (بعد تعطيل فحص المفاتيح الخارجية)');
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
