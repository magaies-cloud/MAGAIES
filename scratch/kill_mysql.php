<?php
require 'c:/xampp/htdocs/ELSAJANA2/magaies/api/db.php';
try {
    $stmt = $pdo->query("SHOW PROCESSLIST");
    $processes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($processes as $process) {
        if ($process['Time'] > 10 && $process['User'] != 'system user') {
            echo "Killing process " . $process['Id'] . "\n";
            $pdo->query("KILL " . $process['Id']);
        }
    }
    echo "Done killing processes.\n";
    
    // Now delete
    $stmt = $pdo->prepare("DELETE FROM global_settings WHERE setting_key LIKE '%balances%' OR setting_key LIKE '%preset%'");
    $stmt->execute();
    echo "Deleted " . $stmt->rowCount() . " rows.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
