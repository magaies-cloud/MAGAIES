<?php require 'api/db.php'; $pdo->exec('UPDATE users SET data = NULL WHERE id = 2'); echo 'Fixed local DB'; ?>
