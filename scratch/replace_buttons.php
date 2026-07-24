<?php
$file = 'general-ledger.php';
$content = file_get_contents($file);

$start_tag = '<!-- ACTION BUTTONS SECTION -->';
$end_tag = '<!-- SUMMARY CARDS -->';

$start_pos = strpos($content, $start_tag);
$end_pos = strpos($content, $end_tag);

if ($start_pos !== false && $end_pos !== false) {
    $replacement = file_get_contents('scratch/new_buttons.html') . "\n    ";
    
    $new_content = substr($content, 0, $start_pos) . $replacement . substr($content, $end_pos);
    file_put_contents($file, $new_content);
    echo "Replaced successfully!\n";
} else {
    echo "Could not find tags.\n";
}
