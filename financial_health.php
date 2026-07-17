<?php
$page_title = 'تقرير الصحة المالية ومعدلات الأمان';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تقرير الصحة المالية ومؤشرات الأمان
// ====================================================================
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// === فلترة التاريخ (من - إلى) ===
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : '';

// === متغيرات الحساب الديناميكي ===
$total_cash = 0;
$total_receivables = 0;
$total_payables = 0;
$quick_ratio = 0;
$safety_rate = 0;
$dso_days = 0;
$aging_report = [];
$total_debt_all = 0;
$total_critical_90 = 0;
$bad_debt_provision_total = 0;
$db_connected = false;

try {
    @include_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        $db_connected = true;

        // === حساب النقدية الحالية من الخزن والبنوك ===
        $safes_rows = $pdo->query("SELECT data FROM safes WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($safes_rows as $row) {
            $d = json_decode($row['data'], true);
            $total_cash += floatval($d['currentBalance'] ?? 0);
        }
        $banks_rows = $pdo->query("SELECT data FROM banks WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($banks_rows as $row) {
            $d = json_decode($row['data'], true);
            $total_cash += floatval($d['currentBalance'] ?? 0);
        }

        // === حساب ذمم العملاء المدينة (مستحقة على العملاء) ===
        $cust_rows = $pdo->query("SELECT data FROM customers WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cust_rows as $row) {
            $d = json_decode($row['data'], true);
            $bal = floatval($d['currentBalance'] ?? 0);
            if ($bal > 0) $total_receivables += $bal;
        }

        // === حساب ذمم الموردين الدائنة (مستحقة للموردين) ===
        $sup_rows = $pdo->query("SELECT data FROM suppliers WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sup_rows as $row) {
            $d = json_decode($row['data'], true);
            $bal = floatval($d['currentBalance'] ?? 0);
            if ($bal > 0) $total_payables += $bal;
        }

        // === حساب نسبة التداول السريعة Quick Ratio ===
        // Quick Ratio = (النقدية + ذمم مدينة) / الالتزامات المتداولة
        if ($total_payables > 0) {
            $quick_ratio = round(($total_cash + $total_receivables) / $total_payables, 2);
        } else {
            $quick_ratio = ($total_cash + $total_receivables) > 0 ? 99.0 : 0;
        }

        // === حساب معدل الأمان والاعتماد الذاتي ===
        // Safety Rate = الأصول / (الأصول + الالتزامات) × 100
        $total_assets = $total_cash + $total_receivables;
        if (($total_assets + $total_payables) > 0) {
            $safety_rate = round(($total_assets / ($total_assets + $total_payables)) * 100, 1);
        } else {
            $safety_rate = 100;
        }

        // === حساب DSO من فواتير البيع الحقيقية ===
        // بناء استعلام فواتير البيع مع فلترة التاريخ
        $inv_query = "SELECT date_val, total_val, data FROM sales_invoices WHERE is_deleted = 0";
        $inv_params = [];
        if ($date_from) {
            $inv_query .= " AND date_val >= :date_from";
            $inv_params[':date_from'] = $date_from;
        }
        if ($date_to) {
            $inv_query .= " AND date_val <= :date_to";
            $inv_params[':date_to'] = $date_to;
        }
        $inv_query .= " ORDER BY date_val DESC";

        $inv_stmt = $pdo->prepare($inv_query);
        $inv_stmt->execute($inv_params);
        $inv_rows = $inv_stmt->fetchAll(PDO::FETCH_ASSOC);

        // حساب إجمالي المبيعات خلال آخر 30 يوم لمعادلة DSO
        $total_sales_30 = 0;
        $cutoff_30 = date('Y-m-d', strtotime('-30 days'));
        foreach ($inv_rows as $row) {
            if ($row['date_val'] >= $cutoff_30) {
                // استخدام total_val أولاً، ثم JSON كبديل
                $amount = floatval($row['total_val'] ?? 0);
                if ($amount <= 0) {
                    $d = json_decode($row['data'], true);
                    $amount = floatval($d['totalAmount'] ?? $d['grandTotal'] ?? 0);
                }
                $total_sales_30 += $amount;
            }
        }
        if ($total_sales_30 > 0) {
            $dso_days = round(($total_receivables / $total_sales_30) * 30, 1);
        }

        // === حساب أعمار الديون من تواريخ آخر فاتورة لكل عميل ===
        // بناء خريطة آخر فاتورة بيع لكل عميل
        $last_sale_map = [];
        foreach ($inv_rows as $row) {
            $d = json_decode($row['data'], true);
            $cid = $d['customerId'] ?? '';
            if ($cid && (!isset($last_sale_map[$cid]) || $row['date_val'] > $last_sale_map[$cid])) {
                $last_sale_map[$cid] = $row['date_val'];
            }
        }

        // بناء تقرير أعمار الديون بناءً على أرصدة العملاء وتواريخ آخر فاتورة
        foreach ($cust_rows as $row) {
            $d = json_decode($row['data'], true);
            $cid = $d['id'] ?? '';
            $cname = $d['name'] ?? 'عميل غير معروف';
            $bal = floatval($d['currentBalance'] ?? 0);

            // فقط العملاء اللي عندهم رصيد موجب (عليهم ديون)
            if ($bal > 0) {
                $days = 0;
                if (isset($last_sale_map[$cid])) {
                    $days = (int)((time() - strtotime($last_sale_map[$cid])) / 86400);
                }

                // توزيع الرصيد على الفئات العمرية بناءً على آخر معاملة
                $current_0_30 = 0;
                $overdue_31_60 = 0;
                $overdue_61_90 = 0;
                $critical_over_90 = 0;
                $bad_debt = 0;

                if ($days <= 30) {
                    $current_0_30 = $bal;
                } elseif ($days <= 60) {
                    $overdue_31_60 = $bal;
                } elseif ($days <= 90) {
                    $overdue_61_90 = $bal;
                } else {
                    $critical_over_90 = $bal;
                    $bad_debt = $bal * 0.50; // مخصص ديون مشكوك فيها 50%
                }

                $aging_report[] = [
                    'customer_name'    => $cname,
                    'current_0_30'     => $current_0_30,
                    'overdue_31_60'    => $overdue_31_60,
                    'overdue_61_90'    => $overdue_61_90,
                    'critical_over_90' => $critical_over_90,
                    'bad_debt_provision'=> $bad_debt,
                    'total_outstanding'=> $bal,
                    'days_since_last'  => $days
                ];
            }
        }

        // ترتيب حسب الديون الحرجة تنازلياً
        usort($aging_report, function($a, $b) {
            return $b['critical_over_90'] <=> $a['critical_over_90'];
        });
    }
} catch (Exception $e) {
    // خطأ أو الجداول غير مهيأة
}

// البديل في حال خلو قاعدة البيانات
if (empty($aging_report) && !$db_connected) {
    $quick_ratio = 2.4;
    $safety_rate = 85;
    $dso_days = 28.5;
    $aging_report = [
        [
            'customer_name' => 'شركة النيل الأزرق للمقاولات',
            'current_0_30' => $rate * 10.0,
            'overdue_31_60' => $rate * 4.5,
            'overdue_61_90' => 0.0,
            'critical_over_90' => 0.0,
            'bad_debt_provision' => 0.0,
            'total_outstanding' => $rate * 14.5
        ],
        [
            'customer_name' => 'مقاولات السجانة الموحدة',
            'current_0_30' => 0.0,
            'overdue_31_60' => 0.0,
            'overdue_61_90' => $rate * 3.0,
            'critical_over_90' => $rate * 8.0,
            'bad_debt_provision' => $rate * 4.0,
            'total_outstanding' => $rate * 11.0
        ],
        [
            'customer_name' => 'مجموعة البرج الهندسية',
            'current_0_30' => $rate * 8.2,
            'overdue_31_60' => 0.0,
            'overdue_61_90' => 0.0,
            'critical_over_90' => 0.0,
            'bad_debt_provision' => 0.0,
            'total_outstanding' => $rate * 8.2
        ]
    ];
}

// في حال DSO لا يزال صفر بعد الحسابات الحقيقية
if ($dso_days == 0 && $db_connected) {
    $dso_days = 0; // يعني لا توجد مبيعات آجلة - وهذا ممتاز
}

// حساب المبالغ الإجمالية والديون الحرجة لحساب مستوى الأمان العام للمنظومة
$total_debt_all = 0;
$total_critical_90 = 0;
$bad_debt_provision_total = 0;
foreach ($aging_report as $age) {
    $total_debt_all += $age['total_outstanding'];
    $total_critical_90 += $age['critical_over_90'];
    $bad_debt_provision_total += $age['bad_debt_provision'];
}

// === تحديد حالة الأمان العام ===
$safety_status = 'GREEN';
if ($dso_days > 45 || ($total_debt_all > 0 && ($total_critical_90 / $total_debt_all) >= 0.25)) {
    $safety_status = 'RED';
} elseif ($dso_days > 30 || ($total_debt_all > 0 && ($total_critical_90 / $total_debt_all) >= 0.10)) {
    $safety_status = 'YELLOW';
}

// === تحديد ألوان وتصنيفات ديناميكية ===
// Quick Ratio: أخضر > 1.5، أصفر 1.0-1.5، أحمر < 1.0
$qr_color = '#00ff7f';
$qr_label = '▲ فوق المعيار العالمي (1.0)';
if ($quick_ratio < 1.0) {
    $qr_color = '#ff0055';
    $qr_label = '▼ أقل من المعيار العالمي (1.0) - خطر';
} elseif ($quick_ratio < 1.5) {
    $qr_color = '#ffa500';
    $qr_label = '● ضمن المعيار المقبول (1.0-1.5)';
}
$qr_percent = ($quick_ratio > 1.0) ? round(($quick_ratio - 1.0) * 100, 0) : 0;

// Safety Rate: أخضر > 70%، أصفر 50-70%، أحمر < 50%
$sr_color = '#00ffff';
$sr_label = '● تصنيف ائتماني قوي جداً (AAA)';
if ($safety_rate < 50) {
    $sr_color = '#ff0055';
    $sr_label = '🚨 تصنيف ضعيف - نسبة ديون عالية';
} elseif ($safety_rate < 70) {
    $sr_color = '#ffa500';
    $sr_label = '⚠️ تصنيف متوسط - يحتاج مراقبة';
} elseif ($safety_rate < 85) {
    $sr_color = '#00ffff';
    $sr_label = '● تصنيف ائتماني جيد (AA)';
}

// DSO: أخضر < 30، أصفر 30-45، أحمر > 45
$dso_color = '#ffcc00';
$dso_label = '★ فترة تحصيل سريعة وممتازة';
if ($dso_days > 45) {
    $dso_color = '#ff0055';
    $dso_label = '🚨 فترة تحصيل بطيئة - تحتاج تسريع';
} elseif ($dso_days > 30) {
    $dso_color = '#ffa500';
    $dso_label = '⚠️ فترة تحصيل متوسطة';
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
    <link rel="stylesheet" href="css/components.css?v=20260626_4">

    <meta charset="UTF-8">
    <title>تقرير الصحة المالية | Al-Sajana ERP</title>
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

        :root {
            --bg-dark: #05050f;
            --panel-bg: rgba(14, 12, 28, 0.7);
            --border-neon: rgba(255, 204, 0, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        * { box-sizing: border-box; }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            padding: 30px;
            background-image: radial-gradient(circle at bottom left, rgba(255, 204, 0, 0.08), transparent 40%);
            opacity: 0;
            animation: pageLoad 0.8s ease-out forwards;
        }

        /* === أنيميشن تحميل الصفحة === */
        @keyframes pageLoad {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === أنيميشن ظهور من الأسفل === */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === أنيميشن ظهور تدريجي === */
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* === أنيميشن نبض متوهج للشارة === */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 8px rgba(var(--glow-rgb), 0.3); }
            50%      { box-shadow: 0 0 25px rgba(var(--glow-rgb), 0.6), 0 0 50px rgba(var(--glow-rgb), 0.2); }
        }

        /* === أنيميشن عداد الأرقام (يتم تطبيقه بالـ JS) === */
        @keyframes counterPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .glass-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.6s ease-out forwards;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn-back {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: rgba(255, 204, 0, 0.1);
            border-color: rgba(255, 204, 0, 0.3);
            color: #ffcc00;
            transform: translateX(-3px);
        }

        /* === فلتر التاريخ === */
        .date-filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            padding: 18px 22px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 204, 0, 0.1);
            border-radius: 16px;
            animation: slideUp 0.7s ease-out forwards;
            opacity: 0;
            animation-delay: 0.1s;
            animation-fill-mode: forwards;
        }
        .date-filter-bar label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: bold;
        }
        .date-filter-bar input[type="date"] {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 204, 0, 0.15);
            color: #fff;
            padding: 8px 14px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            outline: none;
            transition: all 0.3s ease;
        }
        .date-filter-bar input[type="date"]:focus {
            border-color: rgba(255, 204, 0, 0.5);
            box-shadow: 0 0 12px rgba(255, 204, 0, 0.15);
        }
        .filter-btn {
            background: linear-gradient(135deg, rgba(255, 204, 0, 0.2), rgba(255, 0, 85, 0.15));
            border: 1px solid rgba(255, 204, 0, 0.25);
            color: #ffcc00;
            padding: 8px 20px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-btn:hover {
            background: linear-gradient(135deg, rgba(255, 204, 0, 0.35), rgba(255, 0, 85, 0.25));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 204, 0, 0.15);
        }
        .filter-reset {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-reset:hover {
            border-color: rgba(255, 0, 85, 0.3);
            color: #ff0055;
        }

        /* === بطاقات المقاييس === */
        .gauge-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .gauge-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,204,0,0.1);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            opacity: 0;
            animation: slideUp 0.6s ease-out forwards;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        .gauge-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.03), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .gauge-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 204, 0, 0.3);
        }
        .gauge-card:hover::before {
            opacity: 1;
        }
        .gauge-card:nth-child(1) { animation-delay: 0.2s; }
        .gauge-card:nth-child(2) { animation-delay: 0.4s; }
        .gauge-card:nth-child(3) { animation-delay: 0.6s; }

        /* === شارة الأمان المتوهجة === */
        .safety-badge {
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 13.5px;
            display: inline-block;
            animation: pulseGlow 2.5s ease-in-out infinite;
        }
        .safety-badge.green {
            background: rgba(0, 255, 127, 0.12);
            color: #00ff7f;
            border: 1px solid rgba(0, 255, 127, 0.2);
            --glow-rgb: 0, 255, 127;
        }
        .safety-badge.yellow {
            background: rgba(255, 165, 0, 0.12);
            color: #ffa500;
            border: 1px solid rgba(255, 165, 0, 0.2);
            --glow-rgb: 255, 165, 0;
        }
        .safety-badge.red {
            background: rgba(255, 0, 85, 0.12);
            color: #ff0055;
            border: 1px solid rgba(255, 0, 85, 0.2);
            --glow-rgb: 255, 0, 85;
        }

        /* === شريط الأمان === */
        .safety-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 204, 0, 0.1);
            border-radius: 20px;
            box-shadow: inset 0 0 15px rgba(255,204,0,0.02);
            opacity: 0;
            animation: slideUp 0.6s ease-out 0.15s forwards;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* === الرقم الكبير المتحرك === */
        .counter-value {
            margin: 15px 0 5px 0;
            font-size: 36px;
            font-weight: 900;
            animation: counterPulse 0.6s ease-out;
        }

        /* === جدول أعمار الديون === */
        .aging-section {
            background: rgba(0,0,0,0.25);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.03);
            margin-top: 30px;
            opacity: 0;
            animation: slideUp 0.8s ease-out 0.8s forwards;
        }
        .aging-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .aging-table th {
            text-align: right;
            padding: 12px;
            color: var(--text-muted);
            border-bottom: 2px solid rgba(255, 204, 0, 0.2);
        }
        .aging-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.3s ease;
        }
        .aging-table tbody tr {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        .aging-table tbody tr:hover td {
            background: rgba(255, 204, 0, 0.04);
        }

        /* === ملخص أسفل الجدول === */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 204, 0, 0.15);
        }
        .summary-item {
            text-align: center;
            padding: 12px;
            background: rgba(255,255,255,0.015);
            border-radius: 12px;
            opacity: 0;
            animation: slideUp 0.5s ease-out forwards;
        }

        /* === مؤشر مصدر البيانات === */
        .data-source {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 8px;
            margin-top: 8px;
        }
        .data-source.live {
            background: rgba(0, 255, 127, 0.08);
            color: #00ff7f;
            border: 1px solid rgba(0, 255, 127, 0.15);
        }
        .data-source.mock {
            background: rgba(255, 165, 0, 0.08);
            color: #ffa500;
            border: 1px solid rgba(255, 165, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="glass-panel">
        <div class="header">
            
            <a href="smart_reports_hub.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <!-- === فلتر التاريخ (من - إلى) === -->
        <form method="GET" class="date-filter-bar">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <label>📅 من:</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <label>إلى:</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            <button type="submit" class="filter-btn">🔍 تطبيق الفلتر</button>
            <a href="financial_health.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" style="text-decoration:none;">
                <button type="button" class="filter-reset">↻ إعادة تعيين</button>
            </a>
            <?php if ($date_from || $date_to): ?>
                <span style="color: #ffcc00; font-size: 12px; margin-right: 10px;">
                    🔎 الفلتر نشط: <?php echo $date_from ? htmlspecialchars($date_from) : 'البداية'; ?> → <?php echo $date_to ? htmlspecialchars($date_to) : 'الآن'; ?>
                </span>
            <?php endif; ?>
        </form>

        <!-- مؤشر الأمان العام (Safety Gauge) -->
        <div class="safety-bar">
            <div>
                <h3 style="margin: 0; font-size: 16px; color: #ffffff; font-weight: bold;">مستوى الأمان المالي الحالي للمنظومة</h3>
                <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 12.5px;">يتم احتسابه تلقائياً بناءً على سرعة تدفق السيولة وأعمار الديون</p>
            </div>
            <div>
                <?php if ($safety_status === 'GREEN'): ?>
                    <span class="safety-badge green">● آمن ومستقر جداً</span>
                <?php elseif ($safety_status === 'YELLOW'): ?>
                    <span class="safety-badge yellow">⚠️ إنذار مبكر: تباطؤ في التحصيل</span>
                <?php else: ?>
                    <span class="safety-badge red">🚨 خطر: السيولة مهددة</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- === بطاقات المقاييس الثلاثة (محسوبة ديناميكياً) === -->
        <div class="gauge-section">
            <!-- بطاقة نسبة التداول السريعة Quick Ratio -->
            <div class="gauge-card">
                <span style="color: var(--text-muted); font-size: 14px;">نسبة التداول السريعة (Quick Ratio)</span>
                <h1 class="counter-value" style="color: <?php echo $qr_color; ?>;" data-target="<?php echo $quick_ratio; ?>" data-decimals="2">0</h1>
                <span style="font-size:12px; color:<?php echo $qr_color; ?>;"><?php echo $qr_label; ?><?php if ($qr_percent > 0) echo " بـ {$qr_percent}%"; ?></span>
                <p style="color:var(--text-muted); font-size:12px; margin-top:10px;">يعني أنك قادر على سداد ديونك قصيرة الأجل فوراً دون الحاجة لبيع المخزون بشكل مستعجل.</p>
                <?php if ($db_connected): ?>
                <p style="color:var(--text-muted); font-size:10.5px; margin-top:8px; opacity:0.6;">
                    نقدية: <?php echo number_format($total_cash, 0); ?> | ذمم مدينة: <?php echo number_format($total_receivables, 0); ?> | التزامات: <?php echo number_format($total_payables, 0); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- بطاقة معدل الأمان والاعتماد الذاتي -->
            <div class="gauge-card" style="border-color: rgba(0, 255, 255, 0.15);">
                <span style="color: var(--text-muted); font-size: 14px;">معدل الأمان والاعتماد الذاتي</span>
                <h1 class="counter-value" style="color: <?php echo $sr_color; ?>;" data-target="<?php echo $safety_rate; ?>" data-decimals="1" data-suffix="%">0</h1>
                <span style="font-size:12px; color:<?php echo $sr_color; ?>;"><?php echo $sr_label; ?></span>
                <p style="color:var(--text-muted); font-size:12px; margin-top:10px;">يعتمد المحل بالكامل على رأس ماله وأرباحه المحتجزة، ونسبة الديون والمخاطر شبه منعدمة.</p>
                <?php if ($db_connected): ?>
                <p style="color:var(--text-muted); font-size:10.5px; margin-top:8px; opacity:0.6;">
                    إجمالي الأصول: <?php echo number_format($total_cash + $total_receivables, 0); ?> | إجمالي الالتزامات: <?php echo number_format($total_payables, 0); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- بطاقة فترة تحصيل الديون DSO -->
            <div class="gauge-card" style="border-color: rgba(255, 204, 0, 0.15);">
                <span style="color: var(--text-muted); font-size: 14px;">معدل فترة تحصيل الديون باليوم (DSO)</span>
                <h1 class="counter-value" style="color: <?php echo $dso_color; ?>;" data-target="<?php echo $dso_days; ?>" data-decimals="1" data-suffix=" يوم">0</h1>
                <span style="font-size:12px; color:<?php echo $dso_color; ?>;"><?php echo $dso_label; ?></span>
                <p style="color:var(--text-muted); font-size:12px; margin-top:10px;">يقيس متوسط الوقت المستغرق لتحويل المبيعات الآجلة إلى كاش سائل. معدل أقل من 30 يوماً يعكس كفاءة تشغيلية عالية.</p>
            </div>
        </div>

        <!-- كشف أعمار الديون ومخصصات التعثر -->
        <div class="aging-section">
            <h3 style="margin-top:0; color:#ffcc00; margin-bottom:15px;">📊 تحليل أعمار الديون ومخصص الديون المشكوك فيها (IFRS 9 Aging Matrix)</h3>

            <?php if (empty($aging_report)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <p style="font-size: 40px; margin: 0;">✅</p>
                    <p style="font-size: 16px; margin: 10px 0 0 0;">لا توجد ديون مستحقة على العملاء - وضع مالي ممتاز!</p>
                </div>
            <?php else: ?>
            <table class="aging-table">
                <thead>
                    <tr>
                        <th>اسم العميل</th>
                        <th>حالي (0-30 يوم)</th>
                        <th>متأخر (31-60 يوم)</th>
                        <th>متأخر (61-90 يوم)</th>
                        <th>حرج (>90 يوم)</th>
                        <th>مخصص ديون مشكوك فيها (50%)</th>
                        <th>إجمالي الدين المستحق</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $row_delay = 0; foreach ($aging_report as $age): $row_delay++; ?>
                        <tr style="animation-delay: <?php echo 0.8 + ($row_delay * 0.1); ?>s;">
                            <td><strong><?php echo htmlspecialchars($age['customer_name']); ?></strong></td>
                            <td style="color:#00ff7f;"><?php echo number_format($age['current_0_30'], 2); ?></td>
                            <td style="color:#ffa500;"><?php echo number_format($age['overdue_31_60'], 2); ?></td>
                            <td style="color:#ff5500;"><?php echo number_format($age['overdue_61_90'], 2); ?></td>
                            <td style="color:#ff0055; font-weight:bold;"><?php echo number_format($age['critical_over_90'], 2); ?></td>
                            <td style="color:#ffcc00; font-weight:bold;"><?php echo number_format($age['bad_debt_provision'], 2); ?></td>
                            <td style="color:#00ffff; font-weight:bold;"><?php echo number_format($age['total_outstanding'], 2); ?> <span style="font-size:11px; color:var(--text-muted);"><?php echo htmlspecialchars($selected_currency); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ملخص إجمالي أسفل الجدول -->
            <div class="summary-row">
                <div class="summary-item" style="animation-delay: 1.2s;">
                    <span style="color: var(--text-muted); font-size: 12px;">إجمالي الديون المستحقة</span>
                    <h3 style="color: #00ffff; margin: 5px 0 0 0;" class="counter-value" data-target="<?php echo $total_debt_all; ?>" data-decimals="0"><?php echo number_format($total_debt_all, 0); ?></h3>
                </div>
                <div class="summary-item" style="animation-delay: 1.4s;">
                    <span style="color: var(--text-muted); font-size: 12px;">الديون الحرجة (> 90 يوم)</span>
                    <h3 style="color: #ff0055; margin: 5px 0 0 0;" class="counter-value" data-target="<?php echo $total_critical_90; ?>" data-decimals="0"><?php echo number_format($total_critical_90, 0); ?></h3>
                </div>
                <div class="summary-item" style="animation-delay: 1.6s;">
                    <span style="color: var(--text-muted); font-size: 12px;">مخصص ديون مشكوك فيها</span>
                    <h3 style="color: #ffcc00; margin: 5px 0 0 0;" class="counter-value" data-target="<?php echo $bad_debt_provision_total; ?>" data-decimals="0"><?php echo number_format($bad_debt_provision_total, 0); ?></h3>
                </div>
                <div class="summary-item" style="animation-delay: 1.8s;">
                    <span style="color: var(--text-muted); font-size: 12px;">نسبة الديون الحرجة</span>
                    <h3 style="color: <?php echo ($total_debt_all > 0 && ($total_critical_90 / $total_debt_all) >= 0.10) ? '#ff0055' : '#00ff7f'; ?>; margin: 5px 0 0 0;">
                        <?php echo $total_debt_all > 0 ? number_format(($total_critical_90 / $total_debt_all) * 100, 1) : '0.0'; ?>%
                    </h3>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- === سكريبت أنيميشن عداد الأرقام (Counter Animation) === -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // أنيميشن عداد الأرقام - تعد من 0 للقيمة الحقيقية
        const counters = document.querySelectorAll('.counter-value[data-target]');
        const observerOptions = { threshold: 0.3 };

        const counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseFloat(el.dataset.target);
                    const decimals = parseInt(el.dataset.decimals || '0');
                    const suffix = el.dataset.suffix || '';
                    const duration = 1800; // مدة العد بالمللي ثانية
                    const startTime = performance.now();

                    function animate(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        // منحنى تباطؤ ناعم (easeOutExpo)
                        const easeOut = 1 - Math.pow(1 - progress, 4);
                        const current = target * easeOut;

                        el.textContent = current.toFixed(decimals) + suffix;

                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            el.textContent = target.toFixed(decimals) + suffix;
                            // نبضة خفيفة عند الانتهاء
                            el.style.animation = 'counterPulse 0.4s ease-out';
                        }
                    }
                    requestAnimationFrame(animate);
                    counterObserver.unobserve(el);
                }
            });
        }, observerOptions);

        counters.forEach(counter => counterObserver.observe(counter));
    });
    </script>
    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
