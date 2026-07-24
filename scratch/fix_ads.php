<?php
$dir = 'uploads/ads/';

// Copy existing valid banners to the missing filenames so both old/new paths work
if (file_exists($dir . 'ad_1782332954_8182.jpeg')) {
    copy($dir . 'ad_1782332954_8182.jpeg', $dir . 'ad_1783713377_7013.webp');
    echo "Copied Omega banner to ad_1783713377_7013.webp\n";
}
if (file_exists($dir . 'ad_1783415174_5096.webp')) {
    copy($dir . 'ad_1783415174_5096.webp', $dir . 'ad_1783713388_9336.webp');
    echo "Copied City Core banner to ad_1783713388_9336.webp\n";
}
if (file_exists($dir . 'ad_1783415187_8748.webp')) {
    copy($dir . 'ad_1783415187_8748.webp', $dir . 'ad_1783713396_7270.webp');
    echo "Copied Leham banner to ad_1783713396_7270.webp\n";
}

$pdo = new PDO('mysql:host=127.0.0.1;dbname=elsajana2_local', 'root', '');

$updatedAds = [
    [
        "id" => 1,
        "image" => "uploads/ads/ad_1782332988_9597.jpeg",
        "active" => true,
        "companyName" => "مصنع الأسعد للحديد"
    ],
    [
        "id" => 2,
        "image" => "uploads/ads/ad_1782332969_2810.jpeg",
        "active" => true,
        "companyName" => "بنك الخرطوم"
    ],
    [
        "id" => 3,
        "image" => "uploads/ads/ad_1782332954_8182.jpeg",
        "active" => true,
        "companyName" => "أوميغا"
    ],
    [
        "id" => 1783203651832,
        "image" => "uploads/ads/ad_1783415174_5096.webp",
        "active" => true,
        "companyName" => "سيتي كور"
    ],
    [
        "id" => 1783205273257,
        "image" => "uploads/ads/ad_1783415187_8748.webp",
        "active" => true,
        "companyName" => "leham"
    ]
];

$stmt = $pdo->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key = 'saas_ads'");
$stmt->execute([json_encode($updatedAds, JSON_UNESCAPED_UNICODE)]);

echo "Updated saas_ads in database successfully!\n";
