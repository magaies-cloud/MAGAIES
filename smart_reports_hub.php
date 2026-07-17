<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - بوابة التقارير الذكية العالمية V2
// تم تصميمها وهندستها لتكون مستقلة تماماً لحماية نظامك القديم من الأخطاء
// ====================================================================

$selected_currency = isset($_GET['currency']) ? $_GET['currency'] : 'SDG';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة التقارير الذكية العالمية | Al-Sajana ERP</title>
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
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 40px 20px;
            background-image: radial-gradient(circle at 10% 20%, rgba(255, 0, 127, 0.1) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(0, 255, 255, 0.1) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
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
    </style>
</head>
<body>

    <div class="container">
        
        <!-- الهيدر الترحيبي -->
        <div class="welcome-header">
            <h1>مركز التقارير المحاسبية والذكاء المالي V2</h1>
            <p>تقارير جيل 2026 تم تدقيقها وتطويرها بواسطة خبراء الأنظمة والمحاسبة الدولية لتمنحك رؤية فورية لأسواق السجانة.</p>
        </div>

        <!-- أداة تبديل العملة الفورية لدعم الجنيه السوداني والدولار الأمريكي -->
        <div class="currency-selector">
            <a href="?currency=SDG" class="curr-btn <?php echo $selected_currency === 'SDG' ? 'active' : ''; ?>">جنيه سوداني (SDG)</a>
            <a href="?currency=USD" class="curr-btn <?php echo $selected_currency === 'USD' ? 'active' : ''; ?>">دولار أمريكي (USD)</a>
        </div>

        <!-- شبكة الـ 6 تقارير العالمية والحديثة -->
        <div class="reports-grid">

            <!-- 0. دفتر الأستاذ العام المطور والذكي V2 -->
            <a href="general_ledger_v2.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #3b82f6; --shadow-color: 59, 130, 246;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📖</div>
                <div class="card-title">دفتر الأستاذ العام المطور والذكي V2</div>
                <div class="card-desc">مراجعة فورية ومراقبة العمليات الحسابية والقيود اليومية تلقائياً للحد من الأخطاء البشرية.</div>
                <div class="ai-badge">● كاشف الأخطاء والتدقيق الآلي</div>
            </a>

            <!-- 1. كاشف التدفقات النقدية الفعلي -->
            <a href="cashflow_modern.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #00ff7f; --shadow-color: 0, 255, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">💸</div>
                <div class="card-title">كاشف التدفقات النقدية الفعلي (IAS 7)</div>
                <div class="card-desc">يفصل الكاش الداخل والخارج الحقيقي حسب معايير المحاسبة الدولية لـ (التشغيل، الاستثمار، التمويل).</div>
                <div class="ai-badge">● نظام تنبؤ السيولة الذكي</div>
            </a>

            <!-- 2. تحليل ربحية وهامش مساهمة البضائع -->
            <a href="profitability_analysis.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #ff007f; --shadow-color: 255, 0, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📊</div>
                <div class="card-title">محلل ربحية وهامش مساهمة الأصناف</div>
                <div class="card-desc">يحلل أرباحك الصافية الحقيقية لكل صنف (سيخ، أسمنت، صاج) ويحدد الصنف الأكثر ربحاً للمحل.</div>
                <div class="ai-badge">● كاشف الربحية الصافية</div>
            </a>

            <!-- 3. مؤشر نقطة التعادل الديناميكي -->
            <a href="breakeven_analysis.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #ffa500; --shadow-color: 255, 165, 0;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🎯</div>
                <div class="card-title">تحليل نقطة التعادل الفوري (Break-Even)</div>
                <div class="card-desc">يخبرك بدقة كم طن سيخ أو أسمنت يجب بيعه شهرياً لتغطية الإيجارات، الرواتب، واستهلاك طاقة الشمس.</div>
                <div class="ai-badge">● محاكي الأمان المالي</div>
            </a>

            <!-- 4. تقرير ركود البضائع ومعدل الدوران -->
            <a href="inventory_turnover.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">📦</div>
                <div class="card-title">محلل ركود البضائع ومعدل الدوران</div>
                <div class="card-desc">يكتشف البضائع الراكدة التي تجمد رأس مالك، وينبهك للأصناف سريعة الحركة لزيادة المخزون منها.</div>
                <div class="ai-badge">● ذكاء إدارة المخازن</div>
            </a>

            <!-- 5. التنبؤ بالسيولة والمبيعات القادمة -->
            <a href="sales_forecasting.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #9d4edd; --shadow-color: 157, 78, 221;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🔮</div>
                <div class="card-title">التنبؤ المالي الذكي (AI Forecasting)</div>
                <div class="card-desc">يتوقع حجم مبيعاتك واحتياجك من السيولة النقدية خلال الـ 30 يوماً القادمة بناءً على حركة السوق السابقة.</div>
                <div class="ai-badge">● نموذج ذكاء اصطناعي محلي</div>
            </a>

            <!-- 6. مؤشرات الصحة المالية والنسب المحاسبية -->
            <a href="financial_health.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #ffcc00; --shadow-color: 255, 204, 0;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🛡️</div>
                <div class="card-title">تقرير الصحة المالية ومعدلات الأمان</div>
                <div class="card-desc">يعطيك تصنيفاً ائتمانياً فورياً لشركتك عبر حساب نسب التداول، السيولة السريعة، ونسب الديون.</div>
                <div class="ai-badge">● فحص السلامة المالية المعتمد</div>
            </a>

            <!-- 7. محلل الأرصدة الشامل (خزائن، بنوك، موردين) -->
            <a href="balances_modern_hub.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🏦</div>
                <div class="card-title">محلل الأرصدة الشامل (خزائن، بنوك، موردين)</div>
                <div class="card-desc">عرض الأرصدة المجمعة وتفصيل النقدية الحالية بالبنوك المحلية والصندوق ومستحقات الموردين.</div>
                <div class="ai-badge">● كاشف السيولة الحية</div>
            </a>

            <!-- 8. مراقب سقوف الائتمان والمديونية العامه -->
            <a href="credit_limits_dashboard.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #ff0055; --shadow-color: 255, 0, 85;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">⚠️</div>
                <div class="card-title">مراقب سقوف الائتمان والمديونية العامه</div>
                <div class="card-desc">مراقبة سقوف المديونية للعملاء وتنبيهات فورية عند تجاوز الحد الائتماني المسموح به.</div>
                <div class="ai-badge">● كاشف تجاوز الحدود الائتمانية</div>
            </a>

            <!-- 9. كاشف ومقارن الشيكات والالتزامات الورقية -->
            <a href="checks_detector.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #00ff7f; --shadow-color: 0, 255, 127;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">💳</div>
                <div class="card-title">كاشف ومقارن الشيكات والالتزامات</div>
                <div class="card-desc">أداة الرصد والمقاصة الفورية للشيكات لمنع العجز الائتماني وتوقع تدفقات السيولة.</div>
                <div class="ai-badge">● كاشف الشيكات المقارن</div>
            </a>

            <!-- 10. مؤشر موثوقية وثبات أسعار الموردين -->
            <a href="supplier_reliability.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #a855f7; --shadow-color: 168, 85, 247;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🤝</div>
                <div class="card-title">مؤشر موثوقية وثبات أسعار الموردين</div>
                <div class="card-desc">تحليل أداء الموردين وحجم طلبيات التوريد وحساب تقلبات الأسعار لمعرفة المورد الأكثر استقراراً.</div>
                <div class="ai-badge">● مؤشر موثوقية الموردين</div>
            </a>

            <!-- 11. حساب مديونية الأرصدة الشامل -->
            <a href="combined_receivables.php?currency=BOTH&layout=premium" class="report-card" style="--accent-color: #00ffff; --shadow-color: 0, 255, 255;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">⚖️</div>
                <div class="card-title">حساب مديونية الأرصدة الشامل</div>
                <div class="card-desc">يجمع بين رصيد العميل/المورد في دفتر الأستاذ العام وقيمة الشيكات المعلقة معاً بالعملتين المحلية والأجنبية.</div>
                <div class="ai-badge">● مديونية الأستاذ + الشيكات</div>
            </a>

            <!-- 12. تحليلات الجيل القادم -->
            <a href="next_gen_analytics.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #10b981; --shadow-color: 16, 185, 129;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🔮</div>
                <div class="card-title">تحليلات الجيل القادم (Next-Gen)</div>
                <div class="card-desc">رسوم بيانية تفاعلية متقدمة ومخططات هجينة ومقاييس الأمان وحماية الأصول من التعثر.</div>
                <div class="ai-badge">● تحليلات ومخططات بيانية تفاعلية</div>
            </a>

            <!-- 13. بورصة الحديد والتنبؤ بالمخاطر -->
            <a href="steel_market_forecast.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #f59e0b; --shadow-color: 245, 158, 11;">
                <div class="live-badge"><span class="live-dot"></span>متصل لحظياً (Live)</div>
                <div class="card-icon">🏗️</div>
                <div class="card-title">بورصة الحديد والتنبؤ بالمخاطر</div>
                <div class="card-desc">منحنى تتبع بورصة المعادن العالمية LME وتوقعات أسعار حديد التسليح المستورد محلياً.</div>
                <div class="ai-badge">● بورصة الحديد وتوقعات الأسعار</div>
            </a>

            <!-- 14. تقرير مديونيات الأرصدة الاستراتيجي (Customer Exposure) -->
            <a href="customer_exposure.php?currency=<?php echo $selected_currency; ?>" class="report-card" style="--accent-color: #fbbf24; --shadow-color: 251, 191, 36;">
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

        </div>

    </div>

</body>
</html>
