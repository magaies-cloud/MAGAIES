<?php
require 'api/db.php';
$stmt = $pdo->query('SELECT id, company_name, subdomain FROM tenants');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
