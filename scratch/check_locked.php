<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=elsajana2_local", "root", "");
    $stmt = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key='layout_balances_report_user_presets_locked'");
    $res = $stmt->fetchColumn();
    echo "LOCKED PRESETS:\n" . $res;
} catch (Exception $e) {
    echo $e->getMessage();
}
