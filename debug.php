<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Forbidden: CLI only'); }
$lines = file('api_index.php');
for ($i = 875; $i <= 882; $i++) {
    echo $i . ": " . $lines[$i];
}
