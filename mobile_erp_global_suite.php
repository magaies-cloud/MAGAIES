<?php
require_once __DIR__ . '/db_bridge.php';
require_once __DIR__ . '/mobile_auth_guard.php';

$pdo = getDBConnection();
$authUser = requireMobileAuth($pdo, false);

require_once __DIR__ . '/mobile_erp_global_suite.html';
