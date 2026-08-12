<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - بوابة التقارير الذكية العالمية V2
// تم تصميمها وهندستها لتكون مستقلة تماماً لحماية نظامك القديم من الأخطاء
// ====================================================================

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$page_title = 'بوابة التقارير الذكية V2';
$header_theme_class = 'header-theme-reports';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <script src="js/lang.js"></script>
    <script>
    (function(){
        var l = localStorage.getItem('erp_lang') || 'ar';
        if(l==='en') {
            document.documentElement.setAttribute('dir','ltr');
            document.documentElement.setAttribute('lang','en');
        }
    })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة التقارير الذكية العالمية | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #090514;
            --panel-bg: rgba(20, 15, 38, 0.7);
            --border-neon: rgba(0, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }

        body {
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            background-color: var(--bg-dark);
            overflow-x: hidden;
        }

        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(0, 255, 255, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(255, 0, 127, 0.08) 0%, transparent 50%);
        }

        .container {
            max-width: 1400px; /* توسيع الحاوية قليلاً */
            margin: 0 auto;
            padding: 40px 40px; /* هوامش داخلية ممتازة من جميع الجهات */
        }

        /* هيدر ترحيبي فخم */
        .welcome-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .welcome-header h1 {
            font-size: 32px;
            font-weight: 900;
            margin: 0 0 10px 0;
            background: linear-gradient(135deg, #00ffff, #ff007f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(0, 255, 255, 0.2);
        }

        .welcome-header p {
            color: var(--text-muted);
            font-size: 15px;
            margin: 0;
        }


        /* شبكة الأيقونات والتقارير الذكية */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        /* كرت التقرير الفخم */
        .report-card {
            background: var(--panel-bg);
            border: 1.5px solid var(--border-neon);
            border-radius: 24px;
            padding: 30px;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            height: 240px;
        }

        /* تأثير التوهج عند التحويم بالماوس */
        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
            transition: all 0.5s;
        }

        .report-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-color);
            box-shadow: 0 15px 35px rgba(var(--shadow-color), 0.35);
        }

        .card-icon {
            font-size: 45px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px var(--accent-color));
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .card-desc {
            font-size: 12.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .ai-badge {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* تحكم بالعملة */
        .currency-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .curr-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            padding: 8px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            transition: all 0.3s;
        }

        .curr-btn.active {
            background: linear-gradient(135deg, #00ffff, #ff007f);
            color: #000;
            border: none;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
        }

        .live-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(0, 255, 127, 0.08);
            color: #00ff7f;
            border: 1px solid rgba(0, 255, 127, 0.15);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background-color: #00ff7f;
            border-radius: 50%;
            animation: pulse 1.8s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(0.85); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.85); opacity: 0.5; }
        }

        /* ===== مؤثرات حركية احترافية ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .welcome-header {
            animation: fadeIn 1s ease-out;
        }

        .currency-selector {
            animation: fadeIn 1.2s ease-out;
        }

        .report-card {
            opacity: 0;
            animation: fadeInUp 0.7s ease-out forwards;
        }

        /* تأخير متتابع لكل كرت */
        .report-card:nth-child(1) { animation-delay: 0.05s; }
        .report-card:nth-child(2) { animation-delay: 0.1s; }
        .report-card:nth-child(3) { animation-delay: 0.15s; }
        .report-card:nth-child(4) { animation-delay: 0.2s; }
        .report-card:nth-child(5) { animation-delay: 0.25s; }
        .report-card:nth-child(6) { animation-delay: 0.3s; }
        .report-card:nth-child(7) { animation-delay: 0.35s; }
        .report-card:nth-child(8) { animation-delay: 0.4s; }
        .report-card:nth-child(9) { animation-delay: 0.45s; }
        .report-card:nth-child(10) { animation-delay: 0.5s; }
        .report-card:nth-child(11) { animation-delay: 0.55s; }
        .report-card:nth-child(12) { animation-delay: 0.6s; }
        .report-card:nth-child(13) { animation-delay: 0.65s; }
        .report-card:nth-child(14) { animation-delay: 0.7s; }
        .report-card:nth-child(15) { animation-delay: 0.75s; }
        .report-card:nth-child(16) { animation-delay: 0.8s; }

        /* توهج الأيقونة عند التحويم */
        .report-card:hover .card-icon {
            animation: float 2s ease-in-out infinite;
        }

        /* بريق متحرك على الشارة */
        .report-card:hover .ai-badge {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite linear;
        }

        /* تأثير توهج نيون خلف الكرت */
        .report-card:hover::after {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px; bottom: -1px;
            border-radius: 24px;
            background: linear-gradient(135deg, var(--accent-color), transparent, var(--accent-color));
            opacity: 0.15;
            z-index: -1;
            filter: blur(15px);
        }

        /* زر العملة - انتقال سلس */
        .curr-btn {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .curr-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* جزيئات الخلفية المتحركة */
        .particles {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; overflow: hidden;
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.3;
            animation: particleFloat linear infinite;
            filter: blur(1px);
        }
        @keyframes particleFloat {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
.card-icon {
            font-size: 45px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px var(--accent-color));
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .card-desc {
            font-size: 12.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .ai-badge {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* تحكم بالعملة */
        .currency-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .curr-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            padding: 8px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            transition: all 0.3s;
        }

        .curr-btn.active {
            background: linear-gradient(135deg, #00ffff, #ff007f);
            color: #000;
            border: none;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
        }

        .live-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(0, 255, 127, 0.08);
            color: #00ff7f;
            border: 1px solid rgba(0, 255, 127, 0.15);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background-color: #00ff7f;
            border-radius: 50%;
            animation: pulse 1.8s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(0.85); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.85); opacity: 0.5; }
        }

        /* ===== مؤثرات حركية احترافية ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .welcome-header {
            animation: fadeIn 1s ease-out;
        }

        .currency-selector {
            animation: fadeIn 1.2s ease-out;
        }

        .report-card {
            opacity: 0;
            animation: fadeInUp 0.7s ease-out forwards;
        }

        /* تأخير متتابع لكل كرت */
        .report-card:nth-child(1) { animation-delay: 0.05s; }
        .report-card:nth-child(2) { animation-delay: 0.1s; }
        .report-card:nth-child(3) { animation-delay: 0.15s; }
        .report-card:nth-child(4) { animation-delay: 0.2s; }
        .report-card:nth-child(5) { animation-delay: 0.25s; }
        .report-card:nth-child(6) { animation-delay: 0.3s; }
        .report-card:nth-child(7) { animation-delay: 0.35s; }
        .report-card:nth-child(8) { animation-delay: 0.4s; }
        .report-card:nth-child(9) { animation-delay: 0.45s; }
        .report-card:nth-child(10) { animation-delay: 0.5s; }
        .report-card:nth-child(11) { animation-delay: 0.55s; }
        .report-card:nth-child(12) { animation-delay: 0.6s; }
        .report-card:nth-child(13) { animation-delay: 0.65s; }
        .report-card:nth-child(14) { animation-delay: 0.7s; }
        .report-card:nth-child(15) { animation-delay: 0.75s; }
        .report-card:nth-child(16) { animation-delay: 0.8s; }

        /* توهج الأيقونة عند التحويم */
        .report-card:hover .card-icon {
            animation: float 2s ease-in-out infinite;
        }

        /* بريق متحرك على الشارة */
        .report-card:hover .ai-badge {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite linear;
        }

        /* تأثير توهج نيون خلف الكرت */
        .report-card:hover::after {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px; bottom: -1px;
            border-radius: 24px;
            background: linear-gradient(135deg, var(--accent-color), transparent, var(--accent-color));
            opacity: 0.15;
            z-index: -1;
            filter: blur(15px);
        }

        /* زر العملة - انتقال سلس */
        .curr-btn {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .curr-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="container" style="position: relative; z-index: 1;">
        
        <?php $header_theme_class = 'header-theme-reports'; ?>
        <?php include 'includes/header.php'; ?>
        <br>
        
        <!-- الهيدر الترحيبي -->
        <div class="welcome-header">
            <h1>مركز التقارير المحاسبية والذكاء المالي V2</h1>
            <p>تقارير جيل 2026 تم تدقيقها وتطويرها بواسطة خبراء الأنظمة والمحاسبة الدولية لتمنحك رؤية فورية.</p>
        </div>



        <!-- شبكة الـ 6 تقارير العالمية والحديثة -->
        <div class="reports-grid">

            <!-- 0. دفتر الأستاذ العام المطور والذكي V2 -->
            <a href="general_ledger_v2.php" class="report-card" style="--accent-color: #3b82f6; --shadow-color: 59, 130, 246;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📖</div>
                <div class="card-title">دفتر الأستاذ العام المطور والذكي V2</div>
                <div class="card-desc">مراجعة فورية ومراقبة العمليات الحسابية والقيود اليومية تلقائياً للحد من الأخطاء البشرية.</div>
                <div class="ai-badge">● كاشف الأخطاء والتدقيق الآلي</div>
            </a>

            <!-- 1. كاشف التدفقات النقدية الفعلي -->
            <a href="cashflow_modern.php" class="report-card" style="--accent-color: #00ff7f; --shadow-color: 0, 255, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">💸</div>
                <div class="card-title">كاشف التدفقات النقدية الفعلي (IAS 7)</div>
                <div class="card-desc">يفصل الكاش الداخل والخارج الحقيقي حسب معايير المحاسبة الدولية لـ (التشغيل، الاستثمار، التمويل).</div>
                <div class="ai-badge">● نظام تنبؤ السيولة الذكي</div>
            </a>

            <!-- 2. تحليل ربحية وهامش مساهمة البضائع -->
            <a href="profitability_analysis.php" class="report-card" style="--accent-color: #ff007f; --shadow-color: 255, 0, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📊</div>
                <div class="card-title">محلل ربحية وهامش مساهمة الأصناف</div>
                <div class="card-desc">يحلل أرباحك الصافية الحقيقية لكل صنف (سيخ، أسمنت، صاج) ويحدد الصنف الأكثر ربحاً للمحل.</div>
                <div class="ai-badge">● كاشف الربحية الصافية</div>
            </a>

            <!-- 3. مؤشر نقطة التعادل الديناميكي -->
            <a href="breakeven_analysis.php" class="report-card" style="--accent-color: #ffa500; --shadow-color: 255, 165, 0;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🎯</div>
                <div class="card-title">تحليل نقطة التعادل الفوري (Break-Even)</div>
                <div class="card-desc">يخبرك بدقة كم طن سيخ أو أسمنت يجب بيعه شهرياً لتغطية الإيجارات، الرواتب، واستهلاك طاقة الشمس.</div>
                <div class="ai-badge">● محاكي الأمان المالي</div>
            </a>

            <!-- 4. تقرير ركود البضائع ومعدل الدوران -->
            <a href="inventory_turnover.php" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📦</div>
                <div class="card-title">محلل ركود البضائع ومعدل الدوران</div>
                <div class="card-desc">يكتشف البضائع الراكدة التي تجمد رأس مالك، وينبهك للأصناف سريعة الحركة لزيادة المخزون منها.</div>
                <div class="ai-badge">● ذكاء إدارة المخازن</div>
            </a>

            <!-- 5. التنبؤ بالسيولة والمبيعات القادمة -->
            <a href="sales_forecasting.php" class="report-card" style="--accent-color: #9d4edd; --shadow-color: 157, 78, 221;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🔮</div>
                <div class="card-title">التنبؤ المالي الذكي (AI Forecasting)</div>
                <div class="card-desc">يتوقع حجم مبيعاتك واحتياجك من السيولة النقدية خلال الـ 30 يوماً القادمة بناءً على حركة السوق السابقة.</div>
                <div class="ai-badge">● نموذج ذكاء اصطناعي محلي</div>
            </a>

            <!-- 6. مؤشرات الصحة المالية والنسب المحاسبية -->
            <a href="financial_health.php" class="report-card" style="--accent-color: #ffcc00; --shadow-color: 255, 204, 0;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🛡️</div>
                <div class="card-title">تقرير الصحة المالية ومعدلات الأمان</div>
                <div class="card-desc">يعطيك تصنيفاً ائتمانياً فورياً لشركتك عبر حساب نسب التداول، السيولة السريعة، ونسب الديون.</div>
                <div class="ai-badge">● فحص السلامة المالية المعتمد</div>
            </a>

            <!-- 7. محلل الأرصدة الشامل (خزائن، بنوك، موردين) -->
            <a href="balances_modern_hub.php" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🏦</div>
                <div class="card-title">محلل الأرصدة الشامل (خزائن، بنوك، موردين)</div>
                <div class="card-desc">عرض الأرصدة المجمعة وتفصيل النقدية الحالية بالبنوك المحلية والصندوق ومستحقات الموردين.</div>
                <div class="ai-badge">● كاشف السيولة الحية</div>
            </a>

            <!-- 8. مراقب سقوف الائتمان والمديونية العامه -->
            <a href="credit_limits_dashboard.php" class="report-card" style="--accent-color: #ff0055; --shadow-color: 255, 0, 85;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">⚠️</div>
                <div class="card-title">مراقب سقوف الائتمان والمديونية العامه</div>
                <div class="card-desc">مراقبة سقوف المديونية للعملاء وتنبيهات فورية عند تجاوز الحد الائتماني المسموح به.</div>
                <div class="ai-badge">● كاشف تجاوز الحدود الائتمانية</div>
            </a>

            <!-- 9. كاشف ومقارن الشيكات والالتزامات الورقية -->
            <a href="checks_detector.php" class="report-card" style="--accent-color: #00ff7f; --shadow-color: 0, 255, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">💳</div>
                <div class="card-title">كاشف ومقارن الشيكات والالتزامات</div>
                <div class="card-desc">أداة الرصد والمقاصة الفورية للشيكات لمنع العجز الائتماني وتوقع تدفقات السيولة.</div>
                <div class="ai-badge">● كاشف الشيكات المقارن</div>
            </a>

            <!-- 10. مؤشر موثوقية وثبات أسعار الموردين -->
            <a href="supplier_reliability.php" class="report-card" style="--accent-color: #a855f7; --shadow-color: 168, 85, 247;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🤝</div>
                <div class="card-title">مؤشر موثوقية وثبات أسعار الموردين</div>
                <div class="card-desc">تحليل أداء الموردين وحجم طلبيات التوريد وحساب تقلبات الأسعار لمعرفة المورد الأكثر استقراراً.</div>
                <div class="ai-badge">● مؤشر موثوقية الموردين</div>
            </a>

            <!-- 11. حساب مديونية الأرصدة الشامل -->
            <a href="combined_receivables.php?layout=premium" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">⚖️</div>
                <div class="card-title">حساب مديونية الأرصدة الشامل</div>
                <div class="card-desc">يجمع بين رصيد العميل/المورد في دفتر الأستاذ العام وقيمة الشيكات المعلقة معاً بالعملتين المحلية والأجنبية.</div>
                <div class="ai-badge">● مديونية الأستاذ + الشيكات</div>
            </a>

            <!-- 12. تحليلات الجيل القادم -->
            <a href="next_gen_analytics.php" class="report-card" style="--accent-color: #10b981; --shadow-color: 16, 185, 129;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🔮</div>
                <div class="card-title">تحليلات الجيل القادم (Next-Gen)</div>
                <div class="card-desc">رسوم بيانية تفاعلية متقدمة ومخططات هجينة ومقاييس الأمان وحماية الأصول من التعثر.</div>
                <div class="ai-badge">● تحليلات ومخططات بيانية تفاعلية</div>
            </a>

            <!-- 13. بورصة الحديد والتنبؤ بالمخاطر -->
            <a href="steel_market_forecast.php" class="report-card" style="--accent-color: #f59e0b; --shadow-color: 245, 158, 11;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🏗️</div>
                <div class="card-title">بورصة الحديد والتنبؤ بالمخاطر</div>
                <div class="card-desc">منحنى تتبع بورصة المعادن العالمية LME وتوقعات أسعار حديد التسليح المستورد محلياً.</div>
                <div class="ai-badge">● بورصة الحديد وتوقعات الأسعار</div>
            </a>

            <!-- 14. تقرير مديونيات الأرصدة الاستراتيجي (Customer Exposure) -->
            <a href="customer_exposure.php" class="report-card" style="--accent-color: #fbbf24; --shadow-color: 251, 191, 36;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🎚️</div>
                <div class="card-title">تقرير مديونيات الأرصدة الاستراتيجي</div>
                <div class="card-desc">تحليل حجم المخاطرة الكلية لمديونيات العملاء (الأستاذ + الشيكات) وتقييم حالة الأمان لكل عميل.</div>
                <div class="ai-badge">● تقرير التعرض المالي للعملاء</div>
            </a>

            <!-- 15. مركز تخصيص وإدارة التنبيهات الذكية (Alerts Manager) -->
            <a href="alerts_manager.php" class="report-card" style="--accent-color: #ef4444; --shadow-color: 239, 68, 68;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🔔</div>
                <div class="card-title">إدارة وتخصيص التنبيهات الذكية</div>
                <div class="card-desc">تحكم كامل في قواعد الأمان والإنذار المبكر وضبط عتبات ومستويات التحذير المالي الحرج.</div>
                <div class="ai-badge">● محرك التحكم في التنبيهات</div>
            </a>

            <!-- 16. معدل دوران الزبائن -->
            <a href="customer_churn.php" class="report-card" style="--accent-color: #f97316; --shadow-color: 249, 115, 22;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🏃‍♂️</div>
                <div class="card-title">معدل دوران وانقطاع الزبائن</div>
                <div class="card-desc">اكتشاف العملاء المنقطعين وتحديد درجة خطورة فقدانهم بناءً على آخر نشاط بيعي.</div>
                <div class="ai-badge">● محلل خطر الانقطاع</div>
            </a>

            <!-- 17. استهلاك الأصناف الموسمية -->
            <a href="seasonal_trends.php" class="report-card" style="--accent-color: #14b8a6; --shadow-color: 20, 184, 166;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🌦️</div>
                <div class="card-title">استهلاك الأصناف الموسمية</div>
                <div class="card-desc">تحليل سحب الحديد والأسمنت وتأثيره على التدفقات النقدية الداخلة والخارجة حسب أشهر السنة.</div>
                <div class="ai-badge">● متتبع المواسم</div>
            </a>

            <!-- 18. تقرير انحراف السعر -->
            <a href="price_variance.php" class="report-card" style="--accent-color: #f59e0b; --shadow-color: 245, 158, 11;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📉</div>
                <div class="card-title">احترافية التسعير وانحراف السعر</div>
                <div class="card-desc">مراقبة الفواتير والخصومات اليدوية وفروقات الأسعار لنفس الأصناف لمعرفة أعلى وأقل سعر.</div>
                <div class="ai-badge">● مراقب هوامش البيع</div>
            </a>

            <!-- 19. مصفوفة تكرار الزيارة والولاء -->
            <a href="customer_engagement.php" class="report-card" style="--accent-color: #06b6d4; --shadow-color: 6, 182, 212;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">💎</div>
                <div class="card-title">مصفوفة تكرار الزيارة والولاء</div>
                <div class="card-desc">تصنيف الزبائن (VIP، مستمر، عابر) بناءً على حجم التعامل وعدد الزيارات لتقديم خصومات ذكية.</div>
                <div class="ai-badge">● مصنف ولاء العملاء</div>
            </a>




        </div>

    </div>

    <script>
    // === إنشاء جزيئات الخلفية المتحركة بخصائص متطورة ===
    (function() {
        const container = document.getElementById('particles');
        // مجموعة ألوان غنية وموسعة للوهج
        const colors = [
            '#00ffff', '#ff007f', '#00ff7f', '#ffa500', '#9d4edd', 
            '#3b82f6', '#ec4899', '#fcd34d', '#14b8a6', '#8b5cf6', '#ef4444'
        ];
        
        // زيادة عدد الجزيئات لتأثير أكثف
        for (let i = 0; i < 40; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            
            // توزيع عشوائي أفقياً
            p.style.left = Math.random() * 100 + '%';
            
            // اختيار لون عشوائي
            const color = colors[Math.floor(Math.random() * colors.length)];
            p.style.backgroundColor = color;
            
            // تأثير التوهج اللوني (Neon Glow)
            p.style.boxShadow = '0 0 12px ' + color + ', 0 0 20px ' + color;
            
            // سرعة متفاوته لتأثير واقعي (من 10 إلى 30 ثانية للنزول)
            p.style.animationDuration = (10 + Math.random() * 20) + 's';
            
            // تأخير عشوائي كي لا تنزل كلها في وقت واحد
            p.style.animationDelay = (Math.random() * 15) + 's';
            
            // حجم أكبر للجزيئات (بين 4 بكسل و 12 بكسل)
            const size = (4 + Math.random() * 8) + 'px';
            p.style.width = size;
            p.style.height = size;
            
            container.appendChild(p);
        }
    })();
    </script>

</body>
</html>
