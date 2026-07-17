<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - محلل ركود البضائع ومعدلات الدوران
// ====================================================================
$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محلل ركود البضائع | Al-Sajana ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #04030a;
            --panel-bg: rgba(15, 10, 28, 0.75);
            --border-neon: rgba(0, 255, 255, 0.2);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Cairo', sans-serif; padding: 30px; background-image: radial-gradient(circle at bottom right, rgba(0, 255, 255, 0.08), transparent 40%); }
        .glass-panel { background: var(--panel-bg); backdrop-filter: blur(15px); border: 1px solid var(--border-neon); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: transparent; color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 8px 16px; border-radius: 12px; text-decoration: none; font-size: 13px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .status-box { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 20px; text-align: center; }
        .chart-container { position: relative; height: 320px; background: rgba(0,0,0,0.2); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body>
    <div class="glass-panel">
        <div class="header">
            <div>
                <h1 style="margin:0; background: linear-gradient(135deg, #00ffff, #7928ca); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight:900;">محلل ركود البضائع ومعدل دوران المخزون</h1>
                <p style="color: var(--text-muted); margin: 5px 0 0 0;">كشف فوري للأصناف سريعة الحركة والبضائع الراكدة التي تجمد السيولة</p>
            </div>
            <a href="smart_reports_hub.php?currency=<?php echo $selected_currency; ?>" class="btn-back">⬅️ العودة للبوابة</a>
        </div>

        <div class="grid-3">
            <div class="status-box" style="border-color: rgba(0,255,127,0.2);">
                <span style="color: var(--text-muted);">أصناف سريعة الحركة (Fast-Moving)</span>
                <h3 style="color: #00ff7f; margin:10px 0;">أسمنت عطبرة - سيخ 4 لينيا</h3>
                <span style="font-size:11px; color:#00ff7f;">● معدل دوران ممتاز: 18 مرة سنوياً</span>
            </div>
            <div class="status-box" style="border-color: rgba(255,165,0,0.2);">
                <span style="color: var(--text-muted);">أصناف متوسطة الحركة</span>
                <h3 style="color: #ffa500; margin:10px 0;">طن سلك اللحام - صاج مجلفن</h3>
                <span style="font-size:11px; color:#ffa500;">● معدل دوران متوسط: 6 مرات سنوياً</span>
            </div>
            <div class="status-box" style="border-color: rgba(255,0,85,0.2);">
                <span style="color: var(--text-muted);">أصناف راكدة (بحاجة لتخفيض السعر)</span>
                <h3 style="color: #ff0055; margin:10px 0;">صاج حديد أسود سميك</h3>
                <span style="font-size:11px; color:#ff0055;">⚠️ لم يتم بيع أي قطعة منذ 90 يوماً</span>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="inventoryChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['أسمنت عطبرة', 'سيخ 4 لينيا الأسعد', 'سلك لحام الأسعد', 'صاج مجلفن', 'صاج أسود سميك'],
                datasets: [{
                    label: 'معدل دوران الصنف (عدد المرات بالسنوات)',
                    data: [18, 12, 6, 4, 1],
                    backgroundColor: [
                        'rgba(0, 255, 127, 0.6)',
                        'rgba(0, 255, 255, 0.6)',
                        'rgba(255, 165, 0, 0.6)',
                        'rgba(157, 78, 221, 0.6)',
                        'rgba(255, 0, 85, 0.6)'
                    ],
                    borderColor: [
                        '#00ff7f',
                        '#00ffff',
                        '#ffa500',
                        '#9d4edd',
                        '#ff0055'
                    ],
                    borderWidth: 1.5
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
