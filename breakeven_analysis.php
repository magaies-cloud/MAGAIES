<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تحليل نقطة التعادل الفوري
// ====================================================================
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
$fixed_costs = ($selected_currency === 'SDG') ? 3000000 : 5000; // إيجارات ورواتب وتكاليف شمسية
$price_per_ton = ($selected_currency === 'SDG') ? 1000000 : 1600; // سعر بيع طن السيخ المتوسط
$variable_cost_per_ton = ($selected_currency === 'SDG') ? 800000 : 1300; // تكلفة شراء طن السيخ ومصاريف نقله

$contribution_margin = $price_per_ton - $variable_cost_per_ton;
$breakeven_point_units = $fixed_costs / $contribution_margin; // كم طن نحتاج نبيع
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تحليل نقطة التعادل | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #050510;
            --panel-bg: rgba(15, 12, 30, 0.7);
            --border-neon: rgba(255, 165, 0, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Cairo', sans-serif; padding: 30px; background-image: radial-gradient(circle at top right, rgba(255, 165, 0, 0.08), transparent 40%); }
        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; }
        .metric-box { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,165,0,0.1); border-radius: 16px; padding: 20px; text-align: center; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-container { position: relative; height: 320px; background: rgba(0,0,0,0.2); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="glass-panel">
        <div class="header">
            <div>
                <h1 style="margin:0; background: linear-gradient(135deg, #ffa500, #ff007f); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight:900;">مؤشر نقطة التعادل الفوري (Break-Even)</h1>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">حساب حجم المبيعات المطلوب لتغطية كافة المصاريف التشغيلية قبل بدء تحقيق الأرباح</p>
            </div>
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <div class="grid-2">
            <div class="metric-box">
                <span style="color: var(--text-muted);">التكاليف الثابتة شهرياً (رواتب، إيجار، طاقة شمسية)</span>
                <h2 style="color: #ff007f; margin:10px 0;"><?php echo number_format($fixed_costs, 2); ?> <span style="font-size:12px;"><?php echo $selected_currency; ?></span></h2>
                <span style="font-size:11px; color: var(--text-muted);">تكاليف مستقرة ومستمرة بغض النظر عن حجم المبيعات</span>
            </div>
            <div class="metric-box" style="border-color: rgba(0,255,127,0.2);">
                <span style="color: var(--text-muted);">حجم المبيعات الآمن المطلوب شهرياً للتعادل</span>
                <h2 style="color: #00ff7f; margin:10px 0;"><?php echo number_format($breakeven_point_units, 2); ?> طن</h2>
                <span style="font-size:11px; color: #00ff7f;">▲ أي مبيعات فوق هذا الرقم تصبح صافي أرباح بالكامل!</span>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="breakevenChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('breakevenChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['0 طن', '5 طن', '10 طن', '15 طن (نقطة التعادل)', '20 طن', '25 طن', '30 طن'],
                labels_en: ['0 tons', '5 tons', '10 tons', '15 tons (Break-even)', '20 tons', '25 tons', '30 tons'],
                datasets: [{
                    label: 'إجمالي المبيعات والوصول',
                    data: [0, 8000, 16000, 24000, 32000, 40000, 48000],
                    borderColor: '#00ff7f',
                    borderWidth: 3,
                    tension: 0.1
                }, {
                    label: 'إجمالي التكاليف (متغيرة + ثابتة)',
                    data: [9000, 14000, 19000, 24000, 29000, 34000, 39000],
                    borderColor: '#ff0055',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.1
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
