<?php
$files = glob('*.php');
$files = array_merge($files, glob('js/*.js'), glob('includes/*.php'));

foreach ($files as $file) {
    if (!is_file($file)) continue;
    $content = file_get_contents($file);
    if (str_contains($content, 'تخصيص') || str_contains($content, 'contextmenu') || str_contains($content, 'حفظ الترتيب والتخصيص')) {
        echo "Found in: $file\n";
    }
}
