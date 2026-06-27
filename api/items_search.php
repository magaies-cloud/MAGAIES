<?php
header('Content-Type: application/json');
require 'db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $stmt = $pdo->query('SELECT data FROM items');
    $items = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $data = json_decode($row['data'], true);
            if (is_array($data)) {
                // If search query is provided, filter by name
                if ($q !== '') {
                    $name = mb_strtolower($data['name'] ?? '');
                    if (mb_strpos($name, mb_strtolower($q)) === false) {
                        continue;
                    }
                }
                
                $items[] = $data;
            }
        }
    }
    
    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
