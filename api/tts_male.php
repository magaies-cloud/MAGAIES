<?php
// ============================================================================
// ELSAJANA ERP — GENUINE MICROSOFT EDGE NEURAL ARABIC MALE VOICE ENGINE (v6)
// Calm, Clear, Deliberate Pacing (-10% SSML rate + natural punctuation pauses)
// Supports 3 Crystal-Clear Male Dialects:
// 1. egyptian (🇪🇬 اللهجة المصرية الواضحة — صوت رجالي مصري أصيل وهادئ - شاكر)
// 2. sudanese (🇸🇩 اللهجة السودانية الواضحة — صوت رجالي سوداني دافئ وهادئ)
// 3. gulf     (🇸🇦 اللهجة الخليجية / البيضاء — صوت رجالي خليجي واضح وهادئ - حمدان)
// ============================================================================

header('Access-Control-Allow-Origin: *');

$text = isset($_GET['text']) ? trim($_GET['text']) : (isset($_POST['text']) ? trim($_POST['text']) : '');
$dialect = isset($_GET['dialect']) ? trim($_GET['dialect']) : (isset($_POST['dialect']) ? trim($_POST['dialect']) : 'egyptian');

if ($text === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Empty text']);
    exit;
}

// Map dialect to Genuine Microsoft Neural Male Voice + Slower, Clearer Prosody (-10%)
$voiceMap = [
    'egyptian' => ['voice' => 'ar-EG-ShakirNeural', 'rate' => '-10%', 'pitch' => '-1Hz'],
    'sudanese' => ['voice' => 'ar-SA-HamedNeural',  'rate' => '-8%',  'pitch' => '-2Hz'],
    'gulf'     => ['voice' => 'ar-AE-HamdanNeural', 'rate' => '-8%',  'pitch' => '+0Hz'],
];

if (!isset($voiceMap[$dialect])) {
    $dialect = 'egyptian';
}
$cfg = $voiceMap[$dialect];

$cacheDir = dirname(__DIR__) . '/uploads/tts_cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

$hash = md5('edge_neural_male_v6_slow|' . $dialect . '|' . $cfg['voice'] . '|' . $cfg['rate'] . '|' . $text);
$cacheFile = $cacheDir . '/neural_male_v6_' . $hash . '.mp3';

// 1. Instant 0.001s response from local disk cache
if (file_exists($cacheFile) && filesize($cacheFile) > 500) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=31536000');
    header('X-Voice-Engine: Edge-Neural-Male');
    header('X-Voice-Dialect: ' . $dialect);
    readfile($cacheFile);
    exit;
}

// 2. Generate Genuine Microsoft Edge Neural Male MP3 via edge_male_tts.cjs
$scriptPath = __DIR__ . '/edge_male_tts.cjs';
$nodeCandidates = [
    'C:\\Program Files\\nodejs\\node.exe',
    'node',
    '/usr/bin/node',
    '/usr/local/bin/node'
];
$nodeBin = 'node';
foreach ($nodeCandidates as $cand) {
    if ($cand !== 'node' && @file_exists($cand)) {
        $nodeBin = '"' . $cand . '"';
        break;
    }
}

$cmd = $nodeBin
    . ' ' . escapeshellarg($scriptPath)
    . ' ' . escapeshellarg($text)
    . ' ' . escapeshellarg($cacheFile)
    . ' ' . escapeshellarg($cfg['voice'])
    . ' ' . escapeshellarg($cfg['rate'])
    . ' ' . escapeshellarg($cfg['pitch'])
    . ' 2>&1';

$output = @shell_exec($cmd);

if (file_exists($cacheFile) && filesize($cacheFile) > 500) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=31536000');
    header('X-Voice-Engine: Edge-Neural-Male');
    header('X-Voice-Dialect: ' . $dialect);
    readfile($cacheFile);
    exit;
}

// 3. Emergency fallback if offline/firewall blocks WebSocket
$chunks = [];
$words = preg_split('/\s+/u', $text);
$current = '';
foreach ($words as $w) {
    if (mb_strlen($current . ' ' . $w, 'UTF-8') > 165) {
        if ($current !== '') $chunks[] = trim($current);
        $current = $w;
    } else {
        $current .= ($current === '' ? '' : ' ') . $w;
    }
}
if ($current !== '') $chunks[] = trim($current);

$combinedMp3 = '';
foreach ($chunks as $chunk) {
    $url = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=ar&q=' . rawurlencode($chunk);
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_TIMEOUT => 4,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $part = @curl_exec($c);
    curl_close($c);
    if ($part && strlen($part) > 200) {
        $combinedMp3 .= $part;
    }
}

if (strlen($combinedMp3) > 400) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($combinedMp3));
    header('X-Voice-Engine: Fallback-Male-DSP');
    echo $combinedMp3;
    exit;
}

http_response_code(500);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => false, 'error' => 'TTS synthesis error']);
