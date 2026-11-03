<?php
// Unified Bridge to Sales Delivery Screen (sales-delivery.php)
$qs = $_SERVER['QUERY_STRING'] ?? '';
header('Location: sales-delivery.php' . ($qs ? '?' . $qs : ''));
exit;