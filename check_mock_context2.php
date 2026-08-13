<?php
$files = glob('*.php');
foreach ($files as $f) {
    if (in_array($f, ['api_index.php', 'app.js', 'index.php', 'check_mock_data.php', 'clean_mock.php', 'find_mock_words.php', 'check_real_mocks.php', 'check_mock_context.php'])) continue;
    $c = file_get_contents($f);
    if (preg_match_all('/.{0,40}(?:شركة النيل|محلات أولاد|المقاولات السودانية).{0,40}/ui', $c, $m)) {
        echo "=== $f ===\n";
        print_r($m[0]);
    }
}
