<?php
header('Content-Type: application/manifest+json; charset=utf-8');
$t = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['t'] ?? '12');
$name = trim($_GET['name'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$qs = 't=' . urlencode($t) . ($name !== '' ? '&name=' . urlencode($name) : '') . ($phone !== '' ? '&phone=' . urlencode($phone) : '');
$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#047857"/><stop offset="100%" stop-color="#0f172a"/></linearGradient></defs><rect width="512" height="512" rx="112" fill="url(#g)" stroke="#fbbf24" stroke-width="18"/><path d="M256 76 L406 138 V256 C406 354 338 424 256 448 C174 424 106 354 106 256 V138 Z" fill="rgba(16,185,129,0.22)" stroke="#34d399" stroke-width="14"/><text x="256" y="268" font-family="Tajawal,Cairo,Arial" font-size="78" font-weight="900" text-anchor="middle" fill="#fde047">السيطرة</text><text x="256" y="338" font-family="Tajawal,Cairo,Arial" font-size="38" font-weight="800" text-anchor="middle" fill="#ffffff">إرسال إشعار</text></svg>';
$iconData = 'data:image/svg+xml;base64,' . base64_encode($svg);
echo json_encode([
    'name' => 'بوابة السيطرة لإرسال الإشعارات',
    'short_name' => 'السيطرة إشعارات',
    'start_url' => 'send-receipt.php?' . $qs,
    'display' => 'standalone',
    'background_color' => '#050308',
    'theme_color' => '#047857',
    'dir' => 'rtl',
    'lang' => 'ar',
    'icons' => [
        ['src' => $iconData, 'sizes' => '192x192', 'type' => 'image/svg+xml', 'purpose' => 'any maskable'],
        ['src' => $iconData, 'sizes' => '512x512', 'type' => 'image/svg+xml', 'purpose' => 'any maskable']
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
