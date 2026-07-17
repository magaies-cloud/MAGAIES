<?php
require_once __DIR__ . '/../api/db.php';
try {
    $stmt = $pdo->query("SELECT data FROM sales_invoices LIMIT 3");
    while ($row = $stmt->fetch()) {
        echo $row['data'] . "\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
