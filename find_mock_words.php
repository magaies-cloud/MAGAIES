<?php
$files = glob('*.php');
$count = 0;
foreach ($files as $f) {
    if (in_array($f, ['api_index.php', 'app.js', 'index.php'])) continue;
    $c = file_get_contents($f);
    if (preg_match_all('/(?:شركة النيل|النيل|معدل|محلات|المقاولات|سيراميك|السجانة|مواد بناء|حديد التسليح|توسعة|توريد|تحديث|دفعة|شيك|فواتير|استثمار)/ui', $c, $m)) {
        echo "Found " . count($m[0]) . " mock words in $f\n";
        $count++;
    }
}
echo "Total files with mock words: $count\n";
