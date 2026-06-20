<?php require_once 'api/db.php'; $stmt=$pdo->query('SELECT id, username, data FROM users'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); ?>
