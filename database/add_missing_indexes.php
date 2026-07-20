<?php
/**
 * MAGAIEES ERP - Missing Indexes Analyzer & Fixer
 * Analyzes tables for missing performance-critical indexes and adds them.
 * 
 * Run with --dry-run to see what would be added without making changes.
 * Run without flags to apply changes.
 * 
 * Usage:
 *   php database/add_missing_indexes.php --dry-run   (preview only)
 *   php database/add_missing_indexes.php              (apply changes)
 */

$dryRun = in_array('--dry-run', $argv ?? []);

$pdo = new PDO('mysql:host=127.0.0.1;dbname=magaees', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== MAGAIEES ERP — Missing Indexes Analyzer ===\n";
echo "Mode: " . ($dryRun ? "DRY RUN (preview only)" : "LIVE (applying changes)") . "\n\n";

// Get all existing indexes
function getExistingIndexes($pdo, $table) {
    $indexes = [];
    $rows = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $indexes[$row['Key_name']][] = $row['Column_name'];
    }
    return $indexes;
}

// Check if a column exists in the table
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'magaees' AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return $stmt->fetchColumn() > 0;
}

// Check if an index already covers a column (or set of columns)
function indexExistsForColumns($existingIndexes, $columns) {
    $columns = (array)$columns;
    foreach ($existingIndexes as $keyName => $indexCols) {
        // Check if the desired columns are a prefix of this index
        $match = true;
        for ($i = 0; $i < count($columns); $i++) {
            if (!isset($indexCols[$i]) || $indexCols[$i] !== $columns[$i]) {
                $match = false;
                break;
            }
        }
        if ($match) return $keyName;
    }
    return false;
}

// Define indexes needed for each table pattern
// Multi-tenant data tables that use JSON blob architecture
$dataTables = [
    'accounts', 'audit_logs', 'banks', 'categories', 'checks',
    'customers', 'delivery_notes', 'employees', 'financial_periods',
    'gl_entries', 'inventory', 'items', 'journal_vouchers',
    'partners', 'payments', 'payroll', 'purchase_invoices',
    'receipts', 'reserved_sales', 'roles', 'safes',
    'sales_invoices', 'settings', 'single_payments', 'single_receipts',
    'suppliers', 'users', 'warehouses'
];

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($dataTables as $table) {
    // Check if table exists
    try {
        $pdo->query("SELECT 1 FROM `$table` LIMIT 0");
    } catch (Exception $e) {
        echo "⚠  Table `$table` does not exist, skipping.\n";
        continue;
    }

    $existingIndexes = getExistingIndexes($pdo, $table);
    
    // === Index 1: is_deleted (for soft delete filtering) ===
    if (columnExists($pdo, $table, 'is_deleted')) {
        if (!indexExistsForColumns($existingIndexes, ['is_deleted'])) {
            $indexName = "idx_{$table}_is_deleted";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`is_deleted`)";
            echo "  ➕ $indexName on `$table`(`is_deleted`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 2: tenant_id + is_deleted (most common query pattern) ===
    if (columnExists($pdo, $table, 'tenant_id') && columnExists($pdo, $table, 'is_deleted')) {
        if (!indexExistsForColumns($existingIndexes, ['tenant_id', 'is_deleted'])) {
            $indexName = "idx_{$table}_tenant_deleted";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`tenant_id`, `is_deleted`)";
            echo "  ➕ $indexName on `$table`(`tenant_id`, `is_deleted`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 3: updated_at (for sync engine performance) ===
    if (columnExists($pdo, $table, 'updated_at')) {
        if (!indexExistsForColumns($existingIndexes, ['updated_at'])) {
            $indexName = "idx_{$table}_updated_at";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`updated_at`)";
            echo "  ➕ $indexName on `$table`(`updated_at`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 4: date_val (for date-range reporting queries) ===
    if (columnExists($pdo, $table, 'date_val')) {
        if (!indexExistsForColumns($existingIndexes, ['date_val'])) {
            $indexName = "idx_{$table}_date_val";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`date_val`)";
            echo "  ➕ $indexName on `$table`(`date_val`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 5: status_val (for financial document filtering) ===
    if (columnExists($pdo, $table, 'status_val')) {
        if (!indexExistsForColumns($existingIndexes, ['status_val'])) {
            $indexName = "idx_{$table}_status_val";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`status_val`)";
            echo "  ➕ $indexName on `$table`(`status_val`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 6: currency_val (for multi-currency filtering) ===
    if (columnExists($pdo, $table, 'currency_val')) {
        if (!indexExistsForColumns($existingIndexes, ['currency_val'])) {
            $indexName = "idx_{$table}_currency_val";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`currency_val`)";
            echo "  ➕ $indexName on `$table`(`currency_val`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 7: created_by_val (for user activity tracking) ===
    if (columnExists($pdo, $table, 'created_by_val')) {
        if (!indexExistsForColumns($existingIndexes, ['created_by_val'])) {
            $indexName = "idx_{$table}_created_by";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`created_by_val`)";
            echo "  ➕ $indexName on `$table`(`created_by_val`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }

    // === Index 8: Composite tenant_id + updated_at (for sync pull performance) ===
    if (columnExists($pdo, $table, 'tenant_id') && columnExists($pdo, $table, 'updated_at')) {
        if (!indexExistsForColumns($existingIndexes, ['tenant_id', 'updated_at'])) {
            $indexName = "idx_{$table}_tenant_updated";
            $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` (`tenant_id`, `updated_at`)";
            echo "  ➕ $indexName on `$table`(`tenant_id`, `updated_at`)\n";
            if (!$dryRun) {
                try {
                    $pdo->exec($sql);
                    $added++;
                } catch (Exception $e) {
                    echo "    ❌ Error: " . $e->getMessage() . "\n";
                    $errors++;
                }
            } else {
                $added++;
            }
        } else {
            $skipped++;
        }
    }
}

// === Special indexes for SaaS tables ===
echo "\n--- SaaS Platform Tables ---\n";

// saas_audit_logs: index on created_at
$existingIndexes = getExistingIndexes($pdo, 'saas_audit_logs');
if (!indexExistsForColumns($existingIndexes, ['created_at'])) {
    $sql = "ALTER TABLE `saas_audit_logs` ADD INDEX `idx_saas_audit_created` (`created_at`)";
    echo "  ➕ idx_saas_audit_created on `saas_audit_logs`(`created_at`)\n";
    if (!$dryRun) {
        try { $pdo->exec($sql); $added++; } catch (Exception $e) { echo "    ❌ Error: " . $e->getMessage() . "\n"; $errors++; }
    } else { $added++; }
}

// otp_verifications: index on phone + created_at
try {
    $pdo->query("SELECT 1 FROM `otp_verifications` LIMIT 0");
    $existingIndexes = getExistingIndexes($pdo, 'otp_verifications');
    if (columnExists($pdo, 'otp_verifications', 'phone') && !indexExistsForColumns($existingIndexes, ['phone'])) {
        $sql = "ALTER TABLE `otp_verifications` ADD INDEX `idx_otp_phone` (`phone`)";
        echo "  ➕ idx_otp_phone on `otp_verifications`(`phone`)\n";
        if (!$dryRun) {
            try { $pdo->exec($sql); $added++; } catch (Exception $e) { echo "    ❌ Error: " . $e->getMessage() . "\n"; $errors++; }
        } else { $added++; }
    }
} catch (Exception $e) {
    echo "  ⚠ Table `otp_verifications` not found.\n";
}

// broadcast_messages: index on created_at
try {
    $pdo->query("SELECT 1 FROM `broadcast_messages` LIMIT 0");
    $existingIndexes = getExistingIndexes($pdo, 'broadcast_messages');
    if (columnExists($pdo, 'broadcast_messages', 'created_at') && !indexExistsForColumns($existingIndexes, ['created_at'])) {
        $sql = "ALTER TABLE `broadcast_messages` ADD INDEX `idx_broadcast_created` (`created_at`)";
        echo "  ➕ idx_broadcast_created on `broadcast_messages`(`created_at`)\n";
        if (!$dryRun) {
            try { $pdo->exec($sql); $added++; } catch (Exception $e) { echo "    ❌ Error: " . $e->getMessage() . "\n"; $errors++; }
        } else { $added++; }
    }
} catch (Exception $e) {
    echo "  ⚠ Table `broadcast_messages` not found.\n";
}

echo "\n=== Summary ===\n";
echo "Indexes to add / added: $added\n";
echo "Already existing (skipped): $skipped\n";
echo "Errors: $errors\n";

if ($dryRun) {
    echo "\n⚠ This was a DRY RUN. No changes were made.\n";
    echo "Run without --dry-run to apply changes.\n";
} else {
    echo "\n✅ All indexes applied successfully!\n";
    
    // Regenerate schema after adding indexes
    echo "\n📝 Regenerating schema.sql with new indexes...\n";
}
