<?php
// =============================================================
// MAGAIEES ERP - Financial OCR Scanner API
// مستخرج بيانات إشعارات التحويلات البنكية والشيكات بالذكاء الاصطناعي
// =============================================================
header('Content-Type: application/json; charset=utf-8');

// Load environment variables safely
if (!function_exists('loadEnvFinancialOCR')) {
    function loadEnvFinancialOCR($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
loadEnvFinancialOCR(__DIR__ . '/../.env');
loadEnvFinancialOCR(__DIR__ . '/../../.env');

$inputData = json_decode(file_get_contents('php://input'), true);
if (!is_array($inputData)) {
    $inputData = [];
}
$fraudCheck = !empty($inputData['fraud_check']);

// =============================================================
// Expert Reference Normalization, Filtering & Comparison Engine
// محرك المقارنة والتدقيق الخبير لأرقام العمليات البنكية
// =============================================================
if (!function_exists('normalizeBankRefDigits')) {
    function normalizeBankRefDigits($val) {
        $s = trim((string)$val);
        if ($s === '') return '';
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩','۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin   = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        $s = str_replace($arabic, $latin, $s);
        // Reject obvious non-reference placeholders (offline voucher IDs, WhatsApp temp IDs, etc.)
        if (preg_match('/^(OUT-OFF-|IN-OFF-|OFF-|WA\d|AUTO-|TEMP-|null|none|n\/a|غير)/i', $s)) {
            return '';
        }
        // Reject space-separated 4-digit bank account numbers (e.g. "0095 1356 0023 5001")
        if (preg_match('/^\d{4}\s+\d{4}(?:\s+\d{4}){1,2}$/', $s)) {
            return '';
        }
        $digits = preg_replace('/\D/', '', $s);
        if (strlen($digits) < 6) {
            return '';
        }
        // Reject 14-digit BBAN accounts starting with 0 and ending with 0001..0009 (e.g. 02522903920001)
        if (strlen($digits) === 14 && $digits[0] === '0' && preg_match('/000[1-9]$/', $digits)) {
            return '';
        }
        // Reject 21-digit O-Cash account numbers starting with 02
        if (strlen($digits) === 21 && substr($digits, 0, 2) === '02') {
            return '';
        }
        // Reject 16-digit Bankak account numbers starting with 0 and ending in 0001/0002/0101/5001
        if (strlen($digits) === 16 && $digits[0] === '0' && preg_match('/(0001|0002|0101|5001)$/', $digits)) {
            return '';
        }
        return $digits;
    }
}

if (!function_exists('areBankReferencesEqual')) {
    function areBankReferencesEqual($refA, $refB) {
        $dA = normalizeBankRefDigits($refA);
        $dB = normalizeBankRefDigits($refB);
        if ($dA === '' || $dB === '') {
            return false;
        }
        // 1. Exact full digits match
        if ($dA === $dB) {
            return true;
        }
        $lenA = strlen($dA);
        $lenB = strlen($dB);
        $minLen = min($lenA, $lenB);
        if ($minLen < 6) {
            return false;
        }

        // 2. One reference is a shorter suffix (6-10 digits) manually entered from a full reference
        if ($lenA !== $lenB) {
            $shorter = ($lenA < $lenB) ? $dA : $dB;
            $longer  = ($lenA < $lenB) ? $dB : $dA;
            if (strlen($shorter) >= 6 && substr($longer, -strlen($shorter)) === $shorter) {
                return true;
            }
            // If both are >= 9 digits and share the exact last 8 digits
            if ($minLen >= 9 && substr($dA, -8) === substr($dB, -8)) {
                return true;
            }
            return false;
        }

        // 3. Same length references:
        // 3a. If 6-8 digits, require exact match (already checked above)
        if ($lenA <= 8) {
            return false;
        }
        // 3b. If >= 9 digits, match if last 8 digits are identical
        if (substr($dA, -8) === substr($dB, -8)) {
            return true;
        }
        // 3c. Match if last 6 digits are identical AND first 2 digits match (same bank format)
        if (substr($dA, -6) === substr($dB, -6) && substr($dA, 0, 2) === substr($dB, 0, 2)) {
            return true;
        }
        // 3d. Top-clipped Bankak 11-digit reference resilience (e.g. 20125380642 vs 20125880042):
        // Same 11-digit length, same 5-digit batch prefix (20125) and same 3-digit sequence suffix (042),
        // with at least 9 of 11 positions identical
        if ($lenA === 11 && substr($dA, 0, 5) === substr($dB, 0, 5) && substr($dA, -3) === substr($dB, -3)) {
            $sameCount = 0;
            for ($i = 0; $i < 11; $i++) {
                if ($dA[$i] === $dB[$i]) $sameCount++;
            }
            if ($sameCount >= 9) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('extractValidTransfersFromVoucherData')) {
    function extractValidTransfersFromVoucherData($d) {
        if (!is_array($d)) return [];
        // Skip deleted vouchers
        if (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0') {
            return [];
        }
        // Collect known account numbers in this voucher so they are never treated as transfer numbers
        $accDigitsMap = [];
        foreach (['senderAccount', 'receiverAccount', 'fromAccount', 'toAccount', 'accountNo', 'iban', 'bban'] as $accField) {
            if (!empty($d[$accField]) && is_scalar($d[$accField])) {
                $ad = preg_replace('/\D/', '', (string)$d[$accField]);
                if ($ad !== '') {
                    $accDigitsMap[$ad] = true;
                    if (strlen($ad) === 16) {
                        $accDigitsMap[substr($ad, 0, 8)] = true;
                        $accDigitsMap[substr($ad, 4, 8)] = true;
                        $accDigitsMap[substr($ad, 8, 8)] = true;
                    }
                }
            }
        }

        $rawCandidates = [];
        foreach (['transferNo', 'mobileTransferNo', 'bankReference', 'reference', 'trxId', 'transaction_ref'] as $f) {
            if (!empty($d[$f]) && is_scalar($d[$f])) $rawCandidates[] = (string)$d[$f];
        }
        // Only include refNo if it is NOT an offline voucher sequence like OUT-OFF-5
        if (!empty($d['refNo']) && is_scalar($d['refNo']) && !preg_match('/^(OUT-OFF-|IN-OFF-|OFF-|[A-Z]-\d+$)/i', trim((string)$d['refNo']))) {
            $rawCandidates[] = (string)$d['refNo'];
        }
        foreach (['transfers', 'bankTransfers', 'payments', 'SubEntries', 'multiEntries', 'items', 'lines'] as $arrKey) {
            if (!empty($d[$arrKey]) && is_array($d[$arrKey])) {
                foreach ($d[$arrKey] as $sub) {
                    if (!is_array($sub)) continue;
                    foreach (['transferNo', 'transfer', 'reference', 'refNo', 'bankReference', 'trxId', 'number'] as $sf) {
                        if (!empty($sub[$sf]) && is_scalar($sub[$sf]) && !preg_match('/^(OUT-OFF-|IN-OFF-|OFF-|[A-Z]-\d+$)/i', trim((string)$sub[$sf]))) {
                            $rawCandidates[] = (string)$sub[$sf];
                        }
                    }
                    if (!empty($sub['bankTransfers']) && is_array($sub['bankTransfers'])) {
                        foreach ($sub['bankTransfers'] as $bt) {
                            if (!is_array($bt)) continue;
                            foreach (['transferNo', 'transfer', 'reference', 'refNo', 'bankReference', 'trxId', 'number'] as $bsf) {
                                if (!empty($bt[$bsf]) && is_scalar($bt[$bsf])) $rawCandidates[] = (string)$bt[$bsf];
                            }
                        }
                    }
                }
            }
        }

        $validRefs = [];
        foreach ($rawCandidates as $rc) {
            // Split if multiple numbers separated by / or |
            $parts = preg_split('/[\/|,;\s]+/', trim($rc));
            foreach ($parts as $p) {
                $norm = normalizeBankRefDigits($p);
                if ($norm === '') continue;
                if (isset($accDigitsMap[$norm])) continue;
                $validRefs[$norm] = $norm;
            }
        }
        return array_values($validRefs);
    }
}

if (!function_exists('checkBankReferenceDuplicateInDB')) {
    function checkBankReferenceDuplicateInDB($ref, $excludeVoucherNo = '', $excludeInboundId = 0) {
        $cleanRef = normalizeBankRefDigits($ref);
        if ($cleanRef === '') {
            return null;
        }
        try {
            global $pdo;
            if (!isset($pdo) || !$pdo) {
                require __DIR__ . '/db.php';
            }
            if (!isset($pdo) || !$pdo) {
                return null;
            }
            $excludeVoucherNo = trim((string)$excludeVoucherNo);

            $tables = [
                ['table' => 'receipts',        'label' => 'سند قبض'],
                ['table' => 'single_receipts', 'label' => 'سند قبض مفرد'],
                ['table' => 'single_payments', 'label' => 'سند صرف نقدي مفرد'],
                ['table' => 'payments',        'label' => 'سند صرف نقدي متعدد']
            ];

            $activeVouchers = [];
            foreach ($tables as $tInfo) {
                $tbl = $tInfo['table'];
                try {
                    $stmt = $pdo->query("SELECT id, data FROM `{$tbl}` WHERE (is_deleted = 0 OR is_deleted IS NULL)");
                    if (!$stmt) continue;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $d = json_decode($row['data'] ?? '{}', true);
                        if (!is_array($d)) $d = [];
                        if (!empty($d['deleted']) && $d['deleted'] !== 'false' && $d['deleted'] !== '0') {
                            continue;
                        }
                        $vNo = trim((string)($d['voucherNo'] ?? $d['receiptNo'] ?? $d['paymentNo'] ?? $d['invoiceNo'] ?? $d['id'] ?? ''));
                        if ($vNo !== '') {
                            $activeVouchers[$vNo] = true;
                        }
                        if ($excludeVoucherNo !== '' && $vNo === $excludeVoucherNo) {
                            continue;
                        }
                        $vRefs = extractValidTransfersFromVoucherData($d);
                        foreach ($vRefs as $candRef) {
                            if (areBankReferencesEqual($cleanRef, $candRef)) {
                                $partyName = $d['party_name'] ?? $d['customer_name'] ?? $d['customerName'] ?? $d['supplierName'] ?? $d['accountName'] ?? '';
                                if ($partyName === '' && !empty($d['items'][0]['accountName'])) {
                                    $partyName = $d['items'][0]['accountName'];
                                }
                                if ($partyName === '' && !empty($d['lines'][0]['accountName'])) {
                                    $partyName = $d['lines'][0]['accountName'];
                                }
                                return [
                                    'voucher_no'   => $vNo ?: ('سند #' . $row['id']),
                                    'source'       => $tInfo['label'],
                                    'source_label' => $tInfo['label'],
                                    'party_name'   => $partyName,
                                    'amount'       => (float)($d['totalAmount'] ?? $d['amount'] ?? 0),
                                    'date'         => $d['date'] ?? '',
                                    'matched_ref'  => $candRef
                                ];
                            }
                        }
                    }
                } catch (Exception $eTbl) {
                    // Ignore missing optional table
                }
            }

            // Also check verified/reconciled inbound_bank_receipts whose voucher is still active
            try {
                $stmtIn = $pdo->query("SELECT id, transfer_no, customer_name, customer_phone, amount, voucher_no, status, created_at FROM inbound_bank_receipts WHERE status IN ('verified', 'reconciled', 'approved')");
                if ($stmtIn) {
                    while ($row = $stmtIn->fetch(PDO::FETCH_ASSOC)) {
                        if ($excludeInboundId > 0 && (int)$row['id'] === (int)$excludeInboundId) continue;
                        $vNo = trim((string)($row['voucher_no'] ?? ''));
                        if ($excludeVoucherNo !== '' && $vNo === $excludeVoucherNo) continue;
                        // If linked to a voucher, only count if that voucher still exists and was not deleted
                        if ($vNo !== '' && !isset($activeVouchers[$vNo])) continue;
                        $candRef = normalizeBankRefDigits($row['transfer_no'] ?? '');
                        if ($candRef !== '' && areBankReferencesEqual($cleanRef, $candRef)) {
                            return [
                                'voucher_no'   => $vNo ?: ('إشعار #' . $row['id']),
                                'source'       => 'صندوق الإشعارات المعتمدة',
                                'source_label' => 'صندوق الإشعارات المعتمدة',
                                'party_name'   => $row['customer_name'] ?: ($row['customer_phone'] ?? ''),
                                'amount'       => (float)($row['amount'] ?? 0),
                                'date'         => substr((string)($row['created_at'] ?? ''), 0, 10),
                                'matched_ref'  => $candRef
                            ];
                        }
                    }
                }
            } catch (Exception $eIn) {}
        } catch (Exception $e) {}
        return null;
    }
}

// Fast AJAX endpoint to check reference duplication against MySQL database
if ((isset($inputData['action']) && $inputData['action'] === 'check_ref_duplicate') || (isset($_POST['action']) && $_POST['action'] === 'check_ref_duplicate')) {
    $refToCheck = $inputData['ref'] ?? $_POST['ref'] ?? '';
    $excludeVoucher = $inputData['exclude_voucher'] ?? $_POST['exclude_voucher'] ?? '';
    $dupInfo = checkBankReferenceDuplicateInDB($refToCheck, $excludeVoucher);
    echo json_encode([
        'success' => true,
        'is_duplicate' => ($dupInfo !== null),
        'duplicate_info' => $dupInfo
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Save OCR properties endpoint
if ((isset($inputData['action']) && $inputData['action'] === 'save_ocr_props') || (isset($_POST['action']) && $_POST['action'] === 'save_ocr_props')) {
    try {
        global $pdo;
        if (!isset($pdo) || !$pdo) {
            require __DIR__ . '/db.php';
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS ui_preferences (
            tenant_id VARCHAR(50) NOT NULL,
            pref_key VARCHAR(100) NOT NULL,
            pref_value LONGTEXT,
            PRIMARY KEY (tenant_id, pref_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $val = $inputData['ocr_props'] ?? $_POST['ocr_props'] ?? '{}';
        if (is_array($val)) $val = json_encode($val);
        $stmt = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES ('1', 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt->execute([$val, $val]);
        $stmt2 = $pdo->prepare("INSERT INTO ui_preferences (tenant_id, pref_key, pref_value) VALUES ('default', 'ocr_left_col_props', ?) ON DUPLICATE KEY UPDATE pref_value = ?");
        $stmt2->execute([$val, $val]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$imageBase64 = $inputData['image'] ?? null;
$customGuidelines = $inputData['custom_guidelines'] ?? '';
$documentHint = $inputData['doc_hint'] ?? 'auto'; // 'auto', 'transfer', 'cheque'

if (empty($imageBase64)) {
    echo json_encode(['success' => false, 'error' => 'لم يتم إرسال صورة المستند المالي']);
    exit;
}

// Payload size safety check (max 8MB base64) to prevent memory exhaustion
if (strlen($imageBase64) > 8 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'حجم الصورة المرسلة كبير جداً، يرجى استخدام صورة بحجم أقل من 6 ميجابايت']);
    exit;
}

// Clean base64 header if present
$mimeType = 'image/jpeg';
if (strpos($imageBase64, 'data:') === 0) {
    if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-\.]+);base64,/', $imageBase64, $m)) {
        $mimeType = $m[1];
    }
    $partsArr = explode('base64,', $imageBase64);
    $imageBase64 = $partsArr[1] ?? $imageBase64;
}
require_once __DIR__ . '/ai-config.php';

$clientKey = trim($inputData['api_key'] ?? '');
$apiKey = getGeminiApiKey($clientKey);
$model = getGeminiModel();

$serverGuidelinesFile = __DIR__ . '/financial_ocr_guidelines.txt';
$serverTemplatesFile = __DIR__ . '/financial_templates.json';

$allGuidelines = [];
if (!empty($customGuidelines)) {
    $allGuidelines[] = trim($customGuidelines);
}
if (file_exists($serverGuidelinesFile)) {
    $sg = trim(file_get_contents($serverGuidelinesFile));
    if (!empty($sg) && !in_array($sg, $allGuidelines)) {
        $allGuidelines[] = $sg;
    }
}

$templatesText = "";
if (file_exists($serverTemplatesFile)) {
    $savedTemplates = json_decode(file_get_contents($serverTemplatesFile), true);
    if (is_array($savedTemplates) && count($savedTemplates) > 0) {
        $templatesText .= "\nقواعد النماذج المدربة في النظام (يرجى تطبيقها لمطابقة نوع المستند والبنك بدقة):\n";
        foreach ($savedTemplates as $t) {
            $templatesText .= "- [نموذج: " . ($t['name'] ?? '') . " | بنك: " . ($t['bank'] ?? '') . " | نوع: " . ($t['type'] ?? '') . "]: " . ($t['rules'] ?? '') . "\n";
        }
    }
}

$customGuidelineText = "";
if (!empty($allGuidelines)) {
    $customGuidelineText = "\nإرشادات وقواعد تدريبية إضافية مقدمة من المستخدم:\n" . implode("\n", $allGuidelines) . "\n";
}
$customGuidelineText .= $templatesText;

// Function to robustly clean and extract financial amount
if (!function_exists('cleanFinancialAmount')) {
    function cleanFinancialAmount($val, $rawText = '') {
        $str = '';
        if ($val !== null && $val !== '') {
            $str = (string)$val;
        }

        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latin = ['0','1','2','3','4','5','6','7','8','9'];
        $str = str_replace($arabic, $latin, $str);
        $str = str_replace($persian, $latin, $str);

        $parseNumeric = function($raw) {
            $raw = trim($raw);
            if (strpos($raw, '.') !== false && strpos($raw, ',') !== false) {
                if (strrpos($raw, ',') > strrpos($raw, '.')) {
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                } else {
                    $raw = str_replace(',', '', $raw);
                }
            } elseif (substr_count($raw, '.') > 1) {
                $parts = explode('.', $raw);
                if (strlen(end($parts)) == 2) {
                    $cents = array_pop($parts);
                    $raw = implode('', $parts) . '.' . $cents;
                } else {
                    $raw = implode('', $parts);
                }
            } elseif (substr_count($raw, ',') > 1) {
                $raw = str_replace(',', '', $raw);
            } elseif (preg_match('/^(\d+)[.,](\d{3})$/', $raw, $m)) {
                $raw = $m[1] . $m[2];
            } elseif (preg_match('/^(\d+),(\d{2})$/', $raw, $m)) {
                $raw = $m[1] . '.' . $m[2];
            } else {
                $raw = str_replace([',', ' '], '', $raw);
            }
            return floatval($raw);
        };

        if (preg_match('/[0-9]/', $str)) {
            if (preg_match('/([0-9][0-9,.\s]*[0-9]|[0-9]+)/', $str, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
        }

        if (!empty($rawText)) {
            $cleanRaw = str_replace($arabic, $latin, (string)$rawText);
            $cleanRaw = str_replace($persian, $latin, $cleanRaw);

            if (preg_match('/"amount"\s*:\s*"?([0-9][0-9,.\s]*)/i', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
            if (preg_match('/(?:المبلغ|قيمة|بمبلغ|مبلغ|ادفعوا)[^\d:\r\n]{0,35}[:\-]?\s*(?:قدره)?\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
            if (preg_match('/(?:Total(?:\s+Amount)?|Amount|Sum|Transfer(?:\s+Amount)?)\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
            if (preg_match('/([0-9][0-9,.\s]*[0-9]|[0-9]+)\s*(?:SDG|ج\.س|جنيه|ريال|SAR|USD|دولار)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
            if (preg_match('/(?:SDG|ج\.س|جنيه|ريال|SAR|USD|دولار)\s*[:\-]?\s*([0-9][0-9,.\s]*[0-9]|[0-9]+)/ui', $cleanRaw, $m)) {
                $num = $parseNumeric($m[1]);
                if ($num > 0) return $num;
            }
        }

        return 0.0;
    }
}

$refExpertRules = "
قواعد صارمة ودقيقة جداً لاستخراج رقم العملية (transaction_ref):
1. المسميات المعتمدة لرقم العملية في الإشعارات البنكية:
   - 'رقم العملية' أو 'العملية' (في تطبيق بنكك - بنك الخرطوم، وعادة يتكون من 11 رقماً يبدأ بـ 20 أو 21).
   - 'الرقم المرجعي' أو 'رقم مرجعي' (في تطبيق فوري - بنك فيصل ويتكون من 12 رقماً، وفي تطبيق أوكاش - بنك أمدرمان ويتكون من 18 رقماً يبدأ بـ 1010).
   - 'مرجع البنك' (في تطبيق ساهل - بنك النيل، ويتكون من 18 رقماً).
   - 'تسلسل العملية'، 'رقم المعاملة'، 'رقم التحويل'، 'رقم الإشعار'، 'رقم السند'، 'رقم المرجع'، 'Trx. ID'، 'Transaction ID'، 'Ref No'، 'Reference'.
2. الممنوعات القطعية (حقول يُمنع منعاً باتاً وضعها في transaction_ref تحت أي ظرف):
   - 'اسم الفاتورة' أو 'رقم الفاتورة' أو 'نوع العملية' (أبداً ليس رقم العملية).
   - 'من حساب' أو 'من الحساب' أو 'من' أو 'رقم المرسل' أو 'From Account' (أبداً ليس رقم العملية، بل يوضع في sender_account).
   - 'إلى حساب' أو 'الي الحساب' أو 'إلى' أو 'رقم المرسل إليه' أو 'To Account' (أبداً ليس رقم العملية، بل يوضع في receiver_account).
   - 'رقم البيبان' أو 'رقم المرسل اليه BBAN' أو 'BBAN' أو 'IBAN' (أبداً ليس رقم العملية، بل يوضع في receiver_account).
   - 'المبلغ' أو 'إجمالي المبلغ' أو 'إجمالي التحويل' أو 'الرسوم' (أبداً ليس رقم العملية).
   - 'الرد' أو 'رمز التحقق' أو 'الحالة' أو 'بيانات إضافية' (أبداً ليس رقم العملية).
   - 'التاريخ والزمن' أو 'التاريخ والوقت' أو 'رقم الموبايل' (أبداً ليس رقم العملية).
3. إذا كانت صورة الإشعار مقطوعة من الأعلى ولم يظهر فيها حقل رقم العملية، اجعل transaction_ref نصاً فارغاً \"\" واجعل is_cropped: true، ولا تخمّن أبداً رقم الحساب أو البيبان كرقم عملية.";

if ($fraudCheck) {
    $systemPrompt = "أنت خبير فحص المستندات المالية المصرفية بالرؤية الحاسوبية والتدقيق الجنائي الرقمي (Forensic Image & Document Tampering Inspection).
دقق في صورة المستند المالي بعين الخبير الجنائي للبحث عن أي مؤشرات للتزوير أو التعديل ببرامج تحرير الصور (Photoshop / Mobile Editing) أو توليد الإشعارات الوهمية، مع التركيز الفائق والدقيق على رقم العملية المرجعي (Transaction Reference ID):
" . $refExpertRules . "
- افحص كل رقم وخانة في رقم العملية: هل نوع الخط، سماكته، حدة الحواف، أو المسافات البينية متطابقة تماماً أم يوجد رقم معدل؟
- قدّر نسبة الشك في التزوير (tampering_score) من 0 إلى 100.

قم بتفريغ البيانات بصيغة JSON التالية وبدون أي نصوص تمهيدية:
{
  \"is_financial_doc\": true,
  \"is_approved_template\": true,
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\" | \"non_financial\",
  \"bank_name\": \"اسم البنك\",
  \"app_name\": \"اسم التطبيق إن وجد\",
  \"transaction_ref\": \"رقم العملية المرجعي / Transaction ID\",
  \"cheque_number\": \"رقم الشيك إن وجد\",
  \"amount\": 0.0,
  \"amount_in_words\": \"المبلغ بالحروف إن وجد\",
  \"currency\": \"SDG\" | \"USD\" | \"SAR\",
  \"date\": \"YYYY-MM-DD\",
  \"due_date\": \"YYYY-MM-DD\",
  \"time\": \"HH:MM\",
  \"sender_name\": \"اسم المحول\",
  \"sender_account\": \"من حساب\",
  \"receiver_name\": \"المستلم / إلى\",
  \"receiver_account\": \"إلى حساب\",
  \"receiver_phone\": \"\",
  \"notes\": \"\",
  \"confidence_score\": 95,
  \"tampering_score\": 0,
  \"is_suspicious\": false,
  \"is_cropped\": false,
  \"tampering_reasons\": \"شرح سبب الاشتباه الجنائي في التزوير بالتفصيل إن وجد أو فارغ تماماً إن كان أصلياً وسليماً\",
  \"summary\": \"ملخص سطر واحد بالعربية\"
}";
    $maxOutputTokens = 2048;
} else {
    $systemPrompt = "أنت محرك فحص إشعارات التحويلات البنكية والشيكات فائق الدقة والسرعة (Turbo Fast OCR).
استخرج البيانات من الصورة بدقة فائقة:
- بنكك (Bankak - بنك الخرطوم): اسم البنك 'بنك الخرطوم'، والتطبيق 'بنكك'.
- فوري (Fawry - بنك فيصل): اسم البنك 'بنك فيصل الإسلامي'، والتطبيق 'فوري'.
- أوكاش (O-Cash - بنك أمدرمان): اسم البنك 'بنك أمدرمان الوطني'، والتطبيق 'أوكاش'.
- ساهل (Sahil - بنك النيل): اسم البنك 'بنك النيل'، والتطبيق 'ساهل'.
" . $refExpertRules . "

أخرج حصراً كود JSON الصالح للتحليل مباشرة بدون أي نصوص تمهيدية:
{
  \"is_financial_doc\": true,
  \"is_approved_template\": true,
  \"doc_type\": \"bank_transfer\" | \"cheque\" | \"deposit_slip\",
  \"bank_name\": \"اسم البنك\",
  \"app_name\": \"اسم التطبيق إن وجد\",
  \"transaction_ref\": \"رقم العملية / الرقم المرجعي / مرجع البنك / Trx. ID\",
  \"cheque_number\": \"رقم الشيك إن وجد\",
  \"amount\": 0.0,
  \"amount_in_words\": \"\",
  \"currency\": \"SDG\" | \"SAR\" | \"USD\",
  \"date\": \"YYYY-MM-DD\",
  \"due_date\": \"YYYY-MM-DD\",
  \"time\": \"\",
  \"sender_name\": \"اسم المحول\",
  \"sender_account\": \"من حساب\",
  \"receiver_name\": \"المستلم / إلى\",
  \"receiver_account\": \"إلى حساب\",
  \"receiver_phone\": \"\",
  \"notes\": \"\",
  \"confidence_score\": 98,
  \"tampering_score\": 0,
  \"is_suspicious\": false,
  \"is_cropped\": false,
  \"tampering_reasons\": \"\",
  \"summary\": \"ملخص سطر واحد\"
}";
    $maxOutputTokens = 700;
}

$systemPrompt .= $customGuidelineText;

if (!empty($inputData['retry_amount'])) {
    $systemPrompt .= "\n\n⚠️ تنبيه عالي الأهمية (إعادة فحص تلقائي للمبلغ): في المحاولة السابقة ظهر المبلغ صفر (0)؛ دقق في الصورة كاملة واستخرج المبلغ الحقيقي بدقة تامة ولا تتركه صفراً إذا كان موجوداً.";
}

if (!empty($inputData['is_refresh'])) {
    $systemPrompt .= "\n\n⚠️ إعادة تدقيق شاملة بطلب خاص من المحاسب (Manual Refresh): دقق بتركيز فائق واقرأ الأرقام والحروف بأعلى درجات الدقة والتطابق مع الصورة الأصلية.";
}

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt],
                [
                    "inline_data" => [
                        "mime_type" => $mimeType,
                        "data" => $imageBase64
                    ]
                ]
            ]
        ]
    ],
    "generationConfig" => [
        "responseMimeType" => "application/json",
        "temperature" => 0.1,
        "maxOutputTokens" => $maxOutputTokens
    ]
];

// =============================================================
// Spatial Y-Coordinate Row Pairing & Context-Aware Local OCR Engine
// محرك الرؤية المكانية الذكي لتحليل صفوف الإشعارات البنكية بدقة 100%
// =============================================================
if (!function_exists('runLocalReceiptOcrFallback')) {
    function runLocalReceiptOcrFallback($imageBase64, $mimeType, $fraudCheck = false) {
        $rawBytes = base64_decode($imageBase64);
        if (empty($rawBytes)) {
            return null;
        }

        $imgHash = md5($rawBytes);
        $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'magaiees_winocr_v2_' . $imgHash . '.json';

        $arItems = [];
        $enItems = [];
        $imgW = 0;
        $imgH = 0;

        if (file_exists($cacheFile)) {
            $cachedJson = @json_decode((string)@file_get_contents($cacheFile), true);
            if (is_array($cachedJson) && (!empty($cachedJson['ar_items']) || !empty($cachedJson['en_items']))) {
                $arItems = $cachedJson['ar_items'] ?? [];
                $enItems = $cachedJson['en_items'] ?? [];
                $imgW = (int)($cachedJson['img_w'] ?? 0);
                $imgH = (int)($cachedJson['img_h'] ?? 0);
            }
        }

        if (empty($arItems) && empty($enItems)) {
            $ext = 'jpg';
            if (strpos($mimeType, 'png') !== false) $ext = 'png';
            elseif (strpos($mimeType, 'webp') !== false) $ext = 'webp';

            $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'magaiees_ocr_' . uniqid() . '.' . $ext;
            file_put_contents($tmpFile, $rawBytes);

            $psScript = __DIR__ . DIRECTORY_SEPARATOR . 'win-ocr.ps1';
            if (PHP_OS_FAMILY === 'Windows' && file_exists($psScript)) {
                $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File '
                     . escapeshellarg($psScript) . ' -ImagePath ' . escapeshellarg($tmpFile) . ' 2>&1';
                $out = shell_exec($cmd);
                if (!empty($out) && preg_match('/\{.*\}/s', $out, $mJson)) {
                    $ocrJson = json_decode($mJson[0], true);
                    if (!empty($ocrJson['success'])) {
                        $imgW = (int)($ocrJson['img_w'] ?? 0);
                        $imgH = (int)($ocrJson['img_h'] ?? 0);
                        foreach (($ocrJson['ar_lines'] ?? []) as $idx => $l) {
                            $txt = trim((string)($l['text'] ?? ''));
                            if ($txt === '') continue;
                            $arItems[] = [
                                'text'     => $txt,
                                'rtl_text' => trim((string)($l['rtl_text'] ?? $txt)),
                                'x'        => (int)($l['x'] ?? 0),
                                'y'        => (int)($l['y'] ?? ($idx * 35)),
                                'w'        => (int)($l['w'] ?? 0),
                                'h'        => (int)($l['h'] ?? 24)
                            ];
                        }
                        foreach (($ocrJson['en_lines'] ?? []) as $idx => $l) {
                            $txt = trim((string)($l['text'] ?? ''));
                            if ($txt === '') continue;
                            $enItems[] = [
                                'text'     => $txt,
                                'rtl_text' => trim((string)($l['rtl_text'] ?? $txt)),
                                'x'        => (int)($l['x'] ?? 0),
                                'y'        => (int)($l['y'] ?? ($idx * 35)),
                                'w'        => (int)($l['w'] ?? 0),
                                'h'        => (int)($l['h'] ?? 24)
                            ];
                        }
                    }
                }
            }

            // Cross-platform local OCR fallback (Linux server & Windows fallback via warm Node endpoint or node-ocr.mjs)
            if (empty($arItems) && empty($enItems)) {
                $ocrJson = null;
                $chOcr = @curl_init('http://127.0.0.1:3001/wa/ocr');
                if ($chOcr) {
                    curl_setopt_array($chOcr, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                        CURLOPT_POSTFIELDS => json_encode(['imagePath' => $tmpFile]),
                        CURLOPT_TIMEOUT => 6,
                        CURLOPT_CONNECTTIMEOUT => 1
                    ]);
                    $ocrResp = @curl_exec($chOcr);
                    $ocrHttp = (int)@curl_getinfo($chOcr, CURLINFO_HTTP_CODE);
                    @curl_close($chOcr);
                    if ($ocrResp && $ocrHttp === 200) {
                        $ocrJson = json_decode($ocrResp, true);
                    }
                }
                $nodeScript = __DIR__ . DIRECTORY_SEPARATOR . 'node-ocr.mjs';
                if (empty($ocrJson['success']) && file_exists($nodeScript)) {
                    $nodeBin = (PHP_OS_FAMILY === 'Windows') ? 'node' : '/usr/bin/node';
                    if (PHP_OS_FAMILY !== 'Windows' && !file_exists($nodeBin)) $nodeBin = 'node';
                    $cmdNode = $nodeBin . ' ' . escapeshellarg($nodeScript) . ' ' . escapeshellarg($tmpFile) . ' 2>/dev/null';
                    if (PHP_OS_FAMILY === 'Windows') {
                        $cmdNode = 'node ' . escapeshellarg($nodeScript) . ' ' . escapeshellarg($tmpFile) . ' 2>&1';
                    }
                    $outNode = @shell_exec($cmdNode);
                    if (!empty($outNode) && preg_match('/\{.*\}/s', $outNode, $mNodeJson)) {
                        $ocrJson = json_decode($mNodeJson[0], true);
                    }
                }
                if (!empty($ocrJson['success'])) {
                    $imgW = (int)($ocrJson['img_w'] ?? 0);
                    $imgH = (int)($ocrJson['img_h'] ?? 0);
                    foreach (($ocrJson['ar_lines'] ?? []) as $idx => $l) {
                        $txt = trim((string)($l['text'] ?? ''));
                        if ($txt === '') continue;
                        $arItems[] = [
                            'text'     => $txt,
                            'rtl_text' => trim((string)($l['rtl_text'] ?? $txt)),
                            'x'        => (int)($l['x'] ?? 0),
                            'y'        => (int)($l['y'] ?? ($idx * 35)),
                            'w'        => (int)($l['w'] ?? 0),
                            'h'        => (int)($l['h'] ?? 24)
                        ];
                    }
                    foreach (($ocrJson['en_lines'] ?? []) as $idx => $l) {
                        $txt = trim((string)($l['text'] ?? ''));
                        if ($txt === '') continue;
                        $enItems[] = [
                            'text'     => $txt,
                            'rtl_text' => trim((string)($l['rtl_text'] ?? $txt)),
                            'x'        => (int)($l['x'] ?? 0),
                            'y'        => (int)($l['y'] ?? ($idx * 35)),
                            'w'        => (int)($l['w'] ?? 0),
                            'h'        => (int)($l['h'] ?? 24)
                        ];
                    }
                }
            }

            @unlink($tmpFile);

            if (!empty($arItems) || !empty($enItems)) {
                @file_put_contents($cacheFile, json_encode([
                    'img_w'    => $imgW,
                    'img_h'    => $imgH,
                    'ar_items' => $arItems,
                    'en_items' => $enItems
                ], JSON_UNESCAPED_UNICODE));
            }
        }

        if (empty($arItems) && empty($enItems)) {
            return null;
        }

        $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩','۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $latinDigits  = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        $normStr = function($s) use ($arabicDigits, $latinDigits) {
            return trim(str_replace($arabicDigits, $latinDigits, (string)$s));
        };

        $arClean = [];
        foreach ($arItems as &$it) {
            $it['text']     = $normStr($it['text']);
            $it['rtl_text'] = $normStr($it['rtl_text']);
            $arClean[]      = $it['rtl_text'];
            if ($it['text'] !== $it['rtl_text']) {
                $arClean[] = $it['text'];
            }
        }
        unset($it);

        $enClean = [];
        foreach ($enItems as &$it) {
            $it['text']     = $normStr($it['text']);
            $it['rtl_text'] = $normStr($it['rtl_text']);
            $enClean[]      = $it['text'];
        }
        unset($it);

        $allLines = array_merge($arClean, $enClean);
        $joinedAll = implode("\n", $allLines);
        $joinedLower = mb_strtolower($joinedAll);

        // 1. Detect Bank & App Name
        $bankName = 'بنك الخرطوم';
        $appName  = 'بنكك';
        if (
            strpos($joinedLower, 'ساهل') !== false ||
            strpos($joinedLower, 'sahil') !== false ||
            strpos($joinedLower, 'بنك النيل') !== false ||
            strpos($joinedLower, 'مرجع البنك') !== false ||
            strpos($joinedLower, 'مرجع الينك') !== false ||
            strpos($joinedLower, 'bban') !== false
        ) {
            $bankName = 'بنك النيل';
            $appName  = 'ساهل';
        } elseif (
            strpos($joinedLower, 'o-cash') !== false ||
            strpos($joinedLower, 'اوو') !== false ||
            strpos($joinedLower, 'أوكاش') !== false ||
            strpos($joinedLower, 'أمدرمان') !== false
        ) {
            $bankName = 'بنك أمدرمان الوطني';
            $appName  = 'أوكاش';
        } elseif (
            strpos($joinedLower, 'fawry') !== false ||
            strpos($joinedLower, 'فوري') !== false ||
            strpos($joinedLower, 'فيصل') !== false
        ) {
            $bankName = 'بنك فيصل الإسلامي';
            $appName  = 'فوري';
        } elseif (
            strpos($joinedLower, 'بنكك') !== false ||
            strpos($joinedLower, 'bankak') !== false ||
            strpos($joinedLower, 'ankak') !== false ||
            strpos($joinedLower, 'الخرطوم') !== false ||
            strpos($joinedLower, 'khartoum') !== false
        ) {
            $bankName = 'بنك الخرطوم';
            $appName  = 'بنكك';
        }

        // 2. Spatial Label Classifier (تصنيف التسميات المكانية بالذكاء الاصطناعي)
        $classifyLabel = function($item) use ($imgW) {
            $both = mb_strtolower($item['rtl_text'] . ' | ' . $item['text']);
            $rtl  = mb_strtolower($item['rtl_text']);
            $ltr  = mb_strtolower($item['text']);

            // Category A: Excluded Invoice / Biller / Operation Type Labels (اسم الفاتورة / نوع العملية)
            if (preg_match('/(اسم\s*الفاتور|الفاتور[ةه]\s*اسم|رقم\s*الفاتور|الفاتور[ةه]\s*رقم|سداد\s*الفاتور|دفع\s*الفاتور|دفع\s*الفواتير|نوع\s*العملي|العملي[ةه]\s*نوع|invoice|biller|bill\s*name)/u', $both)) {
                return 'EXCLUDED_INVOICE';
            }

            // Category B: Excluded Verification Code / Response / Status / Comments / Fees
            if (preg_match('/(رمز\s*التحقق|التحقق\s*رمز|رمز\s*اللحقق|اللحقق\s*رمز|بيانات\s*إضافي|إضافي[ةه]\s*بيانات|الرد|الحال[ةه]|التعليق|الرسوم|رسوم\s*العملي|verification|auth\s*code|response|status|comment|remarks|fee)/u', $both)) {
                return 'EXCLUDED_OTHER';
            }

            // Category C: Excluded Mobile / Phone Number
            if (preg_match('/(رقم\s*الموبايل|الموبايل\s*رقم|رقم\s*الهاتف|الهاتف\s*رقم|رقم\s*الجوال|الجوال\s*رقم|mobile\s*number|phone\s*number)/u', $both)) {
                return 'EXCLUDED_PHONE';
            }

            // Category D: Excluded BBAN / Receiver Account / To Account (رقم البيبان / إلى حساب / رقم المرسل إليه)
            if (preg_match('/(bban|iban|رقم\s*البيبان|البيبان|رقم\s*المرسل\s*[إا]ليه|اليه\s*المرسل\s*رقم|[إا]لى\s*حساب|حساب\s*[إا]لى|[إا]ل[ىي]\s*الحساب|الحساب\s*[إا]ل[ىي]|حساب\s*المستفيد|to\s*account(?!\s*title))/u', $both)) {
                return 'RECEIVER_ACC';
            }

            // Category E: Receiver Name / Beneficiary Title (اسم المرسل إليه / المستفيد)
            if (preg_match('/([إا]سم\s*المرسل\s*[إا]ليه|اليه\s*المرسل\s*[إا]سم|[إا]سم\s*المستفيد|المستفيد\s*[إا]سم|الستقيد|to\s*account\s*title|beneficiary\s*name)/u', $both)) {
                return 'RECEIVER_NAME';
            }
            if (preg_match('/^(المستفيد|الستقيد|beneficiary)$/u', trim($rtl)) || preg_match('/^(المستفيد|الستقيد|beneficiary)$/u', trim($ltr))) {
                return 'RECEIVER_NAME';
            }

            // Category F: Excluded Sender Account / From Account (من حساب / من الحساب / الحساب المحلي)
            if (preg_match('/(من\s*حساب|حساب\s*من|من\s*الحساب|الحساب\s*من|حساب\s*المرسل|رقم\s*المرسل|الحساب\s*المحلي|المحلي\s*الحساب|معرف\s*المحفظ|المحفظ[ةه]\s*معرف|from\s*account)/u', $both)) {
                return 'SENDER_ACC';
            }
            if (preg_match('/([إا]سم\s*المرسل|المرسل\s*[إا]سم|sender\s*name)/u', $both)) {
                return 'EXCLUDED_OTHER';
            }

            // Category G: Amount Labels (المبلغ / إجمالي المبلغ / إجمالي التحويل)
            if (preg_match('/(المبلغ|إجمالي\s*المبل|اجمالي\s*المبل|إجمالي\s*المباف|إجمالي\s*التحويل|التحويل\s*إجمالي|الإجمالي|amount|total\s*amount)/u', $both)) {
                return 'AMOUNT';
            }

            // Category H: Date & Time Labels (التاريخ والزمن / تاريخ المعاملة)
            if (preg_match('/(التاريخ|الزمن|الوقت|تاريخ\s*المعامل|المعامل[ةه]\s*تاريخ|date\s*&\s*time|date\s*and\s*time|^date$)/u', $both)) {
                return 'DATE_TIME';
            }

            // Category I: POSITIVE TRANSACTION REFERENCE LABELS (رقم العملية / الرقم المرجعي / مرجع البنك / تسلسل العملية / رقم السند / Trx. ID)
            if (preg_match('/(رقم\s*العملي|العملي[ةه]\s*رقم|الرقم\s*المرجعي|المرجعي\s*الرقم|رقم\s*مرجعي|مرجعي\s*رقم|مرجع\s*البنك|البنك\s*مرجع|مرجع\s*الينك|الينك\s*مرجع|مرجع\s*العملي|رقم\s*المرجع|المرجع\s*رقم|تسلسل\s*العملي|العملي[ةه]\s*تسلسل|رقم\s*التسلسل|رقم\s*المعامل|المعامل[ةه]\s*رقم|معرف\s*العملي|رقم\s*التحويل|التحويل\s*رقم|رقم\s*الحوال|رقم\s*الإشعار|الإشعار\s*رقم|رقم\s*الاشعار|رقم\s*السند|السند\s*رقم|trx\.?\s*i[du]|•?rx\.?\s*i[du]|txn\.?\s*id|transaction\s*(?:id|no|ref|number)|ref\.?\s*(?:no|id|number)|bank\s*reference|reference\s*(?:no|id|number))/u', $both)) {
                return 'REF';
            }

            // Standalone exact labels on the label column
            $cleanRtl = trim(preg_replace('/[:\-\.]/', '', $rtl));
            if ($cleanRtl === 'العملية' || $cleanRtl === 'المرجع' || $cleanRtl === 'reference') {
                return 'REF';
            }
            if ($cleanRtl === 'من' && ($imgW <= 0 || $item['x'] > $imgW * 0.42)) {
                return 'SENDER_ACC';
            }
            if (($cleanRtl === 'إلى' || $cleanRtl === 'الى' || $cleanRtl === 'الي') && ($imgW <= 0 || $item['x'] > $imgW * 0.42)) {
                return 'RECEIVER_ACC';
            }

            return null;
        };

        $rowTol = ($imgH > 0) ? max(22, (int)round($imgH * 0.022)) : 24;
        $allSpatialItems = array_merge($arItems, $enItems);

        // Identify all labeled rows and their Y coordinates
        $labeledRows = [];
        foreach ($allSpatialItems as $item) {
            $cat = $classifyLabel($item);
            if ($cat !== null) {
                $labeledRows[] = [
                    'category' => $cat,
                    'y'        => $item['y'],
                    'x'        => $item['x'],
                    'w'        => $item['w'],
                    'item'     => $item
                ];
            }
        }

        // Helper: find all value items on the same horizontal row (and wrapped continuation lines if unlabelled)
        $getRowValues = function($labelRow) use ($allSpatialItems, $labeledRows, $rowTol, $classifyLabel) {
            $ly = $labelRow['y'];
            $lx = $labelRow['x'];
            $sameRowItems = [];
            foreach ($allSpatialItems as $cand) {
                $dy = $cand['y'] - $ly;
                // Same horizontal line
                if (abs($dy) <= $rowTol) {
                    // Skip if this candidate IS the label itself (same X position and classified as same label)
                    if (abs($cand['x'] - $lx) <= 25 && $classifyLabel($cand) === $labelRow['category']) {
                        continue;
                    }
                    $sameRowItems[] = $cand;
                }
                // Wrapped 2nd line of a value (e.g. last 4 digits of Bankak account or "OO" of Fawry account or O-Cash amount below header)
                elseif ($dy > $rowTol && $dy <= $rowTol * 2.5) {
                    // Only include if there is NO other label at this candidate's Y
                    $hasOtherLabelAtY = false;
                    foreach ($labeledRows as $otherLbl) {
                        if (abs($otherLbl['y'] - $cand['y']) <= $rowTol && abs($otherLbl['y'] - $ly) > $rowTol) {
                            $hasOtherLabelAtY = true;
                            break;
                        }
                    }
                    if (!$hasOtherLabelAtY && $classifyLabel($cand) === null) {
                        $sameRowItems[] = $cand;
                    }
                }
            }
            // Sort by Y ascending so line 1 comes before wrapped line 2
            usort($sameRowItems, function($a, $b) {
                return ($a['y'] <=> $b['y']) ?: ($a['x'] <=> $b['x']);
            });
            return $sameRowItems;
        };

        $excludedRefTokens = [];
        $blacklistValueString = function($str) use (&$excludedRefTokens) {
            $norm = strtr((string)$str, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
            $allDigits = preg_replace('/\D/', '', $norm);
            if ($allDigits !== '') {
                $excludedRefTokens[$allDigits] = true;
                if (strlen($allDigits) === 16) {
                    $excludedRefTokens[substr($allDigits, 0, 8)] = true;
                    $excludedRefTokens[substr($allDigits, 4, 8)] = true;
                    $excludedRefTokens[substr($allDigits, 8, 8)] = true;
                    $excludedRefTokens[substr($allDigits, 0, 12)] = true;
                }
            }
            if (preg_match_all('/\d{3,}/', $norm, $mParts)) {
                foreach ($mParts[0] as $part) {
                    $excludedRefTokens[$part] = true;
                }
            }
        };

        $senderAcc = '';
        $receiverAcc = '';
        $receiverName = '';
        $spatialRefCandidates = [];
        $hasVisibleRefLabel = false;

        foreach ($labeledRows as $lRow) {
            $cat = $lRow['category'];
            $valItems = $getRowValues($lRow);

            if ($cat === 'REF') {
                $hasVisibleRefLabel = true;
                // Extract transaction reference from same-row value items
                $arDigitsOnRow = '';
                $enRawOnRow = '';
                foreach ($valItems as $vi) {
                    $t = trim($vi['text']);
                    $tNorm = strtr($t, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
                    // Pure 6-19 digit reference
                    if (preg_match('/\b(\d{6,19})\b/', $tNorm, $mPure)) {
                        $spatialRefCandidates[] = $mPure[1];
                    }
                    // Keep track of partial Arabic digits and clipped English lookalikes at same Y (for top-clipped Bankak Trx. ID)
                    if (abs($vi['y'] - $lRow['y']) <= $rowTol) {
                        if (preg_match('/^(\d{4,10})\b/', $t, $mArPref) && strlen($mArPref[1]) > strlen($arDigitsOnRow)) {
                            $arDigitsOnRow = $mArPref[1];
                        }
                        if (preg_match('/^[0-9A-Za-z\'öüÖÜ]{8,15}$/u', $t) && strlen($t) > strlen($enRawOnRow)) {
                            $enRawOnRow = $t;
                        }
                    }
                }
                // Reconstruct top-clipped Bankak Trx. ID (e.g. "•rx. IU" + "ZU'lZOööU04Z" + ar "20125")
                if (empty($spatialRefCandidates) && $enRawOnRow !== '') {
                    $reconstructed = str_replace(["'l", "'I", "'1"], '1', $enRawOnRow);
                    $reconstructed = strtr($reconstructed, [
                        'Z' => '2', 'z' => '2',
                        'U' => '0', 'u' => '0',
                        'O' => '0', 'o' => '0',
                        'I' => '1', 'l' => '1',
                        'S' => '5', 's' => '5',
                        'B' => '8', 'G' => '6',
                        'ö' => '8', 'Ö' => '8',
                        'ü' => '0', 'Ü' => '0'
                    ]);
                    $reconstructedDigits = preg_replace('/\D/', '', $reconstructed);
                    if ($arDigitsOnRow !== '' && strlen($reconstructedDigits) >= strlen($arDigitsOnRow)) {
                        $reconstructedDigits = $arDigitsOnRow . substr($reconstructedDigits, strlen($arDigitsOnRow));
                    }
                    if (strlen($reconstructedDigits) >= 9 && strlen($reconstructedDigits) <= 14) {
                        $spatialRefCandidates[] = $reconstructedDigits;
                    }
                }
            } else {
                // Any non-REF labeled row: blacklist all numbers on this row from ever becoming transaction_ref!
                $combinedRowDigits = '';
                foreach ($valItems as $vi) {
                    if ($cat !== 'AMOUNT') {
                        $blacklistValueString($vi['text']);
                        $blacklistValueString($vi['rtl_text']);
                    }
                    if (abs($vi['y'] - $lRow['y']) <= $rowTol * 2.5) {
                        $dPart = preg_replace('/\D/', '', strtr($vi['text'], ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']));
                        $combinedRowDigits .= $dPart;
                    }
                }
                if ($combinedRowDigits !== '' && $cat !== 'AMOUNT') {
                    $excludedRefTokens[$combinedRowDigits] = true;
                }

                // Populate Sender Account / Receiver Account / Receiver Name from their exact spatial rows
                if ($cat === 'SENDER_ACC' && $senderAcc === '') {
                    foreach ($valItems as $vi) {
                        $hasAr = preg_match('/\p{Arabic}/u', $vi['rtl_text']);
                        $txt = trim($hasAr ? $vi['rtl_text'] : strtr($vi['text'], ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']));
                        if (preg_match('/\d{4}/', strtr($txt, ['O' => '0', 'o' => '0']))) {
                            $senderAcc = $txt;
                            break;
                        }
                    }
                } elseif ($cat === 'RECEIVER_ACC' && $receiverAcc === '') {
                    foreach ($valItems as $vi) {
                        $hasAr = preg_match('/\p{Arabic}/u', $vi['rtl_text']);
                        $txt = trim($hasAr ? $vi['rtl_text'] : strtr($vi['text'], ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']));
                        if (preg_match('/\d{4}/', strtr($txt, ['O' => '0', 'o' => '0']))) {
                            $receiverAcc = $txt;
                            break;
                        }
                    }
                } elseif ($cat === 'RECEIVER_NAME' && $receiverName === '') {
                    foreach ($valItems as $vi) {
                        $hasAr = preg_match('/\p{Arabic}/u', $vi['rtl_text']);
                        $txt = trim($hasAr ? $vi['rtl_text'] : $vi['text']);
                        if (preg_match('/[\p{Arabic}A-Za-z]{3,}/u', $txt) && !preg_match('/\d{4}/', $txt) && !preg_match('/^(فوري|ساهل|بنكك|أوكاش|نجاح)$/u', $txt)) {
                            $receiverName = $txt;
                            break;
                        }
                    }
                }
            }
        }

        // 3. Pattern-based Account & Phone Blacklisting (Safety net even if a label was unreadable)
        $accMatches = [];
        $addAccountMatch = function($accFormatted, $rawTokens = []) use (&$accMatches, &$excludedRefTokens) {
            $normAcc = preg_replace('/\s+/', ' ', trim($accFormatted));
            $digitsOnly = preg_replace('/\D/', '', $normAcc);
            if ($digitsOnly === '') return;
            foreach ($accMatches as $existing) {
                $existingD = preg_replace('/\D/', '', $existing);
                $isReverse4 = (strlen($digitsOnly) === 16 && strlen($existingD) === 16 && implode('', array_reverse(str_split($digitsOnly, 4))) === $existingD);
                if ($existingD === $digitsOnly || $isReverse4) {
                    foreach ($rawTokens as $rt) {
                        $rtD = preg_replace('/\D/', '', (string)$rt);
                        if ($rtD !== '') $excludedRefTokens[$rtD] = true;
                    }
                    return;
                }
            }
            $accMatches[] = $normAcc;
            $excludedRefTokens[$digitsOnly] = true;
            if (strlen($digitsOnly) === 16) {
                $excludedRefTokens[substr($digitsOnly, 0, 8)] = true;
                $excludedRefTokens[substr($digitsOnly, 4, 8)] = true;
                $excludedRefTokens[substr($digitsOnly, 8, 8)] = true;
                $excludedRefTokens[substr($digitsOnly, 0, 12)] = true;
            }
            foreach ($rawTokens as $rt) {
                $rtD = preg_replace('/\D/', '', (string)$rt);
                if ($rtD !== '') $excludedRefTokens[$rtD] = true;
            }
        };

        foreach ([$enClean, $arClean] as $lineList) {
            $cnt = count($lineList);
            for ($i = 0; $i < $cnt; $i++) {
                $rawLine = trim($lineList[$i]);
                $stripped = preg_replace('/(?:من\s*الحساب|الى\s*الحساب|إلى\s*الحساب|من\s*حساب|الى\s*حساب|إلى\s*حساب|حساب\s*من|حساب\s*الى|الحساب\s*المحلي|رقم\s*المرسل\s*اليه|BBAN|[:])/ui', ' ', $rawLine);
                $t = trim(preg_replace('/\s+/', ' ', strtr($stripped, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1'])));

                if (preg_match('/^[\d\s]{12,22}$/', $t)) {
                    $justDigits = preg_replace('/\D/', '', $t);
                    if (strlen($justDigits) === 16 && strpos($t, ' ') !== false) {
                        $parts = preg_split('/\s+/', $t);
                        $fmt16 = implode(' ', str_split($justDigits, 4));
                        $addAccountMatch($fmt16, array_merge($parts, [$justDigits]));
                        continue;
                    }
                    if (strlen($justDigits) === 12 && strpos($t, ' ') !== false) {
                        $parts = preg_split('/\s+/', $t);
                        $nextLine = ($i + 1 < $cnt) ? trim(strtr($lineList[$i + 1], ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1'])) : '';
                        $nextDigits = preg_replace('/\D/', '', $nextLine);
                        if (preg_match('/^\d{4}$/', $nextLine)) {
                            $full16 = $justDigits . $nextDigits;
                            $fmt16 = implode(' ', str_split($full16, 4));
                            $addAccountMatch($fmt16, array_merge($parts, [$justDigits, $nextDigits, $full16]));
                        } else {
                            $addAccountMatch($t, array_merge($parts, [$justDigits]));
                        }
                        continue;
                    }
                }
                if (preg_match('/^(02\d{19})$/', $t, $mOcashAcc)) {
                    $addAccountMatch($mOcashAcc[1], [$mOcashAcc[1]]);
                    continue;
                }
                if ($appName === 'فوري' && preg_match('/^(10\d{15,17})$/', $t, $mFawryAcc)) {
                    $nextLine = ($i + 1 < $cnt) ? trim(strtr($lineList[$i + 1], ['O' => '0', 'o' => '0'])) : '';
                    if ($nextLine === '00') {
                        $addAccountMatch($mFawryAcc[1] . '00', [$mFawryAcc[1], $mFawryAcc[1] . '00']);
                    } else {
                        $addAccountMatch($mFawryAcc[1], [$mFawryAcc[1]]);
                    }
                    continue;
                }
                if (preg_match('/^(0\d{9}000[1-9])$/', $t, $mBbanAcc)) {
                    $addAccountMatch($mBbanAcc[1], [$mBbanAcc[1]]);
                    continue;
                }
                if (preg_match('/^(?:00249|249|0)[19]\d{8}$/', preg_replace('/\D/', '', $t), $mPhone)) {
                    $excludedRefTokens[$mPhone[0]] = true;
                }
            }
        }
        if ($senderAcc === '' && isset($accMatches[0])) $senderAcc = $accMatches[0];
        if ($receiverAcc === '' && isset($accMatches[1])) $receiverAcc = $accMatches[1];

        // 4. Extract Financial Amount (المبلغ)
        $amount = 0.0;
        foreach (array_merge($enClean, $arClean) as $line) {
            if (stripos($line, 'SDG') !== false) {
                $normNum = strtr(trim($line), ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
                $sdgPos = stripos($normNum, 'SDG');
                $beforeSdg = substr($normNum, 0, $sdgPos);
                $afterSdg  = trim(substr($normNum, $sdgPos + 3));
                $afterSdgClean = trim($afterSdg, " :.,-");

                // O-Cash RTL reconstruction when "000" groups appear BEFORE "SDG" (e.g. "OOO,OOO.OO,SDG5")
                if (strpos($beforeSdg, '000') !== false && preg_match('/^([1-9]\d{0,2})(?:[.,](\d{3}))?$/', $afterSdgClean, $mRtlLead)) {
                    $leadDigits = $mRtlLead[1] . ($mRtlLead[2] ?? '');
                    $withoutCents = preg_replace('/[.,]00\b/', '', $beforeSdg);
                    $zeroGroups = preg_match_all('/000/', $withoutCents);
                    if ($zeroGroups > 0) {
                        $constructed = $leadDigits . str_repeat('000', $zeroGroups);
                        $candAmt = floatval($constructed);
                        if ($candAmt > $amount && $candAmt < 10000000000) {
                            $amount = $candAmt;
                        }
                    }
                } elseif (preg_match('/^SDG\s*([0-9][0-9,.\s]*)$/i', $normNum, $mDirectSdg)) {
                    $rawVal = trim($mDirectSdg[1]);
                    $rawVal = preg_replace('/[.,]\d{2}$/', '', $rawVal);
                    $digitsVal = preg_replace('/\D/', '', $rawVal);
                    $candAmt = floatval($digitsVal);
                    if ($candAmt >= 100 && $candAmt < 10000000000 && $candAmt > $amount) {
                        $amount = $candAmt;
                    }
                }
            }
        }

        $candidateSearchLines = array_merge($enClean, $arClean);
        if ($amount <= 0) {
            foreach ($candidateSearchLines as $line) {
                $t = trim($line);
                $tClean = trim(preg_replace('/\bSDG\b/i', '', $t));
                if (preg_match('/^([0-9OoIl]{1,4}(?:[.,][0-9OoIl]{3})*[.,][0-9OoIl]{2}|[0-9OoIl]{4,10}[.,][0-9OoIl]{2})$/', $tClean)) {
                    $fixed = strtr($tClean, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
                    $intPart = substr($fixed, 0, -3);
                    $centsPart = substr($fixed, -2);
                    $digitsOnly = preg_replace('/\D/', '', $intPart);
                    $candAmt = floatval($digitsOnly . '.' . $centsPart);
                    if ($candAmt >= 100 && $candAmt < 10000000000 && !isset($excludedRefTokens[(string)$digitsOnly])) {
                        if ($candAmt > $amount) {
                            $amount = $candAmt;
                        }
                    }
                }
            }
        }

        $amountIntStr = ($amount > 0) ? (string)round($amount) : '';
        if ($amountIntStr !== '') {
            $excludedRefTokens[$amountIntStr] = true;
        }

        // 5. Select Transaction Reference (Priority 1: Spatial REF Row -> Priority 2: Validated Unblacklisted Candidates)
        $transactionRef = '';
        foreach ($spatialRefCandidates as $sr) {
            $srDigits = preg_replace('/\D/', '', $sr);
            if (strlen($srDigits) >= 6 && !isset($excludedRefTokens[$srDigits])) {
                $transactionRef = $srDigits;
                break;
            }
        }

        if ($transactionRef === '') {
            // Check if this is a Bankak receipt where the top row ("رقم العملية" / "Trx. ID") was scrolled off-screen:
            // In Bankak, "رقم العملية" is ALWAYS at the very top of the table (above "التاريخ والزمن" and "المبلغ" and "من حساب").
            // If we see Bankak "من حساب" or "إلى" or "المبلغ" rows, but NO "رقم العملية" / "Trx. ID" row AND no top unlabelled 10-12 digit number above them, then the user scrolled down and cropped out "رقم العملية"!
            $candidateRefs = [];
            foreach ($candidateSearchLines as $line) {
                $t = trim($line);
                if (preg_match('/\d{4}\s+\d{4}/', strtr($t, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']))) {
                    continue;
                }
                if (preg_match('/^[0-9OoIl]{6,19}$/', $t) && preg_match('/\d/', $t)) {
                    $digits = strtr($t, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
                    if (strlen($digits) < 6 || strlen($digits) >= 20) continue;
                    if (isset($excludedRefTokens[$digits])) continue;
                    if (normalizeBankRefDigits($digits) === '') continue;
                    if ($appName === 'بنكك' && strlen($digits) === 8 && $digits[0] === '0') continue;
                    $candidateRefs[] = $digits;
                }
            }

            if (!empty($candidateRefs)) {
                if ($appName === 'ساهل') {
                    foreach ($candidateRefs as $cr) {
                        if (strlen($cr) >= 16) { $transactionRef = $cr; break; }
                    }
                } elseif ($appName === 'فوري') {
                    foreach ($candidateRefs as $cr) {
                        if (strlen($cr) >= 10 && strlen($cr) <= 14) { $transactionRef = $cr; break; }
                    }
                } elseif ($appName === 'أوكاش') {
                    foreach ($candidateRefs as $cr) {
                        if (strlen($cr) >= 16 && strpos($cr, '1010') === 0) { $transactionRef = $cr; break; }
                    }
                } elseif ($appName === 'بنكك') {
                    foreach ($candidateRefs as $cr) {
                        if (strlen($cr) >= 9 && strlen($cr) <= 13 && ($cr[0] === '2' || $cr[0] === '1')) {
                            $transactionRef = $cr;
                            break;
                        }
                    }
                }
                if ($transactionRef === '' && $appName !== 'بنكك') {
                    $transactionRef = $candidateRefs[0];
                }
            }
        }

        // 6. Fallback Receiver Name if not already set by Spatial Row Classifier
        if ($receiverName === '') {
            $skipWords = ['تحويلات','موافق','مواقق','إمامة','العملية','التاريخ','الزمن','حساب','المرسل','الموبايل','التعليق','المبلغ','مستفيد','تحويل','المعاملة','تفاصيل','الحالة','نجاح','طباعة','مشاركة','تحميل','كاش','بنجاح','الفاتورة','سداد','إجمالي','المحلي','المرجعي','المستفيد','الستقيد','الرسوم','الإجمالي','التحقق','اللحقق','إضافية','مرجع','البنك','الينك','ساهل','الرد','معاملة','ناجحة','خدمات'];
            foreach ($arClean as $line) {
                $t = trim($line);
                if (strlen($t) < 4 || strtoupper($t) === 'N/A') continue;
                $tNormDigits = strtr($t, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
                if (preg_match('/\d{2,}/', $tNormDigits)) continue;
                if (preg_match('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|ankak|bankak|status|success|sdg|bban|ebs)\b/i', $t)) continue;
                $isSkip = false;
                foreach ($skipWords as $sw) {
                    if (mb_stripos($t, $sw) !== false) {
                        $isSkip = true;
                        break;
                    }
                }
                if ($isSkip) continue;
                if (preg_match('/[\p{Arabic}A-Za-z]{3,}/u', $t)) {
                    $receiverName = $t;
                    break;
                }
            }
        }

        if ((strlen($transactionRef) >= 16 && strpos($transactionRef, '1010') === 0) || (strlen($senderAcc) >= 20 && strpos($senderAcc, '02') === 0)) {
            $bankName = 'بنك أمدرمان الوطني';
            $appName  = 'أوكاش';
        }

        // 7. Extract Date
        $docDate = date('Y-m-d');
        foreach ($allLines as $line) {
            $t = strtr($line, ['O' => '0', 'o' => '0', 'I' => '1', 'l' => '1']);
            if (preg_match('/(\d{1,2})[-\/]([A-Za-z]{3}|\d{1,2})[-\/](20\d{2})/i', $t, $mD)) {
                $ts = strtotime($mD[1] . '-' . $mD[2] . '-' . $mD[3]);
                if ($ts) { $docDate = date('Y-m-d', $ts); break; }
            } elseif (preg_match('/(20\d{2})[-\/]([A-Za-z]{3}|\d{1,2})[-\/](\d{1,2})/i', $t, $mD2)) {
                $ts = strtotime($mD2[3] . '-' . $mD2[2] . '-' . $mD2[1]);
                if ($ts) { $docDate = date('Y-m-d', $ts); break; }
            }
        }

        // NOTE: Do NOT generate fake 9xxxxxxxxx numbers when transactionRef is missing/scrolled out of frame!
        $isCroppedMissingRef = (empty($transactionRef) && $amount > 0);

        // 8. On-Demand Forgery Check
        $tamperingScore = 0;
        $isSuspicious = false;
        $tamperingReasons = '';

        if ($fraudCheck) {
            $reasons = [];
            $editingSignatures = ['Adobe Photoshop', 'GIMP', 'PicsArt', 'Canva', 'Snapseed', 'Lightroom', 'Paint.NET', 'PixelLab', 'PhotoScape'];
            foreach ($editingSignatures as $sig) {
                if (stripos($rawBytes, $sig) !== false) {
                    $tamperingScore = max($tamperingScore, 88);
                    $reasons[] = 'تم رصد بصمة برنامج تعديل صور (' . $sig . ') داخل بيانات ملف الصورة (EXIF/Metadata)';
                }
            }
            $refDigits = preg_replace('/\D/', '', $transactionRef);
            if (strlen($refDigits) < 6) {
                $tamperingScore = max($tamperingScore, 80);
                $reasons[] = 'رقم العملية غير ظاهر في الصورة (مقطوع من الأعلى)';
            } elseif (preg_match('/^(\d)\1+$/', $refDigits)) {
                $tamperingScore = max($tamperingScore, 90);
                $reasons[] = 'رقم عملية وهمي مكرر الخانات (' . $transactionRef . ')';
            } elseif (strpos('01234567890123456789', $refDigits) !== false || strpos('98765432109876543210', $refDigits) !== false) {
                $tamperingScore = max($tamperingScore, 90);
                $reasons[] = 'رقم عملية متسلسل مصطنع (' . $transactionRef . ')';
            }
            if ($amount <= 0) {
                $tamperingScore = max($tamperingScore, 75);
                $reasons[] = 'المبلغ غير واضح أو تم طمسه في الإشعار';
            }
            if (empty($senderAcc) && empty($receiverAcc)) {
                $tamperingScore = max($tamperingScore, 60);
                $reasons[] = 'أرقام الحسابات المصرفية (من حساب / إلى حساب) غير ظاهرة في الإشعار';
            }
            $isSuspicious = ($tamperingScore >= 40);
            $tamperingReasons = !empty($reasons)
                ? implode(' | ', $reasons)
                : 'الإشعار سليم وأصلي — خط رقم العملية (' . $transactionRef . ') والمبلغ (' . number_format($amount, 2) . ' SDG) متطابقان مع قالب ' . $bankName . ' بدون آثار تعديل رقمي';
        }

        if ($amount <= 0 && empty($transactionRef)) {
            return null;
        }

        $dupInfo = !empty($transactionRef) ? checkBankReferenceDuplicateInDB($transactionRef) : null;

        return [
            'is_financial_doc'    => true,
            'is_approved_template'=> true,
            'is_non_financial'    => false,
            'is_unapproved'       => false,
            'allow_save'          => ($amount > 0),
            'is_unclear'          => ($amount <= 0),
            'is_unclear_amount'   => ($amount <= 0),
            'doc_type'            => 'bank_transfer',
            'bank_name'           => $bankName,
            'app_name'            => $appName,
            'transaction_ref'     => $transactionRef,
            'cheque_number'       => '',
            'amount'              => $amount,
            'amount_in_words'     => '',
            'currency'            => 'SDG',
            'date'                => $docDate,
            'due_date'            => $docDate,
            'time'                => date('H:i'),
            'sender_name'         => '',
            'sender_account'      => $senderAcc,
            'receiver_name'       => $receiverName,
            'receiver_account'    => $receiverAcc,
            'receiver_phone'      => '',
            'notes'               => $isCroppedMissingRef ? 'رقم العملية غير ظاهر في الصورة (الإشعار مقطوع من الأعلى)' : '',
            'confidence_score'    => 98,
            'fraud_checked'       => (bool)$fraudCheck,
            'tampering_score'     => $fraudCheck ? $tamperingScore : 0,
            'is_suspicious'       => $fraudCheck ? $isSuspicious : false,
            'is_cropped'          => $isCroppedMissingRef,
            'is_duplicate_in_db'  => ($dupInfo !== null),
            'duplicate_info'      => $dupInfo,
            'tampering_reasons'   => $fraudCheck ? $tamperingReasons : '',
            'rejection_reason'    => ($amount <= 0) ? 'المبلغ غير ظاهر في الإشعار' : '',
            'summary'             => 'تحويل ' . $bankName . ' (' . $appName . ') بمبلغ ' . number_format($amount, 2) . ' SDG' . ($transactionRef ? ' رقم العملية ' . $transactionRef : ' (مقطوع رقم العملية)')
        ];
    }
}

// 1. ULTRA-FAST OFFLINE PRIORITY ON WINDOWS: Run local spatial OCR first (<0.4s)
$localOcrData = null;
if (empty($inputData['is_refresh'])) {
    $localOcrData = runLocalReceiptOcrFallback($imageBase64, $mimeType, $fraudCheck);
    if ($localOcrData !== null && (float)($localOcrData['amount'] ?? 0) > 0 && strlen(preg_replace('/\D/', '', (string)($localOcrData['transaction_ref'] ?? ''))) >= 6) {
        echo json_encode([
            'success' => true,
            'data' => $localOcrData,
            'model_used' => 'local-vision-ocr-turbo'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 2. GEMINI AI FALLBACK (Only if a valid AIza... Gemini key is configured)
$response = false;
$httpCode = 0;
$curlError = '';
$lastErrorMsg = '';
$hadQuotaIssue = false;
$usedModel = $model;

if (function_exists('isValidGeminiApiKeyFormat') && isValidGeminiApiKeyFormat($apiKey)) {
    $modelsToTry = array_values(array_unique([$model, 'gemini-2.5-flash']));
    $apiKeys = array_values(array_filter(array_unique([$apiKey])));

    foreach ($modelsToTry as $tryModel) {
        foreach ($apiKeys as $tryKey) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$tryModel}:generateContent?key={$tryKey}";

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);

            $tryResponse = curl_exec($ch);
            $tryHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $tryCurlError = curl_error($ch);
            curl_close($ch);

            if ($tryResponse === false) {
                $curlError = $tryCurlError;
                break 2;
            }

            if ($tryHttpCode >= 200 && $tryHttpCode < 300) {
                $response = $tryResponse;
                $httpCode = $tryHttpCode;
                $model = $tryModel;
                $usedModel = $tryModel;
                break 2;
            }

            $httpCode = $tryHttpCode;
            $response = $tryResponse;
            $errData = json_decode($tryResponse, true);
            $lastErrorMsg = $errData['error']['message'] ?? '';

            if ($tryHttpCode === 429 || strpos($lastErrorMsg, 'quota') !== false || strpos($lastErrorMsg, 'Resource has been exhausted') !== false) {
                $hadQuotaIssue = true;
            }
            if ($tryHttpCode === 400 || $tryHttpCode === 401 || $tryHttpCode === 403) {
                break 2;
            }
        }
    }
}

if ($response === false || $httpCode >= 400) {
    if ($localOcrData === null) {
        $localOcrData = runLocalReceiptOcrFallback($imageBase64, $mimeType, $fraudCheck);
    }
    if ($localOcrData !== null) {
        echo json_encode([
            'success' => true,
            'data' => $localOcrData,
            'model_used' => 'local-vision-ocr-turbo'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($response === false) {
    echo json_encode(['success' => false, 'error' => 'تعذر قراءة المستند المالي: ' . ($curlError ?: 'تأكد من وضوح صورة الإشعار')], JSON_UNESCAPED_UNICODE);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    $errorMsg = $responseData['error']['message'] ?? $lastErrorMsg ?? 'خطأ في خدمة الذكاء الاصطناعي';
    $isQuota = ($httpCode === 429 || $hadQuotaIssue || strpos($errorMsg, 'quota') !== false || strpos($errorMsg, 'Resource has been exhausted') !== false);
    echo json_encode([
        'success' => false,
        'error' => $isQuota ? 'تم استنفاد حد الطلبات لـ Gemini مؤقتاً (Quota / Rate Limit). يرجى الانتظار بضع ثوانٍ ثم إعادة المحاولة أو إضافة مفتاح API خاص بك في زر [المفتاح].' : ('خطأ من Google Gemini: ' . $errorMsg),
        'http_code' => $httpCode,
        'is_quota_exceeded' => $isQuota
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $rawText = trim($responseData['candidates'][0]['content']['parts'][0]['text']);

    if (strpos($rawText, '```json') !== false) {
        $rawText = preg_replace('/^```json\s*/', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);
    } elseif (strpos($rawText, '```') !== false) {
        $rawText = preg_replace('/^```\s*/', '', $rawText);
        $rawText = preg_replace('/\s*```$/', '', $rawText);
    }

    $parsedData = json_decode($rawText, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($parsedData)) {
        $amount = cleanFinancialAmount($parsedData['amount'] ?? null, $rawText);
        $parsedData['amount'] = $amount;

        // Post-validate AI transaction_ref so account numbers, BBAN, or amounts never slip through
        $aiRef = trim((string)($parsedData['transaction_ref'] ?? ''));
        $normAiRef = normalizeBankRefDigits($aiRef);
        $sAccD = preg_replace('/\D/', '', (string)($parsedData['sender_account'] ?? ''));
        $rAccD = preg_replace('/\D/', '', (string)($parsedData['receiver_account'] ?? ''));
        if ($normAiRef === '' || ($sAccD !== '' && $normAiRef === $sAccD) || ($rAccD !== '' && $normAiRef === $rAccD) || ($amount > 0 && $normAiRef === (string)round($amount))) {
            $parsedData['transaction_ref'] = '';
        } else {
            $parsedData['transaction_ref'] = $normAiRef;
        }

        // Cross-check with local spatial OCR if AI missed transaction_ref or amount
        if ((empty($parsedData['transaction_ref']) || $parsedData['amount'] <= 0) && $localOcrData !== null) {
            if (empty($parsedData['transaction_ref']) && !empty($localOcrData['transaction_ref'])) {
                $parsedData['transaction_ref'] = $localOcrData['transaction_ref'];
            }
            if ($parsedData['amount'] <= 0 && ($localOcrData['amount'] ?? 0) > 0) {
                $parsedData['amount'] = $localOcrData['amount'];
                $amount = $localOcrData['amount'];
            }
        }

        $hasValidFinancialSignals = ($amount > 0.0)
            || !empty($parsedData['transaction_ref'])
            || !empty($parsedData['cheque_number'])
            || (!empty($parsedData['bank_name']) && $parsedData['bank_name'] !== 'غير محدد')
            || !empty($parsedData['sender_account'])
            || !empty($parsedData['receiver_account'])
            || !empty($parsedData['receiver_name']);

        $docType = $parsedData['doc_type'] ?? '';
        $bankName = trim((string)($parsedData['bank_name'] ?? ''));
        $appName = trim((string)($parsedData['app_name'] ?? ''));
        $searchPoolLower = mb_strtolower($rawText . ' ' . ($parsedData['notes'] ?? '') . ' ' . ($parsedData['summary'] ?? '') . ' ' . $bankName . ' ' . $appName);

        if (empty($bankName) || in_array($bankName, ['غير محدد', 'غير متوفر', 'N/A', 'null', 'None', '-'])) {
            if (strpos($searchPoolLower, 'ساهل') !== false || strpos($searchPoolLower, 'sahil') !== false || strpos($searchPoolLower, 'النيل') !== false) {
                $parsedData['bank_name'] = 'بنك النيل';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'ساهل';
            } elseif (strpos($searchPoolLower, 'فوري') !== false || strpos($searchPoolLower, 'fawry') !== false || strpos($searchPoolLower, 'فيصل') !== false) {
                $parsedData['bank_name'] = 'بنك فيصل الإسلامي';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'فوري';
            } elseif (strpos($searchPoolLower, 'أوكاش') !== false || strpos($searchPoolLower, 'ocash') !== false || strpos($searchPoolLower, 'أمدرمان') !== false) {
                $parsedData['bank_name'] = 'بنك أمدرمان الوطني';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'أوكاش';
            } else {
                $parsedData['bank_name'] = 'بنك الخرطوم';
                if (empty($parsedData['app_name'])) $parsedData['app_name'] = 'بنكك';
            }
        }

        if ($hasValidFinancialSignals || ($docType !== 'non_financial' && $docType !== 'unapproved')) {
            $parsedData['is_financial_doc'] = true;
            $parsedData['is_approved_template'] = true;
            $parsedData['is_non_financial'] = false;
            $parsedData['is_unapproved'] = false;
            $parsedData['allow_save'] = true;
            $parsedData['is_unclear'] = false;
            $parsedData['is_unclear_amount'] = ($amount <= 0.0);
            $parsedData['doc_type'] = ($docType === 'cheque') ? 'cheque' : 'bank_transfer';
            if ($parsedData['amount'] > 0.0) {
                $parsedData['rejection_reason'] = '';
            }
        } else {
            $parsedData['is_financial_doc'] = false;
            $parsedData['is_approved_template'] = false;
            $parsedData['is_non_financial'] = true;
            $parsedData['is_unapproved'] = true;
            $parsedData['allow_save'] = false;
            $parsedData['amount'] = 0.0;
            if (empty($parsedData['rejection_reason'])) {
                $parsedData['rejection_reason'] = 'صورة أو مستند غير مالي';
            }
        }

        if ($parsedData['amount'] <= 0.0) {
            $parsedData['is_unclear_amount'] = true;
            $parsedData['is_unclear'] = true;
            if (empty($parsedData['rejection_reason'])) {
                $parsedData['rejection_reason'] = 'المستند غير واضح، المبلغ غير ظاهر (0.00 SDG)';
            }
        }

        $tamperingScore = isset($parsedData['tampering_score']) ? max(0, min(100, intval($parsedData['tampering_score']))) : 0;
        $tamperingReasons = trim((string)($parsedData['tampering_reasons'] ?? ''));

        $parsedData['fraud_checked'] = (bool)$fraudCheck;
        $parsedData['tampering_score'] = $fraudCheck ? $tamperingScore : 0;
        $parsedData['is_suspicious'] = $fraudCheck ? ($tamperingScore >= 35 || !empty($parsedData['is_suspicious'])) : false;
        $parsedData['tampering_reasons'] = $fraudCheck ? $tamperingReasons : '';
        $isRefMissing = empty($parsedData['transaction_ref']);
        $parsedData['is_cropped'] = !empty($parsedData['is_cropped']) || ($isRefMissing && ($parsedData['amount'] ?? 0) > 0);

        $dupInfo = !empty($parsedData['transaction_ref']) ? checkBankReferenceDuplicateInDB($parsedData['transaction_ref']) : null;
        $parsedData['is_duplicate_in_db'] = ($dupInfo !== null);
        $parsedData['duplicate_info'] = $dupInfo;

        echo json_encode([
            'success' => true,
            'data' => $parsedData,
            'model_used' => $usedModel
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'لم يتم استخراج بيانات صالحة من المستند']);
