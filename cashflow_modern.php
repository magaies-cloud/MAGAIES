<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - كاشف التدفقات النقدية الفعلي V2
// ====================================================================
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
$rate = ($selected_currency === 'SDG') ? 600000 : 1000;

// فلاتر البحث والتاريخ
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$current_cash = 0;
$upcoming_collection = 0;
$upcoming_payable = 0;

$monthly_inflows = [];
$monthly_outflows = [];
$labels = [];

$db_connected = false;
$is_mock_data = false; // علامة البيانات التجريبية

try {
    @include_once __DIR__ . '/api/db.php';
    if (isset($pdo)) {
        $db_connected = true;
        
        // 1. حساب الرصيد النقدي الحالي (safes + banks)
        $safes_stmt = $pdo->query("SELECT data FROM safes WHERE is_deleted = 0 OR is_deleted IS NULL");
        while ($row = $safes_stmt->fetch()) {
            $data = json_decode($row['data'], true);
            $current_cash += (float)($data['currentBalance'] ?? ($data['balance'] ?? 0));
        }
        
        $banks_stmt = $pdo->query("SELECT data FROM banks WHERE is_deleted = 0 OR is_deleted IS NULL");
        while ($row = $banks_stmt->fetch()) {
            $data = json_decode($row['data'], true);
            $current_cash += (float)($data['currentBalance'] ?? ($data['balance'] ?? 0));
        }
        
        // 2. تجميع التدفقات الداخلة (المبيعات + المقبوضات)
        $inflows = [];
        // المبيعات
        $sales_stmt = $pdo->query("SELECT date_val, data FROM sales_invoices WHERE is_deleted = 0");
        while ($row = $sales_stmt->fetch()) {
            $date = date('Y-m', strtotime($row['date_val']));
            if (!isset($inflows[$date])) $inflows[$date] = 0;
            $d = json_decode($row['data'], true);
            $inflows[$date] += (float)($d['totalAmount'] ?? 0);
        }
        // سندات القبض
        $receipts_stmt = $pdo->query("SELECT date_val, data FROM single_receipts WHERE is_deleted = 0");
        while ($row = $receipts_stmt->fetch()) {
            $date = date('Y-m', strtotime($row['date_val']));
            if (!isset($inflows[$date])) $inflows[$date] = 0;
            $d = json_decode($row['data'], true);
            $inflows[$date] += (float)($d['amountLocal'] ?? 0);
        }
        
        // 3. تجميع التدفقات الخارجة (المشتريات + المدفوعات)
        $outflows = [];
        // المشتريات
        $purchases_stmt = $pdo->query("SELECT date_val, data FROM purchase_invoices WHERE is_deleted = 0");
        while ($row = $purchases_stmt->fetch()) {
            $date = date('Y-m', strtotime($row['date_val']));
            if (!isset($outflows[$date])) $outflows[$date] = 0;
            $d = json_decode($row['data'], true);
            $outflows[$date] += (float)($d['totalAmount'] ?? ($d['grandTotal'] ?? 0));
        }
        // سندات الصرف
        $payments_stmt = $pdo->query("SELECT date_val, data FROM single_payments WHERE is_deleted = 0");
        while ($row = $payments_stmt->fetch()) {
            $date = date('Y-m', strtotime($row['date_val']));
            if (!isset($outflows[$date])) $outflows[$date] = 0;
            $d = json_decode($row['data'], true);
            $outflows[$date] += (float)($d['amountLocal'] ?? 0);
        }
        
        // تجهيز بيانات الرسم البياني
        $all_months = array_unique(array_merge(array_keys($inflows), array_keys($outflows)));
        sort($all_months);
        
        // فلترة التاريخ إذا وجد
        if (!empty($date_from) || !empty($date_to)) {
            $filtered_months = [];
            foreach ($all_months as $m) {
                $m_date = $m . '-01';
                $keep = true;
                if (!empty($date_from) && $m_date < substr($date_from, 0, 7) . '-01') $keep = false;
                if (!empty($date_to) && $m_date > substr($date_to, 0, 7) . '-31') $keep = false;
                if ($keep) $filtered_months[] = $m;
            }
            $all_months = $filtered_months;
        }
        
        // أخذ آخر 12 شهر للعرض
        $all_months = array_slice($all_months, -12);
        
        foreach ($all_months as $m) {
            $labels[] = date('M Y', strtotime($m . '-01'));
            $monthly_inflows[] = $inflows[$m] ?? 0;
            $monthly_outflows[] = $outflows[$m] ?? 0;
        }
        
        // --- حساب التحصيل والدفع القادم من الشيكات المعلقة الحقيقية (30 يوم) ---
        try {
            $checks_stmt = $pdo->query("SELECT data FROM `checks` WHERE is_deleted = 0 OR is_deleted IS NULL");
            while ($crow = $checks_stmt->fetch()) {
                $cd = json_decode($crow['data'], true);
                if (!$cd || ($cd['status'] ?? '') !== 'PENDING') continue;
                $due = $cd['dueDate'] ?? ($cd['due_date'] ?? '');
                if (!$due) continue;
                // فقط الشيكات المعلقة خلال الـ 30 يوم القادمة
                if ($due >= date('Y-m-d') && $due <= date('Y-m-d', strtotime('+30 days'))) {
                    $amt = (float)($cd['amount'] ?? 0);
                    if (($cd['type'] ?? '') === 'INBOUND') {
                        $upcoming_collection += $amt;
                    } else {
                        $upcoming_payable += $amt;
                    }
                }
            }
        } catch (Exception $ex) {
            // جدول الشيكات غير موجود
        }
    }
} catch (Exception $e) {
    // خطأ أو الجداول غير مهيأة
}

// البديل في حال خلو قاعدة البيانات
if ($current_cash == 0 && empty($labels)) {
    $is_mock_data = true;
    $current_cash = $rate * 45.5;
    $upcoming_collection = $rate * 18.0;
    $upcoming_payable = $rate * 12.5;
    $labels = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو'];
    $monthly_inflows = [$rate*30, $rate*35, $rate*28, $rate*40, $rate*48, $rate*42, $rate*45];
    $monthly_outflows = [$rate*20, $rate*22, $rate*18, $rate*25, $rate*30, $rate*26, $rate*28];
}

$projected_liquidity = $current_cash + $upcoming_collection - $upcoming_payable;
$trend_inflow = count($monthly_inflows) > 1 ? (($monthly_inflows[count($monthly_inflows)-1] - $monthly_inflows[count($monthly_inflows)-2]) / ($monthly_inflows[count($monthly_inflows)-2] ?: 1)) * 100 : 0;
$trend_outflow = count($monthly_outflows) > 1 ? (($monthly_outflows[count($monthly_outflows)-1] - $monthly_outflows[count($monthly_outflows)-2]) / ($monthly_outflows[count($monthly_outflows)-2] ?: 1)) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كاشف التدفقات النقدية | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #090514;
            --panel-bg: rgba(20, 15, 38, 0.7);
            --border-neon: rgba(0, 255, 127, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            padding: 30px;
            background-image: radial-gradient(circle at top left, rgba(0, 255, 127, 0.08), transparent 40%);
        }
        .glass-panel {
            background: var(--panel-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.8s ease-out;
        }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; transition: all 0.3s; }
        .btn-back:hover { background: rgba(255,255,255,0.05); color: #fff; }
        
        .filter-container { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 15px 20px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; animation: slideUp 0.6s ease-out 0.2s backwards; }
        .filter-input { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 8px 15px; border-radius: 8px; font-family: 'Cairo'; outline: none; }
        .filter-input:focus { border-color: #00ff7f; }
        .btn-filter { background: linear-gradient(135deg, #00ff7f, #00ffff); color: #000; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; font-family: 'Cairo'; }
        
        .ticker-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            animation: slideUp 0.6s ease-out backwards;
        }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .up-trend { color: #00ff7f; font-weight: bold; }
        .down-trend { color: #ff0055; font-weight: bold; }
        .chart-container { position: relative; height: 350px; margin-bottom: 30px; background: rgba(0,0,0,0.2); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); animation: slideUp 0.6s ease-out 0.6s backwards; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="glass-panel">
        <?php if ($is_mock_data): ?>
        <div style="background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3); color: #fbbf24; padding: 10px 20px; border-radius: 14px; text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 20px; animation: fadeIn 1s ease-out;">
            ⚠️ الشاشة تعرض بيانات تجريبية حالياً — سيتم عرض البيانات الحقيقية عند توفر عمليات بيعية في النظام
        </div>
        <?php endif; ?>
        <div class="header">
            <div>
                <h1 style="margin:0; background: linear-gradient(135deg, #00ff7f, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight:900;">كاشف التدفقات النقدية الفعلي (IAS 7)</h1>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">مراقبة السيولة الحقيقية وحركة الخزائن والبنك</p>
            </div>
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <form method="GET" class="filter-container">
            <input type="hidden" name="currency" value="<?php echo htmlspecialchars($selected_currency); ?>">
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 13px;">من شهر:</label>
                <input type="month" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: var(--text-muted); font-size: 13px;">إلى شهر:</label>
                <input type="month" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <button type="submit" class="btn-filter">تطبيق الفرز</button>
            <?php if(!empty($date_from) || !empty($date_to)): ?>
                <a href="cashflow_modern.php?currency=<?php echo $selected_currency; ?>" style="color:#ff0055; text-decoration:none; font-size:13px; margin-right:10px;">إلغاء الفلتر</a>
            <?php endif; ?>
        </form>

        <!-- كروت مؤشرات البورصة السريعة -->
        <div class="grid-3">
            <div class="ticker-card" style="animation-delay: 0.3s;">
                <span style="color: var(--text-muted); font-size:13px;">▲ التدفقات الداخلة المتوقعة (تحصيل)</span>
                <h2 class="up-trend" style="margin: 10px 0 5px 0;">+<span class="counter" data-target="<?php echo $upcoming_collection; ?>">0</span> <span style="font-size:12px;"><?php echo $selected_currency; ?></span></h2>
                <span style="font-size:11px; color:<?php echo $trend_inflow >= 0 ? '#00ff7f' : '#ff0055'; ?>;">
                    <?php echo $trend_inflow >= 0 ? '▲ صعود' : '▼ تراجع'; ?> بنسبة <?php echo number_format(abs($trend_inflow), 1); ?>% مقارنة بالشهر السابق
                </span>
            </div>
            <div class="ticker-card" style="animation-delay: 0.4s;">
                <span style="color: var(--text-muted); font-size:13px;">▼ التدفقات الخارجة المتوقعة (مدفوعات)</span>
                <h2 class="down-trend" style="margin: 10px 0 5px 0;">-<span class="counter" data-target="<?php echo $upcoming_payable; ?>">0</span> <span style="font-size:12px;"><?php echo $selected_currency; ?></span></h2>
                <span style="font-size:11px; color:<?php echo $trend_outflow <= 0 ? '#00ff7f' : '#ff0055'; ?>;">
                    <?php echo $trend_outflow > 0 ? '▲ ارتفاع النفقات بـ ' : '▼ انخفاض النفقات بـ '; ?><?php echo number_format(abs($trend_outflow), 1); ?>%
                </span>
            </div>
            <div class="ticker-card" style="border-color: rgba(0, 255, 255, 0.2); animation-delay: 0.5s;">
                <span style="color: var(--text-muted); font-size:13px;">💎 صافي السيولة المتوفرة (الخزائن والبنوك)</span>
                <h2 style="margin: 10px 0 5px 0; color:#00ffff;"><span class="counter" data-target="<?php echo $current_cash; ?>">0</span> <span style="font-size:12px;"><?php echo $selected_currency; ?></span></h2>
                <span style="font-size:11px; color:#00ffff;">● رصيد حقيقي مستخرج من الحسابات المالية</span>
            </div>
        </div>

        <!-- توقعات السيولة لـ 30 يوماً القادمة -->
        <div style="background: rgba(0, 255, 255, 0.04); border: 1.5px dashed #00ffff; border-radius: 16px; padding: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; animation: slideUp 0.6s ease-out 0.6s backwards;">
            <div style="font-size: 30px; animation: pulse 2s infinite;">🔮</div>
            <div>
                <strong style="color: #00ffff; font-size: 15px; display: block; margin-bottom: 5px;">توقعات السيولة النقدية لـ 30 يوماً القادمة (Live Cash Flow Forecast)</strong>
                <span style="font-size: 13px; color: #e2e8f0; line-height: 1.6;">
                    الرصيد النقدي الحالي: <strong><?php echo number_format($current_cash, 2); ?> <?php echo $selected_currency; ?></strong>، 
                    يضاف إليه تحصيلات متوقعة بقيمة: <strong><?php echo number_format($upcoming_collection, 2); ?> <?php echo $selected_currency; ?></strong>، 
                    ويخصم منه مدفوعات مجدولة بقيمة: <strong><?php echo number_format($upcoming_payable, 2); ?> <?php echo $selected_currency; ?></strong>.
                    السيولة المتوقعة بعد 30 يوماً: <strong style="color: <?php echo $projected_liquidity >= 0 ? '#00ff7f' : '#ff0055'; ?>;"><?php echo number_format($projected_liquidity, 2); ?> <?php echo $selected_currency; ?></strong>.
                </span>
            </div>
        </div>

        <!-- الرسم البياني للبورصة -->
        <div class="chart-container">
            <canvas id="cashflowChart"></canvas>
        </div>
    </div>

    <script>
        // Counter Animation
        document.querySelectorAll('.counter').forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const duration = 1500;
            const inc = target / (duration / 16);
            let current = 0;
            const isFloat = target % 1 !== 0 || target > 1000;
            
            if (target === 0) return;
            
            const updateCounter = () => {
                current += inc;
                if (current < target) {
                    counter.innerText = isFloat ? current.toLocaleString(undefined, {maximumFractionDigits:0}) : Math.ceil(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.innerText = isFloat ? target.toLocaleString(undefined, {maximumFractionDigits:2}) : target;
                }
            };
            updateCounter();
        });

        // Chart.js Setup
        const ctx = document.getElementById('cashflowChart').getContext('2d');
        
        // حساب صافي التدفق
        const inflows = <?php echo json_encode($monthly_inflows); ?>;
        const outflows = <?php echo json_encode($monthly_outflows); ?>;
        const netFlows = inflows.map((inf, idx) => inf - outflows[idx]);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'الكاش الداخل (+)',
                    data: inflows,
                    borderColor: '#00ff7f',
                    backgroundColor: 'rgba(0, 255, 127, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }, {
                    label: 'الكاش الخارج (-)',
                    data: outflows,
                    borderColor: '#ff0055',
                    backgroundColor: 'rgba(255, 0, 85, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }, {
                    label: 'صافي التدفق (Net)',
                    data: netFlows,
                    type: 'bar',
                    backgroundColor: netFlows.map(val => val >= 0 ? 'rgba(0, 255, 255, 0.5)' : 'rgba(255, 165, 0, 0.5)'),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#ffffff', font: { family: 'Cairo' } } } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#a0aec0', font: { family: 'Cairo' } } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#a0aec0', font: { family: 'Cairo' } } }
                }
            }
        });
    </script>
</body>
</html>
