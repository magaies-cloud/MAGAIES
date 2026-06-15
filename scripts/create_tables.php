<?php
require "api/db.php";

$tables = ["customers", "suppliers", "items", "inventory", "sales_invoices", "purchase_invoices", "receipts", "payments", "journal_vouchers", "settings", "warehouses", "banks", "categories", "roles", "checks", "gl_entries", "single_receipts", "single_payments", "partners", "employees", "payroll", "reserved_sales", "delivery_notes", "accounts"];

$pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    company_name VARCHAR(255), 
    subdomain VARCHAR(255), 
    owner_name VARCHAR(255), 
    owner_phone VARCHAR(255), 
    owner_email VARCHAR(255), 
    status VARCHAR(50) DEFAULT 'active', 
    valid_until DATETIME, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    tenant_id INT, 
    username VARCHAR(255), 
    password VARCHAR(255), 
    role VARCHAR(50), 
    status VARCHAR(50) DEFAULT 'active', 
    data JSON, 
    token VARCHAR(255), 
    token_expires_at DATETIME, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);");

foreach($tables as $t) { 
    $pdo->exec("CREATE TABLE IF NOT EXISTS $t (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        tenant_id INT, 
        data JSON, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
    );"); 
}

// Create default admin user if none exists
$stmt = $pdo->query("SELECT id FROM tenants LIMIT 1");
$tenant_id = $stmt->fetchColumn();

if (!$tenant_id) {
    $pdo->exec("INSERT INTO tenants (company_name, subdomain, owner_name, owner_phone, owner_email, valid_until) 
                VALUES ('Default Company', 'default', 'Admin', '000', 'admin@example.com', '2030-12-31 23:59:59')");
    $tenant_id = $pdo->lastInsertId();
}

$stmt = $pdo->query("SELECT id FROM users WHERE username = 'admin'");
$user_id = $stmt->fetchColumn();

if (!$user_id) {
    $hashed = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (tenant_id, username, password, role) VALUES (?, 'admin', ?, 'admin')")
        ->execute([$tenant_id, $hashed]);
}

echo "Tables created successfully and admin user seeded. Username: admin, Password: 123456";
?>
