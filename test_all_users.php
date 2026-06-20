<?php require 'api/db.php'; $stmt = $pdo->query('SELECT id, tenant_id, username, role, data FROM users'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); ?>
