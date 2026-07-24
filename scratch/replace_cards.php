<?php
$file = 'c:\\xampp\\htdocs\\ELSAJANA2\\magaies\\general-ledger.php';
$content = file_get_contents($file);

$startStr = '<!-- SUMMARY CARDS -->';
$endStr = '<!-- LEDGER TABLE -->';

$startPos = strpos($content, $startStr);
$endPos = strpos($content, $endStr, $startPos);

if ($startPos !== false && $endPos !== false) {
    $newContent = file_get_contents('c:\\xampp\\htdocs\\ELSAJANA2\\magaies\\scratch\\new_cards.html');
    
    // Add two newlines to separate from the ledger table
    $newContent .= "\n    ";

    $content = substr_replace($content, $newContent, $startPos, $endPos - $startPos);
    file_put_contents($file, $content);
    echo "Replaced successfully!\n";
} else {
    echo "Failed to find replacement positions.\n";
}
