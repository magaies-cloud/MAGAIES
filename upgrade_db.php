<?php
// Script to automatically upgrade the live database schema
require_once 'api/db.php';

try {
    $db = getDB();
    
    // Check if base_currency exists in tenants table
    $stmt = $db->query("SHOW COLUMNS FROM tenants LIKE 'base_currency'");
    $hasBaseCurrency = $stmt->rowCount() > 0;
    
    if (!$hasBaseCurrency) {
        $db->exec("ALTER TABLE tenants ADD COLUMN base_currency VARCHAR(10) DEFAULT 'SDG'");
        echo "✅ Column 'base_currency' added successfully to 'tenants' table.<br>";
    } else {
        echo "ℹ️ Column 'base_currency' already exists.<br>";
    }
    
    // Check if foreign_currency exists in tenants table
    $stmt = $db->query("SHOW COLUMNS FROM tenants LIKE 'foreign_currency'");
    $hasForeignCurrency = $stmt->rowCount() > 0;
    
    if (!$hasForeignCurrency) {
        $db->exec("ALTER TABLE tenants ADD COLUMN foreign_currency VARCHAR(10) DEFAULT 'USD'");
        echo "✅ Column 'foreign_currency' added successfully to 'tenants' table.<br>";
    } else {
        echo "ℹ️ Column 'foreign_currency' already exists.<br>";
    }

    echo "<br><b>Database upgrade completed successfully! You can now use the site.</b>";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage();
}
?>
