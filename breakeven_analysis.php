<?php
$page_title = 'تحليل نقطة التعادل الفوري (Break-Even)';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تحليل نقطة التعادل الفوري (ديناميكي)
// ====================================================================
// الاتصال بقاعدة البيانات
require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

// العملة المختارة
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'USD') ? 1 : 1; // معدل التحويل - يمكن تعديله

// فلاتر التاريخ
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : '';

// الفئة المختارة
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

// التكاليف الثابتة اليدوية (إذا أدخلها المستخدم)
$manual_fixed = isset($_GET['fixed_costs']) ? floatval($_GET['fixed_costs']) : 0;

// ====================================================================
// 1. جلب الفئات المتاحة من جدول الأصناف
// ====================================================================
$categories = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT data FROM items WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)");
        $itemsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $catSet = [];
        foreach ($itemsRaw as $row) {
            $item = json_decode($row['data'], true);
            if ($item && !empty($item['category'])) {
                $catSet[$item['category']] = true;
            }
        }
        $categories = array_keys($catSet);
    }
} catch (Exception $e) {
    // fallback - فئات افتراضية
}
// Mock data fallback removed for SaaS environment
// إذا لم يتم اختيار فئة، اختر أول فئة
if (empty($selected_category)) {
    $selected_category = $categories[0];
}

// ====================================================================
// 2. حساب متوسط سعر البيع من فواتير المبيعات
// ====================================================================
$avg_selling_price = 0;
$total_sell_prices = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT data FROM sales_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0)");
        $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($salesRows as $row) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;

            // فلترة بالتاريخ
            $invDate = isset($inv['date']) ? $inv['date'] : '';
            if ($date_from && $invDate && $invDate < $date_from) continue;
            if ($date_to && $invDate && $invDate > $date_to) continue;

            // فلترة الأصناف حسب الفئة المختارة
            if (isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $itm) {
                    $itemName = isset($itm['itemName']) ? $itm['itemName'] : '';
                    // تحقق إذا اسم الصنف يحتوي على الفئة المختارة
                    if (strpos($itemName, $selected_category) !== false || empty($selected_category)) {
                        if (isset($itm['price']) && floatval($itm['price']) > 0) {
                            $total_sell_prices[] = floatval($itm['price']);
                        }
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // fallback
}
if (count($total_sell_prices) > 0) {
    $avg_selling_price = array_sum($total_sell_prices) / count($total_sell_prices);
} else {
    // قيم افتراضية حسب العملة
    $avg_selling_price = ($selected_currency === 'SDG') ? 1000000 : 1600;
}

// ====================================================================
// 3. حساب متوسط تكلفة الشراء من فواتير المشتريات
// ====================================================================
$avg_purchase_cost = 0;
$total_buy_prices = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT data FROM purchase_invoices WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0)");
        $purchaseRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($purchaseRows as $row) {
            $inv = json_decode($row['data'], true);
            if (!$inv) continue;

            // فلترة بالتاريخ
            $invDate = isset($inv['date']) ? $inv['date'] : '';
            if ($date_from && $invDate && $invDate < $date_from) continue;
            if ($date_to && $invDate && $invDate > $date_to) continue;

            if (isset($inv['items']) && is_array($inv['items'])) {
                foreach ($inv['items'] as $itm) {
                    if (isset($itm['price']) && floatval($itm['price']) > 0) {
                        $total_buy_prices[] = floatval($itm['price']);
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // fallback
}
if (count($total_buy_prices) > 0) {
    $avg_purchase_cost = array_sum($total_buy_prices) / count($total_buy_prices);
} else {
    $avg_purchase_cost = ($selected_currency === 'SDG') ? 800000 : 1300;
}

// ====================================================================
// 4. حساب التكاليف الثابتة من المدفوعات التشغيلية
// ====================================================================
$fixed_costs = 0;
if ($manual_fixed > 0) {
    // المستخدم أدخل تكاليف ثابتة يدوياً
    $fixed_costs = $manual_fixed;
} else {
    $payment_totals = 0;
    try {
        if (isset($pdo)) {
            // جلب المدفوعات المفردة - المصاريف التشغيلية
            $stmt = $pdo->query("SELECT data FROM single_payments WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0)");
            $payRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // جلب أسماء العملاء والموردين لاستبعادهم
            $excludeAccounts = [];
            try {
                $cstmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)");
                foreach ($cstmt->fetchAll(PDO::FETCH_ASSOC) as $cr) {
                    $cd = json_decode($cr['data'], true);
                    if ($cd && !empty($cd['name'])) $excludeAccounts[] = $cd['name'];
                }
                $sstmt = $pdo->query("SELECT data FROM suppliers WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)");
                foreach ($sstmt->fetchAll(PDO::FETCH_ASSOC) as $sr) {
                    $sd = json_decode($sr['data'], true);
                    if ($sd && !empty($sd['name'])) $excludeAccounts[] = $sd['name'];
                }
            } catch (Exception $e2) {}

            foreach ($payRows as $pr) {
                $pay = json_decode($pr['data'], true);
                if (!$pay) continue;

                // فلترة بالتاريخ
                $payDate = isset($pay['date']) ? $pay['date'] : '';
                if ($date_from && $payDate && $payDate < $date_from) continue;
                if ($date_to && $payDate && $payDate > $date_to) continue;

                // استبعاد المدفوعات للعملاء والموردين (هذه ليست مصاريف تشغيلية)
                $toAccount = isset($pay['toAccount']) ? $pay['toAccount'] : '';
                $isOperational = true;
                foreach ($excludeAccounts as $excl) {
                    if (!empty($excl) && strpos($toAccount, $excl) !== false) {
                        $isOperational = false;
                        break;
                    }
                }

                if ($isOperational && isset($pay['amountLocal'])) {
                    $payment_totals += floatval($pay['amountLocal']);
                }
            }
        }
    } catch (Exception $e) {
        // fallback
    }

    if ($payment_totals > 0) {
        $fixed_costs = $payment_totals;
    } else {
        // قيم افتراضية
        $fixed_costs = ($selected_currency === 'SDG') ? 3000000 : 5000;
    }
}

// ====================================================================
// 5. حساب نقطة التعادل
// ====================================================================
$price_per_ton = $avg_selling_price;
$variable_cost_per_ton = $avg_purchase_cost;
$contribution_margin = $price_per_ton - $variable_cost_per_ton;

// حماية من القسمة على صفر
if ($contribution_margin <= 0) {
    $contribution_margin = 1;
    $breakeven_point_units = 0;
    $margin_warning = true;
} else {
    $breakeven_point_units = $fixed_costs / $contribution_margin;
    $margin_warning = false;
}

// إيرادات التعادل
$breakeven_revenue = $breakeven_point_units * $price_per_ton;

// نسبة هامش المساهمة
$margin_ratio = ($price_per_ton > 0) ? ($contribution_margin / $price_per_ton) * 100 : 0;

// ====================================================================
// 6. توليد بيانات الرسم البياني الديناميكية (7 نقاط من 0 إلى 2x نقطة التعادل)
// ====================================================================
$max_units = max(ceil($breakeven_point_units * 2), 10);
$step = $max_units / 6; // 7 نقاط (0 + 6 خطوات)
$chart_labels = [];
$chart_revenue = [];
$chart_costs = [];

for ($i = 0; $i <= 6; $i++) {
    $units = round($step * $i, 1);
    $chart_labels[] = $units;
    $chart_revenue[] = round($units * $price_per_ton, 2);
    $chart_costs[] = round($fixed_costs + ($units * $variable_cost_per_ton), 2);
}

// تحويل لـ JSON للرسم البياني
$chart_labels_json = json_encode($chart_labels);
$chart_revenue_json = json_encode($chart_revenue);
$chart_costs_json = json_encode($chart_costs);

// عدد بيانات المبيعات والمشتريات المستخدمة
$data_sell_count = count($total_sell_prices);
$data_buy_count = count($total_buy_prices);

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
    <title>تحليل نقطة التعادل | Al-Sajana ERP</title>
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

        /* ========== المتغيرات الأساسية ========== */
        :root {
            --bg-dark: #050510;
            --panel-bg: rgba(15, 12, 30, 0.7);
            --border-neon: rgba(255, 165, 0, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
            --accent-orange: #ffa500;
            --accent-pink: #ff007f;
            --accent-green: #00ff7f;
            --accent-cyan: #00d4ff;
            --accent-purple: #a855f7;
        }

        /* ========== التنسيقات الأساسية ========== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            padding: 20px 30px;
            min-height: 100vh;
            background-image:
                radial-gradient(circle at top right, rgba(255, 165, 0, 0.08), transparent 40%),
                radial-gradient(circle at bottom left, rgba(255, 0, 127, 0.05), transparent 40%);
        }

        /* ========== أنيميشن fadeIn ========== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ========== أنيميشن slideUp ========== */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ========== أنيميشن pulseGlow ========== */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(255, 165, 0, 0.1); }
            50% { box-shadow: 0 0 30px rgba(255, 165, 0, 0.25); }
        }

        /* ========== أنيميشن shimmer للأرقام ========== */
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        /* ========== لوح الزجاج الرئيسي ========== */
        .glass-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.6s ease-out;
        }

        /* ========== الهيدر ========== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
            animation: slideUp 0.5s ease-out;
        }

        .header h1 {
            margin: 0;
            background: linear-gradient(135deg, #ffa500, #ff007f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
            font-size: 1.6em;
        }

        .btn-back {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: rgba(255, 165, 0, 0.1);
            border-color: rgba(255, 165, 0, 0.4);
            color: #ffa500;
        }

        /* ========== قسم الفلاتر ========== */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 165, 0, 0.08);
            border-radius: 16px;
            animation: slideUp 0.6s ease-out 0.1s both;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
        }

        .filter-group input,
        .filter-group select {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            padding: 8px 14px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            outline: none;
            transition: border-color 0.3s ease;
            min-width: 140px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: rgba(255, 165, 0, 0.5);
        }

        .filter-group input[type="number"] {
            min-width: 170px;
        }

        .btn-filter {
            background: linear-gradient(135deg, #ffa500, #ff6a00);
            color: #000;
            border: none;
            padding: 9px 22px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 165, 0, 0.4);
        }

        .btn-reset {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 9px 16px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-reset:hover {
            border-color: rgba(255, 0, 85, 0.4);
            color: #ff0055;
        }

        /* ========== شبكة البطاقات ========== */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .metric-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 165, 0, 0.1);
            border-radius: 16px;
            padding: 22px;
            text-align: center;
            transition: all 0.4s ease;
            animation: slideUp 0.6s ease-out both;
            position: relative;
            overflow: hidden;
        }

        /* تأخير متدرج للبطاقات */
        .metric-box:nth-child(1) { animation-delay: 0.15s; }
        .metric-box:nth-child(2) { animation-delay: 0.25s; }
        .metric-box:nth-child(3) { animation-delay: 0.35s; }
        .metric-box:nth-child(4) { animation-delay: 0.45s; }
        .metric-box:nth-child(5) { animation-delay: 0.55s; }
        .metric-box:nth-child(6) { animation-delay: 0.65s; }

        .metric-box:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 165, 0, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .metric-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 165, 0, 0.03), transparent);
            transition: left 0.6s ease;
        }
        .metric-box:hover::before {
            left: 100%;
        }

        .metric-label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            display: block;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 1.8em;
            font-weight: 900;
            margin: 5px 0;
            display: block;
        }

        .metric-sub {
            font-size: 11px;
            color: var(--text-muted);
            display: block;
        }

        .metric-currency {
            font-size: 12px;
            font-weight: 400;
            opacity: 0.7;
        }

        /* ========== ألوان البطاقات ========== */
        .color-pink .metric-value { color: #ff007f; }
        .color-green .metric-value { color: #00ff7f; }
        .color-cyan .metric-value { color: #00d4ff; }
        .color-orange .metric-value { color: #ffa500; }
        .color-purple .metric-value { color: #a855f7; }
        .color-red .metric-value { color: #ff4444; }

        .color-green { border-color: rgba(0, 255, 127, 0.15); }
        .color-cyan { border-color: rgba(0, 212, 255, 0.15); }
        .color-purple { border-color: rgba(168, 85, 247, 0.15); }

        /* ========== حاوية الرسم البياني ========== */
        .chart-section {
            animation: slideUp 0.7s ease-out 0.5s both;
        }

        .chart-container {
            position: relative;
            height: 380px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .chart-title {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-title span {
            font-size: 18px;
        }

        /* ========== تحذير الهامش السلبي ========== */
        .warning-box {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideUp 0.5s ease-out 0.2s both;
        }

        .warning-box .icon { font-size: 24px; }
        .warning-box .text { color: #ff8888; font-size: 13px; }

        /* ========== شريط مصادر البيانات ========== */
        .data-source-bar {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.01);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            animation: fadeIn 1s ease-out 0.8s both;
        }

        .data-badge {
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .data-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-green { background: #00ff7f; }
        .dot-orange { background: #ffa500; }
        .dot-pink { background: #ff007f; }
        .dot-cyan { background: #00d4ff; }

        /* ========== ريسبونسف ========== */
        @media (max-width: 768px) {
            body { padding: 12px; }
            .glass-panel { padding: 18px; }
            .header h1 { font-size: 1.2em; }
            .grid-4 { grid-template-columns: 1fr 1fr; }
            .filters-bar { gap: 8px; }
            .metric-value { font-size: 1.3em; }
        }

        @media (max-width: 480px) {
            .grid-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="glass-panel">
        <!-- ========== الهيدر ========== -->
        <div class="header">
            
            <a href="smart_reports_hub.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <!-- ========== شريط الفلاتر ========== -->
        <form method="GET" class="filters-bar">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">

            <div class="filter-group">
                <label>📦 الفئة</label>
                <select name="category">
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($cat === $selected_category) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>📅 من تاريخ</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>

            <div class="filter-group">
                <label>📅 إلى تاريخ</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>

            <div class="filter-group">
                <label>💰 تكاليف ثابتة يدوية (اختياري)</label>
                <input type="number" name="fixed_costs" step="0.01" min="0"
                       placeholder="اتركه فارغاً للحساب التلقائي"
                       value="<?php echo ($manual_fixed > 0) ? $manual_fixed : ''; ?>">
            </div>

            <button type="submit" class="btn-filter">🔍 تحليل</button>
            <a href="breakeven_analysis.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-reset">🔄 إعادة تعيين</a>
        </form>

        <!-- ========== تحذير الهامش السلبي ========== -->
        <?php if (isset($margin_warning) && $margin_warning): ?>
        <div class="warning-box">
            <span class="icon">⚠️</span>
            <span class="text">
                <strong>تنبيه:</strong> هامش المساهمة سالب أو صفر — سعر البيع أقل من أو يساوي تكلفة الشراء!
                يجب مراجعة الأسعار أو تغيير الفئة المحللة. النتائج المعروضة تقريبية.
            </span>
        </div>
        <?php endif; ?>

        <!-- ========== بطاقات المؤشرات ========== -->
        <div class="grid-4">
            <!-- التكاليف الثابتة -->
            <div class="metric-box color-pink">
                <span class="metric-label">التكاليف الثابتة <?php echo ($manual_fixed > 0) ? '(يدوي)' : '(تلقائي)'; ?></span>
                <span class="metric-value" data-counter="<?php echo $fixed_costs; ?>">0</span>
                <span class="metric-currency"><?php echo htmlspecialchars($selected_currency); ?></span>
                <span class="metric-sub">إيجارات، رواتب، مصاريف تشغيلية</span>
            </div>

            <!-- سعر البيع المتوسط -->
            <div class="metric-box color-cyan">
                <span class="metric-label">متوسط سعر البيع / وحدة</span>
                <span class="metric-value" data-counter="<?php echo $price_per_ton; ?>">0</span>
                <span class="metric-currency"><?php echo htmlspecialchars($selected_currency); ?></span>
                <span class="metric-sub">من <?php echo $data_sell_count; ?> عملية بيع</span>
            </div>

            <!-- تكلفة الشراء المتوسطة -->
            <div class="metric-box color-orange">
                <span class="metric-label">متوسط تكلفة الشراء / وحدة</span>
                <span class="metric-value" data-counter="<?php echo $variable_cost_per_ton; ?>">0</span>
                <span class="metric-currency"><?php echo htmlspecialchars($selected_currency); ?></span>
                <span class="metric-sub">من <?php echo $data_buy_count; ?> عملية شراء</span>
            </div>

            <!-- هامش المساهمة -->
            <div class="metric-box color-purple">
                <span class="metric-label">هامش المساهمة / وحدة</span>
                <span class="metric-value" data-counter="<?php echo $contribution_margin; ?>">0</span>
                <span class="metric-currency"><?php echo htmlspecialchars($selected_currency); ?></span>
                <span class="metric-sub">نسبة الهامش: <?php echo number_format($margin_ratio, 1); ?>%</span>
            </div>

            <!-- نقطة التعادل بالوحدات -->
            <div class="metric-box color-green" style="border-color: rgba(0,255,127,0.25);">
                <span class="metric-label">🎯 نقطة التعادل (وحدات)</span>
                <span class="metric-value" data-counter="<?php echo $breakeven_point_units; ?>" data-decimals="2" style="font-size: 2.2em;">0</span>
                <span class="metric-sub" style="color: #00ff7f;">▲ أي مبيعات فوق هذا الرقم = أرباح صافية!</span>
            </div>

            <!-- إيرادات التعادل -->
            <div class="metric-box color-cyan">
                <span class="metric-label">💵 إيرادات التعادل المطلوبة</span>
                <span class="metric-value" data-counter="<?php echo $breakeven_revenue; ?>">0</span>
                <span class="metric-currency"><?php echo htmlspecialchars($selected_currency); ?></span>
                <span class="metric-sub">حجم المبيعات بالقيمة المطلوب تحقيقه</span>
            </div>
        </div>

        <!-- ========== الرسم البياني ========== -->
        <div class="chart-section">
            <div class="chart-title">
                <span>📈</span>
                الرسم البياني لنقطة التعادل — خط الإيرادات مقابل خط التكاليف الكلية
            </div>
            <div class="chart-container">
                <canvas id="breakevenChart"></canvas>
            </div>
        </div>

        <!-- ========== شريط مصادر البيانات ========== -->
        <div class="data-source-bar">
            <span class="data-badge">
                <span class="dot dot-green"></span>
                فواتير المبيعات: <?php echo $data_sell_count; ?> سجل
            </span>
            <span class="data-badge">
                <span class="dot dot-orange"></span>
                فواتير المشتريات: <?php echo $data_buy_count; ?> سجل
            </span>
            <span class="data-badge">
                <span class="dot dot-pink"></span>
                التكاليف الثابتة: <?php echo ($manual_fixed > 0) ? 'إدخال يدوي' : 'من المدفوعات التشغيلية'; ?>
            </span>
            <span class="data-badge">
                <span class="dot dot-cyan"></span>
                الفئة: <?php echo htmlspecialchars($selected_category); ?>
            </span>
        </div>
    </div>

    <!-- ========== سكربت الرسم البياني والأنيميشن ========== -->
    <script>
    // ====================================================================
    // أنيميشن العداد للأرقام - Counter Animation
    // ====================================================================
    function animateCounters() {
        const counters = document.querySelectorAll('[data-counter]');
        counters.forEach(el => {
            const target = parseFloat(el.getAttribute('data-counter'));
            const decimals = parseInt(el.getAttribute('data-decimals') || '2');
            const duration = 1500; // مللي ثانية
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // تأثير easeOutExpo للحركة السلسة
                const eased = 1 - Math.pow(1 - progress, 4);
                const currentValue = target * eased;

                // تنسيق الرقم بفواصل الآلاف
                el.textContent = currentValue.toLocaleString('en-US', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    el.textContent = target.toLocaleString('en-US', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                }
            }

            // بدء الأنيميشن بعد تأخير قصير
            setTimeout(() => requestAnimationFrame(updateCounter), 400);
        });
    }

    // تشغيل العدادات عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', animateCounters);

    // ====================================================================
    // الرسم البياني الديناميكي - Dynamic Chart
    // ====================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('breakevenChart').getContext('2d');

        // البيانات من PHP
        const chartLabels = <?php echo $chart_labels_json; ?>;
        const chartRevenue = <?php echo $chart_revenue_json; ?>;
        const chartCosts = <?php echo $chart_costs_json; ?>;
        const breakevenUnits = <?php echo json_encode(round($breakeven_point_units, 2)); ?>;
        const breakevenRevenue = <?php echo json_encode(round($breakeven_revenue, 2)); ?>;

        // تسميات عربية
        const arabicLabels = chartLabels.map(v => v.toLocaleString('en-US', {maximumFractionDigits: 1}) + ' وحدة');

        // ====================================================================
        // خط نقطة التعادل العمودي (Annotation Plugin بديل يدوي)
        // ====================================================================
        const breakevenLinePlugin = {
            id: 'breakevenLine',
            afterDraw(chart) {
                const xScale = chart.scales.x;
                const yScale = chart.scales.y;
                const ctx2 = chart.ctx;

                // حساب موقع نقطة التعادل على المحور X
                // نبحث عن أقرب نقطة بيانات
                let breakevenX = null;
                let breakevenY = null;

                // حساب النسبة بين نقاط البيانات
                for (let i = 0; i < chartLabels.length - 1; i++) {
                    if (chartLabels[i] <= breakevenUnits && chartLabels[i + 1] >= breakevenUnits) {
                        const ratio = (breakevenUnits - chartLabels[i]) / (chartLabels[i + 1] - chartLabels[i]);
                        const x1 = xScale.getPixelForValue(i);
                        const x2 = xScale.getPixelForValue(i + 1);
                        breakevenX = x1 + (x2 - x1) * ratio;
                        breakevenY = yScale.getPixelForValue(breakevenRevenue);
                        break;
                    }
                }

                if (breakevenX === null) return;

                // رسم الخط العمودي
                ctx2.save();
                ctx2.beginPath();
                ctx2.setLineDash([6, 4]);
                ctx2.strokeStyle = 'rgba(255, 255, 255, 0.4)';
                ctx2.lineWidth = 1.5;
                ctx2.moveTo(breakevenX, yScale.top);
                ctx2.lineTo(breakevenX, yScale.bottom);
                ctx2.stroke();

                // رسم النقطة
                ctx2.beginPath();
                ctx2.arc(breakevenX, breakevenY, 7, 0, Math.PI * 2);
                ctx2.fillStyle = '#ffa500';
                ctx2.fill();
                ctx2.strokeStyle = '#fff';
                ctx2.lineWidth = 2;
                ctx2.stroke();

                // تسمية نقطة التعادل
                ctx2.fillStyle = '#ffa500';
                ctx2.font = 'bold 11px Cairo';
                ctx2.textAlign = 'center';
                ctx2.fillText('⚡ نقطة التعادل', breakevenX, yScale.top + 16);
                ctx2.fillStyle = 'rgba(255, 255, 255, 0.7)';
                ctx2.font = '10px Cairo';
                ctx2.fillText(breakevenUnits.toLocaleString('en-US', {maximumFractionDigits: 2}) + ' وحدة', breakevenX, yScale.top + 32);

                // منطقة الربح (تلوين خفيف)
                ctx2.fillStyle = 'rgba(0, 255, 127, 0.03)';
                ctx2.fillRect(breakevenX, yScale.top, xScale.right - breakevenX, yScale.bottom - yScale.top);

                // منطقة الخسارة (تلوين خفيف)
                ctx2.fillStyle = 'rgba(255, 0, 85, 0.03)';
                ctx2.fillRect(xScale.left, yScale.top, breakevenX - xScale.left, yScale.bottom - yScale.top);

                // تسميات المناطق
                ctx2.font = 'bold 12px Cairo';
                ctx2.fillStyle = 'rgba(0, 255, 127, 0.4)';
                ctx2.textAlign = 'center';
                if (xScale.right - breakevenX > 60) {
                    ctx2.fillText('✅ منطقة الربح', (breakevenX + xScale.right) / 2, yScale.bottom - 10);
                }
                ctx2.fillStyle = 'rgba(255, 0, 85, 0.4)';
                if (breakevenX - xScale.left > 60) {
                    ctx2.fillText('❌ منطقة الخسارة', (xScale.left + breakevenX) / 2, yScale.bottom - 10);
                }

                ctx2.restore();
            }
        };

        // ====================================================================
        // إنشاء الرسم البياني
        // ====================================================================
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: arabicLabels,
                datasets: [
                    {
                        label: 'إجمالي الإيرادات (المبيعات)',
                        data: chartRevenue,
                        borderColor: '#00ff7f',
                        backgroundColor: 'rgba(0, 255, 127, 0.05)',
                        borderWidth: 3,
                        tension: 0.1,
                        fill: false,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#00ff7f',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'إجمالي التكاليف (ثابتة + متغيرة)',
                        data: chartCosts,
                        borderColor: '#ff0055',
                        backgroundColor: 'rgba(255, 0, 85, 0.05)',
                        borderWidth: 2.5,
                        borderDash: [6, 4],
                        tension: 0.1,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#ff0055',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // أنيميشن رسم الخط التدريجي
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 150 + context.datasetIndex * 300;
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff',
                            font: { family: 'Cairo', size: 12 },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(5, 5, 16, 0.95)',
                        titleColor: '#ffa500',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 165, 0, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 14,
                        titleFont: { family: 'Cairo', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Cairo', size: 12 },
                        displayColors: true,
                        callbacks: {
                            title: function(tooltipItems) {
                                return '📊 ' + tooltipItems[0].label;
                            },
                            label: function(context) {
                                const val = context.parsed.y.toLocaleString('en-US', {maximumFractionDigits: 0});
                                const label = context.dataset.label;
                                return label + ': ' + val + ' <?php echo htmlspecialchars($selected_currency); ?>';
                            },
                            afterBody: function(tooltipItems) {
                                if (tooltipItems.length >= 2) {
                                    const revenue = tooltipItems[0].parsed.y;
                                    const cost = tooltipItems[1].parsed.y;
                                    const diff = revenue - cost;
                                    const sign = diff >= 0 ? '✅ ربح: +' : '❌ خسارة: ';
                                    return [
                                        '─────────────',
                                        sign + Math.abs(diff).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' <?php echo htmlspecialchars($selected_currency); ?>'
                                    ];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            color: '#a0aec0',
                            font: { family: 'Cairo', size: 11 }
                        }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            color: '#a0aec0',
                            font: { family: 'Cairo', size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    }
                }
            },
            plugins: [breakevenLinePlugin]
        });
    });
    </script>
    </div>
    <script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
