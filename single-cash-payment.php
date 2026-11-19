<?php if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); } ?>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secMobile = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
$isDesktopParam = isset($_GET['force_desktop']) && $_GET['force_desktop'] === '1';
$isMobileDevice = (bool)preg_match('/(android|iphone|ipad|ipod|mobile|blackberry|iemobile|opera mini)/i', $userAgent) || ($secMobile === '?1');
if ($isMobileDevice && !$isDesktopParam && empty($_POST)) {
    $qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header("Location: mobile-voucher.php{$qs}");
    exit;
}

require_once 'db_bridge.php';
require_once __DIR__ . '/mobile_auth_guard.php';
$pdo = getDBConnection();
$systemUsdRate = function_exists('getSystemUsdExchangeRate') ? getSystemUsdExchangeRate($pdo) : 5999.0;
global $GLOBAL_TENANT_ID;

if (!function_exists('normalizeTransferRefStrict')) {
    function normalizeTransferRefStrict($val) {
        $s = trim((string)$val);
        if ($s === '') return '';
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩','۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin  = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        $s = str_replace($arabic, $latin, $s);
        if (preg_match('/^(OUT-OFF-|IN-OFF-|OFF-|WA\d|AUTO-|TEMP-|null|none|n\/a)/i', $s)) return '';
        if (preg_match('/^\d{4}\s+\d{4}(?:\s+\d{4}){1,2}$/', $s)) return '';
        $digits = preg_replace('/\D/', '', $s);
        if (strlen($digits) < 6) return '';
        if (strlen($digits) === 14 && $digits[0] === '0' && preg_match('/000[1-9]$/', $digits)) return '';
        if (strlen($digits) === 21 && substr($digits, 0, 2) === '02') return '';
        if (strlen($digits) === 16 && $digits[0] === '0' && preg_match('/(0001|0002|0101|5001)$/', $digits)) return '';
        return $digits;
    }
}

if (!function_exists('areTransferRefsEqualStrict')) {
    function areTransferRefsEqualStrict($refA, $refB) {
        $dA = normalizeTransferRefStrict($refA);
        $dB = normalizeTransferRefStrict($refB);
        if ($dA === '' || $dB === '') return false;
        if ($dA === $dB) return true;
        $lenA = strlen($dA);
        $lenB = strlen($dB);
        $minLen = min($lenA, $lenB);
        if ($minLen < 6) return false;
        if ($lenA !== $lenB) {
            $shorter = ($lenA < $lenB) ? $dA : $dB;
            $longer  = ($lenA < $lenB) ? $dB : $dA;
            if (strlen($shorter) >= 6 && substr($longer, -strlen($shorter)) === $shorter) return true;
            if ($minLen >= 9 && substr($dA, -8) === substr($dB, -8)) return true;
            return false;
        }
        if ($lenA <= 8) return false;
        if (substr($dA, -8) === substr($dB, -8)) return true;
        if (substr($dA, -6) === substr($dB, -6) && substr($dA, 0, 2) === substr($dB, 0, 2)) return true;
        return false;
    }
}

if (!function_exists('extractAllTransfersFromRecord')) {
    function extractAllTransfersFromRecord($d) {
        if (!is_array($d)) return [];
        if (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0') return [];
        $refs = [];
        if (!empty($d['mobileTransferNo']) && is_string($d['mobileTransferNo'])) $refs[] = trim($d['mobileTransferNo']);
        if (!empty($d['transferNo']) && is_string($d['transferNo'])) $refs[] = trim($d['transferNo']);
        if (!empty($d['transferRef']) && is_string($d['transferRef'])) $refs[] = trim($d['transferRef']);
        if (!empty($d['refNo']) && is_string($d['refNo']) && !preg_match('/^(OUT-OFF-|IN-OFF-|OFF-)/i', trim($d['refNo']))) {
            $refs[] = trim($d['refNo']);
        }
        if (!empty($d['bankTransfers']) && is_array($d['bankTransfers'])) {
            foreach ($d['bankTransfers'] as $bt) {
                if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
            }
        }
        if (!empty($d['transfers']) && is_array($d['transfers'])) {
            foreach ($d['transfers'] as $bt) {
                if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
                if (!empty($bt['reference']) && is_string($bt['reference'])) $refs[] = trim($bt['reference']);
            }
        }
        if (!empty($d['details']) && is_array($d['details'])) {
            foreach ($d['details'] as $det) {
                if (!empty($det['transferNo']) && is_string($det['transferNo'])) $refs[] = trim($det['transferNo']);
                if (!empty($det['bankTransfers']) && is_array($det['bankTransfers'])) {
                    foreach ($det['bankTransfers'] as $bt) {
                        if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
                    }
                }
            }
        }
        if (!empty($d['items']) && is_array($d['items'])) {
            foreach ($d['items'] as $item) {
                if (!empty($item['transferNo']) && is_string($item['transferNo'])) $refs[] = trim($item['transferNo']);
                if (!empty($item['bankTransfers']) && is_array($item['bankTransfers'])) {
                    foreach ($item['bankTransfers'] as $bt) {
                        if (!empty($bt['transferNo']) && is_string($bt['transferNo'])) $refs[] = trim($bt['transferNo']);
                    }
                }
            }
        }
        $valid = [];
        foreach ($refs as $r) {
            $norm = normalizeTransferRefStrict($r);
            if ($norm !== '') $valid[] = $norm;
        }
        return array_values(array_unique($valid));
    }
}

if (!function_exists('findTransferDuplicateInDb')) {
    function findTransferDuplicateInDb($pdo, $ref, $currentVNo = '', $currentTenantId = null) {
        global $GLOBAL_TENANT_ID;
        $tId = (int)($currentTenantId ?: (!empty($GLOBAL_TENANT_ID) ? $GLOBAL_TENANT_ID : 12));
        $cleanRefDigits = normalizeTransferRefStrict($ref);
        if ($cleanRefDigits === '') return null;
        $candSuffix = substr($cleanRefDigits, -6);

        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tId]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    if (!$d) continue;
                    if (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0') continue;
                    $existingVNo = trim((string)($row['voucher_no'] ?? ($d['voucherNo'] ?? ($d['id'] ?? ''))));
                    if ($existingVNo !== '' && $currentVNo !== '' && $existingVNo === $currentVNo) {
                        continue;
                    }
                    $candidates = extractAllTransfersFromRecord($d);
                    foreach ($candidates as $cr) {
                        if (areTransferRefsEqualStrict($cleanRefDigits, $cr)) {
                            return [
                                'voucherNo' => $existingVNo ?: 'سابق',
                                'ref' => $cr,
                                'date' => $d['date'] ?? '',
                                'amount' => $d['amountLocal'] ?? ($d['amount'] ?? 0),
                                'bank' => $d['bankName'] ?? ($d['bank'] ?? ($d['fromAccount'] ?? '')),
                                'from' => $d['fromAccountName'] ?? ($d['fromAccount'] ?? ($d['from'] ?? '')),
                                'to' => $d['toAccountName'] ?? ($d['toAccount'] ?? ($d['to'] ?? ($d['beneficiaryName'] ?? ''))),
                                'table' => $tbl,
                                'matchedSuffix' => $candSuffix,
                                'matchType' => 'تطابق رقم العملية البنكية'
                            ];
                        }
                    }
                }
            } catch(Exception $e) {}
        }
        return null;
    }
}

if (!function_exists('fetchAllDbTransfers')) {
    function fetchAllDbTransfers($pdo, $currentTenantId = null) {
        global $GLOBAL_TENANT_ID;
        $tId = (int)($currentTenantId ?: (!empty($GLOBAL_TENANT_ID) ? $GLOBAL_TENANT_ID : 12));
        $results = [];
        $seen = [];
        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts'];
        foreach ($tables as $tbl) {
            try {
                $stmt = $pdo->prepare("SELECT id, tenant_id, data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmt->execute([$tId]);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($r['data'], true);
                    if (!$d) continue;
                    if (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0') continue;
                    $vNo = trim((string)($r['voucher_no'] ?? ($d['voucherNo'] ?? ($d['id'] ?? ''))));
                    $refs = extractAllTransfersFromRecord($d);
                    foreach ($refs as $ref) {
                        $clean = normalizeTransferRefStrict($ref);
                        if ($clean === '') continue;
                        $key = $vNo . '_' . $clean;
                        if (isset($seen[$key])) continue;
                        $seen[$key] = true;
                        $results[] = [
                            'voucherNo' => $vNo,
                            'table' => $tbl,
                            'tenantId' => $r['tenant_id'],
                            'ref' => $clean,
                            'cleanRef' => $clean,
                            'date' => $d['date'] ?? '',
                            'amount' => (float)($d['amountLocal'] ?? ($d['amount'] ?? 0)),
                            'bank' => $d['bankName'] ?? ($d['bank'] ?? ($d['fromAccount'] ?? ''))
                        ];
                    }
                }
            } catch(Exception $e) {}
        }
        return $results;
    }
}

// API endpoint لفحص رقم التحويلة فورياً ومطابقة قاعدة بيانات MariaDB
if ((isset($_GET['api']) && $_GET['api'] === 'check_duplicate') || (isset($_POST['api']) && $_POST['api'] === 'check_duplicate')) {
    header('Content-Type: application/json; charset=UTF-8');
    $authCheckUser = function_exists('requireMobileAuth') ? requireMobileAuth($pdo, false) : null;
    $ref = substr(trim((string)($_GET['ref'] ?? ($_POST['ref'] ?? ''))), 0, 60);
    $currentVNo = substr(trim((string)($_GET['voucherNo'] ?? ($_POST['voucherNo'] ?? ''))), 0, 40);
    $tId = !empty($authCheckUser['tenant_id']) ? (int)$authCheckUser['tenant_id'] : (!empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12);

    if ($ref === '') {
        echo json_encode(['valid' => true, 'duplicate' => false, 'message' => 'رقم فارغ']);
        exit;
    }

    $cleanDigits = preg_replace('/\D/', '', $ref);
    $safeRef = htmlspecialchars($ref, ENT_QUOTES, 'UTF-8');
    if (strlen($cleanDigits) < 6) {
        echo json_encode([
            'valid' => false,
            'isShort' => true,
            'duplicate' => false,
            'length' => strlen($cleanDigits),
            'error' => "رقم التحويلة ({$safeRef}) أقل من 6 أرقام! يشترط النظام 6 أرقام على الأقل، ويمنع الحفظ نهائياً لأي تحويلة أقل من 6 أرقام."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $dup = findTransferDuplicateInDb($pdo, $ref, $currentVNo, $tId);
    if ($dup) {
        echo json_encode([
            'valid' => false,
            'duplicate' => true,
            'isShort' => false,
            'error' => "عفواً، رقم التحويلة ({$safeRef}) مكرر ومسجل مسبقاً في السند رقم ({$dup['voucherNo']}) لتطابق آخر 6 أرقام ({$dup['matchedSuffix']}).",
            'matchedVoucher' => $dup['voucherNo'],
            'matchedRef' => $dup['ref'],
            'matchedDate' => $dup['date'],
            'matchedAmount' => $dup['amount'],
            'matchedBank' => $dup['bank'],
            'matchedSuffix' => $dup['matchedSuffix'],
            'matchType' => $dup['matchType']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'valid' => true,
            'duplicate' => false,
            'isShort' => false,
            'message' => 'رقم التحويلة سليم وغير مسجل مسبقاً'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// API endpoint لحفظ وترحيل السند مباشرة إلى قاعدة البيانات MariaDB من اللابتوب
if ((isset($_GET['api']) && $_GET['api'] === 'save_voucher') || (isset($_POST['api']) && $_POST['api'] === 'save_voucher')) {
    header('Content-Type: application/json; charset=UTF-8');
    // يتطلب مستخدماً موثّقاً، ورقم الشركة من حسابه فقط
    $authUser = requireMobileAuth($pdo, true);
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: $_POST;
    if ($payload) {
        $voucherNo = trim((string)($payload['id'] ?? ($payload['voucherNo'] ?? '')));
        $idempotencyKey = trim((string)($payload['idempotencyKey'] ?? ($payload['client_draft_id'] ?? '')));
        $tId = !empty($authUser['tenant_id']) ? (int)$authUser['tenant_id'] : (int)($GLOBAL_TENANT_ID ?: 12);

        // ═══ فحص الضوابط المحاسبية والأمنية على الخادم (Server-Side Accounting Validation) ═══
        $srvAmtLocal = (float)($payload['amountLocal'] ?? ($payload['amount'] ?? ($payload['sdg'] ?? 0)));
        $srvFromAcc = trim((string)($payload['fromAccount'] ?? ($payload['from'] ?? '')));
        $srvToAcc = trim((string)($payload['toAccount'] ?? ($payload['to'] ?? '')));

        if ($srvAmtLocal <= 0) {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: لا يمكن حفظ سند بقيمة صفرية أو سالبة.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($srvFromAcc === '' || $srvToAcc === '') {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: يجب تحديد كل من حساب الصرف وحساب الاستلام.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strtolower($srvFromAcc) === strtolower($srvToAcc)) {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: لا يمكن أن يكون حساب الصرف هو نفس حساب الاستلام (قيد ذاتي مغلق).'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // شروط التحويل المالي الصارمة لنسخة اللابتوب:
        // 1. يمنع الحفظ نهائياً إذا كانت التحويلة أقل من ستة أرقام.
        // 2. يمنع تكرار آخر ستة أرقام نهائياً مع أي تحويلة في النظام أو داخل السند نفسه.
        $bankTransfers = [];
        if (!empty($payload['bankTransfers']) && is_array($payload['bankTransfers'])) {
            $bankTransfers = $payload['bankTransfers'];
        } elseif (!empty($payload['mobileTransferNo'])) {
            $bankTransfers[] = ['transferNo' => $payload['mobileTransferNo'], 'amount' => $srvAmtLocal];
        }

        $seenSuffixes = [];
        $srvTransfersSum = 0.0;
        foreach ($bankTransfers as $bt) {
            $trNo = trim((string)($bt['transferNo'] ?? ''));
            $srvTransfersSum += (float)($bt['amount'] ?? 0);
            if ($trNo === '') continue;
            $cleanDigits = preg_replace('/\D/', '', $trNo);
            if (strlen($cleanDigits) < 6) {
                echo json_encode([
                    'success' => false,
                    'error' => "لا يمكن حفظ السند: رقم التحويلة ({$trNo}) يتكون من " . strlen($cleanDigits) . " أرقام فقط! يشترط النظام 6 أرقام على الأقل، ويمنع الحفظ نهائياً لأي تحويلة أقل من 6 أرقام."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $last6 = substr($cleanDigits, -6);
            if (isset($seenSuffixes[$last6])) {
                echo json_encode([
                    'success' => false,
                    'error' => "لا يمكن حفظ السند: يوجد تكرار في رقم التحويلة داخل نفس السند! تطابق آخر 6 أرقام ({$last6})."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $seenSuffixes[$last6] = true;

            $dup = findTransferDuplicateInDb($pdo, $trNo, $voucherNo, $tId);
            if ($dup) {
                $msg = "لا يمكن حفظ السند: رقم التحويلة ({$trNo}) مكرر ومسجل مسبقاً في السند رقم ({$dup['voucherNo']})";
                if (!empty($dup['date'])) $msg .= " بتاريخ ({$dup['date']})";
                if (!empty($dup['bank'])) $msg .= " لدى ({$dup['bank']})";
                $msg .= " لتطابق آخر 6 أرقام ({$dup['matchedSuffix']}). يمنع النظام تكرار آخر 6 أرقام نهائياً.";
                echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $srvChequesSum = 0.0;
        if (!empty($payload['cheques']) && is_array($payload['cheques'])) {
            foreach ($payload['cheques'] as $chq) {
                $srvChequesSum += (float)($chq['amount'] ?? 0);
            }
        }

        $srvMethod = trim((string)($payload['paymentMethod'] ?? ($payload['method'] ?? 'cash')));
        if (in_array($srvMethod, ['mobile', 'bank', 'transfer'], true) && $srvTransfersSum > 0 && abs($srvTransfersSum - $srvAmtLocal) > 1.0) {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: مجموع التحويلات البنكية لا يطابق إجمالي مبلغ السند.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (in_array($srvMethod, ['check', 'cheque'], true) && $srvChequesSum > 0 && abs($srvChequesSum - $srvAmtLocal) > 1.0) {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: مجموع الشيكات لا يطابق إجمالي مبلغ السند.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($srvMethod === 'hybrid' && ($srvTransfersSum + $srvChequesSum) > 0 && abs(($srvTransfersSum + $srvChequesSum) - $srvAmtLocal) > 1.0) {
            echo json_encode(['success' => false, 'error' => 'رفض محاسبي: مجموع التحويلات والشيكات لا يطابق إجمالي مبلغ السند.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // توثيق بيانات الرقابة الجنائية الرقمية (Forensic Audit Trail)
        $payload['serverSavedAt'] = date('Y-m-d H:i:s');
        $payload['savedByUserId'] = $authUser['id'] ?? ($authUser['user_id'] ?? null);
        $payload['clientIp'] = $_SERVER['REMOTE_ADDR'] ?? '';

        try {
            if (!empty($idempotencyKey)) {
                $stmtCheckIdem = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? LIMIT 1");
                $stmtCheckIdem->execute([$tId, $idempotencyKey]);
                $existingIdem = $stmtCheckIdem->fetch(PDO::FETCH_ASSOC);
                if ($existingIdem) {
                    $d = json_decode($existingIdem['data'], true);
                    echo json_encode(['success' => true, 'id' => $d['voucherNo'] ?? ($d['id'] ?? $voucherNo), 'idempotent' => true]);
                    exit;
                }
            }
            $jsonRecord = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $stmtCheck = $pdo->prepare("SELECT id FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?)");
            $stmtCheck->execute([$tId, $voucherNo, $voucherNo]);
            $existingDbId = $stmtCheck->fetchColumn();
            if ($existingDbId) {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingDbId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tId, $jsonRecord]);
            }
            try {
                $syncId = 'sync-' . bin2hex(random_bytes(6));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW())");
                $stmtSync->execute([$syncId, $voucherNo, $existingDbId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch(Exception $es) {}
            echo json_encode(['success' => true, 'id' => $voucherNo]);
        } catch(Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No payload']);
    }
    exit;
}
// الدستور السادس (6 من 14): بوابة التزامن الصاروخي بالمللي ثانية (Nano-Packet Flash Sync & CRDT Bridge: جوال ↔ لابتوب ↔ أوفلاين)
if (isset($_GET['api']) && $_GET['api'] === 'flash_delta_sync') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $rawPayload = file_get_contents('php://input');
    $req = $rawPayload ? json_decode($rawPayload, true) : [];
    if (!is_array($req)) $req = [];
    $effTenant = !empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12;
    $pushedIds = [];
    $conflictsResolved = 0;
    try {
        if ($pdo) {
            // 1. دمج حزم الأوفلاين (CRDT Nano-Packets) القادمة من الجوال أو اللابتوب ذرياً بدون تضارب
            $crdtQueue = (!empty($req['crdtQueue']) && is_array($req['crdtQueue'])) ? $req['crdtQueue'] : [];
            if (!empty($crdtQueue)) {
                $pdo->beginTransaction();
                foreach ($crdtQueue as $pkt) {
                    if (!is_array($pkt)) continue;
                    $rec = (isset($pkt['record']) && is_array($pkt['record'])) ? $pkt['record'] : $pkt;
                    $vNo = trim((string)($rec['id'] ?? $rec['voucherNo'] ?? ''));
                    if ($vNo === '') continue;
                    $idemKey = trim((string)($rec['idempotencyKey'] ?? $pkt['idempotencyKey'] ?? ('crdt_' . $vNo)));
                    $incHlc = (string)($rec['hlcTimestamp'] ?? $pkt['hlcTimestamp'] ?? '');
                    $incTs  = (int)($rec['updatedAtMs'] ?? $pkt['updatedAtMs'] ?? round(microtime(true) * 1000));
                    $rec['idempotencyKey'] = $idemKey;
                    $rec['hlcTimestamp']   = $incHlc;
                    $rec['updatedAtMs']    = $incTs;

                    // فحص السجل الحالي لمنع الكتابة الفوقية العمياء (CRDT Last-Write-Wins + Field Delta Merge)
                    $chk = $pdo->prepare("SELECT id, data FROM single_payments WHERE tenant_id = ? AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ?) LIMIT 1");
                    $chk->execute([$effTenant, $vNo, $idemKey]);
                    $existingRow = $chk->fetch(PDO::FETCH_ASSOC);

                    if ($existingRow) {
                        $oldData = json_decode($existingRow['data'], true);
                        if (is_array($oldData)) {
                            $oldTs = (int)($oldData['updatedAtMs'] ?? 0);
                            $oldHlc = (string)($oldData['hlcTimestamp'] ?? '');
                            // دمج حقول الدلتا ذرياً مع الاحتفاظ بأحدث قيمة لكل حقل (CRDT Field-Level Merge)
                            if ($oldTs > $incTs && $oldHlc > $incHlc) {
                                $merged = array_merge($rec, $oldData);
                                $conflictsResolved++;
                            } else {
                                $merged = array_merge($oldData, $rec);
                                if ($oldTs > 0 && $oldTs !== $incTs) $conflictsResolved++;
                            }
                            $rec = $merged;
                        }
                        $jsonRec = json_encode($rec, JSON_UNESCAPED_UNICODE);
                        $upd = $pdo->prepare("UPDATE single_payments SET data = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                        $upd->execute([$jsonRec, $existingRow['id'], $effTenant]);
                    } else {
                        $jsonRec = json_encode($rec, JSON_UNESCAPED_UNICODE);
                        $ins = $pdo->prepare("INSERT INTO single_payments (tenant_id, data) VALUES (?, ?)");
                        $ins->execute([$effTenant, $jsonRec]);
                    }
                    $pushedIds[] = $vNo;
                }
                $pdo->commit();
            }

            // 2. جلب أحدث السندات المتزامنة (جوال ↔ لابتوب) في كسر من الثانية
            $stmtAll = $pdo->prepare("SELECT data, updated_at FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
            $stmtAll->execute([$effTenant]);
            $records = [];
            $latestDigest = '';
            while ($r = $stmtAll->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true);
                if (is_array($d)) {
                    if (empty($d['serverUpdatedAt']) && !empty($r['updated_at'])) {
                        $d['serverUpdatedAt'] = $r['updated_at'];
                    }
                    $records[] = $d;
                }
            }
            $latestDigest = md5(json_encode($records, JSON_UNESCAPED_UNICODE));
            $clientDigest = isset($req['clientDigest']) ? (string)$req['clientDigest'] : '';

            echo json_encode([
                'success' => true,
                'serverTimeMs' => round(microtime(true) * 1000),
                'pushedIds' => $pushedIds,
                'conflictsResolved' => $conflictsResolved,
                'digest' => $latestDigest,
                'hasChanges' => ($clientDigest === '' || $clientDigest !== $latestDigest),
                'records' => ($clientDigest !== '' && $clientDigest === $latestDigest) ? [] : $records,
                'totalCount' => count($records)
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => 'PDO unavailable']);
        }
    } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) {
            try { $pdo->rollBack(); } catch (Exception $re) {}
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
$prefix = 'E' . date('ym');
$maxSeq = 0;
try {
    if ($pdo) {
        $effTenant = !empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12;
        $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ?");
        $stmt->execute([$effTenant]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($row['data'], true);
            $id = isset($d['id']) ? (string)$d['id'] : '';
            if (strpos($id, $prefix) === 0) {
                $seq = (int)substr($id, strlen($prefix));
                if ($seq > $maxSeq) $maxSeq = $seq;
            }
        }
    }
} catch (Exception $e) {
    // fallback
}
$voucher_no = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
$page_title = 'حركة مالية';
$SERVER_SINGLE_PAYMENTS_json = '[]';
$SERVER_ALL_TRANSFERS_json = '[]';
try {
    if ($pdo) {
        $effTenant = !empty($GLOBAL_TENANT_ID) ? (int)$GLOBAL_TENANT_ID : 12;
        $stmt = $pdo->prepare("SELECT data FROM single_payments WHERE tenant_id = ? ORDER BY id ASC");
        $stmt->execute([$effTenant]);
        $arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = json_decode($row['data'], true);
        }
        $SERVER_SINGLE_PAYMENTS_json = json_encode($arr);
        $SERVER_ALL_TRANSFERS_json = json_encode(fetchAllDbTransfers($pdo, $effTenant), JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js?v=20261029_fast"></script>
<script>
(function(){
    var l = localStorage.getItem('erp_lang') || 'ar';
    if(l==='en') {
        document.documentElement.setAttribute('dir','ltr');
        document.documentElement.setAttribute('lang','en');
    }
    var width = window.innerWidth || screen.width;
    var ua = navigator.userAgent || '';
    var isRealMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
    var params = new URLSearchParams(window.location.search);
    if (params.get('force_desktop') !== '1' && isRealMobileUA && width < 768) {
        window.location.replace('mobile-voucher.php' + window.location.search);
    }
})();
</script>
<head>

<script>
(function initScreenMaster13ToneEarly() {
  var ckMatch = document.cookie.match(/(?:^|; )magaies_light_tone=([^;]*)/);
  var rawTone = (ckMatch ? decodeURIComponent(ckMatch[1]) : '') || localStorage.getItem('magaies_light_tone') || localStorage.getItem('pur_light_tone_palette') || '';
  var darkTones = { dark_midnight: 'midnight_obsidian', pure_oled: 'pure_oled', mocha_brown: 'mocha_brown', slate_twilight: 'slate_twilight' };
  var lightToneThemes = { ivory_default: 'warm_cream', royal_bronze: 'warm_cream', platinum_gray: 'exec_gray', crystal_silver: 'ultra_white', sage_pearl: 'warm_cream', sky_alabaster: 'ultra_white', rose_blossom: 'warm_cream', warm_mocha_dark: 'warm_cream', deep_slate_dark: 'exec_gray' };
  var activeTone = (darkTones[rawTone] || lightToneThemes[rawTone]) ? rawTone : 'ivory_default';
  var isDark = !!darkTones[activeTone];
  var activeKey = darkTones[activeTone] || lightToneThemes[activeTone] || 'warm_cream';
  document.documentElement.setAttribute('data-light-tone', activeTone);
  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.classList.toggle('light-theme', !isDark);
  document.documentElement.classList.toggle('dark-theme', isDark);
  document.addEventListener('DOMContentLoaded', function() {
    if (document.body) {
      document.body.setAttribute('data-light-tone', activeTone);
      document.body.setAttribute('data-theme', activeKey);
      document.body.classList.toggle('light-theme', !isDark);
      document.body.classList.toggle('dark-theme', isDark);
    }
  });
})();
</script>
    <link rel="stylesheet" href="css/local-fonts.css?v=2027">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حركة مالية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css"><link rel="stylesheet" href="css/theme.css?v=<?php echo file_exists('css/theme.css') ? filemtime('css/theme.css') : '12'; ?>">
    <link rel="stylesheet" href="css/components.css?v=1788466095949">
    <link rel="stylesheet" href="css/sales-invoice.css">
    
<link rel="stylesheet" href="css/screens/single-cash-payment-s1.css?v=<?= @filemtime(__DIR__ . '/css/screens/single-cash-payment-s1.css') ?: '2027' ?>">
<!-- 👑 USER REQUESTED TYPOGRAPHY ENHANCEMENTS (+2 & +3 SIZES) 👑 -->
<link rel="stylesheet" id="user-requested-typography-override" href="css/screens/single-cash-payment-s2.css?v=<?= @filemtime(__DIR__ . '/css/screens/single-cash-payment-s2.css') ?: '2027' ?>">
</head>
<body>

    <!-- Ambient Animated Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <div class="pos-wrapper" id="scp-proportional-root" style="width: var(--erp-ergo-max-w, calc(100% - 24px)) !important; max-width: var(--erp-ergo-max-w, calc(100% - 24px)) !important; margin: 0 auto !important; padding: 6px 8px 26px !important; box-sizing: border-box;">


        <!-- NEW ADVANCED HEADER -->
        <?php $header_theme_class = 'header-theme-sales'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- (Moved SUB HEADER BAR inside main form) -->
        <div class="split-layout-wrapper" id="main-layout-wrapper" style="display: block; margin-top: 8px; width: 100%;">
            <!-- Main Content area (Full width) -->
            <div class="main-panel" id="main-form-panel" style="padding: 4px; margin-bottom: 10px; width: 100%;">

                <div id="edit-warning-banner" style="display: none; background: rgba(220, 38, 38, 0.15); border: 2px solid #ef4444; color: #f87171; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.2); animation: pulse-glow 2s infinite;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <div>
                            <strong style="font-size: 1.1rem; display: block; margin-bottom: 4px;">تنبيه هام: أنت تقوم بعرض سند قديم تم حفظه مسبقاً!</strong>
                            <span style="font-size: 0.95rem;">أي تغييرات تقوم بها سيتم تطبيقها وتعديل هذا السند. هل تريد التعديل حقاً؟</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Warning Modal (شاشة تحذيرية للتعديل) -->
                <div id="edit-warning-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: none; z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
                    <div style="background: linear-gradient(135deg, #7f1d1d, #991b1b); border: 2px solid #f87171; border-radius: 20px; padding: 40px; width: 90%; max-width: 600px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 40px rgba(239,68,68,0.3); transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <div style="font-size: 5rem; margin-bottom: 20px; animation: pulse-glow 2s infinite;">⚠️</div>
                        <h1 style="color: #ffffff; font-size: 2.2rem; font-weight: 900; margin-bottom: 15px; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">تنبيه: أنت تقوم بتعديل سند قديم!</h1>
                        <p style="color: #fca5a5; font-size: 1.3rem; font-weight: 700; margin-bottom: 30px; line-height: 1.6;">هذا السند تم حفظه مسبقاً. أي تغييرات تقوم بها الآن ستؤدي إلى تعديل البيانات الأصلية للسند.<br><br><span style="color:#fff;">هل تريد التعديل حقاً؟</span></p>
                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <button type="button" onclick="confirmEditOldVoucher()" class="btn" style="background: #ef4444; color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 2px solid #fca5a5; border-radius: 12px; cursor: pointer; box-shadow: 0 10px 20px rgba(239,68,68,0.4);">نعم، أريد التعديل</button>
                            <button type="button" onclick="cancelEditOldVoucher()" class="btn" style="background: rgba(255,255,255,0.1); color: white; font-size: 1.3rem; font-weight: 900; padding: 15px 30px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; cursor: pointer;">لا، تراجع</button>
                        </div>
                    </div>
                </div>

                <div class="interactive-columns" style="display: block;">
                    <!-- ONE SINGLE RECTANGLE -->
                    <div class="focus-zone single-unified-form" style="padding: 14px 18px; background: var(--surface, rgba(15, 23, 42, 0.45)); border: 1.5px solid rgba(56, 189, 248, 0.25); border-radius: 16px; box-shadow: 0 10px 28px rgba(0,0,0,0.3);">
                        
                        <!-- Top Row: Date, Balances, Voucher Number -->
                        <div class="sub-header-bar" style="margin-top: 0; margin-bottom: 12px; padding: 6px 12px; border-radius: 12px; background: rgba(0,0,0,0.22); border: 1px solid rgba(255,255,255,0.08); box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);">
                            <!-- Date Only (Inner Picker Icon Only) -->
                            <div class="header-group group-primary" style="flex:0 0 auto;">
                                <div class="crystal-pill date-crystal hover-scale" style="padding:2px 4px; background:transparent; border:none; box-shadow:none;">
                                    <input type="date" id="hdr-date" class="date-input" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            
                            <!-- Balances (Expanded Middle Space, Sleek Compact Text) -->
                            <div class="header-group group-balances" style="flex:1 1 auto; display:flex; justify-content:center; min-width:0;">
                                <div class="crystal-pill bal-crystal hover-scale" style="width:100%; max-width:860px; justify-content:space-around; padding:4px 14px; min-height:42px;">
                                    <div class="bal-card cash-card">
                                        <span class="bal-icon">💵</span>
                                        <div class="bal-info">
                                            <span class="bal-label">نقدية</span>
                                            <strong id="bal-checks" class="bal-val">0</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card check-card">
                                        <span class="bal-icon">🧾</span>
                                        <div class="bal-info">
                                            <span class="bal-label">شيكات</span>
                                            <strong id="bal-debt" class="bal-val">0</strong>
                                        </div>
                                    </div>
                                    <div class="bal-divider"></div>
                                    <div class="bal-card loan-card">
                                        <span class="bal-icon">💼</span>
                                        <div class="bal-info">
                                            <span class="bal-label">عهد / وعد</span>
                                            <strong id="bal-loan" class="bal-val">0</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Next / Prev -->
                            <div class="header-group group-nav">
                                <div class="crystal-pill nav-crystal hover-scale" id="voucher-nav-pill">
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only prev-btn" onclick="loadPreviousVoucher()" title="السند السابق">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                    </button>
                                    <div class="nav-badge" id="voucher-nav-badge">
                                        <span class="badge-label">رقم السند</span>
                                        <span id="voucher-number" class="badge-val"><?php echo $voucher_no; ?></span>
                                    </div>
                                    <button class="crystal-btn-sm btn-blue-glass crystal-btn-icon-only next-btn" onclick="loadNextVoucher()" title="السند التالي">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 1: Labels Above Boxes (Currency | Local Amount | Rate | Foreign Amount) -->
                        <div class="scp-top-amount-row" style="display: grid; grid-template-columns: 1.05fr 1.95fr 1.05fr 1.95fr; gap: 12px; align-items: end; margin-bottom: 12px; background: var(--bg-secondary, rgba(0,0,0,0.2)); padding: 10px 14px; border-radius: 14px; border: 2px solid rgba(251, 191, 36, 0.7);">
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                <label class="label" style="display:flex; align-items:center; gap:4px; color:var(--amber, #fbbf24); font-size:0.84rem; font-weight:800; margin-bottom:0; white-space:nowrap;">💱 العملة</label>
                                <select class="form-input system-currency-select input-glow-focus hover-glow" id="currency" data-next="exchange-rate" onchange="updateCurrency(); document.getElementById('exchange-rate').focus(); document.getElementById('exchange-rate').select();" style="height: 58px !important; border-radius: 10px; width: 100%; font-size: 1.25rem !important; font-weight: 900; padding: 0 10px;">
                                    <option value="SDG">جنيه (SDG)</option>
                                    <option value="USD">دولار (USD)</option>
                                    <option value="AED">درهم (AED)</option>
                                </select>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0; position: relative;">
                                <label id="label-box-1" class="label" style="display:flex; align-items:center; gap:4px; color:var(--text-primary, #cbd5e1); font-size:0.86rem; font-weight:800; margin-bottom:0; white-space:nowrap;">💰 المبلغ (محلي)</label>
                                <div style="position: relative; width: 100%; min-width: 0;">
                                    <span id="amount-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: var(--primary-color, #3b82f6); color: white; padding: 3px 9px; border-radius: 7px; font-weight: 800; font-size: 0.8rem; pointer-events: none; z-index: 5;">SDG</span>
                                    <input type="text" class="form-input amount hover-scale input-glow-focus hover-glow" id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === 'function') calcEquiv('local');" onkeydown="if(event.key.length===1 && !/[0-9٠-٩۰-۹.,+\-*\/()]/.test(event.key) && !event.ctrlKey && !event.metaKey) event.preventDefault();" oninput="if(typeof window.handleSmoothAmountInput === 'function') { window.handleSmoothAmountInput(this, 'local'); } else { let val = this.value.replace(/[^0-9.]/g, ''); this.value = val; calcEquiv('local'); }" style="height: 58px !important; box-sizing: border-box !important; font-size: 1.68rem !important; font-weight: 900; text-align: center; background: #ffffff !important; color: #b91c1c !important; border: 3px solid #ef4444 !important; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 1px; direction: ltr; border-radius: 11px; width: 100%; padding-left: 54px;">
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                <label class="label scp-rate-label" style="display:flex; align-items:center; gap:4px; color:#fbbf24; font-size:0.84rem; font-weight:800; margin-bottom:0; white-space:nowrap;">⚖️ الصرف</label>
                                <input type="number" class="form-input input-glow-focus hover-glow" id="exchange-rate" value="<?php echo htmlspecialchars((string)$systemUsdRate); ?>" placeholder="الصرف" readonly style="text-align: center; cursor:pointer; background:linear-gradient(135deg, #1c1504, #2e2107); color:#ffd700; font-weight:900; height: 58px !important; border-radius: 10px; font-size: 1.48rem !important; letter-spacing: 1px; width: 100%; border: 2px solid #fbbf24; box-shadow: 0 0 12px rgba(251, 191, 36, 0.35);" onclick="if(typeof window.openGlobalExchangeRateModal === 'function') window.openGlobalExchangeRateModal((r)=>{this.value=Math.round(r); this.dataset.userEdited='1'; if(typeof recalcAll === 'function') recalcAll(); else if(typeof recalcTotals==='function') recalcTotals(); else if(typeof calcTotals==='function') calcTotals(); else if(typeof window.updateCurrency==='function') window.updateCurrency();});">
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0; position: relative;">
                                <label id="label-box-2" class="label" style="display:flex; align-items:center; gap:4px; color:var(--text-primary, #cbd5e1); font-size:0.86rem; font-weight:800; margin-bottom:0; white-space:nowrap;">💵 القيمة (أجنبي)</label>
                                <div style="position: relative; width: 100%; min-width: 0;">
                                    <span id="amount-foreign-currency-symbol" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 3px 9px; border-radius: 7px; font-weight: 800; font-size: 0.8rem; pointer-events: none; z-index: 5;">USD</span>
                                    <input type="text" class="form-input amount input-glow-focus hover-glow" id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === 'function') calcEquiv('foreign');" onkeydown="if(event.key.length===1 && !/[0-9٠-٩۰-۹.,+\-*\/()]/.test(event.key) && !event.ctrlKey && !event.metaKey) event.preventDefault();" oninput="if(typeof window.handleSmoothAmountInput === 'function') { window.handleSmoothAmountInput(this, 'foreign'); } else { let val = this.value.replace(/[^0-9.]/g, ''); this.value = val; calcEquiv('foreign'); }" style="height: 58px !important; box-sizing: border-box !important; font-size: 1.68rem !important; text-align: center; background: #ffffff !important; color: #b91c1c !important; font-weight: 900; font-family: 'Inter', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 1px; direction: ltr; border-radius: 11px; width: 100%; padding-left: 54px; cursor: text; border: 2.5px solid #dc2626 !important;">
                                </div>
                            </div>

                            <!-- تفقيط مقسوم إلى جزئين مستقلين (المحلي لوحده والأجنبي لوحده بدون كلمة تفقيط) -->
                            <div id="live-tafqeet-bar" style="grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 2px;">
                                <div style="padding: 5px 12px; border-radius: 8px; background: rgba(251, 191, 36, 0.12); border: 1.5px solid rgba(251, 191, 36, 0.45); text-align: center;">
                                    <span id="live-tafqeet-text" style="color: #fef08a; font-size: 0.92rem; font-weight: 900;">صفر جنيه سوداني لا غير</span>
                                </div>
                                <div style="padding: 5px 12px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); border: 1.5px solid rgba(16, 185, 129, 0.45); text-align: center;">
                                    <span id="live-tafqeet-foreign" style="color: #6ee7b7; font-size: 0.92rem; font-weight: 900; direction: rtl;">صفر دولار أمريكي لا غير</span>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: من حساب | إلى حساب (الدائرة المتوهجة في المنتصف تماماً بدون خط متحرك) -->
                        <div class="from-to-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 62px; margin-bottom: 8px; position: relative;">
                            <div class="epic-arrow-container" onclick="swapAccounts()" role="button" aria-label="عكس الحسابات" title="عكس حساب الصرف والاستلام">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fde047" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 0 8px rgba(253,224,71,0.95));">
                                    <line x1="20" y1="12" x2="4" y2="12"></line>
                                    <polyline points="10 18 4 12 10 6"></polyline>
                                </svg>
                            </div>

                            <!-- من حساب (التسمية فوق المستطيل) -->
                            <div class="scp-account-card scp-from-card" data-swap="1">
                                <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                                    <label class="label" style="display:flex; align-items:center; gap:5px; color:#fecaca; margin-bottom:0; font-weight:800; white-space:nowrap; font-size: 0.92rem !important;">
                                        <span style="display:inline-flex; align-items:center; justify-content:center; width: 20px; height: 20px; background: rgba(239, 68, 68, 0.25); border: 1px solid rgba(248, 113, 113, 0.6); border-radius: 50%; color: #fff;">
                                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                        </span>
                                        من حساب <span style="color:#ffffff;">(صرف)</span>
                                    </label>
                                    <div class="custom-dropdown-input-wrap" style="width: 100%; min-width: 0; position: relative;">
                                        <input type="hidden" id="from-account">
                                        <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" id="from-account-search" placeholder="ابحث عن حساب الصرف..." onfocus="showAccountDropdown('from')" onblur="hideAccountDropdownDelayed('from')" oninput="filterAccountDropdown('from'); if(typeof adjustAccountNameFontSize==='function') adjustAccountNameFontSize(this);" style="width: 100%; height: 50px !important; padding: 6px 14px 6px 76px; border-radius: 10px; font-weight: 900; font-size: 1.18rem !important;">
                                        <button type="button" id="from-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('from-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 6px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.75rem; font-weight: 800; padding: 2px 6px; height: 26px; display: none;" title="عرض كشف حساب الأستاذ العام">📖 الأستاذ</button>
                                        <div class="custom-dropdown-list" id="from-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: var(--surface, #1e293b); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; max-height: 240px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: none; margin-top: 4px;"></div>
                                    </div>
                                </div>
                                <div id="from-account-live-bal" style="display: none; align-items: center; justify-content: space-between; gap: 6px; margin-top: 4px; padding: 3px 10px; border-radius: 7px; background: rgba(0,0,0,0.35); border: 1px solid rgba(248, 113, 113, 0.45); font-size: 0.82rem; font-weight: 800; color: #fecaca; flex-wrap: wrap;"></div>
                            </div>
                            <!-- إلى حساب (التسمية فوق المستطيل) -->
                            <div class="scp-account-card scp-to-card" data-swap="1">
                                <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                                    <label class="label" style="display:flex; align-items:center; gap:5px; color:#a7f3d0; margin-bottom:0; font-weight:800; white-space:nowrap; font-size: 0.92rem !important;">
                                        <span style="display:inline-flex; align-items:center; justify-content:center; width: 20px; height: 20px; background: rgba(16, 185, 129, 0.25); border: 1px solid rgba(52, 211, 153, 0.6); border-radius: 50%; color: #fff;">
                                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                        </span>
                                        إلى حساب <span style="color:#ffffff;">(استلام)</span>
                                    </label>
                                    <div class="custom-dropdown-input-wrap" style="width: 100%; min-width: 0; position: relative;">
                                        <input type="hidden" id="to-account">
                                        <input type="text" autocomplete="off" class="form-input input-glow-focus hover-glow" id="to-account-search" placeholder="ابحث عن حساب الاستلام..." onfocus="showAccountDropdown('to')" onblur="hideAccountDropdownDelayed('to')" oninput="filterAccountDropdown('to'); if(typeof adjustAccountNameFontSize==='function') adjustAccountNameFontSize(this);" style="width: 100%; height: 50px !important; padding: 6px 14px 6px 76px; border-radius: 10px; font-weight: 900; font-size: 1.18rem !important;">
                                        <button type="button" id="to-account-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('to-account').value)" class="crystal-btn-sm btn-blue-glass" style="position: absolute; left: 6px; top: 50%; transform: translateY(-50%); z-index: 5; font-size: 0.75rem; font-weight: 800; padding: 2px 6px; height: 26px; display: none;" title="عرض كشف حساب الأستاذ العام">📖 الأستاذ</button>
                                        <div class="custom-dropdown-list" id="to-account-dropdown-list" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: var(--surface, #1e293b); border: 1px solid var(--border, rgba(255,255,255,0.1)); border-radius: 8px; max-height: 240px; overflow-y: auto; text-align: right; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: none; margin-top: 4px;"></div>
                                    </div>
                                </div>
                                <div id="to-account-live-bal" style="display: none; align-items: center; justify-content: space-between; gap: 6px; margin-top: 4px; padding: 3px 10px; border-radius: 7px; background: rgba(0,0,0,0.35); border: 1px solid rgba(52, 211, 153, 0.45); font-size: 0.82rem; font-weight: 800; color: #a7f3d0; flex-wrap: wrap;"></div>
                            </div>
                        </div>

                        <!-- Row 3+4 مدمجة في سطر واحد: المستفيد | مركز التكلفة | البيان المالي -->
                        <div class="scp-beneficiary-row" style="display: grid; grid-template-columns: 0.9fr 0.75fr 1.65fr; gap: 10px; align-items: center; margin-bottom: 8px; background: var(--bg-secondary, rgba(255,255,255,0.02)); padding: 6px 12px !important; border-radius: 12px !important; border: 1.5px solid rgba(34, 211, 238, 0.55);">
                            <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                <label class="label" style="color:var(--amber, #fbbf24); font-weight:800; margin-bottom:0; white-space:nowrap; font-size:0.86rem;">👨‍💼 المستفيد</label>
                                <input type="text" class="form-input input-glow-focus hover-glow" id="beneficiary-name" data-next="cost-center" placeholder="المستفيد (اختياري)..." style="height: 36px; border-radius: 8px; border-color: rgba(251, 191, 36, 0.4); flex: 1; min-width: 0; font-size:0.95rem;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                <label class="label" style="margin-bottom:0; white-space:nowrap; font-size:0.86rem;">📊 المركز</label>
                                <select class="form-input input-glow-focus hover-glow" id="cost-center" data-next="to-statement" style="height: 36px; border-radius: 8px; flex: 1; min-width: 0; font-size:0.92rem;"></select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                <input type="text" class="form-input input-glow-focus hover-glow" id="to-statement" data-next="btn-save" placeholder="البيان المالي (تفاصيل ومبرر الحركة)..." style="height: 36px; border-radius: 8px; flex: 1; min-width: 0; font-size:0.95rem;">
                                <button type="button" onclick="autoCategorize()" id="btn-auto-categorize" style="background: linear-gradient(135deg, #c084fc, #9333ea); color: white; border: none; border-radius: 8px; padding: 0 10px; height: 36px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-weight: 800; font-size:0.82rem; white-space: nowrap;" title="توجيه محاسبي ذكي">🪄 توقع</button>
                                <input type="hidden" id="from-statement">
                            </div>
                        </div>

                        <!-- Hidden radios to prevent legacy JS checks breaking -->
                        <div style="display: none;">
                            <input type="radio" name="payment_method" value="cash" id="radio-cash">
                            <input type="radio" name="payment_method" value="check" id="radio-check">
                            <input type="radio" name="payment_method" value="mobile" id="radio-mobile" checked>
                            <input type="radio" name="payment_method" value="credit" id="radio-credit">
                            <input type="radio" name="payment_method" value="hybrid" id="radio-hybrid">
                        </div>

                        <!-- Unified Payment details panel styled like Sales/Purchase -->
                        <style>
                            .pay-toggle-btn {
                                background: rgba(255, 255, 255, 0.05);
                                border: 1px solid rgba(255, 255, 255, 0.1);
                                color: #94a3b8;
                                padding: 6px 14px;
                                border-radius: 20px;
                                font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; display: flex; align-items: center; gap: 8px;
                            }
                            .pay-toggle-btn.active {
                                background: rgba(56, 189, 248, 0.2) !important;
                                border-color: #38bdf8 !important;
                                color: #38bdf8 !important;
                                box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
                            }
                            #toggle-credit.active {
                                background: rgba(245, 158, 11, 0.2) !important;
                                border-color: #fbbf24 !important;
                                color: #fbbf24 !important;
                                box-shadow: 0 0 12px rgba(245, 158, 11, 0.3);
                            }
                            #toggle-cash.active {
                                background: rgba(16, 185, 129, 0.2) !important;
                                border-color: #34d399 !important;
                                color: #34d399 !important;
                                box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
                            }
                            #toggle-bank.active {
                                background: rgba(59, 130, 246, 0.2) !important;
                                border-color: #60a5fa !important;
                                color: #60a5fa !important;
                                box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
                            }
                            #toggle-cheque.active {
                                background: rgba(139, 92, 246, 0.2) !important;
                                border-color: #c084fc !important;
                                color: #c084fc !important;
                                box-shadow: 0 0 12px rgba(139, 92, 246, 0.3);
                            }
                            .payment-method-box {
                                transition: transform 0.3s ease, opacity 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
                            }
                            /* Auto-Expanding heights for payment containers */
                            #payment-panel,
                            .payment-layout,
                            .payment-details-card,
                            .payment-method-box,
                            #transfers-container,
                            #cheques-container {
                                height: auto !important;
                                min-height: fit-content !important;
                                overflow: visible !important;
                            }
                                /* Custom Number Properties Menu */
        #num-props-menu { display: none; position: absolute; z-index: 9999; background: rgba(15, 23, 42, 0.95); backdrop-filter: none; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); border-radius: 12px; padding: 10px; width: 260px; color: #cbd5e1; font-family: 'Inter', sans-serif; direction: rtl; }
        #num-props-menu .props-header { font-weight: bold; color: #38bdf8; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); text-align: center; font-size: 1.1rem; }
        #num-props-menu .props-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; cursor: pointer; border-radius: 6px; transition: background 0.2s; font-size: 0.95rem; }
        #num-props-menu .props-item:hover { background: rgba(255, 255, 255, 0.1); }
        #num-props-menu button.prop-btn { background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.5); color: #60a5fa; border-radius: 4px; padding: 2px 8px; cursor: pointer; font-size: 0.9rem; transition: transform 0.2s, opacity 0.2s, background-color 0.2s, border-color 0.2s, color 0.2s; }
        #num-props-menu button.prop-btn:hover { background: rgba(59, 130, 246, 0.4); color: #fff; }
    </style>
                        <div id="payment-panel" style="display: block; background: var(--surface, rgba(15, 23, 42, 0.4)); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; margin-top: 8px; margin-bottom: 6px;">
                            <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 6px 14px; background: var(--surface, rgba(15, 23, 42, 0.4)); border-bottom: 1px solid rgba(255,255,255,0.06); border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 0;">
                                <div class="panel-title" style="background: linear-gradient(135deg, #ff4d4d, #f87171); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.15rem; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; margin: 0; white-space: nowrap; flex: none;">
                                    <span>💳</span> طريقة وتفاصيل الدفع للمستند المجمع
                                </div>
                                
                                <!-- Sleek Payment Progress Bar (Inline) -->
                                <div class="payment-progress-container" style="flex: 1; display: flex; align-items: center; gap: 12px; min-width: 280px; padding: 0 10px;">
                                    <div class="progress-bar-wrapper" style="height: 10px; flex: 1; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); min-width: 120px;">
                                        <div id="prog-cash" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981, #34d399); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(16,185,129,0.3);"></div>
                                        <div id="prog-bank" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(59,130,246,0.3);"></div>
                                        <div id="prog-cheque" style="width: 0%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 0 8px rgba(139,92,246,0.3);"></div>
                                    </div>
                                    <span id="payment-status-badge" style="font-size: 0.78rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; transition: all 0.3s ease; white-space: nowrap; background: rgba(16, 185, 129, 0.15); color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);" data-status="fully-paid">💳 مدفوعة بالكامل</span>
                                </div>

                                <div class="payment-toggles" style="display: flex; gap: 8px; flex: none; flex-wrap: wrap;">
                                    <button type="button" class="pay-toggle-btn" id="toggle-cash" onclick="togglePaymentSection('cash')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">💵 نقدي</button>
                                    <button type="button" class="pay-toggle-btn active" id="toggle-bank" onclick="togglePaymentSection('bank')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">🏦 تحويل بنكي</button>
                                    <button type="button" class="pay-toggle-btn" id="toggle-cheque" onclick="togglePaymentSection('cheque')" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; padding: 6px 14px; border-radius: 20px; font-size: 1.2rem; font-weight: 900; padding: 8px 18px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">✍️ شيك</button>
                                    <button type="button" class="pay-toggle-btn" id="btn-financial-qr" onclick="FinancialOCR.openMobileQRBridge({ target: 'single' })" style="background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.22)); border: 1.5px solid #34d399; color: #34d399; border-radius: 20px; font-size: 1.15rem; font-weight: 900; padding: 8px 16px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16,185,129,0.25);" title="ربط الجوال بضغطة واحدة عبر QR لرفع الإشعار مباشرة من استوديو الهاتف أو الواتساب">
                                        <span id="btn-fin-qr-dot" style="width:9px;height:9px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                                        <span style="font-size: 1.25rem;">📱</span> ربط الجوال (QR)
                                    </button>
                                    <button type="button" class="pay-toggle-btn" id="btn-financial-wa-tray" onclick="if(window.InboundBankakManager){InboundBankakManager.toggleDrawer();}else if(typeof openWAModal==='function'){openWAModal();}" style="background: linear-gradient(135deg, rgba(37,211,102,0.24), rgba(22,163,74,0.22)); border: 1.5px solid #25D366; color: #4ade80; border-radius: 20px; font-size: 1.12rem; font-weight: 900; padding: 8px 15px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 7px;" title="سحب الإشعارات الواردة من حساب الواتساب المربوط مباشرة إلى الحركة">
                                        <span style="font-size: 1.2rem;">📥</span> منصة الإشعارات
                                    </button>
                                    <button type="button" class="pay-toggle-btn" onclick="FinancialOCR.openTrainer()" style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.4); color: #d8b4fe; padding: 8px 14px; border-radius: 20px; font-size: 1.05rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="شرح وتدريب النماذج للذكاء الاصطناعي">
                                        <span>🧠</span> تدريب النماذج
                                    </button>
                                </div>
                            </div>
                            
                            <div class="payment-layout" id="payment-layout" style="display: flex; gap: 15px; padding: 15px; flex-wrap: wrap;">
                                <div class="payment-details-card" style="flex: 1; min-width: 100%; display: flex; flex-direction: column; gap: 10px;">

                                    <!-- Cash section -->
                                    <div id="pay-cash-section" class="payment-method-box" style="background: rgba(16, 185, 129, 0.04); border: 1px solid rgba(16, 185, 129, 0.18); border-radius: 12px; padding: 12px 15px; display: none; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 8px; min-width: 200px; width: 200px; flex: none;">
                                            <span style="font-size: 1.2rem;">💵</span>
                                            <span style="font-weight: 900; color: #34d399; font-size: 1.25rem;">الدفع النقدي (كاش)</span>
                                        </div>
                                        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 320px; flex-wrap: wrap;">
                                            <span style="color: #94a3b8; font-size: 0.9rem;">سيتم السداد نقداً بالكامل من خلال الصندوق المحدد.</span>
                                        </div>
                                    </div>

                                    <!-- Bank Transfer section -->
                                    <div id="pay-bank-section" class="payment-method-box" style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.6), rgba(16, 185, 129, 0.15)); border: 1px solid rgba(16, 185, 129, 0.7); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(16, 185, 129, 0.15); backdrop-filter: none;">
                                        <div id="transfers-container" style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                                            <!-- Dynamic rows -->
                                        </div>
                                    </div>

                                    <!-- Cheque section -->
                                    <div id="pay-cheque-section" class="payment-method-box" style="background: linear-gradient(135deg, rgba(88, 28, 135, 0.6), rgba(168, 85, 247, 0.15)); border: 1px solid rgba(168, 85, 247, 0.7); border-radius: 12px; padding: 12px 15px; display: none; flex-direction: column; gap: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.6), inset 0 0 20px rgba(168, 85, 247, 0.15); backdrop-filter: none;">
                                        <div id="cheques-container" style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                                            <!-- Dynamic rows -->
                                        </div>
                                    </div>

                                    <!-- Unified Total Box Redesign -->
                                    <div id="unified-total-box" style="display: block !important; width: 100%; margin-top: 15px; padding: 10px 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: stretch; gap: 15px; flex-wrap: wrap;">
                                            <!-- Cheques Total -->
                                            <div class="total-glass-card cheque-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(168, 85, 247, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3s infinite alternate;"></div>
                                                <span style="color: #c084fc; font-size: 1.55rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 15px rgba(168,85,247,0.6);">مجموع الشيكات</span>
                                                <span id="unified-cheque-total" style="color: #e9d5ff; font-size: 2.5rem; font-weight: 900; z-index: 2; text-shadow: 0 0 20px rgba(168,85,247,0.9); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Bank Total -->
                                            <div class="total-glass-card bank-glow" style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.4); position: relative; overflow: hidden;">
                                                <div class="glow-orb" style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(16, 185, 129, 0.5); filter: blur(40px); border-radius: 50%; animation: pulseOrb 3.5s infinite alternate-reverse;"></div>
                                                <span style="color: #34d399; font-size: 1.55rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 15px rgba(16,185,129,0.6);">مجموع التحويلات</span>
                                                <span id="unified-bank-total" style="color: #a7f3d0; font-size: 2.5rem; font-weight: 900; z-index: 2; text-shadow: 0 0 20px rgba(16,185,129,0.9); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                            <!-- Grand Total -->
                                            <div class="total-glass-card grand-glow" style="flex: 1; min-width: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border-radius: 16px; background: rgba(56, 189, 248, 0.15); border: 2px solid rgba(56, 189, 248, 0.6); position: relative; overflow: hidden; transform: scale(1.02); box-shadow: 0 0 20px rgba(56,189,248,0.2);">
                                                <div class="glow-orb" style="position: absolute; top: -50px; left: -50px; width: 120px; height: 120px; background: rgba(56, 189, 248, 0.6); filter: blur(50px); border-radius: 50%; animation: pulseOrb 2.5s infinite alternate;"></div>
                                                <span style="color: #7dd3fc; font-size: 1.85rem; font-weight: 900; z-index: 2; margin-bottom: 6px; text-shadow: 0 0 20px rgba(56,189,248,0.8);">إجمالي الجملة</span>
                                                <span id="unified-grand-total" style="color: #ffffff; font-size: 3.1rem; font-weight: 900; z-index: 2; text-shadow: 0 0 30px rgba(56,189,248,1); font-variant-numeric: tabular-nums;">0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden legacy inputs to prevent crashes in old scripts -->
                                    <div style="display: none;">
                                        <input type="text" id="mobile-transfer-no">
                                        <input type="text" id="mobile-note">
                                        <input type="text" id="check-no">
                                        <input type="text" id="check-branch">
                                        <input type="date" id="check-date">
                                        <input type="date" id="check-due-date">
                                        <input type="text" id="check-ref">
                                        <input type="text" id="check-ref-no">
                                        <input type="checkbox" id="check-settle-local">
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- COMPACT BOTTOM ACTION BAR (Fits inside single viewport) -->
        <div class="sticky-footer" style="background: rgba(15,23,42,0.94); border: 1.5px solid rgba(56, 189, 248, 0.45); border-radius: 14px; padding: 6px 14px; margin-top: 4px; box-shadow: 0 -6px 24px rgba(0,0,0,0.45);">
            <div class="footer-actions unified-footer-bar" style="width: 100%; justify-content: center; gap: 10px; display: flex; flex-wrap: wrap; align-items: center;">
                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-exit btn-exit" onclick="if(typeof magaieesGoBack === 'function') { magaieesGoBack(); } else if(document.referrer) { window.history.back(); } else { window.location.href='magaiees_unified_dashboard.php'; }" title="الخروج والرجوع للشاشة السابقة">🚪 خروج</button>
                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-new btn-new" onclick="resetVoucher()" title="تصفير الحقول وبدء سند جديد">✨ جديد</button>
                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-preview btn-print-preview" id="btn-preview-invoice" style="display: inline-flex;" onclick="previewVoucher()" title="معاينة السند المالي قبل الطباعة">👁️ معاينة</button>
                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-print btn-print" id="btn-print-invoice" style="display: inline-flex;" onclick="printVoucher()" title="طباعة السند المالي">🖨️ طباعة</button>
                
                <!-- زر الواتساب - يظهر كشعار واتساب رسمي احترافي -->
                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-whatsapp btn-whatsapp-icon-only" id="btn-whatsapp-master" onclick="openMasterWhatsAppDispatchModal()" title="إرسال كمبيالة وإشعار السند عبر واتساب" style="display: inline-flex; align-items: center; gap: 10px; padding: 0 24px !important; width: auto !important; min-width: 145px !important;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" style="display: block; flex-shrink: 0;">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.373 0 0 5.373 0 12c0 2.115.552 4.103 1.516 5.832L.057 24l6.34-1.662A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" fill="#25D366"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#ffffff"/>
                    </svg>
                    <span style="font-size: 1.2rem; font-weight: 900; color: #ffffff; letter-spacing: 0.3px;">واتساب</span>
                </button>
                <button type="button" id="btn-whatsapp-invoice" style="display: none !important;"></button>

                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-delete btn-delete" onclick="confirmDelete()" title="حذف السند الحالي">🗑️ حذف</button>

                <!-- ═══ زر التحكم في الإرسال التلقائي مع الحفظ ═══ -->
                <div id="auto-send-control-wrapper">
                    <button type="button" class="btn crystal-btn btn-auto-send-toggle inactive" id="btn-toggle-auto-send" onclick="window.toggleAutoSendMode()" title="التحكم في الإرسال التلقائي: انقر للتبديل بين الحفظ مع الإرسال، أو الحفظ فقط بدون إرسال">
                        <span id="auto-send-icon" style="font-size: 1.1rem;">💾</span>
                        <span id="auto-send-label" style="font-weight: 800;">إرسال تلقائي:</span>
                        <span id="auto-send-status-pill" style="padding: 2px 8px; border-radius: 7px; font-size: 0.76rem; font-weight: 900;">معطّل (حفظ فقط)</span>
                    </button>
                    <button type="button" class="btn crystal-btn btn-auto-send-options inactive" id="btn-auto-send-options" onclick="window.toggleAutoSendDropdown(event)" title="خيارات وجهة الإرسال التلقائي (المستلم / الصارف / كلاهما)">
                        ▼
                    </button>
                    
                    <!-- القائمة المنسدلة للخيارات السريعة -->
                    <div id="auto-send-dropdown-menu" style="display: none; position: absolute; bottom: 50px; left: 0; background: #0f172a; border: 1.5px solid #334155; border-radius: 12px; padding: 6px; min-width: 260px; box-shadow: 0 12px 30px rgba(0,0,0,0.7), 0 0 20px rgba(0,0,0,0.5); z-index: 9999; direction: rtl; text-align: right; backdrop-filter: none;">
                        <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 800; padding: 5px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <span>⚙️ وضع الحفظ والإرسال:</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(false)" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #cbd5e1; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>💾</span> <span>حفظ فقط (بدون إرسال)</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'both')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #34d399; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>⚡👥</span> <span>إرسال تلقائي للطرفين (المستلم + الصارف)</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'debtor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #60a5fa; display: flex; align-items: center; gap: 8px; margin-bottom: 3px; transition: background 0.2s;">
                            <span>📥</span> <span>إرسال تلقائي للطرف المستلم فقط</span>
                        </div>
                        <div class="auto-send-opt-item" onclick="window.selectAutoSendOption(true, 'creditor')" style="padding: 7px 10px; border-radius: 7px; cursor: pointer; font-size: 0.82rem; font-weight: 800; color: #f472b6; display: flex; align-items: center; gap: 8px; transition: background 0.2s;">
                            <span>📤</span> <span>إرسال تلقائي للطرف الصارف فقط</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn crystal-btn royal-crystal-btn royal-btn-save btn-save-master" id="btn-save" onclick="submitVoucher()" title="حفظ واعتماد وترحيل السند المالي">
                    <span>✅</span> <span>حفظ واعتماد السند</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Dedicated Premium Voucher Preview Modal -->
    <div id="premium-voucher-preview-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: none; z-index: 99999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="glass-panel" style="width: 95%; max-width: 900px; height: 90vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 20px; overflow: hidden; transform: translateY(30px); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #38bdf8;">
                <h2 style="margin: 0; color: #fff; font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.8rem;">👁️</span> معاينة السند المالي
                </h2>
                <div style="display: flex; gap: 15px;">
                    <button type="button" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 800; font-size: 1.1rem; cursor: pointer; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);" onclick="printVoucher()">
                        🖨️ طباعة فورية
                    </button>
                    <button type="button" onclick="closePremiumPreview()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; width: 45px; height: 45px; border-radius: 10px; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        ❌
                    </button>
                </div>
            </div>

            <!-- Modal Body (The Actual Paper) -->
            <div style="flex: 1; overflow-y: auto; padding: 40px; background: #e2e8f0; display: flex; justify-content: center;">
                <div id="premium-voucher-paper" style="background: #ffffff; width: 100%; max-width: 800px; min-height: 1000px; padding: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); position: relative; border-radius: 4px;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ WhatsApp Enterprise Voucher Dispatch Modal (نافذة إرسال الواتساب الذكي والكمبيالة المعتمدة) ═══ -->
    <div id="wa-voucher-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: none; z-index: 99999; align-items: center; justify-content: center;">
        <div id="wa-voucher-modal-card" class="modal-card glass-container wa-modal-resizable" style="width: 96vw; max-width: 1450px; height: 95vh; max-height: 96vh; border: 2.5px solid #25d366; padding: 12px 18px; border-radius: 14px; background: #0b1120; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.9), 0 0 35px rgba(37,211,102,0.35); display: flex; flex-direction: column; box-sizing: border-box; overflow: hidden;">
            
            <!-- Header (Draggable + Window Controls) -->
            <div id="wa-voucher-modal-header" class="wa-modal-header-draggable" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid rgba(37, 211, 102, 0.4); padding-bottom: 8px; margin-bottom: 8px; flex-shrink: 0;" title="اضغط مع السحب لتحريك النافذة يميناً ويساراً">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.3rem; color: #94a3b8; cursor: grab;" title="اضغط مع السحب لتحريك النافذة">⠿</span>
                    <span style="font-size: 1.5rem;">📱</span>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h3 style="margin: 0; color: #25d366; font-size: 1.30rem; font-weight: 900; line-height: 1.2; text-shadow: 0 0 10px rgba(37,211,102,0.35);">إرسال واعتماد إشعار السند المالي عبر واتساب</h3>
                        <span id="wa-modal-subtitle" style="background: rgba(251, 191, 36, 0.16); border: 1.5px solid rgba(251, 191, 36, 0.6); color: #fef08a; font-size: 0.86rem; font-weight: 800; padding: 3px 12px; border-radius: 6px;">📜 ورقة كمبيالة رسمية مؤطرة (تشمل رصيد دفتر الأستاذ العام)</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- زر التكبير / الاستعادة -->
                    <button type="button" id="btn-wa-modal-maximize" class="wa-btn-header-ctrl" onclick="toggleWAModalMaximize()" title="تكبير / استعادة الحجم" style="width: 32px; height: 32px; font-size: 1.05rem;">🗖</button>
                    <!-- زر الإغلاق -->
                    <button type="button" onclick="closeWAVoucherModal()" class="wa-btn-header-ctrl" style="width: 32px; height: 32px; font-size: 1.05rem; background: rgba(239, 68, 68, 0.25); border-color: #ef4444; color: #ef4444;" title="إغلاق">✕</button>
                </div>
            </div>

            <!-- ═══ شبكة التقسيم الرئيسية ═══ -->
            <div id="wa-voucher-modal-body" style="display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr); gap: 14px; flex: 1; min-height: 0; overflow: hidden;">
                <input type="hidden" id="wa-modal-block-id">
                <input type="hidden" id="wa-modal-target-mode" value="both">
                <input type="hidden" id="wa-modal-format-type" value="promissory_note">
                <input type="hidden" id="wa-both-active-tab" value="debtor">

                <!-- ════════ العمود الأيمن الموسع (لوحة أطراف السند والمعلومات وقنوات الإرسال) ════════ -->
                <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 7px; overflow-y: auto; padding-right: 2px;" class="wa-modal-scroll-area">
                    
                    <!-- 1. شريط وجهة العملية والأطراف واختيار العملة الموحدة -->
                    <div style="background: #0f172a; border: 1.5px solid rgba(37,211,102,0.4); border-radius: 9px; padding: 7px 12px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 3px 10px rgba(0,0,0,0.4);">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: 0.92rem; font-weight: 900; color: #fbbf24; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span style="display: flex; align-items: center; gap: 6px;"><span>🎯</span> <span>وجهة إشعار السند:</span></span>
                                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.45); padding: 2px 8px; border-radius: 6px; border: 1.5px solid #fbbf24;">
                                    <span style="font-size: 1.05rem;">💱</span>
                                    <span style="font-size: 0.86rem; color: #fbbf24; font-weight: 900; white-space: nowrap;">العملة الموحدة:</span>
                                    <select id="wa-modal-currency-select" onchange="window.setWAModalCurrency(this.value)" style="background: #1e293b; border: 1.5px solid #fbbf24; color: #fde047; font-weight: 900; font-size: 0.88rem; padding: 2px 6px; border-radius: 5px; cursor: pointer; outline: none;">
                                        <option value="SDG">جنيه سوداني (SDG)</option>
                                        <option value="USD">دولار أمريكي (USD)</option>
                                        <option value="SAR">ريال سعودي (SAR)</option>
                                        <option value="AED">درهم إماراتي (AED)</option>
                                        <option value="EGP">جنيه مصري (EGP)</option>
                                    </select>
                                </div>
                            </div>
                            <span id="wa-target-status-badge" style="font-size: 0.84rem; font-weight: 900; padding: 3px 12px; border-radius: 6px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(239, 68, 68, 0.3) 100%); color: #fef08a; border: 1.5px solid rgba(251, 191, 36, 0.6);">
                                محدد: إرسال لكليهما معاً (إلى + من) 👥
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: space-between;">
                            <!-- زر الطرف المدين (المستلم) -->
                            <button type="button" id="btn-wa-target-debtor" class="wa-target-btn" onclick="setWATargetMode('debtor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #022c22; border: 2px solid #10b981; color: #a7f3d0; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📥</span> <span id="wa-debtor-role-title">إلى</span>: <b id="wa-debtor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- سهم التبديل الأوسط -->
                            <button type="button" id="btn-wa-swap-parties" onclick="swapWATargetParties()" title="تبديل الإرسال بين الأطراف" style="background: #1e293b; border: 2px solid #fbbf24; color: #fbbf24; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; flex-shrink: 0; transition: all 0.25s; box-shadow: 0 2px 6px rgba(0,0,0,0.5);">
                                ⇄
                            </button>
                            <!-- زر الطرف الدائن (الصارف) -->
                            <button type="button" id="btn-wa-target-creditor" class="wa-target-btn" onclick="setWATargetMode('creditor')" style="flex: 1; height: 42px; padding: 4px 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: #2a080c; border: 2px solid #ef4444; color: #fecaca; font-size: 1.08rem; font-weight: 900; white-space: nowrap;">
                                <span style="font-size: 1.25rem;">📤</span> <span id="wa-creditor-role-title">من</span>: <b id="wa-creditor-acc-name" style="color: #fef08a; font-size: 1.15rem; font-weight: 900;">-</b>
                            </button>
                            <!-- زر كليهما معاً (مصغر وأنيق) -->
                            <button type="button" id="btn-wa-target-both" class="wa-target-btn active" onclick="setWATargetMode('both')" style="flex: 0 0 auto; height: 42px; padding: 4px 14px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; background: linear-gradient(135deg, #047857 0%, #1d4ed8 50%, #b91c1c 100%); border: 2px solid #fbbf24; color: #ffffff; box-shadow: 0 0 12px rgba(251, 191, 36, 0.45); font-size: 1.02rem; font-weight: 900; white-space: nowrap;" title="إرسال لكليهما معاً">
                                <span style="font-size: 1.15rem;">👥</span> <span>كليهما معاً ⚡</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. بطاقات الأطراف في وضع كليهما معاً -->
                    <div id="wa-both-parties-view" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- بطاقة الطرف المدين (المستلم) -->
                        <div id="wa-both-debtor-card" style="background: #061f18; border: 2px solid #10b981; border-right: 8px solid #10b981; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📥</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(16, 185, 129, 0.25); border: 1.5px solid #10b981; color: #86efac; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-debtor-role-label">إلى</span>:
                                        <b id="wa-both-debtor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-debtor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-debtor-balance" style="background: #022c22; border: 2px solid #10b981; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #6ee7b7; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد المستلم:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #064e3b; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #10b981; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #10b981; color: #ffffff; border: 1.5px solid #34d399; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-debtor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('debtor', this.value)" placeholder="أدخل رقم الواتساب للمستلم هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('debtor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #059669 0%, #047857 100%); border: 2px solid #34d399; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(16,185,129,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>

                        <!-- بطاقة الطرف الدائن (الصارف) -->
                        <div id="wa-both-creditor-card" style="background: #200a0f; border: 2px solid #ef4444; border-right: 8px solid #ef4444; border-radius: 12px; padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; transition: all 0.25s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.5); box-sizing: border-box;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                                    <span style="font-size: 1.35rem; flex-shrink: 0;">📤</span>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                                        <span style="background: rgba(239, 68, 68, 0.25); border: 1.5px solid #ef4444; color: #fca5a5; font-size: 1.15rem; font-weight: 900; padding: 3px 10px; border-radius: 6px; white-space: nowrap; min-width: 40px; text-align: center; display: inline-block; box-sizing: border-box;" id="wa-both-creditor-role-label">من</span>:
                                        <b id="wa-both-creditor-name" style="color: #ffffff; font-weight: 900; font-size: 1.32rem; letter-spacing: 0.3px; line-height: 1.3; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">-</b>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <span id="wa-creditor-status-tag" style="background: #1e293b; border: 1.5px solid #475569; color: #cbd5e1; font-size: 0.98rem; font-weight: 800; padding: 4px 12px; border-radius: 6px; white-space: nowrap; min-width: 110px; text-align: center; display: inline-block; box-sizing: border-box;">⏳ بانتظار الإرسال</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; justify-content: space-between;">
                                <div id="wa-both-creditor-balance" style="background: #2a080c; border: 2px solid #ef4444; padding: 4px 10px; border-radius: 8px; white-space: nowrap; flex: 0 0 auto; width: auto; height: 44px; box-sizing: border-box; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.45);">
                                    <span style="color: #fca5a5; font-weight: 900; font-size: 1.05rem; white-space: nowrap;">📊 رصيد الصرف:</span>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <b style="color: #ffffff; background: #7f1d1d; padding: 3px 8px; border-radius: 6px; border: 1.5px solid #ef4444; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px;">-</b>
                                        <select class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                            <option value="SDG">SDG</option>
                                            <option value="USD">USD</option>
                                            <option value="SAR">SAR</option>
                                            <option value="AED">AED</option>
                                            <option value="EGP">EGP</option>
                                        </select>
                                        <span style="background: #ef4444; color: #ffffff; border: 1.5px solid #f87171; padding: 3px 8px; border-radius: 6px; font-size: 1.02rem; font-weight: 900; min-width: 32px; text-align: center;">-</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                    <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                        <div class="wa-ripple-container">
                                            <span class="wa-wave-pulse wave-1"></span>
                                            <span class="wa-wave-pulse wave-2"></span>
                                            <span class="wa-wave-pulse wave-3"></span>
                                        </div>
                                        <div class="wa-luxury-icon-circle">
                                            <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="tel" id="wa-both-creditor-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone('creditor', this.value)" placeholder="أدخل رقم الواتساب لحساب الصرف هنا..." style="flex: 1; min-width: 0; width: 100%; height: 44px; background: #1f070b; border: 2px solid #ef4444; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; border-radius: 8px; box-sizing: border-box; transition: all 0.25s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);">
                                </div>
                                <button type="button" class="btn crystal-btn" onclick="sendSinglePartyDirectly('creditor')" style="height: 44px; min-width: 105px; flex-shrink: 0; background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); border: 2px solid #f87171; color: #ffffff; font-weight: 900; font-size: 1.10rem; padding: 4px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; box-shadow: 0 3px 10px rgba(239,68,68,0.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-sizing: border-box;" title="إرسال إشعار هذا الطرف فقط">
                                    <span>⚡ إرسال</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. بطاقة الطرف الفردي في وضع التحديد المفرد -->
                    <div id="wa-single-party-view" style="display: none; flex-direction: column; gap: 8px; background: #0f172a; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 10px 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.4);">
                        <div>
                            <label id="wa-label-recipient-name" style="color: #34d399; font-size: 1.15rem; font-weight: 900; display: block; margin-bottom: 5px;">👤 الطرف المحدد:</label>
                            <input type="text" id="wa-modal-recipient-name" class="form-input" readonly style="background: #1e293b; color: #ffffff; font-weight: 900; font-size: 1.30rem; padding: 3px 12px; height: 44px; border-radius: 8px; border: 1.5px solid #475569;">
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div id="wa-modal-balance-container" style="flex: 0 0 auto; width: auto; display: inline-flex; align-items: center; justify-content: space-between; gap: 8px; background: #082f49; border: 2px solid #38bdf8; border-radius: 8px; padding: 4px 12px; height: 44px; box-sizing: border-box;">
                                <span style="color: #bae6fd; font-size: 1.05rem; font-weight: 900; white-space: nowrap;">📊 رصيد الأستاذ:</span>
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span id="wa-modal-live-balance" style="color: #ffffff; background: #0369a1; padding: 3px 8px; border-radius: 6px; font-family: 'Consolas', monospace; font-size: 1.25rem; font-weight: 900;">0.00</span>
                                    <select id="wa-modal-single-currency-chip" class="wa-currency-chip" onchange="window.setWAModalCurrency(this.value)" title="تغيير عملة السند والأرصدة الموحدة">
                                        <option value="SDG">SDG</option>
                                        <option value="USD">USD</option>
                                        <option value="SAR">SAR</option>
                                        <option value="AED">AED</option>
                                        <option value="EGP">EGP</option>
                                    </select>
                                    <span id="wa-modal-live-balance-tag" style="font-size: 1.02rem; font-weight: 900; padding: 3px 8px; border-radius: 6px; background: #38bdf8; color: #082f49;">-</span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                                <div class="wa-luxury-badge" title="رقم الواتساب المعتمد للإرسال">
                                    <div class="wa-ripple-container">
                                        <span class="wa-wave-pulse wave-1"></span>
                                        <span class="wa-wave-pulse wave-2"></span>
                                        <span class="wa-wave-pulse wave-3"></span>
                                    </div>
                                    <div class="wa-luxury-icon-circle">
                                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </div>
                                </div>
                                <input type="tel" id="wa-modal-phone" class="form-input" oninput="if(typeof cachePartyPhone==='function') cachePartyPhone(document.getElementById('wa-modal-target-mode')?.value||'debtor', this.value)" placeholder="أدخل رقم الواتساب هنا..." style="background: #021a14; border: 2px solid #10b981; color: #ffffff; font-weight: 900; direction: ltr; text-align: left; font-size: 1.28rem; padding: 2px 14px; height: 44px; border-radius: 8px; flex: 1; min-width: 0; width: 100%; box-sizing: border-box;">
                            </div>
                        </div>
                    </div>

                    <!-- 4. زر الإرسال الرئيسي الشامل لكلا الطرفين (المستلم + الصارف) -->
                    <button type="button" id="btn-wa-send-both-action" onclick="executeWASend('gateway')" class="btn crystal-btn" style="width: 100%; height: 48px; padding: 4px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%); border: 2px solid #34d399; border-radius: 10px; color: #ffffff; font-weight: 900; font-size: 1.22rem; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.45); cursor: pointer; transition: all 0.2s ease;" title="إرسال كمبيالة السند مباشرة عبر خادم الواتساب المربوط">
                        <span style="font-size: 1.45rem;">🚀</span>
                        <span id="btn-wa-send-both-action-text">إرسال إلى كل الأطراف عبر الواتساب ⚡</span>
                    </button>

                    <!-- بانر التسليم الذكي للمرحلة الثانية (Handover Panel) -->
                    <div id="wa-modal-handover-panel" style="display: none; background: #0f172a; border: 2px solid #10b981; border-radius: 9px; padding: 6px 12px; box-shadow: 0 0 16px rgba(16, 185, 129, 0.4); animation: fadeIn 0.2s ease;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                            <span style="color: #86efac; font-weight: 900; font-size: 0.90rem; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span> <span id="wa-handover-status-text">تم إرسال إشعار المستلم بنجاح!</span>
                            </span>
                            <span id="wa-handover-subtext" style="color: #cbd5e1; font-weight: 700; font-size: 0.82rem; width: 100%;">اضغط الزر لإرسال إشعار الصارف فوراً:</span>
                            <span id="wa-handover-countdown-badge" style="display: none;"><b id="wa-handover-timer"></b></span>
                            <div style="display: flex; gap: 8px; align-items: center; width: 100%; margin-top: 3px;">
                                <button type="button" id="btn-wa-handover-next" onclick="triggerHandoverNextParty()" style="flex: 1; height: 36px; padding: 4px 12px; background: linear-gradient(135deg, #10b981, #059669); border: 1.5px solid #34d399; border-radius: 7px; color: #fff; font-size: 0.88rem; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                                    <span>📤</span> <span id="btn-wa-handover-label">إرسال إشعار الطرف الصارف الآن ➔</span>
                                </button>
                                <button type="button" onclick="closeWAVoucherModal()" style="height: 36px; padding: 4px 12px; background: #1e293b; border: 1px solid #475569; border-radius: 7px; color: #cbd5e1; font-size: 0.82rem; font-weight: 800; cursor: pointer;">
                                    إغلاق
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. شريط أدوات تصوير ونسخ صورة السند الفوري (Image & Capture Tools) -->
                    <div style="background: #0f172a; border: 1.5px dashed #fbbf24; border-radius: 9px; padding: 5px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <span style="color: #fbbf24; font-size: 0.90rem; font-weight: 900; display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 1.15rem;">📸</span> <span>تصوير وحفظ السند:</span>
                        </span>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" onclick="downloadWAVoucherImage()" class="btn crystal-btn" style="height: 34px; background: #78350f; border: 1.5px solid #fbbf24; color: #fef08a; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="تنزيل كارت السند المصور عالي الدقة كصورة PNG">
                                <span>💾</span> حفظ كارت السند (PNG)
                            </button>
                            <button type="button" onclick="copyWAVoucherImageToClipboard()" class="btn crystal-btn" style="height: 34px; background: #075985; border: 1.5px solid #38bdf8; color: #e0f2fe; font-size: 0.86rem; font-weight: 900; padding: 4px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;" title="نسخ صورة السند للحافظة للصقها مباشرة في واتساب">
                                <span>📋</span> نسخ الصورة للحافظة
                            </button>
                        </div>
                    </div>

                    <!-- 6. قناة الإرسال الموحدة عبر خادم الواتساب المربوط -->
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" id="btn-wa-send-gateway" class="btn crystal-btn" onclick="executeWASend('gateway')" style="width: 100%; height: 48px; background: linear-gradient(135deg, #15803d 0%, #047857 100%); border: 2px solid #4ade80; color: #ffffff; font-weight: 900; font-size: 1.18rem; padding: 4px 16px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 16px rgba(21,128,61,0.45); cursor: pointer;" title="إرسال كمبيالة السند كملف PDF معتمد وموثق عبر الواتساب">
                            <span style="font-size: 1.45rem;">📄⚡</span> <span id="btn-wa-send-gateway-text">إرسال كمبيالة السند المعتمدة (ملف PDF) عبر الواتساب</span>
                        </button>
                    </div>

                    <!-- 7. شريط الإعدادات والإلغاء السفلي -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1.5px solid rgba(255,255,255,0.1); padding-top: 6px;">
                        <button type="button" class="btn crystal-btn btn-ghost" onclick="closeWAVoucherModal()" style="font-size: 0.85rem; font-weight: 800; padding: 4px 14px;">إلغاء</button>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #86efac; font-size: 0.84rem; font-weight: 900; margin: 0; background: rgba(37,211,102,0.1); border: 1.5px dashed rgba(37,211,102,0.4); padding: 3px 10px; border-radius: 6px;">
                            <input type="checkbox" id="wa-modal-auto-dispatch-sync" onchange="toggleWAAutoDispatch(this.checked)" style="width: 15px; height: 15px; cursor: pointer; accent-color: #25d366;">
                            <span>⚡ إرسال تلقائي دائم فور حفظ السند</span>
                        </label>
                    </div>

                    <div id="wa-modal-status-alert" style="display: none; padding: 5px 8px; border-radius: 6px; font-size: 0.84rem; font-weight: 800;"></div>
                </div>

                <!-- ════════ العمود الأيسر (لوحة كمبيالة السند المعتمدة الكاملة - Full Promissory Note Document) ════════ -->
                <div style="display: flex; flex-direction: column; background: #070d18; border: 1.5px solid rgba(251, 191, 36, 0.4); border-radius: 10px; padding: 8px 10px; overflow: hidden; box-shadow: inset 0 0 30px rgba(0,0,0,0.65);">
                    
                    <!-- ترويسة لوحة الكمبيالة والتبويبات المدمجة -->
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid rgba(255,255,255,0.12); padding-bottom: 5px; margin-bottom: 5px; flex-shrink: 0; flex-wrap: wrap; gap: 5px;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="color: #fbbf24; font-size: 0.86rem; font-weight: 900; display: flex; align-items: center; gap: 5px;">
                                <span>📜</span> <span>المعاينة المعتمدة:</span>
                            </span>
                        </div>
                        
                        <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                            <!-- تبويب المستلم والصارف في وضع كليهما معاً -->
                            <div id="wa-both-tabs-toolbar" style="display: flex; gap: 4px;">
                                <button type="button" id="btn-wa-both-tab-debtor" onclick="switchWABothPreviewTab('debtor')" style="background: rgba(16, 185, 129, 0.35); border: 1.5px solid #10b981; color: #34d399; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📥 المستلم
                                </button>
                                <button type="button" id="btn-wa-both-tab-creditor" onclick="switchWABothPreviewTab('creditor')" style="background: rgba(255, 255, 255, 0.08); border: 1.5px solid rgba(255, 255, 255, 0.25); color: #cbd5e1; font-size: 0.78rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;">
                                    📤 الصارف
                                </button>
                            </div>
                            <!-- زر تحميل PDF الفوري -->
                            <button type="button" onclick="downloadPromissoryPDF()" class="btn crystal-btn" style="background: linear-gradient(135deg, #b45309, #78350f); border: 1px solid #fbbf24; color: #fef08a; font-size: 0.76rem; font-weight: 900; padding: 2px 8px; border-radius: 5px; cursor: pointer;" title="تنزيل كمبيالة السند كملف PDF معتمد">
                                📥 PDF
                            </button>
                            <!-- زر الطباعة المباشرة -->
                            <button type="button" onclick="printPromissoryNote()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="طباعة كمبيالة السند">
                                🖨️ طباعة
                            </button>
                            <!-- زر نسخ النص بالكامل -->
                            <button type="button" onclick="copyFullTextMessage()" class="btn crystal-btn" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.25); color: #f1f5f9; font-size: 0.76rem; font-weight: 800; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="نسخ نص الكمبيالة بالكامل إلى الحافظة">
                                📋 نسخ
                            </button>
                            <!-- زر التبديل بين كمبيالة PDF والنص -->
                            <button type="button" id="btn-wa-toggle-preview-mode" onclick="toggleWAPreviewMode()" class="btn crystal-btn" style="background: rgba(56, 189, 248, 0.15); border: 1.5px solid rgba(56, 189, 248, 0.5); color: #38bdf8; font-size: 0.76rem; font-weight: 900; padding: 2px 7px; border-radius: 5px; cursor: pointer;" title="تبديل العرض بين كمبيالة PDF والنص">
                                📄 PDF / 📝 نص
                            </button>
                        </div>
                    </div>

                    <!-- مساحة عرض كمبيالة السند الكاملة (Certified PDF Document & Monospace Area) -->
                    <div style="flex: 1; min-height: 0; display: flex; flex-direction: column; position: relative;">
                        <!-- حاوية المعاينة الحية لكمبيالة السند الرسمية المعتمدة (PDF Document Live Preview) -->
                        <div id="wa-promissory-preview-wrapper" style="flex: 1; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; background: #0c1424; border-radius: 8px; border: 1.5px solid rgba(251, 191, 36, 0.35); padding: 8px; box-sizing: border-box;">
                            <div id="wa-promissory-note-container" style="background: #ffffff; color: #0f172a; border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); font-family: 'Tajawal', sans-serif; direction: rtl; width: 100%;">
                            </div>
                        </div>

                        <!-- صندوق نص كمبيالة كليهما معاً (Hidden by default, available when user toggles text mode) -->
                        <div id="wa-preview-both-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-both-preview-message" class="form-input" oninput="saveWABothPreviewEdit(this.value)" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- صندوق نص كمبيالة الطرف الفردي -->
                        <div id="wa-preview-single-box" style="display: none; flex: 1; min-height: 0; flex-direction: column;">
                            <textarea id="wa-modal-message" class="form-input" style="width: 100%; height: 100%; flex: 1; background: #050b14; border: 1.5px solid rgba(251, 191, 36, 0.35); color: #f8fafc; font-size: 0.84rem; line-height: 1.55; font-weight: 600; border-radius: 7px; resize: none; padding: 8px 10px; font-family: 'Consolas', 'Courier New', monospace; box-sizing: border-box; box-shadow: inset 0 0 25px rgba(0,0,0,0.85); white-space: pre-wrap; word-break: break-word;"></textarea>
                        </div>

                        <!-- حاوية كارت السند المصور اللحظي (Graphic Preview Container) -->
                        <div id="wa-modal-image-preview-container" style="display: none; flex: 1; min-height: 0; align-items: center; justify-content: center; background: rgba(0,0,0,0.65); border: 1.5px solid rgba(251,191,36,0.35); border-radius: 7px; overflow: hidden; padding: 6px; position: relative;">
                            <img id="wa-modal-image-preview" src="" alt="كارت السند المصور" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.85);">
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.74rem; font-weight: 800; color: #94a3b8;">
                        <span>💡 ورقة كمبيالة معتمدة ومقيدة</span>
                        <span style="color: #fbbf24;">⚡ قيد آلي رقمي</span>
                    </div>
                </div>

            </div>

            <!-- Visual corner resize indicator -->
            <div class="wa-resize-handle-corner" title="اسحب لتغيير الحجم">◢</div>
        </div>
    </div>

    <?php if (file_exists(__DIR__ . '/includes/inbound-bankak-drawer.php')) include_once __DIR__ . '/includes/inbound-bankak-drawer.php'; ?>
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/promissory-flight-animation.js?v=20261029_fast"></script>
    <script defer src="js/app.js?v=<?php echo file_exists('js/app.js') ? filemtime('js/app.js') : '20261117'; ?>"></script>
    <script defer src="js/html2pdf.bundle.min.js"></script>
    <script defer src="js/whatsapp-integration.js?v=<?php echo file_exists('js/whatsapp-integration.js') ? filemtime('js/whatsapp-integration.js') : '20261117'; ?>"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', () => {
          setTimeout(() => {
              const fromSearch = document.getElementById('from-account-search');
              if (fromSearch) {
                  fromSearch.focus();
                  fromSearch.select();
              }
          }, 300);
      });
    </script>
    <script>
        window.SERVER_SINGLE_PAYMENTS = <?php echo $SERVER_SINGLE_PAYMENTS_json; ?>;
        window.DB_ALL_TRANSFER_REFS = <?php echo $SERVER_ALL_TRANSFERS_json; ?>;
    </script>
    <script defer src="js/master-banks-helper.js?v=<?php echo file_exists('js/master-banks-helper.js') ? filemtime('js/master-banks-helper.js') : '1'; ?>"></script>
    <script defer src="js/voucher-qr-barcode-suite.js?v=20261103"></script>
    <script defer src="js/single-cash-payment.js?v=<?php echo file_exists('js/single-cash-payment.js') ? filemtime('js/single-cash-payment.js') : '20261117'; ?>"></script>
<script src="js/screens/single-cash-payment-j1.js?v=<?= @filemtime(__DIR__ . '/js/screens/single-cash-payment-j1.js') ?: '2027' ?>"></script>

    <script>
    let hasWarnedAboutEdit = false;

    // تكييف الرقم التسلسلي (رقم السند) أوتوماتيكياً مع المساحة المخصصة له
    window.autoFitVoucherSerial = function() {
        const vEl = document.getElementById('voucher-number');
        const badgeEl = document.getElementById('voucher-nav-badge');
        if (!vEl) return;
        const txt = (vEl.innerText || vEl.textContent || '').trim();
        const len = txt.length;
        let fontSize = '1.45rem';
        let letterSpacing = '1px';
        let minW = '105px';

        if (len > 16) {
            fontSize = '0.88rem';
            letterSpacing = '0px';
            minW = '165px';
        } else if (len > 12) {
            fontSize = '1.05rem';
            letterSpacing = '0.5px';
            minW = '145px';
        } else if (len > 9) {
            fontSize = '1.25rem';
            letterSpacing = '0.8px';
            minW = '125px';
        } else {
            fontSize = '1.45rem';
            letterSpacing = '1.2px';
            minW = '105px';
        }

        vEl.style.setProperty('font-size', fontSize, 'important');
        vEl.style.setProperty('letter-spacing', letterSpacing, 'important');
        if (badgeEl) {
            badgeEl.style.setProperty('min-width', minW, 'important');
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.autoFitVoucherSerial();
        const vEl = document.getElementById('voucher-number');
        if (vEl && typeof MutationObserver !== 'undefined') {
            const obs = new MutationObserver(() => window.autoFitVoucherSerial());
            obs.observe(vEl, { childList: true, characterData: true, subtree: true });
        }

        // Intercept save by wrapping the original function unconditionally
        if (typeof window.submitVoucher === 'function') {
            const originalSubmit = window.submitVoucher;
            window.submitVoucher = function(...args) {
                // Check financial limits for voucher amount (Fixed ID: amount-local)
                const rawAmtStr = document.getElementById('amount-local')?.value || '0';
                const voucherAmt = parseFloat(String(rawAmtStr).replace(/,/g, '')) || 0;
                if (window.MagaieesPermissions && typeof window.MagaieesPermissions.checkFinancialLimits === 'function') {
                    const check = window.MagaieesPermissions.checkFinancialLimits('voucher_amount', voucherAmt);
                    if (!check.allowed) {
                        window.MagaieesPermissions.requestManagerApproval({
                            title: 'تجاوز سقف الصرف المالي',
                            description: check.message + ' يرجى إدخال رمز المشرف لاعتماد صرف هذا المبلغ الاستثنائي.',
                            requiredLevel: 6,
                            onSuccess: () => {
                                hasWarnedAboutEdit = true;
                                return originalSubmit.apply(this, args);
                            }
                        });
                        return;
                    }
                }

                let editIndex = -1;
                try {
                    if (typeof currentViewedIndex !== 'undefined') {
                        editIndex = currentViewedIndex;
                    }
                } catch(e) {}
                
                const isEditing = (editIndex !== -1) || (new URLSearchParams(window.location.search).get('id') != null);
                
                if (isEditing && !hasWarnedAboutEdit) {
                    showEditWarningModal();
                } else {
                    hasWarnedAboutEdit = false;
                    return originalSubmit.apply(this, args);
                }
            };
        }
    });

    function showEditWarningModal(e) {
        if(e && e.target && typeof e.target.blur === 'function') {
            e.target.blur();
        }
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.firstElementChild.style.transform = 'scale(1)';
            }, 10);
        }
    }

    function confirmEditOldVoucher() {
        hasWarnedAboutEdit = true;
        const modal = document.getElementById('edit-warning-modal');
        if(modal) {
            modal.style.opacity = '0';
            modal.firstElementChild.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
                // Trigger the save action now that warning is accepted
                if (typeof window.submitVoucher === 'function') {
                    window.submitVoucher();
                }
            }, 300);
        }
    }

    function cancelEditOldVoucher() {
        if(document.referrer) {
            window.history.back();
        } else {
            window.location.href = 'magaiees_unified_dashboard.php';
        }
    }
    
    function autoCategorize() {
        const desc = document.getElementById('to-statement').value.trim();
        if (!desc) {
            if (typeof showToast === 'function') showToast('الرجاء كتابة البيان أولاً لكي يتوقع الذكاء الاصطناعي الحساب.', 'warning');
            else alert('الرجاء كتابة البيان أولاً لكي يتوقع الذكاء الاصطناعي الحساب.');
            return;
        }
        
        const btn = document.getElementById('btn-auto-categorize');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span style="font-size: 1.2rem;">⏳</span> <span class="btn-text">جاري التفكير...</span>';
        btn.disabled = true;

        // Gather all accounts from live ACCOUNTS list or fallback to localStorage
        if (typeof window.buildAccountsArray === 'function' && (!ACCOUNTS || ACCOUNTS.length === 0)) {
            window.buildAccountsArray();
        }
        let allAccounts = (typeof ACCOUNTS !== 'undefined' && Array.isArray(ACCOUNTS) && ACCOUNTS.length > 0) ? ACCOUNTS : [];
        if (allAccounts.length === 0) {
            try {
                const getTK = window.getTenantKey || ((k) => k);
                const exps = JSON.parse(localStorage.getItem(getTK('erp_accounts_v1')) || localStorage.getItem('erp_accounts_v1') || '[]');
                const sups = JSON.parse(localStorage.getItem(getTK('erp_suppliers_v1')) || localStorage.getItem('erp_suppliers_v1') || '[]');
                exps.filter(a => !a.deleted).forEach(a => allAccounts.push({ id: `exp-${a.id}`, name: a.name }));
                sups.filter(s => !s.deleted).forEach(s => allAccounts.push({ id: `supp-${s.id}`, name: s.name }));
            } catch(e){}
        }

        // Select only relevant fields to save tokens
        let accountListStr = allAccounts.filter(a => a && a.name && !a.deleted).map(a => a.name).join(' | ');

        let promptMsg = `أنت موجه محاسبي آلي. لقد كتب المستخدم البيان التالي لسند صرف نقدية: "${desc}". 
المطلوب: اختار الحساب الأنسب من ضمن القائمة التالية: [${accountListStr}]. 
يجب أن يكون ردك عبارة عن اسم الحساب فقط بدون أي إضافات أو شرح. إذا لم تجد حساباً مطابقاً بدقة اختر أقرب حساب (مثلاً مصروفات عمومية أو مصروفات ضيافة).`;

        fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: promptMsg, context: '' })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (data.reply) {
                let suggestedName = data.reply.trim();
                let match = allAccounts.find(a => a.name === suggestedName) || allAccounts.find(a => a.name && (a.name.includes(suggestedName) || suggestedName.includes(a.name)));
                if (match) {
                    if (typeof window.selectAccount === 'function') {
                        window.selectAccount('to', match.id, match.name);
                    } else {
                        document.getElementById('to-account').value = match.id;
                        document.getElementById('to-account-search').value = match.name;
                    }
                    if (typeof showToast === 'function') showToast(`🪄 تم اختيار الحساب: ${match.name}`, 'success');
                } else {
                    if (typeof showToast === 'function') showToast('اقترح الذكاء الاصطناعي: ' + suggestedName + ' ولكن لم يتم العثور عليه في القائمة بدقة.', 'warning');
                }
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            if (typeof showToast === 'function') showToast('حدث خطأ في الاتصال بالمساعد الذكي.', 'error');
        });
    }
    </script>
    
    <!-- Custom Context Menu for Numbers -->
    <div id="num-props-menu">
        <div class="props-header">⚙️ خصائص الأرقام</div>
        <div class="props-item">
            <span>حجم الخط (تعديل)</span>
            <div style="display: flex; gap: 5px;">
                <button class="prop-btn" onclick="applyNumProp('font-inc'); event.stopPropagation();">➕</button>
                <button class="prop-btn" onclick="applyNumProp('font-dec'); event.stopPropagation();">➖</button>
            </div>
        </div>
        <div class="props-item" onclick="applyNumProp('toggle-comma')">
            <span>الفاصلة الألفية (,)</span>
            <span id="prop-comma-status" style="color:#10b981; font-weight:bold;">مفعل</span>
        </div>
        <div class="props-item">
            <span>الخانات العشرية</span>
            <div style="display: flex; gap: 5px; align-items: center;">
                <span id="prop-dec-val" style="color:#fbbf24; font-weight:bold;">0</span>
                <button class="prop-btn" onclick="applyNumProp('dec-inc'); event.stopPropagation();">➕</button>
                <button class="prop-btn" onclick="applyNumProp('dec-dec'); event.stopPropagation();">➖</button>
            </div>
        </div>
        <div class="props-item" onclick="applyNumProp('toggle-font')">
            <span>نوع الخط</span>
            <span id="prop-font-status" style="color:#fbbf24; font-size:0.8rem; font-weight:bold;">Inter</span>
        </div>
        <div class="props-item" style="justify-content: center; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
            <button id="save-props-btn" onclick="saveNumberProperties(); event.stopPropagation();" style="background: #10b981; color: #fff; border: none; padding: 6px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.2s;">💾 حفظ الخصائص</button>
        </div>
    </div>
    <!-- Financial OCR Modal & Script -->
    <?php include 'includes/financial-ocr-modal.php'; ?>
    <script defer src="js/magaies-offline-sync.js?v=20261014"></script>
    <script defer src="js/financial-ocr.js?v=<?php echo file_exists('js/financial-ocr.js') ? filemtime('js/financial-ocr.js') : 1; ?>"></script>
    <script defer src="js/elsajana-universal-ui-2027.js?v=<?php echo file_exists('js/elsajana-universal-ui-2027.js') ? filemtime('js/elsajana-universal-ui-2027.js') : 1; ?>"></script>

    
</body>
</html>
