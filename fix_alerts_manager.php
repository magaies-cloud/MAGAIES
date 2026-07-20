<?php
$f = file_get_contents('alerts_manager.php');

// 1. Fix TRUNCATE
$f = str_replace(
    '$pdo->query("TRUNCATE TABLE alert_logs");',
    'global $GLOBAL_TENANT_ID; $pdo->prepare("DELETE FROM alert_logs WHERE tenant_id = ?")->execute([$GLOBAL_TENANT_ID]);',
    $f
);

// 2. Fix SELECTs
$f = str_replace(
    'SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1',
    'SELECT rule_key, is_enabled FROM alert_settings WHERE is_enabled = 1 AND tenant_id = \'{$GLOBAL_TENANT_ID}\'',
    $f
);
$f = str_replace(
    'SELECT * FROM alert_settings WHERE rule_key =',
    'SELECT * FROM alert_settings WHERE tenant_id = \'{$GLOBAL_TENANT_ID}\' AND rule_key =',
    $f
);
$f = str_replace(
    'SELECT * FROM alert_settings',
    'SELECT * FROM alert_settings WHERE tenant_id = \'{$GLOBAL_TENANT_ID}\'',
    $f
);
$f = str_replace(
    'SELECT * FROM alert_logs ORDER',
    'SELECT * FROM alert_logs WHERE tenant_id = \'{$GLOBAL_TENANT_ID}\' ORDER',
    $f
);

// 3. Fix UPDATEs
$f = str_replace(
    'UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE rule_key = ?',
    'UPDATE alert_settings SET warning_threshold = ?, critical_threshold = ? WHERE tenant_id = \'{$GLOBAL_TENANT_ID}\' AND rule_key = ?',
    $f
);

// 4. Fix INSERTs
$f = str_replace(
    'INSERT INTO alert_logs (rule_key, severity, message) VALUES (',
    'INSERT INTO alert_logs (tenant_id, rule_key, severity, message) VALUES (\'{$GLOBAL_TENANT_ID}\', ',
    $f
);

file_put_contents('alerts_manager.php', $f);
echo "alerts_manager.php updated!\n";
