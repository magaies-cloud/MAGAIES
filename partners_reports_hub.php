<?php
// ====================================================================
// بوابة تقارير العملاء والموردين الشاملة V2 - Al-Sajana ERP 2026
// تم تصميمها وهندستها بنفس تصميم وألوان وأيقونات بوابة التقارير الذكية V2
// ====================================================================

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$page_title = 'بوابة تقارير العملاء والموردين V2';
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
    <title>مركز تقارير العملاء والموردين V2 | Al-Sajana ERP</title>
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="js/localforage.min.js"></script>
    <script defer src="js/app.js?v=20261016_v28"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #090514;
            --panel-bg: rgba(20, 15, 38, 0.7);
            --border-neon: rgba(0, 255, 255, 0.15);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }

        :root.light-theme {
            --bg-dark: #f1f5f9;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 40px;
        }

        /* هيدر ترحيبي فخم بستايل V2 */
        .welcome-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeIn 1s ease-out;
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

        :root.light-theme .welcome-header h1 {
            background: linear-gradient(135deg, #0284c7, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-header p {
            color: var(--text-muted);
            font-size: 15px;
            margin: 0;
            font-weight: 600;
        }

        /* شبكة الأيقونات والتقارير الذكية V2 */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        /* كرت التقرير الفخم V2 */
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
            animation: fadeInUp 0.7s ease-out forwards;
        }

        :root.light-theme .report-card {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important;
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

        :root.light-theme .report-card:hover {
            border-color: var(--accent-color) !important;
            box-shadow: 0 15px 35px rgba(var(--shadow-color), 0.25) !important;
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

        :root.light-theme .card-title {
            color: #000000 !important;
            font-weight: 900 !important;
        }

        .card-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        :root.light-theme .card-desc {
            color: #334155 !important;
            font-weight: 600 !important;
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

        :root.light-theme .ai-badge {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border: 2px solid #000000 !important;
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

        :root.light-theme .live-badge {
            background: rgba(5, 150, 105, 0.1) !important;
            color: #059669 !important;
            border: 1.5px solid #059669 !important;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            background-color: #00ff7f;
            border-radius: 50%;
            animation: pulse 1.8s infinite ease-in-out;
        }

        /* تأخير متتابع لكل كرت */
        .report-card:nth-child(1) { animation-delay: 0.05s; }
        .report-card:nth-child(2) { animation-delay: 0.1s; }
        .report-card:nth-child(3) { animation-delay: 0.15s; }
        .report-card:nth-child(4) { animation-delay: 0.2s; }
        .report-card:nth-child(5) { animation-delay: 0.25s; }
        .report-card:nth-child(6) { animation-delay: 0.3s; }

        /* توهج الأيقونة عند التحويم */
        .report-card:hover .card-icon {
            animation: float 2s ease-in-out infinite;
        }

        /* بريق متحرك على الشارة */
        .report-card:hover .ai-badge {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0) 100%);
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

        @keyframes pulse {
            0% { transform: scale(0.85); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.85); opacity: 0.5; }
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
        
        <?php include 'includes/header.php'; ?>
        <br>
        
        <!-- الهيدر الترحيبي V2 -->
        <div class="welcome-header">
            <a href="analytics.php" onclick="window.location.href='analytics.php'; return false;" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0, 255, 255, 0.1); border: 1.5px solid #00ffff; color: #00ffff; padding: 8px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; margin-bottom: 20px; transition: all 0.3s; box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);" onmouseover="this.style.background='#00ffff'; this.style.color='#000';" onmouseout="this.style.background='rgba(0, 255, 255, 0.1)'; this.style.color='#00ffff';">
                <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع للشاشة السابقة (التقارير الرئيسية)
            </a>
            <h1>مركز تقارير العملاء والموردين الشامل V2</h1>
            <p>منظومة تقارير الشركاء والعملاء تم تطويرها بأسلوب V2 العالمي لتمنحك رؤية فورية ودقيقة لكل الحسابات.</p>
        </div>

        <!-- شبكة الـ 6 تقارير الخاصة بالعملاء والموردين V2 -->
        <div class="reports-grid">

            <!-- 1. بطاقة الموقف المالي للعملاء -->
            <a href="customer-portfolio.php" class="report-card" style="--accent-color: #f59e0b; --shadow-color: 245, 158, 11;">
                <div class="live-badge"><span class="live-dot"></span>مفعل (Live)</div>
                <div class="card-icon">💼</div>
                <div class="card-title">بطاقة الموقف المالي للعملاء</div>
                <div class="card-desc">لوحة تحليل شاملة لرصد مواقف العملاء وأداء التحصيل والمديونيات مع مؤشرات الأداء الرئيسية (KPI).</div>
                <div class="ai-badge">● مؤشر أداء التحصيل السريع</div>
            </a>





            <!-- 4. أعمار المديونية (Aging) -->
            <a href="aging-report.php" class="report-card" style="--accent-color: #ff007f; --shadow-color: 255, 0, 127;">
                <div class="live-badge"><span class="live-dot"></span>مفعل (Live)</div>
                <div class="card-icon">⌛</div>
                <div class="card-title">أعمار المديونية (Aging)</div>
                <div class="card-desc">تحليل المتأخرات وفترات الاستحقاق لمراقبة الديون وتحديد الديون المشكوك في تحصيلها.</div>
                <div class="ai-badge">● كاشف الديون المشكوك في تحصيلها</div>
            </a>

            <!-- 5. التقرير الصحي المالي ومعدلات الأمان -->
            <a href="financial_health.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #00ff7f; --shadow-color: 0, 255, 127;">
                <div class="live-badge"><span class="live-dot"></span>مفعل (Live)</div>
                <div class="card-icon">🛡️</div>
                <div class="card-title">التقرير الصحي المالي ومعدلات الأمان</div>
                <div class="card-desc">مؤشرات الأمان المالي، فترات التحصيل (DSO)، وتحليل سيولة السوق لمواجهة المخاطر المحتملة.</div>
                <div class="ai-badge">● تحليل السيولة ومخاطر السوق</div>
            </a>

            <!-- 6. بطاقة العملاء الماليين وتحليل التعرض -->
            <a href="customer_exposure.php" class="report-card" style="--accent-color: #6366f1; --shadow-color: 99, 102, 241;">
                <div class="live-badge"><span class="live-dot"></span>مفعل (Live)</div>
                <div class="card-icon">📊</div>
                <div class="card-title">بطاقة العملاء الماليين وتحليل التعرض</div>
                <div class="card-desc">تقرير مالي متكامل يوضح حدود الائتمان ومعدل التعرض المالي ونسبة التركز للعملاء الماليين.</div>
                <div class="ai-badge">● تحليل عمق الائتمان ومعدل التعرض</div>
            </a>

        </div>

    </div>

</body>
</html>
