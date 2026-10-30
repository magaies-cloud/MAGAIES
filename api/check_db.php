<?php
if (php_sapi_name() !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Forbidden');
}
require 'db.php';
$stmt = $pdo->query("SELECT id, setting_key, updated_at FROM global_settings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
