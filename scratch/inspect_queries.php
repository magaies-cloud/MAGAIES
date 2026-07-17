<?php
$dir = 'c:/ELSAJANA/magaies';
$files = glob("$dir/inv_rep*.php");
foreach ($files as $file) {
    $content = file_get_contents($file);
    $basename = basename($file);
    echo "=== File: $basename ===\n";
    // Find lines containing ->query or ->prepare
    $lines = explode("\n", $content);
    $query_lines = [];
    foreach ($lines as $num => $line) {
        if (strpos($line, 'query') !== false || strpos($line, 'prepare') !== false || strpos($line, 'SELECT') !== false || strpos($line, 'report_data =') !== false) {
            $query_lines[] = "Line " . ($num + 1) . ": " . trim($line);
        }
    }
    if (empty($query_lines)) {
        echo "No DB query lines found.\n";
        // print first 15 lines of content
        echo "First 15 lines:\n";
        $first_lines = array_slice($lines, 0, 15);
        echo implode("\n", $first_lines) . "\n";
    } else {
        echo implode("\n", $query_lines) . "\n";
    }
    echo "\n";
}
?>
