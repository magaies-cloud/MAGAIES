<?php
$dirs = ['uploads/ads', 'assets'];
foreach ($dirs as $dir) {
    echo "=== $dir ===\n";
    $files = glob("$dir/*");
    foreach ($files as $f) {
        if (is_file($f)) {
            $info = @getimagesize($f);
            if ($info) {
                echo basename($f) . ": " . $info[0] . "x" . $info[1] . " (" . $info['mime'] . ")\n";
            } else {
                echo basename($f) . ": not image / " . filesize($f) . " bytes\n";
            }
        }
    }
}
