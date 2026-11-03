<?php
// توجيه موحد إلى شاشة حركة مالية المعتمدة (single-cash-payment.php)
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: single-cash-payment.php' . ($qs ? '?' . $qs : ''));
exit;