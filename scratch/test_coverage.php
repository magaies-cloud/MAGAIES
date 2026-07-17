<?php
require_once __DIR__ . '/../db_bridge.php';
$pdo = getDBConnection();

// Fetch all items
$stmt = $pdo->query("SELECT * FROM items WHERE is_deleted = 0");
$items_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all purchase invoices
$stmt = $pdo->query("SELECT * FROM purchase_invoices WHERE is_deleted = 0 AND status_val = 'confirmed'");
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sales invoices
$stmt = $pdo->query("SELECT * FROM sales_invoices WHERE is_deleted = 0");
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$report_data = [];

foreach ($items_rows as $item_row) {
    $item_data = json_decode($item_row['data'], true);
    if (!$item_data) continue;
    
    $item_id = $item_data['id'] ?? '';
    $item_name = $item_data['name'] ?? 'بدون اسم';
    $opening_qty = (float)($item_data['openingQty'] ?? 0);
    
    // Calculate purchased quantity
    $purchased_qty = 0;
    foreach ($purchases as $p) {
        $p_data = json_decode($p['data'], true);
        if ($p_data && isset($p_data['items'])) {
            foreach ($p_data['items'] as $item) {
                $p_item_id = $item['itemId'] ?? ($item['id'] ?? '');
                if ($p_item_id === $item_id) {
                    $purchased_qty += (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                }
            }
        }
    }
    
    // Calculate sold quantity and average daily sales (last 30 days)
    $sold_qty = 0;
    $sold_last_30_days = 0;
    $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
    
    foreach ($sales as $s) {
        $s_data = json_decode($s['data'], true);
        if ($s_data) {
            $status = $s['status_val'] ?? ($s_data['status'] ?? '');
            $s_date = $s['date_val'] ?? ($s_data['date'] ?? '');
            
            $items_list = $s_data['items'] ?? [];
            foreach ($items_list as $item) {
                $s_item_id = $item['itemId'] ?? ($item['id'] ?? '');
                if ($s_item_id === $item_id) {
                    $qty = (float)($item['quantity'] ?? ($item['qty'] ?? 0));
                    
                    if ($status === 'delivered') {
                        $sold_qty += $qty;
                    }
                    
                    if ($s_date >= $thirty_days_ago && $status === 'delivered') {
                        $sold_last_30_days += $qty;
                    }
                }
            }
        }
    }
    
    $current_stock = $opening_qty + $purchased_qty - $sold_qty;
    $avg_daily_sales = $sold_last_30_days / 30.0;
    
    $days_coverage = 999;
    if ($avg_daily_sales > 0) {
        $days_coverage = round($current_stock / $avg_daily_sales);
    }
    
    $report_data[] = [
        'item_name' => $item_name,
        'stock_qty' => $current_stock,
        'avg_daily_sales' => $avg_daily_sales,
        'days_coverage' => $days_coverage
    ];
}

echo json_encode($report_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
