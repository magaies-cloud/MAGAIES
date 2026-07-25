<?php
$content = file_get_contents('c:/xampp/htdocs/ELSAJANA2/magaies/balances-report.php');
preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $matches);
foreach ($matches[1] as $index => $script) {
    if (trim($script) !== '') {
        file_put_contents('c:/xampp/htdocs/ELSAJANA2/magaies/scratch/bal_script_' . $index . '.js', $script);
    }
}
echo "Scripts dumped.";
?>
