<?php
// Unified into inventory-adjustment.php?tab=transfer (إذن تحويل ومناقلة بضاعة بين المستودعات)
$qs = !empty($_SERVER['QUERY_STRING']) ? ('&' . $_SERVER['QUERY_STRING']) : '';
header('Location: inventory-adjustment.php?tab=transfer' . $qs);
exit;