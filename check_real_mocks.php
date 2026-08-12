<?php
$files = glob('*.php');
$count = 0;
foreach ($files as $f) {
    if (in_array($f, ['api_index.php', 'app.js', 'index.php', 'check_mock_data.php', 'clean_mock.php', 'find_mock_words.php'])) continue;
    $c = file_get_contents($f);
    if (preg_match_all('/(?:شركة النيل|محلات أولاد|المقاولات السودانية)/ui', $c, $m)) {
        echo "Found " . count($m[0]) . " mock names in $f\n";
        $count++;
    }
}
echo "Total files with mock names: $count\n";
