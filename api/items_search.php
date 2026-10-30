<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
require_once __DIR__ . '/db.php';

$tenant_id = resolveCurrentTenantId($pdo);
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $stmt = $pdo->prepare('SELECT data FROM items WHERE tenant_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)');
    $stmt->execute([$tenant_id]);
    $items = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['data'])) {
            $data = json_decode($row['data'], true);
            if (is_array($data) && empty($data['deleted'])) {
                if ($q !== '') {
                    $name = mb_strtolower($data['name'] ?? '');
                    $bc = mb_strtolower($data['barcode'] ?? '');
                    $qLower = mb_strtolower($q);
                    $matches = (strpos($name, $qLower) !== false) || (strpos($bc, $qLower) !== false);
                    if (!$matches && !empty($data['units']) && is_array($data['units'])) {
                        foreach ($data['units'] as $u) {
                            if (!empty($u['barcode']) && strpos(mb_strtolower($u['barcode']), $qLower) !== false) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                    if (!$matches) continue;
                }
                
                $items[] = $data;
            }
        }
    }
    
    echo json_encode($items, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
