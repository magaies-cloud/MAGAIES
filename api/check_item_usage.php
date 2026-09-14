<?php
require_once __DIR__ . '/db.php';

if (!function_exists('getItemUsageDetails')) {
    function getItemUsageDetails($pdo, $tenant_id, $itemId, $itemName = '', $itemBarcodes = []) {
        $usages = [];

        // Find the item in database to gather all synonyms, IDs, names, and barcodes
        $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, search_barcode, data FROM items WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
        $stmt->execute([$itemId, $itemId, $itemId]);
        $itemRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $idsToMatch = array_filter([(string)$itemId]);
        $namesToMatch = [];
        $barcodesToMatch = [];

        if ($itemName) {
            $namesToMatch[] = trim(mb_strtolower((string)$itemName));
        }
        if (!empty($itemBarcodes)) {
            foreach ((array)$itemBarcodes as $bc) {
                if ($bc) $barcodesToMatch[] = trim((string)$bc);
            }
        }

        if ($itemRow) {
            if (!empty($itemRow['frontend_id'])) $idsToMatch[] = (string)$itemRow['frontend_id'];
            if (!empty($itemRow['id'])) $idsToMatch[] = (string)$itemRow['id'];
            if (!empty($itemRow['search_name'])) $namesToMatch[] = trim(mb_strtolower($itemRow['search_name']));
            if (!empty($itemRow['search_barcode'])) $barcodesToMatch[] = trim((string)$itemRow['search_barcode']);

            if (!empty($itemRow['data'])) {
                $d = json_decode($itemRow['data'], true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['barcode'])) $barcodesToMatch[] = trim((string)$d['barcode']);
                    if (!empty($d['units']) && is_array($d['units'])) {
                        foreach ($d['units'] as $u) {
                            if (!empty($u['barcode'])) $barcodesToMatch[] = trim((string)$u['barcode']);
                        }
                    }
                }
            }
        }

        $idsToMatch = array_values(array_unique(array_filter($idsToMatch)));
        $namesToMatch = array_values(array_unique(array_filter($namesToMatch)));
        $barcodesToMatch = array_values(array_unique(array_filter($barcodesToMatch)));

        $matchesItem = function($line) use ($idsToMatch, $namesToMatch, $barcodesToMatch) {
            if (!is_array($line)) return false;
            $lId = (string)($line['itemId'] ?? $line['id'] ?? $line['item_id'] ?? '');
            $lName = trim(mb_strtolower((string)($line['itemName'] ?? $line['name'] ?? $line['item_name'] ?? '')));
            $lBc = trim((string)($line['barcode'] ?? $line['qrCode'] ?? ''));

            if ($lId && in_array($lId, $idsToMatch, true)) return true;
            if ($lName && in_array($lName, $namesToMatch, true)) return true;
            if ($lBc && in_array($lBc, $barcodesToMatch, true)) return true;
            return false;
        };

        // 1. Sales Invoices (فواتير وسندات المبيعات)
        $sStmt = $pdo->query("SELECT id, invoice_no, date_val, total_val, data FROM sales_invoices WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $sStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if ($inv && isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $line) {
                    if ($matchesItem($line)) {
                        $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $inv['id'] ?? $row['id']);
                        $date = $row['date_val'] ?: ($inv['date'] ?? '');
                        $usages[] = [
                            'type' => 'فاتورة مبيعات',
                            'doc_no' => (string)$invNo,
                            'date' => $date,
                            'label' => "فاتورة مبيعات رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                        ];
                        break;
                    }
                }
            }
        }

        // 2. Purchase Invoices (فواتير وسندات المشتريات)
        $pStmt = $pdo->query("SELECT id, invoice_no, date_val, total_val, data FROM purchase_invoices WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $pStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if ($inv && isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $line) {
                    if ($matchesItem($line)) {
                        $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $inv['id'] ?? $row['id']);
                        $date = $row['date_val'] ?: ($inv['date'] ?? '');
                        $usages[] = [
                            'type' => 'فاتورة مشتريات',
                            'doc_no' => (string)$invNo,
                            'date' => $date,
                            'label' => "فاتورة مشتريات رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                        ];
                        break;
                    }
                }
            }
        }

        // 3. Direct Purchases (المشتريات المباشرة)
        $dpStmt = $pdo->query("SELECT id, invoice_no, date_val, total_val, data FROM direct_purchases WHERE data IS NOT NULL");
        while ($row = $dpStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if ($inv && isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $line) {
                    if ($matchesItem($line)) {
                        $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $row['id']);
                        $date = $row['date_val'] ?: ($inv['date'] ?? '');
                        $usages[] = [
                            'type' => 'سند مشتريات مباشرة',
                            'doc_no' => (string)$invNo,
                            'date' => $date,
                            'label' => "سند مشتريات مباشرة رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                        ];
                        break;
                    }
                }
            }
        }

        // 4. Delivery Notes (سندات وأذونات التسليم والصرف)
        $dnStmt = $pdo->query("SELECT id, frontend_id, data FROM delivery_notes WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $dnStmt->fetch(PDO::FETCH_ASSOC)) {
            $dn = json_decode($row['data'], true);
            if ($dn && isset($dn['items']) && is_array($dn['items'])) {
                foreach ($dn['items'] as $line) {
                    if ($matchesItem($line)) {
                        $docNo = $dn['noteNo'] ?? $dn['id'] ?? $row['frontend_id'] ?? $row['id'];
                        $date = $dn['date'] ?? '';
                        $usages[] = [
                            'type' => 'سند تسليم / إذن صرف',
                            'doc_no' => (string)$docNo,
                            'date' => $date,
                            'label' => "سند تسليم رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                        ];
                        break;
                    }
                }
            }
        }

        // 5. Reserved Sales (سندات حجز المبيعات)
        $rsStmt = $pdo->query("SELECT id, frontend_id, data FROM reserved_sales WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $rsStmt->fetch(PDO::FETCH_ASSOC)) {
            $rs = json_decode($row['data'], true);
            if ($rs && isset($rs['items']) && is_array($rs['items'])) {
                foreach ($rs['items'] as $line) {
                    if ($matchesItem($line)) {
                        $docNo = $rs['orderNo'] ?? $rs['id'] ?? $row['frontend_id'] ?? $row['id'];
                        $date = $rs['date'] ?? '';
                        $usages[] = [
                            'type' => 'سند حجز مبيعات',
                            'doc_no' => (string)$docNo,
                            'date' => $date,
                            'label' => "سند حجز مبيعات رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                        ];
                        break;
                    }
                }
            }
        }

        // 6. Inventory Records (حركات وجرد وتسويات المخزون)
        $inStmt = $pdo->query("SELECT id, frontend_id, data FROM inventory WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $inStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if ($inv) {
                $lines = $inv['items'] ?? $inv['lines'] ?? [];
                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        if ($matchesItem($line)) {
                            $docNo = $inv['docNo'] ?? $inv['id'] ?? $row['frontend_id'] ?? $row['id'];
                            $date = $inv['date'] ?? '';
                            $usages[] = [
                                'type' => 'سند تسوية / جرد مخزني',
                                'doc_no' => (string)$docNo,
                                'date' => $date,
                                'label' => "سند تسوية مخزنية رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                            ];
                            break;
                        }
                    }
                }
            }
        }

        // 7. Transactions table (جدول الحركات)
        if (!empty($idsToMatch)) {
            $placeholders = implode(',', array_fill(0, count($idsToMatch), '?'));
            $trStmt = $pdo->prepare("SELECT id, item_id, transaction_date FROM transactions WHERE item_id IN ($placeholders)");
            try {
                $trStmt->execute($idsToMatch);
                while ($row = $trStmt->fetch(PDO::FETCH_ASSOC)) {
                    $usages[] = [
                        'type' => 'حركة معاملات',
                        'doc_no' => (string)$row['id'],
                        'date' => $row['transaction_date'] ?? '',
                        'label' => "حركة معاملات رقم " . $row['id'] . (!empty($row['transaction_date']) ? " بتاريخ " . $row['transaction_date'] : "")
                    ];
                }
            } catch(Exception $e) {}
        }

        return [
            'is_used' => count($usages) > 0,
            'can_delete' => count($usages) === 0,
            'usage_count' => count($usages),
            'usages' => $usages
        ];
    }
}

// If accessed directly as API endpoint
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'check_item_usage.php') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $tenant_id = 1;
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    if (!empty($token)) {
        $stmtAuth = $pdo->prepare('SELECT tenant_id FROM users WHERE token = ? LIMIT 1');
        $stmtAuth->execute([$token]);
        $t = $stmtAuth->fetchColumn();
        if ($t) { $tenant_id = (int)$t; }
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_GET;
    }

    $itemId = $input['item_id'] ?? $input['id'] ?? null;
    $itemName = $input['item_name'] ?? $input['name'] ?? null;
    $itemBarcodes = $input['barcodes'] ?? $input['barcode'] ?? [];

    if (!$itemId && !$itemName) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Item ID or name is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $usageResult = getItemUsageDetails($pdo, $tenant_id, $itemId, $itemName, $itemBarcodes);
        echo json_encode([
            'success' => true,
            'item_id' => $itemId,
            'item_name' => $itemName,
            'is_used' => $usageResult['is_used'],
            'can_delete' => $usageResult['can_delete'],
            'usage_count' => $usageResult['usage_count'],
            'usages' => $usageResult['usages'],
            'message' => $usageResult['is_used']
                ? 'هذا الصنف مستخدم في سندات مسجلة بالنظام ولا يمكن حذفه نهائياً.'
                : 'هذا الصنف غير مستخدم في أي سند أو حركة، ويمكن حذفه نهائياً من قاعدة البيانات.'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
