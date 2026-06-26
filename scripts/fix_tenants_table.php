<?php
require __DIR__ . '/../api/db.php';
try {
    $pdo->exec("ALTER TABLE tenants ADD COLUMN phone2 VARCHAR(255) DEFAULT NULL;");
    echo "Added phone2\n";
} catch(Exception $e){}
try {
    $pdo->exec("ALTER TABLE tenants ADD COLUMN max_users INT DEFAULT 5;");
    echo "Added max_users\n";
} catch(Exception $e){}
try {
    $pdo->exec("ALTER TABLE tenants ADD COLUMN plan_id INT DEFAULT 1;");
    echo "Added plan_id\n";
} catch(Exception $e){}
try {
    $pdo->exec("ALTER TABLE tenants ADD COLUMN receipt_path VARCHAR(255) DEFAULT NULL;");
    echo "Added receipt_path\n";
} catch(Exception $e){}
echo "Columns checked/added successfully.\n";
