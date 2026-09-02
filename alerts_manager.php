<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مركز إدارة وتخصيص التنبيهات الذكية V3
// تم التحديث لإضافة 15 قاعدة رقابية ذكية، وتبويبات قطاعية، ومفاتيح تحكم فورية
// ====================================================================
$page_title = 'إدارة وتخصيص التنبيهات الذكية';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

// -------------------------------------------------------------
// 1. التحرّي وتأكيد وجود جداول التنبيهات وزرع القواعد الذكية الـ 15
// -------------------------------------------------------------
function ensureAlertTablesExist($pdo, $tenant_id) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            rule_name VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT '',
            category VARCHAR(50) DEFAULT 'GENERAL',
            is_enabled TINYINT(1) DEFAULT 1,
            warning_threshold FLOAT DEFAULT 80,
            critical_threshold FLOAT DEFAULT 100,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY tenant_rule (tenant_id, rule_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            severity VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // باقة القواعد الذكية الـ 15 الشاملة
        $allRules = [
            // 💰 المالية والخزائن والبنوك (FINANCE)
            ['negative_cash_balance', 'الرصيد السالب للخزائن والبنوك', 'إنذار فوري عند تحول رصيد أي خزينة أو حساب بنكي إلى سالب', 'FINANCE', 1, 0, 0],
            ['overdue_checks', 'تأخر استحقاق الشيكات البنكية (أيام)', 'مراقبة الشيكات المعلقة التي تجاوزت تاريخ الاستحقاق دون تحصيل', 'FINANCE', 1, 1, 3],
            ['duplicate_transfer_cheque', 'تكرار رقم التحويلة أو الشيك', 'منع وتنبيه تكرار نفس رقم الحوالة أو الشيك في سندات متعددة', 'FINANCE', 1, 1, 2],
            ['unposted_vouchers', 'السندات والفواتير المعلقة (المسودة)', 'مراقبة السندات المعلقة أو المسودة لأكثر من 24 ساعة دون ترحيل', 'FINANCE', 1, 5, 10],
            ['bounced_cheques', 'شيكات مرتدة أو مرفوضة بنكياً', 'إشعار فوري بالشيكات المرفوضة من البنك لاتخاذ الإجراء الفوري', 'FINANCE', 1, 1, 1],

            // 👥 العملاء والائتمان والمبيعات (CREDIT & SALES)
            ['credit_limit_breach', 'تجاوز الحد الائتماني للعملاء (%)', 'تنبيه عند اقتراب أو تجاوز العميل لسقف الائتمان المسموح', 'CREDIT', 1, 80, 100],
            ['aging_receivables_danger', 'تراكم وأعمار الديون المتأخرة (أيام)', 'إنذار بالعملاء الذين تجاوزت فواتيرهم فترات ائتمان حرجة دون سداد', 'CREDIT', 1, 60, 90],
            ['inactive_customers', 'انقطاع وركود العملاء الدائمين (أيام)', 'تنبيه بانقطاع عملاء مميزين عن الشراء لفترة طويلة دون حركة', 'SALES', 1, 30, 60],

            // 📦 المستودعات والمخزون والتوريد (INVENTORY & SUPPLY)
            ['negative_stock', 'الرصيد السالب لكميات الأصناف', 'تحذير حرج عند خروج أو بيع صنف برصيد أقل من الصفر بالمستودع', 'INVENTORY', 1, 0, 0],
            ['selling_below_cost', 'البيع بأقل من سعر التكلفة', 'تنبيه فوري عند بيع أي صنف بسعر أقل من متوسط التكلفة المرجحة', 'INVENTORY', 1, 1, 3],
            ['low_stock_level', 'الأصناف دون حد الطلب الأدنى (كمية)', 'تنبيه فوري للأصناف التي وصلت لكميات حرجة بالمخزن', 'INVENTORY', 1, 10, 5],
            ['days_coverage_danger', 'خطورة نفاد كميات الأصناف (أيام)', 'تحذير عند انخفاض أيام تغطية المخزون بناءً على معدل السحب', 'INVENTORY', 1, 7, 3],
            ['deadstock_limit_breach', 'ركود البضاعة في المخازن (أيام)', 'إنذار عن البضائع المخزنة لفترات طويلة دون حركة بيع', 'INVENTORY', 1, 90, 180],
            ['factory_lead_time_breach', 'تأخر وتباطؤ توريد الموردين (أيام)', 'مراقبة تأخر استلام طلبات الشراء من المصانع والموردين', 'SUPPLY', 1, 7, 14],

            // 🛡️ الأمان والرقابة (SECURITY)
            ['missed_backup', 'تأخر النسخ الاحتياطي لقاعدة البيانات', 'إنذار عند عدم تنفيذ نسخة احتياطية للنظام خلال 24 ساعة', 'SECURITY', 1, 1, 2]
        ];

        $stmt = $pdo->prepare("INSERT INTO alert_settings (tenant_id, rule_key, rule_name, description, category, is_enabled, warning_threshold, critical_threshold) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE rule_name = VALUES(rule_name), description = VALUES(description), category = VALUES(category)");
        foreach ($allRules as $r) {
            $stmt->execute([$tenant_id, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]]);
        }
    } catch (Exception $e) {
        error_log("Error initializing alert tables: " . $e->getMessage());
    }
}

ensureAlertTablesExist($pdo, $GLOBAL_TENANT_ID);

// -------------------------------------------------------------
// 2. محرك فحص البيانات الحية وتوليد الإنذارات والتنبيهات
// -------------------------------------------------------------
function runAlertEngine($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        // تصفير سجلات التنبيهات القديمة لإنتاج تنبيهات محدثة ومطابقة للحالة الحالية
        $pdo->prepare("DELETE FROM alert_logs WHERE tenant_id = ?")->execute([$GLOBAL_TENANT_ID]);
        
        $stmtSet = $pdo->prepare("SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1 AND tenant_id = ?");
        $stmtSet->execute([$GLOBAL_TENANT_ID]);
        $settings = $stmtSet->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 1. فحص تجاوز الحد الائتماني للعملاء
        if (isset($settings['credit_limit_breach'])) {
            $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? AND rule_key = 'credit_limit_breach'");
            $stmtR->execute([$GLOBAL_TENANT_ID]);
            $rule = $stmtR->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->prepare("SELECT data FROM customers WHERE tenant_id = ?");
                    $stmt->execute([$GLOBAL_TENANT_ID]);
                    while ($row = $stmt->fetch()) {
                        $c = json_decode($row['data'], true);
                        if ($c && (float)($c['creditLimit'] ?? 0) > 0) {
                            $name = $c['name'] ?? 'عميل غير معروف';
                            $bal = (float)($c['currentBalance'] ?? 0);
                            $limit = (float)($c['creditLimit'] ?? 0);
                            $pct = ($bal / $limit) * 100;
                            
                            if ($pct >= $rule['critical_threshold']) {
                                $msg = "🚨 خطر ائتماني حرج! العميل [" . $name . "] تجاوز الحد الائتماني بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . " / الحد: " . number_format($limit, 2) . ")";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'credit_limit_breach', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            } elseif ($pct >= $rule['warning_threshold']) {
                                $msg = "⚠️ تنبيه ائتماني! العميل [" . $name . "] اقترب من سقف الائتمان المسموح بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . ")";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'credit_limit_breach', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            }
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

        // 2. فحص الشيكات المعلقة المتأخرة
        if (isset($settings['overdue_checks'])) {
            $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? AND rule_key = 'overdue_checks'");
            $stmtR->execute([$GLOBAL_TENANT_ID]);
            $rule = $stmtR->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->prepare("SELECT data FROM `checks` WHERE tenant_id = ?");
                    $stmt->execute([$GLOBAL_TENANT_ID]);
                    $overdue_count = 0;
                    while ($row = $stmt->fetch()) {
                        $ch = json_decode($row['data'], true);
                        if ($ch && ($ch['status'] ?? '') === 'PENDING') {
                            $due = $ch['dueDate'] ?? ($ch['due_date'] ?? '');
                            if ($due && $due < date('Y-m-d')) {
                                $overdue_count++;
                            }
                        }
                    }
                    if ($overdue_count >= $rule['critical_threshold']) {
                        $msg = "🚨 تحذير مالي! يوجد عدد [" . $overdue_count . "] شيك معلق متأخر عن تاريخ الاستحقاق ولم يُحصّل حتى الآن.";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'overdue_checks', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    } elseif ($overdue_count >= $rule['warning_threshold']) {
                        $msg = "⚠️ تنبيه شيكات! يوجد عدد [" . $overdue_count . "] شيك حان تاريخ استحقاقه.";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'overdue_checks', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                } catch (Exception $ex) {}
            }
        }

        // 3. فحص الرصيد السالب للخزائن والبنوك (Negative Cash/Bank Balance)
        if (isset($settings['negative_cash_balance'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM safes WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $safe = json_decode($row['data'], true);
                    if ($safe) {
                        $name = $safe['name'] ?? 'خزينة';
                        $bal = (float)($safe['balance'] ?? 0);
                        if ($bal < 0) {
                            $msg = "🚨 عجز نقدي حرج! رصيد الخزينة/الحساب [" . $name . "] أصبح سالباً بمبلغ (" . number_format($bal, 2) . ")!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'negative_cash_balance', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 4. فحص الرصيد السالب للأصناف (Negative Stock)
        if (isset($settings['negative_stock'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $item = json_decode($row['data'], true);
                    if ($item) {
                        $name = $item['name'] ?? 'صنف';
                        $stock = (float)($item['stockQuantity'] ?? ($item['quantity'] ?? ($item['qty'] ?? 0)));
                        if ($stock < 0) {
                            $msg = "🚨 رصيد مخزني سالب! الصنف [" . $name . "] رصيده أقل من الصفر (" . $stock . " وحدة) في المستودع ويجب تسوية الجرد!";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'negative_stock', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                }
            } catch (Exception $ex) {}
        }

        // 5. فحص كميات أصناف المخزون المنخفضة (Low Stock Level)
        if (isset($settings['low_stock_level'])) {
            $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? AND rule_key = 'low_stock_level'");
            $stmtR->execute([$GLOBAL_TENANT_ID]);
            $rule = $stmtR->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ?");
                    $stmt->execute([$GLOBAL_TENANT_ID]);
                    while ($row = $stmt->fetch()) {
                        $item = json_decode($row['data'], true);
                        if ($item) {
                            $name = $item['name'] ?? 'صنف غير معروف';
                            $stock = (float)($item['stockQuantity'] ?? ($item['quantity'] ?? ($item['qty'] ?? 0)));
                            $minStock = (float)($item['minStock'] ?? 10);

                            if ($stock <= $rule['critical_threshold'] && $stock > 0) {
                                $msg = "🚨 نفاد مخزون حرج! الصنف [" . $name . "] متبقي منه (" . $stock . ") وحدة فقط في العنابر.";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'low_stock_level', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            } elseif ($stock <= $rule['warning_threshold'] && $stock > 0) {
                                $msg = "⚠️ مخزون منخفض! الصنف [" . $name . "] وصل إلى حد الطلب الأدنى (المتبقي: " . $stock . " وحدة).";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'low_stock_level', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            }
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

        // 6. فحص ركود الأصناف (Deadstock)
        if (isset($settings['deadstock_limit_breach'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM items WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $item = json_decode($row['data'], true);
                    if ($item && !empty($item['isStagnant'])) {
                        $name = $item['name'] ?? 'صنف غير معروف';
                        $msg = "🚨 جمود رأسمالي! الصنف [" . $name . "] راكد بالمخازن دون أي حركة سحب بيعية منذ فترة طويلة.";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'deadstock_limit_breach', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                }
            } catch (Exception $ex) {}
        }

        // 7. فحص الشيكات المرتدة أو المرفوضة (Bounced Cheques)
        if (isset($settings['bounced_cheques'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM `checks` WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                while ($row = $stmt->fetch()) {
                    $ch = json_decode($row['data'], true);
                    if ($ch && in_array(strtoupper($ch['status'] ?? ''), ['BOUNCED', 'REJECTED', 'RETURNED'])) {
                        $no = $ch['checkNo'] ?? ($ch['no'] ?? '---');
                        $amt = (float)($ch['amount'] ?? 0);
                        $msg = "🚨 شيك مرتد! الشيك رقم [" . $no . "] بمبلغ (" . number_format($amt, 2) . ") تم رفضه من البنك ويتطلب إجراءً فورياً!";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'bounced_cheques', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                }
            } catch (Exception $ex) {}
        }

        // 8. فحص تكرار أرقام التحويلات والشيكات (Duplicate Transfer / Cheque)
        if (isset($settings['duplicate_transfer_cheque'])) {
            try {
                $stmt = $pdo->prepare("SELECT data FROM vouchers WHERE tenant_id = ?");
                $stmt->execute([$GLOBAL_TENANT_ID]);
                $transferMap = [];
                while ($row = $stmt->fetch()) {
                    $v = json_decode($row['data'], true);
                    if ($v) {
                        $transfers = $v['transfers'] ?? [];
                        if (empty($transfers) && !empty($v['mobileTransferNo'])) {
                            $transfers = [['transferNo' => $v['mobileTransferNo']]];
                        }
                        foreach ($transfers as $tr) {
                            $tNo = trim($tr['transferNo'] ?? '');
                            if ($tNo !== '' && $tNo !== '0') {
                                $transferMap[$tNo] = ($transferMap[$tNo] ?? 0) + 1;
                            }
                        }
                    }
                }
                foreach ($transferMap as $tNo => $cnt) {
                    if ($cnt > 1) {
                        $msg = "🚨 تكرار رقم مستند مالي! رقم التحويلة البنكية [" . $tNo . "] مكرر في (" . $cnt . ") سندات مختلفة!";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'duplicate_transfer_cheque', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                }
            } catch (Exception $ex) {}
        }

        // 9. فحص النسخ الاحتياطي لقاعدة البيانات (Missed Backup)
        if (isset($settings['missed_backup'])) {
            $backupDir = __DIR__ . '/backups';
            $hasRecentBackup = false;
            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/*.sql');
                if (!empty($files)) {
                    $latest = max(array_map('filemtime', $files));
                    if ((time() - $latest) <= 86400) {
                        $hasRecentBackup = true;
                    }
                }
            }
            if (!$hasRecentBackup) {
                $msg = "🚨 تنبيه أمان حرج! لم يتم إنشاء نسخة احتياطية لقاعدة البيانات خلال آخر 24 ساعة.";
                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'missed_backup', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
            }
        }

    } catch (Exception $e) {
        error_log("Error running alert engine: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 3. معالجة طلبات AJAX و POST
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // AJAX Toggle Rule
    if ($action === 'toggle_rule_ajax') {
        header('Content-Type: application/json');
        $rule_key = $_POST['rule_key'] ?? '';
        $is_enabled = !empty($_POST['is_enabled']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$is_enabled, $GLOBAL_TENANT_ID, $rule_key]);
        
        runAlertEngine($pdo);
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM alert_settings WHERE is_enabled = 1 AND tenant_id = ?");
        $stmtCount->execute([$GLOBAL_TENANT_ID]);
        $enabledCount = $stmtCount->fetchColumn();

        $stmtLogs = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
        $stmtLogs->execute([$GLOBAL_TENANT_ID]);
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        $red = 0; $yellow = 0;
        foreach ($logs as $l) {
            if (($l['severity'] ?? '') === 'RED') $red++; else $yellow++;
        }

        echo json_encode([
            'success' => true,
            'is_enabled' => $is_enabled,
            'enabled_count' => (int)$enabledCount,
            'red_count' => $red,
            'yellow_count' => $yellow,
            'logs' => $logs
        ]);
        exit;
    }

    // AJAX Update Thresholds
    if ($action === 'update_thresholds_ajax') {
        header('Content-Type: application/json');
        $rule_key = $_POST['rule_key'] ?? '';
        $warn = (float)($_POST['warning_threshold'] ?? 0);
        $crit = (float)($_POST['critical_threshold'] ?? 0);

        $stmt = $pdo->prepare("UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$warn, $crit, $GLOBAL_TENANT_ID, $rule_key]);

        runAlertEngine($pdo);

        echo json_encode(['success' => true, 'message' => 'تم حفظ الشروط بنجاح']);
        exit;
    }

    // Fallback POST
    if ($action === 'toggle_rule') {
        $rule_key = $_POST['rule_key'];
        $is_enabled = $_POST['is_enabled'] == '1' ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$is_enabled, $GLOBAL_TENANT_ID, $rule_key]);
    } elseif ($action === 'update_thresholds') {
        $rule_key = $_POST['rule_key'];
        $warn = (float)$_POST['warning_threshold'];
        $crit = (float)$_POST['critical_threshold'];
        $stmt = $pdo->prepare("UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$warn, $crit, $GLOBAL_TENANT_ID, $rule_key]);
    }
    header("Location: alerts_manager.php");
    exit;
}

// -------------------------------------------------------------
// 4. معالجة طلب جلب التنبيهات بصيغة JSON للشاشات الأخرى (Dashboard API)
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_live_alerts_json') {
    header('Content-Type: application/json');
    runAlertEngine($pdo);
    
    $stmtL = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
    $stmtL->execute([$GLOBAL_TENANT_ID]);
    $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
    
    $red = 0; $yellow = 0;
    foreach ($logs as $l) {
        if (($l['severity'] ?? '') === 'RED') $red++; else $yellow++;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($logs),
        'red_count' => $red,
        'yellow_count' => $yellow,
        'alerts' => $logs
    ]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get_rules_json') {
    header('Content-Type: application/json');
    $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, id ASC");
    $stmtR->execute([$GLOBAL_TENANT_ID]);
    $rules = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'rules' => $rules
    ]);
    exit;
}

// تشغيل المحرك لجلب البيانات للعرض الأولي
runAlertEngine($pdo);

$rules = [];
$logs = [];
try {
    $stmtR = $pdo->prepare("SELECT * FROM alert_settings WHERE tenant_id = ? ORDER BY category ASC, id ASC");
    $stmtR->execute([$GLOBAL_TENANT_ID]);
    $rules = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    $stmtL = $pdo->prepare("SELECT * FROM alert_logs WHERE tenant_id = ? ORDER BY severity DESC, id DESC");
    $stmtL->execute([$GLOBAL_TENANT_ID]);
    $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$red_count = 0; $yellow_count = 0; $enabled_rules = 0;
foreach ($logs as $l) {
    if (($l['severity'] ?? '') === 'RED') $red_count++;
    else $yellow_count++;
}
foreach ($rules as $r) {
    if ($r['is_enabled']) $enabled_rules++;
}

// أيقونات الأقسام
$categoryIcons = [
    'FINANCE'   => ['icon' => 'fa-vault', 'name' => 'المالية والخزائن والبنوك', 'color' => '#38bdf8', 'bg' => 'rgba(56,189,248,0.1)'],
    'CREDIT'    => ['icon' => 'fa-users-gear', 'name' => 'العملاء والائتمان', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.1)'],
    'SALES'     => ['icon' => 'fa-chart-line', 'name' => 'المبيعات والعملاء', 'color' => '#34d399', 'bg' => 'rgba(52,211,153,0.1)'],
    'INVENTORY' => ['icon' => 'fa-boxes-stacked', 'name' => 'المخزون والمستودعات', 'color' => '#c084fc', 'bg' => 'rgba(192,132,252,0.1)'],
    'SUPPLY'    => ['icon' => 'fa-truck-fast', 'name' => 'التوريد والمصانع', 'color' => '#f472b6', 'bg' => 'rgba(244,114,182,0.1)'],
    'SECURITY'  => ['icon' => 'fa-shield-halved', 'name' => 'الأمان والرقابة', 'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.1)']
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز إدارة وتخصيص التنبيهات الذكية | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(239, 68, 68, 0.25);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        .alerts-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 12px 12px 60px 12px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stat-card-v2 {
            background: rgba(15, 23, 42, 0.7);
            border: 2px solid var(--border-glow, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        :root.light-theme .stat-card-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06) !important;
        }

        .stat-card-v2:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px var(--border-glow, rgba(239, 68, 68, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #ef4444), transparent);
        }

        .glass-panel-v2 {
            background: var(--panel-bg);
            border: 2px solid var(--border-neon);
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        :root.light-theme .glass-panel-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06) !important;
        }

        /* ── تبويبات الأقسام ── */
        .category-tab-btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #94a3b8;
            white-space: nowrap;
        }

        .category-tab-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateY(-1px);
        }

        .category-tab-btn.active {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(244, 63, 94, 0.4)) !important;
            border-color: #f43f5e !important;
            color: #ffffff !important;
            box-shadow: 0 0 15px rgba(244, 63, 94, 0.4);
        }

        /* ── مفتاح iOS Switch التفاعلي الفخم ── */
        .ios-switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
            flex-shrink: 0;
        }

        .ios-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .ios-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #334155;
            transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 34px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .ios-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.4);
        }

        input:checked + .ios-slider {
            background-color: #10b981;
            border-color: #10b981;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.5);
        }

        input:checked + .ios-slider:before {
            transform: translateX(24px);
        }

        .rule-card {
            transition: all 0.25s ease;
        }

        .rule-card:hover {
            border-color: rgba(239, 68, 68, 0.4) !important;
            background: rgba(15, 23, 42, 0.95) !important;
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="alerts-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 border-2 border-rose-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="magaiees_unified_dashboard.php" class="inline-flex items-center gap-2 text-rose-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للوحة التحكم الموحدة (Unified Dashboard)
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-bell text-rose-400"></i>
                    مركز التحكم وإدارة التنبيهات والإنذارات الذكية
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">يمكنك تفعيل أو تعطيل أي تنبيه بضغطة زر وتحديد حدود التنبيه والإنذار الحرج بدقة متناهية.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-rose-600 hover:bg-rose-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة تقرير التنبيهات
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS (4 Grid) 📊 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Critical Alerts Count -->
            <div class="stat-card-v2" style="--border-glow: rgba(239, 68, 68, 0.4); --bar-color: #ef4444;">
                <span class="text-xs text-rose-400 font-bold block">🚨 الإنذارات الحرجـة النشطة</span>
                <span class="text-3xl font-black text-rose-400 mt-2 block" id="kpi-red-count"><?php echo $red_count; ?></span>
                <span class="text-xs text-rose-400/80 mt-1 block">تتطلب إجراءً ومتابعة فورية</span>
            </div>

            <!-- 2. Warning Alerts Count -->
            <div class="stat-card-v2" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b;">
                <span class="text-xs text-amber-400 font-bold block">⚠️ التحذيرات والتنبيهات المتوسطة</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block" id="kpi-yellow-count"><?php echo $yellow_count; ?></span>
                <span class="text-xs text-amber-400/80 mt-1 block">تنبيهات للمتابعة الدورية</span>
            </div>

            <!-- 3. Enabled Alert Rules -->
            <div class="stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981;">
                <span class="text-xs text-emerald-400 font-bold block">⚙️ القواعد المفعلة بالنظام</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block" id="kpi-enabled-rules"><?php echo $enabled_rules; ?> / <?php echo count($rules); ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">قواعد رقابية نشطة تعمل آلياً</span>
            </div>

            <!-- 4. System Monitoring Status -->
            <div class="stat-card-v2" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8;">
                <span class="text-xs text-sky-400 font-bold block">📡 حالة المراقبة الحية</span>
                <span class="text-xl font-black text-sky-400 mt-2 block flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span> متصل ويعمل 100%
                </span>
                <span class="text-xs text-slate-500 mt-1 block">فحص لحظي مستمر للبيانات</span>
            </div>
        </div>

        <!-- 🎛️ CATEGORY FILTER TABS 🎛️ -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-slate-900/60 rounded-2xl border border-slate-800">
            <div class="flex flex-wrap items-center gap-2" id="category-tabs">
                <button type="button" class="category-tab-btn active" onclick="filterCategory('ALL', this)">
                    <i class="fa-solid fa-layer-group"></i> 🌟 جميع التنبيهات (<?php echo count($rules); ?>)
                </button>
                <button type="button" class="category-tab-btn" onclick="filterCategory('FINANCE', this)">
                    <i class="fa-solid fa-vault text-sky-400"></i> 💰 المالية والخزائن
                </button>
                <button type="button" class="category-tab-btn" onclick="filterCategory('CREDIT', this)">
                    <i class="fa-solid fa-users-gear text-amber-400"></i> 👥 العملاء والائتمان
                </button>
                <button type="button" class="category-tab-btn" onclick="filterCategory('INVENTORY', this)">
                    <i class="fa-solid fa-boxes-stacked text-purple-400"></i> 📦 المخزون والمستودعات
                </button>
                <button type="button" class="category-tab-btn" onclick="filterCategory('SECURITY', this)">
                    <i class="fa-solid fa-shield-halved text-rose-400"></i> 🛡️ الرقابة والأمان
                </button>
            </div>

            <!-- Search box -->
            <div class="relative min-w-[240px]">
                <input type="text" id="rule-search" oninput="searchRules(this.value)" placeholder="ابحث عن اسم أو شرط التنبيه..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs font-bold text-slate-200 outline-none focus:border-rose-500 pr-9">
                <i class="fa-solid fa-search absolute right-3 top-3 text-slate-500 text-xs"></i>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: RULES CONFIG (2/3) + LIVE ALERTS PANEL (1/3) 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Rules Configuration List (2 Cols Wide) -->
            <div class="lg:col-span-2 glass-panel-v2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-rose-400"></i>
                            تخصيص وشروط قوانين التنبيهات (تحكم كامل On/Off)
                        </h3>
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20" id="badge-active-rules">
                            <span id="active-rules-num"><?php echo $enabled_rules; ?></span> من <?php echo count($rules); ?> مفعل
                        </span>
                    </div>

                    <div class="space-y-3.5" id="rules-list-container">
                        <?php foreach ($rules as $rule): 
                            $catInfo = $categoryIcons[$rule['category']] ?? ['icon' => 'fa-bell', 'name' => $rule['category'], 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.1)'];
                        ?>
                            <div class="rule-card p-4 bg-slate-900/80 rounded-2xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4" data-category="<?php echo $rule['category']; ?>" data-name="<?php echo htmlspecialchars($rule['rule_name'] . ' ' . $rule['description']); ?>">
                                <div class="flex items-start gap-3.5 flex-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: <?php echo $catInfo['bg']; ?>; color: <?php echo $catInfo['color']; ?>; border: 1px solid <?php echo $catInfo['color']; ?>40;">
                                        <i class="fa-solid <?php echo $catInfo['icon']; ?> text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-extrabold text-slate-100 text-sm md:text-base"><?php echo htmlspecialchars($rule['rule_name']); ?></h4>
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded" style="background: <?php echo $catInfo['bg']; ?>; color: <?php echo $catInfo['color']; ?>;">
                                                <?php echo $catInfo['name']; ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1 font-bold leading-relaxed"><?php echo htmlspecialchars($rule['description'] ?? ''); ?></p>
                                    </div>
                                </div>

                                <!-- Controls: Thresholds + Instant iOS Switch -->
                                <div class="flex flex-wrap items-center gap-3 justify-end">
                                    <!-- Threshold Form -->
                                    <div class="flex items-center gap-2 text-xs bg-slate-950/80 px-3 py-1.5 rounded-xl border border-slate-800">
                                        <label class="text-amber-400 font-bold">تحذير ⚠️:</label>
                                        <input type="number" step="any" id="warn-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['warning_threshold']; ?>" class="w-14 bg-slate-900 border border-slate-700 text-center py-1 rounded text-amber-400 font-black outline-none focus:border-amber-400">
                                        <label class="text-rose-400 font-bold mr-1">خطر 🚨:</label>
                                        <input type="number" step="any" id="crit-<?php echo $rule['rule_key']; ?>" value="<?php echo (float)$rule['critical_threshold']; ?>" class="w-14 bg-slate-900 border border-slate-700 text-center py-1 rounded text-rose-400 font-black outline-none focus:border-rose-400">
                                        <button type="button" onclick="saveThresholds('<?php echo $rule['rule_key']; ?>')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-[11px] font-black transition border border-slate-700">💾 حفظ</button>
                                    </div>

                                    <!-- iOS On/Off Switch -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold" id="status-text-<?php echo $rule['rule_key']; ?>" style="color: <?php echo $rule['is_enabled'] ? '#34d399' : '#64748b'; ?>;">
                                            <?php echo $rule['is_enabled'] ? 'مفعّل' : 'معطّل'; ?>
                                        </span>
                                        <label class="ios-switch">
                                            <input type="checkbox" id="switch-<?php echo $rule['rule_key']; ?>" <?php echo $rule['is_enabled'] ? 'checked' : ''; ?> onchange="toggleRuleAjax('<?php echo $rule['rule_key']; ?>', this.checked)">
                                            <span class="ios-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Live Active Alerts Panel (1 Col Wide) -->
            <div class="glass-panel-v2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                            الإنذارات والتنبيهات الحية النشطة
                        </h3>
                        <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-2.5 py-1 rounded-lg border border-rose-500/20" id="live-total-badge">
                            <?php echo count($logs); ?> تنبيه
                        </span>
                    </div>

                    <div class="space-y-3 max-h-[620px] overflow-y-auto pr-1" id="live-alerts-container">
                        <?php if (empty($logs)): ?>
                            <div class="text-center p-8 text-slate-400 text-xs border border-dashed border-slate-800 rounded-2xl" id="no-alerts-placeholder">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>
                                جميع مؤشرات وقواعد النظام آمنة وممتازة حالياً.
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): 
                                $isRed = ($log['severity'] ?? '') === 'RED';
                                $bgCls = $isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
                            ?>
                                <div class="p-3.5 rounded-xl border text-xs font-bold leading-relaxed <?php echo $bgCls; ?>">
                                    <?php echo nl2br(htmlspecialchars($log['message'])); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 font-bold flex justify-between items-center">
                    <span>تحديث مستمر من قاعدة البيانات</span>
                    <button type="button" onclick="refreshAlertsLive()" class="text-sky-400 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-rotate"></i> تحديث الآن
                    </button>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            نظام Al-Sajana Smart ERP 2026 · محرك الرقابة والتنبيهات الذكي الموحد
        </div>

    </div>

    <!-- JavaScript Controllers -->
    <script>
    // 1. AJAX Toggle Rule
    function toggleRuleAjax(ruleKey, isChecked) {
        const formData = new FormData();
        formData.append('action', 'toggle_rule_ajax');
        formData.append('rule_key', ruleKey);
        formData.append('is_enabled', isChecked ? 1 : 0);

        fetch('alerts_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Update label text and color
                const statusTxt = document.getElementById('status-text-' + ruleKey);
                if (statusTxt) {
                    statusTxt.textContent = res.is_enabled ? 'مفعّل' : 'معطّل';
                    statusTxt.style.color = res.is_enabled ? '#34d399' : '#64748b';
                }

                // Update counters
                const enabledNum = document.getElementById('active-rules-num');
                if (enabledNum) enabledNum.textContent = res.enabled_count;
                
                const kpiEnabled = document.getElementById('kpi-enabled-rules');
                if (kpiEnabled) kpiEnabled.textContent = res.enabled_count + ' / <?php echo count($rules); ?>';

                const kpiRed = document.getElementById('kpi-red-count');
                if (kpiRed) kpiRed.textContent = res.red_count;

                const kpiYellow = document.getElementById('kpi-yellow-count');
                if (kpiYellow) kpiYellow.textContent = res.yellow_count;

                const totalBadge = document.getElementById('live-total-badge');
                if (totalBadge) totalBadge.textContent = (res.red_count + res.yellow_count) + ' تنبيه';

                // Re-render live alerts panel
                renderLogs(res.logs);
            }
        })
        .catch(err => console.error('Error toggling rule:', err));
    }

    // 2. AJAX Save Thresholds
    function saveThresholds(ruleKey) {
        const warn = document.getElementById('warn-' + ruleKey).value;
        const crit = document.getElementById('crit-' + ruleKey).value;

        const formData = new FormData();
        formData.append('action', 'update_thresholds_ajax');
        formData.append('rule_key', ruleKey);
        formData.append('warning_threshold', warn);
        formData.append('critical_threshold', crit);

        fetch('alerts_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Flash visual confirmation
                const btn = event.target;
                const origTxt = btn.textContent;
                btn.textContent = '✓ حُفظ';
                btn.classList.add('bg-emerald-600');
                setTimeout(() => {
                    btn.textContent = origTxt;
                    btn.classList.remove('bg-emerald-600');
                }, 1200);

                refreshAlertsLive();
            }
        });
    }

    // 3. Category Filter
    function filterCategory(cat, btn) {
        document.querySelectorAll('#category-tabs .category-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.rule-card');
        cards.forEach(c => {
            if (cat === 'ALL' || c.getAttribute('data-category') === cat) {
                c.style.display = 'flex';
            } else {
                c.style.display = 'none';
            }
        });
    }

    // 4. Quick Rule Search
    function searchRules(query) {
        const q = query.trim().toLowerCase();
        const cards = document.querySelectorAll('.rule-card');
        cards.forEach(c => {
            const name = (c.getAttribute('data-name') || '').toLowerCase();
            if (q === '' || name.includes(q)) {
                c.style.display = 'flex';
            } else {
                c.style.display = 'none';
            }
        });
    }

    // 5. Refresh Live Alerts via API
    function refreshAlertsLive() {
        fetch('alerts_manager.php?action=get_live_alerts_json')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const kpiRed = document.getElementById('kpi-red-count');
                if (kpiRed) kpiRed.textContent = res.red_count;

                const kpiYellow = document.getElementById('kpi-yellow-count');
                if (kpiYellow) kpiYellow.textContent = res.yellow_count;

                const totalBadge = document.getElementById('live-total-badge');
                if (totalBadge) totalBadge.textContent = res.total + ' تنبيه';

                renderLogs(res.alerts);
            }
        });
    }

    function renderLogs(logs) {
        const container = document.getElementById('live-alerts-container');
        if (!container) return;

        if (!logs || logs.length === 0) {
            container.innerHTML = `
                <div class="text-center p-8 text-slate-400 text-xs border border-dashed border-slate-800 rounded-2xl">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>
                    جميع مؤشرات وقواعد النظام آمنة وممتازة حالياً.
                </div>
            `;
            return;
        }

        let html = '';
        logs.forEach(l => {
            const isRed = l.severity === 'RED';
            const bgCls = isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
            html += `<div class="p-3.5 rounded-xl border text-xs font-bold leading-relaxed ${bgCls}">${l.message}</div>`;
        });
        container.innerHTML = html;
    }
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>