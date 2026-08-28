<?php
require_once __DIR__ . '/db.php';

if (!function_exists('checkEntityUsageInDatabase')) {
    function checkEntityUsageInDatabase($pdo, $tenant_id, $entityType, $entityId, $entityName = '', $extraData = []) {
        $usages = [];

        // Prepare matching terms
        $idsToMatch = array_filter([(string)$entityId]);
        $namesToMatch = [];
        $extraToMatch = [];

        if ($entityName) {
            $namesToMatch[] = trim(mb_strtolower((string)$entityName));
        }

        // Entity-specific term gathering from DB tables
        if ($entityType === 'warehouse') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, data FROM warehouses WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                }
            }
        } else if ($entityType === 'customer') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, search_phone, data FROM customers WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                if (!empty($row['search_name'])) $namesToMatch[] = trim(mb_strtolower($row['search_name']));
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['accountNo'])) $extraToMatch[] = (string)$d['accountNo'];
                }
            }
        } else if ($entityType === 'supplier') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, search_phone, data FROM suppliers WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                if (!empty($row['search_name'])) $namesToMatch[] = trim(mb_strtolower($row['search_name']));
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['accountNo'])) $extraToMatch[] = (string)$d['accountNo'];
                }
            }
        } else if ($entityType === 'bank') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, data FROM banks WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['bankName'])) $namesToMatch[] = trim(mb_strtolower($d['bankName']));
                    if (!empty($d['accountNumber'])) $extraToMatch[] = (string)$d['accountNumber'];
                }
            }
        } else if ($entityType === 'safe') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, data FROM safes WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['safeName'])) $namesToMatch[] = trim(mb_strtolower($d['safeName']));
                }
            }
        } else if ($entityType === 'employee' || $entityType === 'user') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, username, data FROM users WHERE (frontend_id = ? OR id = ? OR username = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                if (!empty($row['username'])) $extraToMatch[] = (string)$row['username'];
                if (!empty($row['search_name'])) $namesToMatch[] = trim(mb_strtolower($row['search_name']));
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                }
            }
        } else if ($entityType === 'item') {
            $stmt = $pdo->prepare("SELECT id, frontend_id, search_name, search_barcode, data FROM items WHERE (frontend_id = ? OR id = ? OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.id')) = ?) LIMIT 1");
            $stmt->execute([$entityId, $entityId, $entityId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['id'])) $idsToMatch[] = (string)$row['id'];
                if (!empty($row['frontend_id'])) $idsToMatch[] = (string)$row['frontend_id'];
                if (!empty($row['search_name'])) $namesToMatch[] = trim(mb_strtolower($row['search_name']));
                if (!empty($row['search_barcode'])) $extraToMatch[] = (string)$row['search_barcode'];
                $d = json_decode($row['data'] ?? '', true);
                if ($d) {
                    if (!empty($d['id'])) $idsToMatch[] = (string)$d['id'];
                    if (!empty($d['name'])) $namesToMatch[] = trim(mb_strtolower($d['name']));
                    if (!empty($d['barcode'])) $extraToMatch[] = (string)$d['barcode'];
                    if (!empty($d['units']) && is_array($d['units'])) {
                        foreach ($d['units'] as $u) {
                            if (!empty($u['barcode'])) $extraToMatch[] = (string)$u['barcode'];
                        }
                    }
                }
            }
        }

        $idsToMatch = array_values(array_unique(array_filter($idsToMatch)));
        $namesToMatch = array_values(array_unique(array_filter($namesToMatch)));
        $extraToMatch = array_values(array_unique(array_filter($extraToMatch)));

        $checkMatch = function($val) use ($idsToMatch, $namesToMatch, $extraToMatch) {
            if ($val === null || $val === '') return false;
            $strVal = (string)$val;
            $strLower = trim(mb_strtolower($strVal));
            
            // Check direct id or name match
            if (in_array($strVal, $idsToMatch, true)) return true;
            if (in_array($strLower, $namesToMatch, true)) return true;
            if (in_array($strVal, $extraToMatch, true)) return true;

            // Check prefixed values like cust-CUST-... or supp-SUP-... or wh-WH-... or safe-001
            foreach ($idsToMatch as $id) {
                if ($strVal === 'cust-' . $id || $strVal === 'supp-' . $id || $strVal === 'wh-' . $id || $strVal === 'bank-' . $id || $strVal === 'safe-' . $id) {
                    return true;
                }
            }
            return false;
        };

        // 1. Check Sales Invoices (sales_invoices)
        $sStmt = $pdo->query("SELECT id, invoice_no, date_val, data FROM sales_invoices WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $sStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $inv['id'] ?? $row['id']);
            $date = $row['date_val'] ?: ($inv['date'] ?? '');
            $matched = false;

            if ($entityType === 'warehouse') {
                if ($checkMatch($inv['warehouse'] ?? '') || $checkMatch($inv['warehouseId'] ?? '') || $checkMatch($inv['deliveryWarehouse'] ?? '')) {
                    $matched = true;
                } else if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['warehouseId'] ?? '') || $checkMatch($it['warehouse'] ?? '')) { $matched = true; break; }
                    }
                }
            } else if ($entityType === 'customer') {
                if ($checkMatch($inv['customer'] ?? '') || $checkMatch($inv['customerId'] ?? '') || $checkMatch($inv['customerName'] ?? '') || $checkMatch($inv['client'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'bank') {
                if ($checkMatch($inv['paymentAccountId'] ?? '') || $checkMatch($inv['bankId'] ?? '') || $checkMatch($inv['paymentAccount'] ?? '') || $checkMatch($inv['bank'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'safe') {
                if ($checkMatch($inv['safe'] ?? '') || $checkMatch($inv['safeId'] ?? '') || $checkMatch($inv['paymentAccountId'] ?? '') || $checkMatch($inv['cashBox'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'item') {
                if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '') || $checkMatch($it['barcode'] ?? '') || $checkMatch($it['qrCode'] ?? '')) {
                            $matched = true; break;
                        }
                    }
                }
            } else if ($entityType === 'unit') {
                if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['selectedUnit'] ?? '') || $checkMatch($it['unit'] ?? '')) { $matched = true; break; }
                    }
                }
            } else if ($entityType === 'cost_center') {
                if ($checkMatch($inv['costCenter'] ?? '') || $checkMatch($inv['costCenterId'] ?? '')) {
                    $matched = true;
                } else if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['costCenterId'] ?? '') || $checkMatch($it['costCenter'] ?? '')) { $matched = true; break; }
                    }
                }
            } else if ($entityType === 'employee' || $entityType === 'user') {
                if ($checkMatch($inv['createdBy'] ?? '') || $checkMatch($inv['salesRep'] ?? '') || $checkMatch($inv['salesman'] ?? '') || $checkMatch($inv['driver'] ?? '') || $checkMatch($inv['user'] ?? '')) {
                    $matched = true;
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'فاتورة مبيعات',
                    'doc_no' => (string)$invNo,
                    'date' => $date,
                    'label' => "فاتورة مبيعات رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 2. Check Purchase Invoices (purchase_invoices)
        $pStmt = $pdo->query("SELECT id, invoice_no, date_val, data FROM purchase_invoices WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $pStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $inv['id'] ?? $row['id']);
            $date = $row['date_val'] ?: ($inv['date'] ?? '');
            $matched = false;

            if ($entityType === 'warehouse') {
                if ($checkMatch($inv['warehouse'] ?? '') || $checkMatch($inv['warehouseId'] ?? '')) {
                    $matched = true;
                } else if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['warehouseId'] ?? '') || $checkMatch($it['warehouse'] ?? '')) { $matched = true; break; }
                    }
                }
            } else if ($entityType === 'supplier') {
                if ($checkMatch($inv['supplier'] ?? '') || $checkMatch($inv['supplierId'] ?? '') || $checkMatch($inv['supplierName'] ?? '') || $checkMatch($inv['customer'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'bank') {
                if ($checkMatch($inv['paymentAccountId'] ?? '') || $checkMatch($inv['bankId'] ?? '') || $checkMatch($inv['paymentAccount'] ?? '') || $checkMatch($inv['bank'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'safe') {
                if ($checkMatch($inv['safe'] ?? '') || $checkMatch($inv['safeId'] ?? '') || $checkMatch($inv['paymentAccountId'] ?? '') || $checkMatch($inv['cashBox'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'item') {
                if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '') || $checkMatch($it['barcode'] ?? '') || $checkMatch($it['qrCode'] ?? '')) {
                            $matched = true; break;
                        }
                    }
                }
            } else if ($entityType === 'unit') {
                if (!empty($inv['items']) && is_array($inv['items'])) {
                    foreach ($inv['items'] as $it) {
                        if ($checkMatch($it['selectedUnit'] ?? '') || $checkMatch($it['unit'] ?? '')) { $matched = true; break; }
                    }
                }
            } else if ($entityType === 'cost_center') {
                if ($checkMatch($inv['costCenter'] ?? '') || $checkMatch($inv['costCenterId'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'employee' || $entityType === 'user') {
                if ($checkMatch($inv['createdBy'] ?? '') || $checkMatch($inv['user'] ?? '')) {
                    $matched = true;
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'فاتورة مشتريات',
                    'doc_no' => (string)$invNo,
                    'date' => $date,
                    'label' => "فاتورة مشتريات رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 3. Check Direct Purchases (direct_purchases)
        $dpStmt = $pdo->query("SELECT id, invoice_no, date_val, data FROM direct_purchases WHERE data IS NOT NULL");
        while ($row = $dpStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            $invNo = $row['invoice_no'] ?: ($inv['invoiceNo'] ?? $row['id']);
            $date = $row['date_val'] ?: ($inv['date'] ?? '');
            $matched = false;

            if ($entityType === 'warehouse' && $checkMatch($inv['warehouse'] ?? '')) $matched = true;
            if ($entityType === 'supplier' && ($checkMatch($inv['supplier'] ?? '') || $checkMatch($inv['supplierId'] ?? '') || $checkMatch($inv['supplierName'] ?? ''))) $matched = true;
            if ($entityType === 'item' && !empty($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $it) {
                    if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '')) { $matched = true; break; }
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند مشتريات مباشرة',
                    'doc_no' => (string)$invNo,
                    'date' => $date,
                    'label' => "سند مشتريات مباشرة رقم " . $invNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 4. Check Single Payments & Payments (سندات الصرف والمدفوعات)
        $spStmt = $pdo->query("SELECT id, date_val, data FROM single_payments WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $spStmt->fetch(PDO::FETCH_ASSOC)) {
            $pay = json_decode($row['data'], true);
            if (!$pay) continue;
            $docNo = $pay['id'] ?? $pay['voucherNo'] ?? $row['id'];
            $date = $pay['date'] ?? $row['date_val'] ?? '';
            $matched = false;

            if ($entityType === 'customer' || $entityType === 'supplier' || $entityType === 'bank' || $entityType === 'safe') {
                if ($checkMatch($pay['fromAccount'] ?? '') || $checkMatch($pay['toAccount'] ?? '') || $checkMatch($pay['accountId'] ?? '') || $checkMatch($pay['partyId'] ?? '') || $checkMatch($pay['bank'] ?? '') || $checkMatch($pay['safe'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'cost_center' && ($checkMatch($pay['costCenter'] ?? '') || $checkMatch($pay['costCenterId'] ?? ''))) {
                $matched = true;
            } else if (($entityType === 'employee' || $entityType === 'user') && ($checkMatch($pay['createdBy'] ?? '') || $checkMatch($pay['paidBy'] ?? '') || $checkMatch($pay['user'] ?? ''))) {
                $matched = true;
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند صرف',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند صرف رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        $pPayStmt = $pdo->query("SELECT id, date_val, data FROM payments WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $pPayStmt->fetch(PDO::FETCH_ASSOC)) {
            $pay = json_decode($row['data'], true);
            if (!$pay) continue;
            $docNo = $pay['id'] ?? $pay['voucherNo'] ?? $row['id'];
            $date = $pay['date'] ?? $row['date_val'] ?? '';
            $matched = false;

            if ($entityType === 'customer' || $entityType === 'supplier' || $entityType === 'bank' || $entityType === 'safe') {
                if ($checkMatch($pay['fromAccount'] ?? '') || $checkMatch($pay['toAccount'] ?? '') || $checkMatch($pay['accountId'] ?? '') || $checkMatch($pay['partyId'] ?? '') || $checkMatch($pay['bank'] ?? '') || $checkMatch($pay['safe'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'cost_center' && ($checkMatch($pay['costCenter'] ?? '') || $checkMatch($pay['costCenterId'] ?? ''))) {
                $matched = true;
            } else if (($entityType === 'employee' || $entityType === 'user') && ($checkMatch($pay['createdBy'] ?? '') || $checkMatch($pay['paidBy'] ?? ''))) {
                $matched = true;
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند صرف شامل',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند صرف شامل رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 5. Check Single Receipts & Receipts (سندات القبض والمقبوضات)
        $srStmt = $pdo->query("SELECT id, date_val, data FROM single_receipts WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $srStmt->fetch(PDO::FETCH_ASSOC)) {
            $rec = json_decode($row['data'], true);
            if (!$rec) continue;
            $docNo = $rec['id'] ?? $rec['voucherNo'] ?? $row['id'];
            $date = $rec['date'] ?? $row['date_val'] ?? '';
            $matched = false;

            if ($entityType === 'customer' || $entityType === 'supplier' || $entityType === 'bank' || $entityType === 'safe') {
                if ($checkMatch($rec['fromAccount'] ?? '') || $checkMatch($rec['toAccount'] ?? '') || $checkMatch($rec['accountId'] ?? '') || $checkMatch($rec['partyId'] ?? '') || $checkMatch($rec['bank'] ?? '') || $checkMatch($rec['safe'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'cost_center' && ($checkMatch($rec['costCenter'] ?? '') || $checkMatch($rec['costCenterId'] ?? ''))) {
                $matched = true;
            } else if (($entityType === 'employee' || $entityType === 'user') && ($checkMatch($rec['createdBy'] ?? '') || $checkMatch($rec['receivedBy'] ?? '') || $checkMatch($rec['collectorName'] ?? ''))) {
                $matched = true;
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند قبض',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند قبض رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        $rRecStmt = $pdo->query("SELECT id, date_val, data FROM receipts WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $rRecStmt->fetch(PDO::FETCH_ASSOC)) {
            $rec = json_decode($row['data'], true);
            if (!$rec) continue;
            $docNo = $rec['id'] ?? $rec['voucherNo'] ?? $row['id'];
            $date = $rec['date'] ?? $row['date_val'] ?? '';
            $matched = false;

            if ($entityType === 'customer' || $entityType === 'supplier' || $entityType === 'bank' || $entityType === 'safe') {
                if ($checkMatch($rec['fromAccount'] ?? '') || $checkMatch($rec['toAccount'] ?? '') || $checkMatch($rec['accountId'] ?? '') || $checkMatch($rec['partyId'] ?? '') || $checkMatch($rec['bank'] ?? '') || $checkMatch($rec['safe'] ?? '')) {
                    $matched = true;
                }
            } else if ($entityType === 'cost_center' && ($checkMatch($rec['costCenter'] ?? '') || $checkMatch($rec['costCenterId'] ?? ''))) {
                $matched = true;
            } else if (($entityType === 'employee' || $entityType === 'user') && ($checkMatch($rec['createdBy'] ?? '') || $checkMatch($rec['receivedBy'] ?? ''))) {
                $matched = true;
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند قبض شامل',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند قبض شامل رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 6. Check Delivery Notes (delivery_notes)
        $dnStmt = $pdo->query("SELECT id, frontend_id, data FROM delivery_notes WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $dnStmt->fetch(PDO::FETCH_ASSOC)) {
            $dn = json_decode($row['data'], true);
            if (!$dn) continue;
            $docNo = $dn['noteNo'] ?? $dn['id'] ?? $row['frontend_id'] ?? $row['id'];
            $date = $dn['date'] ?? '';
            $matched = false;

            if ($entityType === 'warehouse' && ($checkMatch($dn['warehouse'] ?? '') || $checkMatch($dn['warehouseId'] ?? '') || $checkMatch($dn['targetWarehouse'] ?? ''))) $matched = true;
            if ($entityType === 'customer' && ($checkMatch($dn['customer'] ?? '') || $checkMatch($dn['customerId'] ?? '') || $checkMatch($dn['customerName'] ?? ''))) $matched = true;
            if ($entityType === 'employee' && ($checkMatch($dn['driver'] ?? '') || $checkMatch($dn['deliveredBy'] ?? '') || $checkMatch($dn['receivedBy'] ?? '') || $checkMatch($dn['createdBy'] ?? ''))) $matched = true;
            if ($entityType === 'item' && !empty($dn['items']) && is_array($dn['items'])) {
                foreach ($dn['items'] as $it) {
                    if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '')) { $matched = true; break; }
                }
            }
            if ($entityType === 'unit' && !empty($dn['items']) && is_array($dn['items'])) {
                foreach ($dn['items'] as $it) {
                    if ($checkMatch($it['selectedUnit'] ?? '') || $checkMatch($it['unit'] ?? '')) { $matched = true; break; }
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند تسليم / إذن صرف',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند تسليم رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 7. Check Reserved Sales (reserved_sales)
        $rsStmt = $pdo->query("SELECT id, frontend_id, data FROM reserved_sales WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $rsStmt->fetch(PDO::FETCH_ASSOC)) {
            $rs = json_decode($row['data'], true);
            if (!$rs) continue;
            $docNo = $rs['orderNo'] ?? $rs['id'] ?? $row['frontend_id'] ?? $row['id'];
            $date = $rs['date'] ?? '';
            $matched = false;

            if ($entityType === 'warehouse' && ($checkMatch($rs['warehouse'] ?? '') || $checkMatch($rs['warehouseId'] ?? ''))) $matched = true;
            if ($entityType === 'customer' && ($checkMatch($rs['customer'] ?? '') || $checkMatch($rs['customerId'] ?? '') || $checkMatch($rs['customerName'] ?? ''))) $matched = true;
            if ($entityType === 'item' && !empty($rs['items']) && is_array($rs['items'])) {
                foreach ($rs['items'] as $it) {
                    if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '')) { $matched = true; break; }
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند حجز مبيعات',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند حجز مبيعات رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 8. Check Inventory Records (inventory)
        $inStmt = $pdo->query("SELECT id, frontend_id, data FROM inventory WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $inStmt->fetch(PDO::FETCH_ASSOC)) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            $docNo = $inv['docNo'] ?? $inv['id'] ?? $row['frontend_id'] ?? $row['id'];
            $date = $inv['date'] ?? '';
            $matched = false;

            if ($entityType === 'warehouse' && ($checkMatch($inv['warehouse'] ?? '') || $checkMatch($inv['warehouseId'] ?? '') || $checkMatch($inv['warehouseName'] ?? ''))) $matched = true;
            if ($entityType === 'item') {
                $lines = $inv['items'] ?? $inv['lines'] ?? [];
                if (is_array($lines)) {
                    foreach ($lines as $it) {
                        if ($checkMatch($it['itemId'] ?? '') || $checkMatch($it['id'] ?? '') || $checkMatch($it['itemName'] ?? '') || $checkMatch($it['name'] ?? '')) { $matched = true; break; }
                    }
                }
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'سند تسوية / جرد مخزني',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "سند تسوية مخزنية رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        // 9. Check Items table for Unit & Category usage
        if ($entityType === 'unit') {
            $stmtItems = $pdo->query("SELECT id, search_name, data FROM items WHERE is_deleted = 0 OR is_deleted IS NULL");
            while ($row = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
                $itemData = json_decode($row['data'], true);
                if (!$itemData) continue;
                $itName = $row['search_name'] ?: ($itemData['name'] ?? 'صنف');
                $matched = false;

                if ($checkMatch($itemData['baseUnit'] ?? '') || $checkMatch($itemData['unit'] ?? '')) $matched = true;
                if (!empty($itemData['units']) && is_array($itemData['units'])) {
                    foreach ($itemData['units'] as $u) {
                        if ($checkMatch($u['unitName'] ?? '')) { $matched = true; break; }
                    }
                }
                if (!empty($itemData['unitsArray']) && is_array($itemData['unitsArray'])) {
                    foreach ($itemData['unitsArray'] as $u) {
                        if ($checkMatch($u['name'] ?? '') || $checkMatch($u['unitName'] ?? '')) { $matched = true; break; }
                    }
                }

                if ($matched) {
                    $usages[] = [
                        'type' => 'بطاقة صنف',
                        'doc_no' => (string)$row['id'],
                        'date' => '',
                        'label' => "مستخدم كوحدة في بطاقة صنف: " . $itName
                    ];
                }
            }
        } else if ($entityType === 'category') {
            $stmtItems = $pdo->query("SELECT id, search_name, category_val, data FROM items WHERE is_deleted = 0 OR is_deleted IS NULL");
            while ($row = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
                $itemData = json_decode($row['data'], true);
                $itName = $row['search_name'] ?: ($itemData['name'] ?? 'صنف');
                $catVal = $row['category_val'] ?: ($itemData['category'] ?? '');
                if ($checkMatch($catVal) || $checkMatch($itemData['subCategory'] ?? '')) {
                    $usages[] = [
                        'type' => 'بطاقة صنف',
                        'doc_no' => (string)$row['id'],
                        'date' => '',
                        'label' => "مستخدم كفئة للصنف: " . $itName
                    ];
                }
            }
        }

        // 10. Check Journal Vouchers & GL Entries
        $jvStmt = $pdo->query("SELECT id, frontend_id, date_val, data FROM journal_vouchers WHERE data IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)");
        while ($row = $jvStmt->fetch(PDO::FETCH_ASSOC)) {
            $jv = json_decode($row['data'], true);
            if (!$jv) continue;
            $docNo = $jv['voucherNo'] ?? $jv['id'] ?? $row['frontend_id'] ?? $row['id'];
            $date = $row['date_val'] ?: ($jv['date'] ?? '');
            $matched = false;

            if ($entityType === 'customer' || $entityType === 'supplier' || $entityType === 'bank' || $entityType === 'safe') {
                $lines = $jv['entries'] ?? $jv['lines'] ?? [];
                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        if ($checkMatch($line['accountId'] ?? '') || $checkMatch($line['accountNo'] ?? '') || $checkMatch($line['accountName'] ?? '')) {
                            $matched = true; break;
                        }
                    }
                }
            } else if ($entityType === 'cost_center') {
                if ($checkMatch($jv['costCenter'] ?? '')) $matched = true;
                $lines = $jv['entries'] ?? $jv['lines'] ?? [];
                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        if ($checkMatch($line['costCenter'] ?? '') || $checkMatch($line['costCenterId'] ?? '')) {
                            $matched = true; break;
                        }
                    }
                }
            } else if ($entityType === 'employee' || $entityType === 'user') {
                if ($checkMatch($jv['createdBy'] ?? '')) $matched = true;
            }

            if ($matched) {
                $usages[] = [
                    'type' => 'قيد يومية',
                    'doc_no' => (string)$docNo,
                    'date' => $date,
                    'label' => "قيد يومية رقم " . $docNo . ($date ? " بتاريخ " . $date : "")
                ];
            }
        }

        return [
            'entity_type' => $entityType,
            'is_used' => count($usages) > 0,
            'can_delete' => count($usages) === 0,
            'usage_count' => count($usages),
            'usages' => $usages
        ];
    }
}

// Backward compatibility alias for items
if (!function_exists('getItemUsageDetails')) {
    function getItemUsageDetails($pdo, $tenant_id, $itemId, $itemName = '', $itemBarcodes = []) {
        return checkEntityUsageInDatabase($pdo, $tenant_id, 'item', $itemId, $itemName, ['barcodes' => $itemBarcodes]);
    }
}

// Direct API access
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'check_entity_usage.php') {
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

    $input = json_decode(file_get_contents('php://input'), true) ?: $_GET;

    $entityType = $input['entity_type'] ?? $input['type'] ?? 'item';
    $entityId = $input['id'] ?? $input['item_id'] ?? $input['entity_id'] ?? null;
    $entityName = $input['name'] ?? $input['item_name'] ?? $input['entity_name'] ?? '';

    if (!$entityId && !$entityName) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Entity ID or name is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $usageResult = checkEntityUsageInDatabase($pdo, $tenant_id, $entityType, $entityId, $entityName, $input);
        echo json_encode([
            'success' => true,
            'entity_type' => $entityType,
            'id' => $entityId,
            'name' => $entityName,
            'is_used' => $usageResult['is_used'],
            'can_delete' => $usageResult['can_delete'],
            'usage_count' => $usageResult['usage_count'],
            'usages' => $usageResult['usages'],
            'message' => $usageResult['is_used']
                ? 'هذا العنصر مستخدم في سندات أو سجلات مسجلة بالنظام ولا يمكن حذفه نهائياً.'
                : 'هذا العنصر غير مستخدم في أي سند أو حركة، ويمكن حذفه نهائياً من قاعدة البيانات.'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
