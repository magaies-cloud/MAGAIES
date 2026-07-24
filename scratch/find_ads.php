<?php
$content = file_get_contents('saas-admin.php');
preg_match_all('/.*(ads|إعلان|banner|إعلانات).*/u', $content, $matches, PREG_OFFSET_CAPTURE);
foreach (array_slice($matches[0], 0, 30) as $m) {
    echo $m[0] . "\n";
}
