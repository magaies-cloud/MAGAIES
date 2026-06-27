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
                if ($q !== '') {
                    $name = mb_strtolower($data['name'] ?? '');
                    // Smart filter: match from the beginning of ANY word in the item name
                    $qLower = mb_strtolower($q);
                    if (!preg_match('/(^|\s)' . preg_quote($qLower, '/') . '/u', $name)) {
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
