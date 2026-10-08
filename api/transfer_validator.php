<?php
/**
 * ══════════════════════════════════════════════════════════════════════════════
 * MAGAIES ERP - AUTHORITATIVE TRANSFER VALIDATION & ANTI-DUPLICATION ENGINE
 * المحرك المركزي الموحد للتحقق من أرقام التحويلات البنكية ومنع تكرار آخر 6 أرقام
 * ══════════════════════════════════════════════════════════════════════════════
 * القواعد الصارمة المعتمدة:
 * 1. يمنع منعاً باتاً تسجيل أو حفظ أي رقم تحويلة يقل عن 6 أرقام (حظر قطعي).
 * 2. مقارنة آخر 6 أرقام مع جميع شاشات وجداول النظام (مبيعات، مشتريات، سندات، حركة مالية).
 * 3. منع تكرار آخر 6 أرقام في أي تحويلة مهما كان طول الرقم (6، 7، 10، 15، 16 رقماً).
 */

if (!function_exists('extractAllTransfersFromRecord')) {
    function extractAllTransfersFromRecord($d) {
        $refs = [];
        if (!is_array($d)) return $refs;
        $targetKeys = ['transferNo', 'mobileTransferNo', 'transferRef', 'bankTransferNo', 'refNo', 'reference', 'ref', 'checkNo'];
        
        $walker = function($item) use (&$walker, &$refs, $targetKeys) {
            if (!is_array($item)) return;
            foreach ($item as $k => $v) {
                if (is_string($k) && in_array($k, $targetKeys, true)) {
                    if (is_string($v) || is_numeric($v)) {
                        $str = trim((string)$v);
                        if ($str !== '' && !in_array(strtolower($str), ['cash', 'credit', 'bank', 'transfer', 'cheque', '---', '-'], true)) {
                            $refs[] = $str;
                        }
                    }
                }
                if (is_array($v)) {
                    $walker($v);
                }
            }
        };
        $walker($d);
        return array_values(array_unique(array_filter($refs)));
    }
}

if (!function_exists('findTransferDuplicateInDb')) {
    function findTransferDuplicateInDb($pdo, $ref, $currentVNo = '', $currentTenantId = null) {
        $ref = trim((string)$ref);
        if ($ref === '') return null;
        $cleanRef = preg_replace('/\D/', '', $ref);
        if (strlen($cleanRef) < 6) {
            return [
                'isTooShort' => true,
                'length' => strlen($cleanRef),
                'error' => 'رقم التحويلة غير مكتمل (' . strlen($cleanRef) . ' أرقام)، يمنع الحفظ برقم تحويلة يقل عن 6 أرقام نهائياً.'
            ];
        }
        $candSuffix = substr($cleanRef, -6);

        // شمل كافة شاشات وجداول النظام بدون استثناء
        $tables = ['single_payments', 'single_receipts', 'payments', 'receipts', 'purchase_invoices', 'sales_invoices'];
        foreach ($tables as $tbl) {
            try {
                $sql = "SELECT id, tenant_id, data FROM `$tbl` WHERE (is_deleted = 0 OR is_deleted IS NULL)";
                if ($currentTenantId) {
                    $sql .= " AND (tenant_id = " . (int)$currentTenantId . " OR tenant_id IS NULL)";
                }
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $d = json_decode($row['data'], true);
                    if (!$d) continue;
                    if (!empty($d['deleted']) || !empty($d['is_deleted'])) continue;

                    $existingVNo = trim((string)($d['voucherNo'] ?? ($d['invoiceNo'] ?? ($d['id'] ?? ''))));
                    if ($existingVNo !== '' && $currentVNo !== '' && $existingVNo === $currentVNo) {
                        continue;
                    }

                    $candidates = extractAllTransfersFromRecord($d);
                    foreach ($candidates as $cr) {
                        $strCr = trim((string)$cr);
                        $cleanCr = preg_replace('/\D/', '', $strCr);
                        if ($cleanCr === '') continue;

                        $matched = false;
                        $matchType = '';

                        // المقارنة الصارمة لآخر 6 أرقام مهما كان طول الرقمين
                        if (strlen($cleanCr) >= 6) {
                            $existSuffix = substr($cleanCr, -6);
                            if ($candSuffix === $existSuffix) {
                                $matched = true;
                                $matchType = "تطابق آخر 6 أرقام ({$candSuffix})";
                            }
                        } else {
                            if ($cleanRef === $cleanCr || substr($cleanRef, -strlen($cleanCr)) === $cleanCr) {
                                $matched = true;
                                $matchType = 'تطابق مع رقم تحويلة مسجل';
                            }
                        }

                        if ($matched) {
                            $docLabel = $existingVNo ?: ('#' . $row['id']);
                            if ($tbl === 'sales_invoices') $docLabel = 'فاتورة مبيعات ' . $docLabel;
                            elseif ($tbl === 'purchase_invoices') $docLabel = 'فاتورة مشتريات ' . $docLabel;
                            elseif ($tbl === 'payments' || $tbl === 'single_payments') $docLabel = 'سند صرف ' . $docLabel;
                            elseif ($tbl === 'receipts' || $tbl === 'single_receipts') $docLabel = 'سند قبض ' . $docLabel;

                            return [
                                'voucherNo' => $docLabel,
                                'matchedId' => $existingVNo ?: ('#' . $row['id']),
                                'ref' => $strCr,
                                'matchedSuffix' => $candSuffix,
                                'date' => $d['date'] ?? '',
                                'amount' => $d['amountLocal'] ?? ($d['amount'] ?? ($d['grandTotal'] ?? ($d['total'] ?? 0))),
                                'bank' => $d['bankName'] ?? ($d['bank'] ?? ''),
                                'from' => $d['fromAccountName'] ?? ($d['from'] ?? ($d['customerName'] ?? ($d['supplierName'] ?? ''))),
                                'to' => $d['toAccountName'] ?? ($d['to'] ?? ($d['beneficiaryName'] ?? '')),
                                'table' => $tbl,
                                'matchType' => $matchType
                            ];
                        }
                    }
                }
            } catch (Exception $e) {}
        }
        return null;
    }
}

if (!function_exists('validatePayloadTransfers')) {
    function validatePayloadTransfers($pdo, $payload, $currentDocId = '', $currentTenantId = null) {
        $transfers = extractAllTransfersFromRecord($payload);
        foreach ($transfers as $tr) {
            $cleanDigits = preg_replace('/\D/', '', $tr);
            if (strlen($cleanDigits) < 6) {
                return [
                    'valid' => false,
                    'error' => "رقم التحويلة ({$tr}) غير مكتمل (" . strlen($cleanDigits) . " أرقام)، يمنع منعاً باتاً الحفظ برقم تحويلة يقل عن 6 أرقام."
                ];
            }
            $dup = findTransferDuplicateInDb($pdo, $tr, $currentDocId, $currentTenantId);
            if ($dup) {
                return [
                    'valid' => false,
                    'duplicate' => true,
                    'error' => "رقم التحويلة ({$tr}) مكرر! تطابقت آخر 6 أرقام ({$dup['matchedSuffix']}) مع ({$dup['voucherNo']})" . (!empty($dup['date']) ? " بتاريخ {$dup['date']}" : "") . "، يمنع التكرار نهائياً!"
                ];
            }
        }
        return ['valid' => true];
    }
}
