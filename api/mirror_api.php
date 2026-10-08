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

// 1. الترويسة أو المعامل الصريح
if (!empty($_SERVER['HTTP_X_TENANT_ID']) && is_numeric($_SERVER['HTTP_X_TENANT_ID'])) {
    $tenant_id = (int)$_SERVER['HTTP_X_TENANT_ID'];
} elseif (!empty($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
    $tenant_id = (int)$_GET['tenant_id'];
} elseif (!empty($_POST['tenant_id']) && is_numeric($_POST['tenant_id'])) {
    $tenant_id = (int)$_POST['tenant_id'];
}

// 2. فحص رمز المصادقة المباشر
if (!$tenant_id) {
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
}

// 3. جلسة المستخدم
if (!$tenant_id && !empty($_SESSION['user']['tenant_id'])) {
    $tenant_id = (int)$_SESSION['user']['tenant_id'];
}

// 4. استخراج رقم الشركة من النطاق الفرعي (مثل c.elsajana.com -> c -> 12)
if (!$tenant_id) {
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
    if (preg_match('/^([a-z0-9_-]+)\.elsajana\.com$/i', $host, $m)) {
        $sub = strtolower($m[1]);
        try {
            $stmtSub = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ? LIMIT 1");
            $stmtSub->execute([$sub]);
            $subTenant = $stmtSub->fetchColumn();
            if ($subTenant) $tenant_id = (int)$subTenant;
        } catch (Exception $e) {}
    }
}

// 5. الافتراضي الموحد للشركة النشطة (Company 'c' = 12)
if (!$tenant_id) {
    try {
        $stmtDef = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = 'c' LIMIT 1");
        $stmtDef->execute();
        $cTenant = $stmtDef->fetchColumn();
        if ($cTenant) $tenant_id = (int)$cTenant;
    } catch (Exception $e) {}
}

if (!$tenant_id) {
    $tenant_id = 12; // القيمة الموحدة للشركة c
}

// ── تضمين المحرك المركزي للتحقق من التحويلات ومنع تكرار آخر 6 أرقام ──
require_once __DIR__ . '/transfer_validator.php';

// ── محرك الحصانة ضد التكرار (Idempotency Engine) ──
function checkIdempotencyInDb($pdo, $table, $idempotencyKey, $tenant_id) {
    if (empty($idempotencyKey)) return null;
    try {
        $stmt = $pdo->prepare("SELECT data FROM {$table} WHERE (tenant_id = ? OR tenant_id IS NULL) AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? AND (is_deleted = 0 OR is_deleted IS NULL) LIMIT 1");
        $stmt->execute([$tenant_id, $idempotencyKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['data'])) {
            return json_decode($row['data'], true);
        }
    } catch (Exception $e) {}
    return null;
}

// ── فحص المعرفات المؤقتة لوضع الأوفلاين (مثل: 1-أوفلاين، 2-أوفلاين...) ──
function isOfflineDocIdentifier($docNo) {
    if (empty($docNo) || $docNo === 'preliminary' || $docNo === '---') return true;
    if (strpos($docNo, 'أوفلاين') !== false || strpos($docNo, 'offline') !== false || strpos($docNo, 'OFF-') !== false) return true;
    return false;
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
                    'nextPurNo' => $formattedNextNo,
                    'formattedNextNo' => $formattedNextNo,
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

                $sysRate = 2650;
                try {
                    $stmtRate = $pdo->prepare("SELECT setting_value FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) AND setting_key = 'system_usd_rate' ORDER BY updated_at DESC LIMIT 1");
                    $stmtRate->execute([$tenant_id]);
                    $rVal = $stmtRate->fetchColumn();
                    if ($rVal && (float)$rVal > 0) $sysRate = (float)$rVal;
                } catch(Exception $e) {}

                echo json_encode([
                    'success' => true,
                    'entity' => 'vouchers',
                    'tenant_id' => $tenant_id,
                    'lastSeq' => $maxSeq,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo,
                    'nextVoucherNo' => $formattedNextNo,
                    'formattedNextNo' => $formattedNextNo,
                    'numericNo' => (string)$nextSeq,
                    'prefix' => $prefix,
                    'usdRate' => $sysRate
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
                    'lastSeq' => $maxSeq,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo,
                    'formattedNextNo' => $formattedNextNo,
                    'numericNo' => (string)$nextSeq
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
                    'lastSeq' => $maxSeq,
                    'nextSeq' => $nextSeq,
                    'nextNo' => $formattedNextNo,
                    'formattedNextNo' => $formattedNextNo,
                    'numericNo' => (string)$nextSeq
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

            // فحص مفتاح الحصانة ضد التكرار (Idempotency Key)
            $idempotencyKey = trim((string)($saleData['idempotencyKey'] ?? ''));
            if ($idempotencyKey !== '') {
                $existingDoc = checkIdempotencyInDb($pdo, 'sales_invoices', $idempotencyKey, $tenant_id);
                if ($existingDoc) {
                    $exNo = $existingDoc['invoiceNo'] ?? ($existingDoc['id'] ?? '');
                    echo json_encode([
                        'success' => true,
                        'already_processed' => true,
                        'invoiceNo' => $exNo,
                        'id' => $exNo,
                        'officialNo' => $exNo,
                        'offlineRef' => $existingDoc['offlineRef'] ?? ($existingDoc['offlineTempNo'] ?? ''),
                        'idempotencyKey' => $idempotencyKey,
                        'message' => 'المعاملة معتمدة ومسجلة مسبقاً بنفس مفتاح الحصانة'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $rawInvoiceNo = trim((string)($saleData['invoiceNo'] ?? ($saleData['id'] ?? '')));
            $isOfflineDoc = isOfflineDocIdentifier($rawInvoiceNo) || !empty($saleData['isOfflinePending']);
            $offlineRef = $isOfflineDoc ? $rawInvoiceNo : '';

            $invoiceNo = $rawInvoiceNo;
            if ($isOfflineDoc || $invoiceNo === '' || $invoiceNo === 'preliminary' || $invoiceNo === '---') {
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

            if (!empty($offlineRef)) {
                $saleData['offlineRef'] = $offlineRef;
                $saleData['offlineTempNo'] = $offlineRef;
            }

            $dateVal = $saleData['date'] ?? ($saleData['fullDate'] ?? date('Y-m-d'));
            if (strlen($dateVal) > 10) $dateVal = substr($dateVal, 0, 10);
            $totalVal = (float)($saleData['grandTotal'] ?? ($saleData['total'] ?? 0));
            $custId = $saleData['customerId'] ?? ($saleData['customer'] ?? '');
            $statusVal = $saleData['status'] ?? 'posted';
            $currencyVal = $saleData['currency'] ?? 'SDG';

            // فحص التحويلات البنكية المرفقة بفاتورة المبيعات (الحد الأدنى 6 أرقام ومنع تكرار آخر 6 أرقام)
            $saleTransfers = extractAllTransfersFromRecord($saleData);
            foreach ($saleTransfers as $strTr) {
                $digits = preg_replace('/\D/', '', $strTr);
                if (strlen($digits) < 6) {
                    echo json_encode(['success' => false, 'error' => "رقم التحويلة البنكية ({$strTr}) في المبيعات غير مكتمل، يمنع منعاً باتاً الحفظ برقم أقل من 6 أرقام"], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $dup = findTransferDuplicateInDb($pdo, $strTr, $invoiceNo, $tenant_id);
                if ($dup) {
                    echo json_encode([
                        'success' => false,
                        'duplicate' => true,
                        'error' => "رقم التحويلة ({$strTr}) في المبيعات مكرر! تطابقت آخر 6 أرقام ({$dup['matchedSuffix']}) مع ({$dup['voucherNo']})، يمنع التكرار نهائياً!"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

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
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invoiceNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $invoiceNo, $jsonRecord]);
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
                'officialNo' => $invoiceNo,
                'offlineRef' => $offlineRef,
                'idempotencyKey' => $idempotencyKey,
                'isOfflineConverted' => !empty($offlineRef),
                'record' => $saleData,
                'isNew' => !$existingId,
                'message' => !empty($offlineRef)
                    ? "تم اعتماد الفاتورة رسمياً بالرقم ({$invoiceNo}) وربطها برقم الأوفلاين ({$offlineRef})"
                    : 'تم حفظ وترحيل فاتورة المبيعات بنجاح في قاعدة البيانات وسيرفر المرآة'
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

            // فحص مفتاح الحصانة ضد التكرار (Idempotency Key)
            $idempotencyKey = trim((string)($purData['idempotencyKey'] ?? ''));
            if ($idempotencyKey !== '') {
                $existingDoc = checkIdempotencyInDb($pdo, 'purchase_invoices', $idempotencyKey, $tenant_id);
                if ($existingDoc) {
                    $exNo = $existingDoc['invoiceNo'] ?? ($existingDoc['id'] ?? '');
                    echo json_encode([
                        'success' => true,
                        'already_processed' => true,
                        'invoiceNo' => $exNo,
                        'id' => $exNo,
                        'officialNo' => $exNo,
                        'offlineRef' => $existingDoc['offlineRef'] ?? ($existingDoc['offlineTempNo'] ?? ''),
                        'idempotencyKey' => $idempotencyKey,
                        'message' => 'فاتورة المشتريات معتمدة ومسجلة مسبقاً بنفس مفتاح الحصانة'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $rawInvoiceNo = trim((string)($purData['invoiceNo'] ?? ($purData['id'] ?? '')));
            $isOfflineDoc = isOfflineDocIdentifier($rawInvoiceNo) || !empty($purData['isOfflinePending']);
            $offlineRef = $isOfflineDoc ? $rawInvoiceNo : '';

            $invoiceNo = $rawInvoiceNo;
            if ($isOfflineDoc || $invoiceNo === '' || $invoiceNo === '---') {
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

            if (!empty($offlineRef)) {
                $purData['offlineRef'] = $offlineRef;
                $purData['offlineTempNo'] = $offlineRef;
            }

            $dateVal = $purData['date'] ?? ($purData['fullDate'] ?? date('Y-m-d'));
            if (strlen($dateVal) > 10) $dateVal = substr($dateVal, 0, 10);
            $totalVal = (float)($purData['grandTotal'] ?? ($purData['total'] ?? 0));
            $suppId = $purData['supplierId'] ?? ($purData['supplier'] ?? '');
            $statusVal = $purData['status'] ?? 'posted';
            $currencyVal = $purData['currency'] ?? 'SDG';

            // فحص التحويلات البنكية المرفقة بفاتورة المشتريات (الحد الأدنى 6 أرقام ومنع تكرار آخر 6 أرقام)
            $purTransfers = extractAllTransfersFromRecord($purData);
            foreach ($purTransfers as $strTr) {
                $digits = preg_replace('/\D/', '', $strTr);
                if (strlen($digits) < 6) {
                    echo json_encode(['success' => false, 'error' => "رقم التحويلة البنكية ({$strTr}) في المشتريات غير مكتمل، يمنع منعاً باتاً الحفظ برقم أقل من 6 أرقام"], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $dup = findTransferDuplicateInDb($pdo, $strTr, $invoiceNo, $tenant_id);
                if ($dup) {
                    echo json_encode([
                        'success' => false,
                        'duplicate' => true,
                        'error' => "رقم التحويلة ({$strTr}) في المشتريات مكرر! تطابقت آخر 6 أرقام ({$dup['matchedSuffix']}) مع ({$dup['voucherNo']})، يمنع التكرار نهائياً!"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

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
                $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invoiceNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenant_id, $invoiceNo, $jsonRecord]);
            }

            echo json_encode([
                'success' => true,
                'invoiceNo' => $invoiceNo,
                'id' => $invoiceNo,
                'officialNo' => $invoiceNo,
                'offlineRef' => $offlineRef,
                'idempotencyKey' => $idempotencyKey,
                'isOfflineConverted' => !empty($offlineRef),
                'record' => $purData,
                'isNew' => !$existingId,
                'message' => !empty($offlineRef)
                    ? "تم اعتماد فاتورة المشتريات رسمياً بالرقم ({$invoiceNo}) وربطها برقم الأوفلاين ({$offlineRef})"
                    : 'تم حفظ وترحيل فاتورة المشتريات بنجاح في قاعدة البيانات وسيرفر المرآة'
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
        // 6.3. حفظ سند / حركة مالية (Single Payment Voucher) - مرآة كاملة ومطابقة
        // ═════════════════════════════════════════════════════════════════
        case 'save_voucher':
            $vData = $input['data'] ?? $input;
            if (!$vData || !is_array($vData)) {
                echo json_encode(['success' => false, 'error' => 'بيانات السند المالي فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 1. فحص المبلغ الإجمالي
            $amtLocal = (float)($vData['amountLocal'] ?? ($vData['amount'] ?? ($vData['sdg'] ?? 0)));
            if ($amtLocal <= 0) {
                echo json_encode(['success' => false, 'error' => 'لا يمكن اعتماد سند بقيمة صفر'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 2. فحص أطراف السند (من وإلى)
            $fromName = trim((string)($vData['fromAccountName'] ?? ($vData['from'] ?? '')));
            $fromAccId = trim((string)($vData['fromAccount'] ?? ($vData['fromId'] ?? '')));
            if ($fromName === '' && $fromAccId === '') {
                echo json_encode(['success' => false, 'error' => 'يرجى اختيار حساب الدفع (الدافع)'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if ($fromName === '') $fromName = $fromAccId;

            $toName = trim((string)($vData['toAccountName'] ?? ($vData['beneficiaryName'] ?? ($vData['to'] ?? ''))));
            $toAccId = trim((string)($vData['toAccount'] ?? ($vData['toId'] ?? '')));
            if ($toName === '' && $toAccId === '') {
                echo json_encode(['success' => false, 'error' => 'يرجى اختيار الحساب المستفيد'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if ($toName === '') $toName = $toAccId;

            // منع تطابق حساب الدافع مع المستفيد
            $normFrom = preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($fromName, 'UTF-8'));
            $normTo = preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($toName, 'UTF-8'));
            if ($normFrom !== '' && $normFrom === $normTo) {
                echo json_encode(['success' => false, 'error' => 'لا يمكن أن يكون حساب الدفع مطابقاً لحساب المستفيد'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 3. طريقة الدفع
            $rawMethod = strtolower(trim((string)($vData['paymentMethod'] ?? ($vData['payType'] ?? 'transfer'))));
            $payMethod = $rawMethod;
            if ($payMethod === 'mobile') $payMethod = 'transfer';
            if ($payMethod === 'check' || $payMethod === 'cheque' || strpos($payMethod, 'cheque') !== false) $payMethod = 'check';

            $trNo = trim((string)($vData['mobileTransferNo'] ?? ($vData['refNo'] ?? ($vData['transferRef'] ?? ''))));
            $bankName = trim((string)($vData['bankName'] ?? ($vData['bank'] ?? ($vData['mobileNote'] ?? ''))));
            $chNo = trim((string)($vData['checkNo'] ?? ($vData['refNo'] ?? '')));

            // فحص مفتاح الحصانة ضد التكرار للسند المالي
            $idempotencyKey = trim((string)($vData['idempotencyKey'] ?? ''));
            if ($idempotencyKey !== '') {
                $existingDoc = checkIdempotencyInDb($pdo, 'single_payments', $idempotencyKey, $tenant_id);
                if ($existingDoc) {
                    $exNo = $existingDoc['voucherNo'] ?? ($existingDoc['id'] ?? '');
                    echo json_encode([
                        'success' => true,
                        'already_processed' => true,
                        'voucherNo' => $exNo,
                        'id' => $exNo,
                        'officialNo' => $exNo,
                        'offlineRef' => $existingDoc['offlineRef'] ?? ($existingDoc['offlineTempNo'] ?? ''),
                        'idempotencyKey' => $idempotencyKey,
                        'message' => 'السند المالي معتمد ومسجل مسبقاً بنفس مفتاح الحصانة'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $rawVoucherNo = trim((string)($vData['voucherNo'] ?? ($vData['id'] ?? '')));
            $isOfflineDoc = isOfflineDocIdentifier($rawVoucherNo) || !empty($vData['isOfflinePending']);
            $offlineRef = $isOfflineDoc ? $rawVoucherNo : '';
            $isNew = $isOfflineDoc || empty($rawVoucherNo) || $rawVoucherNo === 'preliminary' || $rawVoucherNo === '---' || !empty($vData['isNew']);
            $voucherNo = $rawVoucherNo;

            // شروط التحويلة
            if ($payMethod === 'transfer') {
                if ($trNo === '') {
                    echo json_encode(['success' => false, 'error' => 'يرجى إدخال رقم حساب التحويل'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $digitsOnly = preg_replace('/\D/', '', $trNo);
                if (strlen($digitsOnly) < 6) {
                    echo json_encode(['success' => false, 'error' => 'رقم التحويلة غير مكتمل (' . strlen($digitsOnly) . ' أرقام)، يمنع منعاً باتاً الحفظ برقم تحويلة يقل عن 6 أرقام'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($bankName === '') {
                    echo json_encode(['success' => false, 'error' => 'اسم البنك إجباري، يرجى كتابة اسم البنك'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                // فحص التكرار الحاسم في قاعدة البيانات MariaDB ومقارنة آخر 6 أرقام
                $dup = findTransferDuplicateInDb($pdo, $trNo, $isNew ? '' : $voucherNo, $tenant_id);
                if ($dup) {
                    echo json_encode([
                        'success' => false,
                        'duplicate' => true,
                        'error' => "رقم التحويلة ({$trNo}) مكرر! تطابقت آخر 6 أرقام ({$dup['matchedSuffix']}) مع ({$dup['voucherNo']})، لا يمكن تكرار رقم التحويلة نهائياً!"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } elseif ($payMethod === 'check') {
                if ($bankName === '') {
                    echo json_encode(['success' => false, 'error' => 'اسم البنك إجباري، يرجى كتابة اسم البنك'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($chNo === '') {
                    echo json_encode(['success' => false, 'error' => 'رقم الشيك إجباري، لابد من كتابة رقم الشيك'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // 4. الرقم التسلسلي الموحد للسند
            $prefix = 'E' . date('ym');
            $maxSeq = 0;
            $stmtSeq = $pdo->prepare("SELECT data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmtSeq->execute([$tenant_id]);
            while ($row = $stmtSeq->fetch(PDO::FETCH_ASSOC)) {
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

            if ($isNew) {
                $voucherNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
                $vData['voucherNo'] = $voucherNo;
                $vData['id'] = $voucherNo;
            }
            if (!empty($offlineRef)) {
                $vData['offlineRef'] = $offlineRef;
                $vData['offlineTempNo'] = $offlineRef;
            }

            // سعر الصرف المعتمد
            $rate = (float)($vData['rate'] ?? ($vData['exchangeRate'] ?? 0));
            if ($rate <= 0) {
                try {
                    $stmtR = $pdo->prepare("SELECT setting_value FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) AND setting_key = 'system_usd_rate' ORDER BY updated_at DESC LIMIT 1");
                    $stmtR->execute([$tenant_id]);
                    $rVal = $stmtR->fetchColumn();
                    if ($rVal && (float)$rVal > 0) $rate = (float)$rVal;
                } catch(Exception $e) {}
            }
            if ($rate <= 0) $rate = 2650;

            // تحديث سعر الصرف للسيستم إذا تم تعديله
            if ($rate > 0) {
                try {
                    $stmtUpRate = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, updated_at) VALUES (?, 'system_usd_rate', ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                    $stmtUpRate->execute([$tenant_id, (string)$rate]);
                } catch(Exception $e) {}
            }

            $foreignAmt = (float)($vData['amountForeign'] ?? ($vData['usd'] ?? ($rate > 0 ? round($amtLocal / $rate, 2) : 0)));

            // 5. توحيد السجل بكافة الحقول المتطابقة
            $notes = trim((string)($vData['notes'] ?? ($vData['toStatement'] ?? '')));
            $costCenter = trim((string)($vData['costCenter'] ?? ''));

            $vData['id'] = $voucherNo;
            $vData['voucherNo'] = $voucherNo;
            $vData['tenant_id'] = $tenant_id;
            $vData['date'] = $vData['date'] ?? date('Y-m-d');
            $vData['time'] = $vData['time'] ?? date('H:i');
            $vData['type'] = 'single_payment';
            $vData['amountLocal'] = $amtLocal;
            $vData['amount'] = $amtLocal;
            $vData['sdg'] = $amtLocal;
            $vData['amountForeign'] = $foreignAmt;
            $vData['usd'] = $foreignAmt;
            $vData['currency'] = $vData['currency'] ?? 'SDG';
            $vData['rate'] = $rate;
            $vData['exchangeRate'] = $rate;
            $vData['systemExchangeRate'] = $rate;
            $vData['fromAccount'] = $fromAccId;
            $vData['fromAccountName'] = $fromName;
            $vData['from'] = $fromName;
            $vData['toAccount'] = $toAccId;
            $vData['toAccountName'] = $toName;
            $vData['to'] = $toName;
            $vData['beneficiaryName'] = $toName;
            $vData['paymentMethod'] = $payMethod;
            $vData['payType'] = $payMethod;
            $vData['bank'] = $bankName;
            $vData['bankName'] = $bankName;
            $vData['notes'] = $notes;
            $vData['toStatement'] = $notes;
            $vData['costCenter'] = $costCenter;
            $vData['isPosted'] = true;
            $vData['timestamp'] = $vData['timestamp'] ?? date('c');

            if ($payMethod === 'transfer') {
                $vData['mobileTransferNo'] = $trNo;
                $vData['refNo'] = $trNo;
                $vData['transferRef'] = $trNo;
                if (empty($vData['bankTransfers'])) {
                    $vData['bankTransfers'] = [['transferNo' => $trNo, 'note' => $bankName, 'amount' => $amtLocal, 'statement' => $notes]];
                }
            } elseif ($payMethod === 'check') {
                $vData['checkNo'] = $chNo;
                $vData['refNo'] = $chNo;
                $vData['checkBranch'] = $bankName;
                if (empty($vData['cheques'])) {
                    $vData['cheques'] = [['checkNo' => $chNo, 'checkBranch' => $bankName, 'checkDate' => $vData['date'] ?? date('Y-m-d'), 'amount' => $amtLocal, 'statement' => $notes]];
                }
            }

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

            // احتساب الرقم التسلسلي التالي الجديد بعد الحفظ فوراً
            $newMax = $maxSeq;
            if ($isNew) {
                if (strpos($voucherNo, $prefix) === 0) {
                    $s = (int)substr($voucherNo, strlen($prefix));
                    if ($s > $newMax) $newMax = $s;
                }
            }
            $nextVoucherNo = $prefix . str_pad($newMax + 1, 4, '0', STR_PAD_LEFT);

            try {
                $syncId = 'sync-vouch-' . bin2hex(random_bytes(5));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $voucherNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Exception $es) {}

            echo json_encode([
                'success' => true,
                'voucherNo' => $voucherNo,
                'id' => $voucherNo,
                'officialNo' => $voucherNo,
                'offlineRef' => $offlineRef,
                'idempotencyKey' => $idempotencyKey,
                'isOfflineConverted' => !empty($offlineRef),
                'nextNo' => $nextVoucherNo,
                'nextVoucherNo' => $nextVoucherNo,
                'usdRate' => $rate,
                'record' => $vData,
                'isNew' => !$existingId,
                'message' => !empty($offlineRef)
                    ? "تم اعتماد السند المالي رسمياً بالرقم ({$voucherNo}) وربطه برقم الأوفلاين ({$offlineRef})"
                    : 'تم حفظ وترحيل السند المالي بنجاح في قاعدة البيانات وسيرفر المرآة'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // 6.4. جلب ومزامنة السندات المالية (Single Payment Vouchers)
        // ═════════════════════════════════════════════════════════════════
        case 'get_vouchers':
            $stmt = $pdo->prepare("SELECT id, data, created_at, updated_at FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
            $stmt->execute([$tenant_id]);
            $list = [];
            $prefix = 'E' . date('ym');
            $maxSeq = 0;
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'] ?? '{}', true);
                if (is_array($d)) {
                    $vNo = trim((string)($d['voucherNo'] ?? ($d['id'] ?? '')));
                    if (empty($vNo)) $vNo = 'E' . $r['id'];
                    $d['voucherNo'] = $vNo;
                    $d['id'] = $vNo;
                    $list[] = $d;

                    if (strpos($vNo, $prefix) === 0) {
                        $s = (int)substr($vNo, strlen($prefix));
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (preg_match('/^E\d{4}(\d{4,})$/', $vNo, $m)) {
                        $s = (int)$m[1];
                        if ($s > $maxSeq) $maxSeq = $s;
                    } elseif (is_numeric($vNo) && (int)$vNo < 20000000) {
                        $s = (int)$vNo;
                        if ($s > $maxSeq) $maxSeq = $s;
                    }
                }
            }

            $nextVNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);

            $sysRate = 2650;
            try {
                $stmtRate = $pdo->prepare("SELECT setting_value FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) AND setting_key = 'system_usd_rate' ORDER BY updated_at DESC LIMIT 1");
                $stmtRate->execute([$tenant_id]);
                $rVal = $stmtRate->fetchColumn();
                if ($rVal && (float)$rVal > 0) $sysRate = (float)$rVal;
            } catch(Exception $e) {}

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenant_id,
                'count' => count($list),
                'nextNo' => $nextVNo,
                'nextVoucherNo' => $nextVNo,
                'usdRate' => $sysRate,
                'vouchers' => $list
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════════════
        // فحص تكرار التحويلة الفوري عبر الـ API
        // ═════════════════════════════════════════════════════════════════
        case 'check_duplicate':
            $ref = trim($_GET['ref'] ?? ($input['ref'] ?? ''));
            $currentVNo = trim($_GET['voucherNo'] ?? ($input['voucherNo'] ?? ''));
            $cleanDigits = preg_replace('/\D/', '', $ref);
            if (strlen($cleanDigits) < 6) {
                echo json_encode([
                    'valid' => false,
                    'duplicate' => false,
                    'tooShort' => true,
                    'error' => "رقم التحويلة غير مكتمل (" . strlen($cleanDigits) . " أرقام)، يمنع منعاً باتاً الحفظ برقم أقل من 6 أرقام."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $dup = findTransferDuplicateInDb($pdo, $ref, $currentVNo, $tenant_id);
            if ($dup) {
                echo json_encode([
                    'valid' => false,
                    'duplicate' => true,
                    'error' => "رقم التحويلة ({$ref}) مكرر! تطابقت آخر 6 أرقام ({$dup['matchedSuffix']}) مع ({$dup['voucherNo']})" . (!empty($dup['date']) ? " بتاريخ {$dup['date']}" : ""),
                    'matchedVoucher' => $dup['voucherNo'],
                    'matchedRef' => $dup['ref'],
                    'matchedSuffix' => $dup['matchedSuffix'] ?? '',
                    'date' => $dup['date'] ?? ''
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['valid' => true, 'duplicate' => false], JSON_UNESCAPED_UNICODE);
            }
            exit;

        // ═════════════════════════════════════════════════════════════════
        // حفظ واسترجاع سعر صرف الدولار للسيستم
        // ═════════════════════════════════════════════════════════════════
        case 'save_rate':
            $rVal = (float)($_GET['rate'] ?? ($input['rate'] ?? 0));
            if ($rVal > 0) {
                try {
                    $stmtUpRate = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, updated_at) VALUES (?, 'system_usd_rate', ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                    $stmtUpRate->execute([$tenant_id, (string)$rVal]);
                    echo json_encode(['success' => true, 'rate' => $rVal], JSON_UNESCAPED_UNICODE);
                    exit;
                } catch(Exception $e) {}
            }
            echo json_encode(['success' => false, 'error' => 'Invalid rate'], JSON_UNESCAPED_UNICODE);
            exit;

        case 'get_rate':
            $sysRate = 2650;
            try {
                $stmtRate = $pdo->prepare("SELECT setting_value FROM settings WHERE (tenant_id = ? OR tenant_id IS NULL) AND setting_key = 'system_usd_rate' ORDER BY updated_at DESC LIMIT 1");
                $stmtRate->execute([$tenant_id]);
                $rVal = $stmtRate->fetchColumn();
                if ($rVal && (float)$rVal > 0) $sysRate = (float)$rVal;
            } catch(Exception $e) {}
            echo json_encode(['success' => true, 'rate' => $sysRate], JSON_UNESCAPED_UNICODE);
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
            $rawDocId = trim((string)($docData['id'] ?? ''));
            $isOfflineDoc = isOfflineDocIdentifier($rawDocId) || !empty($docData['isOfflinePending']);
            $offlineRef = $isOfflineDoc ? $rawDocId : '';
            $docId = $rawDocId;

            if ($isOfflineDoc || $docId === '') {
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

            if (!empty($offlineRef)) {
                $docData['offlineRef'] = $offlineRef;
                $docData['offlineTempNo'] = $offlineRef;
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
                'officialNo' => $docId,
                'offlineRef' => $offlineRef,
                'type' => $type,
                'record' => $docData,
                'message' => !empty($offlineRef)
                    ? "تم اعتماد الإذن رسمياً بالرقم ({$docId}) وربطه برقم الأوفلاين ({$offlineRef})"
                    : 'تم حفظ وترحيل الإذن بنجاح في قاعدة البيانات'
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
