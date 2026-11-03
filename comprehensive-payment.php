<?php
// توجيه موحد إلى شاشة الصرف والقبض المجمع المعتمدة (multi-cash-payment.php)
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: multi-cash-payment.php' . ($qs ? '?' . $qs : ''));
exit;