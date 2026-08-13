<?php
$lines = file('api_index.php');
for ($i = 875; $i <= 882; $i++) {
    echo $i . ": " . $lines[$i];
}
