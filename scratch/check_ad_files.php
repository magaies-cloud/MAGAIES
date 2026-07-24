<?php
$files = glob('uploads/ads/*');
foreach ($files as $f) {
    echo $f . " (" . filesize($f) . " bytes)\n";
}
