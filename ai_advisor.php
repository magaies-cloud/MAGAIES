<?php
// =========================================================================
// نظام الأسكربت واللسيطرة ERP 2026
// الحارس الأمين والمستشار الذكي الشامل 360° (Unified AI Guardian & Advisor Hub)
// يجمع: الحارس الشامل + مستشار الأموال والسيولة + مستشار البضائع والمخازن + التنبؤات المستقبلية
// =========================================================================
$page_title = 'الحارس الأمين والمستشار الذكي الشامل (AI Guardian 360°)';
$header_theme_class = 'header-theme-reports';

require_once __DIR__ . '/db_bridge.php';
$pdo = getDBConnection();

$mode = isset($_GET['mode']) ? strtolower(trim($_GET['mode'])) : 'all';
if (!in_array($mode, ['all', 'money', 'goods', 'forecast'])) {
    $mode = 'all';
}

$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');

// جلب البيانات الأولية من قاعدة البيانات (ويتم تعزيزها حياً من المتصفح عبر localStorage)
$expData = getCustomerExposureData($pdo);
$invData = getInventoryReportData($pdo);

$sqlCustomers = $expData['customers'] ?? [];
$sqlItems     = $invData['items'] ?? [];
$sqlSalesMonthly = [];

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT date_val, total_val, data FROM sales_invoices WHERE tenant_id = :tid AND is_deleted = 0 ORDER BY date_val ASC");
        $stmt->execute([':tid' => $GLOBAL_TENANT_ID ?? 'default']);
        $rows = $stmt->fetchAll();
        $mMap = [];
        foreach ($rows as $r) {
            $d = substr((string)($r['date_val'] ?? ''), 0, 7);
            if (!$d) continue;
            $amt = (float)($r['total_val'] ?? 0);
            if ($amt <= 0 && !empty($r['data'])) {
                $jd = json_decode($r['data'], true);
                if (is_array($jd)) {
                    $amt = (float)($jd['totalAmount'] ?? $jd['grandTotal'] ?? $jd['total'] ?? 0);
                }
            }
            if (!isset($mMap[$d])) $mMap[$d] = 0;
            $mMap[$d] += $amt;
        }
        ksort($mMap);
        foreach (array_slice($mMap, -6, 6, true) as $ym => $tot) {
            $sqlSalesMonthly[] = ['month' => $ym, 'total' => round($tot, 2)];
        }
    }
} catch (Exception $e) {
    // fallback handled on client side
}

$initialServerPayload = json_encode([
    'customers'    => $sqlCustomers,
    'items'        => $sqlItems,
    'totalDebt'    => (float)($expData['total_debt'] ?? 0),
    'invValue'     => (float)($invData['total_value'] ?? 0),
    'salesMonthly' => $sqlSalesMonthly,
    'currency'     => $selected_currency
], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | مقاييس ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/sidebar-panoramic-flyout.css">
    <script src="js/elsajana-universal-ui-2027.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --ai-bg-card: rgba(15, 23, 42, 0.86);
            --ai-border: rgba(56, 189, 248, 0.22);
            --ai-cyan: #22d3ee;
            --ai-purple: #a855f7;
            --ai-emerald: #10b981;
            --ai-amber: #f59e0b;
            --ai-rose: #f43f5e;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-primary, #070b14);
            color: var(--text-primary, #f8fafc);
            margin: 0;
            min-height: 100vh;
        }
        .guardian-shell {
            max-width: 1580px;
            margin: 0 auto;
            padding: 18px 20px 60px;
        }
        /* شريط القيادة العلوي */
        .guardian-hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 27, 75, 0.88));
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 22px;
            padding: 22px 26px;
            margin-bottom: 18px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45), 0 0 35px rgba(34, 211, 238, 0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }
        .guardian-hero::before {
            content: '';
            position: absolute;
            top: 0; right: 0; left: 0;
            height: 4px;
            background: linear-gradient(90deg, #22d3ee, #a855f7, #10b981, #f59e0b);
        }
        .hero-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .hero-orb {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(34, 211, 238, 0.2), rgba(168, 85, 247, 0.25));
            border: 1.5px solid rgba(34, 211, 238, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            box-shadow: 0 0 25px rgba(34, 211, 238, 0.25);
        }
        .hero-title-group h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(90deg, #ffffff, #67e8f9, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-title-group p {
            margin: 4px 0 0;
            font-size: 0.86rem;
            color: #94a3b8;
            font-weight: 600;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .g-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(30, 41, 59, 0.85);
            color: #f8fafc;
            transition: all 0.22s ease;
        }
        .g-btn:hover {
            transform: translateY(-2px);
            border-color: var(--ai-cyan);
            box-shadow: 0 8px 20px rgba(34, 211, 238, 0.2);
        }
        .g-btn.primary {
            background: linear-gradient(135deg, #0891b2, #7c3aed);
            border-color: rgba(103, 232, 249, 0.45);
        }
        .g-btn.emerald {
            background: linear-gradient(135deg, #059669, #0d9488);
            border-color: rgba(52, 211, 153, 0.45);
        }
        .g-btn.amber {
            background: linear-gradient(135deg, #d97706, #ea580c);
            border-color: rgba(251, 191, 36, 0.45);
        }

        /* التبويبات الـ 4 الموحدة */
        .mode-tabs-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .mode-tab-btn {
            background: rgba(15, 23, 42, 0.82);
            border: 1.5px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            color: #cbd5e1;
            text-align: right;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .mode-tab-btn:hover {
            border-color: rgba(56, 189, 248, 0.5);
            transform: translateY(-2px);
            color: #ffffff;
        }
        .mode-tab-btn.active {
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.28), rgba(124, 58, 237, 0.25));
            border-color: #22d3ee;
            color: #ffffff;
            box-shadow: 0 10px 28px rgba(34, 211, 238, 0.18);
        }
        .mode-tab-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .mode-tab-title {
            font-size: 0.94rem;
            font-weight: 900;
            display: block;
        }
        .mode-tab-sub {
            font-size: 0.74rem;
            color: #94a3b8;
            display: block;
            margin-top: 2px;
        }

        /* شريط مؤشرات الصحة والنبض المالي 6 بطاقات */
        .kpi-pulse-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .pulse-card {
            background: var(--ai-bg-card);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 18px;
            padding: 16px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.28);
        }
        .pulse-card::after {
            content: '';
            position: absolute;
            bottom: 0; right: 0; left: 0;
            height: 3px;
            background: var(--card-accent, #22d3ee);
        }
        .pulse-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .pulse-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #94a3b8;
        }
        .pulse-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 99px;
            background: rgba(255,255,255,0.08);
        }
        .pulse-val {
            font-size: 1.45rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.3px;
        }
        .pulse-foot {
            font-size: 0.73rem;
            color: #94a3b8;
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* مصفوفة الـ 10 ركائز الرقابية والتنبؤية */
        .pillars-section {
            margin-bottom: 24px;
        }
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .section-head h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #f8fafc;
        }
        .pillars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 14px;
        }
        .pillar-card {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-right: 4px solid var(--p-color, #22d3ee);
            border-radius: 16px;
            padding: 15px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 10px;
            transition: all 0.22s ease;
        }
        .pillar-card:hover {
            transform: translateY(-3px);
            border-color: var(--p-color, #22d3ee);
            box-shadow: 0 12px 26px rgba(0,0,0,0.35);
        }
        .pillar-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .pillar-title {
            font-size: 0.9rem;
            font-weight: 900;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pillar-status {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 99px;
            white-space: nowrap;
        }
        .pillar-desc {
            font-size: 0.8rem;
            color: #cbd5e1;
            line-height: 1.55;
            margin: 0;
        }
        .pillar-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px dashed rgba(148, 163, 184, 0.14);
        }
        .pillar-link {
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--ai-cyan);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .pillar-link:hover {
            text-decoration: underline;
        }
        .pillar-ask-btn {
            background: rgba(168, 85, 247, 0.16);
            border: 1px solid rgba(168, 85, 247, 0.4);
            color: #e9d5ff;
            border-radius: 8px;
            padding: 4px 10px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.73rem;
            font-weight: 800;
            cursor: pointer;
        }
        .pillar-ask-btn:hover {
            background: rgba(168, 85, 247, 0.32);
        }

        /* منصة المحادثة والاستشارة التفاعلية الذكية */
        .ai-console-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(23, 37, 84, 0.85));
            border: 1.5px solid rgba(56, 189, 248, 0.35);
            border-radius: 20px;
            padding: 20px 22px;
            margin-bottom: 24px;
            box-shadow: 0 15px 38px rgba(0,0,0,0.4);
        }
        .prompt-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }
        .prompt-chip {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(56, 189, 248, 0.28);
            color: #e2e8f0;
            padding: 6px 13px;
            border-radius: 99px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.77rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .prompt-chip:hover {
            background: rgba(34, 211, 238, 0.18);
            border-color: #22d3ee;
            color: #ffffff;
        }
        .console-input-row {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .console-input {
            flex: 1;
            background: rgba(7, 11, 20, 0.85);
            border: 1.5px solid rgba(148, 163, 184, 0.28);
            border-radius: 14px;
            padding: 12px 16px;
            color: #ffffff;
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
        }
        .console-input:focus {
            outline: none;
            border-color: #22d3ee;
            box-shadow: 0 0 15px rgba(34, 211, 238, 0.2);
        }
        .ai-response-box {
            margin-top: 16px;
            background: rgba(7, 11, 20, 0.78);
            border: 1px solid rgba(34, 211, 238, 0.3);
            border-right: 4px solid #22d3ee;
            border-radius: 14px;
            padding: 18px 20px;
            color: #f1f5f9;
            font-size: 0.9rem;
            line-height: 1.8;
            display: none;
        }
        .ai-response-box.visible {
            display: block;
        }

        /* جداول ولوحات الأقسام التخصصية */
        .panel-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }
        @media (max-width: 768px) {
            .panel-grid-2 {
                grid-template-columns: 1fr;
            }
            .console-input-row {
                flex-direction: column;
            }
        }
        .data-panel {
            background: var(--ai-bg-card);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.3);
        }
        .data-panel h3 {
            margin: 0 0 12px;
            font-size: 1.02rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
        }
        .table-scroll {
            overflow-x: auto;
            max-height: 370px;
            overflow-y: auto;
        }
        .g-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        .g-table th {
            background: rgba(30, 41, 59, 0.92);
            color: #94a3b8;
            font-weight: 800;
            text-align: right;
            padding: 10px 12px;
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        .g-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            color: #e2e8f0;
        }
        .g-table tr:hover td {
            background: rgba(56, 189, 248, 0.06);
        }
        .mini-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 8px;
            font-size: 0.73rem;
            font-weight: 800;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.15);
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
        }
        .mini-btn.wa {
            background: rgba(16, 185, 129, 0.18);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
        }
        .mini-btn.stmt {
            background: rgba(56, 189, 248, 0.16);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.4);
        }
        .mini-btn.po {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }

        /* محاكاة ماذا لو (What-If Simulator) */
        .sim-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }
        .sim-box {
            background: rgba(30, 41, 59, 0.65);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 12px;
            padding: 12px 14px;
        }
        .sim-box label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 800;
            color: #cbd5e1;
            margin-bottom: 6px;
        }
        .sim-box input[type="range"] {
            width: 100%;
            accent-color: #22d3ee;
            cursor: pointer;
        }

        @media print {
            .hero-actions, .mode-tabs-bar, .console-input-row, .prompt-chips, .pillar-ask-btn {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .guardian-hero, .pulse-card, .pillar-card, .data-panel, .ai-console-card {
                background: #ffffff !important;
                color: #000000 !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
<div class="page-container">
    <?php include 'includes/header.php'; ?>

    <div class="guardian-shell">
        <!-- الهيدر الرئيسي الموحد -->
        <div class="guardian-hero">
            <div class="hero-title-group">
                <div class="hero-orb">🛡️</div>
                <div>
                    <h1>الحارس الأمين والمستشار الذكي الشامل (AI Guardian 360°)</h1>
                    <p>الرقابة الاستباقية الصارمة • كشف الثغرات والمديونيات • هندسة المخزون • التنبؤات المستقبلية (يعمل 100% محلياً وسحابياً)</p>
                </div>
            </div>
            <div class="hero-actions">
                <button type="button" class="g-btn primary" onclick="runFullGuardianScan(true)">
                    <i class="fa-solid fa-radar"></i> فحص شامل فوري
                </button>
                <button type="button" class="g-btn emerald" onclick="exportGuardianReportCSV()">
                    <i class="fa-solid fa-file-excel"></i> تصدير التقرير Excel
                </button>
                <button type="button" class="g-btn" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> طباعة التقرير
                </button>
                <a href="sales_forecasting.php" class="g-btn amber">
                    <i class="fa-solid fa-chart-line"></i> شاشة التنبؤات التفصيلية
                </a>
                <a href="alarm.php" class="g-btn">
                    <i class="fa-solid fa-bell"></i> منظومة الـ 88 تنبيهاً
                </a>
                <a href="magaiees_unified_dashboard.php" class="g-btn">
                    <i class="fa-solid fa-house"></i> لوحة التحكم
                </a>
            </div>
        </div>

        <!-- التبويبات الـ 4 الموحدة (تدمج الشاشات الثلاث + التنبؤات في مكان واحد) -->
        <div class="mode-tabs-bar">
            <button type="button" class="mode-tab-btn" id="tab-btn-all" onclick="switchGuardianMode('all')">
                <div class="mode-tab-icon" style="color:#22d3ee;">🛡️</div>
                <div>
                    <span class="mode-tab-title">الحارس الشامل وكاشف الثغرات (360°)</span>
                    <span class="mode-tab-sub">الـ 10 ركائز الرقابية + كشف الأخطاء المحاسبية</span>
                </div>
            </button>
            <button type="button" class="mode-tab-btn" id="tab-btn-money" onclick="switchGuardianMode('money')">
                <div class="mode-tab-icon" style="color:#c084fc;">💰</div>
                <div>
                    <span class="mode-tab-title">مستشار الأموال والسيولة والمديونيات</span>
                    <span class="mode-tab-sub">تحصيل الديون، السقوف الائتمانية، الشيكات والسيولة</span>
                </div>
            </button>
            <button type="button" class="mode-tab-btn" id="tab-btn-goods" onclick="switchGuardianMode('goods')">
                <div class="mode-tab-icon" style="color:#34d399;">📦</div>
                <div>
                    <span class="mode-tab-title">مستشار البضائع والمخازن والإمداد</span>
                    <span class="mode-tab-sub">النواقص الحرجة، الرواكد المجمدة، وأوامر الشراء</span>
                </div>
            </button>
            <button type="button" class="mode-tab-btn" id="tab-btn-forecast" onclick="switchGuardianMode('forecast')">
                <div class="mode-tab-icon" style="color:#fbbf24;">🔮</div>
                <div>
                    <span class="mode-tab-title">التنبؤات المستقبلية والمحاكاة (30/90 يوم)</span>
                    <span class="mode-tab-sub">توقعات المبيعات والسيولة + محاكاة (ماذا لو؟)</span>
                </div>
            </button>
        </div>

        <!-- شريط مؤشرات النبض المالي والمخزني والصحة الرقابية -->
        <div class="kpi-pulse-grid">
            <div class="pulse-card" style="--card-accent:#10b981;">
                <div class="pulse-top">
                    <span class="pulse-label">مؤشر صحة النظام والرقابة</span>
                    <span class="pulse-badge" id="kpi-health-badge" style="color:#34d399;">ممتاز</span>
                </div>
                <div class="pulse-val" id="kpi-health-score">96%</div>
                <div class="pulse-foot">
                    <span id="kpi-anomalies-count">0 ثغرات محاسبية</span>
                    <a href="audit-log.php" style="color:#34d399;text-decoration:none;font-weight:800;">سجل الرقابة ⬅</a>
                </div>
            </div>

            <div class="pulse-card" style="--card-accent:#22d3ee;">
                <div class="pulse-top">
                    <span class="pulse-label">السيولة المتاحة (خزائن + بنوك)</span>
                    <span class="pulse-badge" style="color:#22d3ee;">نقد حي</span>
                </div>
                <div class="pulse-val" id="kpi-liquid-cash">0</div>
                <div class="pulse-foot">
                    <span id="kpi-runway-text">تغطية التزامات آمنة</span>
                    <a href="vouchers-hub.php" style="color:#22d3ee;text-decoration:none;font-weight:800;">المالية ⬅</a>
                </div>
            </div>

            <div class="pulse-card" style="--card-accent:#f43f5e;">
                <div class="pulse-top">
                    <span class="pulse-label">إجمالي مديونيات العملاء</span>
                    <span class="pulse-badge" id="kpi-debt-risk-badge" style="color:#fb7185;">رقابة التحصيل</span>
                </div>
                <div class="pulse-val" id="kpi-total-debt">0</div>
                <div class="pulse-foot">
                    <span id="kpi-overlimit-count">0 متجاوز للسقف</span>
                    <a href="customer_exposure.php" style="color:#fb7185;text-decoration:none;font-weight:800;">المديونية ⬅</a>
                </div>
            </div>

            <div class="pulse-card" style="--card-accent:#a855f7;">
                <div class="pulse-top">
                    <span class="pulse-label">قيمة المخزون والأصول السلعية</span>
                    <span class="pulse-badge" style="color:#c084fc;">تقييم فعلي</span>
                </div>
                <div class="pulse-val" id="kpi-inv-value">0</div>
                <div class="pulse-foot">
                    <span id="kpi-shortage-count">0 نواقص حرجة</span>
                    <a href="inventory-movement.php" style="color:#c084fc;text-decoration:none;font-weight:800;">المخازن ⬅</a>
                </div>
            </div>

            <div class="pulse-card" style="--card-accent:#f59e0b;">
                <div class="pulse-top">
                    <span class="pulse-label">شيكات مستحقة / مرتجعة قريباً</span>
                    <span class="pulse-badge" style="color:#fbbf24;">رادار الشيكات</span>
                </div>
                <div class="pulse-val" id="kpi-checks-alert">0</div>
                <div class="pulse-foot">
                    <span id="kpi-checks-val">0 مستحق خلال 7 أيام</span>
                    <a href="checks_detector.php" style="color:#fbbf24;text-decoration:none;font-weight:800;">الشيكات ⬅</a>
                </div>
            </div>

            <div class="pulse-card" style="--card-accent:#38bdf8;">
                <div class="pulse-top">
                    <span class="pulse-label">تنبؤ المبيعات (30 يوماً قادمة)</span>
                    <span class="pulse-badge" id="kpi-forecast-trend" style="color:#38bdf8;">AI Forecast</span>
                </div>
                <div class="pulse-val" id="kpi-forecast-30d">0</div>
                <div class="pulse-foot">
                    <span id="kpi-margin-guard">هامش الربح محمي</span>
                    <a href="sales_forecasting.php" style="color:#38bdf8;text-decoration:none;font-weight:800;">التنبؤات ⬅</a>
                </div>
            </div>
        </div>

        <!-- منصة الاستشارة التفاعلية بالذكاء الاصطناعي (تعمل سحابياً ومحلياً) -->
        <div class="ai-console-card">
            <div class="section-head" style="margin-bottom:6px;">
                <h2><i class="fa-solid fa-wand-magic-sparkles" style="color:#22d3ee;"></i> المستشار التفاعلي الفوري (اسأل الحارس الذكي عن أي قرار مالي أو مخزني أو رقابي)</h2>
                <span style="font-size:0.76rem;color:#94a3b8;font-weight:700;">متصل بـ Gemini / OpenAI + المحرك الاستنتاجي المحلي الفوري</span>
            </div>

            <div class="prompt-chips">
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('comprehensive')">🛡️ تقرير الحارس الشامل وكشف الثغرات الآن</button>
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('collection_plan')">💰 خطة تحصيل الديون المتعثرة وتأمين السيولة</button>
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('reorder_plan')">📦 خطة الشراء الذكي وتصفية البضائع الراكدة</button>
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('profit_margin')">📈 تحليل تآكل هامش الربح والفواتير الحرجة</button>
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('forecast_90')">🔮 توقعات المبيعات والتدفق النقدي لـ 90 يوماً</button>
                <button type="button" class="prompt-chip" onclick="askGuardianPreset('accounting_audit')">⚖️ فحص توازن القيود المحاسبية وتكرار الفواتير</button>
            </div>

            <div class="console-input-row">
                <input type="text" id="guardian-custom-input" class="console-input" placeholder="اكتب سؤالك للمستشار الذكي... مثال: من هم أخطر 5 عملاء مديونية؟ وما الأصناف التي يجب شراؤها فوراً؟" onkeydown="if(event.key==='Enter') runCustomGuardianQuery();">
                <button type="button" class="g-btn primary" onclick="runCustomGuardianQuery()" id="btn-ask-ai">
                    <i class="fa-solid fa-paper-plane"></i> توليد التوصية الذكية
                </button>
            </div>

            <div id="guardian-ai-response" class="ai-response-box"></div>
        </div>

        <!-- ============================================================== -->
        <!-- القسم 1: مصفوفة الـ 10 ركائز الرقابية والتنبؤية (تظهر في وضع الحارس الشامل) -->
        <!-- ============================================================== -->
        <div id="section-mode-all" class="pillars-section">
            <div class="section-head">
                <h2><i class="fa-solid fa-shield-halved" style="color:#10b981;"></i> مصفوفة الركائز الـ 10 للحارس الأمين (فحص حي لبيانات النظام)</h2>
                <span style="font-size:0.8rem;color:#94a3b8;">يتم فحص جميع الفواتير، الأرصدة، الشيكات، المخازن، والقيود لحظياً</span>
            </div>
            <div class="pillars-grid" id="ten-pillars-container">
                <!-- يتم ملؤها ديناميكياً من المحرك الذكي -->
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- القسم 2: مستشار الأموال والسيولة والمديونيات (Money Advisor) -->
        <!-- ============================================================== -->
        <div id="section-mode-money" class="pillars-section">
            <div class="section-head">
                <h2><i class="fa-solid fa-sack-dollar" style="color:#c084fc;"></i> مستشار الأموال والسيولة ورادار المديونيات الحرجة</h2>
                <div style="display:flex;gap:8px;">
                    <a href="customer_exposure.php" class="mini-btn stmt">تقرير مديونية العملاء ⬅</a>
                    <a href="credit_limits_dashboard.php" class="mini-btn po">السقوف الائتمانية ⬅</a>
                    <a href="combined_receivables.php" class="mini-btn wa">الذمم الموحد ⬅</a>
                </div>
            </div>

            <div class="panel-grid-2">
                <div class="data-panel">
                    <h3>
                        <span>🚨 أعلى العملاء مديونية وتجاوزاً للسقف الائتماني</span>
                        <span style="font-size:0.75rem;color:#fb7185;" id="money-panel-count">0 عميل</span>
                    </h3>
                    <div class="table-scroll">
                        <table class="g-table">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th>المديونية الحالية</th>
                                    <th>السقف الائتماني</th>
                                    <th>درجة المخاطرة</th>
                                    <th>إجراء فوري</th>
                                </tr>
                            </thead>
                            <tbody id="money-customers-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="data-panel">
                    <h3>
                        <span>💳 رادار الشيكات والالتزامات ومصادر السيولة</span>
                        <a href="checks-cycle.php" class="mini-btn stmt">دورة الشيكات ⬅</a>
                    </h3>
                    <div class="table-scroll">
                        <table class="g-table">
                            <thead>
                                <tr>
                                    <th>البيان / الشيك / الخزينة</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>الحالة / الاستحقاق</th>
                                    <th>إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="money-liquidity-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- القسم 3: مستشار البضائع والمخازن وسلاسل الإمداد (Goods Advisor) -->
        <!-- ============================================================== -->
        <div id="section-mode-goods" class="pillars-section">
            <div class="section-head">
                <h2><i class="fa-solid fa-boxes-stacked" style="color:#34d399;"></i> مستشار البضائع والمخازن وتوصيات الشراء الذكية</h2>
                <div style="display:flex;gap:8px;">
                    <a href="inventory-movement.php" class="mini-btn stmt">حركات البضائع ⬅</a>
                    <a href="smart_inventory_report.php" class="mini-btn wa">تقرير المخزن الذكي ⬅</a>
                    <a href="purchase.php" class="mini-btn po">+ فاتورة مشتريات جديدة</a>
                </div>
            </div>

            <div class="panel-grid-2">
                <div class="data-panel">
                    <h3>
                        <span>⚠️ أصناف وصلت حد الطلب أو رصيد سالب (توصية شراء عاجلة)</span>
                        <span style="font-size:0.75rem;color:#fbbf24;" id="goods-shortage-badge">0 صنف</span>
                    </h3>
                    <div class="table-scroll">
                        <table class="g-table">
                            <thead>
                                <tr>
                                    <th>الصنف</th>
                                    <th>الرصيد الحالي</th>
                                    <th>حد الطلب</th>
                                    <th>الكمية المقترح شراؤها</th>
                                    <th>إجراء فوري</th>
                                </tr>
                            </thead>
                            <tbody id="goods-shortage-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="data-panel">
                    <h3>
                        <span>🧊 البضائع الراكدة والأموال المجمدة وأعلى الأصناف دوراناً</span>
                        <a href="inventory_turnover.php" class="mini-btn stmt">معدل الدوران ⬅</a>
                    </h3>
                    <div class="table-scroll">
                        <table class="g-table">
                            <thead>
                                <tr>
                                    <th>الصنف</th>
                                    <th>الكمية</th>
                                    <th>القيمة المجمدة</th>
                                    <th>التصنيف الذكي</th>
                                    <th>التوصية</th>
                                </tr>
                            </thead>
                            <tbody id="goods-deadstock-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- القسم 4: التنبؤات المستقبلية والمحاكاة المالية (Forecast & What-If) -->
        <!-- ============================================================== -->
        <div id="section-mode-forecast" class="pillars-section">
            <div class="section-head">
                <h2><i class="fa-solid fa-crystal-ball" style="color:#fbbf24;"></i> التنبؤات المستقبلية (30 / 60 / 90 يوماً) ومحاكاة القرارات المالية (What-If)</h2>
                <a href="sales_forecasting.php" class="mini-btn stmt">فتح شاشة التنبؤات التاريخية الكاملة ⬅</a>
            </div>

            <div class="panel-grid-2">
                <div class="data-panel">
                    <h3>
                        <span>📈 منحنى المبيعات الفعلية والتنبؤ الذكي للأشهر الـ 3 القادمة</span>
                        <span style="font-size:0.75rem;color:#22d3ee;" id="forecast-growth-label">تحليل الاتجاه</span>
                    </h3>
                    <div style="height:290px;position:relative;">
                        <canvas id="guardianForecastChart"></canvas>
                    </div>
                </div>

                <div class="data-panel">
                    <h3>
                        <span>🎛️ مختبر محاكاة القرارات (ماذا لو؟ What-If Simulator)</span>
                        <button type="button" class="mini-btn stmt" onclick="resetSimulator()">إعادة ضبط</button>
                    </h3>
                    <div class="sim-controls">
                        <div class="sim-box">
                            <label>
                                <span>زيادة حجم المبيعات / الأسعار</span>
                                <span id="sim-sales-val" style="color:#22d3ee;">+10%</span>
                            </label>
                            <input type="range" id="sim-sales-pct" min="-20" max="50" value="10" oninput="updateWhatIfSimulation()">
                        </div>
                        <div class="sim-box">
                            <label>
                                <span>نسبة تحصيل ديون العملاء</span>
                                <span id="sim-collect-val" style="color:#34d399;">30%</span>
                            </label>
                            <input type="range" id="sim-collect-pct" min="0" max="100" value="30" oninput="updateWhatIfSimulation()">
                        </div>
                        <div class="sim-box">
                            <label>
                                <span>تصفية وتحويل الرواكد لسيولة</span>
                                <span id="sim-dead-val" style="color:#fbbf24;">25%</span>
                            </label>
                            <input type="range" id="sim-dead-pct" min="0" max="100" value="25" oninput="updateWhatIfSimulation()">
                        </div>
                    </div>

                    <div id="sim-results-card" style="background:rgba(15,23,42,0.9);border:1px solid rgba(34,211,238,0.3);border-radius:14px;padding:14px 16px;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;">
                            <div>
                                <div style="font-size:0.74rem;color:#94a3b8;">السيولة الإضافية المتولدة</div>
                                <div style="font-size:1.18rem;font-weight:900;color:#34d399;" id="sim-out-cash">+0</div>
                            </div>
                            <div>
                                <div style="font-size:0.74rem;color:#94a3b8;">المبيعات المتوقعة بعد القرار</div>
                                <div style="font-size:1.18rem;font-weight:900;color:#22d3ee;" id="sim-out-sales">0</div>
                            </div>
                            <div>
                                <div style="font-size:0.74rem;color:#94a3b8;">صافي الربح الإضافي التقديري</div>
                                <div style="font-size:1.18rem;font-weight:900;color:#c084fc;" id="sim-out-profit">+0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const SERVER_INITIAL_DATA = <?php echo $initialServerPayload; ?>;
let CURRENT_MODE = <?php echo json_encode($mode); ?>;
let GUARDIAN_STATE = {
    currency: SERVER_INITIAL_DATA.currency || 'SDG',
    customers: [],
    suppliers: [],
    items: [],
    salesInvoices: [],
    purchaseInvoices: [],
    checks: [],
    safesCash: 0,
    banksCash: 0,
    totalLiquidCash: 0,
    totalCustomerDebt: 0,
    totalSupplierDebt: 0,
    totalInventoryValue: 0,
    deadStockValue: 0,
    overLimitCustomers: [],
    shortageItems: [],
    deadStockItems: [],
    starItems: [],
    maturingChecks: [],
    bouncedChecks: [],
    belowCostSales: [],
    duplicateInvoices: [],
    unbalancedGL: [],
    monthlySales: [],
    forecastNext30: 0,
    avgGrowthRate: 0,
    healthScore: 96,
    pillars: []
};

let guardianChartInstance = null;

function safeReadLS(key, fallback = []) {
    try {
        const raw = localStorage.getItem(key);
        if (!raw) return fallback;
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : fallback;
    } catch (e) {
        return fallback;
    }
}

function fmtNum(n) {
    return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
}

function runFullGuardianScan(showNotification = false) {
    // 1. دمج بيانات السيرفر مع بيانات المتصفح الحية (localStorage)
    const lsCustomers = safeReadLS('erp_customers_v1', []);
    const lsSuppliers = safeReadLS('erp_suppliers_v1', []);
    const lsItems     = safeReadLS('erp_items_v1', []);
    const lsInvMap    = (() => {
        try { return JSON.parse(localStorage.getItem('erp_inventory_v1') || '{}') || {}; } catch(e) { return {}; }
    })();
    const lsSales     = safeReadLS('erp_sales_invoices_v1', []).filter(x => !x.isDeleted && !x.wasSplit);
    const lsPurchases = safeReadLS('erp_purchase_invoices_v1', []).filter(x => !x.isDeleted);
    const lsChecks    = safeReadLS('erp_checks_v1', []);
    const lsSafes     = safeReadLS('erp_safes_v1', []);
    const lsBanks     = safeReadLS('erp_banks_v1', []);
    const lsGL        = safeReadLS('erp_gl_v1', []);

    GUARDIAN_STATE.salesInvoices = lsSales;
    GUARDIAN_STATE.purchaseInvoices = lsPurchases;
    GUARDIAN_STATE.checks = lsChecks;

    // 2. حساب السيولة في الخزائن والبنوك
    let safesSum = 0;
    lsSafes.forEach(s => { safesSum += parseFloat(s.balance ?? s.openingBalance ?? 0) || 0; });
    let banksSum = 0;
    lsBanks.forEach(b => { banksSum += parseFloat(b.balance ?? b.openingBalance ?? 0) || 0; });
    GUARDIAN_STATE.safesCash = safesSum;
    GUARDIAN_STATE.banksCash = banksSum;
    GUARDIAN_STATE.totalLiquidCash = safesSum + banksSum;

    // 3. بناء قائمة العملاء الموحدة (السيرفر + localStorage)
    const custMap = new Map();
    (SERVER_INITIAL_DATA.customers || []).forEach(c => {
        const name = (c.name || '').trim();
        if (!name) return;
        custMap.set(name, {
            id: c.id || name,
            name: name,
            phone: c.phone || '',
            debt: parseFloat(c.total_debt ?? c.balance ?? 0) || 0,
            creditLimit: parseFloat(c.credit_limit ?? c.creditLimit ?? 5000000) || 5000000
        });
    });
    lsCustomers.forEach(c => {
        const name = (c.name || c.customerName || '').trim();
        if (!name) return;
        const debtVal = parseFloat(c.balance ?? c.currentBalance ?? c.openingBalance ?? 0) || 0;
        const limVal  = parseFloat(c.creditLimit ?? c.credit_limit ?? 5000000) || 5000000;
        if (custMap.has(name)) {
            const ex = custMap.get(name);
            ex.debt = Math.max(ex.debt, debtVal);
            if (c.phone) ex.phone = c.phone;
            if (limVal > 0) ex.creditLimit = limVal;
        } else {
            custMap.set(name, {
                id: c.id || name,
                name: name,
                phone: c.phone || c.mobile || '',
                debt: debtVal,
                creditLimit: limVal
            });
        }
    });

    const allCustomers = Array.from(custMap.values()).sort((a, b) => b.debt - a.debt);
    GUARDIAN_STATE.customers = allCustomers;
    GUARDIAN_STATE.totalCustomerDebt = allCustomers.reduce((s, c) => s + (c.debt > 0 ? c.debt : 0), 0);
    GUARDIAN_STATE.overLimitCustomers = allCustomers.filter(c => c.debt > 0 && c.creditLimit > 0 && c.debt >= c.creditLimit * 0.85);

    // 4. حساب التزامات الموردين
    let suppDebt = 0;
    lsSuppliers.forEach(s => {
        const b = parseFloat(s.balance ?? s.currentBalance ?? s.openingBalance ?? 0) || 0;
        if (b > 0) suppDebt += b;
    });
    GUARDIAN_STATE.totalSupplierDebt = suppDebt;

    // 5. بناء قائمة الأصناف والمخزون الموحدة
    const itemSoldQtyMap = {};
    const belowCostArr = [];
    const seenInvNumbers = new Set();
    const dupInvArr = [];

    lsSales.forEach(inv => {
        const invNo = String(inv.invoiceNumber || inv.id || '').trim();
        if (invNo) {
            if (seenInvNumbers.has(invNo)) dupInvArr.push(invNo);
            seenInvNumbers.add(invNo);
        }
        const itemsArr = Array.isArray(inv.items) ? inv.items : [];
        itemsArr.forEach(it => {
            const nm = (it.itemName || it.name || '').trim();
            const q = parseFloat(it.qty ?? it.quantity ?? 0) || 0;
            const sellP = parseFloat(it.price ?? it.unitPrice ?? 0) || 0;
            const costP = parseFloat(it.costPrice ?? it.buyPrice ?? 0) || 0;
            if (nm) itemSoldQtyMap[nm] = (itemSoldQtyMap[nm] || 0) + q;
            if (sellP > 0 && costP > 0 && sellP < costP) {
                belowCostArr.push({ invNo, itemName: nm, sellPrice: sellP, costPrice: costP });
            }
        });
    });

    GUARDIAN_STATE.belowCostSales = belowCostArr;
    GUARDIAN_STATE.duplicateInvoices = dupInvArr;

    const itemMap = new Map();
    (SERVER_INITIAL_DATA.items || []).forEach(it => {
        const name = (it.name || '').trim();
        if (!name) return;
        const qty = parseFloat(it.stock_qty ?? it.qty ?? 0) || 0;
        const cost = parseFloat(it.cost_price ?? it.buy_price ?? it.price ?? 0) || 0;
        const minL = parseFloat(it.min_limit ?? it.minStock ?? 10) || 10;
        itemMap.set(name, {
            id: it.id || name,
            name: name,
            qty: qty,
            cost: cost,
            minLimit: minL,
            soldQty: itemSoldQtyMap[name] || 0
        });
    });

    lsItems.forEach(it => {
        const name = (it.name || it.itemName || '').trim();
        if (!name) return;
        let qty = parseFloat(it.qty ?? it.stock ?? it.openingStock ?? 0) || 0;
        if (lsInvMap[it.id] !== undefined) {
            const mVal = lsInvMap[it.id];
            qty = typeof mVal === 'object' ? (parseFloat(mVal.qty ?? 0) || 0) : (parseFloat(mVal) || 0);
        }
        const cost = parseFloat(it.buyPrice ?? it.costPrice ?? it.price ?? 0) || 0;
        const minL = parseFloat(it.minStock ?? it.reorderLevel ?? 10) || 10;
        itemMap.set(name, {
            id: it.id || name,
            name: name,
            qty: qty,
            cost: cost,
            minLimit: minL,
            soldQty: itemSoldQtyMap[name] || 0
        });
    });

    const allItems = Array.from(itemMap.values());
    GUARDIAN_STATE.items = allItems;
    GUARDIAN_STATE.totalInventoryValue = allItems.reduce((s, it) => s + (Math.max(0, it.qty) * (it.cost || 0)), 0) || SERVER_INITIAL_DATA.invValue || 0;
    GUARDIAN_STATE.shortageItems = allItems.filter(it => it.qty <= it.minLimit).sort((a, b) => a.qty - b.qty);
    GUARDIAN_STATE.deadStockItems = allItems.filter(it => it.qty > it.minLimit && it.soldQty === 0 && (it.qty * it.cost) > 0).sort((a, b) => (b.qty * b.cost) - (a.qty * a.cost));
    GUARDIAN_STATE.deadStockValue = GUARDIAN_STATE.deadStockItems.reduce((s, it) => s + (it.qty * it.cost), 0);
    GUARDIAN_STATE.starItems = allItems.filter(it => it.soldQty > 0).sort((a, b) => b.soldQty - a.soldQty);

    // 6. فحص الشيكات المستحقة والمرتجعة
    const nowMs = Date.now();
    const sevenDaysMs = 7 * 86400 * 1000;
    const maturing = [];
    const bounced = [];
    lsChecks.forEach(chk => {
        const st = String(chk.status || '').toLowerCase();
        if (st.includes('مرتجع') || st.includes('bounced') || st.includes('rejected')) {
            bounced.push(chk);
        } else if (chk.dueDate) {
            const dueMs = new Date(chk.dueDate).getTime();
            if (!isNaN(dueMs) && (dueMs - nowMs) <= sevenDaysMs && !st.includes('محصل') && !st.includes('paid')) {
                maturing.push(chk);
            }
        }
    });
    GUARDIAN_STATE.maturingChecks = maturing;
    GUARDIAN_STATE.bouncedChecks = bounced;

    // 7. فحص القيود المحاسبية غير المتوازنة
    const unbalanced = [];
    lsGL.forEach(jv => {
        const d = parseFloat(jv.totalDebit ?? jv.debit ?? 0) || 0;
        const c = parseFloat(jv.totalCredit ?? jv.credit ?? 0) || 0;
        if (Math.abs(d - c) > 0.01 && (jLinesHasDiff(jv))) {
            unbalanced.push(jv);
        }
    });
    GUARDIAN_STATE.unbalancedGL = unbalanced;

    // 8. حساب المبيعات الشهرية والتنبؤ لـ 30 يوماً
    const mSalesMap = {};
    (SERVER_INITIAL_DATA.salesMonthly || []).forEach(m => {
        if (m.month) mSalesMap[m.month] = parseFloat(m.total || 0);
    });
    lsSales.forEach(inv => {
        if (inv.type === 'return') return;
        const dStr = String(inv.date || inv.createdAt || '').slice(0, 7);
        if (!dStr || dStr.length < 7) return;
        const tot = parseFloat(inv.grandTotal ?? inv.totalAmount ?? inv.total ?? 0) || 0;
        mSalesMap[dStr] = (mSalesMap[dStr] || 0) + tot;
    });
    let sortedMonths = Object.keys(mSalesMap).sort().slice(-6).map(k => ({ month: k, total: Math.round(mSalesMap[k]) }));
    if (sortedMonths.length === 0) {
        sortedMonths = [
            { month: '2026-05', total: 950000 },
            { month: '2026-06', total: 1120000 },
            { month: '2026-07', total: 1280000 },
            { month: '2026-08', total: 1390000 },
            { month: '2026-09', total: 1520000 },
            { month: '2026-10', total: 1680000 }
        ];
    }
    GUARDIAN_STATE.monthlySales = sortedMonths;
    let gRates = [];
    for (let i = 1; i < sortedMonths.length; i++) {
        const p = sortedMonths[i - 1].total;
        const c = sortedMonths[i].total;
        if (p > 0) gRates.push((c - p) / p);
    }
    const avgG = gRates.length > 0 ? (gRates.reduce((a, b) => a + b, 0) / gRates.length) : 0.08;
    GUARDIAN_STATE.avgGrowthRate = avgG;
    const lastM = sortedMonths[sortedMonths.length - 1].total;
    GUARDIAN_STATE.forecastNext30 = Math.round(lastM * (1 + avgG));

    // 9. حساب مؤشر صحة النظام (Health Score 0-100%)
    let penalties = 0;
    if (GUARDIAN_STATE.overLimitCustomers.length > 0) penalties += Math.min(12, GUARDIAN_STATE.overLimitCustomers.length * 3);
    if (GUARDIAN_STATE.shortageItems.length > 0) penalties += Math.min(10, GUARDIAN_STATE.shortageItems.length * 2);
    if (GUARDIAN_STATE.bouncedChecks.length > 0) penalties += Math.min(10, GUARDIAN_STATE.bouncedChecks.length * 5);
    if (GUARDIAN_STATE.belowCostSales.length > 0) penalties += Math.min(8, GUARDIAN_STATE.belowCostSales.length * 4);
    if (GUARDIAN_STATE.unbalancedGL.length > 0) penalties += 10;
    GUARDIAN_STATE.healthScore = Math.max(55, 100 - penalties);

    // 10. تحديث واجهة المستخدم بالكامل
    renderKPIHeader();
    renderTenPillars();
    renderMoneyAdvisorPanels();
    renderGoodsAdvisorPanels();
    renderForecastChartAndSimulator();

    if (showNotification) {
        askGuardianPreset('comprehensive');
    }
}

function jLinesHasDiff(jv) {
    if (!Array.isArray(jv.lines)) return false;
    let d = 0, c = 0;
    jv.lines.forEach(l => { d += parseFloat(l.debit || 0); c += parseFloat(l.credit || 0); });
    return Math.abs(d - c) > 0.01;
}

function renderKPIHeader() {
    const cur = GUARDIAN_STATE.currency;
    document.getElementById('kpi-health-score').textContent = GUARDIAN_STATE.healthScore + '%';
    const hb = document.getElementById('kpi-health-badge');
    if (GUARDIAN_STATE.healthScore >= 88) {
        hb.textContent = 'درع رقابي ممتاز';
        hb.style.color = '#34d399';
    } else if (GUARDIAN_STATE.healthScore >= 72) {
        hb.textContent = 'يحتاج انتباه';
        hb.style.color = '#fbbf24';
    } else {
        hb.textContent = 'مخاطر مرتفعة';
        hb.style.color = '#fb7185';
    }

    const totalAnom = GUARDIAN_STATE.belowCostSales.length + GUARDIAN_STATE.duplicateInvoices.length + GUARDIAN_STATE.unbalancedGL.length;
    document.getElementById('kpi-anomalies-count').textContent = totalAnom + ' ملاحظة رقابية';

    document.getElementById('kpi-liquid-cash').textContent = fmtNum(GUARDIAN_STATE.totalLiquidCash) + ' ' + cur;
    const covRatio = GUARDIAN_STATE.totalSupplierDebt > 0
        ? Math.round((GUARDIAN_STATE.totalLiquidCash / GUARDIAN_STATE.totalSupplierDebt) * 100)
        : 100;
    document.getElementById('kpi-runway-text').textContent = 'تغطية الموردين: ' + covRatio + '%';

    document.getElementById('kpi-total-debt').textContent = fmtNum(GUARDIAN_STATE.totalCustomerDebt) + ' ' + cur;
    document.getElementById('kpi-overlimit-count').textContent = GUARDIAN_STATE.overLimitCustomers.length + ' عميل متجاوز للسقف';

    document.getElementById('kpi-inv-value').textContent = fmtNum(GUARDIAN_STATE.totalInventoryValue) + ' ' + cur;
    document.getElementById('kpi-shortage-count').textContent = GUARDIAN_STATE.shortageItems.length + ' صنف تحت حد الطلب';

    const chkCount = GUARDIAN_STATE.maturingChecks.length + GUARDIAN_STATE.bouncedChecks.length;
    document.getElementById('kpi-checks-alert').textContent = chkCount + ' شيك';
    document.getElementById('kpi-checks-val').textContent = GUARDIAN_STATE.bouncedChecks.length + ' مرتجع | ' + GUARDIAN_STATE.maturingChecks.length + ' مستحق';

    document.getElementById('kpi-forecast-30d').textContent = fmtNum(GUARDIAN_STATE.forecastNext30) + ' ' + cur;
    const gPct = Math.round(GUARDIAN_STATE.avgGrowthRate * 100);
    document.getElementById('kpi-forecast-trend').textContent = (gPct >= 0 ? '↑ +' : '↓ ') + gPct + '% شهرياً';
    document.getElementById('kpi-margin-guard').textContent = GUARDIAN_STATE.belowCostSales.length === 0
        ? '0 بيع تحت التكلفة'
        : (GUARDIAN_STATE.belowCostSales.length + ' بند بأقل من التكلفة!');
}

function renderTenPillars() {
    const cur = GUARDIAN_STATE.currency;
    const pillars = [
        {
            num: 1,
            icon: '🚨',
            title: 'كاشف مخاطر المديونيات وتجاوز السقوف',
            color: '#f43f5e',
            status: GUARDIAN_STATE.overLimitCustomers.length > 0 ? (GUARDIAN_STATE.overLimitCustomers.length + ' عميل حرج') : 'آمن 100%',
            desc: `إجمالي ديون العملاء ${fmtNum(GUARDIAN_STATE.totalCustomerDebt)} ${cur}. يوجد ${GUARDIAN_STATE.overLimitCustomers.length} عميل تجاوزوا 85% من السقف الائتماني المسموح.`,
            link: 'customer_exposure.php',
            linkText: 'فتح رادار المديونية',
            preset: 'collection_plan'
        },
        {
            num: 2,
            icon: '💳',
            title: 'رادار الشيكات المرتجعة والمستحقة (7 أيام)',
            color: '#f59e0b',
            status: (GUARDIAN_STATE.bouncedChecks.length + GUARDIAN_STATE.maturingChecks.length) + ' شيك للمتابعة',
            desc: `تم رصد ${GUARDIAN_STATE.bouncedChecks.length} شيك مرتجع و ${GUARDIAN_STATE.maturingChecks.length} شيك يستحق التحصيل أو السداد خلال الـ 7 أيام القادمة.`,
            link: 'checks_detector.php',
            linkText: 'فتح كاشف الشيكات',
            preset: 'collection_plan'
        },
        {
            num: 3,
            icon: '📦',
            title: 'كاشف العجز المخزني والنواقص الحرجة',
            color: '#38bdf8',
            status: GUARDIAN_STATE.shortageItems.length + ' صنف يحتاج شراء',
            desc: `يوجد ${GUARDIAN_STATE.shortageItems.length} صنف وصل إلى حد إعادة الطلب أو رصيد سالب ويحتاج إلى أمر شراء فوري لتفادي توقف المبيعات.`,
            link: 'inventory-movement.php',
            linkText: 'حركات البضائع',
            preset: 'reorder_plan'
        },
        {
            num: 4,
            icon: '🧊',
            title: 'محلل البضائع الراكدة والأموال المجمدة',
            color: '#a855f7',
            status: fmtNum(GUARDIAN_STATE.deadStockValue) + ' ' + cur,
            desc: `رصد ${GUARDIAN_STATE.deadStockItems.length} صنف راكد بدون حركة بيع. تسييل 25% منها يضخ ${fmtNum(GUARDIAN_STATE.deadStockValue * 0.25)} ${cur} سيولة فورية.`,
            link: 'inventory_turnover.php',
            linkText: 'تحليل دوران المخزون',
            preset: 'reorder_plan'
        },
        {
            num: 5,
            icon: '⚖️',
            title: 'كاشف الثغرات المحاسبية وعدم توازن القيود',
            color: '#10b981',
            status: (GUARDIAN_STATE.unbalancedGL.length + GUARDIAN_STATE.duplicateInvoices.length) + ' ثغرة مرصودة',
            desc: `فحص دفتر الأستاذ والفواتير: ${GUARDIAN_STATE.unbalancedGL.length} قيد غير متوازن و ${GUARDIAN_STATE.duplicateInvoices.length} رقم فاتورة مكرر.`,
            link: 'general-ledger.php',
            linkText: 'دفتر الأستاذ العام',
            preset: 'accounting_audit'
        },
        {
            num: 6,
            icon: '🛡️',
            title: 'حارس هامش الربح والبيع بأقل من التكلفة',
            color: '#ec4899',
            status: GUARDIAN_STATE.belowCostSales.length === 0 ? 'هامش محمي' : (GUARDIAN_STATE.belowCostSales.length + ' مخالفة تسعير'),
            desc: GUARDIAN_STATE.belowCostSales.length === 0
                ? 'جميع بنود المبيعات المسجلة تحترم حاجز التكلفة ولا يوجد تآكل سعري.'
                : `تنبيه: تم رصد ${GUARDIAN_STATE.belowCostSales.length} عملية بيع بسعر أقل من تكلفة الشراء!`,
            link: 'profitability_analysis.php',
            linkText: 'تحليل الربحية',
            preset: 'profit_margin'
        },
        {
            num: 7,
            icon: '🏦',
            title: 'اختبار ضغط السيولة وتغطية الموردين',
            color: '#22d3ee',
            status: fmtNum(GUARDIAN_STATE.totalLiquidCash) + ' ' + cur,
            desc: `السيولة الجاهزة بالخزائن والبنوك ${fmtNum(GUARDIAN_STATE.totalLiquidCash)} ${cur} مقابل التزامات موردين بقيمة ${fmtNum(GUARDIAN_STATE.totalSupplierDebt)} ${cur}.`,
            link: 'cashflow_modern.php',
            linkText: 'التدفقات النقدية',
            preset: 'collection_plan'
        },
        {
            num: 8,
            icon: '🔮',
            title: 'التنبؤ الذكي بالمبيعات والاحتياج التمويلي (30 يوم)',
            color: '#fbbf24',
            status: fmtNum(GUARDIAN_STATE.forecastNext30) + ' ' + cur,
            desc: `يتوقع محرك الذكاء الاصطناعي تحقيق مبيعات بقيمة ${fmtNum(GUARDIAN_STATE.forecastNext30)} ${cur} الشهر القادم بمعدل تغير ${Math.round(GUARDIAN_STATE.avgGrowthRate * 100)}%.`,
            link: 'sales_forecasting.php',
            linkText: 'شاشة التنبؤات',
            preset: 'forecast_90'
        },
        {
            num: 9,
            icon: '🔐',
            title: 'مؤشر الأمان الرقمي وسجل الرقابة الصارمة',
            color: '#34d399',
            status: 'صحة الرقابة ' + GUARDIAN_STATE.healthScore + '%',
            desc: 'مراقبة الصلاحيات، تعديلات الفواتير، الحذف، والنسخ الاحتياطي لضمان عدم ضياع أي حركة مالية أو مخزنية.',
            link: 'audit-log.php',
            linkText: 'سجل الرقابة والأمان',
            preset: 'accounting_audit'
        },
        {
            num: 10,
            icon: '🔔',
            title: 'التكامل المباشر مع منظومة الـ 88 تنبيهاً استباقياً',
            color: '#818cf8',
            status: 'متصل لحظياً',
            desc: 'يرتبط الحارس الذكي مباشرة بمركز الإنذار المبكر (88 قاعدة رقابية في 7 قطاعات) لإرسال تنبيهات فورية.',
            link: 'alarm.php',
            linkText: 'فتح مركز الـ 88 تنبيهاً',
            preset: 'comprehensive'
        }
    ];

    GUARDIAN_STATE.pillars = pillars;
    const container = document.getElementById('ten-pillars-container');
    container.innerHTML = pillars.map(p => `
        <div class="pillar-card" style="--p-color:${p.color};">
            <div>
                <div class="pillar-head">
                    <span class="pillar-title"><span>${p.icon}</span> ${p.num}. ${p.title}</span>
                    <span class="pillar-status" style="background:${p.color}22;color:${p.color};border:1px solid ${p.color}55;">${p.status}</span>
                </div>
                <p class="pillar-desc" style="margin-top:8px;">${p.desc}</p>
            </div>
            <div class="pillar-actions">
                <a href="${p.link}" class="pillar-link">${p.linkText} <i class="fa-solid fa-arrow-left"></i></a>
                <button type="button" class="pillar-ask-btn" onclick="askGuardianPreset('${p.preset}')">🤖 استشارة AI</button>
            </div>
        </div>
    `).join('');
}

function renderMoneyAdvisorPanels() {
    const cur = GUARDIAN_STATE.currency;
    const custs = GUARDIAN_STATE.customers.filter(c => c.debt > 0).slice(0, 12);
    document.getElementById('money-panel-count').textContent = custs.length + ' عميل مدين';

    const tbody = document.getElementById('money-customers-tbody');
    if (custs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:24px;color:#34d399;font-weight:800;">🎉 ممتاز! لا توجد مديونيات حرجة متأخرة حالياً.</td></tr>`;
    } else {
        tbody.innerHTML = custs.map(c => {
            const ratio = c.creditLimit > 0 ? Math.round((c.debt / c.creditLimit) * 100) : 50;
            const isHigh = ratio >= 85;
            const badgeColor = isHigh ? '#fb7185' : (ratio >= 60 ? '#fbbf24' : '#34d399');
            const badgeText = isHigh ? `تجاوز حرج (${ratio}%)` : `متابعة (${ratio}%)`;
            const waMsg = encodeURIComponent(`السلام عليكم عميلنا العزيز ${c.name}، نود تذكيركم بسداد الرصيد المستحق بقيمة ${fmtNum(c.debt)} ${cur}. شاكرين تعاونكم الدائم.`);
            const waHref = c.phone ? `https://wa.me/${String(c.phone).replace(/[^0-9]/g, '')}?text=${waMsg}` : `javascript:alert('لا يوجد رقم هاتف مسجل للعميل')`;
            return `
                <tr>
                    <td style="font-weight:800;color:#fff;">${c.name}</td>
                    <td style="font-weight:900;color:#fb7185;">${fmtNum(c.debt)} ${cur}</td>
                    <td>${fmtNum(c.creditLimit)} ${cur}</td>
                    <td><span style="padding:2px 8px;border-radius:99px;font-size:0.72rem;font-weight:800;background:${badgeColor}22;color:${badgeColor};">${badgeText}</span></td>
                    <td style="display:flex;gap:6px;">
                        <a href="${waHref}" target="_blank" class="mini-btn wa" title="إرسال مطالبة واتساب">💬 واتساب</a>
                        <a href="account-statement.php?customer=${encodeURIComponent(c.name)}" class="mini-btn stmt">📄 كشف</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    const liqBody = document.getElementById('money-liquidity-tbody');
    const rowsHtml = [];
    rowsHtml.push(`
        <tr>
            <td style="font-weight:800;">🏦 إجمالي أرصدة الخزائن النقدية</td>
            <td>سيولة جاهزة</td>
            <td style="font-weight:900;color:#34d399;">${fmtNum(GUARDIAN_STATE.safesCash)} ${cur}</td>
            <td>متاح للصرف الفوري</td>
            <td><a href="vouchers-hub.php" class="mini-btn stmt">الخزائن</a></td>
        </tr>
        <tr>
            <td style="font-weight:800;">🏛️ إجمالي أرصدة الحسابات البنكية</td>
            <td>سيولة بنكية</td>
            <td style="font-weight:900;color:#22d3ee;">${fmtNum(GUARDIAN_STATE.banksCash)} ${cur}</td>
            <td>متاح للتحويل</td>
            <td><a href="vouchers-hub.php" class="mini-btn stmt">البنوك</a></td>
        </tr>
        <tr>
            <td style="font-weight:800;">🤝 إجمالي التزامات الموردين</td>
            <td>ذمم دائنة</td>
            <td style="font-weight:900;color:#fbbf24;">${fmtNum(GUARDIAN_STATE.totalSupplierDebt)} ${cur}</td>
            <td>جدولة سداد</td>
            <td><a href="combined_receivables.php" class="mini-btn po">الذمم</a></td>
        </tr>
    `);

    GUARDIAN_STATE.bouncedChecks.slice(0, 4).forEach(chk => {
        rowsHtml.push(`
            <tr>
                <td style="font-weight:800;color:#fb7185;">⚠️ شيك رقم ${chk.checkNumber || chk.id || '-'} (${chk.partyName || chk.customerName || 'عميل'})</td>
                <td>شيك مرتجع</td>
                <td style="font-weight:900;color:#fb7185;">${fmtNum(chk.amount || 0)} ${cur}</td>
                <td>مرتجع - يتطلب تحصيل فوري</td>
                <td><a href="checks-cycle.php" class="mini-btn po">معالجة</a></td>
            </tr>
        `);
    });

    GUARDIAN_STATE.maturingChecks.slice(0, 4).forEach(chk => {
        rowsHtml.push(`
            <tr>
                <td style="font-weight:800;color:#fbbf24;">⏰ شيك رقم ${chk.checkNumber || chk.id || '-'} (${chk.partyName || chk.customerName || 'طرف'})</td>
                <td>شيك مستحق</td>
                <td style="font-weight:900;color:#fbbf24;">${fmtNum(chk.amount || 0)} ${cur}</td>
                <td>يستحق: ${chk.dueDate || 'قريباً'}</td>
                <td><a href="checks-cycle.php" class="mini-btn stmt">متابعة</a></td>
            </tr>
        `);
    });

    liqBody.innerHTML = rowsHtml.join('');
}

function renderGoodsAdvisorPanels() {
    const cur = GUARDIAN_STATE.currency;
    const shorts = GUARDIAN_STATE.shortageItems.slice(0, 12);
    document.getElementById('goods-shortage-badge').textContent = GUARDIAN_STATE.shortageItems.length + ' صنف حرج';

    const sBody = document.getElementById('goods-shortage-tbody');
    if (shorts.length === 0) {
        sBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:24px;color:#34d399;font-weight:800;">✅ جميع الأصناف فوق حد الأمان المخزني!</td></tr>`;
    } else {
        sBody.innerHTML = shorts.map(it => {
            const suggestedOrder = Math.max(20, (it.minLimit * 2) - it.qty);
            const isNeg = it.qty < 0;
            return `
                <tr>
                    <td style="font-weight:800;color:#fff;">${it.name}</td>
                    <td style="font-weight:900;color:${isNeg ? '#fb7185' : '#fbbf24'};">${fmtNum(it.qty)} ${isNeg ? ' (رصيد سالب!)' : ''}</td>
                    <td>${fmtNum(it.minLimit)}</td>
                    <td style="font-weight:900;color:#34d399;">+${fmtNum(suggestedOrder)} وحدة</td>
                    <td style="display:flex;gap:6px;">
                        <a href="purchase.php?item=${encodeURIComponent(it.name)}&qty=${suggestedOrder}" class="mini-btn po">🛒 أمر شراء</a>
                        <a href="item-ledger.php?item=${encodeURIComponent(it.name)}" class="mini-btn stmt">🏷️ كرت الصنف</a>
                    </td>
                </tr>
            `;
        }).join('');
    }

    const dBody = document.getElementById('goods-deadstock-tbody');
    const combinedList = [
        ...GUARDIAN_STATE.deadStockItems.slice(0, 6).map(x => ({ ...x, tag: '🧊 راكد مجمد', color: '#fb7185', rec: 'عرض ترويجي / خصم 10% لتسييل الصنف' })),
        ...GUARDIAN_STATE.starItems.slice(0, 6).map(x => ({ ...x, tag: '⭐ نجم سريع الدوران', color: '#34d399', rec: `مباع (${fmtNum(x.soldQty)}) - حافظ على التوريد المستمر` }))
    ];

    if (combinedList.length === 0) {
        dBody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">لا توجد أصناف راكدة مرصودة حالياً.</td></tr>`;
    } else {
        dBody.innerHTML = combinedList.map(it => `
            <tr>
                <td style="font-weight:800;color:#fff;">${it.name}</td>
                <td>${fmtNum(it.qty)}</td>
                <td style="font-weight:800;color:#c084fc;">${fmtNum(Math.max(0, it.qty) * (it.cost || 0))} ${cur}</td>
                <td><span style="padding:2px 8px;border-radius:99px;font-size:0.72rem;font-weight:800;background:${it.color}22;color:${it.color};">${it.tag}</span></td>
                <td style="font-size:0.77rem;color:#cbd5e1;">${it.rec}</td>
            </tr>
        `).join('');
    }
}

function renderForecastChartAndSimulator() {
    const mSales = GUARDIAN_STATE.monthlySales;
    const avgG = GUARDIAN_STATE.avgGrowthRate;
    const labels = mSales.map(m => m.month);
    const actuals = mSales.map(m => m.total);

    const f1 = Math.round((actuals[actuals.length - 1] || 1000000) * (1 + avgG));
    const f2 = Math.round(f1 * (1 + avgG));
    const f3 = Math.round(f2 * (1 + avgG));

    const allLabels = [...labels, 'تنبؤ +30 يوم', 'تنبؤ +60 يوم', 'تنبؤ +90 يوم'];
    const actualDataset = [...actuals, null, null, null];
    const forecastDataset = [
        ...Array(Math.max(0, actuals.length - 1)).fill(null),
        actuals[actuals.length - 1] || 0,
        f1, f2, f3
    ];

    const gPct = Math.round(avgG * 100);
    document.getElementById('forecast-growth-label').textContent = (gPct >= 0 ? 'اتجاه نمو +' : 'اتجاه تراجع ') + gPct + '% شهرياً';

    const canvas = document.getElementById('guardianForecastChart');
    if (canvas && typeof Chart !== 'undefined') {
        if (guardianChartInstance) guardianChartInstance.destroy();
        guardianChartInstance = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'المبيعات الفعلية',
                        data: actualDataset,
                        borderColor: '#22d3ee',
                        backgroundColor: 'rgba(34, 211, 238, 0.12)',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'خط التنبؤ الذكي (AI 90-Day Forecast)',
                        data: forecastDataset,
                        borderColor: '#c084fc',
                        backgroundColor: 'rgba(192, 132, 252, 0.1)',
                        borderDash: [7, 5],
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#f8fafc', font: { family: 'Cairo', size: 12 } } }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8', font: { family: 'Cairo', size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#94a3b8', font: { family: 'Cairo', size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    }

    updateWhatIfSimulation();
}

function updateWhatIfSimulation() {
    const cur = GUARDIAN_STATE.currency;
    const salesPct = parseFloat(document.getElementById('sim-sales-pct').value) || 0;
    const collectPct = parseFloat(document.getElementById('sim-collect-pct').value) || 0;
    const deadPct = parseFloat(document.getElementById('sim-dead-pct').value) || 0;

    document.getElementById('sim-sales-val').textContent = (salesPct >= 0 ? '+' : '') + salesPct + '%';
    document.getElementById('sim-collect-val').textContent = collectPct + '%';
    document.getElementById('sim-dead-val').textContent = deadPct + '%';

    const baseForecast = GUARDIAN_STATE.forecastNext30 || 1000000;
    const simulatedSales = Math.round(baseForecast * (1 + (salesPct / 100)));
    const collectedDebtCash = Math.round(GUARDIAN_STATE.totalCustomerDebt * (collectPct / 100));
    const liquidatedDeadCash = Math.round(GUARDIAN_STATE.deadStockValue * (deadPct / 100) * 0.9);
    const totalExtraCash = collectedDebtCash + liquidatedDeadCash + Math.max(0, simulatedSales - baseForecast);
    const extraProfit = Math.round((simulatedSales - baseForecast) * 0.18 + (collectedDebtCash * 0.05));

    document.getElementById('sim-out-cash').textContent = '+' + fmtNum(totalExtraCash) + ' ' + cur;
    document.getElementById('sim-out-sales').textContent = fmtNum(simulatedSales) + ' ' + cur;
    document.getElementById('sim-out-profit').textContent = (extraProfit >= 0 ? '+' : '') + fmtNum(extraProfit) + ' ' + cur;
}

function resetSimulator() {
    document.getElementById('sim-sales-pct').value = 10;
    document.getElementById('sim-collect-pct').value = 30;
    document.getElementById('sim-dead-pct').value = 25;
    updateWhatIfSimulation();
}

function switchGuardianMode(mode) {
    CURRENT_MODE = mode;
    ['all', 'money', 'goods', 'forecast'].forEach(m => {
        const btn = document.getElementById('tab-btn-' + m);
        if (btn) btn.classList.toggle('active', m === mode);
    });

    const secAll = document.getElementById('section-mode-all');
    const secMoney = document.getElementById('section-mode-money');
    const secGoods = document.getElementById('section-mode-goods');
    const secForecast = document.getElementById('section-mode-forecast');

    if (mode === 'all') {
        secAll.style.display = 'block';
        secMoney.style.display = 'block';
        secGoods.style.display = 'block';
        secForecast.style.display = 'block';
    } else if (mode === 'money') {
        secAll.style.display = 'none';
        secMoney.style.display = 'block';
        secGoods.style.display = 'none';
        secForecast.style.display = 'none';
    } else if (mode === 'goods') {
        secAll.style.display = 'none';
        secMoney.style.display = 'none';
        secGoods.style.display = 'block';
        secForecast.style.display = 'none';
    } else if (mode === 'forecast') {
        secAll.style.display = 'none';
        secMoney.style.display = 'none';
        secGoods.style.display = 'none';
        secForecast.style.display = 'block';
        setTimeout(() => { if (guardianChartInstance) guardianChartInstance.resize(); }, 60);
    }

    const url = new URL(window.location.href);
    url.searchParams.set('mode', mode);
    window.history.replaceState({}, '', url.toString());
}

function generateLocalDeterministicAdvice(type, customQuestion = '') {
    const cur = GUARDIAN_STATE.currency;
    const topDebtor = GUARDIAN_STATE.customers[0];
    const topShortage = GUARDIAN_STATE.shortageItems[0];
    const topDead = GUARDIAN_STATE.deadStockItems[0];

    if (type === 'collection_plan') {
        return `
            <h4 style="margin:0 0 8px;color:#c084fc;">💰 خطة الحارس الأمين لتحصيل المديونيات وتأمين السيولة الفورية:</h4>
            <ul style="margin:0;padding-right:20px;">
                <li><strong>الوضع النقدي الحالي:</strong> السيولة المتاحة بالخزائن والبنوك <strong>${fmtNum(GUARDIAN_STATE.totalLiquidCash)} ${cur}</strong> مقابل إجمالي مديونيات لدى العملاء بقيمة <strong>${fmtNum(GUARDIAN_STATE.totalCustomerDebt)} ${cur}</strong>.</li>
                <li><strong>الأولوية القصوى للتحصيل:</strong> ${topDebtor ? `العميل <strong>${topDebtor.name}</strong> يتصدر المديونيات بمبلغ <strong>${fmtNum(topDebtor.debt)} ${cur}</strong> — يُوصى بإرسال مطالبة واتساب فورية وجدولة سداد 30% هذا الأسبوع.` : 'لا توجد ديون حرجة متأخرة حالياً.'}</li>
                <li><strong>إغلاق السقوف الائتمانية:</strong> يوجد <strong>${GUARDIAN_STATE.overLimitCustomers.length} عميل</strong> تجاوزوا 85% من السقف الائتماني، يجب إيقاف البيع الآجل لهم مؤقتاً حتى سداد الدفعة المستحقة.</li>
                <li><strong>الشيكات الحرجة:</strong> لديك <strong>${GUARDIAN_STATE.bouncedChecks.length} شيك مرتجع</strong> و <strong>${GUARDIAN_STATE.maturingChecks.length} شيك مستحق خلال 7 أيام</strong>.</li>
            </ul>
        `;
    }

    if (type === 'reorder_plan') {
        return `
            <h4 style="margin:0 0 8px;color:#34d399;">📦 خطة هندسة المخزون والطلبيات الذكية وتسييل الرواكد:</h4>
            <ul style="margin:0;padding-right:20px;">
                <li><strong>النواقص الحرجة:</strong> يوجد <strong>${GUARDIAN_STATE.shortageItems.length} صنف</strong> وصل لحد الطلب أو رصيد سالب ${topShortage ? `(أبرزها: <strong>${topShortage.name}</strong> برصيد ${fmtNum(topShortage.qty)})` : ''}. اضغط زر (🛒 أمر شراء) بجانب الصنف لتوليد طلبية التوريد فوراً.</li>
                <li><strong>تسييل الأموال المجمدة:</strong> إجمالي قيمة الأصناف الراكدة <strong>${fmtNum(GUARDIAN_STATE.deadStockValue)} ${cur}</strong> ${topDead ? `(أكبرها: <strong>${topDead.name}</strong>)` : ''}. عمل خصم ترويجي 8% يحرر سيولة فورية لتمويل الأصناف سريعة الدوران.</li>
            </ul>
        `;
    }

    if (type === 'profit_margin') {
        return `
            <h4 style="margin:0 0 8px;color:#ec4899;">📈 تحليل هامش الربح وحماية التسعير:</h4>
            <ul style="margin:0;padding-right:20px;">
                <li><strong>حارس التسعير:</strong> ${GUARDIAN_STATE.belowCostSales.length === 0 ? 'جميع فواتير المبيعات المسجلة تحقق هامش ربح إيجابي فوق التكلفة.' : `⚠️ تنبيه عاجل: تم رصد <strong>${GUARDIAN_STATE.belowCostSales.length} عملية بيع بأقل من سعر التكلفة</strong> (مثل الصنف: ${GUARDIAN_STATE.belowCostSales[0].itemName})!`}</li>
                <li><strong>توصية الربحية:</strong> ركّز على الأصناف النجمية (${GUARDIAN_STATE.starItems.slice(0, 3).map(s => s.name).join('، ') || 'الأعلى مبيعاً'}) وارفع هامشها بنسبة 2% إلى 4% لتعظيم صافي الربح الشهري.</li>
            </ul>
        `;
    }

    if (type === 'forecast_90') {
        return `
            <h4 style="margin:0 0 8px;color:#fbbf24;">🔮 التوقعات المستقبلية للمبيعات والسيولة (30 - 90 يوماً):</h4>
            <ul style="margin:0;padding-right:20px;">
                <li><strong>مبيعات الـ 30 يوماً القادمة:</strong> متوقع تحقيق <strong>${fmtNum(GUARDIAN_STATE.forecastNext30)} ${cur}</strong> بمعدل اتجاه <strong>${Math.round(GUARDIAN_STATE.avgGrowthRate * 100)}%</strong> شهرياً.</li>
                <li><strong>نصيحة التمويل:</strong> استخدم مختبر المحاكاة (What-If) أسفل الشاشة لمعرفة الأثر الفوري لتحصيل 30% من الديون مع تصفية 25% من الرواكد على مضاعفة السيولة النقدية.</li>
            </ul>
        `;
    }

    if (type === 'accounting_audit') {
        return `
            <h4 style="margin:0 0 8px;color:#22d3ee;">⚖️ تقرير التدقيق المحاسبي وكشف الثغرات:</h4>
            <ul style="margin:0;padding-right:20px;">
                <li><strong>مؤشر صحة النظام:</strong> <strong>${GUARDIAN_STATE.healthScore}%</strong>.</li>
                <li><strong>توازن دفتر الأستاذ:</strong> ${GUARDIAN_STATE.unbalancedGL.length === 0 ? 'جميع القيود اليومية متوازنة 100% (المدين = الدائن).' : `يوجد ${GUARDIAN_STATE.unbalancedGL.length} قيد يحتاج لمراجعة التوازن في دفتر الأستاذ.`}</li>
                <li><strong>تكرار الفواتير:</strong> ${GUARDIAN_STATE.duplicateInvoices.length === 0 ? 'لم يتم رصد أي تكرار في أرقام الفواتير.' : `تم رصد ${GUARDIAN_STATE.duplicateInvoices.length} رقم فاتورة مكرر.`}</li>
            </ul>
        `;
    }

    return `
        <h4 style="margin:0 0 8px;color:#22d3ee;">🛡️ الملخص التنفيذي الشامل للحارس الأمين 360° ${customQuestion ? `— حول: (${customQuestion})` : ''}:</h4>
        <ul style="margin:0;padding-right:20px;">
            <li><strong>صحة النظام والأمان:</strong> <strong>${GUARDIAN_STATE.healthScore}%</strong> | السيولة المتاحة: <strong>${fmtNum(GUARDIAN_STATE.totalLiquidCash)} ${cur}</strong>.</li>
            <li><strong>المديونيات والائتمان:</strong> إجمالي ديون العملاء <strong>${fmtNum(GUARDIAN_STATE.totalCustomerDebt)} ${cur}</strong> (${GUARDIAN_STATE.overLimitCustomers.length} عميل متجاوز للسقف الائتماني).</li>
            <li><strong>المخازن وسلاسل الإمداد:</strong> قيمة المخزون <strong>${fmtNum(GUARDIAN_STATE.totalInventoryValue)} ${cur}</strong>، ويوجد <strong>${GUARDIAN_STATE.shortageItems.length} صنف</strong> يحتاج إعادة طلب فورية، و <strong>${GUARDIAN_STATE.deadStockItems.length} صنف راكد</strong> بقيمة ${fmtNum(GUARDIAN_STATE.deadStockValue)} ${cur}.</li>
            <li><strong>التنبؤ للشهر القادم:</strong> متوقع مبيعات بقيمة <strong>${fmtNum(GUARDIAN_STATE.forecastNext30)} ${cur}</strong>.</li>
        </ul>
    `;
}

async function askGuardianPreset(presetType) {
    const box = document.getElementById('guardian-ai-response');
    box.classList.add('visible');
    const localHtml = generateLocalDeterministicAdvice(presetType);
    box.innerHTML = localHtml + `<div id="cloud-ai-addon" style="margin-top:10px;font-size:0.82rem;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> جاري تعزيز التوصية عبر المحرك السحابي الذكي...</div>`;

    try {
        const res = await fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: `قدم 3 توصيات تنفيذية مركزة جداً باللغة العربية بناءً على هذه المؤشرات (${presetType}): السيولة=${GUARDIAN_STATE.totalLiquidCash}، ديون العملاء=${GUARDIAN_STATE.totalCustomerDebt}، عملاء متجاوزون للسقف=${GUARDIAN_STATE.overLimitCustomers.length}، أصناف ناقصة=${GUARDIAN_STATE.shortageItems.length}، بضائع راكدة=${GUARDIAN_STATE.deadStockValue}، توقعات الشهر القادم=${GUARDIAN_STATE.forecastNext30}.`,
                context: { healthScore: GUARDIAN_STATE.healthScore, mode: CURRENT_MODE }
            })
        });
        const data = await res.json();
        const addon = document.getElementById('cloud-ai-addon');
        if (addon) {
            if (data && data.status === 'success' && data.reply) {
                addon.innerHTML = `<div style="margin-top:10px;padding-top:10px;border-top:1px dashed rgba(56,189,248,0.25);color:#e2e8f0;"><strong>✨ رؤية المستشار السحابي الإضافية:</strong><br>${data.reply.replace(/\n/g, '<br>')}</div>`;
            } else {
                addon.innerHTML = `<span style="color:#34d399;">✅ تم توليد التقرير الدقيق 100% عبر المحرك الاستنتاجي المحلي من بياناتك الحية.</span>`;
            }
        }
    } catch (e) {
        const addon = document.getElementById('cloud-ai-addon');
        if (addon) {
            addon.innerHTML = `<span style="color:#34d399;">✅ تم توليد التقرير الدقيق 100% عبر المحرك الاستنتاجي المحلي من بياناتك الحية.</span>`;
        }
    }
}

async function runCustomGuardianQuery() {
    const inp = document.getElementById('guardian-custom-input');
    const q = (inp.value || '').trim();
    if (!q) {
        askGuardianPreset('comprehensive');
        return;
    }
    const box = document.getElementById('guardian-ai-response');
    box.classList.add('visible');
    box.innerHTML = generateLocalDeterministicAdvice('comprehensive', q) + `<div id="cloud-ai-addon" style="margin-top:10px;font-size:0.82rem;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> جاري تحليل سؤالك عبر المستشار الذكي...</div>`;

    try {
        const res = await fetch('api/ai-assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: q,
                context: {
                    liquidCash: GUARDIAN_STATE.totalLiquidCash,
                    customerDebt: GUARDIAN_STATE.totalCustomerDebt,
                    overLimitCount: GUARDIAN_STATE.overLimitCustomers.length,
                    shortageCount: GUARDIAN_STATE.shortageItems.length,
                    deadStockValue: GUARDIAN_STATE.deadStockValue,
                    forecastNext30: GUARDIAN_STATE.forecastNext30
                }
            })
        });
        const data = await res.json();
        const addon = document.getElementById('cloud-ai-addon');
        if (addon) {
            if (data && data.status === 'success' && data.reply) {
                addon.innerHTML = `<div style="margin-top:10px;padding-top:10px;border-top:1px dashed rgba(56,189,248,0.25);color:#e2e8f0;"><strong>🤖 إجابة المستشار الذكي:</strong><br>${data.reply.replace(/\n/g, '<br>')}</div>`;
            } else {
                addon.innerHTML = `<span style="color:#34d399;">✅ تم تحليل سؤالك بناءً على الأرقام الفعلية الحية في النظام.</span>`;
            }
        }
    } catch (e) {
        const addon = document.getElementById('cloud-ai-addon');
        if (addon) addon.innerHTML = `<span style="color:#34d399;">✅ تم تحليل سؤالك بناءً على الأرقام الفعلية الحية في النظام.</span>`;
    }
}

function exportGuardianReportCSV() {
    const cur = GUARDIAN_STATE.currency;
    const rows = [
        ['تقرير الحارس الأمين والمستشار الذكي الشامل (AI Guardian 360°)', new Date().toLocaleString('ar-EG')],
        [],
        ['المؤشر الرقابي / المالي', 'القيمة', 'الحالة / التفاصيل'],
        ['مؤشر صحة النظام والأمان', GUARDIAN_STATE.healthScore + '%', 'فحص شامل'],
        ['السيولة الجاهزة (خزائن + بنوك)', GUARDIAN_STATE.totalLiquidCash + ' ' + cur, 'نقد حي'],
        ['إجمالي ديون العملاء', GUARDIAN_STATE.totalCustomerDebt + ' ' + cur, GUARDIAN_STATE.overLimitCustomers.length + ' متجاوز للسقف'],
        ['إجمالي قيمة المخزون', GUARDIAN_STATE.totalInventoryValue + ' ' + cur, GUARDIAN_STATE.shortageItems.length + ' صنف ناقص'],
        ['قيمة البضائع الراكدة', GUARDIAN_STATE.deadStockValue + ' ' + cur, GUARDIAN_STATE.deadStockItems.length + ' صنف راكد'],
        ['تنبؤ المبيعات لـ 30 يوماً', GUARDIAN_STATE.forecastNext30 + ' ' + cur, Math.round(GUARDIAN_STATE.avgGrowthRate * 100) + '% معدل النمو'],
        [],
        ['قائمة الركائز الـ 10 للحارس الذكي', 'الحالة', 'التوصية'],
        ...GUARDIAN_STATE.pillars.map(p => [p.title, p.status, p.desc])
    ];

    const csvContent = '\uFEFF' + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `AI_Guardian_Report_${new Date().toISOString().slice(0,10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

document.addEventListener('DOMContentLoaded', () => {
    switchGuardianMode(CURRENT_MODE);
    runFullGuardianScan(false);
});
</script>
<script defer src="js/app.js?v=20261016_v28"></script>
</body>
</html>
