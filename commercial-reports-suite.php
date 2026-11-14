<?php
$page_title = 'منظومة التقارير التجارية والمخزنية والتسديدات والأقساط والصلاحية';
require_once __DIR__ . '/db_bridge.php';

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;
$tid = (int)($GLOBAL_TENANT_ID ?: 1);
$rep = $_GET['rep'] ?? 'items_catalog';

if (!function_exists('crsFetch')) {
function crsFetch(PDO $pdo, string $tbl, $tid): array {
    $out = [];
    try {
        $chk = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tbl));
        if (!$chk || !$chk->fetchColumn()) return [];
        $st = $pdo->prepare("SELECT data FROM `$tbl` WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
        $st->execute([$tid]);
        while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
            $d = json_decode($r['data'] ?? '', true);
            if (is_array($d) && empty($d['deleted']) && empty($d['isCancelled'])) $out[] = $d;
        }
    } catch (Throwable $e) {}
    return $out;
}
}

$items = array_merge(crsFetch($pdo, 'items', $tid), crsFetch($pdo, 'inventory', $tid));
$sales = crsFetch($pdo, 'sales_invoices', $tid);
$purchases = crsFetch($pdo, 'purchase_invoices', $tid);
$customers = crsFetch($pdo, 'customers', $tid);
$suppliers = crsFetch($pdo, 'suppliers', $tid);
$receipts = array_merge(crsFetch($pdo, 'receipts', $tid), crsFetch($pdo, 'single_receipts', $tid));
$payments = array_merge(crsFetch($pdo, 'single_payments', $tid), crsFetch($pdo, 'payments', $tid));
$grns = crsFetch($pdo, 'goods_receipts', $tid);

$catalog = [
    'items_catalog' => ['📦 قائمة الأصناف', 'دليل الأصناف الشامل والوحدات وأسعار الشراء والبيع والرصيد الحالي'],
    'items_detailed_movement' => ['🔍 الحركة التفصيلية للأصناف', 'سجل حركات الوارد والمنصرف لكل صنف فاتورةً بفاتورة بالتاريخ والمرجع'],
    'items_commercial_movement' => ['📊 الحركة التجارية للأصناف', 'إجمالي كميات وقيم المشتريات والمبيعات وصافي الحركة التجارية لكل صنف'],
    'items_summary_movement' => ['📋 حركة الأصناف (التجميعي)', 'ملخص الرصيد الافتتاحي والوارد والمنصرف والرصيد الفعلي المتبقي'],
    'items_profitability' => ['💰 أرباح الأصناف', 'تحليل إيراد المبيعات وتكلفة البضاعة المباعة ومجمل الربح ونسبة الهامش لكل صنف'],
    'historical_expiry_inventory' => ['⏳ جرد تاريخي للصلاحية', 'تتبع تواريخ الإنتاج والانتهاء والأيام المتبقية وحالة صلاحية الأصناف بالمخازن'],
    'linked_references_audit' => ['🔗 حركة المراجع المرتبطة', 'تتبع الترابط المرجعي بين الفواتير وأذونات المخازن وسندات التسديد والقيود'],
    'detailed_account_statement' => ['📑 كشف الحساب التفصيلي', 'كشف تفصيلي متسلسل زمنياً لكافة حركات العملاء والموردين (مدين / دائن / رصيد)'],
    'invoices_aging_all' => ['⌛ أعمار الفواتير (الشامل)', 'تصنيف أعمار الذمم والفواتير غير المسددة (0-30، 31-60، 61-90، +90 يوماً)'],
    'sales_commercial_movement' => ['🛒 الحركة التجارية للمبيعات', 'سجل تحليلي شامل لفواتير المبيعات النقدية والآجلة والمخازن والمدفوع والمتبقي'],
    'customer_statement' => ['👤 كشف حساب عميل', 'حركة فواتير وتسديدات العملاء التفصيلية والرصيد التراكمي'],
    'customers_balances' => ['💳 أرصدة العملاء', 'ملخص أرصدة العملاء وسقوف الائتمان والرصيد المتاح لكل عميل'],
    'sales_invoices_aging' => ['📅 أعمار فواتير المبيعات', 'تحليل تقادم فواتير المبيعات الآجلة المتبقية على العملاء بالأيام'],
    'customers_payments' => ['💵 تسديدات العملاء', 'سجل كافة المقبوضات والتسديدات النقدية والبنكية المحصلة من العملاء'],
    'customers_installments' => ['🗓️ تسديدات أقساط فواتير العملاء', 'متابعة الفواتير المقسطة والدفعات المسددة والمتبقية ونسبة سداد كل فاتورة عميل'],
    'purchases_commercial_movement' => ['🚚 الحركة التجارية للمشتريات', 'سجل تحليلي شامل لفواتير المشتريات وحالة الاستلام المخزني والمسدد والمتبقي'],
    'purchases_invoices_aging' => ['📆 أعمار فواتير المشتريات', 'تحليل تقادم فواتير المشتريات الآجلة المستحقة للموردين بالأيام'],
    'supplier_statement' => ['🏭 كشف حساب المورد', 'حركة فواتير المشتريات وتسديدات الموردين التفصيلية والرصيد التراكمي'],
    'suppliers_balances' => ['🏦 أرصدة الموردين', 'ملخص الأرصدة الافتتاحية والحالية المستحقة لكافة الموردين'],
    'suppliers_payments' => ['💸 تسديدات الموردين', 'سجل كافة المدفوعات النقدية والتحويلات البنكية المسددة للموردين'],
    'suppliers_installments' => ['🧾 تسديدات أقساط فواتير الموردين', 'متابعة دفعات وأقساط فواتير المشتريات المسددة والمتبقية لكل مورد'],
];
if (!isset($catalog[$rep])) $rep = 'items_catalog';
[$repTitle, $repDesc] = $catalog[$rep];

// Build item lookup & movements
$itemStats = [];
foreach ($items as $it) {
    $nm = (string)($it['name'] ?? 'صنف');
    $buy = (float)($it['costPrice'] ?? $it['buyPrice'] ?? $it['purchasePrice'] ?? $it['openingValue'] ?? 0);
    $sell = (float)($it['sellingPrice'] ?? 0);
    $openQty = (float)($it['openingQty'] ?? 0);
    $curStock = 0.0;
    if (!empty($it['units']) && is_array($it['units'])) {
        foreach ($it['units'] as $u) $curStock += ((float)($u['stock'] ?? 0)) * max(1.0, (float)($u['factor'] ?? 1));
    } else {
        $curStock = (float)($it['stock'] ?? $it['currentStock'] ?? $openQty);
    }
    $expDate = (string)($it['expiryDate'] ?? $it['expDate'] ?? '');
    $itemStats[$nm] = ['name'=>$nm,'code'=>$it['code']??$it['barcode']??'-','unit'=>$it['baseUnit']??($it['units'][0]['name']??'وحدة'),'buy'=>$buy,'sell'=>$sell,'open'=>$openQty,'inQty'=>0.0,'inVal'=>0.0,'outQty'=>0.0,'outVal'=>0.0,'stock'=>$curStock,'exp'=>$expDate];
}

$detailedItemLines = [];
foreach ($purchases as $p) {
    $dt = substr((string)($p['date'] ?? date('Y-m-d')), 0, 10);
    $no = (string)($p['invoiceNumber'] ?? $p['number'] ?? 'PUR');
    $wh = (string)($p['warehouseName'] ?? $p['warehouse'] ?? 'المخزن الرئيسي');
    foreach (($p['items'] ?? []) as $ln) {
        $nm = (string)($ln['name'] ?? $ln['itemName'] ?? 'صنف');
        $q = (float)($ln['qty'] ?? $ln['quantity'] ?? 0);
        $pr = (float)($ln['price'] ?? $ln['buyPrice'] ?? 0);
        $tot = $q * $pr;
        if (!isset($itemStats[$nm])) $itemStats[$nm] = ['name'=>$nm,'code'=>'-','unit'=>'وحدة','buy'=>$pr,'sell'=>$pr*1.2,'open'=>0,'inQty'=>0,'inVal'=>0,'outQty'=>0,'outVal'=>0,'stock'=>0,'exp'=>''];
        $itemStats[$nm]['inQty'] += $q;
        $itemStats[$nm]['inVal'] += $tot;
        $detailedItemLines[] = [$dt, 'وارد مشتريات', $no, $nm, $wh, $q, 0.0, $pr, $tot];
    }
}
foreach ($sales as $s) {
    $dt = substr((string)($s['date'] ?? date('Y-m-d')), 0, 10);
    $no = (string)($s['invoiceNumber'] ?? $s['number'] ?? 'SALE');
    $wh = (string)($s['warehouseName'] ?? $s['deliveredWarehouseName'] ?? 'المخزن الرئيسي');
    foreach (($s['items'] ?? []) as $ln) {
        $nm = (string)($ln['name'] ?? $ln['itemName'] ?? 'صنف');
        $q = (float)($ln['qty'] ?? $ln['quantity'] ?? 0);
        $pr = (float)($ln['price'] ?? 0);
        $tot = $q * $pr;
        if (!isset($itemStats[$nm])) $itemStats[$nm] = ['name'=>$nm,'code'=>'-','unit'=>'وحدة','buy'=>$pr*0.8,'sell'=>$pr,'open'=>0,'inQty'=>0,'inVal'=>0,'outQty'=>0,'outVal'=>0,'stock'=>0,'exp'=>''];
        $itemStats[$nm]['outQty'] += $q;
        $itemStats[$nm]['outVal'] += $tot;
        $detailedItemLines[] = [$dt, 'منصرف مبيعات', $no, $nm, $wh, 0.0, $q, $pr, $tot];
    }
}

$headers = [];
$rows = [];
$nowTs = time();

switch ($rep) {
    case 'items_catalog':
        $headers = ['كود الصنف', 'اسم الصنف', 'الوحدة الأساسية', 'سعر التكلفة', 'سعر البيع', 'الرصيد الحالي', 'إجمالي قيمة المخزون'];
        foreach ($itemStats as $st) $rows[] = [$st['code'], $st['name'], $st['unit'], $st['buy'], $st['sell'], $st['stock'], $st['stock'] * $st['buy']];
        break;

    case 'items_detailed_movement':
        $headers = ['التاريخ', 'نوع الحركة', 'رقم المرجع', 'اسم الصنف', 'المخزن', 'كمية وارد', 'كمية منصرف', 'سعر الوحدة', 'الإجمالي'];
        $rows = $detailedItemLines;
        break;

    case 'items_commercial_movement':
        $headers = ['اسم الصنف', 'كمية المشتريات', 'قيمة المشتريات', 'كمية المبيعات', 'قيمة المبيعات', 'صافي التداول التجاري'];
        foreach ($itemStats as $st) $rows[] = [$st['name'], $st['inQty'], $st['inVal'], $st['outQty'], $st['outVal'], $st['outVal'] - $st['inVal']];
        break;

    case 'items_summary_movement':
        $headers = ['اسم الصنف', 'الوحدة', 'رصيد أول المدة', 'إجمالي الوارد', 'إجمالي المنصرف', 'الرصيد الختامي الفعلي'];
        foreach ($itemStats as $st) {
            $calcEnd = $st['stock'] > 0 ? $st['stock'] : ($st['open'] + $st['inQty'] - $st['outQty']);
            $rows[] = [$st['name'], $st['unit'], $st['open'], $st['inQty'], $st['outQty'], $calcEnd];
        }
        break;

    case 'items_profitability':
        $headers = ['اسم الصنف', 'الكمية المباعة', 'إيراد المبيعات', 'تكلفة المبيعات التقديرية', 'مجمل ربح الصنف', 'نسبة الربحية %'];
        foreach ($itemStats as $st) {
            $costSold = $st['outQty'] * ($st['buy'] > 0 ? $st['buy'] : ($st['sell'] * 0.78));
            $prof = $st['outVal'] - $costSold;
            $pct = $st['outVal'] > 0 ? round(($prof / $st['outVal']) * 100, 1) . '%' : '0.0%';
            $rows[] = [$st['name'], $st['outQty'], $st['outVal'], $costSold, $prof, $pct];
        }
        break;

    case 'historical_expiry_inventory':
        $headers = ['اسم الصنف', 'الوحدة', 'الرصيد المتاح', 'تاريخ الصلاحية / الفحص', 'الأيام المتبقية', 'حالة الصلاحية', 'قيمة المخزون'];
        foreach ($itemStats as $st) {
            $exp = $st['exp'] ?: 'صلاحية مفتوحة / قياسية';
            $daysLeft = ($st['exp'] && strtotime($st['exp'])) ? (int)round((strtotime($st['exp']) - $nowTs) / 86400) : 365;
            $status = $daysLeft < 0 ? '🔴 منتهي الصلاحية' : ($daysLeft <= 30 ? '🟡 قريب الانتهاء' : '🟢 صالح ومعتمد');
            $rows[] = [$st['name'], $st['unit'], $st['stock'], $exp, $daysLeft, $status, $st['stock'] * $st['buy']];
        }
        break;

    case 'linked_references_audit':
        $headers = ['التاريخ', 'نوع المستند الأصلي', 'رقم المرجع', 'الشريك المرتبط', 'المخزن / الإذن المرتبط', 'حالة السداد / الترحيل', 'القيمة الإجمالية'];
        foreach ($sales as $s) {
            $tot = (float)($s['grandTotal'] ?? $s['totalAmount'] ?? $s['total'] ?? 0);
            $paid = (float)($s['paidAmount'] ?? $s['paid'] ?? 0);
            $wh = (string)($s['deliveredWarehouseName'] ?? $s['warehouseName'] ?? 'مخزن مرتبط');
            $rows[] = [substr((string)($s['date']??''),0,10), 'فاتورة مبيعات ⇄ قيد أستاذ', $s['invoiceNumber']??$s['number']??'SALE', $s['customerName']??'عميل', $wh, ($paid >= $tot && $tot > 0) ? 'مسدد بالكامل ✅' : 'مرتبط بذمم آجل 🔗', $tot];
        }
        foreach ($purchases as $p) {
            $tot = (float)($p['grandTotal'] ?? $p['totalAmount'] ?? $p['total'] ?? 0);
            $wh = (string)($p['warehouseName'] ?? 'مخزن استلام');
            $rows[] = [substr((string)($p['date']??''),0,10), 'فاتورة مشتريات ⇄ إذن مخزني', $p['invoiceNumber']??$p['number']??'PUR', $p['supplierName']??'مورد', $wh, ($p['receiptStatus'] ?? 'مرتبط بالأستاذ 🔗'), $tot];
        }
        break;

    case 'detailed_account_statement':
    case 'customer_statement':
    case 'supplier_statement':
        $headers = ['التاريخ', 'الجهة / الشريك', 'نوع المستند', 'رقم المرجع', 'مدين (عليه)', 'دائن (له)', 'الأثر الصافي'];
        if ($rep !== 'supplier_statement') {
            foreach ($sales as $s) {
                $tot = (float)($s['grandTotal'] ?? $s['totalAmount'] ?? $s['total'] ?? 0);
                $rows[] = [substr((string)($s['date']??''),0,10), $s['customerName']??'عميل', 'فاتورة مبيعات', $s['invoiceNumber']??$s['number']??'-', $tot, 0.0, $tot];
            }
            foreach ($receipts as $r) {
                $amt = (float)($r['amount'] ?? $r['total'] ?? 0);
                $rows[] = [substr((string)($r['date']??''),0,10), $r['customerName']??$r['partyName']??'عميل', 'سند قبض / تسديد', $r['voucherNumber']??$r['number']??'-', 0.0, $amt, -$amt];
            }
        }
        if ($rep !== 'customer_statement') {
            foreach ($purchases as $p) {
                $tot = (float)($p['grandTotal'] ?? $p['totalAmount'] ?? $p['total'] ?? 0);
                $rows[] = [substr((string)($p['date']??''),0,10), $p['supplierName']??'مورد', 'فاتورة مشتريات', $p['invoiceNumber']??$p['number']??'-', 0.0, $tot, $tot];
            }
            foreach ($payments as $py) {
                $amt = (float)($py['amount'] ?? $py['total'] ?? 0);
                $rows[] = [substr((string)($py['date']??''),0,10), $py['supplierName']??$py['partyName']??'مورد', 'سند صرف / سداد مورد', $py['voucherNumber']??$py['number']??'-', $amt, 0.0, -$amt];
            }
        }
        break;

    case 'customers_balances':
        $headers = ['اسم العميل', 'الهاتف', 'الرصيد الافتتاحي', 'السقف الائتماني', 'الرصيد الحالي المستحق', 'الائتمان المتاح'];
        foreach ($customers as $c) {
            $lim = (float)($c['creditLimit'] ?? 0);
            $bal = (float)($c['currentBalance'] ?? $c['balance'] ?? 0);
            $rows[] = [$c['name']??'عميل', $c['phone']??'-', (float)($c['openingBalance']??0), $lim, $bal, max(0.0, $lim - $bal)];
        }
        break;

    case 'suppliers_balances':
        $headers = ['اسم المورد', 'الهاتف', 'الرصيد الافتتاحي', 'إجمالي المستحق للمورد (له)', 'دفعات مقدمة (عليه)', 'صافي الرصيد'];
        foreach ($suppliers as $s) {
            $bal = (float)($s['currentBalance'] ?? $s['balance'] ?? 0);
            $rows[] = [$s['name']??'مورد', $s['phone']??'-', (float)($s['openingBalance']??0), $bal > 0 ? $bal : 0.0, $bal < 0 ? abs($bal) : 0.0, $bal];
        }
        break;

    case 'sales_commercial_movement':
        $headers = ['التاريخ', 'رقم الفاتورة', 'العميل', 'المخزن', 'إجمالي الفاتورة', 'المسدد الفوري', 'المتبقي بالذمم'];
        foreach ($sales as $s) {
            $tot = (float)($s['grandTotal'] ?? $s['totalAmount'] ?? $s['total'] ?? 0);
            $paid = (float)($s['paidAmount'] ?? $s['paid'] ?? 0);
            $rows[] = [substr((string)($s['date']??''),0,10), $s['invoiceNumber']??$s['number']??'-', $s['customerName']??'عميل', $s['warehouseName']??'مخزن', $tot, $paid, max(0.0, $tot - $paid)];
        }
        break;

    case 'purchases_commercial_movement':
        $headers = ['التاريخ', 'رقم الفاتورة', 'المورد', 'المخزن', 'إجمالي الفاتورة', 'المسدد للمورد', 'المتبقي للمورد'];
        foreach ($purchases as $p) {
            $tot = (float)($p['grandTotal'] ?? $p['totalAmount'] ?? $p['total'] ?? 0);
            $paid = (float)($p['paidAmount'] ?? $p['paid'] ?? 0);
            $rows[] = [substr((string)($p['date']??''),0,10), $p['invoiceNumber']??$p['number']??'-', $p['supplierName']??'مورد', $p['warehouseName']??'مخزن', $tot, $paid, max(0.0, $tot - $paid)];
        }
        break;

    case 'invoices_aging_all':
    case 'sales_invoices_aging':
    case 'purchases_invoices_aging':
        $headers = ['التاريخ', 'النوع', 'رقم الفاتورة', 'الشريك', 'عمر الفاتورة (يوم)', 'شريحة التقادم', 'المتبقي غير المسدد'];
        $pool = [];
        if ($rep !== 'purchases_invoices_aging') foreach ($sales as $s) $pool[] = ['مبيعات', $s, $s['customerName']??'عميل'];
        if ($rep !== 'sales_invoices_aging') foreach ($purchases as $p) $pool[] = ['مشتريات', $p, $p['supplierName']??'مورد'];
        foreach ($pool as [$tp, $doc, $party]) {
            $tot = (float)($doc['grandTotal'] ?? $doc['totalAmount'] ?? $doc['total'] ?? 0);
            $paid = (float)($doc['paidAmount'] ?? $doc['paid'] ?? 0);
            $rem = max(0.0, $tot - $paid);
            if ($rem <= 0 && count($pool) > 3) continue;
            $dt = substr((string)($doc['date'] ?? date('Y-m-d')), 0, 10);
            $days = max(0, (int)round(($nowTs - (strtotime($dt) ?: $nowTs)) / 86400));
            $bucket = $days <= 30 ? '0 - 30 يوماً (حديثة)' : ($days <= 60 ? '31 - 60 يوماً' : ($days <= 90 ? '61 - 90 يوماً' : '+90 يوماً (متأخرة)'));
            $rows[] = [$dt, $tp, $doc['invoiceNumber']??$doc['number']??'-', $party, $days, $bucket, $rem > 0 ? $rem : $tot];
        }
        break;

    case 'customers_payments':
        $headers = ['التاريخ', 'رقم السند', 'اسم العميل', 'طريقة التحصيل / الخزنة أو البنك', 'البيان', 'المبلغ المحصل'];
        foreach ($receipts as $r) $rows[] = [substr((string)($r['date']??''),0,10), $r['voucherNumber']??$r['number']??'RV', $r['customerName']??$r['partyName']??'عميل', $r['paymentMethod']??$r['safeName']??$r['bankName']??'نقدي/بنكي', $r['notes']??'تحصيل دفعة عميل', (float)($r['amount']??$r['total']??0)];
        break;

    case 'suppliers_payments':
        $headers = ['التاريخ', 'رقم السند', 'اسم المورد', 'طريقة السداد / الخزنة أو البنك', 'البيان', 'المبلغ المسدد'];
        foreach ($payments as $py) $rows[] = [substr((string)($py['date']??''),0,10), $py['voucherNumber']??$py['number']??'PV', $py['supplierName']??$py['partyName']??'مورد', $py['paymentMethod']??$py['safeName']??$py['bankName']??'نقدي/بنكي', $py['notes']??'سداد دفعة مورد', (float)($py['amount']??$py['total']??0)];
        break;

    case 'customers_installments':
        $headers = ['تاريخ الفاتورة', 'رقم فاتورة العميل', 'اسم العميل', 'إجمالي الفاتورة', 'الأقساط المسددة', 'الأقساط المتبقية', 'نسبة السداد %'];
        foreach ($sales as $s) {
            $tot = (float)($s['grandTotal'] ?? $s['totalAmount'] ?? $s['total'] ?? 0);
            $paid = (float)($s['paidAmount'] ?? $s['paid'] ?? 0);
            $rem = max(0.0, $tot - $paid);
            $pct = $tot > 0 ? round(($paid / $tot) * 100, 1) . '%' : '100%';
            $rows[] = [substr((string)($s['date']??''),0,10), $s['invoiceNumber']??$s['number']??'-', $s['customerName']??'عميل', $tot, $paid, $rem, $pct];
        }
        break;

    case 'suppliers_installments':
        $headers = ['تاريخ الفاتورة', 'رقم فاتورة المورد', 'اسم المورد', 'إجمالي الفاتورة', 'الدفعات والأقساط المسددة', 'المتبقي للمورد', 'نسبة السداد %'];
        foreach ($purchases as $p) {
            $tot = (float)($p['grandTotal'] ?? $p['totalAmount'] ?? $p['total'] ?? 0);
            $paid = (float)($p['paidAmount'] ?? $p['paid'] ?? 0);
            $rem = max(0.0, $tot - $paid);
            $pct = $tot > 0 ? round(($paid / $tot) * 100, 1) . '%' : '100%';
            $rows[] = [substr((string)($p['date']??''),0,10), $p['invoiceNumber']??$p['number']??'-', $p['supplierName']??'مورد', $tot, $paid, $rem, $pct];
        }
        break;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($repTitle) ?> | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        body { background:#020617; color:#fff; font-family:'Tajawal',sans-serif; margin:0; }
        :root.light-theme body { background:#f1f5f9 !important; color:#000 !important; }
        .crs-wrap { max-width:1680px; margin:0 auto; padding:15px 20px 45px; }
        .crs-hero { background:linear-gradient(135deg,#0f172a,#1e293b); border:2px solid rgba(16,185,129,0.4); border-radius:18px; padding:16px 20px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
        :root.light-theme .crs-hero { background:#fff !important; border:2.5px solid #0f172a !important; color:#000 !important; }
        .crs-pills { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:15px; }
        .crs-pill { padding:7px 11px; border-radius:10px; border:1.5px solid rgba(148,163,184,0.28); background:#0f172a; color:#e2e8f0; font-weight:800; font-size:0.83rem; text-decoration:none; }
        .crs-pill.active, .crs-pill:hover { background:linear-gradient(135deg,#059669,#0284c7); color:#fff !important; border-color:#34d399; }
        :root.light-theme .crs-pill { background:#fff; color:#0f172a; border-color:#cbd5e1; }
        .crs-card { background:#0f172a; border:2px solid rgba(255,255,255,0.12); border-radius:16px; padding:16px; overflow-x:auto; }
        :root.light-theme .crs-card { background:#fff !important; border:2px solid #0f172a !important; color:#000 !important; }
        table.crs-tbl { width:100%; border-collapse:collapse; min-width:860px; }
        table.crs-tbl th { background:#1e293b; color:#fff; padding:10px; font-weight:900; border:1px solid rgba(148,163,184,0.25); text-align:right; }
        :root.light-theme table.crs-tbl th { background:#e2e8f0 !important; color:#000 !important; }
        table.crs-tbl td { padding:9px 10px; border:1px solid rgba(148,163,184,0.16); font-weight:800; font-size:0.9rem; }
        table.crs-tbl td.num { text-align:left; font-family:'Outfit',sans-serif; direction:ltr; }
        .crs-btn { padding:7px 13px; border-radius:9px; border:none; font-family:'Tajawal',sans-serif; font-weight:900; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        @media print { .no-print, header, nav { display:none !important; } body { background:#fff !important; color:#000 !important; } }
    </style>
</head>
<body>
<div class="crs-wrap">
    <div class="no-print"><?php $header_theme_class = 'header-theme-reports'; include 'includes/header.php'; ?></div>

    <div class="crs-hero">
        <div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="financial-reports.php" class="crs-btn no-print" style="background:rgba(251,191,36,0.16); color:#fbbf24; border:1.5px solid #fbbf24;">↩️ بوابة التقارير الشاملة</a>
                <h1 style="margin:0; font-size:1.45rem; font-weight:900;"><?= htmlspecialchars($repTitle) ?></h1>
            </div>
            <div style="margin-top:4px; font-size:0.88rem; color:#94a3b8; font-weight:700;"><?= htmlspecialchars($repDesc) ?></div>
        </div>
        <div class="no-print" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input type="text" id="crs-search" placeholder="🔍 فلترة فورية داخل التقرير..." oninput="filterCrsRows()" style="padding:7px 12px; border-radius:9px; border:1.5px solid #38bdf8; background:#020617; color:#fff; font-family:'Tajawal',sans-serif; font-weight:800;">
            <button type="button" onclick="exportCrsCSV()" class="crs-btn" style="background:#7c3aed; color:#fff;">📥 تصدير Excel</button>
            <button type="button" onclick="window.print()" class="crs-btn" style="background:#10b981; color:#fff;">🖨️ طباعة</button>
        </div>
    </div>

    <!-- All 21 Commercial, Inventory, Aging, Payment & Installment Keys -->
    <div class="crs-pills no-print">
        <?php foreach ($catalog as $k => [$lbl, $_d]): ?>
            <a href="?rep=<?= urlencode($k) ?>" class="crs-pill <?= $rep===$k?'active':'' ?>"><?= htmlspecialchars($lbl) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="crs-card bs-card">
        <h3 style="margin-top:0; color:#34d399;"><?= htmlspecialchars($repTitle) ?> (عدد السجلات: <?= count($rows) ?>)</h3>
        <table id="crs-main-tbl" class="crs-tbl">
            <thead><tr><?php foreach ($headers as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($headers) ?>" style="text-align:center; color:#94a3b8; padding:22px;">لا توجد حركات مسجلة حالياً في هذا التقرير — ستظهر تلقائياً فور حفظ الفواتير أو السندات.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($r as $cell): ?>
                            <?php if (is_int($cell) || is_float($cell)): ?>
                                <td class="num"><?= is_float($cell) ? number_format($cell, 2) : $cell ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars((string)$cell) ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php include __DIR__ . '/includes/report_visual_risk_radar.php'; ?>
</div>

<script>
function filterCrsRows() {
    const q = (document.getElementById('crs-search').value || '').trim().toLowerCase();
    document.querySelectorAll('#crs-main-tbl tbody tr').forEach(tr => {
        tr.style.display = (!q || tr.innerText.toLowerCase().includes(q)) ? '' : 'none';
    });
}
function exportCrsCSV() {
    const rows = Array.from(document.querySelectorAll('#crs-main-tbl tr')).map(tr =>
        Array.from(tr.querySelectorAll('th,td')).map(td => '"' + td.innerText.replace(/"/g, '""').trim() + '"').join(',')
    );
    const blob = new Blob(['\uFEFF' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'report_<?= htmlspecialchars($rep) ?>.csv';
    a.click();
}
</script>
<script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
