<?php
$pdo = new PDO('mysql:host=localhost;dbname=magaees;charset=utf8', 'root', '');
$stmt = $pdo->query("SELECT setting_value FROM global_settings WHERE setting_key = 'saas_ads'");
$row = $stmt->fetch();
if($row) {
    $ads = json_decode($row['setting_value'], true);
    $names = ['مصنع الأسعد للحديد', 'بنك الخرطوم', 'أوميغا', 'شركة دال للسيارات', 'بنك المزارع'];
    foreach($ads as $i => &$ad) {
        $ad['companyName'] = $names[$i % count($names)];
    }
    $pdo->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key = 'saas_ads'")->execute([json_encode($ads, JSON_UNESCAPED_UNICODE)]);
    echo 'DB Ads Updated!';
}
