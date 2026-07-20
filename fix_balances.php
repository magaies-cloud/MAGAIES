<?php
$file = 'balances_modern_hub.php';
$content = file_get_contents($file);

// Replace the netting query section
$new_logic = <<<PHP
        // Modern Netting Logic via JSON-in-SQL tables
        \$stmt = \$pdo->query("
            WITH CustomerBalances AS (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) AS name, 
                       CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.currentBalance')) AS DECIMAL(15,2)) AS debit_balance
                FROM customers 
                WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)
            ),
            SupplierBalances AS (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(data, '$.name')) AS name, 
                       CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.currentBalance')) AS DECIMAL(15,2)) AS credit_balance
                FROM suppliers 
                WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' AND (is_deleted = 0 OR is_deleted IS NULL)
            )
            SELECT 
                c.name AS entity_name,
                COALESCE(c.debit_balance, 0) AS our_money_with_them,
                COALESCE(s.credit_balance, 0) AS their_money_with_us,
                (COALESCE(c.debit_balance, 0) - COALESCE(s.credit_balance, 0)) AS net_position
            FROM CustomerBalances c
            INNER JOIN SupplierBalances s ON c.name = s.name
            WHERE c.name IS NOT NULL AND c.name != ''
        ");
        \$rows = \$stmt->fetchAll(PDO::FETCH_ASSOC);
PHP;

$content = preg_replace('/\$netting_query = ".*?";\s+\$stmt = \$pdo->query\(\$netting_query\);\s+\$rows = \$stmt->fetchAll\(PDO::FETCH_ASSOC\);/s', $new_logic, $content);

file_put_contents($file, $content);
echo "balances_modern_hub.php fixed!\n";
