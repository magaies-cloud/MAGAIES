<?php $f = file_get_contents('sw.js'); $f = preg_replace('/Version: \d+/', 'Version: ' . date('YmdHis'), $f); file_put_contents('sw.js', $f); ?>
