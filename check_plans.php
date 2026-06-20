<?php
require 'api/db.php';
$stmt = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key='saas_plans'");
print_r($stmt->fetch());
