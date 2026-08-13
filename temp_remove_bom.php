<?php
$files = glob('*.php');
$count = 0;
foreach($files as $file) {
    $content = file_get_contents($file);
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        file_put_contents($file, substr($content, 3));
        echo "Removed BOM from $file\n";
        $count++;
    }
}
echo "Total files fixed: $count\n";
