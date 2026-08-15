<?php
$pdo = new PDO('mysql:host=localhost;dbname=magaees;charset=utf8mb4', 'root', '');
print_r($pdo->query('DESCRIBE saas_tickets')->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query('DESCRIBE saas_ticket_replies')->fetchAll(PDO::FETCH_ASSOC));
