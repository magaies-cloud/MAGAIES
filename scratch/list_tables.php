<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();
foreach (['items', 'inventory_items', 'inventory'] as $tbl) {
    echo "--- $tbl ---\n";
    $colStmt = $pdo->query("DESCRIBE `$tbl`");
    foreach($colStmt->fetchAll(PDO::FETCH_COLUMN) as $col) {
        echo "  - $col\n";
    }
}
?>
