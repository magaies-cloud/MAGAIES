<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - تهيئة جداول الحسابات والمعاملات والشيكات
// ====================================================================

// تضمين اتصال قاعدة البيانات الرئيسي للنظام
require_once __DIR__ . '/api/db.php';

try {
    // 1. إنشاء جدول العملاء والموردين (accounts)
    $sql1 = "CREATE TABLE IF NOT EXISTS accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT chk_accounts_type CHECK (type IN ('CUSTOMER', 'SUPPLIER'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql1);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [accounts] بنجاح.</div>";

    // 2. إنشاء جدول حركة المخزون (inventory_items)
    $sql2 = "CREATE TABLE IF NOT EXISTS inventory_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql2);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [inventory_items] بنجاح.</div>";

    // 3. إنشاء جدول الفواتير والحركات المالية (transactions)
    $sql3 = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        item_id INT,
        quantity DECIMAL(15, 2),
        unit_price DECIMAL(15, 2),
        actual_cost DECIMAL(15, 2),
        total_amount DECIMAL(15, 2),
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
        FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql3);
    echo "<div style='color: green; font-family: Cairo, sans-serif; margin-bottom: 10px;'>✓ تم إنشاء جدول [transactions] بنجاح.</div>";

    // 4. إنشاء جدول الشيكات وحركتها (checks)
    $sql4 = "CREATE TABLE IF NOT EXISTS `checks` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT,
        amount DECIMAL(15, 2) NOT NULL,
        due_date DATE NOT NULL,
        type VARCHAR(10),
        status VARCHAR(20),
        CONSTRAINT chk_checks_type CHECK (type IN ('INBOUND', 'OUTBOUND')),
        CONSTRAINT chk_checks_status CHECK (status IN ('PENDING', 'CLEARED', 'BOUNCED')),
        FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql4);
    echo "<div style='color: green; font-family: Cairo, sans-serif;'>✓ تم إنشاء جدول [checks] بنجاح.</div>";

} catch (\PDOException $e) {
    echo "<div style='color: red; font-family: Cairo, sans-serif;'>❌ فشل تهيئة الجداول: " . $e->getMessage() . "</div>";
}
?>
