<?php
$pdo = new PDO('mysql:host=localhost;dbname=magaees', 'root', '');
echo "Items: " . $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn() . "\n";
echo "Sales: " . $pdo->query('SELECT COUNT(*) FROM sales_invoices')->fetchColumn() . "\n";
echo "Purchases: " . $pdo->query('SELECT COUNT(*) FROM purchase_invoices')->fetchColumn() . "\n";
