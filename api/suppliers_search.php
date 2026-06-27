<?php
require_once 'db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([]);
    exit;
}

$q = $_GET['q'] ?? '';
$q = trim(strtolower($q));

try {
    if (empty($q)) {
        // Return first 20 if no query
        $sql = "SELECT data FROM suppliers ORDER BY id DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT data FROM suppliers WHERE LOWER(data) LIKE :q ORDER BY id DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':q', "%$q%");
        $stmt->execute();
    }

    $results = [];
    while ($row = $stmt->fetch()) {
        $data = json_decode($row['data'], true);
        if ($data) {
            $results[] = [
                'id' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'ledger' => floatval($data['currentBalance'] ?? 0),
                'creditLimit' => floatval($data['creditLimit'] ?? 0),
                'phone' => $data['phone'] ?? '',
                'code' => $data['code'] ?? ''
            ];
        }
    }
    
    echo json_encode($results);
} catch (Exception $e) {
    error_log('Supplier Search API Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
