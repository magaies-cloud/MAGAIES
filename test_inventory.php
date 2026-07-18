<?php
require_once 'db_bridge.php';
$pdo = getDBConnection();
$data = getInventoryReportData($pdo);
print_r($data);
