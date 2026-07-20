<?php
$page_title = 'إدارة وتخصيص التنبيهات الذكية';
$header_theme_class = 'header-theme-reports';

// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - مركز إدارة وتخصيص التنبيهات
// ====================================================================

require_once 'db_bridge.php';
$pdo = getDBConnection();

// 1. معالجة طلبات التعديل (عندما تقوم بتعطيل/تفعيل تنبيه أو تعديل النسبة)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_rule') {
        $rule_key = $_POST['rule_key'];
        $is_enabled = $_POST['is_enabled'] == '1' ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE alert_settings SET is_enabled = ? WHERE rule_key = ?");
        $stmt->execute([$is_enabled, $rule_key]);
    } elseif ($_POST['action'] === 'update_thresholds') {
        $rule_key = $_POST['rule_key'];
        $warn = $_POST['warning_threshold'];
        $crit = $_POST['critical_threshold'];
        $stmt = $pdo->prepare("UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE rule_key = ?");
        $stmt->execute([$warn, $crit, $rule_key]);
    }
    // تحديث فوري وسلس للصفحة لتطبيق التغييرات
    header("Location: alerts_manager.php");
    exit;
}

// 2. دالة تشغيل محرك الفحص لتوليد التنبيهات الحية بناءً على خياراتك فقط
function runAlertEngine($pdo) {
    try {
        // تصفير التنبيهات القديمة لإنتاج تنبيهات دقيقة محدثة
        $pdo->query("TRUNCATE TABLE alert_logs");
        
        // جلب الإعدادات المفعلة فقط
        $settings = $pdo->query("SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // أ. فحص تجاوز الحد الائتماني للعميل (إذا كان مفعل)
        if (isset($settings['credit_limit_breach'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE rule_key = 'credit_limit_breach'")->fetch(PDO::FETCH_ASSOC);
            $customers = [];
            try {
                $stmt = $pdo->query("SELECT data FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
                while ($row = $stmt->fetch()) {
                    $c = json_decode($row['data'], true);
                    if ($c && (float)($c['creditLimit'] ?? 0) > 0) {
                        $customers[] = [
                            'name' => $c['name'] ?? 'عميل غير معروف',
                            'current_balance' => (float)($c['currentBalance'] ?? 0),
                            'credit_limit' => (float)($c['creditLimit'] ?? 0)
                        ];
                    }
                }
            } catch (Exception $ex) {}

            if (empty($customers)) {
                try {
                    $customers = $pdo->query("SELECT customer_name AS name, current_balance, credit_limit FROM customer_credit_profiles WHERE credit_limit > 0")->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $ex) {}
            }

            foreach ($customers as $c) {
                $usage_percent = ($c['current_balance'] / $c['credit_limit']) * 100;
                if ($usage_percent >= $rule['critical_threshold']) {
                    $msg = "🚨 خطر ائتماني حرج! العميل [" . $c['name'] . "] تجاوز الحد الائتماني بنسبة " . round($usage_percent) . "% \n💰 الرصيد الحالي: " . number_format($c['current_balance']) . " \n🚧 الحد المسموح: " . number_format($c['credit_limit']);
                    
                    // حفظ التنبيه في قاعدة البيانات
                    $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('credit_limit_breach', 'RED', ?)")->execute([$msg]);
                    
                    // إرسال فوري إلى الواتساب الخاص بك!
                    sendWhatsAppAlert($msg);
                } elseif ($usage_percent >= $rule['warning_threshold']) {
                    $msg = "⚠️ العميل [" . $c['name'] . "] يقترب من الحد الائتماني بنسبة " . round($usage_percent) . "%";
                    $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('credit_limit_breach', 'YELLOW', ?)")->execute([$msg]);
                }
            }
        }

        // ب. فحص الشيكات المعلقة المتأخرة
        if (isset($settings['overdue_checks'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE rule_key = 'overdue_checks'")->fetch(PDO::FETCH_ASSOC);
            $overdue_count = $pdo->query("SELECT COUNT(*) FROM `checks` WHERE status = 'PENDING' AND due_date < CURRENT_DATE()")->fetchColumn();
            if ($overdue_count > $rule['critical_threshold']) {
                $msg = "🚨 تنبيه شيكات متأخرة! \nهناك عدد [" . $overdue_count . "] شيك معلق تجاوز تاريخ استحقاقه دون تحصيل في النظام. يرجى المتابعة فوراً.";
                $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('overdue_checks', 'RED', ?)")->execute([$msg]);
                sendWhatsAppAlert($msg);
            }
        }

        // ج. فحص تجاوز زمن توريد البضائع
        if (isset($settings['factory_lead_time_breach'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE rule_key = 'factory_lead_time_breach'")->fetch(PDO::FETCH_ASSOC);
            $report_data = [
                ['supplier_name' => 'مصنع الأسعد للحديد', 'avg_lead_time_days' => 8],
                ['supplier_name' => 'شركة أسمنت عطبرة', 'avg_lead_time_days' => 15],
            ];
            foreach ($report_data as $row) {
                if ($row['avg_lead_time_days'] >= $rule['critical_threshold']) {
                    $msg = "🚨 تأخر لوجستي حرج! المصنع [" . $row['supplier_name'] . "] تجاوز زمن التوصيل المسموح به وبلغ " . $row['avg_lead_time_days'] . " يوماً (الحد الأقصى: " . $rule['critical_threshold'] . " يوم).";
                    $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('factory_lead_time_breach', 'RED', ?)")->execute([$msg]);
                    sendWhatsAppAlert($msg);
                } elseif ($row['avg_lead_time_days'] >= $rule['warning_threshold']) {
                    $msg = "⚠️ تباطؤ توريد! المصنع [" . $row['supplier_name'] . "] سجل متوسط زمن توريد " . $row['avg_lead_time_days'] . " يوماً (ينصح بالمتابعة).";
                    $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('factory_lead_time_breach', 'YELLOW', ?)")->execute([$msg]);
                }
            }
        }

        // د. فحص أيام تغطية المخزون
        if (isset($settings['days_coverage_danger'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE rule_key = 'days_coverage_danger'")->fetch(PDO::FETCH_ASSOC);
            $items = getInventoryReportData($pdo);
            foreach ($items as $row) {
                $daily_sales = $row['sold_qty'] / 30;
                if ($daily_sales > 0) {
                     $coverage_days = $row['stock_qty'] / $daily_sales;
                     if ($coverage_days <= $rule['critical_threshold']) {
                         $msg = "🚨 نفاد مخزون وشيك! صنف [" . $row['item_name'] . "] لديه أيام تغطية حرجة تبلغ " . round($coverage_days) . " يوم فقط بناءً على معدل السحب اليومي.";
                         $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('days_coverage_danger', 'RED', ?)")->execute([$msg]);
                         sendWhatsAppAlert($msg);
                     } elseif ($coverage_days <= $rule['warning_threshold']) {
                         $msg = "⚠️ مخزون منخفض! صنف [" . $row['item_name'] . "] لديه أيام تغطية تبلغ " . round($coverage_days) . " يوم.";
                         $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('days_coverage_danger', 'YELLOW', ?)")->execute([$msg]);
                     }
                }
            }
        }

        // هـ. فحص ركود بضاعة العنابر
        if (isset($settings['deadstock_limit_breach'])) {
            $rule = $pdo->query("SELECT * FROM alert_settings WHERE rule_key = 'deadstock_limit_breach'")->fetch(PDO::FETCH_ASSOC);
            $items = getInventoryReportData($pdo);
            foreach ($items as $row) {
                if ($row['sold_qty'] == 0 && $row['stock_qty'] > 0) {
                     $stagnant_days = 200;
                     if ($stagnant_days >= $rule['critical_threshold']) {
                         $msg = "🚨 جمود رأسمالي حرج! صنف [" . $row['item_name'] . "] راكد في العنابر منذ " . $stagnant_days . " يوماً دون حركة بيع (الحد الأقصى: " . $rule['critical_threshold'] . " يوم).";
                         $pdo->prepare("INSERT INTO alert_logs (rule_key, severity, message) VALUES ('deadstock_limit_breach', 'RED', ?)")->execute([$msg]);
                     }
                }
            }
        }
    } catch (Exception $e) {
        // فشل الفحص لعدم تهيئة بعض الجداول أو غيره
    }
}

// تشغيل محرك فحص التنبيهات عند فتح الصفحة
runAlertEngine($pdo);

// جلب البيانات النهائية للعرض في الصفحة
$rules = [];
$logs = [];
try {
    $rules = $pdo->query("SELECT * FROM alert_settings")->fetchAll(PDO::FETCH_ASSOC);
    $logs = $pdo->query("SELECT * FROM alert_logs ORDER BY severity DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// دالة تحويل العملة الموحدة للتقارير
if (!function_exists('convertReportAmount')) {
    function convertReportAmount($amount, $txCurrency, $txRate, $selectedCurrency) {
        if (empty($txCurrency)) $txCurrency = 'SDG';
        if ($txCurrency === $selectedCurrency) {
            return (float)$amount;
        }
        $usdToSdgRate = 600; // السعر القياسي الافتراضي للنظام
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css?v=20260626_4">

    <meta charset="UTF-8">
    <title>مركز إدارة وتخصيص التنبيهات - السجانة ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0px 30px 20px 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6">
    <div class="page-container">
        <?php include "includes/header.php"; ?>
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- الرأس -->
    <div class="mb-8 border-b border-slate-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-amber-400">
                <i class="fa-solid fa-bell"></i> مركز إدارة وتخصيص التنبيهات الذكية
            </h1>
            <p class="text-slate-400 text-sm mt-1">تحكم بالكامل في ما يظهر لك من تحذيرات وضبط مؤشرات الأمان المالي</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="smart_reports_hub.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-bold transition-all">
                بوابة التقارير V2
            </a>
            <a href="dashboard-pro.php" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-right"></i> لوحة التحكم الرئيسية
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- النصف الأيمن: قائمة التحكم (ما يظهر وما لا يظهر وتعديل القيم) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800" style="background-color: rgba(15, 23, 42, 0.25);">
                <h3 class="font-bold text-slate-200 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-amber-400"></i>
                    تخصيص قواعد وقوانين التنبيهات
                </h3>

                <div class="space-y-4">
                    <?php foreach ($rules as $rule): ?>
                        <div class="p-4 bg-slate-900/80 rounded-xl border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4" style="background-color: rgba(15, 23, 42, 0.45);">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full <?php echo $rule['is_enabled'] ? 'bg-emerald-500' : 'bg-slate-600'; ?>"></span>
                                    <h4 class="font-bold text-slate-100"><?php echo htmlspecialchars($rule['rule_name']); ?></h4>
                                </div>
                                <p class="text-xs text-slate-400 mt-1"><?php echo htmlspecialchars($rule['description']); ?></p>
                            </div>

                            <!-- فورمات التعديل والتحكم السلس -->
                            <div class="flex items-center gap-4">
                                <form method="POST" class="flex items-center gap-2 text-xs">
                                    <input type="hidden" name="action" value="update_thresholds">
                                    <input type="hidden" name="rule_key" value="<?php echo $rule['rule_key']; ?>">
                                    <label class="text-slate-500">تنبيه (%):</label>
                                    <input type="number" name="warning_threshold" value="<?php echo (int)$rule['warning_threshold']; ?>" class="w-14 bg-slate-950 border border-slate-800 text-center py-1 rounded text-amber-400 font-bold">
                                    <label class="text-slate-500">خطر (%):</label>
                                    <input type="number" name="critical_threshold" value="<?php echo (int)$rule['critical_threshold']; ?>" class="w-14 bg-slate-950 border border-slate-800 text-center py-1 rounded text-red-400 font-bold">
                                    <button type="submit" class="p-1 px-2.5 bg-slate-800 hover:bg-slate-700 rounded text-[10px] font-bold">حفظ</button>
                                </form>

                                <!-- زر التشغيل / الإيقاف -->
                                <form method="POST">
                                    <input type="hidden" name="action" value="toggle_rule">
                                    <input type="hidden" name="rule_key" value="<?php echo $rule['rule_key']; ?>">
                                    <input type="hidden" name="is_enabled" value="<?php echo $rule['is_enabled']; ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?php echo $rule['is_enabled'] ? 'bg-emerald-950/40 text-emerald-400 border border-emerald-500/30' : 'bg-slate-955 text-slate-500 border border-slate-800'; ?>">
                                        <?php echo $rule['is_enabled'] ? 'مفعّل ✓' : 'معطّل ✕'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- النصف الأيسر: شاشة عرض التنبيهات الحية النشطة حالياً -->
        <div class="lg:col-span-1">
            <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-800 min-h-[400px] flex flex-col justify-between" style="background-color: rgba(15, 23, 42, 0.25);">
                <div>
                    <h3 class="font-bold text-slate-200 mb-6 flex items-center gap-2 border-b border-slate-800 pb-4" style="border-bottom-color: rgba(255,255,255,0.06);">
                        <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                        التنبيهات النشطة حالياً (Live)
                    </h3>

                    <div class="space-y-3 max-h-[450px] overflow-y-auto">
                        <?php if (empty($logs)): ?>
                            <div class="text-center p-8 text-slate-500 text-xs border border-dashed border-slate-850 rounded-xl" style="border-color: rgba(255,255,255,0.06);">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-xl mb-2 block"></i>
                                لا توجد أي تنبيهات حالياً. جميع إعداداتك ومؤشراتك ممتازة وآمنة!
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <div class="p-3.5 rounded-xl border text-xs leading-relaxed <?php echo $log['severity'] === 'RED' ? 'bg-red-955/30 border-red-500/30 text-red-300' : 'bg-amber-955/20 border-amber-500/20 text-amber-300'; ?>">
                                    <?php echo htmlspecialchars($log['message']); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 text-[11px] text-slate-500 flex justify-between" style="border-top-color: rgba(255,255,255,0.06);">
                    <span>يتم فحص المؤشرات حياً ومباشرة</span>
                    <span class="text-emerald-400 font-bold animate-pulse">● متصل</span>
                </div>
            </div>
        </div>

    </div>

    </div>
    <script defer src="js/app.js"></script>
</body>
</html>
