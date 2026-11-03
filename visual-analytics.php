<?php
// Unified into inventory-analytics.php (التحليلات المرئية وتقييم ABC)
$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
header('Location: inventory-analytics.php' . $qs);
exit;