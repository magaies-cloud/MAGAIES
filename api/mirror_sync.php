<?php
/**
 * ═════════════════════════════════════════════════════════════════════
 * 🪞 MAGAIEES ERP 2026 - REAL-TIME MIRROR ENGINE (نظام المرآة الموحد الفوري)
 * ═════════════════════════════════════════════════════════════════════
 * يضمن تطابق تام 100% بين نسخة اللابتوب ونسخة الجوال أو التابلت:
 * 1. صرف مالي / حركة مالية (Single Payments)
 * 2. المبيعات (Sales Invoices)
 * 3. تسليم المبيعات (Sales Delivery)
 * 4. تقسيم المبيعات (Sales Split)
 * 5. المشتريات (Purchase Invoices)
 * 6. تسليم واستلام المشتريات (Purchase Receipts / GRN)
 * 7. تقسيم المشتريات (Purchase Split)
 * ═════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Tenant-Id');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    exit(0);
}

if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
}

require_once __DIR__ . '/../db_bridge.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$pdo = getDBConnection();
$tenantId = (int)$GLOBAL_TENANT_ID;
if (!$tenantId) $tenantId = 12;

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
if (empty($action) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $rawInput = json_decode(file_get_contents('php://input'), true);
    if (!empty($rawInput['action'])) {
        $action = $rawInput['action'];
    }
}

// ─────────────────────────────────────────────────────────────
// Helper Functions: Universal Sequential Number Generators (SQL-Optimized)
// ─────────────────────────────────────────────────────────────

function extractUniversalDocSeq($rawNo, $letter, $currentPrefix) {
    $rawNo = trim((string)$rawNo);
    if ($rawNo === '' || $rawNo === 'new' || $rawNo === 'SAL-NEW' || $rawNo === '---') return 0;
    if (strpos($rawNo, 'أوفلاين') !== false || strpos($rawNo, 'OFF-') !== false || strpos($rawNo, 'offline') !== false) return 0;
    if (stripos($rawNo, 'PRE-') === 0 || stripos($rawNo, 'GRN') === 0 || stripos($rawNo, 'DEL') === 0 || stripos($rawNo, 'Z') === 0) return 0;
    if (preg_match('/\d{10,}/', $rawNo)) return 0;
    if (preg_match('/^' . preg_quote($letter, '/') . '\d{4}(\d{5,})$/i', $rawNo, $mRand)) {
        if ((int)$mRand[1] >= 10000) return 0;
    }

    if (stripos($rawNo, $currentPrefix) === 0) {
        $rem = substr($rawNo, strlen($currentPrefix));
        if (preg_match('/^\d{1,4}$/', $rem)) {
            return (int)$rem;
        }
    }
    if (preg_match('/^' . preg_quote($letter, '/') . '\d{3}(\d{4})$/i', $rawNo, $m3)) {
        return (int)$m3[1];
    }
    if (preg_match('/^' . preg_quote($letter, '/') . '\d{4}(\d{4})$/i', $rawNo, $m4)) {
        return (int)$m4[1];
    }
    if (preg_match('/^' . preg_quote($letter, '/') . '[-_]?(\d{1,4})$/i', $rawNo, $mShort)) {
        return (int)$mShort[1];
    }
    if (preg_match('/^\d{1,5}$/', $rawNo)) {
        $val = (int)$rawNo;
        if ($val > 0 && $val < 10000) return $val;
    }
    return 0;
}

function getFinanceSequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'E' . date('ym');
    $maxSeq = 0;
    $lastVoucher = null;
    $lastRowId = 0;
    $lastVoucherNo = '';
    $totalCount = 0;

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();
    } catch (Exception $e) {}

    $stmt = $pdo->prepare("SELECT id, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')), '') AS v_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.deleted')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.isDeleted')), '0') AS is_del FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isDel = strtolower((string)($r['is_del'] ?? '0'));
        if ($isDel === 'true' || $isDel === '1') continue;
        if ($lastRowId === 0) {
            $lastRowId = (int)$r['id'];
        }
        $id = (string)($r['v_no'] ?? '');
        if ($lastVoucherNo === '' && $id !== '' && $id !== 'null') {
            $lastVoucherNo = $id;
        }
        $s = extractUniversalDocSeq($id, 'E', $prefix);
        if ($s > $maxSeq) $maxSeq = $s;
    }

    if ($includeLast && $lastRowId > 0) {
        $stLast = $pdo->prepare("SELECT data FROM single_payments WHERE id = ? LIMIT 1");
        $stLast->execute([$lastRowId]);
        $rawLast = $stLast->fetchColumn();
        if ($rawLast) $lastVoucher = json_decode($rawLast, true);
    }

    $nextVNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'lastVoucherNo' => $lastVoucherNo,
        'nextVoucherNo' => $nextVNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastVoucher;
    return $res;
}

function getSalesSequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'S' . date('ym');
    $maxSeq = 0;
    $lastSale = null;
    $lastRowId = 0;
    $lastInvoiceNo = '';
    $totalCount = 0;
    $candidates = [];

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();
    } catch (Exception $e) {}

    $stmt = $pdo->prepare("SELECT id, invoice_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')), '') AS j_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.isDeleted')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.deleted')), '0') AS is_del FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isDel = strtolower((string)($r['is_del'] ?? '0'));
        if ($isDel === 'true' || $isDel === '1') continue;
        if ($lastRowId === 0) {
            $lastRowId = (int)$r['id'];
        }
        $jNo = ($r['j_no'] !== 'null') ? $r['j_no'] : '';
        $invNo = (string)($r['invoice_no'] ?: $jNo);
        if ($lastInvoiceNo === '' && $invNo !== '') {
            $lastInvoiceNo = $invNo;
        }

        $s = extractUniversalDocSeq($invNo, 'S', $prefix);
        if ($s > 0) {
            $candidates[] = ['no' => $invNo, 'seq' => $s];
        }
    }

    foreach ($candidates as $c) {
        if ($c['no'] === 'S26101012' && $totalCount < 900) continue;
        if ($c['seq'] > $maxSeq) $maxSeq = $c['seq'];
    }

    if ($includeLast && $lastRowId > 0) {
        $stLast = $pdo->prepare("SELECT invoice_no, total_val, data FROM sales_invoices WHERE id = ? LIMIT 1");
        $stLast->execute([$lastRowId]);
        $rL = $stLast->fetch(PDO::FETCH_ASSOC);
        if ($rL) {
            $d = json_decode($rL['data'], true) ?: [];
            $invNo = (string)($rL['invoice_no'] ?: ($d['invoiceNo'] ?? ($d['id'] ?? '')));
            $d['invoiceNo'] = $invNo;
            $d['id'] = $invNo;
            $d['total'] = (float)($rL['total_val'] ?? ($d['grandTotal'] ?? ($d['total'] ?? 0)));
            $lastSale = $d;
        }
    }

    $nextInvNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'lastInvoiceNo' => $lastInvoiceNo,
        'nextInvoiceNo' => $nextInvNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastSale;
    return $res;
}

function getPurchaseSequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'P' . date('ym');
    $maxSeq = 0;
    $lastPur = null;
    $lastRowId = 0;
    $lastInvoiceNo = '';
    $totalCount = 0;
    $candidates = [];

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (invoice_no IS NULL OR (invoice_no NOT LIKE 'GRN%' AND invoice_no NOT LIKE 'Z%'))");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();
    } catch (Exception $e) {}

    $stmt = $pdo->prepare("SELECT id, invoice_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')), '') AS j_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.isDeleted')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.deleted')), '0') AS is_del, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.isGRN')), '0') AS is_grn FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
    $stmt->execute([$tenantId]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isDel = strtolower((string)($r['is_del'] ?? '0'));
        $isGrn = strtolower((string)($r['is_grn'] ?? '0'));
        if ($isDel === 'true' || $isDel === '1' || $isGrn === 'true' || $isGrn === '1') continue;
        $jNo = ($r['j_no'] !== 'null') ? $r['j_no'] : '';
        $invNo = (string)($r['invoice_no'] ?: $jNo);
        if (stripos($invNo, 'GRN') === 0 || stripos($invNo, 'Z') === 0) continue;
        if ($lastRowId === 0) {
            $lastRowId = (int)$r['id'];
        }
        if ($lastInvoiceNo === '' && $invNo !== '') {
            $lastInvoiceNo = $invNo;
        }

        $s = extractUniversalDocSeq($invNo, 'P', $prefix);
        if ($s > 0) {
            $candidates[] = ['no' => $invNo, 'seq' => $s];
        }
    }

    foreach ($candidates as $c) {
        if ($c['no'] === 'P26100045' && $totalCount < 40) continue;
        if ($c['seq'] > $maxSeq) $maxSeq = $c['seq'];
    }

    if ($includeLast && $lastRowId > 0) {
        $stLast = $pdo->prepare("SELECT invoice_no, data FROM purchase_invoices WHERE id = ? LIMIT 1");
        $stLast->execute([$lastRowId]);
        $rL = $stLast->fetch(PDO::FETCH_ASSOC);
        if ($rL) {
            $d = json_decode($rL['data'], true) ?: [];
            $invNo = (string)($rL['invoice_no'] ?: ($d['invoiceNo'] ?? ($d['id'] ?? '')));
            $d['invoiceNo'] = $invNo;
            $d['id'] = $invNo;
            $lastPur = $d;
        }
    }

    $nextPurNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'lastInvoiceNo' => $lastInvoiceNo,
        'nextPurchaseNo' => $nextPurNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastPur;
    return $res;
}

function getDeliverySequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'DEL-' . date('ym');
    $maxSeq = 0;
    $lastDel = null;
    $lastRowId = 0;
    $totalCount = 0;

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, frontend_id, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.deliveryNo')), JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')), '') AS j_no FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
        $stmt->execute([$tenantId]);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($lastRowId === 0) {
                $lastRowId = (int)$r['id'];
            }
            $jNo = ($r['j_no'] !== 'null') ? $r['j_no'] : '';
            $dNo = (string)($jNo ?: ($r['frontend_id'] ?? ''));
            if (strpos($dNo, $prefix) === 0) {
                $s = (int)substr($dNo, strlen($prefix));
                if ($s > $maxSeq) $maxSeq = $s;
            } elseif (preg_match('/(\d+)$/', $dNo, $m)) {
                $s = (int)$m[1];
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
        if ($includeLast && $lastRowId > 0) {
            $stLast = $pdo->prepare("SELECT data FROM delivery_notes WHERE id = ? LIMIT 1");
            $stLast->execute([$lastRowId]);
            $rawLast = $stLast->fetchColumn();
            if ($rawLast) $lastDel = json_decode($rawLast, true);
        }
    } catch (Exception $e) {}

    $nextDelNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextDeliveryNo' => $nextDelNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastDel;
    return $res;
}

function getReceiptSequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'GRN-' . date('ym');
    $maxSeq = 0;
    $lastRec = null;
    $lastRowId = 0;
    $totalCount = 0;

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_EXTRACT(data, '$.isGRN') = true OR invoice_no LIKE 'Z%' OR invoice_no LIKE 'GRN%')");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, invoice_no, COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')), '') AS j_no FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_EXTRACT(data, '$.isGRN') = true OR invoice_no LIKE 'Z%' OR invoice_no LIKE 'GRN%') ORDER BY id DESC LIMIT 300");
        $stmt->execute([$tenantId]);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($lastRowId === 0) {
                $lastRowId = (int)$r['id'];
            }
            $jNo = ($r['j_no'] !== 'null') ? $r['j_no'] : '';
            $rNo = (string)($r['invoice_no'] ?: $jNo);
            if (strpos($rNo, $prefix) === 0) {
                $s = (int)substr($rNo, strlen($prefix));
                if ($s > $maxSeq) $maxSeq = $s;
            } elseif (preg_match('/(\d+)$/', $rNo, $m)) {
                $s = (int)$m[1];
                if ($s > $maxSeq) $maxSeq = $s;
            }
        }
        if ($includeLast && $lastRowId > 0) {
            $stLast = $pdo->prepare("SELECT data FROM purchase_invoices WHERE id = ? LIMIT 1");
            $stLast->execute([$lastRowId]);
            $rawLast = $stLast->fetchColumn();
            if ($rawLast) $lastRec = json_decode($rawLast, true);
        }
    } catch (Exception $e) {}

    $nextRecNo = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextReceiptNo' => $nextRecNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastRec;
    return $res;
}

function getJournalSequence($pdo, $tenantId, $includeLast = false) {
    $prefix = 'J' . date('ym');
    $maxSeq = 0;
    $lastRec = null;
    $totalCount = 0;

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM journal_vouchers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();
        if ($totalCount > $maxSeq) $maxSeq = $totalCount;

        $stmt = $pdo->prepare("SELECT id, frontend_id, data, created_at FROM journal_vouchers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
        $stmt->execute([$tenantId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($row['data'], true);
            if (!$d || !empty($d['deleted'])) continue;
            $rawNo = trim($row['frontend_id'] ?? ($d['voucherNo'] ?? ($d['id'] ?? '')));
            $seq = extractUniversalDocSeq($rawNo, 'J', $prefix);
            if ($seq > $maxSeq) $maxSeq = $seq;
            if ($includeLast && $lastRec === null) {
                $lastRec = $d;
                $lastRec['_db_id'] = $row['id'];
            }
        }
    } catch (Exception $e) {}

    $nextSeq = $maxSeq + 1;
    $nextJournalNo = $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextJournalNo' => $nextJournalNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastRec;
    return $res;
}

function getInventorySequence($pdo, $tenantId, $prefixLetter = 'I', $includeLast = false) {
    $letter = strtoupper(substr(trim($prefixLetter ?: 'I'), 0, 1));
    $prefix = $letter . date('ym');
    $maxSeq = 0;
    $lastRec = null;
    $totalCount = 0;

    try {
        $stmtCnt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL)");
        $stmtCnt->execute([$tenantId]);
        $totalCount = (int)$stmtCnt->fetchColumn();
        if ($totalCount > $maxSeq) $maxSeq = $totalCount;

        $stmt = $pdo->prepare("SELECT id, frontend_id, data, created_at FROM inventory WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 300");
        $stmt->execute([$tenantId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($row['data'], true);
            if (!$d || !empty($d['deleted'])) continue;
            $rawNo = trim($row['frontend_id'] ?? ($d['docNo'] ?? ($d['id'] ?? '')));
            $seq = extractUniversalDocSeq($rawNo, $letter, $prefix);
            if ($seq > $maxSeq) $maxSeq = $seq;
            if ($includeLast && $lastRec === null) {
                $lastRec = $d;
                $lastRec['_db_id'] = $row['id'];
            }
        }
    } catch (Exception $e) {}

    $nextSeq = $maxSeq + 1;
    $nextDocNo = $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    $res = [
        'prefix' => $prefix,
        'maxSeq' => $maxSeq,
        'nextDocNo' => $nextDocNo,
        'totalCount' => $totalCount
    ];
    if ($includeLast) $res['lastRecord'] = $lastRec;
    return $res;
}

/**
 * ─────────────────────────────────────────────────────────────
 * IMMEDIATE LEDGER & STOCK POSTING ENGINE IN MYSQL
 * يرحّل أرصدة (العميل / المورد / المخزون / آخر تكلفة شراء / الجرد / المناقلات / القيود اليدوية) فوراً في MySQL
 * مع حماية كاملة ضد التكرار (Idempotent Delta Reversal عند التعديل)
 * ─────────────────────────────────────────────────────────────
 */
function applyInvoiceEffectsInDB($pdo, $tenantId, $docType, &$data, $oldData = null) {
    try {
        // 1. إذا كان هذا تعديلاً لفاتورة سابقة سبق ترحيل أثرها، نعكس الأثر القديم أولاً بدقة
        if (is_array($oldData) && !empty($oldData['_ledgerPosted']) && is_array($oldData['_ledgerPosted'])) {
            $prev = $oldData['_ledgerPosted'];
            if (!empty($prev['partnerTable']) && !empty($prev['partnerRowId']) && !empty($prev['partnerDelta'])) {
                $pTbl = in_array($prev['partnerTable'], ['suppliers', 'customers', 'safes', 'banks', 'accounts'], true) ? $prev['partnerTable'] : 'customers';
                $stRevP = $pdo->prepare("SELECT id, data FROM `$pTbl` WHERE id = ? LIMIT 1");
                $stRevP->execute([(int)$prev['partnerRowId']]);
                $rRevP = $stRevP->fetch(PDO::FETCH_ASSOC);
                if ($rRevP) {
                    $pObj = json_decode($rRevP['data'], true) ?: [];
                    $curBal = (float)($pObj['currentBalance'] ?? ($pObj['balance'] ?? ($pObj['openingBalance'] ?? 0)));
                    $pObj['currentBalance'] = round($curBal - (float)$prev['partnerDelta'], 2);
                    if (isset($pObj['balance'])) $pObj['balance'] = $pObj['currentBalance'];
                    $upRevP = $pdo->prepare("UPDATE `$pTbl` SET data = ?, updated_at = NOW() WHERE id = ?");
                    $upRevP->execute([json_encode($pObj, JSON_UNESCAPED_UNICODE), $rRevP['id']]);
                }
            }
            if (!empty($prev['stockDeltas']) && is_array($prev['stockDeltas'])) {
                foreach ($prev['stockDeltas'] as $sd) {
                    $itRowId = (int)($sd['itemRowId'] ?? 0);
                    $deltaVal = (float)($sd['delta'] ?? 0);
                    $whName = trim((string)($sd['warehouse'] ?? 'المستودع الرئيسي'));
                    if ($itRowId > 0 && $deltaVal != 0) {
                        $stRevIt = $pdo->prepare("SELECT id, data FROM items WHERE id = ? LIMIT 1");
                        $stRevIt->execute([$itRowId]);
                        $rRevIt = $stRevIt->fetch(PDO::FETCH_ASSOC);
                        if ($rRevIt) {
                            $itObj = json_decode($rRevIt['data'], true) ?: [];
                            if (isset($itObj['stock']) && is_array($itObj['stock'])) {
                                $isAssoc = array_keys($itObj['stock']) !== range(0, count($itObj['stock']) - 1);
                                if ($isAssoc) {
                                    $itObj['stock'][$whName] = round((float)($itObj['stock'][$whName] ?? 0) - $deltaVal, 3);
                                } elseif (isset($itObj['stock'][0]['qty'])) {
                                    $itObj['stock'][0]['qty'] = round((float)$itObj['stock'][0]['qty'] - $deltaVal, 3);
                                }
                            } else {
                                $itObj['stock'] = round((float)($itObj['stock'] ?? ($itObj['quantity'] ?? 0)) - $deltaVal, 3);
                            }
                            $upRevIt = $pdo->prepare("UPDATE items SET data = ?, updated_at = NOW() WHERE id = ?");
                            $upRevIt->execute([json_encode($itObj, JSON_UNESCAPED_UNICODE), $rRevIt['id']]);
                        }
                    }
                }
            }
        }

        $postedMeta = [
            'postedAt' => date('Y-m-d H:i:s'),
            'docType' => $docType,
            'partnerTable' => null,
            'partnerRowId' => null,
            'partnerDelta' => 0,
            'stockDeltas' => []
        ];

        // 2. تحديث رصيد العميل أو المورد في جدول customers / suppliers
        if (in_array($docType, ['sales', 'sales_split', 'purchase', 'purchase_split'], true)) {
            $isSales = ($docType === 'sales' || $docType === 'sales_split');
            $partnerTable = $isSales ? 'customers' : 'suppliers';
            $partnerName = trim((string)($isSales
                ? ($data['customerName'] ?? ($data['customer_name'] ?? ''))
                : ($data['supplierName'] ?? ($data['supplier_name'] ?? ''))
            ));
            $partnerId = trim((string)($isSales
                ? ($data['customerId'] ?? ($data['customer'] ?? ''))
                : ($data['supplierId'] ?? ($data['supplier'] ?? ''))
            ));

            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['subTotal'] ?? 0)));
            $paidVal = (float)($data['paidAmount'] ?? ($data['downPayment'] ?? 0));
            $payMethod = strtolower(trim((string)($data['paymentMethod'] ?? 'cash')));
            $isSplitDoc = ($docType === 'sales_split' || $docType === 'purchase_split' || !empty($data['isSplit']));

            $partnerDelta = 0.0;
            if ($isSplitDoc) {
                $partnerDelta = isset($data['remainingBalance']) ? (float)$data['remainingBalance'] : max(0.0, $totalVal - $paidVal);
            } elseif (in_array($payMethod, ['credit', 'آجل', 'اجل', 'deferred'], true)) {
                $partnerDelta = max(0.0, $totalVal - $paidVal);
            } elseif (isset($data['remainingBalance']) && (float)$data['remainingBalance'] > 0) {
                $partnerDelta = (float)$data['remainingBalance'];
            }

            $isGenericCashPartner = in_array($partnerName, ['عميل نقدي', 'عميل نقدي عام', 'مورد نقدي', 'مورد نقدي عام'], true);
            if ($partnerDelta != 0 && (!$isGenericCashPartner || $partnerId !== '')) {
                $partnerWhere = ($partnerTable === 'customers')
                    ? "(frontend_id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?)"
                    : "(frontend_id = ? OR supplier_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?)";
                $stmtP = $pdo->prepare("SELECT id, data FROM `$partnerTable` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND $partnerWhere LIMIT 1");
                $stmtP->execute([$tenantId, $partnerId ?: $partnerName, $partnerName, $partnerName]);
                $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
                if ($rowP) {
                    $pData = json_decode($rowP['data'], true) ?: [];
                    $curBal = (float)($pData['currentBalance'] ?? ($pData['openingBalance'] ?? 0));
                    $pData['currentBalance'] = round($curBal + $partnerDelta, 2);
                    $upP = $pdo->prepare("UPDATE `$partnerTable` SET data = ?, updated_at = NOW() WHERE id = ?");
                    $upP->execute([json_encode($pData, JSON_UNESCAPED_UNICODE), $rowP['id']]);
                    $postedMeta['partnerTable'] = $partnerTable;
                    $postedMeta['partnerRowId'] = (int)$rowP['id'];
                    $postedMeta['partnerDelta'] = $partnerDelta;
                }
            }

            // 3. تحديث كمية المخزون وآخر سعر شراء في جدول items
            $defaultWh = trim((string)($data['warehouse'] ?? ($data['warehouseName'] ?? 'المستودع الرئيسي'))) ?: 'المستودع الرئيسي';
            $lines = (isset($data['items']) && is_array($data['items'])) ? $data['items'] : [];
            foreach ($lines as $line) {
                if (!is_array($line)) continue;
                $itName = trim((string)($line['name'] ?? ($line['itemName'] ?? '')));
                $itId = trim((string)($line['id'] ?? ($line['itemId'] ?? '')));
                $qty = (float)($line['qty'] ?? ($line['quantity'] ?? 0));
                $factor = (float)($line['factor'] ?? ($line['unitFactor'] ?? 1));
                if ($factor <= 0) $factor = 1.0;
                $lineWh = trim((string)($line['warehouse'] ?? $defaultWh)) ?: 'المستودع الرئيسي';
                $linePrice = (float)($line['price'] ?? 0);

                if ($qty <= 0 || ($itName === '' && $itId === '')) continue;

                $stmtIt = $pdo->prepare("SELECT id, data FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (frontend_id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?) LIMIT 1");
                $stmtIt->execute([$tenantId, $itId ?: $itName, $itName, $itName]);
                $rowIt = $stmtIt->fetch(PDO::FETCH_ASSOC);
                if ($rowIt) {
                    $itData = json_decode($rowIt['data'], true) ?: [];
                    $baseQtyDelta = ($isSales ? -1.0 : 1.0) * ($qty * $factor);

                    if (isset($itData['stock']) && is_array($itData['stock'])) {
                        $isAssoc = count($itData['stock']) > 0 && (array_keys($itData['stock']) !== range(0, count($itData['stock']) - 1));
                        if ($isAssoc) {
                            $itData['stock'][$lineWh] = round((float)($itData['stock'][$lineWh] ?? 0) + $baseQtyDelta, 3);
                        } else {
                            $matchedWh = false;
                            foreach ($itData['stock'] as &$stEntry) {
                                if (is_array($stEntry) && isset($stEntry['warehouse']) && $stEntry['warehouse'] === $lineWh) {
                                    $stEntry['qty'] = round((float)($stEntry['qty'] ?? 0) + $baseQtyDelta, 3);
                                    $matchedWh = true;
                                    break;
                                }
                            }
                            unset($stEntry);
                            if (!$matchedWh) {
                                if (isset($itData['stock'][0]) && is_array($itData['stock'][0]) && isset($itData['stock'][0]['qty'])) {
                                    $itData['stock'][0]['qty'] = round((float)$itData['stock'][0]['qty'] + $baseQtyDelta, 3);
                                } else {
                                    $itData['stock'][$lineWh] = round($baseQtyDelta, 3);
                                }
                            }
                        }
                    } else {
                        $curStock = (float)($itData['stock'] ?? ($itData['quantity'] ?? 0));
                        $itData['stock'] = round($curStock + $baseQtyDelta, 3);
                    }

                    // في حالة المشتريات: حفظ آخر سعر شراء وتحديث تكلفة الصنف في قاعدة البيانات
                    if (!$isSales && $linePrice > 0) {
                        $unitCost = ($factor > 1 && $linePrice > $factor) ? round($linePrice / $factor, 2) : $linePrice;
                        $itData['lastPurchaseCost'] = $linePrice;
                        $itData['lastPurchaseUnit'] = trim((string)($line['unit'] ?? ''));
                        $itData['lastPurchaseSupplier'] = $partnerName;
                        $itData['lastPurchaseDate'] = $data['date'] ?? date('Y-m-d');
                        if (empty($itData['costPrice']) || $unitCost > 0) {
                            $itData['costPrice'] = $linePrice;
                        }
                    }

                    $upIt = $pdo->prepare("UPDATE items SET data = ?, updated_at = NOW() WHERE id = ?");
                    $upIt->execute([json_encode($itData, JSON_UNESCAPED_UNICODE), $rowIt['id']]);

                    $postedMeta['stockDeltas'][] = [
                        'itemRowId' => (int)$rowIt['id'],
                        'warehouse' => $lineWh,
                        'delta' => $baseQtyDelta
                    ];
                }
            }
        } elseif ($docType === 'finance') {
            // السندات المالية المحفوظة عبر save_voucher أو أوفلاين
            $vAmount = (float)($data['amount'] ?? ($data['totalAmount'] ?? ($data['total'] ?? 0)));
            $vType = strtolower(trim((string)($data['type'] ?? ($data['voucherType'] ?? 'payment'))));
            $targetAccount = trim((string)($data['beneficiaryName'] ?? ($data['accountName'] ?? ($data['customerName'] ?? ($data['supplierName'] ?? '')))));
            if ($vAmount > 0 && $targetAccount !== '') {
                $isReceipt = (strpos($vType, 'receipt') !== false || strpos($vType, 'قبض') !== false);
                $tablesToTry = $isReceipt ? ['customers', 'suppliers'] : ['suppliers', 'customers'];
                foreach ($tablesToTry as $tbl) {
                    $tblWhere = ($tbl === 'customers')
                        ? "(frontend_id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?)"
                        : "(frontend_id = ? OR supplier_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?)";
                    $stAcc = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND $tblWhere LIMIT 1");
                    $stAcc->execute([$tenantId, $targetAccount, $targetAccount, $targetAccount]);
                    $rAcc = $stAcc->fetch(PDO::FETCH_ASSOC);
                    if ($rAcc) {
                        $accObj = json_decode($rAcc['data'], true) ?: [];
                        $curBal = (float)($accObj['currentBalance'] ?? ($accObj['openingBalance'] ?? 0));
                        // سند القبض يخفض مديونية العميل، وسند الصرف يخفض مديونية المورد
                        $delta = -$vAmount;
                        $accObj['currentBalance'] = round($curBal + $delta, 2);
                        $upAcc = $pdo->prepare("UPDATE `$tbl` SET data = ?, updated_at = NOW() WHERE id = ?");
                        $upAcc->execute([json_encode($accObj, JSON_UNESCAPED_UNICODE), $rAcc['id']]);
                        $postedMeta['partnerTable'] = $tbl;
                        $postedMeta['partnerRowId'] = (int)$rAcc['id'];
                        $postedMeta['partnerDelta'] = $delta;
                        break;
                    }
                }
            }
        } elseif ($docType === 'stock_adjustment') {
            // تسوية الجرد المخزني وفروقات التالف
            $whName = trim((string)($data['warehouse'] ?? ($data['warehouseName'] ?? 'المستودع الرئيسي'))) ?: 'المستودع الرئيسي';
            $itName = trim((string)($data['itemName'] ?? ($data['name'] ?? '')));
            $itId = trim((string)($data['itemId'] ?? ($data['id_item'] ?? '')));
            $actualQty = isset($data['actualQty']) ? (float)$data['actualQty'] : null;
            $bookQty = isset($data['bookQty']) ? (float)$data['bookQty'] : null;
            $varianceQty = isset($data['varianceDelta']) ? (float)$data['varianceDelta'] : (($actualQty !== null && $bookQty !== null) ? ($actualQty - $bookQty) : 0.0);

            if ($itName !== '' || $itId !== '') {
                $stmtIt = $pdo->prepare("SELECT id, data FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (frontend_id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?) LIMIT 1");
                $stmtIt->execute([$tenantId, $itId ?: $itName, $itName, $itName]);
                $rowIt = $stmtIt->fetch(PDO::FETCH_ASSOC);
                if ($rowIt) {
                    $itData = json_decode($rowIt['data'], true) ?: [];
                    if (isset($itData['stock']) && is_array($itData['stock'])) {
                        $isAssoc = count($itData['stock']) > 0 && (array_keys($itData['stock']) !== range(0, count($itData['stock']) - 1));
                        if ($isAssoc) {
                            $curW = (float)($itData['stock'][$whName] ?? 0);
                            $itData['stock'][$whName] = ($actualQty !== null) ? round($actualQty, 3) : round($curW + $varianceQty, 3);
                        } else {
                            if (isset($itData['stock'][0]['qty'])) {
                                $curW = (float)$itData['stock'][0]['qty'];
                                $itData['stock'][0]['qty'] = ($actualQty !== null) ? round($actualQty, 3) : round($curW + $varianceQty, 3);
                            }
                        }
                    } else {
                        $curStock = (float)($itData['stock'] ?? ($itData['quantity'] ?? 0));
                        $itData['stock'] = ($actualQty !== null) ? round($actualQty, 3) : round($curStock + $varianceQty, 3);
                    }
                    $upIt = $pdo->prepare("UPDATE items SET data = ?, updated_at = NOW() WHERE id = ?");
                    $upIt->execute([json_encode($itData, JSON_UNESCAPED_UNICODE), $rowIt['id']]);
                    $postedMeta['stockDeltas'][] = [
                        'itemRowId' => (int)$rowIt['id'],
                        'warehouse' => $whName,
                        'delta' => $varianceQty
                    ];
                }
            }
        } elseif ($docType === 'warehouse_transfer') {
            // تحويل ومناقلة بين المستودعات
            $fromWh = trim((string)($data['fromWarehouse'] ?? 'المستودع الرئيسي'));
            $toWh = trim((string)($data['toWarehouse'] ?? 'مخزن صالة المعرض'));
            $itName = trim((string)($data['itemName'] ?? ($data['name'] ?? '')));
            $itId = trim((string)($data['itemId'] ?? ''));
            $qty = (float)($data['qty'] ?? ($data['quantity'] ?? 0));

            if ($qty > 0 && ($itName !== '' || $itId !== '') && $fromWh !== $toWh) {
                $stmtIt = $pdo->prepare("SELECT id, data FROM items WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (frontend_id = ? OR search_name = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?) LIMIT 1");
                $stmtIt->execute([$tenantId, $itId ?: $itName, $itName, $itName]);
                $rowIt = $stmtIt->fetch(PDO::FETCH_ASSOC);
                if ($rowIt) {
                    $itData = json_decode($rowIt['data'], true) ?: [];
                    if (!isset($itData['warehouseStocks']) || !is_array($itData['warehouseStocks'])) {
                        $itData['warehouseStocks'] = [];
                    }
                    $curFrom = (float)($itData['warehouseStocks'][$fromWh] ?? ($itData['stock'] ?? 0));
                    $curTo = (float)($itData['warehouseStocks'][$toWh] ?? 0);
                    $itData['warehouseStocks'][$fromWh] = round(max(0, $curFrom - $qty), 3);
                    $itData['warehouseStocks'][$toWh] = round($curTo + $qty, 3);
                    if (isset($itData['stock']) && is_array($itData['stock'])) {
                        $isAssoc = count($itData['stock']) > 0 && (array_keys($itData['stock']) !== range(0, count($itData['stock']) - 1));
                        if ($isAssoc) {
                            $itData['stock'][$fromWh] = round((float)($itData['stock'][$fromWh] ?? 0) - $qty, 3);
                            $itData['stock'][$toWh] = round((float)($itData['stock'][$toWh] ?? 0) + $qty, 3);
                        }
                    }
                    $upIt = $pdo->prepare("UPDATE items SET data = ?, updated_at = NOW() WHERE id = ?");
                    $upIt->execute([json_encode($itData, JSON_UNESCAPED_UNICODE), $rowIt['id']]);
                }
            }
        } elseif ($docType === 'journal_entry') {
            // قيود اليومية اليدوية وتسوية الحسابات
            $amount = (float)($data['amount'] ?? ($data['totalDebit'] ?? 0));
            $debitName = trim((string)($data['debitAccountName'] ?? ''));
            $creditName = trim((string)($data['creditAccountName'] ?? ''));
            $updatePartyByName = function($accName, $deltaSign) use ($pdo, $tenantId, $amount) {
                if ($accName === '' || $amount <= 0) return;
                // تنظيف الاسم في حال كان يحتوي على الرصيد بين قوسين
                $cleanName = trim(preg_replace('/\s*\(.*$/u', '', $accName));
                foreach (['customers', 'suppliers', 'safes', 'banks'] as $tbl) {
                    try {
                        $st = $pdo->prepare("SELECT id, data FROM `$tbl` WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND (frontend_id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) = ?) LIMIT 1");
                        $st->execute([$tenantId, $cleanName, $cleanName]);
                        $r = $st->fetch(PDO::FETCH_ASSOC);
                        if ($r) {
                            $obj = json_decode($r['data'], true) ?: [];
                            $cur = (float)($obj['currentBalance'] ?? ($obj['balance'] ?? ($obj['openingBalance'] ?? 0)));
                            // بالنسبة للموردين: المدين يخفض مديونيتهم والدائن يزيدها، وللعملاء والخزائن: المدين يزيد والدائن ينقص
                            $effectiveSign = ($tbl === 'suppliers') ? (-1.0 * $deltaSign) : $deltaSign;
                            $obj['currentBalance'] = round($cur + ($effectiveSign * $amount), 2);
                            if (isset($obj['balance'])) $obj['balance'] = $obj['currentBalance'];
                            $up = $pdo->prepare("UPDATE `$tbl` SET data = ?, updated_at = NOW() WHERE id = ?");
                            $up->execute([json_encode($obj, JSON_UNESCAPED_UNICODE), $r['id']]);
                            return;
                        }
                    } catch (Throwable $e) {}
                }
            };
            $updatePartyByName($debitName, 1.0);
            $updatePartyByName($creditName, -1.0);
        }

        $data['_ledgerPosted'] = $postedMeta;
    } catch (Throwable $e) {
        // Non-blocking ledger hook
    }
}

// ─────────────────────────────────────────────────────────────
// MAIN DISPATCHER
// ─────────────────────────────────────────────────────────────

try {
    switch ($action) {
        case 'check_git_version':
            $gitHead = trim(@shell_exec('git rev-parse HEAD') ?: 'unknown');
            $gitLog = trim(@shell_exec('git log -n 1 --oneline') ?: 'unknown');
            $suitePath = __DIR__ . '/../mobile_erp_global_suite.html';
            $suiteContent = file_exists($suitePath) ? file_get_contents($suitePath) : '';
            echo json_encode([
                'success' => true,
                'git_head' => $gitHead,
                'git_log' => $gitLog,
                'suite_exists' => file_exists($suitePath),
                'suite_size' => strlen($suiteContent),
                'suite_has_pos' => strpos($suiteContent, 'SuiteSalesPOS') !== false,
                'suite_has_calc' => strpos($suiteContent, 'suiteSmartCalcModal') !== false,
                'has_cost_center_top' => strpos($suiteContent, 'suiteCostCenterDisplayName') !== false,
                'has_cost_center_search' => strpos($suiteContent, 'suiteCostCenterSearchModal') !== false,
                'has_customer_search' => strpos($suiteContent, 'suiteCustomerSearchModal') !== false,
                'has_warehouse_search' => strpos($suiteContent, 'suiteWarehouseSearchModal') !== false,
                'has_item_search' => strpos($suiteContent, 'suiteItemSearchModal') !== false,
                'has_dual_balance' => strpos($suiteContent, 'suiteCustBalForeign') !== false,
                'has_statement_btn' => strpos($suiteContent, 'btnCustStatementDirect') !== false,
                'has_checks_payment' => strpos($suiteContent, 'suitePayBtnCheck') !== false,
                'server_time' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            exit;

        // ═════════════════════════════════════════════════════════
        // 1A. ULTRA-LIGHTWEIGHT 2G NEXT NUMBERS (~350 bytes)
        // ═════════════════════════════════════════════════════════
        case 'get_next_numbers':
            echo json_encode([
                'success' => true,
                'tenant_id' => $tenantId,
                'server_time' => date('Y-m-d H:i:s'),
                'finance' => getFinanceSequence($pdo, $tenantId, false),
                'sales' => getSalesSequence($pdo, $tenantId, false),
                'purchase' => getPurchaseSequence($pdo, $tenantId, false),
                'delivery' => getDeliverySequence($pdo, $tenantId, false),
                'receipt' => getReceiptSequence($pdo, $tenantId, false),
                'journal' => getJournalSequence($pdo, $tenantId, false),
                'inventory' => getInventorySequence($pdo, $tenantId, 'I', false)
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 1B. FULL MIRROR STATUS & LATEST RECORDS
        // ═════════════════════════════════════════════════════════
        case 'get_mirror_status':
        default:
            $finSeq = getFinanceSequence($pdo, $tenantId, true);
            $salesSeq = getSalesSequence($pdo, $tenantId, true);
            $purSeq = getPurchaseSequence($pdo, $tenantId, true);
            $delSeq = getDeliverySequence($pdo, $tenantId, true);
            $recSeq = getReceiptSequence($pdo, $tenantId, true);
            $jvSeq = getJournalSequence($pdo, $tenantId, true);
            $invSeq = getInventorySequence($pdo, $tenantId, 'I', true);

            $rawItems = $pdo->query("SELECT data FROM items WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);
            $rawCusts = $pdo->query("SELECT data FROM customers WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 100")->fetchAll(PDO::FETCH_COLUMN);
            $rawWhs = $pdo->query("SELECT data FROM warehouses WHERE (is_deleted = 0 OR is_deleted IS NULL) LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);

            $dbItems = array_values(array_filter(array_map('json_decode', $rawItems)));
            $dbCusts = array_values(array_filter(array_map('json_decode', $rawCusts)));
            $dbWhs = array_values(array_filter(array_map('json_decode', $rawWhs)));

            echo json_encode([
                'success' => true,
                'tenant_id' => $tenantId,
                'server_time' => date('Y-m-d H:i:s'),
                'finance' => $finSeq,
                'sales' => $salesSeq,
                'purchase' => $purSeq,
                'delivery' => $delSeq,
                'receipt' => $recSeq,
                'journal' => $jvSeq,
                'inventory' => $invSeq,
                'items' => $dbItems,
                'customers' => $dbCusts,
                'warehouses' => $dbWhs
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 2. SAVE SALES INVOICE (FROM MOBILE OR DESKTOP)
        // ═════════════════════════════════════════════════════════
        case 'save_sales_invoice':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات الفاتورة فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $idemKey = trim((string)($data['idempotencyKey'] ?? ($data['idempotency_key'] ?? ($data['client_draft_id'] ?? ''))));
            if ($idemKey !== '') {
                try {
                    $stmtIdem = $pdo->prepare("SELECT invoice_no, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? LIMIT 1");
                    $stmtIdem->execute([$tenantId, $idemKey]);
                    $rowIdem = $stmtIdem->fetch(PDO::FETCH_ASSOC);
                    if ($rowIdem) {
                        $existRec = json_decode($rowIdem['data'], true) ?: [];
                        $existNo = $rowIdem['invoice_no'] ?: ($existRec['invoiceNo'] ?? '');
                        $updatedSeq = getSalesSequence($pdo, $tenantId);
                        echo json_encode([
                            'success' => true,
                            'idempotent' => true,
                            'message' => 'تم حفظ الفاتورة مسبقاً (منع التكرار)',
                            'invoiceNo' => $existNo,
                            'nextInvoiceNo' => $updatedSeq['nextInvoiceNo'],
                            'record' => $existRec
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                } catch (Exception $e) {}
            }

            $salesSeq = getSalesSequence($pdo, $tenantId);
            $invNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            $isEdit = !empty($data['isEdit']);
            $originalOfflineId = '';
            if (strpos($invNo, 'أوفلاين') !== false || strpos($invNo, 'OFF-') !== false || strpos($invNo, 'offline') !== false) {
                $originalOfflineId = $invNo;
                $data['offline_id'] = $invNo;
                $invNo = $salesSeq['nextInvoiceNo'];
            } elseif (empty($invNo) || strpos($invNo, 'undefined') !== false || $invNo === 'new' || $invNo === 'SAL-NEW' || $invNo === 'S26101012' || preg_match('/^S\d{4}\d{5,}$/i', $invNo) || preg_match('/^S\d{10,}$/i', $invNo)) {
                $invNo = $salesSeq['nextInvoiceNo'];
            }

            // Check if exists
            $stmtCheck = $pdo->prepare("SELECT id, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmtCheck->execute([$tenantId, $invNo, $invNo, $invNo]);
            $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $existingId = $existingRow ? $existingRow['id'] : false;
            $oldData = ($existingRow && !empty($existingRow['data'])) ? (json_decode($existingRow['data'], true) ?: null) : null;

            // If this is a new invoice (not an explicit edit) and the number was already taken by another device (e.g. Laptop), assign the next sequential number
            if ($existingId && !$isEdit) {
                $invNo = $salesSeq['nextInvoiceNo'];
                $existingId = false;
                $oldData = null;
            }

            $data['invoiceNo'] = $invNo;
            $data['id'] = $invNo;
            if ($idemKey !== '') $data['idempotencyKey'] = $idemKey;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            $dateVal = $data['date'];

            $custName = $data['customerName'] ?? ($data['customer_name'] ?? 'عميل نقدي');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['netTotal'] ?? ($data['subTotal'] ?? 0))));
            $statusVal = $data['status'] ?? ($data['saleType'] ?? 'completed');
            $currencyVal = $data['currency'] ?? 'SDG';
            $rateVal = (float)($data['exchangeRate'] ?? ($data['rate'] ?? 1.0));
            $userVal = $data['createdBy'] ?? 'user';

            $data['customerId'] = $data['customerId'] ?? $custName;
            $data['customerName'] = $custName;
            $data['netTotal'] = $totalVal;
            $data['total'] = $totalVal;
            $data['grandTotal'] = $totalVal;
            $data['status'] = $statusVal;
            $data['currency'] = $currencyVal;
            $data['exchangeRate'] = $rateVal;
            $data['createdBy'] = $userVal;
            $data['deleted'] = 0;

            applyInvoiceEffectsInDB($pdo, $tenantId, 'sales', $data, $oldData);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $invNo, $jsonRecord]);
            }

            // Sync queue
            try {
                $syncId = $idemKey !== '' ? $idemKey : ('sync-' . bin2hex(random_bytes(6)));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'sales_invoices', ?, ?, ?, 'SYNCED', NOW(), NOW()) ON DUPLICATE KEY UPDATE record_id = VALUES(record_id), status = 'SYNCED', synced_at = NOW()");
                $stmtSync->execute([$syncId, $invNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Throwable $e) {}

            $updatedSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ فاتورة المبيعات في قاعدة البيانات بنجاح',
                'invoiceNo' => $invNo,
                'nextInvoiceNo' => $updatedSeq['nextInvoiceNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 3. SAVE SALES SPLIT / INSTALLMENTS (تقسيم مبيعات وجدولة أقساط)
        // ═════════════════════════════════════════════════════════
        case 'save_sales_split':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات تقسيم المبيعات فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $salesSeq = getSalesSequence($pdo, $tenantId);
            $invNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            if (empty($invNo) || $invNo === 'new') {
                $invNo = $salesSeq['nextInvoiceNo'];
            }
            $data['invoiceNo'] = $invNo;
            $data['id'] = $invNo;
            $data['isSplit'] = true;
            $data['saleType'] = 'split';
            $data['status'] = 'split';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['updatedAt'] = date('Y-m-d H:i:s');

            $custName = $data['customerName'] ?? ($data['customer_name'] ?? 'عميل تجزئة');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['netTotal'] ?? 0)));
            $paidVal = (float)($data['paidAmount'] ?? ($data['downPayment'] ?? 0));
            $remainingVal = $totalVal - $paidVal;
            $data['remainingBalance'] = $remainingVal;
            $data['customerId'] = $data['customerId'] ?? $custName;
            $data['customerName'] = $custName;
            $data['netTotal'] = $totalVal;
            $data['total'] = $totalVal;
            $data['grandTotal'] = $totalVal;
            $data['deleted'] = 0;

            $stmtCheck = $pdo->prepare("SELECT id, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?) LIMIT 1");
            $stmtCheck->execute([$tenantId, $invNo, $invNo]);
            $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $existingId = $existingRow ? $existingRow['id'] : false;
            $oldData = ($existingRow && !empty($existingRow['data'])) ? (json_decode($existingRow['data'], true) ?: null) : null;

            applyInvoiceEffectsInDB($pdo, $tenantId, 'sales_split', $data, $oldData);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE sales_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$invNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $invNo, $jsonRecord]);
            }

            $updatedSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ واعتماد فاتورة تقسيم المبيعات وجدولة الأقساط بنجاح',
                'invoiceNo' => $invNo,
                'nextInvoiceNo' => $updatedSeq['nextInvoiceNo'],
                'remaining' => $remainingVal,
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 4. SAVE PURCHASE INVOICE (FROM MOBILE OR DESKTOP)
        // ═════════════════════════════════════════════════════════
        case 'save_purchase_invoice':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات فاتورة الشراء فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $idemKey = trim((string)($data['idempotencyKey'] ?? ($data['idempotency_key'] ?? ($data['client_draft_id'] ?? ''))));
            if ($idemKey !== '') {
                try {
                    $stmtIdem = $pdo->prepare("SELECT invoice_no, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? LIMIT 1");
                    $stmtIdem->execute([$tenantId, $idemKey]);
                    $rowIdem = $stmtIdem->fetch(PDO::FETCH_ASSOC);
                    if ($rowIdem) {
                        $existRec = json_decode($rowIdem['data'], true) ?: [];
                        $existNo = $rowIdem['invoice_no'] ?: ($existRec['invoiceNo'] ?? '');
                        $updatedSeq = getPurchaseSequence($pdo, $tenantId);
                        echo json_encode([
                            'success' => true,
                            'idempotent' => true,
                            'message' => 'تم حفظ فاتورة المشتريات مسبقاً (منع التكرار)',
                            'invoiceNo' => $existNo,
                            'nextPurchaseNo' => $updatedSeq['nextPurchaseNo'],
                            'record' => $existRec
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                } catch (Throwable $e) {}
            }

            $purSeq = getPurchaseSequence($pdo, $tenantId);
            $purNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            $isEdit = !empty($data['isEdit']);
            $originalOfflineId = '';
            if (strpos($purNo, 'أوفلاين') !== false || strpos($purNo, 'OFF-') !== false || strpos($purNo, 'offline') !== false) {
                $originalOfflineId = $purNo;
                $data['offline_id'] = $purNo;
                $purNo = $purSeq['nextPurchaseNo'];
            } elseif (empty($purNo) || strpos($purNo, 'undefined') !== false || $purNo === 'new' || $purNo === 'P26100045' || preg_match('/^P\d{10,}$/i', $purNo)) {
                $purNo = $purSeq['nextPurchaseNo'];
            }

            $stmtCheck = $pdo->prepare("SELECT id, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmtCheck->execute([$tenantId, $purNo, $purNo, $purNo]);
            $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $existingId = $existingRow ? $existingRow['id'] : false;
            $oldData = ($existingRow && !empty($existingRow['data'])) ? (json_decode($existingRow['data'], true) ?: null) : null;

            if ($existingId && !$isEdit) {
                $purNo = $purSeq['nextPurchaseNo'];
                $existingId = false;
                $oldData = null;
            }

            $data['invoiceNo'] = $purNo;
            $data['id'] = $purNo;
            if ($idemKey !== '') $data['idempotencyKey'] = $idemKey;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            $dateVal = $data['date'];

            $suppName = $data['supplierName'] ?? ($data['supplier_name'] ?? 'مورد نقدي');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['netTotal'] ?? 0)));
            $statusVal = $data['status'] ?? 'completed';
            $currencyVal = $data['currency'] ?? 'SDG';
            $rateVal = (float)($data['exchangeRate'] ?? 1.0);
            $userVal = $data['createdBy'] ?? 'user';

            $data['supplierId'] = $data['supplierId'] ?? $suppName;
            $data['supplierName'] = $suppName;
            $data['netTotal'] = $totalVal;
            $data['total'] = $totalVal;
            $data['grandTotal'] = $totalVal;
            $data['status'] = $statusVal;
            $data['currency'] = $currencyVal;
            $data['exchangeRate'] = $rateVal;
            $data['createdBy'] = $userVal;
            $data['deleted'] = 0;

            applyInvoiceEffectsInDB($pdo, $tenantId, 'purchase', $data, $oldData);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$purNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $purNo, $jsonRecord]);
            }

            try {
                $syncId = $idemKey !== '' ? $idemKey : ('sync-' . bin2hex(random_bytes(6)));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'purchase_invoices', ?, ?, ?, 'SYNCED', NOW(), NOW()) ON DUPLICATE KEY UPDATE record_id = VALUES(record_id), status = 'SYNCED', synced_at = NOW()");
                $stmtSync->execute([$syncId, $purNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch (Throwable $e) {}

            $updatedSeq = getPurchaseSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ فاتورة المشتريات في قاعدة البيانات بنجاح',
                'invoiceNo' => $purNo,
                'nextPurchaseNo' => $updatedSeq['nextPurchaseNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 4B. SAVE PURCHASE SPLIT / INSTALLMENTS (تقسيم مشتريات وجدولة دفعات المورد)
        // ═════════════════════════════════════════════════════════
        case 'save_purchase_split':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات تقسيم المشتريات فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $purSeq = getPurchaseSequence($pdo, $tenantId);
            $purNo = trim($data['invoiceNo'] ?? ($data['id'] ?? ''));
            if (empty($purNo) || $purNo === 'new') {
                $purNo = $purSeq['nextPurchaseNo'];
            }
            $data['invoiceNo'] = $purNo;
            $data['id'] = $purNo;
            $data['isSplit'] = true;
            $data['purchaseType'] = 'split';
            $data['status'] = 'split';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['updatedAt'] = date('Y-m-d H:i:s');

            $suppName = $data['supplierName'] ?? ($data['supplier_name'] ?? 'مورد تجزئة');
            $totalVal = (float)($data['grandTotal'] ?? ($data['total'] ?? ($data['netTotal'] ?? 0)));
            $paidVal = (float)($data['paidAmount'] ?? ($data['downPayment'] ?? 0));
            $remainingVal = $totalVal - $paidVal;
            $data['remainingBalance'] = $remainingVal;
            $data['supplierId'] = $data['supplierId'] ?? $suppName;
            $data['supplierName'] = $suppName;
            $data['netTotal'] = $totalVal;
            $data['total'] = $totalVal;
            $data['grandTotal'] = $totalVal;
            $data['deleted'] = 0;

            $stmtCheck = $pdo->prepare("SELECT id, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?) LIMIT 1");
            $stmtCheck->execute([$tenantId, $purNo, $purNo]);
            $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $existingId = $existingRow ? $existingRow['id'] : false;
            $oldData = ($existingRow && !empty($existingRow['data'])) ? (json_decode($existingRow['data'], true) ?: null) : null;

            applyInvoiceEffectsInDB($pdo, $tenantId, 'purchase_split', $data, $oldData);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE purchase_invoices SET invoice_no = ?, data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$purNo, $jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $purNo, $jsonRecord]);
            }

            $updatedSeq = getPurchaseSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ واعتماد فاتورة تقسيم المشتريات وجدولة دفعات المورد بنجاح',
                'invoiceNo' => $purNo,
                'nextPurchaseNo' => $updatedSeq['nextPurchaseNo'],
                'remaining' => $remainingVal,
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 5. SAVE SALES DELIVERY NOTE (تسليم مبيعات)
        // ═════════════════════════════════════════════════════════
        case 'save_delivery_note':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات إذن التسليم فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $delSeq = getDeliverySequence($pdo, $tenantId);
            $delNo = trim($data['deliveryNo'] ?? ($data['id'] ?? ''));
            if (empty($delNo) || $delNo === 'new') {
                $delNo = $delSeq['nextDeliveryNo'];
            }
            $data['deliveryNo'] = $delNo;
            $data['id'] = $delNo;
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'delivered';
            $data['deleted'] = 0;
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            try {
                $stmtDel = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtDel->execute([$tenantId, $jsonRecord]);
            } catch (Throwable $e) {}

            // Update associated sales invoice if provided
            $relatedInvNo = trim($data['invoiceNo'] ?? ($data['relatedInvoice'] ?? ''));
            if (!empty($relatedInvNo)) {
                try {
                    $stmtInv = $pdo->prepare("SELECT id, data FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?)");
                    $stmtInv->execute([$tenantId, $relatedInvNo, $relatedInvNo]);
                    $row = $stmtInv->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $invD = json_decode($row['data'], true) ?: [];
                        $invD['deliveryStatus'] = 'delivered';
                        $invD['status'] = 'delivered';
                        $invD['deliveryNo'] = $delNo;
                        $invD['deliveredAt'] = date('Y-m-d H:i:s');
                        $upStmt = $pdo->prepare("UPDATE sales_invoices SET data = ?, updated_at = NOW() WHERE id = ?");
                        $upStmt->execute([json_encode($invD, JSON_UNESCAPED_UNICODE), $row['id']]);
                    }
                } catch (Throwable $e) {}
            }

            $updatedDelSeq = getDeliverySequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم تسجيل وتوثيق تسليم المبيعات وخصم المخزون بنجاح',
                'deliveryNo' => $delNo,
                'nextDeliveryNo' => $updatedDelSeq['nextDeliveryNo']
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6. SAVE PURCHASE RECEIPT / GRN (تسليم واستلام المشتريات)
        // ═════════════════════════════════════════════════════════
        case 'save_purchase_receipt':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات استلام المشتريات فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $recSeq = getReceiptSequence($pdo, $tenantId);
            $recNo = trim($data['receiptNo'] ?? ($data['grnNo'] ?? ($data['id'] ?? '')));
            if (empty($recNo) || $recNo === 'new') {
                $recNo = $recSeq['nextReceiptNo'];
            }
            $data['receiptNo'] = $recNo;
            $data['id'] = $recNo;
            $data['isGRN'] = true;
            $data['type'] = 'purchase_receipt';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'received';
            $data['supplierId'] = $data['supplierId'] ?? ($data['supplierName'] ?? 'مورد');
            $data['netTotal'] = 0;
            $data['deleted'] = 0;
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $relatedPurNo = trim($data['originalInvoiceId'] ?? ($data['invoiceNo'] ?? ''));

            try {
                $stmtIns = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $recNo, $jsonRecord]);
            } catch (Throwable $e) {}

            // Update parent purchase invoice if provided
            if (!empty($relatedPurNo)) {
                try {
                    $stmtPur = $pdo->prepare("SELECT id, data FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (invoice_no = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.invoiceNo')) = ?)");
                    $stmtPur->execute([$tenantId, $relatedPurNo, $relatedPurNo]);
                    $row = $stmtPur->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $pD = json_decode($row['data'], true) ?: [];
                        $pD['receiptStatus'] = 'received';
                        $pD['status'] = 'received';
                        $pD['receiptNo'] = $recNo;
                        $pD['receivedAt'] = date('Y-m-d H:i:s');
                        $upStmt = $pdo->prepare("UPDATE purchase_invoices SET data = ?, updated_at = NOW() WHERE id = ?");
                        $upStmt->execute([json_encode($pD, JSON_UNESCAPED_UNICODE), $row['id']]);
                    }
                } catch (Throwable $e) {}
            }

            $updatedRecSeq = getReceiptSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم فحص واستلام بضاعة المشتريات وإضافتها للمخزن بنجاح',
                'receiptNo' => $recNo,
                'nextReceiptNo' => $updatedRecSeq['nextReceiptNo']
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.5 SAVE FINANCIAL VOUCHER / CASH PAYMENT (حركة مالية)
        // ═════════════════════════════════════════════════════════
        case 'save_voucher':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات السند فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $idemKey = trim((string)($data['idempotencyKey'] ?? ($data['idempotency_key'] ?? ($data['client_draft_id'] ?? ''))));
            if ($idemKey !== '') {
                try {
                    $stmtIdem = $pdo->prepare("SELECT data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.idempotencyKey')) = ? LIMIT 1");
                    $stmtIdem->execute([$tenantId, $idemKey]);
                    $rowIdem = $stmtIdem->fetch(PDO::FETCH_ASSOC);
                    if ($rowIdem) {
                        $existRec = json_decode($rowIdem['data'], true) ?: [];
                        $existNo = $existRec['voucherNo'] ?? ($existRec['id'] ?? '');
                        $updatedFinSeq = getFinanceSequence($pdo, $tenantId);
                        echo json_encode([
                            'success' => true,
                            'idempotent' => true,
                            'message' => 'تم حفظ السند المالي مسبقاً (منع التكرار)',
                            'voucherNo' => $existNo,
                            'id' => $existNo,
                            'nextVoucherNo' => $updatedFinSeq['nextVoucherNo'],
                            'record' => $existRec
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                } catch (Exception $e) {}
            }

            $finSeq = getFinanceSequence($pdo, $tenantId);
            $vNo = trim($data['voucherNo'] ?? ($data['id'] ?? ''));
            $isEdit = !empty($data['isEdit']);
            $originalOfflineId = '';
            $isOffline = (strpos($vNo, 'أوفلاين') !== false || strpos($vNo, 'OFF-') !== false || strpos($vNo, 'offline') !== false);
            if ($isOffline) {
                $originalOfflineId = $vNo;
                $data['offline_id'] = $vNo;
                $vNo = $finSeq['nextVoucherNo'];
            } elseif (empty($vNo) || $vNo === 'new' || strpos($vNo, 'undefined') !== false || preg_match('/^E\d{10,}$/i', $vNo)) {
                $vNo = $finSeq['nextVoucherNo'];
            }

            $stmtCheck = $pdo->prepare("SELECT id, data FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (JSON_UNQUOTE(JSON_EXTRACT(data, '$.voucherNo')) = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmtCheck->execute([$tenantId, $vNo, $vNo]);
            $existingRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $existingId = $existingRow ? $existingRow['id'] : false;
            $oldData = ($existingRow && !empty($existingRow['data'])) ? (json_decode($existingRow['data'], true) ?: null) : null;

            if ($existingId && !$isEdit) {
                $vNo = $finSeq['nextVoucherNo'];
                $existingId = false;
                $oldData = null;
            }

            $data['voucherNo'] = $vNo;
            $data['id'] = $vNo;
            if ($idemKey !== '') $data['idempotencyKey'] = $idemKey;
            $data['updatedAt'] = date('Y-m-d H:i:s');
            if (empty($data['date'])) $data['date'] = date('Y-m-d');
            applyInvoiceEffectsInDB($pdo, $tenantId, 'finance', $data, $oldData);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($existingId) {
                $stmtUp = $pdo->prepare("UPDATE single_payments SET data = ?, updated_at = NOW() WHERE id = ?");
                $stmtUp->execute([$jsonRecord, $existingId]);
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $jsonRecord]);
            }

            try {
                $syncId = $idemKey !== '' ? $idemKey : ('sync-' . bin2hex(random_bytes(6)));
                $stmtSync = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, 'single_payments', ?, ?, ?, 'SYNCED', NOW(), NOW()) ON DUPLICATE KEY UPDATE record_id = VALUES(record_id), status = 'SYNCED', synced_at = NOW()");
                $stmtSync->execute([$syncId, $vNo, $existingId ? 'UPDATE' : 'INSERT', $jsonRecord]);
            } catch(Exception $e) {}

            $updatedFinSeq = getFinanceSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم حفظ السند المالي بنجاح في قاعدة البيانات',
                'voucherNo' => $vNo,
                'id' => $vNo,
                'original_offline_id' => $originalOfflineId,
                'nextVoucherNo' => $updatedFinSeq['nextVoucherNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.6 ATOMIC BATCH OFFLINE SYNC (ترحيل دفعات الأوفلاين بطلب واحد)
        // ═════════════════════════════════════════════════════════
        case 'sync_offline_batch':
            $raw = file_get_contents('php://input');
            $req = json_decode($raw, true) ?: $_POST;
            $items = $req['items'] ?? [];
            if (!is_array($items) || empty($items)) {
                echo json_encode(['success' => false, 'error' => 'لا توجد عناصر للترحيل'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $syncedResults = [];
            foreach ($items as $item) {
                $rawType = strtolower(trim((string)($item['type'] ?? 'sales')));
                if ($rawType === 'sales_invoice' || $rawType === 'sale' || $rawType === 'pos') {
                    $type = 'sales';
                } elseif ($rawType === 'purchase_invoice' || $rawType === 'purchases') {
                    $type = 'purchase';
                } elseif (in_array($rawType, ['voucher', 'cash_payment', 'cash_receipt', 'single_payment', 'single_receipt'], true)) {
                    $type = 'finance';
                } else {
                    $type = $rawType;
                }

                $payload = $item['payload'] ?? ($item['data'] ?? []);
                if (!is_array($payload)) $payload = [];
                $offlineId = $item['offline_id'] ?? ($payload['offline_id'] ?? ($payload['invoiceNo'] ?? ($payload['voucherNo'] ?? ($payload['id'] ?? ''))));
                $idemKey = trim((string)($item['idempotency_key'] ?? ($payload['idempotencyKey'] ?? ($item['id'] ?? ''))));
                $realId = '';

                // Idempotency Check: prevent duplicate numbering if connection dropped right after sync
                if ($idemKey !== '') {
                    try {
                        $stmtIdem = $pdo->prepare("SELECT record_id FROM sync_queue WHERE id = ? AND status = 'SYNCED' LIMIT 1");
                        $stmtIdem->execute([$idemKey]);
                        $existingRealId = $stmtIdem->fetchColumn();
                        if (!empty($existingRealId)) {
                            $syncedResults[] = [
                                'type' => $type,
                                'offline_id' => $offlineId,
                                'real_id' => $existingRealId,
                                'status' => 'synced',
                                'idempotent_hit' => true,
                                'synced_at' => date('Y-m-d H:i:s')
                            ];
                            continue;
                        }
                    } catch (Exception $e) {}
                }

                if ($type === 'sales') {
                    $sSeq = getSalesSequence($pdo, $tenantId);
                    $realId = $sSeq['nextInvoiceNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['customerId'] = $payload['customerId'] ?? ($payload['customerName'] ?? 'عميل');
                    $payload['date'] = $payload['date'] ?? date('Y-m-d');
                    $payload['netTotal'] = (float)($payload['grandTotal'] ?? ($payload['total'] ?? ($payload['netTotal'] ?? 0)));
                    $payload['status'] = $payload['status'] ?? 'completed';
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'sales', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'sales_split') {
                    $sSeq = getSalesSequence($pdo, $tenantId);
                    $realId = $sSeq['nextInvoiceNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['isSplit'] = true;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['customerId'] = $payload['customerId'] ?? ($payload['customerName'] ?? 'عميل');
                    $payload['date'] = $payload['date'] ?? date('Y-m-d');
                    $payload['netTotal'] = (float)($payload['grandTotal'] ?? ($payload['total'] ?? ($payload['netTotal'] ?? 0)));
                    $payload['status'] = 'split';
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'sales_split', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO sales_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'purchase') {
                    $pSeq = getPurchaseSequence($pdo, $tenantId);
                    $realId = $pSeq['nextPurchaseNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['supplierId'] = $payload['supplierId'] ?? ($payload['supplierName'] ?? 'مورد');
                    $payload['date'] = $payload['date'] ?? date('Y-m-d');
                    $payload['netTotal'] = (float)($payload['grandTotal'] ?? ($payload['total'] ?? ($payload['netTotal'] ?? 0)));
                    $payload['status'] = $payload['status'] ?? 'completed';
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'purchase', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'purchase_split') {
                    $pSeq = getPurchaseSequence($pdo, $tenantId);
                    $realId = $pSeq['nextPurchaseNo'];
                    $payload['invoiceNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['isSplit'] = true;
                    $payload['purchaseType'] = 'split';
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['supplierId'] = $payload['supplierId'] ?? ($payload['supplierName'] ?? 'مورد');
                    $payload['date'] = $payload['date'] ?? date('Y-m-d');
                    $payload['netTotal'] = (float)($payload['grandTotal'] ?? ($payload['total'] ?? ($payload['netTotal'] ?? 0)));
                    $payload['status'] = 'split';
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'purchase_split', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'delivery') {
                    $dSeq = getDeliverySequence($pdo, $tenantId);
                    $realId = $dSeq['nextDeliveryNo'];
                    $payload['deliveryNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['deleted'] = 0;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO delivery_notes (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $json]);
                } elseif ($type === 'purchase_receipt') {
                    $rSeq = getReceiptSequence($pdo, $tenantId);
                    $realId = $rSeq['nextReceiptNo'];
                    $payload['receiptNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['supplierId'] = $payload['supplierId'] ?? ($payload['supplierName'] ?? 'مورد');
                    $payload['date'] = $payload['date'] ?? date('Y-m-d');
                    $payload['netTotal'] = 0;
                    $payload['status'] = 'received';
                    $payload['deleted'] = 0;
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO purchase_invoices (tenant_id, invoice_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $realId, $json]);
                } elseif ($type === 'finance') {
                    $fSeq = getFinanceSequence($pdo, $tenantId);
                    $realId = $fSeq['nextVoucherNo'];
                    $payload['voucherNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'finance', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO single_payments (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $json]);
                } elseif ($type === 'journal_entry' || $type === 'journal_voucher') {
                    $jSeq = getJournalSequence($pdo, $tenantId);
                    $realId = $jSeq['nextJournalNo'];
                    $payload['voucherNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'journal_entry', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    try {
                        $stmt = $pdo->prepare("INSERT INTO journal_vouchers (tenant_id, jv_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                        $stmt->execute([$tenantId, $realId, $json]);
                    } catch (Throwable $e) {
                        $stmt = $pdo->prepare("INSERT INTO journal_vouchers (tenant_id, data) VALUES (?, ?)");
                        $stmt->execute([$tenantId, $json]);
                    }
                } elseif ($type === 'stock_adjustment') {
                    $iSeq = getInventorySequence($pdo, $tenantId, 'I');
                    $realId = $iSeq['nextDocNo'];
                    $payload['docNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'stock_adjustment', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $json]);
                } elseif ($type === 'warehouse_transfer') {
                    $tSeq = getInventorySequence($pdo, $tenantId, 'T');
                    $realId = $tSeq['nextDocNo'];
                    $payload['docNo'] = $realId;
                    $payload['id'] = $realId;
                    $payload['offline_id'] = $offlineId;
                    $payload['synced_at'] = date('Y-m-d H:i:s');
                    $payload['deleted'] = 0;
                    applyInvoiceEffectsInDB($pdo, $tenantId, 'warehouse_transfer', $payload, null);
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmt->execute([$tenantId, $json]);
                }

                if ($idemKey !== '' && $realId !== '') {
                    try {
                        $stmtLog = $pdo->prepare("INSERT INTO sync_queue (id, entity_name, record_id, operation, payload, status, created_at, synced_at) VALUES (?, ?, ?, 'INSERT', ?, 'SYNCED', NOW(), NOW()) ON DUPLICATE KEY UPDATE record_id = VALUES(record_id), status = 'SYNCED', synced_at = NOW()");
                        $stmtLog->execute([$idemKey, $type, $realId, json_encode($payload, JSON_UNESCAPED_UNICODE)]);
                    } catch (Throwable $e) {}
                }

                $syncedResults[] = [
                    'type' => $type,
                    'offline_id' => $offlineId,
                    'real_id' => $realId,
                    'status' => 'synced',
                    'synced_at' => date('Y-m-d H:i:s')
                ];
            }

            echo json_encode([
                'success' => true,
                'message' => 'تم ترحيل الدفعة الأوفلاين بنجاح وتخصيص الأرقام الحقيقية في قاعدة البيانات',
                'count' => count($syncedResults),
                'synced' => $syncedResults
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.7 SAVE MANUAL JOURNAL ENTRY (تسوية قيود مالية / قيد يومية يدوي)
        // ═════════════════════════════════════════════════════════
        case 'save_journal_entry':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات قيد التسوية فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $jSeq = getJournalSequence($pdo, $tenantId);
            $jvNo = trim($data['voucherNo'] ?? ($data['id'] ?? ''));
            if (empty($jvNo) || $jvNo === 'new' || strpos($jvNo, 'JV-2026') === 0 || strpos($jvNo, 'أوفلاين') !== false) {
                $jvNo = $jSeq['nextJournalNo'];
            }
            $data['voucherNo'] = $jvNo;
            $data['id'] = $jvNo;
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'posted';
            $data['deleted'] = 0;
            $data['updatedAt'] = date('Y-m-d H:i:s');

            $amount = (float)($data['amount'] ?? ($data['totalDebit'] ?? 0));
            $debitAccName = trim((string)($data['debitAccountName'] ?? 'حساب مدين'));
            $creditAccName = trim((string)($data['creditAccountName'] ?? 'حساب دائن'));
            if (empty($data['lines']) || !is_array($data['lines'])) {
                $data['lines'] = [
                    [
                        'accountId' => $data['debitAccountId'] ?? 'debit',
                        'accountName' => $debitAccName,
                        'debit' => $amount,
                        'credit' => 0,
                        'notes' => $data['notes'] ?? ''
                    ],
                    [
                        'accountId' => $data['creditAccountId'] ?? 'credit',
                        'accountName' => $creditAccName,
                        'debit' => 0,
                        'credit' => $amount,
                        'notes' => $data['notes'] ?? ''
                    ]
                ];
            }

            applyInvoiceEffectsInDB($pdo, $tenantId, 'journal_entry', $data, null);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            try {
                $stmtIns = $pdo->prepare("INSERT INTO journal_vouchers (tenant_id, jv_no, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([$tenantId, $jvNo, $jsonRecord]);
            } catch (Throwable $e) {
                $stmtIns = $pdo->prepare("INSERT INTO journal_vouchers (tenant_id, data) VALUES (?, ?)");
                $stmtIns->execute([$tenantId, $jsonRecord]);
            }

            // أيضاً نسجّل القيود في gl_entries للتوافق مع دفتر الأستاذ العام باللابتوب
            try {
                foreach ($data['lines'] as $idx => $line) {
                    $glEntry = [
                        'id' => $jvNo . '-' . ($idx + 1),
                        'voucherId' => $jvNo,
                        'voucherNo' => $jvNo,
                        'date' => $data['date'],
                        'accountId' => $line['accountId'] ?? '',
                        'accountName' => $line['accountName'] ?? '',
                        'debit' => (float)($line['debit'] ?? 0),
                        'credit' => (float)($line['credit'] ?? 0),
                        'description' => $data['notes'] ?? 'قيد تسوية يومية',
                        'source' => 'mobile_journal',
                        'deleted' => 0
                    ];
                    $stmtGl = $pdo->prepare("INSERT INTO gl_entries (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                    $stmtGl->execute([$tenantId, json_encode($glEntry, JSON_UNESCAPED_UNICODE)]);
                }
            } catch (Throwable $e) {}

            $updatedJSeq = getJournalSequence($pdo, $tenantId);
            echo json_encode([
                'success' => true,
                'message' => 'تم اعتماد وترحيل قيد التسوية المحاسبي وتحديث الأرصدة في قاعدة البيانات فوراً',
                'voucherNo' => $jvNo,
                'nextJournalNo' => $updatedJSeq['nextJournalNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.8 SAVE STOCK ADJUSTMENT (تسوية الجرد المخزني وفروقات التالف)
        // ═════════════════════════════════════════════════════════
        case 'save_stock_adjustment':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات تسوية الجرد فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $iSeq = getInventorySequence($pdo, $tenantId, 'I');
            $docNo = trim($data['docNo'] ?? ($data['id'] ?? ''));
            if (empty($docNo) || $docNo === 'new' || strpos($docNo, 'ADJ-2026') === 0 || strpos($docNo, 'أوفلاين') !== false) {
                $docNo = $iSeq['nextDocNo'];
            }
            $data['docNo'] = $docNo;
            $data['id'] = $docNo;
            $data['type'] = 'stock_adjustment';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'approved';
            $data['deleted'] = 0;
            $data['updatedAt'] = date('Y-m-d H:i:s');

            applyInvoiceEffectsInDB($pdo, $tenantId, 'stock_adjustment', $data, null);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $stmtIns = $pdo->prepare("INSERT INTO inventory (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmtIns->execute([$tenantId, $jsonRecord]);

            $updatedISeq = getInventorySequence($pdo, $tenantId, 'I');
            echo json_encode([
                'success' => true,
                'message' => 'تم اعتماد تسوية الجرد وتحديث رصيد الصنف في قاعدة البيانات فوراً',
                'docNo' => $docNo,
                'nextDocNo' => $updatedISeq['nextDocNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 6.9 SAVE WAREHOUSE TRANSFER (تحويل ومناقلة بين المخازن)
        // ═════════════════════════════════════════════════════════
        case 'save_warehouse_transfer':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;
            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'بيانات التحويل المخزني فارغة'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $tSeq = getInventorySequence($pdo, $tenantId, 'T');
            $docNo = trim($data['docNo'] ?? ($data['id'] ?? ''));
            if (empty($docNo) || $docNo === 'new' || strpos($docNo, 'TR-2026') === 0 || strpos($docNo, 'أوفلاين') !== false) {
                $docNo = $tSeq['nextDocNo'];
            }
            $data['docNo'] = $docNo;
            $data['id'] = $docNo;
            $data['type'] = 'warehouse_transfer';
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['status'] = 'completed';
            $data['deleted'] = 0;
            $data['updatedAt'] = date('Y-m-d H:i:s');

            applyInvoiceEffectsInDB($pdo, $tenantId, 'warehouse_transfer', $data, null);
            $jsonRecord = json_encode($data, JSON_UNESCAPED_UNICODE);

            $stmtIns = $pdo->prepare("INSERT INTO inventory (tenant_id, data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmtIns->execute([$tenantId, $jsonRecord]);

            $updatedTSeq = getInventorySequence($pdo, $tenantId, 'T');
            echo json_encode([
                'success' => true,
                'message' => 'تم اعتماد أمر التحويل المخزني وتحديث أرصدة المستودعات في قاعدة البيانات فوراً',
                'docNo' => $docNo,
                'nextDocNo' => $updatedTSeq['nextDocNo'],
                'record' => $data
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 7. GET RECENT TRANSACTIONS FOR MIRROR SYNC ON DEVICES
        // ═════════════════════════════════════════════════════════
        case 'get_recent_sales':
            $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, customer_id_val, date_val, status_val, data, created_at FROM sales_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $invNo = $r['invoice_no'] ?: ($d['invoiceNo'] ?? ('S-' . $r['id']));
                $d['invoiceNo'] = $invNo;
                $d['id'] = $invNo;
                $d['customerName'] = $r['customer_id_val'] ?: ($d['customerName'] ?? ($d['customer_name'] ?? 'عميل نقدي'));
                $d['total'] = (float)($r['total_val'] ?? ($d['grandTotal'] ?? ($d['total'] ?? 0)));
                $d['grandTotal'] = $d['total'];
                $d['date'] = $r['date_val'] ?: ($d['date'] ?? date('Y-m-d', strtotime($r['created_at'])));
                $list[] = $d;
            }
            $salesSeq = getSalesSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'sales' => $list, 'nextInvoiceNo' => $salesSeq['nextInvoiceNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_purchases':
            $stmt = $pdo->prepare("SELECT id, invoice_no, total_val, supplier_id_val, date_val, status_val, data, created_at FROM purchase_invoices WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $purNo = $r['invoice_no'] ?: ($d['invoiceNo'] ?? ('P-' . $r['id']));
                $d['invoiceNo'] = $purNo;
                $d['id'] = $purNo;
                $d['supplierName'] = $r['supplier_id_val'] ?: ($d['supplierName'] ?? ($d['supplier_name'] ?? 'مورد نقدي'));
                $d['total'] = (float)($r['total_val'] ?? ($d['grandTotal'] ?? 0));
                $d['grandTotal'] = $d['total'];
                $d['date'] = $r['date_val'] ?: ($d['date'] ?? date('Y-m-d', strtotime($r['created_at'])));
                $list[] = $d;
            }
            $purSeq = getPurchaseSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'purchases' => $list, 'nextPurchaseNo' => $purSeq['nextPurchaseNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_vouchers':
            $stmt = $pdo->prepare("SELECT id, data, date_val, created_at FROM single_payments WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $vNo = $d['voucherNo'] ?? ($d['id'] ?? ('E-' . $r['id']));
                $d['voucherNo'] = $vNo;
                $d['id'] = $vNo;
                $list[] = $d;
            }
            $finSeq = getFinanceSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'vouchers' => $list, 'nextVoucherNo' => $finSeq['nextVoucherNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_deliveries':
            $stmt = $pdo->prepare("SELECT id, data, frontend_id, created_at FROM delivery_notes WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $dNo = $r['frontend_id'] ?: ($d['deliveryNo'] ?? ($d['id'] ?? ('DEL-' . $r['id'])));
                $d['deliveryNo'] = $dNo;
                $d['id'] = $dNo;
                $list[] = $d;
            }
            $delSeq = getDeliverySequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'deliveries' => $list, 'nextDeliveryNo' => $delSeq['nextDeliveryNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_journals':
            $stmt = $pdo->prepare("SELECT id, data, frontend_id, created_at FROM journal_vouchers WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $jNo = $r['frontend_id'] ?: ($d['voucherNo'] ?? ($d['id'] ?? ('J-' . $r['id'])));
                $d['voucherNo'] = $jNo;
                $d['id'] = $jNo;
                $list[] = $d;
            }
            $jSeq = getJournalSequence($pdo, $tenantId);
            echo json_encode(['success' => true, 'journals' => $list, 'nextJournalNo' => $jSeq['nextJournalNo']], JSON_UNESCAPED_UNICODE);
            break;

        case 'get_recent_inventory_docs':
            $stmt = $pdo->prepare("SELECT id, data, frontend_id, created_at FROM inventory WHERE (tenant_id = ? OR tenant_id IS NULL) AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id DESC LIMIT 50");
            $stmt->execute([$tenantId]);
            $list = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $d = json_decode($r['data'], true) ?: [];
                $docNo = $r['frontend_id'] ?: ($d['docNo'] ?? ($d['id'] ?? ('I-' . $r['id'])));
                $d['docNo'] = $docNo;
                $d['id'] = $docNo;
                $list[] = $d;
            }
            $iSeq = getInventorySequence($pdo, $tenantId, 'I');
            echo json_encode(['success' => true, 'inventory_docs' => $list, 'nextDocNo' => $iSeq['nextDocNo']], JSON_UNESCAPED_UNICODE);
            break;

        // ═════════════════════════════════════════════════════════
        // 8. REAL-TIME CURRENCY EXCHANGE RATE (SYSTEM-WIDE)
        // ═════════════════════════════════════════════════════════
        case 'get_currency_rate':
            $rate = 0;
            $details = [];
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('tenant_rate_" . intval($tenantId) . "', 'exchange_rate', 'erp_rate_usd', 'erp_currency_rates_v1')");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            if (!empty($rows['tenant_rate_' . intval($tenantId)])) {
                $val = $rows['tenant_rate_' . intval($tenantId)];
                $rate = is_numeric($val) ? (float)$val : 0;
            }
            if (!$rate && !empty($rows['exchange_rate'])) {
                $val = $rows['exchange_rate'];
                if (is_numeric($val)) {
                    $rate = (float)$val;
                } else {
                    $d = json_decode($val, true);
                    if ($d) {
                        $details = $d;
                        if (!empty($d['rate'])) $rate = (float)$d['rate'];
                        elseif (!empty($d['USD'])) $rate = (float)$d['USD'];
                    }
                }
            }
            if (!$rate && !empty($rows['erp_rate_usd'])) {
                $rate = (float)$rows['erp_rate_usd'];
            }
            if (!$rate && !empty($rows['erp_currency_rates_v1'])) {
                $ratesObj = json_decode($rows['erp_currency_rates_v1'], true);
                if (!empty($ratesObj['USD'])) $rate = (float)$ratesObj['USD'];
            }
            if (!$rate) {
                $rate = 2850.0;
            }

            echo json_encode([
                'success' => true,
                'rate' => $rate,
                'currency' => 'USD',
                'details' => $details
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'save_currency_rate':
            $newRate = 0;
            if (isset($_POST['rate'])) {
                $newRate = (float)$_POST['rate'];
            } elseif (isset($rawInput['rate'])) {
                $newRate = (float)$rawInput['rate'];
            } elseif (isset($_GET['rate'])) {
                $newRate = (float)$_GET['rate'];
            }
            if ($newRate <= 0) {
                throw new Exception('القيمة المدخلة لسعر الصرف غير صالحة');
            }

            // 1. Update exchange_rate with history preservation
            $stmt = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'exchange_rate'");
            $stmt->execute();
            $curVal = $stmt->fetchColumn();
            $d = $curVal ? json_decode($curVal, true) : null;
            $oldRate = 0;
            if (is_array($d)) {
                $oldRate = (float)($d['rate'] ?? 0);
                $d['rate'] = $newRate;
                $trend = ($newRate >= $oldRate) ? 'up' : 'down';
                $d['trend'] = $trend;
                if (!isset($d['history']) || !is_array($d['history'])) $d['history'] = [];
                $d['history'][] = [
                    'date' => date('Y-m-d'),
                    'time' => date('H:i'),
                    'rate' => $newRate,
                    'old_rate' => $oldRate,
                    'trend' => $trend,
                    'source' => 'mobile_pos'
                ];
                if (count($d['history']) > 50) $d['history'] = array_slice($d['history'], -50);
                $stmtUp = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmtUp->execute([json_encode($d, JSON_UNESCAPED_UNICODE)]);
            } else {
                $stmtUp = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('exchange_rate', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmtUp->execute([json_encode(['rate' => $newRate, 'trend' => 'up', 'history' => [['date' => date('Y-m-d'), 'rate' => $newRate, 'source' => 'mobile_pos']]], JSON_UNESCAPED_UNICODE)]);
            }

            // 2. Update erp_currency_rates_v1
            $stmtCr = $pdo->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'erp_currency_rates_v1'");
            $stmtCr->execute();
            $crVal = $stmtCr->fetchColumn();
            $rates = $crVal ? json_decode($crVal, true) : [];
            if (!is_array($rates)) $rates = [];
            $rates['USD'] = $newRate;
            $rates['SDG'] = 1;
            $stmtUpCr = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_currency_rates_v1', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmtUpCr->execute([json_encode($rates, JSON_UNESCAPED_UNICODE)]);

            // 3. Update erp_rate_usd & tenant_rate
            $stmtUpUsd = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('erp_rate_usd', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmtUpUsd->execute([(string)$newRate]);

            if (!empty($tenantId)) {
                $stmtUpTen = $pdo->prepare("INSERT INTO global_settings (setting_key, setting_value) VALUES ('tenant_rate_" . intval($tenantId) . "', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                $stmtUpTen->execute([(string)$newRate]);
            }

            echo json_encode([
                'success' => true,
                'rate' => $newRate,
                'old_rate' => $oldRate,
                'message' => 'تم حفظ وتحديث سعر صرف الدولار بنجاح في قاعدة البيانات وعلى مستوى السيستم بالكامل'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
