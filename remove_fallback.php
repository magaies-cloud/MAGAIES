<?php
$f = 'alerts_manager.php';
$c = file_get_contents($f);

$c = preg_replace('/if \(empty\(\$customers\)\) \{.*?catch \(Exception \$ex\) \{\}.*?\}/s', '', $c);

file_put_contents($f, $c);
echo "alerts_manager.php dead table fallback removed!\n";
