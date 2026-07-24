<?php
$urls = [
    'http://localhost:3000/uploads/ads/ad_1782332988_9597.jpeg',
    'http://localhost:3000/uploads/ads/ad_1782332969_2810.jpeg',
    'http://localhost:3000/uploads/ads/ad_1782332954_8182.jpeg',
    'http://localhost:3000/uploads/ads/ad_1783415174_5096.webp',
    'http://localhost:3000/uploads/ads/ad_1783415187_8748.webp',
    'http://localhost:3000/uploads/ads/ad_1783713377_7013.webp',
    'http://localhost:3000/uploads/ads/ad_1783713388_9336.webp',
    'http://localhost:3000/uploads/ads/ad_1783713396_7270.webp',
];

foreach ($urls as $url) {
    $headers = @get_headers($url);
    $status = $headers ? $headers[0] : 'Failed to connect';
    echo "$url => $status\n";
}
