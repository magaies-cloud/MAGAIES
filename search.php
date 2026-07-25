<?php
$files = glob("c:/xampp/htdocs/ELSAJANA2/magaies/js/*.js");
foreach($files as $file) {
    $lines = file($file);
    foreach($lines as $num => $line) {
        if(stripos($line, 'applyPresetGL') !== false) {
            echo basename($file) . ":" . ($num+1) . ":" . trim($line) . "\n";
        }
    }
}
?>
