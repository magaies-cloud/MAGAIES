<?php
// ابحث عن الملفات التي تستخدم جدول items أو تستعلم عن المخزون
$dir = 'c:/ELSAJANA/magaies';
$files = scandir($dir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents("$dir/$file");
        if (strpos($content, 'FROM items') !== false || strpos($content, 'FROM `items`') !== false) {
            echo "$file contains FROM items\n";
        }
        if (strpos($content, 'FROM inventory') !== false || strpos($content, 'FROM `inventory`') !== false) {
            echo "$file contains FROM inventory\n";
        }
    }
}
?>
