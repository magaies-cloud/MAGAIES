<?php
require_once __DIR__ . '/api/db.php';

try {
    $stmt = $pdo->query("SELECT id, data FROM users WHERE data IS NOT NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;
    
    foreach ($users as $u) {
        $data = json_decode($u['data'], true);
        if (isset($data['password'])) {
            unset($data['password']);
            $newData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $pdo->prepare("UPDATE users SET data = ? WHERE id = ?")->execute([$newData, $u['id']]);
            $count++;
        }
    }
    
    echo "Cleaned $count users' plaintext passwords from JSON data.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
