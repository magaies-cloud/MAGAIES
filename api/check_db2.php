<?php
require 'db.php';
$stmt = $pdo->query("SELECT id, subdomain, company_name, max_users FROM tenants");
echo "TENANTS:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("SELECT id, tenant_id, username, role, status FROM users");
echo "USERS:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

