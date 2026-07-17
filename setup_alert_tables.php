<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تهيئة جداول التنبؤ والإنذار المبكر
// ====================================================================

// تضمين اتصال قاعدة البيانات الرئيسي للنظام
require_once __DIR__ . '/api/db.php';

try {
    // 1. إنشاء جدول إعدادات التنبيهات (alert_settings)
    $sql1 = "CREATE TABLE IF NOT EXISTS alert_settings (
        rule_key VARCHAR(50) PRIMARY KEY,
        rule_name VARCHAR(100) NOT NULL,
        description TEXT,
        is_enabled TINYINT(1) DEFAULT 1, -- 1 = مفعل، 0 = معطل
        warning_threshold DECIMAL(15, 2) DEFAULT 0.00,
        critical_threshold DECIMAL(15, 2) DEFAULT 0.00
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql1);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [alert_settings] بنجاح.</div>";

    // 2. إنشاء جدول سجل التنبيهات النشطة (alert_logs)
    $sql2 = "CREATE TABLE IF NOT EXISTS alert_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_key VARCHAR(50),
        severity VARCHAR(10) CHECK (severity IN ('YELLOW', 'RED')),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql2);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [alert_logs] بنجاح.</div>";

    // 3. إدخال الإعدادات الافتراضية
    $sql3 = "INSERT INTO alert_settings (rule_key, rule_name, description, is_enabled, warning_threshold, critical_threshold) VALUES
    ('credit_limit_breach', 'تجاوز الحد الائتماني للعميل', 'تنبيه عند اقتراب العميل من سقف مديونيته المسموح بها', 1, 80.00, 95.00),
    ('debt_concentration', 'تركيز مديونية السوق', 'تنبيه إذا كان عميل واحد يستحوذ على نسبة كبيرة من مبيعات الآجل', 1, 15.00, 25.00),
    ('bounced_checks', 'الشيكات المرتجعة', 'تنبيه أحمر فوري عند ارتداد أي شيك من البنك', 1, 1.00, 1.00),
    ('overdue_checks', 'شيكات معلقة متأخرة', 'تنبيه عند وجود شيكات مؤجلة تجاوزت تاريخ استحقاقها دون تحصيل', 1, 3.00, 7.00),
    ('price_dumping', 'البيع بأقل من سعر التكلفة', 'تنبيه عند قيام موظف ببيع بضاعة بربح يقارب الصفر أو بخسارة', 1, 2.00, 0.00)
    ON DUPLICATE KEY UPDATE rule_name=VALUES(rule_name);";
    
    $pdo->exec($sql3);
    echo "<div style='color: green; font-family: Cairo, sans-serif;'>✓ تم إدخال الإعدادات الافتراضية للتنبيهات بنجاح.</div>";

} catch (\PDOException $e) {
    echo "<div style='color: red; font-family: Cairo, sans-serif;'>❌ فشل تهيئة جداول التنبيهات: " . $e->getMessage() . "</div>";
}
?>
