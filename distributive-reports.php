<?php
$page_title = 'التقارير التوزيعية ومولد التقارير الذكي';
require_once __DIR__ . '/db_bridge.php';

$pdo = getDBConnection();
global $GLOBAL_TENANT_ID;
$tid = (int)($GLOBAL_TENANT_ID ?: 1);
$activeType = $_GET['type'] ?? 'journal';
$axis = $_GET['axis'] ?? 'months'; // months | centers

function drFetchRows(PDO $pdo, string $tbl, $tid): array {
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

$sales = drFetchRows($pdo, 'sales_invoices', $tid);
$purchases = drFetchRows($pdo, 'purchase_invoices', $tid);
$receipts = drFetchRows($pdo, 'receipts', $tid);
$payments = drFetchRows($pdo, 'single_payments', $tid);
$glEntries = drFetchRows($pdo, 'gl_entries', $tid);
$customers = drFetchRows($pdo, 'customers', $tid);
$suppliers = drFetchRows($pdo, 'suppliers', $tid);
$items = drFetchRows($pdo, 'items', $tid);

// Build unified accounting movements stream from GL + Operational tables if GL is sparse
$movements = [];
foreach ($glEntries as $g) {
    $dt = substr((string)($g['date'] ?? $g['createdAt'] ?? date('Y-m-d')), 0, 10);
    $m = max(1, min(12, (int)substr($dt, 5, 2) ?: (int)date('n')));
    $dr = (float)($g['debit'] ?? 0);
    $cr = (float)($g['credit'] ?? 0);
    $acc = (string)($g['accountName'] ?? $g['account'] ?? 'حساب عام');
    $cc = (string)($g['costCenter'] ?? $g['warehouseName'] ?? 'المركز الرئيسي');
    $cat = 'asset';
    $low = mb_strtolower($acc . ' ' . ($g['account'] ?? ''));
    if (strpos($low, 'مبيعات') !== false || strpos($low, 'إيراد') !== false || strpos($low, 'revenue') !== false) $cat = 'revenue';
    elseif (strpos($low, 'مشتريات') !== false || strpos($low, 'تكلفة') !== false || strpos($low, 'مصروف') !== false || strpos($low, 'exp-') !== false) $cat = 'expense';
    elseif (strpos($low, 'مورد') !== false || strpos($low, 'دائن') !== false) $cat = 'liability';
    elseif (strpos($low, 'رأس المال') !== false || strpos($low, 'ملكية') !== false) $cat = 'equity';
    $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$g['ref']??$g['voucherNo']??'-','acc'=>$acc,'cat'=>$cat,'center'=>$cc,'dr'=>$dr,'cr'=>$cr,'source'=>'قيد أستاذ'];
}

if (empty($movements)) {
    foreach ($sales as $s) {
        $dt = substr((string)($s['date'] ?? date('Y-m-d')), 0, 10);
        $m = max(1, min(12, (int)substr($dt, 5, 2) ?: (int)date('n')));
        $amt = (float)($s['grandTotal'] ?? $s['totalAmount'] ?? $s['total'] ?? 0);
        $cc = (string)($s['warehouseName'] ?? $s['deliveredWarehouseName'] ?? 'المخزن الرئيسي');
        $ref = (string)($s['invoiceNumber'] ?? $s['number'] ?? 'SALE');
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'مدينو العملاء - '.($s['customerName']??'عميل'),'cat'=>'asset','center'=>$cc,'dr'=>$amt,'cr'=>0,'source'=>'مبيعات'];
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'إيراد المبيعات','cat'=>'revenue','center'=>$cc,'dr'=>0,'cr'=>$amt,'source'=>'مبيعات'];
    }
    foreach ($purchases as $p) {
        $dt = substr((string)($p['date'] ?? date('Y-m-d')), 0, 10);
        $m = max(1, min(12, (int)substr($dt, 5, 2) ?: (int)date('n')));
        $amt = (float)($p['grandTotal'] ?? $p['totalAmount'] ?? $p['total'] ?? 0);
        $cc = (string)($p['warehouseName'] ?? 'المخزن الرئيسي');
        $ref = (string)($p['invoiceNumber'] ?? $p['number'] ?? 'PUR');
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'المشتريات وتكلفة المخزون','cat'=>'expense','center'=>$cc,'dr'=>$amt,'cr'=>0,'source'=>'مشتريات'];
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'دائنو الموردين - '.($p['supplierName']??'مورد'),'cat'=>'liability','center'=>$cc,'dr'=>0,'cr'=>$amt,'source'=>'مشتريات'];
    }
    foreach ($receipts as $r) {
        $dt = substr((string)($r['date'] ?? date('Y-m-d')), 0, 10);
        $m = max(1, min(12, (int)substr($dt, 5, 2) ?: (int)date('n')));
        $amt = (float)($r['amount'] ?? $r['total'] ?? 0);
        $ref = (string)($r['voucherNumber'] ?? $r['number'] ?? 'RV');
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'الخزينة والبنوك','cat'=>'asset','center'=>'المركز المالي','dr'=>$amt,'cr'=>0,'source'=>'سند قبض'];
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'مدينو العملاء','cat'=>'asset','center'=>'المركز المالي','dr'=>0,'cr'=>$amt,'source'=>'سند قبض'];
    }
    foreach ($payments as $py) {
        $dt = substr((string)($py['date'] ?? date('Y-m-d')), 0, 10);
        $m = max(1, min(12, (int)substr($dt, 5, 2) ?: (int)date('n')));
        $amt = (float)($py['amount'] ?? $py['total'] ?? 0);
        $ref = (string)($py['voucherNumber'] ?? $py['number'] ?? 'PV');
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'دائنو الموردين / مصروفات','cat'=>'liability','center'=>'المركز المالي','dr'=>$amt,'cr'=>0,'source'=>'سند صرف'];
        $movements[] = ['date'=>$dt,'month'=>$m,'ref'=>$ref,'acc'=>'الخزينة والبنوك','cat'=>'asset','center'=>'المركز المالي','dr'=>0,'cr'=>$amt,'source'=>'سند صرف'];
    }
}

$monthNames = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
$centersSet = [];
foreach ($movements as $mv) { $centersSet[$mv['center']] = true; }
if (empty($centersSet)) $centersSet['المركز الرئيسي'] = true;
$columns = ($axis === 'centers') ? array_keys($centersSet) : $monthNames;

// Helper to get bucket key
function drColKey(array $mv, string $axis) { return $axis === 'centers' ? $mv['center'] : (int)$mv['month']; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">
    <style>
        body { background:#020617; color:#ffffff; font-family:'Tajawal',sans-serif; margin:0; }
        :root.light-theme body { background:#f1f5f9 !important; color:#000000 !important; }
        .dr-wrap { max-width:1680px; margin:0 auto; padding:15px 20px 45px; }
        .dr-hero { background:linear-gradient(135deg,#0f172a,#1e293b); border:2px solid rgba(168,85,247,0.4); border-radius:18px; padding:16px 20px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
        :root.light-theme .dr-hero { background:#ffffff !important; border:2.5px solid #0f172a !important; color:#000 !important; }
        .dr-tabs { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:14px; }
        .dr-tab { padding:8px 13px; border-radius:11px; border:2px solid rgba(148,163,184,0.28); background:#0f172a; color:#e2e8f0; font-weight:900; font-size:0.88rem; cursor:pointer; text-decoration:none; }
        .dr-tab.active, .dr-tab:hover { background:linear-gradient(135deg,#7c3aed,#2563eb); color:#fff !important; border-color:#c084fc; }
        :root.light-theme .dr-tab { background:#fff; color:#0f172a; border-color:#cbd5e1; }
        .dr-card { background:#0f172a; border:2px solid rgba(255,255,255,0.12); border-radius:16px; padding:16px; overflow-x:auto; }
        :root.light-theme .dr-card { background:#ffffff !important; border:2px solid #0f172a !important; color:#000 !important; }
        table.dr-tbl { width:100%; border-collapse:collapse; min-width:920px; }
        table.dr-tbl th { background:#1e293b; color:#f8fafc; padding:9px 10px; font-weight:900; font-size:0.86rem; border:1px solid rgba(148,163,184,0.25); white-space:nowrap; }
        :root.light-theme table.dr-tbl th { background:#e2e8f0 !important; color:#000 !important; }
        table.dr-tbl td { padding:8px 10px; border:1px solid rgba(148,163,184,0.16); font-weight:800; font-size:0.88rem; }
        table.dr-tbl td.num { text-align:left; font-family:'Outfit',sans-serif; direction:ltr; white-space:nowrap; }
        .dr-tot td { background:rgba(16,185,129,0.16); font-weight:900; border-top:2px solid #10b981; }
        .dr-btn { padding:7px 13px; border-radius:9px; border:none; font-family:'Tajawal',sans-serif; font-weight:900; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .gen-ctrl { padding:8px 11px; border-radius:9px; border:1.5px solid #475569; background:#020617; color:#fff; font-family:'Tajawal',sans-serif; font-weight:800; }
        :root.light-theme .gen-ctrl { background:#fff; color:#000; }
        @media print { .no-print, header, nav { display:none !important; } body { background:#fff !important; color:#000 !important; } }
    </style>
</head>
<body>
<div class="dr-wrap">
    <div class="no-print"><?php $header_theme_class = 'header-theme-reports'; include 'includes/header.php'; ?></div>

    <div class="dr-hero">
        <div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="financial-reports.php" class="dr-btn no-print" style="background:rgba(251,191,36,0.16); color:#fbbf24; border:1.5px solid #fbbf24;">↩️ القوائم والتقارير المالية</a>
                <h1 style="margin:0; font-size:1.45rem; font-weight:900;">📊 منظومة الدفاتر والقوائم التوزيعية ومولد التقارير الحر</h1>
            </div>
        </div>
        <div class="no-print" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <?php if ($activeType !== 'generator'): ?>
            <a href="?type=<?= urlencode($activeType) ?>&axis=months" class="dr-btn" style="background:<?= $axis==='months'?'#0284c7':'#1e293b' ?>; color:#fff;">📅 توزيع شهري (12 شهراً)</a>
            <a href="?type=<?= urlencode($activeType) ?>&axis=centers" class="dr-btn" style="background:<?= $axis==='centers'?'#059669':'#1e293b' ?>; color:#fff;">🎯 توزيع على مراكز الكلفة/المخازن</a>
            <?php endif; ?>
            <button type="button" onclick="exportActiveTableCSV()" class="dr-btn" style="background:#7c3aed; color:#fff;">📥 تصدير Excel / CSV</button>
            <button type="button" onclick="window.print()" class="dr-btn" style="background:#10b981; color:#fff;">🖨️ طباعة</button>
        </div>
    </div>

    <div class="dr-tabs no-print">
        <a class="dr-tab <?= $activeType==='journal'?'active':'' ?>" href="?type=journal&axis=<?= urlencode($axis) ?>">📒 1. دفتر اليومية التوزيعي</a>
        <a class="dr-tab <?= $activeType==='ledger'?'active':'' ?>" href="?type=ledger&axis=<?= urlencode($axis) ?>">📓 2. دفتر الأستاذ التوزيعي</a>
        <a class="dr-tab <?= $activeType==='trial_balance'?'active':'' ?>" href="?type=trial_balance&axis=<?= urlencode($axis) ?>">⚖️ 3. ميزان المراجعة التوزيعي</a>
        <a class="dr-tab <?= $activeType==='income'?'active':'' ?>" href="?type=income&axis=<?= urlencode($axis) ?>">📈 4. قائمة الدخل التوزيعي</a>
        <a class="dr-tab <?= $activeType==='balance_sheet'?'active':'' ?>" href="?type=balance_sheet&axis=<?= urlencode($axis) ?>">🏛️ 5. الميزانية العمومية التوزيعية</a>
        <a class="dr-tab <?= $activeType==='cost_centers'?'active':'' ?>" href="?type=cost_centers&axis=centers">🎯 6. تقرير مراكز الكلفة التوزيعي</a>
        <a class="dr-tab <?= $activeType==='generator'?'active':'' ?>" href="?type=generator">🛠️ 7. مولد التقارير الحر (Report Builder)</a>
    </div>

    <div class="dr-card">
    <?php if ($activeType === 'generator'): ?>
        <h3 style="margin-top:0; color:#c084fc;">🛠️ مولد التقارير المحاسبية والمخزنية الحر (Custom Dynamic Report Generator)</h3>
        <div class="no-print" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; align-items:end;">
            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; margin-bottom:4px;">مصدر البيانات:</label>
                <select id="gen-src" class="gen-ctrl" onchange="renderCustomReport()">
                    <option value="movements">القيود والحركات المالية الموحدة</option>
                    <option value="customers">أرصدة ومديونيات العملاء</option>
                    <option value="suppliers">أرصدة ومستحقات الموردين</option>
                    <option value="items">جرد وتقييم الأصناف بالمخازن</option>
                    <option value="sales">سجل فواتير المبيعات</option>
                    <option value="purchases">سجل فواتير المشتريات</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; margin-bottom:4px;">طريقة التجميع (Group By):</label>
                <select id="gen-grp" class="gen-ctrl" onchange="renderCustomReport()">
                    <option value="none">تفصيلي كامل (بدون تجميع)</option>
                    <option value="name">تجميع حسب الحساب / الاسم / الصنف</option>
                    <option value="center">تجميع حسب مركز الكلفة / المخزن</option>
                    <option value="month">تجميع حسب الشهر</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; margin-bottom:4px;">بحث وفلترة فورية:</label>
                <input type="text" id="gen-q" class="gen-ctrl" placeholder="اكتب اسم حساب أو عميل أو مرجع..." oninput="renderCustomReport()">
            </div>
            <button type="button" class="dr-btn" style="background:#0284c7; color:#fff;" onclick="renderCustomReport()">⚡ توليد التقرير فوراً</button>
        </div>
        <div id="gen-output"></div>
        <script>
        const GEN_DATA = <?= json_encode([
            'movements' => $movements,
            'customers' => array_map(fn($c)=>['name'=>$c['name']??'عميل','phone'=>$c['phone']??'-','limit'=>(float)($c['creditLimit']??0),'balance'=>(float)($c['currentBalance']??$c['balance']??0)], $customers),
            'suppliers' => array_map(fn($s)=>['name'=>$s['name']??'مورد','phone'=>$s['phone']??'-','balance'=>(float)($s['currentBalance']??$s['balance']??0)], $suppliers),
            'items' => array_map(fn($i)=>['name'=>$i['name']??'صنف','cat'=>$i['category']??'عام','buy'=>(float)($i['costPrice']??$i['buyPrice']??0),'sell'=>(float)($i['sellingPrice']??0),'stock'=>(float)($i['units'][0]['stock']??$i['stock']??0)], $items),
            'sales' => array_map(fn($x)=>['no'=>$x['invoiceNumber']??$x['number']??'-','date'=>substr((string)($x['date']??''),0,10),'party'=>$x['customerName']??'-','center'=>$x['warehouseName']??'مخزن','total'=>(float)($x['grandTotal']??$x['totalAmount']??$x['total']??0)], $sales),
            'purchases' => array_map(fn($x)=>['no'=>$x['invoiceNumber']??$x['number']??'-','date'=>substr((string)($x['date']??''),0,10),'party'=>$x['supplierName']??'-','center'=>$x['warehouseName']??'مخزن','total'=>(float)($x['grandTotal']??$x['totalAmount']??$x['total']??0)], $purchases),
        ], JSON_UNESCAPED_UNICODE) ?>;

        function renderCustomReport() {
            const src = document.getElementById('gen-src').value;
            const grp = document.getElementById('gen-grp').value;
            const q = (document.getElementById('gen-q').value || '').trim().toLowerCase();
            let rows = (GEN_DATA[src] || []).filter(r => !q || JSON.stringify(r).toLowerCase().includes(q));
            const out = document.getElementById('gen-output');
            if (!rows.length) { out.innerHTML = '<p style="color:#94a3b8; font-weight:800;">لا توجد سجلات مطابقة لمعيار التوليد الحالي.</p>'; return; }

            if (grp !== 'none') {
                const map = {};
                rows.forEach(r => {
                    const k = grp === 'center' ? (r.center || r.cat || 'عام') : (grp === 'month' ? ((r.date || '').slice(0,7) || 'الحالي') : (r.acc || r.name || r.party || 'بند'));
                    if (!map[k]) map[k] = { 'البند المجمّع': k, 'عدد الحركات': 0, 'إجمالي القيمة / المدين': 0, 'إجمالي الدائن': 0, 'الصافي': 0 };
                    map[k]['عدد الحركات']++;
                    const v1 = Number(r.dr ?? r.total ?? r.balance ?? (Number(r.stock||0)*Number(r.buy||0)) ?? 0);
                    const v2 = Number(r.cr ?? 0);
                    map[k]['إجمالي القيمة / المدين'] += v1;
                    map[k]['إجمالي الدائن'] += v2;
                    map[k]['الصافي'] += (v1 - v2);
                });
                rows = Object.values(map);
            }
            const keys = Object.keys(rows[0]);
            let html = '<table id="active-dr-table" class="dr-tbl"><thead><tr>' + keys.map(k=>`<th>${k}</th>`).join('') + '</tr></thead><tbody>';
            rows.forEach(r => {
                html += '<tr>' + keys.map(k => {
                    const v = r[k];
                    return typeof v === 'number' ? `<td class="num">${v.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>` : `<td>${v ?? '-'}</td>`;
                }).join('') + '</tr>';
            });
            html += '</tbody></table>';
            out.innerHTML = html;
        }
        document.addEventListener('DOMContentLoaded', renderCustomReport);
        </script>
    <?php elseif ($activeType === 'journal'): ?>
        <?php
        // Distributive Journal: Each voucher/entry row distributed across major accounts
        $accCols = [];
        foreach ($movements as $mv) { $accCols[$mv['acc']] = ($accCols[$mv['acc']] ?? 0) + $mv['dr'] + $mv['cr']; }
        arsort($accCols);
        $topAccs = array_slice(array_keys($accCols), 0, 7, true);
        ?>
        <h3 style="margin-top:0; color:#38bdf8;">📒 دفتر اليومية التوزيعي (الأمريكي متعدد الأعمدة — Multi-Column Distributive Journal)</h3>
        <table id="active-dr-table" class="dr-tbl">
            <thead>
                <tr>
                    <th>التاريخ</th><th>المرجع</th><th>المصدر</th><th>الحساب الأساسي</th><th>مدين إجمالي</th><th>دائن إجمالي</th>
                    <?php foreach ($topAccs as $acName): ?><th><?= htmlspecialchars($acName) ?> (صافي)</th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $totDr=0; $totCr=0; foreach (array_slice($movements, 0, 120) as $mv): $totDr+=$mv['dr']; $totCr+=$mv['cr']; ?>
                <tr>
                    <td><?= htmlspecialchars($mv['date']) ?></td>
                    <td><?= htmlspecialchars($mv['ref']) ?></td>
                    <td><?= htmlspecialchars($mv['source']) ?></td>
                    <td><?= htmlspecialchars($mv['acc']) ?></td>
                    <td class="num" style="color:#34d399;"><?= number_format($mv['dr'], 2) ?></td>
                    <td class="num" style="color:#f87171;"><?= number_format($mv['cr'], 2) ?></td>
                    <?php foreach ($topAccs as $acName): $cell = ($mv['acc'] === $acName) ? ($mv['dr'] - $mv['cr']) : 0; ?>
                        <td class="num"><?= $cell != 0 ? number_format($cell, 2) : '-' ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                <tr class="dr-tot"><td colspan="4">الإجمالي المطابق لدفتر اليومية التوزيعي</td><td class="num"><?= number_format($totDr, 2) ?></td><td class="num"><?= number_format($totCr, 2) ?></td><?php foreach ($topAccs as $_): ?><td></td><?php endforeach; ?></tr>
            </tbody>
        </table>
    <?php else: ?>
        <?php
        // Matrix Pivot Engine for: ledger | trial_balance | income | balance_sheet | cost_centers
        $titles = [
            'ledger' => '📓 دفتر الأستاذ التوزيعي (توزيع صافي حركات الحسابات أفقياً)',
            'trial_balance' => '⚖️ ميزان المراجعة التوزيعي (أرصدة الحسابات الموزعة ومجاميعها)',
            'income' => '📈 قائمة الدخل التوزيعي (الإيرادات والمصروفات وصافي الربح الموزع)',
            'balance_sheet' => '🏛️ الميزانية العمومية التوزيعية (الأصول والالتزامات وحقوق الملكية الموزعة)',
            'cost_centers' => '🎯 تقرير مراكز الكلفة التوزيعي (مصفوفة الحسابات × المخازن ومراكز الكلفة)',
        ];
        $effectiveAxis = ($activeType === 'cost_centers') ? 'centers' : $axis;
        $cols = ($effectiveAxis === 'centers') ? array_combine(array_keys($centersSet), array_keys($centersSet)) : $monthNames;
        $matrix = [];
        foreach ($movements as $mv) {
            if ($activeType === 'income' && !in_array($mv['cat'], ['revenue', 'expense'], true)) continue;
            if ($activeType === 'balance_sheet' && in_array($mv['cat'], ['revenue', 'expense'], true)) continue;
            $rowKey = $mv['acc'];
            $ck = drColKey($mv, $effectiveAxis);
            if (!isset($matrix[$rowKey])) $matrix[$rowKey] = ['cat'=>$mv['cat'], 'dr'=>0.0, 'cr'=>0.0, 'buckets'=>[]];
            $val = ($mv['cat'] === 'revenue' || $mv['cat'] === 'liability' || $mv['cat'] === 'equity') ? ($mv['cr'] - $mv['dr']) : ($mv['dr'] - $mv['cr']);
            $matrix[$rowKey]['dr'] += $mv['dr'];
            $matrix[$rowKey]['cr'] += $mv['cr'];
            $matrix[$rowKey]['buckets'][$ck] = ($matrix[$rowKey]['buckets'][$ck] ?? 0.0) + $val;
        }
        ?>
        <h3 style="margin-top:0; color:#34d399;"><?= $titles[$activeType] ?? 'التقرير التوزيعي' ?></h3>
        <table id="active-dr-table" class="dr-tbl">
            <thead>
                <tr>
                    <th>اسم الحساب / البند</th>
                    <?php if ($activeType === 'trial_balance'): ?><th>إجمالي مدين</th><th>إجمالي دائن</th><?php endif; ?>
                    <?php foreach ($cols as $ck => $cLabel): ?><th><?= htmlspecialchars((string)$cLabel) ?></th><?php endforeach; ?>
                    <th>الإجمالي الكلي</th>
                </tr>
            </thead>
            <tbody>
                <?php $colTotals = []; $grandDr = 0; $grandCr = 0; $grandNet = 0;
                foreach ($matrix as $accName => $data):
                    $rowSum = array_sum($data['buckets']);
                    $grandDr += $data['dr']; $grandCr += $data['cr']; $grandNet += $rowSum;
                ?>
                <tr>
                    <td><?= htmlspecialchars($accName) ?></td>
                    <?php if ($activeType === 'trial_balance'): ?>
                        <td class="num"><?= number_format($data['dr'], 2) ?></td>
                        <td class="num"><?= number_format($data['cr'], 2) ?></td>
                    <?php endif; ?>
                    <?php foreach ($cols as $ck => $cLabel): $v = $data['buckets'][$ck] ?? 0.0; $colTotals[$ck] = ($colTotals[$ck] ?? 0.0) + $v; ?>
                        <td class="num"><?= $v != 0 ? number_format($v, 2) : '0.00' ?></td>
                    <?php endforeach; ?>
                    <td class="num" style="font-weight:900; color:#38bdf8;"><?= number_format($rowSum, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="dr-tot">
                    <td>المجموع التوزيعي الموحد</td>
                    <?php if ($activeType === 'trial_balance'): ?><td class="num"><?= number_format($grandDr, 2) ?></td><td class="num"><?= number_format($grandCr, 2) ?></td><?php endif; ?>
                    <?php foreach ($cols as $ck => $cLabel): ?><td class="num"><?= number_format($colTotals[$ck] ?? 0, 2) ?></td><?php endforeach; ?>
                    <td class="num"><?= number_format($grandNet, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/report_visual_risk_radar.php'; ?>
</div>

<script>
function exportActiveTableCSV() {
    const tbl = document.getElementById('active-dr-table');
    if (!tbl) return;
    const rows = Array.from(tbl.querySelectorAll('tr')).map(tr =>
        Array.from(tr.querySelectorAll('th,td')).map(td => '"' + td.innerText.replace(/"/g, '""').trim() + '"').join(',')
    );
    const blob = new Blob(['\uFEFF' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'distributive_report_<?= htmlspecialchars($activeType) ?>.csv';
    a.click();
}
</script>
<script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
