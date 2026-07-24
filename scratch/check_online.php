<?php
$urls = ['http://localhost:3000/', 'http://127.0.0.1:8080/login.php'];
foreach ($urls as $url) {
    $headers = @get_headers($url);
    echo $url . " => " . ($headers ? $headers[0] : 'OFFLINE') . "\n";
}
