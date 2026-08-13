<?php
$files = glob(__DIR__ . '/*.*');
foreach($files as $f) {
    if(strpos($f, 'push') !== false || strpos($f, 'deploy') !== false || strpos($f, 'git') !== false) {
        echo basename($f) . "<br>";
    }
}
echo "Done";
