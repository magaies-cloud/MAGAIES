<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=elsajana2_local", "root", "");
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM global_settings");
    while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $r['setting_key'] . "\n" . $r['setting_value'] . "\n---\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
