<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, data FROM items");
$items = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data = json_decode($row['data'], true);
    $items[] = [
        'id' => $data['id'] ?? '',
        'name' => $data['name'] ?? '',
        'category' => $data['category'] ?? '',
        'minLimit' => $data['minLimit'] ?? 0,
        'reorderLevel' => $data['reorderLevel'] ?? 0,
        'maxLimit' => $data['maxLimit'] ?? 0,
        'sellingPrice' => $data['sellingPrice'] ?? 0,
        'units' => $data['units'] ?? []
    ];
}
echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
