<?php
// Unified into inventory-movement.php (حركات البضائع وأرصدة المستودعات)
$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
header('Location: inventory-movement.php' . $qs);
exit;