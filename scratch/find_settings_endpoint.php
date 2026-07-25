<?php
$lines = file('c:/xampp/htdocs/ELSAJANA2/magaies/api_index.php');
foreach($lines as $line) {
    if (strpos($line, 'settings') !== false || strpos($line, 'layout') !== false) {
        echo trim($line) . "\n";
    }
}
?>
