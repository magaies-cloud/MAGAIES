<?php
require 'api/db.php';
$stmt = $pdo->prepare("SELECT u.*, t.subdomain, t.company_name, t.status, t.valid_until FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE u.username = 'C'");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
