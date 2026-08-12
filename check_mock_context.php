<?php
$c = file_get_contents('checks_detector.php');
preg_match_all('/.{0,40}(?:شركة النيل|النيل|معدل|محلات|المقاولات|سيراميك|السجانة).{0,40}/ui', $c, $m);
print_r($m[0]);
