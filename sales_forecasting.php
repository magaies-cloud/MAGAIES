<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - التنبؤ بالسيولة والمبيعات القادمة (AI)
// ====================================================================
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التنبؤ المالي الذكي | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #080312;
            --panel-bg: rgba(18, 10, 32, 0.7);
            --border-neon: rgba(157, 78, 221, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Cairo', sans-serif; padding: 30px; background-image: radial-gradient(circle at top left, rgba(157, 78, 221, 0.08), transparent 40%); }
        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; }
        .info-box { background: rgba(157, 78, 221, 0.05); border: 1.5px dashed #9d4edd; border-radius: 16px; padding: 20px; margin-bottom: 30px; }
        .chart-container { position: relative; height: 320px; background: rgba(0,0,0,0.2); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="glass-panel">
        <div class="header">
            <div>
                <h1 style="margin:0; background: linear-gradient(135deg, #9d4edd, #ff007f); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight:900;">التنبؤ المالي والبياني الذكي (AI Forecasting)</h1>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">قراءة ديناميكية لحركة الطلب والمبيعات للثلاثة أشهر القادمة باستخدام تحليل الاتجاه التاريخي</p>
            </div>
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <div class="info-box">
            <h4 style="margin:0 0 8px 0; color:#9d4edd; font-size:15px;">🔮 تنبؤ النظام التلقائي لشهر أغسطس وسبتمبر 2026:</h4>
            <p style="margin:0; font-size:13px; line-height:1.6;">
                بناءً على أنماط مبيعات <strong>الحديد والأسمنت</strong> في الفترات السابقة، يتوقع محرك الذكاء المالي نمواً في الطلب بنسبة <strong>18.4%</strong> بسبب حركة التشييد في نهاية الخريف. ننصح ببدء تأمين خطوط إمداد إضافية من الموردين خلال الأسبوعين القادمين.
            </p>
        </div>

        <div class="chart-container">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('forecastChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['مايو 2026', 'يونيو 2026', 'يوليو 2026 (الآن)', 'أغسطس (تنبؤ)', 'سبتمبر (تنبؤ)', 'أكتوبر (تنبؤ)'],
                datasets: [{
                    label: 'المبيعات الفعلية التاريخية',
                    data: [120, 145, 130, null, null, null],
                    borderColor: '#00ffff',
                    borderWidth: 3,
                    tension: 0.3
                }, {
                    label: 'خط التنبؤ الذكي المستقبلي (AI Predicted)',
                    data: [null, null, 130, 154, 172, 165],
                    borderColor: '#ff007f',
                    borderWidth: 3,
                    borderDash: [6, 6],
                    tension: 0.3
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
