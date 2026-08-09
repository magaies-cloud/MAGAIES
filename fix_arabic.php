<?php
function fix_file($file) {
    $content = file_get_contents($file);
    $content = preg_replace('/\?\?\?\?\?\?\?/', 'วÝสัวึํ', $content);
    $content = preg_replace('/\?\?\?/', '??', $content);
    $content = preg_replace('/\?\?\?\? \?\? \?\?\? \?\?\?\? \(\?\?\?\?\? \?\?\)/', 'Þวแศ ึศุ ใีไฺ ใÞÝแ (แแฺัึ ÝÞุ)', $content);
    $content = preg_replace('/\?\?\?/', '??', $content);
    $content = preg_replace('/\?\?\?\? \?\?\?\?\?\?\?\? \?\?\?\?\?\? \? \?\?\? \?\?\?\?\?\?\?\?\? \?\?\?/', 'วไÞั แวำสัฬวฺ วแไใๆะฬ รๆ อÝู วแสฺฯํแวส ??', $content);
    $content = preg_replace('/\?\?\?\? \(/', 'ลฺฯวฯวส (', $content);
    $content = preg_replace('/\- \? \[OK \/ \?\] \? \? \? \? ? \?\./', '- วึÛุ [OK / อำไว๐] ละว ฿ไส สัํฯ สอใํแ ๅะว วแไใๆะฬ.', $content);
    $content = preg_replace('/\- \? \[Cancel \/ \?\?\] \? \? \? \? \?\?\? \?\?\?\? \? ? \?\./', '- วึÛุ [Cancel / ลแÛวม] ละว ฿ไส สัํฯ อÝู วแสฺฯํแวส วแอวแํษ Ýํ ๅะว วแไใๆะฬ.', $content);
    $content = preg_replace('/\? \? \?/', '? สร฿ํฯ วแอÝู', $content);
    $content = preg_replace('/\? \? ?\? \? \?\?\?\? \? \? \?\?\? \?\?\?\? \?/', 'ๅแ รไส ใสร฿ฯ ใไ ัÛศส฿ Ýํ อÝู วแสฺฯํแวส วแอวแํษ Ýํ', $content);
    $content = preg_replace('/\? \?\?\?/', 'ศิ฿แ ไๅวฦํ', $content);
    file_put_contents($file, $content);
}
fix_file('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js');
