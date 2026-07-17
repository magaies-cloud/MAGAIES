<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - دفتر الأستاذ العام المطور وتوزيع الديون
// ====================================================================
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$page_title = 'دفتر الأستاذ العام المطور V2';
$header_theme_class = 'header-theme-reports';
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// فلاتر البحث والتاريخ
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$target_customer_id = isset($_GET['accountId']) ? (int)str_replace('cust-', '', $_GET['accountId']) : 0;

$debt_distribution = [];
$customer_profile = null;
$ledger_entries = [];
$selected_customer = "اختر عميلاً";
$customers_list = [];
$db_connected = false;
$is_mock_data = false; // علامة البيانات التجريبية

try {
    @include_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        $db_connected = true;

        // 1. حساب التمركز الائتماني الفعلي من جدول customers وجلب قائمة العملاء
        $cust_stmt = $pdo->query("SELECT id, data FROM customers WHERE is_deleted = 0 OR is_deleted IS NULL");
        $cust_rows = $cust_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $grand_total = 0;
        $all_debts = [];
        
        foreach ($cust_rows as $row) {
            $d = json_decode($row['data'], true);
            if (!$d) continue;
            
            $cid = $d['id'] ?? $row['id'];
            $cname = $d['name'] ?? 'عميل غير معروف';
            $bal = (float)($d['currentBalance'] ?? 0);
            
            $customers_list[$cid] = $cname;
            
            // تحديد العميل الافتراضي إذا لم يتم اختيار عميل
            if ($target_customer_id == 0 && count($customers_list) == 1) {
                $target_customer_id = $cid;
            }
            
            if ($bal > 0) {
                $all_debts[] = [
                    'id' => $cid,
                    'customer_name' => $cname,
                    'debt' => $bal,
                    'bounced_checks' => 0, // من الممكن جلبها من الشيكات لو وجدت
                    'max_days' => 0
                ];
                $grand_total += $bal;
            }
        }
        
        if ($target_customer_id == 0 && !empty($all_debts)) {
            // اختيار العميل الأكبر مديونية كافتراضي
            usort($all_debts, function($a, $b) { return $b['debt'] <=> $a['debt']; });
            $target_customer_id = $all_debts[0]['id'];
        }
        
        // جلب اسم العميل المختار حتى لو رصيده صفر
        if ($target_customer_id > 0 && isset($customers_list[$target_customer_id])) {
            $selected_customer = $customers_list[$target_customer_id];
            $customer_profile = [
                'customer_name' => $selected_customer,
                'current_balance' => 0,
                'bounced_checks_count' => 0,
                'max_days_overdue' => 'لا يوجد بيانات شيكات',
                'share_of_total_company_debt' => 0
            ];
        }
        
        // ترتيب وتنسيق التوزيع
        usort($all_debts, function($a, $b) { return $b['debt'] <=> $a['debt']; });
        
        if ($grand_total > 0) {
            $colors = ['#ff0055', '#00ffff', '#ffa500', '#00ff7f', '#a855f7', '#3b82f6', '#ffcc00'];
            $idx = 0;
            foreach ($all_debts as $row) {
                $pct = ($row['debt'] / $grand_total) * 100;
                $debt_distribution[] = [
                    'id' => $row['id'],
                    'customer' => $row['customer_name'],
                    'debt' => $row['debt'],
                    'color' => $colors[$idx % count($colors)],
                    'percentage' => round($pct, 2)
                ];
                $idx++;
            }
            
            // تحديث ملف العميل المختار بالبيانات الصحيحة من قائمة الديون
            foreach ($all_debts as $row) {
                if ($row['id'] == $target_customer_id) {
                    $customer_profile['current_balance'] = $row['debt'];
                    $customer_profile['share_of_total_company_debt'] = ($row['debt'] / $grand_total) * 100;
                    break;
                }
            }
        }
        
        // 2. بناء دفتر الأستاذ للعميل المختار
        if ($target_customer_id > 0) {
            // أ. الفواتير (مدين)
            $sales_query = "SELECT date_val, data FROM sales_invoices WHERE is_deleted = 0 AND customer_id_val = :cid";
            $conditions = [];
            $params = [':cid' => $target_customer_id];
            
            if (!empty($date_from)) { $sales_query .= " AND date_val >= :date_from"; $params[':date_from'] = $date_from . ' 00:00:00'; }
            if (!empty($date_to)) { $sales_query .= " AND date_val <= :date_to_end"; $params[':date_to_end'] = $date_to . ' 23:59:59'; }
            
            $sales_stmt = $pdo->prepare($sales_query);
            $sales_stmt->execute($params);
            
            // للحصول على فواتير المبيعات للعميل كـ String "cust-XX" أحياناً في JSON
            $sales_query_str = "SELECT date_val, data FROM sales_invoices WHERE is_deleted = 0 AND customer_id_val = :cid_str";
            $params_str = [':cid_str' => 'cust-' . $target_customer_id];
            if (!empty($date_from)) { $sales_query_str .= " AND date_val >= :date_from"; $params_str[':date_from'] = $date_from . ' 00:00:00'; }
            if (!empty($date_to)) { $sales_query_str .= " AND date_val <= :date_to_end"; $params_str[':date_to_end'] = $date_to . ' 23:59:59'; }
            $sales_stmt_str = $pdo->prepare($sales_query_str);
            $sales_stmt_str->execute($params_str);
            
            $all_sales_rows = array_merge($sales_stmt->fetchAll(), $sales_stmt_str->fetchAll());
            // إزالة التكرار إن وجد
            $unique_sales_rows = array_map("unserialize", array_unique(array_map("serialize", $all_sales_rows)));

            foreach ($unique_sales_rows as $row) {
                $d = json_decode($row['data'], true);
                $ledger_entries[] = [
                    'date' => $row['date_val'],
                    'desc' => 'فاتورة مبيعات ' . ($d['invoiceNo'] ?? ''),
                    'debit' => (float)($d['totalAmount'] ?? 0),
                    'credit' => 0
                ];
            }
            
            // ب. سندات القبض (دائن) - محسّن: فلترة SQL بـ JSON لتقليل الحمل على PHP
            $cust_key = 'cust-' . $target_customer_id;
            $receipts_query = "SELECT date_val, data FROM single_receipts WHERE is_deleted = 0";
            // محاولة فلترة في SQL مباشرة باستخدام LIKE على حقل data للعميل المحدد
            $receipts_query .= " AND (data LIKE :cust_pattern1 OR data LIKE :cust_pattern2)";
            $r_params = [
                ':cust_pattern1' => '%"fromAccount":"' . $cust_key . '"%',
                ':cust_pattern2' => '%"fromAccount": "' . $cust_key . '"%'
            ];
            if (!empty($date_from)) { $receipts_query .= " AND date_val >= :r_date_from"; $r_params[':r_date_from'] = $date_from . ' 00:00:00'; }
            if (!empty($date_to)) { $receipts_query .= " AND date_val <= :r_date_to_end"; $r_params[':r_date_to_end'] = $date_to . ' 23:59:59'; }
            
            $receipts_stmt = $pdo->prepare($receipts_query);
            $receipts_stmt->execute($r_params);
            
            while ($row = $receipts_stmt->fetch()) {
                $d = json_decode($row['data'], true);
                $from_acc = $d['fromAccount'] ?? '';
                // تأكيد نهائي في PHP أن السند فعلاً من العميل المحدد
                if ($from_acc === $cust_key) {
                    $ledger_entries[] = [
                        'date' => $row['date_val'],
                        'desc' => 'سند قبض ' . ($d['description'] ?? ''),
                        'debit' => 0,
                        'credit' => (float)($d['amountLocal'] ?? 0)
                    ];
                }
            }
            
            // ترتيب حسب التاريخ
            usort($ledger_entries, function($a, $b) {
                return strtotime($a['date']) <=> strtotime($b['date']);
            });
            
            // حساب الرصيد التراكمي
            $running_balance = 0;
            foreach ($ledger_entries as &$entry) {
                $running_balance += $entry['debit'] - $entry['credit'];
                $entry['balance'] = $running_balance;
            }
        }
    }
} catch (Exception $e) {
    // خطأ أو لا توجد جداول
}

// البديل في حال تعذر الاستعلام أو البيانات فارغة - مع علامة تجريبية
if (empty($debt_distribution) && empty($date_from) && empty($date_to)) {
    $is_mock_data = true;
    $debt_distribution = [
        ['customer' => 'شركة النيل الأزرق للمقاولات', 'debt' => $rate * 14.5, 'color' => '#ff0055', 'percentage' => 41.2],
        ['customer' => 'مجموعة البرج الهندسية', 'debt' => $rate * 8.2, 'color' => '#00ffff', 'percentage' => 23.3],
        ['customer' => 'مقاولات السجانة الموحدة', 'debt' => $rate * 11.0, 'color' => '#ffa500', 'percentage' => 31.3],
        ['customer' => 'المهندس محمد كمال', 'debt' => $rate * 1.5, 'color' => '#00ff7f', 'percentage' => 4.2]
    ];
}

if (!$customer_profile && empty($date_from) && empty($date_to)) {
    $is_mock_data = true;
    $customer_profile = [
        'customer_name' => 'شركة النيل الأزرق للمقاولات',
        'current_balance' => $rate * 14.5,
        'bounced_checks_count' => 1,
        'max_days_overdue' => '12 يوم',
        'share_of_total_company_debt' => 41.2
    ];
    $selected_customer = 'شركة النيل الأزرق للمقاولات';
}

if (empty($ledger_entries) && empty($date_from) && empty($date_to)) {
    $is_mock_data = true;
    $ledger_entries = [
        ['date' => '2026-06-01', 'desc' => 'رصيد افتتاحي مرحل', 'debit' => $rate * 5.0, 'credit' => 0, 'balance' => $rate * 5.0],
        ['date' => '2026-06-15', 'desc' => 'فاتورة بيع حديد 4 لينيا الأسعد (آجل)', 'debit' => $rate * 12.0, 'credit' => 0, 'balance' => $rate * 17.0],
        ['date' => '2026-07-02', 'desc' => 'سند قبض - تحويل عبر تطبيق بنكك', 'debit' => 0, 'credit' => $rate * 2.5, 'balance' => $rate * 14.5]
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>دفتر الأستاذ العام وتوزيع الديون | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #040209;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(157, 78, 221, 0.25);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body { 
            background-color: var(--bg-dark); 
            color: var(--text-main); 
            font-family: 'Cairo', sans-serif; 
            padding: 30px; 
            background-image: radial-gradient(circle at top left, rgba(157, 78, 221, 0.1), transparent 45%); 
            margin: 0;
        }
        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5); animation: fadeIn 0.8s ease-out; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; transition: all 0.3s; }
        .btn-back:hover { background: rgba(157, 78, 221, 0.15); border-color: #9d4edd; color: #fff; }
        
        .filter-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 15px 20px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; animation: slideUp 0.6s ease-out 0.2s backwards; }
        .filter-input { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 8px 15px; border-radius: 8px; font-family: 'Cairo'; outline: none; }
        .filter-input:focus { border-color: #9d4edd; box-shadow: 0 0 10px rgba(157, 78, 221, 0.3); }
        .btn-filter { background: linear-gradient(135deg, #9d4edd, #6930c3); color: #fff; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; font-family: 'Cairo'; }
        
        .grid-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; }
        .chart-container { background: rgba(0,0,0,0.25); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.03); display: flex; flex-direction: column; align-items: center; justify-content: flex-start; animation: slideUp 0.6s ease-out 0.4s backwards; }
        .table-container { background: rgba(0,0,0,0.2); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.03); animation: slideUp 0.6s ease-out 0.6s backwards; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: right; padding: 12px; color: var(--text-muted); font-size: 13px; border-bottom: 2px solid var(--border-neon); }
        td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 13.5px; transition: background 0.3s; }
        tr:hover td { background: rgba(255,255,255,0.02); }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    <?php include 'includes/header.php'; ?>
    <div class="glass-panel">
        <?php if ($is_mock_data): ?>
        <div style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3); color: #fbbf24; padding: 10px 20px; border-radius: 14px; text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 20px; animation: fadeIn 1s ease-out;">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>
        
        <form method="GET" class="filter-container">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 13px;">العميل:</label>
                <select name="accountId" class="filter-input" onchange="this.form.submit()">
                    <?php if(empty($customers_list)): ?>
                        <option value="cust-0"><?php echo htmlspecialchars($selected_customer); ?></option>
                    <?php else: ?>
                        <?php foreach($customers_list as $cid => $cname): ?>
                            <option value="cust-<?php echo $cid; ?>" <?php echo $target_customer_id == $cid ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cname); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 13px;">من:</label>
                <input type="date" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 13px;">إلى:</label>
                <input type="date" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <button type="submit" class="btn-filter">تحديث الكشف</button>
            <?php if(!empty($date_from) || !empty($date_to)): ?>
                <a href="general_ledger_v2.php?currency=<?php echo $selected_currency; ?>&accountId=cust-<?php echo $target_customer_id; ?>" style="color:#9d4edd; text-decoration:none; font-size:13px; margin-right:10px;">إلغاء الفلتر</a>
            <?php endif; ?>
        </form>

        <div class="grid-layout">
            <!-- تفاصيل الحركات لأستاذ العميل -->
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
                    <h3 style="margin:0; color:#00ffff;">👤 كشف حساب: <?php echo htmlspecialchars($selected_customer); ?></h3>
                </div>
                
                <?php if(empty($ledger_entries)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted); background: rgba(0,0,0,0.2); border-radius: 16px;">
                        لا توجد حركات مسجلة لهذا العميل في الفترة المحددة.
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>البيان / الحركة</th>
                                <th>مدين (له)</th>
                                <th>دائن (عليه)</th>
                                <th>الرصيد المستمر</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ledger_entries as $index => $entry): 
                                $delay = 0.6 + ($index * 0.05);
                            ?>
                                <tr style="animation: slideUp 0.4s ease-out <?php echo $delay; ?>s backwards;">
                                    <td><?php echo $entry['date']; ?></td>
                                    <td><strong><?php echo $entry['desc']; ?></strong></td>
                                    <td style="color:#ff0055; font-weight:bold;"><?php echo $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-'; ?></td>
                                    <td style="color:#00ff7f; font-weight:bold;"><?php echo $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-'; ?></td>
                                    <td style="color:#00ffff; font-weight:bold;"><?php echo number_format($entry['balance'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- كعكة توزيع الديون -->
            <div class="chart-container">
                <h3 style="margin-top:0; color:#9d4edd; margin-bottom:20px; width:100%;">🍩 التمركز الائتماني للسوق</h3>
                <div style="width: 250px; height: 250px; margin-bottom: 20px;">
                    <canvas id="debtDonutChart"></canvas>
                </div>
                <div style="width:100%; font-size:12px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <?php 
                    $count = 0;
                    foreach ($debt_distribution as $dd): 
                        if ($count++ >= 8) break; // Top 8 only in legend
                    ?>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="display:inline-block; width:10px; height:10px; background:<?php echo $dd['color']; ?>; border-radius:50%;"></span>
                            <span style="color:var(--text-muted); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="<?php echo htmlspecialchars($dd['customer']); ?>">
                                <?php echo htmlspecialchars($dd['customer']); ?> (<strong><?php echo $dd['percentage']; ?>%</strong>)
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- تحليل الجدارة الائتمانية والمخاطر للعميل -->
                <?php if($customer_profile): ?>
                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(0,255,255,0.1); border-radius: 20px; padding: 20px; margin-top: 25px; width: 100%; box-sizing: border-box;">
                    <h3 style="margin-top:0; color:#ffa500; font-size:14px; margin-bottom:12px; font-weight:bold;">🛡️ ملف العميل (Credit Profile)</h3>
                    <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom:6px;">
                            <span style="color:var(--text-muted);">الرصيد القائم:</span>
                            <strong style="color:#ffffff;"><?php echo number_format($customer_profile['current_balance'], 2); ?> <?php echo $selected_currency; ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom:6px;">
                            <span style="color:var(--text-muted);">الشيكات المرتجعة:</span>
                            <strong style="color:<?php echo $customer_profile['bounced_checks_count'] > 0 ? '#ff0055' : '#00ff7f'; ?>;"><?php echo $customer_profile['bounced_checks_count']; ?> شيك</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom:6px;">
                            <span style="color:var(--text-muted);">أقصى مدة تأخير سداد:</span>
                            <strong style="color:<?php echo $customer_profile['max_days_overdue'] !== 'لا يوجد بيانات شيكات' ? '#ff0055' : '#00ff7f'; ?>;"><?php echo $customer_profile['max_days_overdue']; ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">حصة العميل من ديون السوق:</span>
                            <strong style="color:#00ffff;"><?php echo number_format($customer_profile['share_of_total_company_debt'], 2); ?>%</strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('debtDonutChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [<?php foreach ($debt_distribution as $dd) { echo "'" . addslashes($dd['customer']) . "',"; } ?>],
                datasets: [{
                    data: [<?php foreach ($debt_distribution as $dd) { echo $dd['debt'] . ","; } ?>],
                    backgroundColor: [<?php foreach ($debt_distribution as $dd) { echo "'" . $dd['color'] . "',"; } ?>],
                    borderColor: '#090514',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { titleFont: { family: 'Cairo' }, bodyFont: { family: 'Cairo' } } },
                cutout: '65%',
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1800,
                    easing: 'easeOutQuart'
                }
            }
        });
    </script>
</body>
</html>
