<?php
/**
 * Redirect: single-cash-receipt.php → single-cash-payment.php
 * هذا الملف يقوم بتحويل جميع الروابط القديمة لسند القبض
 * إلى شاشة سند الصرف/القبض الجديدة الموحدة
 */

// نقل جميع المعاملات (parameters) كما هي
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// إضافة معامل type=receipt لتعريف الشاشة الجديدة أنه سند قبض
if (!empty($queryString)) {
    if (strpos($queryString, 'type=') === false) {
        $queryString .= '&type=receipt';
    }
    header("Location: single-cash-payment.php?" . $queryString);
} else {
    header("Location: single-cash-payment.php?type=receipt");
}
exit;
?>
