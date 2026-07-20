<?php
$c = file_get_contents('db_bridge.php');
// Find every function definition that takes ($pdo or other params and add global $GLOBAL_TENANT_ID; right after the opening brace.
// Example: function getCustomerChurnData($pdo) {
// becomes: function getCustomerChurnData($pdo) { global $GLOBAL_TENANT_ID;

$c = preg_replace_callback('/function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*\{/s', function($m) {
    // Check if it already has global $GLOBAL_TENANT_ID;
    return $m[0] . "\n    global \$GLOBAL_TENANT_ID;";
}, $c);

// Remove duplicates if any (like line 364 where it was already there)
$c = str_replace("global \$GLOBAL_TENANT_ID;\n        global \$GLOBAL_TENANT_ID;", "global \$GLOBAL_TENANT_ID;", $c);

file_put_contents('db_bridge.php', $c);
echo "Added global GLOBAL_TENANT_ID to all functions in db_bridge.php\n";
