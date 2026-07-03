<?php
$env = parse_ini_file('.env');
if (!$env) {
    die("Cannot read .env file");
}

$host = $env['DB_HOST'];
$port = $env['DB_PORT'];
$db   = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Empty the accounts table
    $stmt = $pdo->prepare("DELETE FROM accounts");
    $stmt->execute();
    $accountsDeleted = $stmt->rowCount();
    
    echo "Deleted $accountsDeleted rows from accounts table.\n";
    
} catch (\PDOException $e) {
    echo "DB Connection Error: " . $e->getMessage();
}
?>
