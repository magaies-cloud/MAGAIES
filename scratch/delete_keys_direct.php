<?php
require 'c:/xampp/htdocs/ELSAJANA2/magaies/api/db.php';
try {
    $stmt = $pdo->prepare("DELETE FROM global_settings WHERE setting_key LIKE '%balances%' OR setting_key LIKE '%preset%'");
    $stmt->execute();
    echo "Deleted " . $stmt->rowCount() . " rows.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
