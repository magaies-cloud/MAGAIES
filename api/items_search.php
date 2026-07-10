<?php
header('Content-Type: application/json');

// Verify auth token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
if (empty($token)) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
require_once __DIR__ . '/db.php';
$stmtAuth = $pdo->prepare('SELECT tenant_id FROM users WHERE token = ?');
$stmtAuth->execute([$token]);
$authUser = $stmtAuth->fetch(PDO::FETCH_ASSOC);
if (!$authUser) { http_response_code(401); echo json_encode(['error' => 'Invalid token']); exit; }
$tenant_id = $authUser['tenant_id'];

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $stmt = $pdo->prepare('SELECT data FROM items WHERE tenant_id = ?');
    $stmt->execute([$tenant_id]);
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
