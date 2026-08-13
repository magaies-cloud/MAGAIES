<?php
$token = 'TEST'; // Need a valid token. Or just test public endpoint size limit.
// Let's find a valid token from DB.
$pdo = new PDO('mysql:host=localhost;dbname=magaees;charset=utf8mb4', 'root', '');
$stmt = $pdo->query("SELECT token FROM users LIMIT 1");
$token = $stmt->fetchColumn();

if (!$token) die("No token found");

$payload = [
    'erp_purchase_invoices_v1' => array_fill(0, 1000, ['id' => 'P123', 'invoiceNo' => 'P123', 'date' => '2026-08-05'])
];
$json = json_encode($payload);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer $token\r\n",
        'content' => $json
    ]
];
$context = stream_context_create($opts);
$result = file_get_contents('http://localhost/magaees/api_index.php/sync/push', false, $context);
echo "Response:\n";
echo $result;
?>
