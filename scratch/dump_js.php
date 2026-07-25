<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php');
preg_match_all('/<script>(.*?)<\/script>/s', $content, $matches);
foreach ($matches[1] as $index => $script) {
    file_put_contents('c:/xampp/htdocs/ELSAJANA2/magaies/scratch/script_' . $index . '.js', $script);
}
echo "Scripts dumped. Found " . count($matches[1]) . " scripts.";
?>
