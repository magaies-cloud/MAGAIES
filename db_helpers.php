<?php
// ====================================================================
// نظام Al-Sajana Smart ERP 2026 - ملف جسر الربط الموحد مع قاعدة البيانات الحقيقية
// ====================================================================

// 1. إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'magaees'); // اسم قاعدة البيانات الخاصة بك
define('DB_USER', 'root');     // اسم المستخدم الافتراضي
define('DB_PASS', '');         // كلمة المرور الافتراضية

function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // تفعيل الاستعلامات السريعة والمخزنة مؤقتاً لسرعة فائقة
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        // المحاولة باستخدام ملف الاتصال الافتراضي للنظام إذا كان متوفراً
        try {
            @include_once __DIR__ . '/api/db.php';
            if (isset($pdo)) {
                return $pdo;
            }
        } catch (Exception $ex) {}
        
        error_log("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
        die("<div style='background:#7f1d1d; color:#fecaca; padding:15px; text-align:center; font-family:sans-serif; direction:rtl;'>⚠️ عذراً، فشل الاتصال التلقائي بقاعدة البيانات الحقيقية للمنظومة. تأكد من تشغيل خادم MySQL الخاص بك.</div>");
    }
}

// 2. دالة جلب السيولة اللحظية الموزعة (بين كل بنك وكل خزنة كاش)
function getRealTimeLiquidity($pdo) {
    try {
        // استعلام سريع لحساب الأرصدة الحالية في الخزن والبنوك من جدول الحسابات المباشر
        $query = "
            SELECT 
                name AS vault_name, 
                COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = accounts.id), 0) AS amount,
                CASE 
                    WHEN name LIKE '%بنك%' THEN '#3b82f6' -- أزرق للبنوك
                    WHEN name LIKE '%خزنة%' THEN '#10b981' -- أخضر للخزن الكاش
                    ELSE '#8b5cf6'
                END AS color
            FROM accounts 
            WHERE type = 'BANK' OR type = 'VAULT' OR type = 'CUSTOMER' OR type = 'SUPPLIER'
            ORDER BY amount DESC
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// 3. دالة جلب مديونيات الأرصدة الشاملة للعملاء (رصيد الأستاذ + الشيكات المؤجلة الحقيقية)
function getCustomerExposureData($pdo) {
    try {
        // استعلام ذكي مدمج ومفهرس يربط العملاء بشيكاتهم المعلقة في السيرفر دون تكرار
        $query = "
            SELECT 
                a.id AS customer_id,
                a.name AS customer_name,
                COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) AS ledger_balance,
                COALESCE((SELECT SUM(c.amount) FROM `checks` c WHERE c.account_id = a.id AND c.type = 'INBOUND' AND c.status = 'PENDING'), 0) AS pending_checks_total,
                (COALESCE((SELECT SUM(t.total_amount) FROM transactions t WHERE t.account_id = a.id), 0) + COALESCE((SELECT SUM(c.amount) FROM `checks` c WHERE c.account_id = a.id AND c.type = 'INBOUND' AND c.status = 'PENDING'), 0)) AS total_exposure
            FROM accounts a
            WHERE a.type = 'CUSTOMER'
            GROUP BY a.id, a.name
            ORDER BY total_exposure DESC
        ";
        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
