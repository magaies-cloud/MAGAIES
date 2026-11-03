<?php
// تم توحيد دفتر الأستاذ العام المطور V2 مع النسخة الرئيسية الشاملة (general-ledger.php)
$qs = $_SERVER['QUERY_STRING'] ?? '';
header("Location: general-ledger.php" . ($qs ? "?" . $qs : ""));
exit;