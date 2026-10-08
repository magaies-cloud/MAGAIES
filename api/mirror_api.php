<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 * 🪞 UNIVERSAL MIRROR SYNCHRONIZATION & NUMBERING API (محرك المرآة الموحد)
 * ────────────────────────────────────────────────────────────────────────────
 * - يربط ويدمج قاعدة بيانات MariaDB وشاشات اللابتوب والديسكتوب والجوال والتابلت
 * - نظام المرآة التبادلي الدقيق (Mirror System): أي إذن أو حركة على جهاز تظهر فوراً
 *   على الجهاز الآخر وبنفس التسلسل اللحظي الدقيق (547 -> 548 -> 549).
 * - يدعم:
 *   1. المبيعات (فواتير وأذونات المبيعات)
 *   2. المشتريات (فواتير وأذونات الشراء)
 *   3. الحركات المالية (سندات الصرف والتحويلات)
 *   4. تسليم واستلام المبيعات (Delivery Notes)
 *   5. استلام وتوريد المشتريات (Goods Receipt Notes)
 *   6. تقسيم المبيعات وتقسيم المشتريات (Splits)
 *   7. التقارير الاستراتيجية ومؤشرات الأداء اللحظية
 * ════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Id, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';

// ── تحديد الشركة (Tenant) بدقة ومطابقة تامة بين الجوال والكمبيوتر ──
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$tenant_id = null;

// 1. فحص رمز المصادقة المباشر
$token = $_COOKIE['saas_token'] ?? ($_COOKIE['authToken'] ?? '');
if (empty($token) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = trim(preg_replace('/^Bearer\s+/i', '', $_SERVER['HTTP_AUTHORIZATION']));
}
if (empty($token) && !empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
    $token = trim($_SERVER['HTTP_X_AUTH_TOKEN']);
}

if (!empty($token) && strpos($token, 'offline_token_') !== 0) {
    try {
        $stmtToken = $pdo->prepare("SELECT tenant_id FROM users WHERE token = ? AND (token_expires_at IS NULL OR token_expires_at > NOW()) AND status = 'active' LIMIT 1");
        $stmtToken->execute([$token]);
        $t = $stmtToken->fetchColumn();
        if ($t) $tenant_id = (int)$t;
    } catch (Exception $e) {}
}

// 2. جلسة المستخدم
if (!$tenant_id && !empty($_SESSION['user']['tenant_id'])) {
    $tenant_id = (int)$_SESSION['user']['tenant_id'];
}

// 3. الترويسة أو المعامل الصريح
if (!$tenant_id && !empty($_SERVER['HTTP_X_TENANT_ID']) && is_numeric($_SERVER['HTTP_X_TENANT_ID'])) {
    $tenant_id = (int)$_SERVER['HTTP_X_TENANT_ID'];
} elseif (!$tenant_id && !empty($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
    $tenant_id = (int)$_GET['tenant_id'];
} elseif (!$tenant_id && !empty($_POST['tenant_id']) && is_numeric($_POST['tenant_id'])) {
    $tenant_id = (int)$_POST['tenant_id'];
}

// 4. الافتراضي الموحد للشركة النشطة (Company 'c' = 2)
if (!$tenant_id) {
    $tenant_id = 2; // القيمة الموحدة لكافة أجهزة المحل
}

if (php_sapi_name() === 'cli' && !empty($_SERVER['argv'])) {
    for ($i = 1; $i < count($_SERVER['argv']); $i++) {
        parse_str($_SERVER['argv'][$i], $parsedArg);
        $_GET = array_merge($_GET, $parsedArg);
    }
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: $_POST;

try {
    switch ($action) {

        // ═════════════════════════════════════════════════════════════════
        // 1. توليد الرقم التسلسلي التالي الموحد لحظياً (Next Sequence Number)
        // ═════════════════════════════════════════════════════════════════
        case 'get_next_no':
            $entity = strtolower(trim($_GET['entity'] ?? ($input['entity'] ?? 'sales')));
            $now = new DateTime('now');
            $yy = $now->format('y');
            $mm = $now->format('m');
            $yymm = $yy . $mm;

            if ($entity === 'sales' || $entity === 'sale') {
                $prefix = 'S' . $yymm;
                $maxSeq = 0;
                $maxPureNumber = 0;

                // فحص كافة الفواتير للشركة
                $stmt = $pdo->prepare("SELECT invoice_no, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $invNo = trim((string)($row['invoice_no'] ?? ''));
                    $d = json_decode($row['data'] ?? '{}', true);
                    $jsonInvNo = trim((string)($d['invoiceNo'] ?? ($d['id'] ?? '')));

                    foreach ([$invNo, $jsonInvNo] as $targetNo) {
                        if ($targetNo === '' || $targetNo === 'preliminary' || strpos($targetNo, 'PRE-') === 0) continue;

                        // نمط 1: S + yymm + 4 أرقام (S26100547)
                        if (strpos($targetNo, $prefix) === 0) {
                            $seq = (int)substr($targetNo, strlen($prefix));
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        } elseif (preg_match('/^S\d{4}(\d{4,})$/', $targetNo, $m)) {
                            // بادئة شهر مختلف
                            $seq = (int)$m[1];
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        }

                        // نمط 2: رقم صحيح صريح (مثل 547 أو INV-547)
                        if (is_numeric($targetNo)) {
                            $pNum = (int)$targetNo;
                            if ($pNum > $maxPureNumber && $pNum < 20000000) $maxPureNumber = $pNum;
                        } elseif (preg_match('/(?:INV|SAL|SALE)[-_]?(\d+)/i', $targetNo, $pm)) {
                            $pNum = (int)$pm[1];
                            if ($pNum > $maxPureNumber && $pNum < 20000000) $maxPureNumber = $pNum;
                        }
                    }
                }

                // حساب الرقم التالي
                $activeMax = max($maxSeq, $maxPureNumber);
                $nextSeq = $activeMax + 1;
                $formattedNextNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                $numericNextNo = (string)$nextSeq;

                echo json_encode([
                    'success' => true,
                    'entity' => 'sales',
                    'tenant_id' => $tenant_id,
                    'lastSeq' => $activeMax,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo, // مثلاً S26100548
                    'numericNo' => $numericNextNo, // 548
                    'formattedNextNo' => $formattedNextNo,
                    'prefix' => $prefix
                ], JSON_UNESCAPED_UNICODE);
                exit;

            } elseif ($entity === 'purchases' || $entity === 'purchase') {
                $prefix = 'P' . $yymm;
                $maxSeq = 0;

                $stmt = $pdo->prepare("SELECT invoice_no, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $invNo = trim((string)($row['invoice_no'] ?? ''));
                    $d = json_decode($row['data'] ?? '{}', true);
                    $jsonInvNo = trim((string)($d['invoiceNo'] ?? ($d['id'] ?? '')));

                    foreach ([$invNo, $jsonInvNo] as $targetNo) {
                        if ($targetNo === '') continue;
                        if (strpos($targetNo, $prefix) === 0) {
                            $seq = (int)substr($targetNo, strlen($prefix));
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        } elseif (preg_match('/^P\d{4}(\d{4,})$/', $targetNo, $m)) {
                            $seq = (int)$m[1];
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        } elseif (preg_match('/(?:PUR)[-_]?(\d+)/i', $targetNo, $pm)) {
                            $seq = (int)$pm[1];
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        } elseif (is_numeric($targetNo) && (int)$targetNo < 20000000) {
                            $seq = (int)$targetNo;
                            if ($seq > $maxSeq) $maxSeq = $seq;
                        }
                    }
                }

                $nextSeq = $maxSeq + 1;
                $formattedNextNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                echo json_encode([
                    'success' => true,
                    'entity' => 'purchases',
                    'tenant_id' => $tenant_id,
                    'lastSeq' => $maxSeq,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo,
                    'numericNo' => (string)$nextSeq,
                    'prefix' => $prefix
                ], JSON_UNESCAPED_UNICODE);
                exit;

            } elseif ($entity === 'vouchers' || $entity === 'voucher' || $entity === 'payment') {
                $prefix = 'E' . $yymm;
                $maxSeq = 0;

                $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    $vId = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    if (strpos($vId, $prefix) === 0) {
                        $s = (int)substr($vId, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (preg_match('/^E\d{4}(\d{4,})$/', $vId, $m)) {
                        $s = (int)$m[1];
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (is_numeric($vId) && (int)$vId < 20000000) {
                        $s = (int)$vId;
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }

                $nextSeq = $maxSeq + 1;
                $formattedNextNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                echo json_encode([
                    'success' => true,
                    'entity' => 'vouchers',
                    'tenant_id' => $tenant_id,
                    'lastSeq' => $maxSeq,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo,
                    'numericNo' => (string)$nextSeq,
                    'prefix' => $prefix
                ], JSON_UNESCAPED_UNICODE);
                exit;

            } elseif ($entity === 'deliveries' || $entity === 'delivery') {
                $prefix = 'DLV-' . $yymm;
                $maxSeq = 0;

                $stmt = $pdo->prepare("SELECT frontend_id, data FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    $dId = trim((string)($row['frontend_id'] ?? ($d['id'] ?? '')));
                    if (strpos($dId, $prefix) === 0) {
                        $s = (int)substr($dId, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (preg_match('/(?:DLV)[-_]?(\d+)/i', $dId, $m)) {
                        $s = (int)$m[1];
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }

                $nextSeq = $maxSeq + 1;
                $formattedNextNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                echo json_encode([
                    'success' => true,
                    'entity' => 'deliveries',
                    'tenant_id' => $tenant_id,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo
                ], JSON_UNESCAPED_UNICODE);
                exit;

            } elseif ($entity === 'receipts' || $entity === 'receipt') {
                $prefix = 'RCP-' . $yymm;
                $maxSeq = 0;

                $stmt = $pdo->prepare("SELECT frontend_id, data FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    $dId = trim((string)($row['frontend_id'] ?? ($d['id'] ?? '')));
                    if (strpos($dId, $prefix) === 0) {
                        $s = (int)substr($dId, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (preg_match('/(?:RCP|Z)[-_]?(\d+)/i', $dId, $m)) {
                        $s = (int)$m[1];
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }

                $nextSeq = $maxSeq + 1;
                $formattedNextNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                echo json_encode([
                    'success' => true,
                    'entity' => 'receipts',
                    'tenant_id' => $tenant_id,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo
                ], JSON_UNESCAPED_UNICODE);
                exit;

            } else {
                // Splits
                $prefix = 'SPL-' . $yymm;
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT frontend_id, data FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    $dId = trim((string)($row['frontend_id'] ?? ($d['id'] ?? '')));
                    if (strpos($dId, $prefix) === 0) {
                        $s = (int)substr($dId, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
                $nextSeq = $maxSeq + 1;
                echo json_encode([
                    'success' => true,
                    'entity' => 'splits',
                    'tenant_id' => $tenant_id,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT)
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            break;

        // ═════════════════════════════════════════════════════════════════
        // 2. حفظ فاتورة / إذن مبيعات مباشر في MariaDB
        // ═════════════════════════════════════════════════════════════════
        case 'save_sale':
            $saleData = $input['data'] ?? $input;
            if (!$saleData || !is_array($saleData)) {
                echo json_encode(['success' => false, 'error' => 'بيانات الفاتورة فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $invoiceNo = trim((string)($saleData['invoiceNo'] ?? ($saleData['id'] ?? '')));
            if ($invoiceNo === '' || $invoiceNo === 'preliminary' || $invoiceNo === '---') {
                // توليد الرقم التسلسلي تلقائياً
                $now = new DateTime('now');
                $prefix = 'S' . $now->format('ym');
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT invoice_no, data FROM sales_invoices WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $invNo = trim((string)($row['invoice_no'] ?? ''));
                    if (strpos($invNo, $prefix) === 0) {
                        $s = (int)substr($invNo, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
                $invoiceNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $saleData['invoiceNo'] = $invoiceNo;
                $saleData['id'] = $invoiceNo;
            }

            $dateVal = $saleData['date'] ?? ($saleData['fullDate'] ?? date('Y-m-d'));
            if (strlen($dateVal) > 10) $dateVal = substr($dateVal, 0, 10);
            $totalVal = (float)($saleData['grandTotal'] ?? ($saleData['total'] ?? 0));
            $custId = $saleData['customerId'] ?? ($saleData['customer'] ?? '');
            $statusVal = $saleData['status'] ?? 'posted';
            $currencyVal = $saleData['currency'] ?? 'SDG';

            // ضمان وجود الحقول الموحدة داخل JSON
            $saleData['id'] = $invoiceNo;
            $saleData['invoiceNo'] = $invoiceNo;
            $saleData['tenant_id'] = $tenant_id;
            $saleData['isPosted'] = true;
            $saleData['sync_status'] = 'SYNCED';
            $saleData['official_server_id'] = $invoiceNo;
            $saleData['updated_at'] = date('Y-m-d H:i:s');

            $jsonRecord = json_encode($saleData, JSON_UNESCAPED_UNICODE);

            // فحص وجود الفاتورة مسبقاً (تحديث أم إدراج جديد)
            $stmtCheck = $pdo->prepare("SELECT id FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenant_id, $invoiceNo, $invoiceNo, $invoiceNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $jsonRecord]);
            }

            // إدراج بطابور المزامنة
            try {
                $syncId = 'sync-sale-' . bin2hex(random_bytes(5));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'sales_invoices', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $invoiceNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Exception $es) {}

            echo json_encode([
                'success' => true,
                'invoiceNo' => $invoiceNo,
                'id' => $invoiceNo,
                'record' => $saleData,
                'isNew' => !$existingId,
                'message' => 'تم حفظ وترحيل فاتورة المبيعات بنجاح في قاعدة البيانات وسيرفر المرآة'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 3. جلب كافة فواتير المبيعات المعتمدة (Mirror Pull)
        // ═════════════════════════════════════════════════════════════════
        case 'get_sales':
            $stmt = $pdo->prepare("SELECT id, invoice_no, data, created_at, updated_at FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 500");
            $stmt->execute([$tenant_id]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    if (empty($d['invoiceNo'])) $d['invoiceNo'] = $r['invoice_no'] ?: ('S' . $r['id']);
                    if (empty($d['id'])) $d['id'] = $d['invoiceNo'];
                    $list[] = $d;
                }
            }

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'count' => count($list),
                'sales' => $list
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 4. حذف فاتورة مبيعات
        // ═════════════════════════════════════════════════════════════════
        case 'delete_sale':
            $invoiceNo = trim((string)($_GET['invoice_no'] ?? ($_GET['invoiceNo'] ?? ($input['invoiceNo'] ?? ($input['id'] ?? '')))));
            if ($invoiceNo !== '') {
                $stmt = $pdo->prepare("UPDATE sales_invoices SET data = JSON_SET(data, '$.deleted', true), updated_at = NOW() WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmt->execute([$tenant_id, $invoiceNo, $invoiceNo, $invoiceNo]);

                try {
                    $syncId = 'sync-del-' . bin2hex(random_bytes(5));
                    $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'sales_invoices', ?, 'DELETE', ?, 'SYNCED', NOW(), NOW())");
                    $stmtSync->execute([$syncId, $invoiceNo, json_encode(['invoiceNo' => $invoiceNo])]);
                } catch (Exception $es) {}

                echo json_encode(['success' => true, 'invoiceNo' => $invoiceNo], JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo json_encode(['success' => false, 'error' => 'رقم الفاتورة مفقود'], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 5. حفظ فاتورة / إذن مشتريات مباشر في MariaDB
        // ═════════════════════════════════════════════════════════════════
        case 'save_purchase':
            $purData = $input['data'] ?? $input;
            if (!$purData || !is_array($purData)) {
                echo json_encode(['success' => false, 'error' => 'بيانات فاتورة الشراء فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $invoiceNo = trim((string)($purData['invoiceNo'] ?? ($purData['id'] ?? '')));
            if ($invoiceNo === '' || $invoiceNo === '---') {
                $now = new DateTime('now');
                $prefix = 'P' . $now->format('ym');
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT invoice_no, data FROM purchase_invoices WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $invNo = trim((string)($row['invoice_no'] ?? ''));
                    if (strpos($invNo, $prefix) === 0) {
                        $s = (int)substr($invNo, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
                $invoiceNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $purData['invoiceNo'] = $invoiceNo;
                $purData['id'] = $invoiceNo;
            }

            $dateVal = $purData['date'] ?? ($purData['fullDate'] ?? date('Y-m-d'));
            if (strlen($dateVal) > 10) $dateVal = substr($dateVal, 0, 10);
            $totalVal = (float)($purData['grandTotal'] ?? ($purData['total'] ?? 0));
            $suppId = $purData['supplierId'] ?? ($purData['supplier'] ?? '');
            $statusVal = $purData['status'] ?? 'posted';
            $currencyVal = $purData['currency'] ?? 'SDG';

            $purData['id'] = $invoiceNo;
            $purData['invoiceNo'] = $invoiceNo;
            $purData['tenant_id'] = $tenant_id;
            $purData['isPosted'] = true;
            $purData['sync_status'] = 'SYNCED';
            $purData['official_server_id'] = $invoiceNo;
            $purData['updated_at'] = date('Y-m-d H:i:s');

            $jsonRecord = json_encode($purData, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenant_id, $invoiceNo, $invoiceNo, $invoiceNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $jsonRecord]);
            }

            echo json_encode([
                'success' => true,
                'invoiceNo' => $invoiceNo,
                'id' => $invoiceNo,
                'record' => $purData,
                'isNew' => !$existingId,
                'message' => 'تم حفظ وترحيل فاتورة المشتريات بنجاح في قاعدة البيانات وسيرفر المرآة'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6. جلب فواتير المشتريات (Mirror Pull)
        // ═════════════════════════════════════════════════════════════════
        case 'get_purchases':
            $stmt = $pdo->prepare("SELECT id, invoice_no, data, created_at, updated_at FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 500");
            $stmt->execute([$tenant_id]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    if (empty($d['invoiceNo'])) $d['invoiceNo'] = $r['invoice_no'] ?: ('P' . $r['id']);
                    if (empty($d['id'])) $d['id'] = $d['invoiceNo'];
                    $list[] = $d;
                }
            }

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'count' => count($list),
                'purchases' => $list
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6.2. حذف فاتورة مشتريات
        // ═════════════════════════════════════════════════════════════════
        case 'delete_purchase':
            $invoiceNo = trim((string)($_GET['invoice_no'] ?? ($_GET['invoiceNo'] ?? ($input['invoiceNo'] ?? ($input['id'] ?? '')))));
            if ($invoiceNo !== '') {
                $stmt = $pdo->prepare("UPDATE purchase_invoices SET data = JSON_SET(data, '$.deleted', true), updated_at = NOW() WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmt->execute([$tenant_id, $invoiceNo, $invoiceNo, $invoiceNo]);

                try {
                    $syncId = 'sync-del-pur-' . bin2hex(random_bytes(5));
                    $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'purchase_invoices', ?, 'DELETE', ?, 'SYNCED', NOW(), NOW())");
                    $stmtSync->execute([$syncId, $invoiceNo, json_encode(['invoiceNo' => $invoiceNo])]);
                } catch (Exception $es) {}

                echo json_encode(['success' => true, 'invoiceNo' => $invoiceNo], JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo json_encode(['success' => false, 'error' => 'رقم فاتورة الشراء مفقود'], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6.3. حفظ سند / حركة مالية (Single Payment Voucher)
        // ═════════════════════════════════════════════════════════════════
        case 'save_voucher':
            $vData = $input['data'] ?? $input;
            if (!$vData || !is_array($vData)) {
                echo json_encode(['success' => false, 'error' => 'بيانات السند المالي فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $voucherNo = trim((string)($vData['voucherNo'] ?? ($vData['id'] ?? '')));
            if ($voucherNo === '' || $voucherNo === 'preliminary' || $voucherNo === '---') {
                $now = new DateTime('now');
                $prefix = 'E' . $now->format('ym');
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tenant_id]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'] ?? '{}', true);
                    $vId = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    if (strpos($vId, $prefix) === 0) {
                        $s = (int)substr($vId, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
                $voucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $vData['voucherNo'] = $voucherNo;
                $vData['id'] = $voucherNo;
            }

            $vData['id'] = $voucherNo;
            $vData['voucherNo'] = $voucherNo;
            $vData['tenant_id'] = $tenant_id;
            $vData['sync_status'] = 'SYNCED';
            $vData['updated_at'] = date('Y-m-d H:i:s');
            $jsonRecord = json_encode($vData, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tenant_id, $voucherNo, $voucherNo]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $jsonRecord]);
            }

            try {
                $syncId = 'sync-vouch-' . bin2hex(random_bytes(5));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $voucherNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Exception $es) {}

            echo json_encode([
                'success' => true,
                'voucherNo' => $voucherNo,
                'id' => $voucherNo,
                'record' => $vData,
                'isNew' => !$existingId,
                'message' => 'تم حفظ وترحيل السند المالي بنجاح في قاعدة البيانات وسيرفر المرآة'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6.4. جلب السندات المالية (Single Payment Vouchers)
        // ═════════════════════════════════════════════════════════════════
        case 'get_vouchers':
            $stmt = $pdo->prepare("SELECT id, data, created_at, updated_at FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 500");
            $stmt->execute([$tenant_id]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    if (empty($d['voucherNo'])) $d['voucherNo'] = $d['id'] ?? ('E' . $r['id']);
                    if (empty($d['id'])) $d['id'] = $d['voucherNo'];
                    $list[] = $d;
                }
            }
            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'count' => count($list),
                'vouchers' => $list
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6.5. حذف سند مالي
        // ═════════════════════════════════════════════════════════════════
        case 'delete_voucher':
            $vNo = trim((string)($_GET['voucher_no'] ?? ($_GET['voucherNo'] ?? ($input['voucherNo'] ?? ($input['id'] ?? '')))));
            if ($vNo !== '') {
                $stmt = $pdo->prepare("UPDATE single_payments SET data = JSON_SET(data, '$.deleted', true), updated_at = NOW() WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
                $stmt->execute([$tenant_id, $vNo, $vNo]);

                try {
                    $syncId = 'sync-del-vouch-' . bin2hex(random_bytes(5));
                    $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, 'DELETE', ?, 'SYNCED', NOW(), NOW())");
                    $stmtSync->execute([$syncId, $vNo, json_encode(['voucherNo' => $vNo])]);
                } catch (Exception $es) {}

                echo json_encode(['success' => true, 'voucherNo' => $vNo], JSON_UNESCAPED_UNICODE);
                exit;
            }
            echo json_encode(['success' => false, 'error' => 'رقم السند المالي مفقود'], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 7. حفظ إذن تسليم مبيعات أو استلام مشتريات (Delivery Notes / Receipts)
        // ═════════════════════════════════════════════════════════════════
        case 'save_delivery':
        case 'save_receipt':
            $docData = $input['data'] ?? $input;
            $type = ($action === 'save_receipt') ? 'purchase_receipt' : 'sales_delivery';
            $docId = trim((string)($docData['id'] ?? ''));

            if ($docId === '' || strpos($docId, 'OFF-') !== false) {
                $prefix = ($type === 'purchase_receipt') ? 'RCP-' : 'DLV-';
                $prefix .= date('ym');
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT frontend_id FROM delivery_notes WHERE tenant_id = ?");
                $stmt->execute([$tenant_id]);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fid = (string)($r['frontend_id'] ?? '');
                    if (strpos($fid, $prefix) === 0) {
                        $s = (int)substr($fid, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
                $docId = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $docData['id'] = $docId;
            }

            $docData['type'] = $type;
            $docData['tenant_id'] = $tenant_id;
            $docData['timestamp'] = date('c');
            $jsonRecord = json_encode($docData, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND frontend_id = ?");
            $stmtCheck->execute([$tenant_id, $docId]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE delivery_notes SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, data, frontend_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $jsonRecord, $docId]);
            }

            echo json_encode([
                'success' => true,
                'id' => $docId,
                'type' => $type,
                'record' => $docData,
                'message' => 'تم حفظ وترحيل الإذن بنجاح في قاعدة البيانات'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 8. جلب أذونات التسليم والاستلام (Deliveries & Receipts)
        // ═════════════════════════════════════════════════════════════════
        case 'get_deliveries':
        case 'get_receipts':
            $type = ($action === 'get_receipts') ? 'purchase_receipt' : 'sales_delivery';
            $stmt = $pdo->prepare("SELECT id, frontend_id, data, created_at FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 500");
            $stmt->execute([$tenant_id]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    if (empty($d['id'])) $d['id'] = $r['frontend_id'];
                    if (!isset($d['type']) || $d['type'] === $type || $action === 'get_deliveries') {
                        $list[] = $d;
                    }
                }
            }
            echo json_encode(['success' => true, 'tenant_id' => $tenant_id, 'count' => count($list), 'items' => $list], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 9. حفظ خطط تقسيم المبيعات والمشتريات (Splits)
        // ═════════════════════════════════════════════════════════════════
        case 'save_split':
            $splitData = $input['data'] ?? $input;
            $splitType = $splitData['type'] ?? 'sales_split';
            $splitId = trim((string)($splitData['id'] ?? ''));

            if ($splitId === '' || strpos($splitId, 'OFF-') !== false) {
                $prefix = ($splitType === 'purchase_split' ? 'PSPL-' : 'SPL-') . date('ym');
                $maxSeq = 0;
                $stmt = $pdo->prepare("SELECT frontend_id FROM delivery_notes WHERE tenant_id = ? AND frontend_id LIKE ?");
                $stmt->execute([$tenant_id, $prefix . '%']);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fid = (string)($r['frontend_id'] ?? '');
                    $s = (int)substr($fid, strlen($prefix));
                    if ($s > $maxSeq) $maxSeq = $s;
                }
                $splitId = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $splitData['id'] = $splitId;
            }

            $splitData['tenant_id'] = $tenant_id;
            $jsonRecord = json_encode($splitData, JSON_UNESCAPED_UNICODE);

            $stmtCheck = $pdo->prepare("SELECT id FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND frontend_id = ?");
            $stmtCheck->execute([$tenant_id, $splitId]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE delivery_notes SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, data, frontend_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $jsonRecord, $splitId]);
            }

            echo json_encode(['success' => true, 'id' => $splitId, 'record' => $splitData], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 9.2. جلب خطط تقسيم المبيعات والمشتريات (Splits)
        // ═════════════════════════════════════════════════════════════════
        case 'get_splits':
            $stmt = $pdo->prepare("SELECT id, frontend_id, data, created_at FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (frontend_id LIKE 'SPL-%' OR frontend_id LIKE 'PSPL-%' OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) LIKE '%split%') ORDER BY id DESC LIMIT 500");
            $stmt->execute([$tenant_id]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    if (empty($d['id'])) $d['id'] = $r['frontend_id'];
                    $list[] = $d;
                }
            }
            echo json_encode(['success' => true, 'tenant_id' => $tenant_id, 'count' => count($list), 'splits' => $list], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 10. مؤشر حالة المرآة اللحظي (Mirror Status Heartbeat)
        // ═════════════════════════════════════════════════════════════════
        case 'mirror_status':
        default:
            $salesCount = (int)$pdo->query("SELECT COUNT(*) FROM sales_invoices WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)")->fetchColumn();
            $lastSale = $pdo->query("SELECT invoice_no, created_at FROM sales_invoices WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            $purCount = (int)$pdo->query("SELECT COUNT(*) FROM purchase_invoices WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)")->fetchColumn();
            $lastPur = $pdo->query("SELECT invoice_no, created_at FROM purchase_invoices WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            $payCount = (int)$pdo->query("SELECT COUNT(*) FROM single_payments WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)")->fetchColumn();
            $lastPayRow = $pdo->query("SELECT data, created_at FROM single_payments WHERE (tenant_id = $tenant_id OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $lastPayNo = 'none';
            if ($lastPayRow) {
                $pd = json_decode($lastPayRow['data'] ?? '{}', true);
                $lastPayNo = $pd['voucherNo'] ?? ($pd['id'] ?? 'none');
            }

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'sales' => ['count' => $salesCount, 'lastInvoiceNo' => $lastSale['invoice_no'] ?? '', 'lastCreatedAt' => $lastSale['created_at'] ?? ''],
                'purchases' => ['count' => $purCount, 'lastInvoiceNo' => $lastPur['invoice_no'] ?? '', 'lastCreatedAt' => $lastPur['created_at'] ?? ''],
                'single_payments' => ['count' => $payCount, 'lastVoucherNo' => $lastPayNo, 'lastCreatedAt' => $lastPayRow['created_at'] ?? ''],
                'server_time' => date('Y-m-d H:i:s'),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
