<?php
require 'api/db.php';
global $GLOBAL_TENANT_ID;
$pdo = getDBConnection();
$stmt=$pdo->query("SELECT count(*) FROM customers WHERE tenant_id = '{$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)");
echo $stmt->fetchColumn() . "\n";
