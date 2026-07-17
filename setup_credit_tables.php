<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تهيئة جداول الائتمان والشيكات
// ====================================================================

// تضمين اتصال قاعدة البيانات الرئيسي للنظام
require_once __DIR__ . '/api/db.php';

try {
    // 1. إنشاء جدول تفاصيل الحد الائتماني وحالة العميل (customer_credit_profiles)
    $sql1 = "CREATE TABLE IF NOT EXISTS customer_credit_profiles (
        customer_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        credit_limit DECIMAL(15, 2) DEFAULT 0.00,
        current_balance DECIMAL(15, 2) DEFAULT 0.00,
        risk_level VARCHAR(10) DEFAULT 'GREEN',
        last_payment_date TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT chk_risk_level CHECK (risk_level IN ('GREEN', 'YELLOW', 'RED'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql1);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [customer_credit_profiles] بنجاح.</div>";

    // 2. إنشاء جدول الشيكات المؤجلة ومتابعة التحصيل (checks_velocity)
    $sql2 = "CREATE TABLE IF NOT EXISTS checks_velocity (
        check_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        check_number VARCHAR(50) NOT NULL,
        amount DECIMAL(15, 2) NOT NULL,
        due_date DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'PENDING',
        type VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT chk_status CHECK (status IN ('PENDING', 'CLEARED', 'BOUNCED')),
        CONSTRAINT chk_type CHECK (type IN ('INBOUND', 'OUTBOUND')),
        FOREIGN KEY (customer_id) REFERENCES customer_credit_profiles(customer_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql2);
    echo "<div style='color: green; font-family: Cairo, sans-serif;'>✓ تم إنشاء جدول [checks_velocity] بنجاح.</div>";

} catch (\PDOException $e) {
    echo "<div style='color: red; font-family: Cairo, sans-serif;'>❌ فشل تهيئة الجداول: " . $e->getMessage() . "</div>";
}
?>
