<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مركز إدارة وتخصيص التنبيهات V2
// تم التحديث لتهيئة القواعد وتوليد التنبيهات الحية من البيانات واستغلال الشاشة بالكامل
// ====================================================================
$page_title = 'إدارة وتخصيص التنبيهات الذكية';
$header_theme_class = 'header-theme-reports';

require_once 'db_bridge.php';
$selected_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
$pdo = getDBConnection();

// -------------------------------------------------------------
// 1. التحرّي وتأكيد وجود جداول التنبيهات في قاعدة البيانات لجميع المستأجرين
// -------------------------------------------------------------
function ensureAlertTablesExist($pdo, $tenant_id) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            rule_name VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT '',
            category VARCHAR(50) DEFAULT 'GENERAL',
            is_enabled TINYINT(1) DEFAULT 1,
            warning_threshold FLOAT DEFAULT 80,
            critical_threshold FLOAT DEFAULT 100,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY tenant_rule (tenant_id, rule_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS alert_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id VARCHAR(100) NOT NULL,
            rule_key VARCHAR(100) NOT NULL,
            severity VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // زرع القواعد الافتراضية إذا لم تكن موجودة للمستأجر الحالي
        $count = $pdo->query("SELECT COUNT(*) FROM alert_settings WHERE tenant_id = '{$tenant_id}'")->fetchColumn();
        if ($count == 0) {
            $defaultRules = [
                ['credit_limit_breach', 'تجاوز الحد الائتماني للعملاء', 'تنبيه عند اقتراب أو تجاوز العميل لسقف الائتمان المسموح', 'CREDIT', 1, 80, 100],
                ['overdue_checks', 'تأخر استحقاق الشيكات البنكية', 'مراقبة الشيكات المعلقة التي تجاوزت تاريخ الاستحقاق دون تحصيل', 'FINANCE', 1, 1, 3],
                ['days_coverage_danger', 'خطورة نفاد كميات الأصناف', 'تحذير عند انخفاض أيام تغطية المخزون بناءً على معدل السحب', 'INVENTORY', 1, 7, 3],
                ['deadstock_limit_breach', 'ركود البضاعة في المخازن', 'إنذار عن البضائع المخزنة لفترات طويلة دون حركة بيع', 'INVENTORY', 1, 90, 180],
                ['factory_lead_time_breach', 'تأخر وتباطؤ توريد الموردين', 'مراقبة تأخر استلام طلبات الشراء من المصانع والموردين', 'SUPPLY', 1, 7, 14],
                ['low_stock_level', 'الأصناف دون حد الطلب الأدنى', 'تنبيه فوري للأصناف التي وصلت لكميات حرجة بالمخزن', 'INVENTORY', 1, 10, 5]
            ];
            $stmt = $pdo->prepare("INSERT INTO alert_settings (tenant_id, rule_key, rule_name, description, category, is_enabled, warning_threshold, critical_threshold) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($defaultRules as $r) {
                $stmt->execute([$tenant_id, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]]);
            }
        }
    } catch (Exception $e) {
        error_log("Error initializing alert tables: " . $e->getMessage());
    }
}

ensureAlertTablesExist($pdo, $GLOBAL_TENANT_ID);

// -------------------------------------------------------------
// 2. معالجة طلبات التعديل (POST)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_rule') {
        $rule_key = $_POST['rule_key'];
        $is_enabled = $_POST['is_enabled'] == '1' ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$is_enabled, $GLOBAL_TENANT_ID, $rule_key]);
    } elseif ($_POST['action'] === 'update_thresholds') {
        $rule_key = $_POST['rule_key'];
        $warn = (float)$_POST['warning_threshold'];
        $crit = (float)$_POST['critical_threshold'];
        $stmt = $pdo->prepare("UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE tenant_id = ? AND rule_key = ?");
        $stmt->execute([$warn, $crit, $GLOBAL_TENANT_ID, $rule_key]);
    }
    header("Location: alerts_manager.php");
    exit;
}

// -------------------------------------------------------------
// 3. محرك فحص البيانات الحية وتوليد الإنذارات والتنبيهات
// -------------------------------------------------------------
function runAlertEngine($pdo) {
    global $GLOBAL_TENANT_ID;
    try {
        // تصفير سجلات التنبيهات المؤقتة القديمة لإنتاج تنبيهات محدثة ومطابقة للحالة الحالية
        $pdo->prepare("DELETE FROM alert_logs WHERE tenant_id = ?")->execute([$GLOBAL_TENANT_ID]);
        
        $settings = $pdo->query("SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1 AND tenant_id = '{$GLOBAL_TENANT_ID}'")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // أ. فحص تجاوز الحد الائتماني للعملاء
        if (isset($settings['credit_limit_breach'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND rule_key = 'credit_limit_breach'")->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                    while ($row = $stmt->fetch()) {
                        $c = json_decode($row['data'], true);
                        if ($c && (float)($c['creditLimit'] ?? 0) > 0) {
                            $name = $c['name'] ?? 'عميل غير معروف';
                            $bal = (float)($c['currentBalance'] ?? 0);
                            $limit = (float)($c['creditLimit'] ?? 0);
                            $pct = ($bal / $limit) * 100;
                            
                            if ($pct >= $rule['critical_threshold']) {
                                $msg = "🚨 خطر ائتماني حرج! العميل [" . $name . "] تجاوز الحد الائتماني بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . " / الحد: " . number_format($limit, 2) . ")";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'credit_limit_breach', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            } elseif ($pct >= $rule['warning_threshold']) {
                                $msg = "⚠️ تنبيه ائتماني! العميل [" . $name . "] اقترب من سقف الائتمان المسموح بنسبة " . round($pct) . "% (الرصيد: " . number_format($bal, 2) . ")";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'credit_limit_breach', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            }
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

        // ب. فحص الشيكات المعلقة المتأخرة
        if (isset($settings['overdue_checks'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND rule_key = 'overdue_checks'")->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->query("SELECT data FROM `checks` WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                    $overdue_count = 0;
                    while ($row = $stmt->fetch()) {
                        $ch = json_decode($row['data'], true);
                        if ($ch && ($ch['status'] ?? '') === 'PENDING') {
                            $due = $ch['dueDate'] ?? ($ch['due_date'] ?? '');
                            if ($due && $due < date('Y-m-d')) {
                                $overdue_count++;
                            }
                        }
                    }
                    if ($overdue_count >= $rule['critical_threshold']) {
                        $msg = "🚨 تحذير مالي! يوجد عدد [" . $overdue_count . "] شيك معلق متأخر عن تاريخ الاستحقاق ولم يُحصّل حتى الآن.";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'overdue_checks', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    } elseif ($overdue_count >= $rule['warning_threshold']) {
                        $msg = "⚠️ تنبيه شيكات! يوجد عدد [" . $overdue_count . "] شيك حان تاريخ استحقاقه.";
                        $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'overdue_checks', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                    }
                } catch (Exception $ex) {}
            }
        }

        // ج. فحص كميات أصناف المخزون المنخفضة (Low Stock Level)
        if (isset($settings['low_stock_level'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND rule_key = 'low_stock_level'")->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->query("SELECT data FROM items WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                    while ($row = $stmt->fetch()) {
                        $item = json_decode($row['data'], true);
                        if ($item) {
                            $name = $item['name'] ?? 'صنف غير معروف';
                            $stock = (float)($item['stockQuantity'] ?? ($item['quantity'] ?? ($item['qty'] ?? 0)));
                            $minStock = (float)($item['minStock'] ?? 10);

                            if ($stock <= $rule['critical_threshold'] || $stock <= 0) {
                                $msg = "🚨 نفاد مخزون حرج! الصنف [" . $name . "] متبقي منه (" . $stock . ") وحدة فقط في العنابر.";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'low_stock_level', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            } elseif ($stock <= $rule['warning_threshold'] || $stock <= $minStock) {
                                $msg = "⚠️ مخزون منخفض! الصنف [" . $name . "] وصل إلى حد الطلب الأدنى (المتبقي: " . $stock . " وحدة).";
                                $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'low_stock_level', 'YELLOW', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                            }
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

        // د. فحص أيام تغطية المخزون لركود الأصناف (Deadstock)
        if (isset($settings['deadstock_limit_breach'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND rule_key = 'deadstock_limit_breach'")->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                try {
                    $stmt = $pdo->query("SELECT data FROM items WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                    while ($row = $stmt->fetch()) {
                        $item = json_decode($row['data'], true);
                        if ($item && !empty($item['isStagnant'])) {
                            $name = $item['name'] ?? 'صنف غير معروف';
                            $msg = "🚨 جمود رأسمالي! الصنف [" . $name . "] راكد بالمخازن دون أي حركة سحب بيعية منذ فترة طويلة.";
                            $pdo->prepare("INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (?, 'deadstock_limit_breach', 'RED', ?)")->execute([$GLOBAL_TENANT_ID, $msg]);
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

    } catch (Exception $e) {
        error_log("Error running alert engine: " . $e->getMessage());
    }
}

runAlertEngine($pdo);

// جلب البيانات النهائية لخط القواعد وسجل التنبيهات
$rules = [];
$logs = [];
try {
    $rules = $pdo->query("SELECT * FROM alert_settings WHERE tenant_id = '{$GLOBAL_TENANT_ID}' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $logs = $pdo->query("SELECT * FROM alert_logs WHERE tenant_id = '{$GLOBAL_TENANT_ID}' ORDER BY severity DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// حساب مؤشرات KPI
$red_count = 0; $yellow_count = 0; $enabled_rules = 0;
foreach ($logs as $l) {
    if (($l['severity'] ?? '') === 'RED') $red_count++;
    else $yellow_count++;
}
foreach ($rules as $r) {
    if ($r['is_enabled']) $enabled_rules++;
}

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600;
        $rate = (float)$txRate;
        if ($rate <= 0) {
            $rate = $usdToSdgRate;
        }
        if ($txCurrency === 'USD' && $selectedCurrency === 'SDG') {
            return (float)$amount * $rate;
        } else if ($txCurrency === 'SDG' && $selectedCurrency === 'USD') {
            return (float)$amount / $rate;
        }
        return (float)$amount;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<script src="js/lang.js"></script>
<script>
(function(){
    var l = localStorage.getItem("erp_lang") || "ar";
    if(l==="en") {
        document.documentElement.setAttribute("dir","ltr");
        document.documentElement.setAttribute("lang","en");
    }
})();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز إدارة وتخصيص التنبيهات | Al-Sajana ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260723_v14">

    <link rel="stylesheet" href="css/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #04020a;
            --panel-bg: rgba(14, 8, 28, 0.75);
            --border-neon: rgba(239, 68, 68, 0.25);
            --text-main: #ffffff;
            --text-muted: #a0aec0;
        }

        :root.light-theme {
            --bg-dark: #f8fafc;
            --panel-bg: #ffffff;
            --border-neon: #000000;
            --text-main: #000000;
            --text-muted: #334155;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Cairo', 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        /* ── استغلال العرض الكامل للشاشة بالتساوي يميناً ويساراً ── */
        .alerts-master-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 12px 12px 60px 12px !important;
            box-sizing: border-box !important;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ── كروت الإحصائيات V2 ── */
        .stat-card-v2 {
            background: rgba(15, 23, 42, 0.7);
            border: 2px solid var(--border-glow, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        :root.light-theme .stat-card-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06) !important;
        }

        .stat-card-v2:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px var(--border-glow, rgba(239, 68, 68, 0.25));
        }

        .stat-card-v2::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, var(--bar-color, #ef4444), transparent);
        }

        /* ── اللوحات الفخمة V2 ── */
        .glass-panel-v2 {
            background: var(--panel-bg);
            border: 2px solid var(--border-neon);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        :root.light-theme .glass-panel-v2 {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            color: #000000 !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06) !important;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .anim { animation: fadeInUp 0.5s ease-out backwards; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .alerts-master-wrapper { padding: 0 !important; }
            .glass-panel-v2, .stat-card-v2 { border: 1px solid #000 !important; box-shadow: none !important; }
        }
    </style>
</head>

<body class="no-page-transition">

    <div class="alerts-master-wrapper">
        
        <?php include "includes/header.php"; ?>

        <!-- 🌟 ACTION CONTROLS & NAVIGATION BAR 🌟 -->
        <div class="flex flex-wrap items-center justify-between gap-4 no-print bg-slate-900/80 dark:bg-slate-900/90 border-2 border-rose-500/30 p-5 rounded-2xl shadow-xl backdrop-blur-xl">
            <div>
                <a href="smart_reports_hub.php" onclick="window.location.href='smart_reports_hub.php'; return false;" class="inline-flex items-center gap-2 text-rose-400 font-extrabold hover:underline text-sm mb-1.5">
                    <i class="fa-solid fa-arrow-right"></i> ↩️ رجوع لمركز التقارير المحاسبية المتقدمة V2
                </a>
                <h1 class="text-2xl md:text-3xl font-black flex items-center gap-3">
                    <i class="fa-solid fa-bell text-rose-400"></i>
                    مركز إدارة وتخصيص التنبيهات والإنذارات الذكية
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1 font-bold">تخصيص قواعد الأمان المالي والمخزني، ومراقبة التنبيهات الحية الناتجة من قاعدة البيانات وتطبيقات النظام.</p>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="window.print();" class="bg-rose-600 hover:bg-rose-500 text-white font-black px-5 py-2.5 rounded-xl text-sm transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> طباعة تقرير التنبيهات
                </button>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY CARDS (Full Width 4 Grid) 📊 -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- 1. Critical Alerts Count -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(239, 68, 68, 0.4); --bar-color: #ef4444; animation-delay:.1s">
                <span class="text-xs text-rose-400 font-bold block">🚨 الإنذارات الحرجـة النشطة</span>
                <span class="text-3xl font-black text-rose-400 mt-2 block" id="kpi-red-count"><?php echo $red_count; ?></span>
                <span class="text-xs text-rose-400/80 mt-1 block">تتطلب إجراءً فورياً ومتابعة</span>
            </div>

            <!-- 2. Warning Alerts Count -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(245, 158, 11, 0.4); --bar-color: #f59e0b; animation-delay:.2s">
                <span class="text-xs text-amber-400 font-bold block">⚠️ التحذيرات والتنبيهات المتوسطة</span>
                <span class="text-3xl font-black text-amber-400 mt-2 block" id="kpi-yellow-count"><?php echo $yellow_count; ?></span>
                <span class="text-xs text-amber-400/80 mt-1 block">تنبيهات للمتابعة الروتينية</span>
            </div>

            <!-- 3. Enabled Alert Rules -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(16, 185, 129, 0.4); --bar-color: #10b981; animation-delay:.3s">
                <span class="text-xs text-emerald-400 font-bold block">⚙️ القواعد المفعلة بالنظام</span>
                <span class="text-3xl font-black text-emerald-400 mt-2 block"><?php echo $enabled_rules; ?> / <?php echo count($rules); ?></span>
                <span class="text-xs text-emerald-400/80 mt-1 block">قوانين مراقبة حية نشطة</span>
            </div>

            <!-- 4. System Monitoring Status -->
            <div class="anim stat-card-v2" style="--border-glow: rgba(56, 189, 248, 0.4); --bar-color: #38bdf8; animation-delay:.4s">
                <span class="text-xs text-sky-400 font-bold block">📡 حالة المراقبة والربط</span>
                <span class="text-xl font-black text-sky-400 mt-2 block flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span> متصل ونشط
                </span>
                <span class="text-xs text-slate-500 mt-1 block">فحص حظري لبيانات DB + LocalStorage</span>
            </div>
        </div>

        <!-- 📈 MAIN CONTENT SECTION: RULES CONFIG (2/3) + LIVE ALERTS PANEL (1/3) 📈 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- 1. Rules Configuration List (2 Cols Wide) -->
            <div class="lg:col-span-2 anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.5s">
                <div>
                    <div class="flex items-center justify-between mb-5 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-rose-400"></i>
                            تخصيص وشروط قوانين الإنذارات والتنبيهات الذكية
                        </h3>
                        <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-3 py-1 rounded-lg border border-rose-500/20">
                            <?php echo count($rules); ?> قوانين مفعلة
                        </span>
                    </div>

                    <div class="space-y-4">
                        <?php if (empty($rules)): ?>
                            <div class="text-center p-6 text-slate-400 font-bold">جاري تهيئة قواعد التنبيهات الافتراضية...</div>
                        <?php else: ?>
                            <?php foreach ($rules as $rule): ?>
                                <div class="p-4 bg-slate-900/80 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full <?php echo $rule['is_enabled'] ? 'bg-emerald-500' : 'bg-slate-600'; ?>"></span>
                                            <h4 class="font-bold text-slate-100 text-base"><?php echo htmlspecialchars($rule['rule_name']); ?></h4>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1 font-bold"><?php echo htmlspecialchars($rule['description'] ?? ''); ?></p>
                                    </div>

                                    <!-- Forms for Updating Thresholds and Toggling Rules -->
                                    <div class="flex flex-wrap items-center gap-3">
                                        <form method="POST" class="flex items-center gap-2 text-xs">
                                            <input type="hidden" name="action" value="update_thresholds">
                                            <input type="hidden" name="rule_key" value="<?php echo $rule['rule_key']; ?>">
                                            <label class="text-slate-400 font-bold">تنبيه:</label>
                                            <input type="number" step="any" name="warning_threshold" value="<?php echo (float)$rule['warning_threshold']; ?>" class="w-16 bg-slate-950 border border-slate-700 text-center py-1 rounded text-amber-400 font-black outline-none focus:border-amber-400">
                                            <label class="text-slate-400 font-bold">خطر:</label>
                                            <input type="number" step="any" name="critical_threshold" value="<?php echo (float)$rule['critical_threshold']; ?>" class="w-16 bg-slate-950 border border-slate-700 text-center py-1 rounded text-rose-400 font-black outline-none focus:border-rose-400">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-black transition">حفظ</button>
                                        </form>

                                        <form method="POST">
                                            <input type="hidden" name="action" value="toggle_rule">
                                            <input type="hidden" name="rule_key" value="<?php echo $rule['rule_key']; ?>">
                                            <input type="hidden" name="is_enabled" value="<?php echo $rule['is_enabled']; ?>">
                                            <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-black transition <?php echo $rule['is_enabled'] ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-400 border border-slate-700'; ?>">
                                                <?php echo $rule['is_enabled'] ? 'مفعّل ✓' : 'معطّل ✕'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Live Active Alerts Panel (1 Col Wide) -->
            <div class="anim glass-panel-v2 flex flex-col justify-between" style="animation-delay:.6s">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-3">
                        <h3 class="text-lg font-black text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                            التنبيهات والإنذارات الحية المباشرة
                        </h3>
                    </div>

                    <div class="space-y-3 max-h-[500px] overflow-y-auto" id="live-alerts-container">
                        <?php if (empty($logs)): ?>
                            <div class="text-center p-6 text-slate-400 text-xs border border-dashed border-slate-800 rounded-xl" id="no-alerts-placeholder">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-2xl mb-2 block"></i>
                                جميع إعدادات ومؤشرات النظام آمنة وممتازة حالياً.
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): 
                                $isRed = ($log['severity'] ?? '') === 'RED';
                                $bgCls = $isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
                            ?>
                                <div class="p-3.5 rounded-xl border text-xs font-bold leading-relaxed <?php echo $bgCls; ?>">
                                    <?php echo nl2br(htmlspecialchars($log['message'])); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 font-bold flex justify-between">
                    <span>فحص وتدقيق البيانات الحية</span>
                    <span class="text-emerald-400 font-black animate-pulse">● مراقبة مستمرة</span>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500 font-bold mt-2 no-print">
            آخر تحديث للحسابات: <?php echo date('Y-m-d H:i'); ?> · Al-Sajana Smart ERP 2026
        </div>

    </div>

    <!-- Client-side LocalStorage Alert Engine Sync Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('live-alerts-container');
        const placeholder = document.getElementById('no-alerts-placeholder');
        
        let clientAlerts = [];

        // 1. Scan LocalStorage Items for Low Stock
        const localItems = JSON.parse(localStorage.getItem('erp_items_v1')) || [];
        localItems.forEach(it => {
            const name = it.name || 'صنف غير معروف';
            const stock = parseFloat(it.stockQuantity || it.quantity || it.qty || 0);
            const minStock = parseFloat(it.minStock || 10);

            if (stock <= 3) {
                clientAlerts.push({
                    severity: 'RED',
                    msg: `🚨 كمية مخزون حرجة جداً! الصنف [${name}] المتبقي منه بالمستودع (${stock}) وحدة فقط.`
                });
            } else if (stock <= minStock) {
                clientAlerts.push({
                    severity: 'YELLOW',
                    msg: `⚠️ تنبيه حد الطلب! الصنف [${name}] وصل لمستوى المخزون الأدنى (المتبقي: ${stock} وحدة).`
                });
            }
        });

        // 2. Scan LocalStorage Customers for Credit Limits
        const localCusts = JSON.parse(localStorage.getItem('erp_customers_v1')) || [];
        localCusts.forEach(c => {
            const name = c.name || 'عميل';
            const bal = parseFloat(c.currentBalance || 0);
            const limit = parseFloat(c.creditLimit || 0);

            if (limit > 0) {
                const pct = (bal / limit) * 100;
                if (pct >= 100) {
                    clientAlerts.push({
                        severity: 'RED',
                        msg: `🚨 تجاوز حد ائتماني! العميل [${name}] تجاوز سقفه الائتماني بنسبة ${Math.round(pct)}% (الرصيد: ${bal.toLocaleString()}).`
                    });
                } else if (pct >= 80) {
                    clientAlerts.push({
                        severity: 'YELLOW',
                        msg: `⚠️ يقترب من الحد الائتماني! العميل [${name}] استهلك ${Math.round(pct)}% من حد الائتمان.`
                    });
                }
            }
        });

        // Append Client Alerts if any exist
        if (clientAlerts.length > 0) {
            if (placeholder) placeholder.style.display = 'none';

            let extraHtml = '';
            let redAdd = 0;
            let yellowAdd = 0;

            clientAlerts.forEach(a => {
                const isRed = a.severity === 'RED';
                if (isRed) redAdd++; else yellowAdd++;
                const bgCls = isRed ? 'bg-rose-950/40 border-rose-500/40 text-rose-200' : 'bg-amber-950/40 border-amber-500/40 text-amber-200';
                extraHtml += `<div class="p-3.5 rounded-xl border text-xs font-bold leading-relaxed ${bgCls}">${a.msg}</div>`;
            });

            container.insertAdjacentHTML('afterbegin', extraHtml);

            // Update KPI counters dynamically
            const redKpi = document.getElementById('kpi-red-count');
            const yellowKpi = document.getElementById('kpi-yellow-count');
            if (redKpi) redKpi.textContent = parseInt(redKpi.textContent || '0') + redAdd;
            if (yellowKpi) yellowKpi.textContent = parseInt(yellowKpi.textContent || '0') + yellowAdd;
        }
    });
    </script>
    <script defer src="js/app.js"></script>
</body>
</html>
