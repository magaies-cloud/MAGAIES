<?php
$page_title = 'التنبؤ المالي الذكي (AI Forecasting)';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - التنبؤ بالسيولة والمبيعات القادمة (AI)
// ====================================================================
// الاتصال بقاعدة البيانات بشكل آمن
require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');

// ---- فلتر التاريخ ----
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : '';

// ---- مصفوفات البيانات ----
$monthly_sales = [];
$is_demo = false;
$category_data = [];

// ---- جلب بيانات المبيعات من قاعدة البيانات ----
try {
    if (isset($pdo)) {
        // بناء الاستعلام مع دعم فلتر التاريخ
        $where_clauses = ['is_deleted = 0'];
        $params = [];

        if (!empty($date_from)) {
            $where_clauses[] = 'date_val >= :date_from';
            $params[':date_from'] = $date_from;
        }
        if (!empty($date_to)) {
            $where_clauses[] = 'date_val <= :date_to';
            $params[':date_to'] = $date_to;
        }

        $where_sql = implode(' AND ', $where_clauses);

        // جلب كل الفواتير لحساب المجاميع بشكل صحيح (مع دعم JSON)
        $sql = "SELECT id, date_val, total_val, data
                FROM sales_invoices WHERE tenant_id = '{\}'

                WHERE $where_sql
                ORDER BY date_val ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // ---- حساب المبيعات الشهرية ----
        $monthly_map = [];
        $category_map = [];

        foreach ($rows as $row) {
            $date_val = $row['date_val'] ?? '';
            if (empty($date_val)) continue;

            // استخراج الشهر
            $month_key = substr($date_val, 0, 7); // 'YYYY-MM'

            // حساب المبلغ: أولاً total_val، وإذا كان فارغاً نستخرج من JSON
            $amount = floatval($row['total_val'] ?? 0);
            if ($amount <= 0 && !empty($row['data'])) {
                $json_data = json_decode($row['data'], true);
                if ($json_data && isset($json_data['totalAmount'])) {
                    $amount = floatval($json_data['totalAmount']);
                }
            }

            if (!isset($monthly_map[$month_key])) {
                $monthly_map[$month_key] = ['total' => 0, 'count' => 0];
            }
            $monthly_map[$month_key]['total'] += $amount;
            $monthly_map[$month_key]['count']++;

            // تصنيف حسب الفئة (من بيانات الأصناف)
            if (!empty($row['data'])) {
                $json_data = json_decode($row['data'], true);
                if ($json_data && isset($json_data['items']) && is_array($json_data['items'])) {
                    foreach ($json_data['items'] as $item) {
                        $cat = $item['category'] ?? ($item['itemName'] ?? 'أخرى');
                        $item_total = floatval($item['total'] ?? 0);
                        if (!isset($category_map[$cat])) {
                            $category_map[$cat] = 0;
                        }
                        $category_map[$cat] += $item_total;
                    }
                }
            }
        }

        // ترتيب حسب الشهر وأخذ آخر 6 أشهر
        ksort($monthly_map);
        $all_months = array_keys($monthly_map);
        $last_months = array_slice($all_months, -6);

        foreach ($last_months as $mk) {
            $monthly_sales[] = [
                'month'   => $mk,
                'total'   => $monthly_map[$mk]['total'],
                'count'   => $monthly_map[$mk]['count']
            ];
        }

        // ترتيب بيانات الفئات
        arsort($category_map);
        $category_data = array_slice($category_map, 0, 6, true);

        // إذا لم نجد بيانات حقيقية
        if (count($monthly_sales) === 0) {
            $is_demo = true;
        }
    } else {
        $is_demo = true;
    }
} catch (Exception $e) {
    error_log('Sales Forecasting Error: ' . $e->getMessage());
    $is_demo = true;
}

// ---- بيانات تجريبية كبديل ----
if ($is_demo) {
    $monthly_sales = [
        ['month' => '2026-02', 'total' => 85000,  'count' => 8],
        ['month' => '2026-03', 'total' => 102000, 'count' => 11],
        ['month' => '2026-04', 'total' => 120000, 'count' => 14],
        ['month' => '2026-05', 'total' => 115000, 'count' => 12],
        ['month' => '2026-06', 'total' => 145000, 'count' => 16],
        ['month' => '2026-07', 'total' => 130000, 'count' => 11],
    ];
    $category_data = [
        'حديد تسليح' => 52000,
        'أسمنت'       => 38000,
        'رمل وزلط'    => 22000,
        'سيراميك'     => 11000,
        'أدوات كهربائية' => 7000,
    ];
}

// ---- حساب معدل النمو والتنبؤ ----
$num_months = count($monthly_sales);
$last_month_sales = $num_months > 0 ? $monthly_sales[$num_months - 1]['total'] : 0;
$prev_month_sales = $num_months > 1 ? $monthly_sales[$num_months - 2]['total'] : 0;

// نسبة النمو بين آخر شهرين
$growth_rate = 0;
if ($prev_month_sales > 0) {
    $growth_rate = (($last_month_sales - $prev_month_sales) / $prev_month_sales) * 100;
}

// حساب متوسط النمو الشهري (اتجاه خطي)
$growth_rates = [];
for ($i = 1; $i < $num_months; $i++) {
    $prev = $monthly_sales[$i - 1]['total'];
    $curr = $monthly_sales[$i]['total'];
    if ($prev > 0) {
        $growth_rates[] = ($curr - $prev) / $prev;
    }
}
$avg_growth = count($growth_rates) > 0 ? array_sum($growth_rates) / count($growth_rates) : 0;

// التنبؤ بالأشهر الثلاثة القادمة
$forecast = [];
$base = $last_month_sales;
for ($i = 1; $i <= 3; $i++) {
    $predicted = $base * (1 + $avg_growth);
    $forecast[] = round($predicted);
    $base = $predicted;
}

// ---- توليد أسماء الأشهر بالعربية ----
$arabic_months = [
    '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس', '04' => 'أبريل',
    '05' => 'مايو',  '06' => 'يونيو',  '07' => 'يوليو', '08' => 'أغسطس',
    '09' => 'سبتمبر', '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر'
];

function getArabicMonthLabel($month_key, $arabic_months) {
    $parts = explode('-', $month_key);
    $year = $parts[0];
    $mon  = $parts[1];
    return ($arabic_months[$mon] ?? $mon) . ' ' . $year;
}

// تسميات المحور X
$chart_labels = [];
foreach ($monthly_sales as $ms) {
    $chart_labels[] = getArabicMonthLabel($ms['month'], $arabic_months);
}

// إضافة أشهر التنبؤ
$last_date = end($monthly_sales)['month'] ?? date('Y-m');
$last_parts = explode('-', $last_date);
$forecast_labels = [];
for ($i = 1; $i <= 3; $i++) {
    $fmon = intval($last_parts[1]) + $i;
    $fyear = intval($last_parts[0]);
    if ($fmon > 12) { $fmon -= 12; $fyear++; }
    $fmon_str = str_pad($fmon, 2, '0', STR_PAD_LEFT);
    $forecast_labels[] = ($arabic_months[$fmon_str] ?? $fmon_str) . ' ' . $fyear . ' (تنبؤ)';
}

// بيانات الرسم البياني
$actual_values = array_map(function($m) { return round($m['total']); }, $monthly_sales);
$forecast_values = $forecast;

// التنبؤ بالشهر القادم
$predicted_next = $forecast[0] ?? 0;

// ---- نص التوصية الديناميكي ----
$trend_direction = $avg_growth >= 0 ? 'نمو' : 'انخفاض';
$trend_pct = abs(round($avg_growth * 100, 1));
$trend_color_class = $avg_growth >= 0 ? 'trend-up' : 'trend-down';

if ($avg_growth >= 0.1) {
    $recommendation = "يُظهر التحليل اتجاه نمو قوي بنسبة <strong>" . $trend_pct . "%</strong> شهرياً. يُنصح بزيادة المخزون وتأمين خطوط إمداد إضافية من الموردين لتلبية الطلب المتزايد خلال الأشهر القادمة.";
} elseif ($avg_growth >= 0.02) {
    $recommendation = "يشهد السوق نمواً تدريجياً بمعدل <strong>" . $trend_pct . "%</strong> شهرياً. يُنصح بالحفاظ على مستويات المخزون الحالية مع مراقبة اتجاهات الطلب عن كثب.";
} elseif ($avg_growth >= -0.02) {
    $recommendation = "الأداء مستقر تقريباً بتغير <strong>" . $trend_pct . "%</strong> فقط. يُنصح بتنويع المنتجات واستكشاف فرص تسويقية جديدة لدفع عجلة النمو.";
} else {
    $recommendation = "⚠️ يلاحظ انخفاض في المبيعات بنسبة <strong>" . $trend_pct . "%</strong> شهرياً. يُنصح بمراجعة الأسعار والعروض الترويجية ودراسة أسباب تراجع الطلب بشكل عاجل.";
}

// ---- تجهيز بيانات JSON للرسم البياني ----
$chart_data_json = json_encode([
    'labels'    => array_merge($chart_labels, $forecast_labels),
    'actual'    => $actual_values,
    'forecast'  => $forecast_values,
    'numActual' => count($actual_values),
], JSON_UNESCAPED_UNICODE);

$category_json = json_encode($category_data, JSON_UNESCAPED_UNICODE);

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
    <title>التنبؤ المالي الذكي | Al-Sajana ERP</title>
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

        /* ---- متغيرات التصميم الأساسية ---- */
        :root {
            --bg-dark: #080312;
            --panel-bg: rgba(18, 10, 32, 0.7);
            --border-neon: rgba(157, 78, 221, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
            --accent: #9d4edd;
            --accent-pink: #ff007f;
            --green: #00e676;
            --red: #ff5252;
            --cyan: #00ffff;
        }

        /* ---- تهيئة عامة ---- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            padding: 30px;
            background-image: radial-gradient(circle at top left, rgba(157, 78, 221, 0.08), transparent 40%),
                              radial-gradient(circle at bottom right, rgba(255, 0, 127, 0.04), transparent 40%);
            min-height: 100vh;
            opacity: 0;
            animation: fadeInPage 0.8s ease forwards;
        }

        /* ---- حركة ظهور الصفحة ---- */
        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 8px rgba(157, 78, 221, 0.4); }
            50%      { box-shadow: 0 0 20px rgba(157, 78, 221, 0.8), 0 0 40px rgba(157, 78, 221, 0.3); }
        }
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes borderGlow {
            0%, 100% { border-color: rgba(157, 78, 221, 0.2); }
            50%      { border-color: rgba(157, 78, 221, 0.6); }
        }

        /* ---- اللوحة الزجاجية ---- */
        .glass-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeInScale 0.6s ease forwards;
        }

        /* ---- الهيدر ---- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 {
            margin: 0;
            background: linear-gradient(135deg, #9d4edd, #ff007f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
            font-size: clamp(18px, 3vw, 26px);
        }
        .header p {
            color: var(--text-muted);
            margin: 5px 0 0 0;
            font-size: 13px;
        }
        .btn-back {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
            font-family: 'Cairo', sans-serif;
        }
        .btn-back:hover {
            border-color: var(--accent);
            color: #fff;
            background: rgba(157, 78, 221, 0.1);
        }

        /* ---- شارة AI النابضة ---- */
        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(157, 78, 221, 0.15), rgba(255, 0, 127, 0.1));
            border: 1px solid rgba(157, 78, 221, 0.3);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            animation: pulse 2s infinite ease-in-out;
            margin-right: 10px;
        }
        .ai-badge .dot {
            width: 6px;
            height: 6px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        /* ---- شارة البيانات التجريبية ---- */
        .demo-badge {
            display: inline-block;
            background: rgba(255, 152, 0, 0.15);
            border: 1px solid rgba(255, 152, 0, 0.4);
            color: #ffab40;
            padding: 4px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            margin-right: 10px;
        }

        /* ---- فلتر التاريخ ---- */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            padding: 16px 20px;
            background: rgba(0,0,0,0.25);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            animation: slideUp 0.5s ease 0.2s backwards;
        }
        .filter-bar label {
            font-size: 13px;
            color: var(--text-muted);
        }
        .filter-bar input[type="date"] {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            padding: 7px 14px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            outline: none;
            transition: border-color 0.3s;
        }
        .filter-bar input[type="date"]:focus {
            border-color: var(--accent);
        }
        .filter-bar button {
            background: linear-gradient(135deg, var(--accent), var(--accent-pink));
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
        }
        .filter-bar button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(157, 78, 221, 0.4);
        }
        .filter-bar .btn-reset {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
        }
        .filter-bar .btn-reset:hover {
            border-color: var(--red);
            color: var(--red);
        }

        /* ---- بطاقات المؤشرات ---- */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .metric-card {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            padding: 22px;
            text-align: center;
            opacity: 0;
            animation: slideUp 0.5s ease forwards;
            transition: transform 0.3s, border-color 0.3s;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            border-color: var(--accent);
        }
        .metric-card:nth-child(1) { animation-delay: 0.3s; }
        .metric-card:nth-child(2) { animation-delay: 0.45s; }
        .metric-card:nth-child(3) { animation-delay: 0.6s; }
        .metric-card:nth-child(4) { animation-delay: 0.75s; }
        .metric-card .metric-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .metric-card .metric-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .metric-card .metric-value {
            font-size: 22px;
            font-weight: 900;
            background: linear-gradient(135deg, #fff, var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .metric-card .metric-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* ---- صندوق التوصيات ---- */
        .info-box {
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 25px;
            opacity: 0;
            animation: slideUp 0.6s ease 0.4s forwards;
            transition: background 0.5s, border-color 0.5s;
        }
        .info-box.trend-up {
            background: rgba(0, 230, 118, 0.05);
            border: 1.5px dashed var(--green);
        }
        .info-box.trend-down {
            background: rgba(255, 82, 82, 0.05);
            border: 1.5px dashed var(--red);
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-box.trend-up h4 { color: var(--green); }
        .info-box.trend-down h4 { color: var(--red); }
        .info-box p {
            margin: 0;
            font-size: 13px;
            line-height: 1.8;
            color: rgba(255,255,255,0.85);
        }

        /* ---- حاوية الرسم البياني ---- */
        .chart-section {
            opacity: 0;
            animation: slideUp 0.6s ease 0.6s forwards;
        }
        .chart-container {
            position: relative;
            height: 340px;
            background: rgba(0,0,0,0.2);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 25px;
        }
        .chart-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---- تبديل الفئات ---- */
        .toggle-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .toggle-btn {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-muted);
            padding: 7px 18px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .toggle-btn.active, .toggle-btn:hover {
            background: rgba(157, 78, 221, 0.15);
            border-color: var(--accent);
            color: #fff;
        }

        /* ---- الرسم البياني الفئوي ---- */
        .category-chart-wrap {
            display: none;
            opacity: 0;
            animation: fadeInScale 0.4s ease forwards;
        }
        .category-chart-wrap.visible {
            display: block;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="glass-panel">
        <!-- ---- الهيدر ---- -->
        <div class="header">
            
            <a href="smart_reports_hub.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <!-- ---- فلتر التاريخ ---- -->
        <form class="filter-bar" method="GET">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <label>📅 من:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <label>إلى:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <button type="submit">🔍 تصفية</button>
            <a href="sales_forecasting.php?currency=<?php echo htmlspecialchars($selected_currency); ?>">
                <button type="button" class="btn-reset">↺ إعادة ضبط</button>
            </a>
        </form>

        <!-- ---- بطاقات المؤشرات الرئيسية ---- -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">📊</div>
                <div class="metric-label">مبيعات آخر شهر</div>
                <div class="metric-value" data-counter="<?php echo round($last_month_sales); ?>">0</div>
                <div class="metric-sub"><?php echo htmlspecialchars($selected_currency); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><?php echo $growth_rate >= 0 ? '📈' : '📉'; ?></div>
                <div class="metric-label">معدل النمو</div>
                <div class="metric-value" data-counter="<?php echo round(abs($growth_rate), 1); ?>" data-suffix="%"
                     style="<?php echo $growth_rate >= 0 ? 'background:linear-gradient(135deg,#00e676,#69f0ae);' : 'background:linear-gradient(135deg,#ff5252,#ff8a80);'; ?> -webkit-background-clip:text; -webkit-text-fill-color:transparent;">0</div>
                <div class="metric-sub"><?php echo $growth_rate >= 0 ? '↑ ' . $trend_direction : '↓ ' . $trend_direction; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">🔮</div>
                <div class="metric-label">التنبؤ للشهر القادم</div>
                <div class="metric-value" data-counter="<?php echo round($predicted_next); ?>">0</div>
                <div class="metric-sub"><?php echo htmlspecialchars($selected_currency); ?> (تقدير)</div>
            </div>
            <div class="metric-card">
                <div class="metric-icon">🧾</div>
                <div class="metric-label">إجمالي الفواتير</div>
                <div class="metric-value" data-counter="<?php echo array_sum(array_column($monthly_sales, 'count')); ?>">0</div>
                <div class="metric-sub">فاتورة في الفترة</div>
            </div>
        </div>

        <!-- ---- صندوق التوصيات الذكية ---- -->
        <div class="info-box <?php echo $trend_color_class; ?>">
            <h4>
                <?php echo $avg_growth >= 0 ? '🟢' : '🔴'; ?>
                تحليل النظام الذكي — <?php echo $trend_direction; ?> بنسبة <?php echo $trend_pct; ?>% شهرياً
            </h4>
            <p><?php echo $recommendation; ?></p>
        </div>

        <!-- ---- تبديل العرض ---- -->
        <div class="toggle-bar">
            <button class="toggle-btn active" onclick="showChart('main', this)">📈 خط الاتجاه والتنبؤ</button>
            <button class="toggle-btn" onclick="showChart('category', this)">📦 تصنيف حسب الفئة</button>
        </div>

        <!-- ---- الرسم البياني الرئيسي ---- -->
        <div class="chart-section" id="mainChartSection">
            <div class="chart-title">📈 المبيعات الفعلية مقابل خط التنبؤ الذكي</div>
            <div class="chart-container">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>

        <!-- ---- الرسم البياني الفئوي ---- -->
        <div class="chart-section category-chart-wrap" id="categoryChartSection">
            <div class="chart-title">📦 توزيع المبيعات حسب الفئة</div>
            <div class="chart-container" style="height:300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // ====== بيانات الرسم البياني من PHP ======
        const chartData = <?php echo $chart_data_json; ?>;
        const categoryData = <?php echo $category_json; ?>;

        // ====== حركة عداد الأرقام ======
        function animateCounters() {
            document.querySelectorAll('[data-counter]').forEach(el => {
                const target = parseFloat(el.getAttribute('data-counter'));
                const suffix = el.getAttribute('data-suffix') || '';
                const duration = 1800;
                const start = performance.now();
                const isLarge = target > 1000;

                function update(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    // easeOutExpo
                    const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const current = target * eased;

                    if (isLarge) {
                        el.textContent = Math.round(current).toLocaleString('en-US') + suffix;
                    } else {
                        el.textContent = (Math.round(current * 10) / 10) + suffix;
                    }

                    if (progress < 1) requestAnimationFrame(update);
                }
                // بدء بتأخير حسب الترتيب
                setTimeout(() => requestAnimationFrame(update), 600);
            });
        }

        // ====== تبديل الرسوم البيانية ======
        function showChart(type, btn) {
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const mainSec = document.getElementById('mainChartSection');
            const catSec  = document.getElementById('categoryChartSection');

            if (type === 'main') {
                mainSec.style.display = 'block';
                catSec.classList.remove('visible');
            } else {
                mainSec.style.display = 'none';
                catSec.classList.add('visible');
            }
        }

        // ====== الرسم البياني الرئيسي - خط الاتجاه والتنبؤ ======
        const ctx = document.getElementById('forecastChart').getContext('2d');

        // تحضير بيانات الخطين
        const numActual = chartData.numActual;
        const allLabels = chartData.labels;

        // البيانات الفعلية: القيم + null للتنبؤات
        const actualData = [...chartData.actual, ...Array(chartData.forecast.length).fill(null)];

        // بيانات التنبؤ: null للسابق + آخر نقطة فعلية + التنبؤات (لربط الخط)
        const forecastData = [
            ...Array(numActual - 1).fill(null),
            chartData.actual[numActual - 1], // نقطة الربط
            ...chartData.forecast
        ];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'المبيعات الفعلية التاريخية',
                        data: actualData,
                        borderColor: '#00ffff',
                        backgroundColor: 'rgba(0, 255, 255, 0.08)',
                        borderWidth: 3,
                        tension: 0.35,
                        pointBackgroundColor: '#00ffff',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        fill: true
                    },
                    {
                        label: 'خط التنبؤ الذكي (AI Predicted)',
                        data: forecastData,
                        borderColor: '#ff007f',
                        backgroundColor: 'rgba(255, 0, 127, 0.06)',
                        borderWidth: 3,
                        borderDash: [8, 5],
                        tension: 0.35,
                        pointBackgroundColor: '#ff007f',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart',
                    delay: (ctx) => ctx.dataIndex * 200
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff',
                            font: { family: 'Cairo', size: 12 },
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(8, 3, 18, 0.95)',
                        titleFont: { family: 'Cairo', size: 13 },
                        bodyFont: { family: 'Cairo', size: 12 },
                        borderColor: 'rgba(157, 78, 221, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 14,
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y;
                                if (val !== null) {
                                    return context.dataset.label + ': ' + val.toLocaleString('en-US') + ' <?php echo htmlspecialchars($selected_currency); ?>';
                                }
                                return '';
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
                        ticks: {
                            color: '#a0aec0',
                            font: { family: 'Cairo', size: 11 },
                            callback: (v) => v.toLocaleString('en-US')
                        }
                    }
                }
            }
        });

        // ====== الرسم البياني الفئوي (دونت) ======
        const catLabels = Object.keys(categoryData);
        const catValues = Object.values(categoryData);
        const catColors = ['#9d4edd', '#ff007f', '#00ffff', '#00e676', '#ffab40', '#7c4dff'];

        if (catLabels.length > 0) {
            const ctxCat = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catValues,
                        backgroundColor: catColors.slice(0, catLabels.length),
                        borderColor: 'rgba(8,3,18,0.8)',
                        borderWidth: 3,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutBounce',
                        animateRotate: true,
                        animateScale: true
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#fff',
                                font: { family: 'Cairo', size: 12 },
                                usePointStyle: true,
                                padding: 16
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(8, 3, 18, 0.95)',
                            titleFont: { family: 'Cairo' },
                            bodyFont: { family: 'Cairo' },
                            borderColor: 'rgba(157, 78, 221, 0.3)',
                            borderWidth: 1,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.parsed.toLocaleString('en-US') + ' <?php echo htmlspecialchars($selected_currency); ?>';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ====== تشغيل حركة العدادات بعد تحميل الصفحة ======
        document.addEventListener('DOMContentLoaded', animateCounters);
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>

