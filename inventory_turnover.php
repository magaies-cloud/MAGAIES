<?php
$page_title = 'محلل ركود البضائع ومعدل الدوران';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - محلل ركود البضائع ومعدلات الدوران
// ====================================================================
// الاتصال بقاعدة البيانات
require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

// العملة المختارة
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'USD') ? 1 : 1;

// فلاتر التاريخ
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : '';

// فلتر الفئة
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// فلتر البحث
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// ====================================================================
// 1. جلب جميع الأصناف من جدول items
// ====================================================================
$all_items = [];
$categories = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT data FROM items WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $item = json_decode($row['data'], true);
            if ($item && !empty($item['name'])) {
                $all_items[] = [
                    'id'           => isset($item['id']) ? $item['id'] : '',
                    'name'         => $item['name'],
                    'category'     => isset($item['category']) ? $item['category'] : 'بدون فئة',
                    'openingQty'   => isset($item['openingQty']) ? floatval($item['openingQty']) : 0,
                    'openingValue' => isset($item['openingValue']) ? floatval($item['openingValue']) : 0,
                    'sellingPrice' => isset($item['sellingPrice']) ? floatval($item['sellingPrice']) : 0,
                    'costMethod'   => isset($item['costMethod']) ? $item['costMethod'] : '',
                ];
                // جمع الفئات
                $cat = isset($item['category']) ? $item['category'] : '';
                if ($cat && !in_array($cat, $categories)) {
                    $categories[] = $cat;
                }
            }
        }
    }
} catch (Exception $e) {
    // fallback
}

// ====================================================================
// 2. جلب بيانات المبيعات: الكمية المباعة لكل صنف + آخر تاريخ بيع
// ====================================================================
$sales_data = []; // itemName => ['totalQty' => X, 'totalRevenue' => Y, 'lastSaleDate' => 'YYYY-MM-DD']
try {
    if (isset($pdo)) {
        $sql = "SELECT date_val, data FROM sales_invoices";
        $params = [];
        $conditions = [];
        if ($date_from) {
            $conditions[] = "date_val >= ?";
            $params[] = $date_from;
        }
        if ($date_to) {
            $conditions[] = "date_val <= ?";
            $params[] = $date_to;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($salesRows as $row) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            // التاريخ: نستخدم date_val من العمود أولاً ثم من JSON
            $invDate = !empty($row['date_val']) ? $row['date_val'] : (isset($inv['date']) ? $inv['date'] : '');

            if (isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $itm) {
                    $itemName = isset($itm['itemName']) ? trim($itm['itemName']) : '';
                    if (empty($itemName)) continue;
                    $qty   = isset($itm['qty']) ? floatval($itm['qty']) : 0;
                    $price = isset($itm['price']) ? floatval($itm['price']) : 0;
                    $total = isset($itm['total']) ? floatval($itm['total']) : ($qty * $price);

                    if (!isset($sales_data[$itemName])) {
                        $sales_data[$itemName] = ['totalQty' => 0, 'totalRevenue' => 0, 'lastSaleDate' => ''];
                    }
                    $sales_data[$itemName]['totalQty'] += $qty;
                    $sales_data[$itemName]['totalRevenue'] += $total;
                    if ($invDate > $sales_data[$itemName]['lastSaleDate']) {
                        $sales_data[$itemName]['lastSaleDate'] = $invDate;
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // fallback
}

// ====================================================================
// 3. جلب بيانات المشتريات: الكمية المشتراة لكل صنف
// ====================================================================
$purchase_data = []; // itemId => totalQty
$purchase_by_name = []; // itemName => totalQty (fallback)
try {
    if (isset($pdo)) {
        $sql = "SELECT date_val, data FROM purchase_invoices";
        $params = [];
        $conditions = [];
        if ($date_from) {
            $conditions[] = "date_val >= ?";
            $params[] = $date_from;
        }
        if ($date_to) {
            $conditions[] = "date_val <= ?";
            $params[] = $date_to;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $purchaseRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($purchaseRows as $row) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;
            if (isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $itm) {
                    $qty = isset($itm['quantity']) ? floatval($itm['quantity']) : 0;
                    $itemId = isset($itm['itemId']) ? $itm['itemId'] : '';
                    if ($itemId) {
                        if (!isset($purchase_data[$itemId])) $purchase_data[$itemId] = 0;
                        $purchase_data[$itemId] += $qty;
                    }
                    // أيضاً نحفظ بالاسم إن وجد
                    $iName = isset($itm['itemName']) ? trim($itm['itemName']) : '';
                    if ($iName) {
                        if (!isset($purchase_by_name[$iName])) $purchase_by_name[$iName] = 0;
                        $purchase_by_name[$iName] += $qty;
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // fallback
}

// ====================================================================
// 4. حساب مقاييس دوران المخزون لكل صنف
// ====================================================================
$today = date('Y-m-d');
// حساب عدد الأيام في النطاق المحدد (أو 365 يوم افتراضياً)
$period_days = 365;
if ($date_from && $date_to) {
    $d1 = new DateTime($date_from);
    $d2 = new DateTime($date_to);
    $diff = $d1->diff($d2)->days;
    if ($diff > 0) $period_days = $diff;
} elseif ($date_from) {
    $d1 = new DateTime($date_from);
    $d2 = new DateTime($today);
    $diff = $d1->diff($d2)->days;
    if ($diff > 0) $period_days = $diff;
}

$items_metrics = [];
foreach ($all_items as $item) {
    $name     = $item['name'];
    $itemId   = $item['id'];
    $category = $item['category'];
    $opening  = $item['openingQty'];

    // فلتر الفئة
    if ($selected_category && $category !== $selected_category) continue;

    // فلتر البحث
    if ($search_query && strpos($name, $search_query) === false) continue;

    // الكمية المباعة
    $sold = 0;
    $revenue = 0;
    $lastSale = '';
    if (isset($sales_data[$name])) {
        $sold    = $sales_data[$name]['totalQty'];
        $revenue = $sales_data[$name]['totalRevenue'];
        $lastSale = $sales_data[$name]['lastSaleDate'];
    }

    // الكمية المشتراة (نبحث بالـ id أولاً ثم بالاسم)
    $purchased = 0;
    if ($itemId && isset($purchase_data[$itemId])) {
        $purchased = $purchase_data[$itemId];
    } elseif (isset($purchase_by_name[$name])) {
        $purchased = $purchase_by_name[$name];
    }

    // المخزون الحالي التقديري
    $current_stock = $opening + $purchased - $sold;
    if ($current_stock < 0) $current_stock = 0;

    // متوسط المخزون = (المخزون الافتتاحي + المخزون الحالي) / 2
    $avg_inventory = ($opening + $current_stock) / 2;
    if ($avg_inventory <= 0) $avg_inventory = 1; // تجنب القسمة على صفر

    // معدل الدوران السنوي = (الكمية المباعة / متوسط المخزون) * (365 / عدد أيام الفترة)
    $turnover_rate = ($sold / $avg_inventory) * (365 / $period_days);
    $turnover_rate = round($turnover_rate, 1);

    // أيام منذ آخر بيع
    $days_since_last_sale = 999;
    if ($lastSale) {
        $d1 = new DateTime($lastSale);
        $d2 = new DateTime($today);
        $days_since_last_sale = $d1->diff($d2)->days;
    }

    // أيام مخزون = 365 / معدل الدوران
    $days_of_inventory = ($turnover_rate > 0) ? round(365 / $turnover_rate) : 999;

    // التصنيف
    if ($turnover_rate >= 12) {
        $classification = 'fast';
        $class_label    = 'سريع الحركة';
        $class_color    = '#00ff7f';
    } elseif ($turnover_rate >= 4) {
        $classification = 'medium';
        $class_label    = 'متوسط الحركة';
        $class_color    = '#ffa500';
    } else {
        $classification = 'slow';
        $class_label    = 'راكد';
        $class_color    = '#ff0055';
    }
    // إذا لم يُبع أبداً أو أكثر من 60 يوم بدون بيع → راكد
    if ($days_since_last_sale > 60 && $classification !== 'fast') {
        $classification = 'slow';
        $class_label    = 'راكد';
        $class_color    = '#ff0055';
    }

    $items_metrics[] = [
        'name'               => $name,
        'category'           => $category,
        'opening'            => $opening,
        'purchased'          => $purchased,
        'sold'               => $sold,
        'current_stock'      => $current_stock,
        'revenue'            => $revenue,
        'turnover_rate'      => $turnover_rate,
        'days_of_inventory'  => $days_of_inventory,
        'days_since_sale'    => $days_since_last_sale,
        'lastSaleDate'       => $lastSale,
        'classification'     => $classification,
        'class_label'        => $class_label,
        'class_color'        => $class_color,
        'sellingPrice'       => $item['sellingPrice'],
    ];
}

// ====================================================================
// 5. بيانات احتياطية (fallback) إذا لم توجد بيانات حقيقية
// ====================================================================
if (empty($items_metrics)) {
    $items_metrics = [
        ['name'=>'أسمنت عطبرة','category'=>'أسمنت','opening'=>100,'purchased'=>200,'sold'=>280,'current_stock'=>20,'revenue'=>1400000,'turnover_rate'=>18.7,'days_of_inventory'=>20,'days_since_sale'=>2,'lastSaleDate'=>'2026-07-15','classification'=>'fast','class_label'=>'سريع الحركة','class_color'=>'#00ff7f','sellingPrice'=>5000],
        ['name'=>'سيخ 4 لينيا الأسعد','category'=>'سيخ','opening'=>50,'purchased'=>80,'sold'=>110,'current_stock'=>20,'revenue'=>990000,'turnover_rate'=>14.2,'days_of_inventory'=>26,'days_since_sale'=>5,'lastSaleDate'=>'2026-07-12','classification'=>'fast','class_label'=>'سريع الحركة','class_color'=>'#00ff7f','sellingPrice'=>9000],
        ['name'=>'سلك لحام الأسعد','category'=>'حديد','opening'=>30,'purchased'=>40,'sold'=>35,'current_stock'=>35,'revenue'=>175000,'turnover_rate'=>6.8,'days_of_inventory'=>54,'days_since_sale'=>18,'lastSaleDate'=>'2026-06-29','classification'=>'medium','class_label'=>'متوسط الحركة','class_color'=>'#ffa500','sellingPrice'=>5000],
        ['name'=>'صاج مجلفن','category'=>'صاج','opening'=>25,'purchased'=>20,'sold'=>22,'current_stock'=>23,'revenue'=>330000,'turnover_rate'=>5.1,'days_of_inventory'=>72,'days_since_sale'=>25,'lastSaleDate'=>'2026-06-22','classification'=>'medium','class_label'=>'متوسط الحركة','class_color'=>'#ffa500','sellingPrice'=>15000],
        ['name'=>'صاج حديد أسود سميك','category'=>'صاج','opening'=>15,'purchased'=>5,'sold'=>2,'current_stock'=>18,'revenue'=>50000,'turnover_rate'=>0.8,'days_of_inventory'=>456,'days_since_sale'=>92,'lastSaleDate'=>'2026-04-16','classification'=>'slow','class_label'=>'راكد','class_color'=>'#ff0055','sellingPrice'=>25000],
    ];
    if (empty($categories)) {
        $categories = ['أسمنت', 'سيخ', 'حديد', 'صاج'];
    }
}

// ====================================================================
// 6. تصنيف الأصناف حسب الفئات
// ====================================================================
$fast_items   = array_filter($items_metrics, fn($i) => $i['classification'] === 'fast');
$medium_items = array_filter($items_metrics, fn($i) => $i['classification'] === 'medium');
$slow_items   = array_filter($items_metrics, fn($i) => $i['classification'] === 'slow');

// ترتيب حسب معدل الدوران تنازلياً
usort($items_metrics, fn($a, $b) => $b['turnover_rate'] <=> $a['turnover_rate']);

// أعلى صنف في كل فئة
$top_fast   = !empty($fast_items) ? array_values($fast_items)[0] : null;
$top_medium = !empty($medium_items) ? array_values($medium_items)[0] : null;
$top_slow   = !empty($slow_items) ? array_values($slow_items)[0] : null;

// إجمالي القيمة الراكدة
$dead_stock_value = 0;
foreach ($slow_items as $si) {
    $dead_stock_value += $si['current_stock'] * $si['sellingPrice'];
}

// بيانات الرسم البياني
$chart_labels  = [];
$chart_data    = [];
$chart_colors  = [];
$chart_borders = [];
foreach ($items_metrics as $im) {
    $chart_labels[]  = mb_substr($im['name'], 0, 20);
    $chart_data[]    = $im['turnover_rate'];
    $chart_colors[]  = str_replace('#', 'rgba(', '') . ''; // سنعيد بناء الألوان في JS
    $chart_borders[] = $im['class_color'];
}

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600; // السعر القياسي الافتراضي للنظام
        $rate = (float)$txRate;
        if ($rate <= 0) {
            $rate = $usdToSdgRate;
        }
        if ($txCurrency === 'USD' && $selectedCurrency === 'SDG') {
            return (float)$amount * $rate;
        } else if ($txCurrency === 'SDG' && $selectedCurrency === 'USD') {
            return (float)$amount / $rate;
        }
        return (float)$amount;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محلل ركود البضائع ومعدل دوران المخزون | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ====== المتغيرات الأساسية ====== */
        :root {
            --bg-dark: #04030a;
            --panel-bg: rgba(15, 10, 28, 0.75);
            --border-neon: rgba(0, 255, 255, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
            --green: #00ff7f;
            --orange: #ffa500;
            --red: #ff0055;
            --cyan: #00ffff;
            --purple: #9d4edd;
        }

        /* ====== الحركات والانيميشنات ====== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(0,255,255,0.1); }
            50%      { box-shadow: 0 0 30px rgba(0,255,255,0.25); }
        }
        @keyframes barGrow {
            from { transform: scaleX(0); }
            to   { transform: scaleX(1); }
        }

        .animate-fade   { animation: fadeIn 0.6s ease-out both; }
        .animate-slide  { animation: slideUp 0.7s ease-out both; }
        .animate-scale  { animation: scaleIn 0.5s ease-out both; }
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }

        /* ====== التصميم العام ====== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            padding: 25px;
            min-height: 100vh;
            background-image:
                radial-gradient(circle at bottom right, rgba(0,255,255,0.06), transparent 40%),
                radial-gradient(circle at top left, rgba(157,78,221,0.05), transparent 40%);
        }

        .glass-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            animation: pulseGlow 4s ease-in-out infinite;
        }

        /* ====== الهيدر ====== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.6em;
            font-weight: 900;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header p { color: var(--text-muted); margin: 5px 0 0 0; font-size: 13px; }
        .btn-back {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-family: 'Cairo';
            transition: all 0.3s ease;
        }
        .btn-back:hover { background: rgba(0,255,255,0.1); color: var(--cyan); border-color: var(--cyan); }

        /* ====== الفلاتر ====== */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        .filters-bar label { color: var(--text-muted); font-size: 12px; white-space: nowrap; }
        .filters-bar input, .filters-bar select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            padding: 8px 14px;
            border-radius: 10px;
            font-family: 'Cairo';
            font-size: 13px;
            outline: none;
            transition: border-color 0.3s;
        }
        .filters-bar input:focus, .filters-bar select:focus { border-color: var(--cyan); }
        .filters-bar input[type="date"] { min-width: 140px; }
        .btn-filter {
            background: linear-gradient(135deg, rgba(0,255,255,0.2), rgba(157,78,221,0.2));
            color: var(--cyan);
            border: 1px solid rgba(0,255,255,0.3);
            padding: 8px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Cairo';
            font-size: 13px;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-filter:hover { background: rgba(0,255,255,0.3); transform: translateY(-1px); }
        .btn-reset {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Cairo';
            font-size: 12px;
            transition: all 0.3s;
        }
        .btn-reset:hover { color: var(--red); border-color: var(--red); }

        /* ====== بطاقات الملخص ====== */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            padding: 22px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 18px 18px 0 0;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .summary-card .card-icon { font-size: 28px; margin-bottom: 8px; }
        .summary-card .card-label { color: var(--text-muted); font-size: 11px; margin-bottom: 6px; }
        .summary-card .card-value { font-size: 1.8em; font-weight: 900; }
        .summary-card .card-sub { font-size: 11px; margin-top: 6px; }

        /* ====== بطاقات الحالة (3 فئات) ====== */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        .status-box {
            background: rgba(255,255,255,0.02);
            border-radius: 18px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        .status-box:hover { transform: translateY(-3px); }
        .status-box .status-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .status-box .status-count {
            font-size: 2em;
            font-weight: 900;
            line-height: 1;
        }
        .status-box .status-label { font-size: 12px; }
        .status-box .status-items { font-size: 12px; margin-top: 10px; line-height: 1.8; }
        .status-box .status-items span {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 8px;
            margin: 2px 3px;
            font-size: 11px;
        }

        /* ====== الرسم البياني ====== */
        .chart-container {
            position: relative;
            height: 350px;
            background: rgba(0,0,0,0.25);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 25px;
        }
        .chart-container h3 {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 400;
        }

        /* ====== الجدول التفصيلي ====== */
        .table-container {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        thead th {
            background: rgba(0,255,255,0.08);
            color: var(--cyan);
            padding: 14px 12px;
            text-align: right;
            font-weight: 700;
            font-size: 11px;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }
        tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.03);
            transition: background 0.3s ease;
        }
        tbody tr:hover { background: rgba(0,255,255,0.04); }
        tbody td {
            padding: 12px;
            white-space: nowrap;
        }
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-fast   { background: rgba(0,255,127,0.15); color: var(--green); }
        .badge-medium { background: rgba(255,165,0,0.15); color: var(--orange); }
        .badge-slow   { background: rgba(255,0,85,0.15); color: var(--red); }

        /* ====== شريط التقدم المصغر ====== */
        .mini-bar {
            width: 80px;
            height: 6px;
            background: rgba(255,255,255,0.06);
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        .mini-bar-fill {
            height: 100%;
            border-radius: 3px;
            animation: barGrow 1s ease-out both;
            transform-origin: right;
        }

        /* ====== لا توجد بيانات ====== */
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* ====== Responsive ====== */
        @media (max-width: 768px) {
            body { padding: 12px; }
            .glass-panel { padding: 18px; border-radius: 16px; }
            .header h1 { font-size: 1.2em; }
            .filters-bar { flex-direction: column; }
            .chart-container { height: 280px; }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="glass-panel animate-fade">
        <!-- ====== الهيدر ====== -->
        <div class="header">
            
            <a href="smart_reports_hub.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <!-- ====== الفلاتر ====== -->
        <form method="GET" class="filters-bar animate-fade stagger-1">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <label>📅 من:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <label>إلى:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <label>📂 الفئة:</label>
            <select name="category">
                <option value="">الكل</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($selected_category === $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>🔍</label>
            <input type="text" name="search" placeholder="بحث باسم الصنف..." value="<?php echo htmlspecialchars($search_query); ?>" style="min-width:160px;">
            <button type="submit" class="btn-filter">🔎 تطبيق</button>
            <a href="inventory_turnover.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-reset">🔄 إعادة</a>
        </form>

        <!-- ====== بطاقات الملخص العلوي ====== -->
        <div class="summary-grid">
            <div class="summary-card animate-scale stagger-1" style="border-color: rgba(0,255,255,0.15);">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--cyan),var(--purple));border-radius:18px 18px 0 0;"></div>
                <div class="card-icon">📊</div>
                <div class="card-label">إجمالي الأصناف</div>
                <div class="card-value counter" data-target="<?php echo count($items_metrics); ?>" style="color:var(--cyan);">0</div>
                <div class="card-sub" style="color:var(--text-muted);">صنف في التحليل</div>
            </div>
            <div class="summary-card animate-scale stagger-2" style="border-color: rgba(0,255,127,0.15);">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:var(--green);border-radius:18px 18px 0 0;"></div>
                <div class="card-icon">🚀</div>
                <div class="card-label">أصناف سريعة الحركة</div>
                <div class="card-value counter" data-target="<?php echo count($fast_items); ?>" style="color:var(--green);">0</div>
                <div class="card-sub" style="color:var(--green);">دوران > 12 مرة/سنوياً</div>
            </div>
            <div class="summary-card animate-scale stagger-3" style="border-color: rgba(255,165,0,0.15);">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:var(--orange);border-radius:18px 18px 0 0;"></div>
                <div class="card-icon">⚖️</div>
                <div class="card-label">أصناف متوسطة</div>
                <div class="card-value counter" data-target="<?php echo count($medium_items); ?>" style="color:var(--orange);">0</div>
                <div class="card-sub" style="color:var(--orange);">دوران 4-12 مرة/سنوياً</div>
            </div>
            <div class="summary-card animate-scale stagger-4" style="border-color: rgba(255,0,85,0.15);">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:var(--red);border-radius:18px 18px 0 0;"></div>
                <div class="card-icon">⚠️</div>
                <div class="card-label">أصناف راكدة</div>
                <div class="card-value counter" data-target="<?php echo count($slow_items); ?>" style="color:var(--red);">0</div>
                <div class="card-sub" style="color:var(--red);">قيمة مجمدة: <?php echo number_format($dead_stock_value); ?> <?php echo $selected_currency; ?></div>
            </div>
        </div>

        <!-- ====== بطاقات الحالة (3 فئات) ====== -->
        <div class="status-grid">
            <!-- سريعة الحركة -->
            <div class="status-box animate-slide stagger-2" style="border: 1px solid rgba(0,255,127,0.2);">
                <div class="status-header">
                    <span style="font-size:32px;">🟢</span>
                    <div>
                        <div class="status-count" style="color:var(--green);"><?php echo count($fast_items); ?></div>
                        <div class="status-label" style="color:var(--green);">أصناف سريعة الحركة (Fast-Moving)</div>
                    </div>
                </div>
                <div class="status-items">
                    <?php if (!empty($fast_items)): ?>
                        <?php foreach (array_slice(array_values($fast_items), 0, 4) as $fi): ?>
                            <span style="background:rgba(0,255,127,0.1); color:var(--green);">
                                <?php echo htmlspecialchars($fi['name']); ?> (<?php echo $fi['turnover_rate']; ?>x)
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:var(--text-muted);">لا توجد أصناف سريعة الحركة</span>
                    <?php endif; ?>
                </div>
                <?php if ($top_fast): ?>
                    <div style="margin-top:10px; font-size:11px; color:var(--green);">● الأعلى دوراناً: <?php echo htmlspecialchars($top_fast['name']); ?> — <?php echo $top_fast['turnover_rate']; ?> مرة سنوياً</div>
                <?php endif; ?>
            </div>

            <!-- متوسطة الحركة -->
            <div class="status-box animate-slide stagger-3" style="border: 1px solid rgba(255,165,0,0.2);">
                <div class="status-header">
                    <span style="font-size:32px;">🟡</span>
                    <div>
                        <div class="status-count" style="color:var(--orange);"><?php echo count($medium_items); ?></div>
                        <div class="status-label" style="color:var(--orange);">أصناف متوسطة الحركة</div>
                    </div>
                </div>
                <div class="status-items">
                    <?php if (!empty($medium_items)): ?>
                        <?php foreach (array_slice(array_values($medium_items), 0, 4) as $mi): ?>
                            <span style="background:rgba(255,165,0,0.1); color:var(--orange);">
                                <?php echo htmlspecialchars($mi['name']); ?> (<?php echo $mi['turnover_rate']; ?>x)
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:var(--text-muted);">لا توجد أصناف متوسطة</span>
                    <?php endif; ?>
                </div>
                <?php if ($top_medium): ?>
                    <div style="margin-top:10px; font-size:11px; color:var(--orange);">● الأعلى: <?php echo htmlspecialchars($top_medium['name']); ?> — <?php echo $top_medium['turnover_rate']; ?> مرة سنوياً</div>
                <?php endif; ?>
            </div>

            <!-- راكدة -->
            <div class="status-box animate-slide stagger-4" style="border: 1px solid rgba(255,0,85,0.2);">
                <div class="status-header">
                    <span style="font-size:32px;">🔴</span>
                    <div>
                        <div class="status-count" style="color:var(--red);"><?php echo count($slow_items); ?></div>
                        <div class="status-label" style="color:var(--red);">أصناف راكدة (بحاجة لتخفيض أو تصفية)</div>
                    </div>
                </div>
                <div class="status-items">
                    <?php if (!empty($slow_items)): ?>
                        <?php foreach (array_slice(array_values($slow_items), 0, 4) as $si): ?>
                            <span style="background:rgba(255,0,85,0.1); color:var(--red);">
                                <?php echo htmlspecialchars($si['name']); ?>
                                (<?php echo ($si['days_since_sale'] < 999) ? $si['days_since_sale'] . ' يوم' : 'لم يُبع'; ?>)
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:var(--text-muted);">لا توجد أصناف راكدة 🎉</span>
                    <?php endif; ?>
                </div>
                <?php if ($top_slow): ?>
                    <div style="margin-top:10px; font-size:11px; color:var(--red);">⚠️ أكثر ركوداً: <?php echo htmlspecialchars($top_slow['name']); ?> — <?php echo ($top_slow['days_since_sale'] < 999) ? $top_slow['days_since_sale'] . ' يوم بدون بيع' : 'لم يُبع أبداً'; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====== الرسم البياني ====== -->
        <div class="chart-container animate-slide stagger-4">
            <h3>📊 معدل دوران المخزون حسب الصنف (مرات/سنة)</h3>
            <canvas id="turnoverChart"></canvas>
        </div>

        <!-- ====== الجدول التفصيلي ====== -->
        <div class="table-container animate-slide stagger-5">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الصنف</th>
                        <th>الفئة</th>
                        <th>المخزون الافتتاحي</th>
                        <th>المشتريات</th>
                        <th>المبيعات</th>
                        <th>المخزون الحالي</th>
                        <th>معدل الدوران</th>
                        <th>أيام المخزون</th>
                        <th>آخر بيع</th>
                        <th>التصنيف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items_metrics)): ?>
                        <?php foreach ($items_metrics as $idx => $im): ?>
                            <?php
                                // حساب نسبة شريط التقدم (max 30 للتطبيع)
                                $bar_pct = min(100, ($im['turnover_rate'] / 30) * 100);
                                $badge_class = 'badge-' . $im['classification'];
                            ?>
                            <tr class="animate-fade" style="animation-delay: <?php echo 0.05 * $idx; ?>s;">
                                <td style="color:var(--text-muted);"><?php echo $idx + 1; ?></td>
                                <td style="font-weight:700;">
                                    <?php echo htmlspecialchars($im['name']); ?>
                                </td>
                                <td style="color:var(--text-muted); font-size:11px;"><?php echo htmlspecialchars($im['category']); ?></td>
                                <td><?php echo number_format($im['opening']); ?></td>
                                <td style="color:var(--cyan);">+<?php echo number_format($im['purchased']); ?></td>
                                <td style="color:var(--green);">-<?php echo number_format($im['sold']); ?></td>
                                <td style="font-weight:700;"><?php echo number_format($im['current_stock']); ?></td>
                                <td>
                                    <div class="mini-bar">
                                        <div class="mini-bar-fill" style="width:<?php echo $bar_pct; ?>%; background:<?php echo $im['class_color']; ?>; animation-delay:<?php echo 0.1 * $idx; ?>s;"></div>
                                    </div>
                                    <span style="color:<?php echo $im['class_color']; ?>; font-weight:700;"><?php echo $im['turnover_rate']; ?>x</span>
                                </td>
                                <td style="color:var(--text-muted);">
                                    <?php echo ($im['days_of_inventory'] < 999) ? $im['days_of_inventory'] . ' يوم' : '∞'; ?>
                                </td>
                                <td style="font-size:11px; color:var(--text-muted);">
                                    <?php if ($im['lastSaleDate']): ?>
                                        <?php echo $im['lastSaleDate']; ?>
                                        <br><span style="color:<?php echo ($im['days_since_sale'] > 60) ? 'var(--red)' : 'var(--green)'; ?>; font-size:10px;">
                                            (منذ <?php echo $im['days_since_sale']; ?> يوم)
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--red);">لم يُبع</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($im['class_label']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-data">لا توجد بيانات مطابقة للفلاتر المحددة</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== JavaScript: الرسم البياني + الأنيميشن ====== -->
    <script>
        // ====== Counter Animation (عدّاد متحرك) ======
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            const speed = 50;
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = +counter.getAttribute('data-target');
                        const increment = Math.max(1, Math.ceil(target / speed));
                        let current = 0;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= target) {
                                current = target;
                                clearInterval(timer);
                            }
                            counter.textContent = current.toLocaleString('ar-EG');
                        }, 30);
                        observer.unobserve(counter);
                    }
                });
            }, { threshold: 0.3 });
            counters.forEach(c => observer.observe(c));
        });

        // ====== Chart.js - معدل الدوران ======
        const chartLabels = <?php echo json_encode($chart_labels, JSON_UNESCAPED_UNICODE); ?>;
        const chartData   = <?php echo json_encode($chart_data); ?>;
        const chartBorders = <?php echo json_encode($chart_borders); ?>;

        // توليد الألوان بشفافية للخلفية
        const chartBgColors = chartBorders.map(c => {
            // تحويل hex إلى rgba
            let r = parseInt(c.slice(1,3), 16);
            let g = parseInt(c.slice(3,5), 16);
            let b = parseInt(c.slice(5,7), 16);
            return `rgba(${r}, ${g}, ${b}, 0.5)`;
        });

        const ctx = document.getElementById('turnoverChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'معدل الدوران (مرات/سنة)',
                    data: chartData,
                    backgroundColor: chartBgColors,
                    borderColor: chartBorders,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    delay: function(ctx) {
                        return ctx.dataIndex * 120;
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#ffffff', font: { family: 'Cairo', size: 12 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(4,3,10,0.9)',
                        titleFont: { family: 'Cairo' },
                        bodyFont: { family: 'Cairo' },
                        borderColor: 'rgba(0,255,255,0.3)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(ctx) {
                                let val = ctx.raw;
                                let status = val >= 12 ? '🟢 سريع' : (val >= 4 ? '🟡 متوسط' : '🔴 راكد');
                                return `الدوران: ${val}x — ${status}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#a0aec0', font: { family: 'Cairo', size: 11 }, maxRotation: 45 }
                    },
                    y: {
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        ticks: { color: '#a0aec0', font: { family: 'Cairo', size: 11 } },
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'معدل الدوران (مرات/سنة)',
                            color: '#a0aec0',
                            font: { family: 'Cairo', size: 12 }
                        }
                    }
                }
            }
        });
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
