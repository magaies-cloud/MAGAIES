<?php
$content = file_get_contents('saas-admin.php');
preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $matches);
$js = implode("\n", $matches[1]);
file_put_contents('temp.js', $js);
echo "Extracted.\n";
