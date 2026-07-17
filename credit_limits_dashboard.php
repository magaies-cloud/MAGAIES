<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مراقب سقوف المديونية والائتمان
// تم إصلاحه ليعمل مع البيانات الحقيقية من جداول customers و sales_invoices
// ====================================================================
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// فلترة البحث والترتيب
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'usage_desc';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// محاولة جلب حدود الائتمان وسقوف الديون للعملاء من قاعدة البيانات الحقيقية
$customers = [];
$customer_engagement = [];
$db_connected = false;

try {
    @include_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        $db_connected = true;

        // ====================================================================
        // 1. جلب بيانات العملاء الحقيقية من جدول customers (JSON data column)
        // ====================================================================
        $cust_rows = $pdo->query("SELECT id, data FROM customers WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchAll(PDO::FETCH_ASSOC);

        // جلب آخر تاريخ بيع لكل عميل لحساب أيام التأخير
        $last_sales = [];
        try {
            $sale_stmt = $pdo->query("SELECT customer_id_val, MAX(date_val) as last_date FROM sales_invoices WHERE is_deleted = 0 GROUP BY customer_id_val");
            $sale_rows = $sale_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sale_rows as $sr) {
                $last_sales[$sr['customer_id_val']] = $sr['last_date'];
            }
        } catch (Exception $e) {
            // جدول sales_invoices قد لا يحتوي على الأعمدة المطلوبة
        }

        // بناء مصفوفة العملاء مع حدود الائتمان من البيانات الحقيقية
        foreach ($cust_rows as $row) {
            $d = json_decode($row['data'], true);
            if (!$d) continue;

            $balance = floatval($d['currentBalance'] ?? 0);
            $limit = floatval($d['creditLimit'] ?? 0);
            $name = $d['name'] ?? 'عميل';
            $phone = $d['phone'] ?? '';
            $cust_id = $d['id'] ?? $row['id'];

            // حساب أيام التأخير من آخر فاتورة مبيعات
            $days_overdue = 0;
            if (isset($last_sales[$cust_id]) && !empty($last_sales[$cust_id]) && $last_sales[$cust_id] !== '0000-00-00') {
                try {
                    $last_date = new DateTime($last_sales[$cust_id]);
                    $today = new DateTime();
                    $days_overdue = max(0, $today->diff($last_date)->days);
                } catch (Exception $e) {
                    $days_overdue = 0;
                }
            }
            // أيضاً نحاول مطابقة بـ row id
            if ($days_overdue === 0 && isset($last_sales[$row['id']]) && !empty($last_sales[$row['id']]) && $last_sales[$row['id']] !== '0000-00-00') {
                try {
                    $last_date = new DateTime($last_sales[$row['id']]);
                    $today = new DateTime();
                    $days_overdue = max(0, $today->diff($last_date)->days);
                } catch (Exception $e) {
                    $days_overdue = 0;
                }
            }

            // نعرض فقط العملاء الذين لديهم سقف ائتماني > 0 أو رصيد مدين > 0
            if ($limit > 0 || $balance > 0) {
                $customers[] = [
                    'name' => $name,
                    'debt' => abs($balance),
                    'limit' => $limit > 0 ? $limit : abs($balance) * 1.5,
                    'days_overdue' => $days_overdue,
                    'phone' => $phone,
                    'has_real_limit' => $limit > 0,
                    'cust_id' => $cust_id
                ];
            }
        }

        // ====================================================================
        // 2. مصفوفة تفاعل العملاء من فواتير المبيعات الحقيقية
        // ====================================================================
        try {
            // جلب عدد الفواتير وإجمالي المبيعات لكل عميل
            $eng_rows = $pdo->query("SELECT customer_id_val, COUNT(*) as visit_count, data, total_val FROM sales_invoices WHERE is_deleted = 0 GROUP BY customer_id_val")->fetchAll(PDO::FETCH_ASSOC);

            // بناء خريطة أسماء العملاء
            $customerNames = [];
            foreach ($cust_rows as $cr) {
                $d = json_decode($cr['data'], true);
                if ($d) {
                    $customerNames[$cr['id']] = $d['name'] ?? 'عميل';
                    if (isset($d['id'])) {
                        $customerNames[$d['id']] = $d['name'] ?? 'عميل';
                    }
                }
            }

            // حساب المجاميع من JSON data لأن total_val قد يكون صفراً
            $allSales = $pdo->query("SELECT customer_id_val, data, total_val FROM sales_invoices WHERE is_deleted = 0")->fetchAll(PDO::FETCH_ASSOC);
            $custAgg = [];
            foreach ($allSales as $s) {
                $cid = $s['customer_id_val'] ?? 'unknown';
                if (!isset($custAgg[$cid])) {
                    $custAgg[$cid] = ['count' => 0, 'total' => 0];
                }
                $custAgg[$cid]['count']++;
                // محاولة جلب المجموع من total_val أو من JSON
                $total = floatval($s['total_val'] ?? 0);
                if ($total <= 0 && !empty($s['data'])) {
                    $sd = json_decode($s['data'], true);
                    $total = floatval($sd['totalAmount'] ?? $sd['total'] ?? 0);
                }
                $custAgg[$cid]['total'] += $total;

                // جلب اسم العميل من JSON الفاتورة
                if (!isset($customerNames[$cid]) && !empty($s['data'])) {
                    $sd = json_decode($s['data'], true);
                    $customerNames[$cid] = $sd['customerName'] ?? 'عميل ' . $cid;
                }
            }

            // فلترة التاريخ للفواتير إذا تم تحديد نطاق
            if (!empty($date_from) || !empty($date_to)) {
                $date_filter_sql = "SELECT customer_id_val, data, total_val, date_val FROM sales_invoices WHERE is_deleted = 0";
                $conditions = [];
                if (!empty($date_from)) $conditions[] = "date_val >= " . $pdo->quote($date_from);
                if (!empty($date_to)) $conditions[] = "date_val <= " . $pdo->quote($date_to);
                if (!empty($conditions)) $date_filter_sql .= " AND " . implode(" AND ", $conditions);

                $filteredSales = $pdo->query($date_filter_sql)->fetchAll(PDO::FETCH_ASSOC);
                $custAgg = []; // إعادة تعيين
                foreach ($filteredSales as $s) {
                    $cid = $s['customer_id_val'] ?? 'unknown';
                    if (!isset($custAgg[$cid])) {
                        $custAgg[$cid] = ['count' => 0, 'total' => 0];
                    }
                    $custAgg[$cid]['count']++;
                    $total = floatval($s['total_val'] ?? 0);
                    if ($total <= 0 && !empty($s['data'])) {
                        $sd = json_decode($s['data'], true);
                        $total = floatval($sd['totalAmount'] ?? $sd['total'] ?? 0);
                    }
                    $custAgg[$cid]['total'] += $total;
                }
            }

            foreach ($custAgg as $cid => $agg) {
                $visits = $agg['count'];
                $spend = $agg['total'];

                if ($visits >= 15) {
                    $tier = 'VIP (نشط جداً)';
                    $badge_class = 'badge-success';
                } elseif ($visits >= 5) {
                    $tier = 'REGULAR (نشط)';
                    $badge_class = 'badge-warning';
                } else {
                    $tier = 'OCCASIONAL (موسمي)';
                    $badge_class = 'badge-info';
                }

                $customer_engagement[] = [
                    'name' => $customerNames[$cid] ?? 'عميل ' . $cid,
                    'visits' => $visits,
                    'spend' => round($spend, 2),
                    'tier' => $tier,
                    'badge' => $badge_class
                ];
            }

            // ترتيب حسب عدد الزيارات تنازلياً
            usort($customer_engagement, function($a, $b) { return $b['visits'] - $a['visits']; });

        } catch (Exception $e) {
            // فشل في جلب بيانات التفاعل
        }
    }
} catch (Exception $e) {
    // خطأ في الاتصال أو الجداول غير مهيأة
}

// ====================================================================
// بناء رسالة الإنذار الديناميكية من البيانات الفعلية
// ====================================================================
$alert_messages = [];
$exceeded_customers = [];
$near_limit_customers = [];
$total_at_risk = 0;

foreach ($customers as $cust) {
    $usage = $cust['limit'] > 0 ? ($cust['debt'] / $cust['limit']) * 100 : 0;
    if ($usage >= 100) {
        $exceeded_customers[] = [
            'name' => $cust['name'],
            'usage' => round($usage, 1),
            'days' => $cust['days_overdue']
        ];
        $total_at_risk += $cust['debt'];
    } elseif ($usage >= 80) {
        $near_limit_customers[] = [
            'name' => $cust['name'],
            'usage' => round($usage, 1)
        ];
    }
}

// بناء نص الإنذار الديناميكي
$alert_text = '';
$alert_level = 'safe'; // safe, warning, critical
if (!empty($exceeded_customers)) {
    $alert_level = 'critical';
    $alert_text .= 'تنبيه حرج! ';
    foreach ($exceeded_customers as $ec) {
        $alert_text .= 'لقد تجاوز <strong>"' . htmlspecialchars($ec['name']) . '"</strong> السقف المسموح بنسبة <strong>' . $ec['usage'] . '%</strong>';
        if ($ec['days'] > 0) {
            $alert_text .= ' مع تأخر ' . $ec['days'] . ' يوماً';
        }
        $alert_text .= '. ';
    }
}
if (!empty($near_limit_customers)) {
    if ($alert_level === 'safe') $alert_level = 'warning';
    if (!empty($exceeded_customers)) $alert_text .= 'كما ';
    foreach ($near_limit_customers as $nc) {
        $alert_text .= 'يقترب <strong>"' . htmlspecialchars($nc['name']) . '"</strong> من السقف الحرج (مستهلك ' . $nc['usage'] . '%). ';
    }
}
if (empty($alert_text)) {
    $alert_level = 'safe';
    $alert_text = 'جميع العملاء ضمن حدود الائتمان المسموحة. لا توجد مخاطر ائتمانية حالياً. ✅';
}

// ====================================================================
// تطبيق فلتر البحث على العملاء
// ====================================================================
$filtered_customers = $customers;
if (!empty($search_query)) {
    $filtered_customers = array_filter($customers, function($c) use ($search_query) {
        return mb_stripos($c['name'], $search_query) !== false;
    });
    $filtered_customers = array_values($filtered_customers);
}

// ====================================================================
// تطبيق الترتيب
// ====================================================================
usort($filtered_customers, function($a, $b) use ($sort_by) {
    switch ($sort_by) {
        case 'usage_desc':
            $ua = $a['limit'] > 0 ? ($a['debt'] / $a['limit']) : 0;
            $ub = $b['limit'] > 0 ? ($b['debt'] / $b['limit']) : 0;
            return $ub <=> $ua;
        case 'usage_asc':
            $ua = $a['limit'] > 0 ? ($a['debt'] / $a['limit']) : 0;
            $ub = $b['limit'] > 0 ? ($b['debt'] / $b['limit']) : 0;
            return $ua <=> $ub;
        case 'debt_desc':
            return $b['debt'] <=> $a['debt'];
        case 'debt_asc':
            return $a['debt'] <=> $b['debt'];
        case 'name_asc':
            return strcmp($a['name'], $b['name']);
        case 'name_desc':
            return strcmp($b['name'], $a['name']);
        case 'overdue_desc':
            return $b['days_overdue'] <=> $a['days_overdue'];
        default:
            $ua = $a['limit'] > 0 ? ($a['debt'] / $a['limit']) : 0;
            $ub = $b['limit'] > 0 ? ($b['debt'] / $b['limit']) : 0;
            return $ub <=> $ua;
    }
});

// ====================================================================
// إحصائيات سريعة
// ====================================================================
$total_customers = count($customers);
$total_exceeded = count($exceeded_customers);
$total_near = count($near_limit_customers);
$total_safe = $total_customers - $total_exceeded - $total_near;
$total_debt_sum = array_sum(array_column($customers, 'debt'));
$total_limit_sum = array_sum(array_column($customers, 'limit'));
$overall_usage = $total_limit_sum > 0 ? round(($total_debt_sum / $total_limit_sum) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراقب الائتمان وسقوف الديون | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #07030f;
            --panel-bg: rgba(18, 12, 32, 0.7);
            --border-neon: rgba(255, 0, 85, 0.15);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Cairo', sans-serif; padding: 30px; background-image: radial-gradient(circle at bottom left, rgba(255, 0, 85, 0.08), transparent 40%); min-height: 100vh; }

        /* أنيميشن fadeIn */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        /* أنيميشن slideUp */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* أنيميشن slideDown */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* أنيميشن pulse للتنبيه */
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 5px rgba(255, 0, 85, 0.3); }
            50% { box-shadow: 0 0 25px rgba(255, 0, 85, 0.6), 0 0 50px rgba(255, 0, 85, 0.2); }
        }
        @keyframes pulseSafe {
            0%, 100% { box-shadow: 0 0 5px rgba(0, 255, 127, 0.3); }
            50% { box-shadow: 0 0 25px rgba(0, 255, 127, 0.5), 0 0 50px rgba(0, 255, 127, 0.15); }
        }
        /* أنيميشن shimmer للشريط */
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        /* أنيميشن scaleIn */
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        /* أنيميشن countUp (يُنفذ بـ JS) */
        @keyframes glow {
            0%, 100% { text-shadow: 0 0 5px currentColor; }
            50% { text-shadow: 0 0 20px currentColor, 0 0 40px currentColor; }
        }

        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); animation: fadeIn 0.6s ease-out; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; animation: slideDown 0.5s ease-out; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; transition: all 0.3s ease; }
        .btn-back:hover { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 0, 85, 0.4); color: #fff; transform: translateX(-3px); }

        /* صندوق الإنذار مع نبض */
        .ai-risk-alert {
            border-radius: 16px; padding: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;
            animation: slideUp 0.6s ease-out 0.2s both;
            transition: all 0.3s ease;
        }
        .ai-risk-alert.critical {
            background: rgba(255, 0, 85, 0.05); border: 1.5px solid #ff0055;
            animation: slideUp 0.6s ease-out 0.2s both, pulse 3s ease-in-out infinite 1s;
        }
        .ai-risk-alert.warning {
            background: rgba(255, 165, 0, 0.05); border: 1.5px solid #ffa500;
            animation: slideUp 0.6s ease-out 0.2s both, pulse 3s ease-in-out infinite 1s;
        }
        .ai-risk-alert.safe {
            background: rgba(0, 255, 127, 0.05); border: 1.5px solid #00ff7f;
            animation: slideUp 0.6s ease-out 0.2s both, pulseSafe 4s ease-in-out infinite 1s;
        }

        /* كروت الإحصائيات السريعة */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px; text-align: center;
            animation: scaleIn 0.5s ease-out both;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .stat-card:nth-child(1) { animation-delay: 0.3s; }
        .stat-card:nth-child(2) { animation-delay: 0.4s; }
        .stat-card:nth-child(3) { animation-delay: 0.5s; }
        .stat-card:nth-child(4) { animation-delay: 0.6s; }
        .stat-card:nth-child(5) { animation-delay: 0.7s; }
        .stat-value { font-size: 28px; font-weight: 900; margin: 5px 0; }
        .stat-label { font-size: 12px; color: var(--text-muted); }

        /* شريط البحث والفلترة */
        .filter-bar {
            display: flex; gap: 12px; margin-bottom: 25px; flex-wrap: wrap; align-items: center;
            animation: slideUp 0.5s ease-out 0.4s both;
        }
        .filter-bar input, .filter-bar select {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); color: #fff;
            padding: 10px 16px; border-radius: 12px; font-family: 'Cairo', sans-serif; font-size: 13px;
            transition: all 0.3s ease; outline: none;
        }
        .filter-bar input:focus, .filter-bar select:focus {
            border-color: rgba(255, 0, 85, 0.5); box-shadow: 0 0 15px rgba(255, 0, 85, 0.15);
        }
        .filter-bar input::placeholder { color: rgba(255,255,255,0.3); }
        .filter-bar input[type="text"] { flex: 1; min-width: 200px; }
        .filter-bar input[type="date"] { min-width: 150px; }
        .filter-bar select { min-width: 160px; cursor: pointer; }
        .filter-bar select option { background: #1a1030; color: #fff; }
        .btn-filter {
            background: linear-gradient(135deg, #ff0055, #ff6600); color: #fff; border: none;
            padding: 10px 20px; border-radius: 12px; cursor: pointer; font-family: 'Cairo'; font-size: 13px; font-weight: 700;
            transition: all 0.3s ease;
        }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(255, 0, 85, 0.3); }
        .btn-reset {
            background: transparent; color: var(--text-muted); border: 1px solid rgba(255,255,255,0.1);
            padding: 10px 16px; border-radius: 12px; cursor: pointer; font-family: 'Cairo'; font-size: 13px;
            transition: all 0.3s ease; text-decoration: none;
        }
        .btn-reset:hover { border-color: rgba(255,255,255,0.3); color: #fff; }

        /* بطاقات العملاء */
        .customer-card {
            background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 20px; margin-bottom: 20px;
            opacity: 0; animation: slideUp 0.5s ease-out both;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }
        .customer-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); background: rgba(255,255,255,0.035); }

        .progress-bar-bg { background: rgba(255,255,255,0.05); border-radius: 50px; height: 12px; overflow: hidden; margin: 15px 0 10px 0; border: 1px solid rgba(255,255,255,0.05); }
        .progress-bar-fill {
            height: 100%; border-radius: 50px;
            width: 0; /* يبدأ من صفر ثم يتحرك بالأنيميشن */
            transition: width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }
        .progress-bar-fill.animated::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        .flex-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
        .badge-danger { background: rgba(255, 0, 85, 0.15); color: #ff0055; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 11px; }
        .badge-warning { background: rgba(255, 165, 0, 0.15); color: #ffa500; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 11px; }
        .badge-success { background: rgba(0, 255, 127, 0.15); color: #00ff7f; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 11px; }
        .badge-info { background: rgba(0, 255, 255, 0.15); color: #00ffff; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 11px; }

        /* مؤشر المصدر */
        .data-source-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: bold;
        }
        .data-source-badge.live { background: rgba(0, 255, 127, 0.1); color: #00ff7f; }
        .data-source-badge.live::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #00ff7f; animation: pulse 1.5s infinite; }

        /* الجدول */
        .engagement-table { width: 100%; border-collapse: collapse; }
        .engagement-table thead th {
            text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid rgba(255, 0, 85, 0.2);
        }
        .engagement-table tbody tr {
            opacity: 0; animation: slideUp 0.4s ease-out both;
            transition: background 0.3s ease;
        }
        .engagement-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .engagement-table td {
            padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px;
        }

        /* حالة فارغة */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-muted);
            animation: fadeIn 0.5s ease-out;
        }
        .empty-state .icon { font-size: 50px; margin-bottom: 15px; }

        /* عدد النتائج */
        .results-count { font-size: 13px; color: var(--text-muted); margin-bottom: 15px; animation: fadeIn 0.5s ease-out; }
        .results-count strong { color: #ffa500; }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding: 15px; }
            .glass-panel { padding: 20px; border-radius: 16px; }
            .header { flex-direction: column; text-align: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .filter-bar { flex-direction: column; }
            .filter-bar input, .filter-bar select { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="glass-panel">
        <!-- الرأس -->
        <div class="header">
            <div>
                <h1 style="margin:0; background: linear-gradient(135deg, #ff0055, #ffa500); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight:900;">مراقب الائتمان وسقوف المديونية العامه ⚠️</h1>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">
                    مراقبة سريعة ومتقدمة لسقوف المديونيات لحماية المحل من مخاطر تعثر سداد العملاء
                    <span class="data-source-badge <?php echo $db_connected ? 'live' : ''; ?>" style="margin-right: 10px;">
                        <?php echo $db_connected ? '🟢 بيانات حية' : '⚪ بيانات تجريبية'; ?>
                    </span>
                </p>
            </div>
            <a href="smart_reports_hub.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <!-- إنذار ائتماني ديناميكي مبني على البيانات الفعلية -->
        <div class="ai-risk-alert <?php echo $alert_level; ?>">
            <div style="font-size:30px;">
                <?php
                if ($alert_level === 'critical') echo '🚨';
                elseif ($alert_level === 'warning') echo '⚠️';
                else echo '✅';
                ?>
            </div>
            <div>
                <strong style="color: <?php echo $alert_level === 'critical' ? '#ff0055' : ($alert_level === 'warning' ? '#ffa500' : '#00ff7f'); ?>; font-size:15px; display:block;">
                    كاشف المخاطر والإنذار المبكر الائتماني:
                </strong>
                <span style="font-size:13px; color:#e2e8f0; line-height: 1.6;">
                    <?php echo $alert_text; ?>
                </span>
            </div>
        </div>

        <!-- كروت الإحصائيات السريعة -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">إجمالي العملاء بسقوف ائتمانية</div>
                <div class="stat-value counter" data-target="<?php echo $total_customers; ?>" style="color: #00ffff;">0</div>
                <div class="stat-label">عميل</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">متجاوزو السقف 🚨</div>
                <div class="stat-value counter" data-target="<?php echo $total_exceeded; ?>" style="color: #ff0055;">0</div>
                <div class="stat-label">عميل</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">قريبون من الحد ⚠️</div>
                <div class="stat-value counter" data-target="<?php echo $total_near; ?>" style="color: #ffa500;">0</div>
                <div class="stat-label">عميل (>80%)</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">ائتمان سليم ✅</div>
                <div class="stat-value counter" data-target="<?php echo $total_safe; ?>" style="color: #00ff7f;">0</div>
                <div class="stat-label">عميل</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">نسبة الاستخدام الكلي</div>
                <div class="stat-value counter-decimal" data-target="<?php echo $overall_usage; ?>" style="color: <?php echo $overall_usage >= 80 ? '#ff0055' : ($overall_usage >= 60 ? '#ffa500' : '#00ff7f'); ?>;">0</div>
                <div class="stat-label">%</div>
            </div>
        </div>

        <!-- شريط البحث والفلترة -->
        <form method="GET" class="filter-bar" id="filterForm">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <input type="text" name="search" placeholder="🔍 بحث بالاسم..." value="<?php echo htmlspecialchars($search_query); ?>">
            <select name="sort">
                <option value="usage_desc" <?php echo $sort_by === 'usage_desc' ? 'selected' : ''; ?>>📊 نسبة الاستهلاك (الأعلى)</option>
                <option value="usage_asc" <?php echo $sort_by === 'usage_asc' ? 'selected' : ''; ?>>📊 نسبة الاستهلاك (الأقل)</option>
                <option value="debt_desc" <?php echo $sort_by === 'debt_desc' ? 'selected' : ''; ?>>💰 المديونية (الأعلى)</option>
                <option value="debt_asc" <?php echo $sort_by === 'debt_asc' ? 'selected' : ''; ?>>💰 المديونية (الأقل)</option>
                <option value="name_asc" <?php echo $sort_by === 'name_asc' ? 'selected' : ''; ?>>🔤 الاسم (أ-ي)</option>
                <option value="name_desc" <?php echo $sort_by === 'name_desc' ? 'selected' : ''; ?>>🔤 الاسم (ي-أ)</option>
                <option value="overdue_desc" <?php echo $sort_by === 'overdue_desc' ? 'selected' : ''; ?>>📅 أيام التأخير (الأعلى)</option>
            </select>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" title="من تاريخ">
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" title="إلى تاريخ">
            <button type="submit" class="btn-filter">🔍 فلترة</button>
            <a href="credit_limits_dashboard.php?currency=<?php echo htmlspecialchars($selected_currency); ?>" class="btn-reset">🔄 إعادة تعيين</a>
        </form>

        <h3 style="color: #ffa500; margin-bottom: 10px; animation: fadeIn 0.5s ease-out 0.5s both;">👤 حالة ائتمان عملاء المبيعات الآجلة:</h3>

        <?php if (!empty($search_query) || !empty($date_from) || !empty($date_to)): ?>
            <div class="results-count">
                عدد النتائج: <strong><?php echo count($filtered_customers); ?></strong> من أصل <strong><?php echo $total_customers; ?></strong> عميل
                <?php if (!empty($search_query)): ?>
                    | بحث: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($filtered_customers)): ?>
            <div class="empty-state">
                <div class="icon">🔍</div>
                <h3>لا توجد نتائج</h3>
                <p>لم يتم العثور على عملاء بسقوف ائتمانية تطابق معايير البحث.</p>
                <?php if (!$db_connected): ?>
                    <p style="color: #ff0055; margin-top: 10px;">⚠️ لم يتم الاتصال بقاعدة البيانات</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php $card_index = 0; foreach ($filtered_customers as $cust):
                $usage_percent = $cust['limit'] > 0 ? ($cust['debt'] / $cust['limit']) * 100 : 0;
                if ($usage_percent >= 100) {
                    $color = '#ff0055';
                    $status_badge = '<span class="badge-danger">🚨 متجاوز السقف الحرج</span>';
                } elseif ($usage_percent >= 80) {
                    $color = '#ffa500';
                    $status_badge = '<span class="badge-warning">⚠️ اقتراب من الحد الأقصى</span>';
                } else {
                    $color = '#00ff7f';
                    $status_badge = '<span class="badge-success">✓ ائتمان سليم</span>';
                }
                // حساب تأخير الأنيميشن لكل بطاقة (تأثير التتابع)
                $delay = 0.6 + ($card_index * 0.1);
                $card_index++;
            ?>
                <div class="customer-card" style="border-left: 4px solid <?php echo $color; ?>; animation-delay: <?php echo $delay; ?>s;" data-usage="<?php echo round($usage_percent, 1); ?>">
                    <div class="flex-row">
                        <div>
                            <strong style="font-size:16px; color:#ffffff;"><?php echo htmlspecialchars($cust['name']); ?></strong>
                            <span style="margin-right:10px;"><?php echo $status_badge; ?></span>
                            <?php if (!$cust['has_real_limit']): ?>
                                <span class="badge-info" style="font-size:10px;">سقف تقديري</span>
                            <?php endif; ?>
                        </div>
                        <span style="font-size:13px; color: var(--text-muted);">
                            آخر تعامل منذ: <strong style="color:<?php echo $cust['days_overdue'] > 30 ? '#ff0055' : ($cust['days_overdue'] > 14 ? '#ffa500' : '#00ff7f'); ?>;"><?php echo $cust['days_overdue']; ?> يوم</strong>
                            <?php if (!empty($cust['phone'])): ?>
                                &nbsp;|&nbsp; 📞 <?php echo htmlspecialchars($cust['phone']); ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- شريط تقدم نيون مع أنيميشن العرض -->
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill animated" data-width="<?php echo min($usage_percent, 100); ?>" style="background: <?php echo $color; ?>; box-shadow: 0 0 10px <?php echo $color; ?>;"></div>
                    </div>

                    <div class="flex-row" style="font-size:12.5px; color: var(--text-muted);">
                        <span>المديونية الحالية: <strong style="color:#ffffff;"><?php echo number_format($cust['debt'], 2); ?></strong> <small><?php echo htmlspecialchars($selected_currency); ?></small></span>
                        <span>سقف الدين المسموح: <strong style="color:#ffffff;"><?php echo number_format($cust['limit'], 2); ?></strong> <small><?php echo htmlspecialchars($selected_currency); ?></small></span>
                        <span>نسبة الاستهلاك: <strong class="usage-counter" data-target="<?php echo round($usage_percent, 1); ?>" style="color:<?php echo $color; ?>;">0%</strong></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- مصفوفة تفاعل وتصنيف العملاء -->
        <div style="background: rgba(0,0,0,0.25); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.03); margin-top: 30px; animation: slideUp 0.6s ease-out 1s both;">
            <h3 style="margin-top:0; color:#ffa500; margin-bottom:15px;">👥 مصفوفة تفاعل وتصنيف العملاء (Customer Engagement Grid)</h3>

            <?php if (empty($customer_engagement)): ?>
                <div class="empty-state" style="padding: 30px;">
                    <div class="icon">📊</div>
                    <p>لا توجد بيانات تفاعل متاحة حالياً</p>
                </div>
            <?php else: ?>
                <table class="engagement-table">
                    <thead>
                        <tr>
                            <th>اسم العميل</th>
                            <th>عدد التفاعلات / الطلبيات</th>
                            <th>إجمالي الإنفاق والمشتريات</th>
                            <th>فئة التفاعل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $eng_index = 0; foreach ($customer_engagement as $eng):
                            $eng_delay = 1.2 + ($eng_index * 0.08);
                            $eng_index++;
                        ?>
                            <tr style="animation-delay: <?php echo $eng_delay; ?>s;">
                                <td><strong><?php echo htmlspecialchars($eng['name']); ?></strong></td>
                                <td style="color:#ffffff;"><?php echo $eng['visits']; ?> معاملة</td>
                                <td style="color:#00ff7f; font-weight:bold;">
                                    <?php echo number_format($eng['spend'], 2); ?>
                                    <span style="font-size:11px;"><?php echo htmlspecialchars($selected_currency); ?></span>
                                </td>
                                <td>
                                    <span class="<?php echo htmlspecialchars($eng['badge']); ?>">
                                        <?php echo htmlspecialchars($eng['tier']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ====================================================================
         سكريبت الأنيميشن: عدادات + أشرطة التقدم + مراقب التمرير
    ==================================================================== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // ====================================================================
        // 1. أنيميشن أشرطة التقدم - تبدأ عند ظهور البطاقة
        // ====================================================================
        const progressBars = document.querySelectorAll('.progress-bar-fill[data-width]');
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const width = bar.getAttribute('data-width');
                    // تأخير بسيط لجعل الأنيميشن أكثر سلاسة
                    setTimeout(function() {
                        bar.style.width = width + '%';
                    }, 300);
                    observer.unobserve(bar);
                }
            });
        }, { threshold: 0.2 });

        progressBars.forEach(function(bar) {
            observer.observe(bar);
        });

        // ====================================================================
        // 2. أنيميشن العدادات في كروت الإحصائيات
        // ====================================================================
        function animateCounter(element, target, duration, isDecimal) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(function() {
                start += increment;
                if (start >= target) {
                    start = target;
                    clearInterval(timer);
                }
                if (isDecimal) {
                    element.textContent = start.toFixed(1);
                } else {
                    element.textContent = Math.floor(start);
                }
            }, 16);
        }

        // عدادات الأعداد الصحيحة
        const counters = document.querySelectorAll('.counter');
        const counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    if (target > 0) {
                        animateCounter(el, target, 1200, false);
                    } else {
                        el.textContent = '0';
                    }
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function(c) { counterObserver.observe(c); });

        // عدادات النسب المئوية (أعداد عشرية)
        const decimalCounters = document.querySelectorAll('.counter-decimal');
        decimalCounters.forEach(function(el) {
            const target = parseFloat(el.getAttribute('data-target'));
            if (target > 0) {
                setTimeout(function() { animateCounter(el, target, 1500, true); }, 800);
            } else {
                el.textContent = '0';
            }
        });

        // ====================================================================
        // 3. أنيميشن نسب الاستهلاك في البطاقات
        // ====================================================================
        const usageCounters = document.querySelectorAll('.usage-counter');
        const usageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseFloat(el.getAttribute('data-target'));
                    if (target > 0) {
                        let start = 0;
                        const increment = target / 60;
                        const timer = setInterval(function() {
                            start += increment;
                            if (start >= target) {
                                start = target;
                                clearInterval(timer);
                            }
                            el.textContent = start.toFixed(1) + '%';
                        }, 16);
                    } else {
                        el.textContent = '0%';
                    }
                    usageObserver.unobserve(el);
                }
            });
        }, { threshold: 0.3 });

        usageCounters.forEach(function(c) { usageObserver.observe(c); });

        // ====================================================================
        // 4. بحث فوري أثناء الكتابة (يكمل فلترة الخادم)
        // ====================================================================
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const cards = document.querySelectorAll('.customer-card');
                cards.forEach(function(card) {
                    const name = card.querySelector('strong').textContent.toLowerCase();
                    if (query === '' || name.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
