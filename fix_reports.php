<?php
$files = glob('*.php');

function injectTenantId($query) {
    if (strpos($query, 'tenant_id') !== false) return $query;
    
    // Pattern: SELECT data FROM table WHERE condition
    if (preg_match('/^(SELECT data FROM\s+`?[a-zA-Z0-9_]+`?)\s+WHERE\s+(.*)$/i', $query, $m)) {
        return $m[1] . " WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' AND (" . $m[2] . ")";
    }
    
    // Pattern: SELECT data FROM table LIMIT x
    if (preg_match('/^(SELECT data FROM\s+`?[a-zA-Z0-9_]+`?)\s+LIMIT\s+(.*)$/i', $query, $m)) {
        return $m[1] . " WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' LIMIT " . $m[2];
    }
    
    // Pattern: SELECT data FROM table
    if (preg_match('/^(SELECT data FROM\s+`?[a-zA-Z0-9_]+`?)$/i', trim($query))) {
        return trim($query) . " WHERE tenant_id = '{\$GLOBAL_TENANT_ID}'";
    }
    
    return $query;
}

$count = 0;
foreach ($files as $f) {
    if ($f === 'api_index.php') continue;
    
    $c = file_get_contents($f);
    $original_c = $c;
    
    // Find all $pdo->query("SELECT ...") or $pdo->prepare("SELECT ...")
    $c = preg_replace_callback('/(\$pdo->(?:query|prepare)\s*\(\s*["\'])(SELECT data FROM.*?)(["\']\s*\))/i', function($m) {
        return $m[1] . injectTenantId($m[2]) . $m[3];
    }, $c);
    
    // Also catch $safes_stmt = $pdo->query("SELECT data FROM safes WHERE tenant_id = '{$GLOBAL_TENANT_ID}'");
    $c = preg_replace_callback('/(= \$pdo->(?:query|prepare)\s*\(\s*["\'])(SELECT data FROM.*?)(["\']\s*\))/i', function($m) {
        return $m[1] . injectTenantId($m[2]) . $m[3];
    }, $c);

    if ($c !== $original_c) {
        // Ensure global $GLOBAL_TENANT_ID is available in functions if needed
        // But since db_bridge defines it globally, any file including db_bridge has it.
        // If the query is inside a function, $GLOBAL_TENANT_ID might not be accessible unless globalized.
        // Actually, $pdo is globalized, so let's make sure $GLOBAL_TENANT_ID is too.
        if (strpos($c, 'global $pdo, $GLOBAL_TENANT_ID;') !== false) {
            $c = str_replace('global $pdo, $GLOBAL_TENANT_ID;', 'global $pdo, $GLOBAL_TENANT_ID;', $c);
        }
        
        file_put_contents($f, $c);
        echo "Updated $f\n";
        $count++;
    }
}
echo "Total files updated: $count\n";
