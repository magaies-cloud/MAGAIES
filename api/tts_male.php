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
    $rawBody = @file_get_contents('php://input');
    if ($rawBody) {
        $jsonBody = @json_decode($rawBody, true);
        if (is_array($jsonBody) && !empty($jsonBody['text'])) {
            $text = trim((string)$jsonBody['text']);
            if (!empty($jsonBody['dialect'])) {
                $dialect = trim((string)$jsonBody['dialect']);
            }
        }
    }
}

if ($text === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Empty text']);
    exit;
}

// Unified Studio-Pure HD Neural Voice Library (Warm & Crystal-Clear Articulation)
$voiceMap = [
    'warm_professor' => ['voice' => 'ar-SA-HamedNeural',    'rate' => '-2%', 'pitch' => '-3Hz'],
    'warm_male'      => ['voice' => 'ar-OM-AbdullahNeural', 'rate' => '-2%', 'pitch' => '-2Hz'],
    'female_zariyah' => ['voice' => 'ar-SA-ZariyahNeural',  'rate' => '-3%', 'pitch' => '-2Hz'],
    'female_salma'   => ['voice' => 'ar-EG-SalmaNeural',    'rate' => '-3%', 'pitch' => '-2Hz'],
    'female_fatima'  => ['voice' => 'ar-AE-FatimaNeural',   'rate' => '-3%', 'pitch' => '-2Hz'],
    'male_hamed'     => ['voice' => 'ar-SA-HamedNeural',    'rate' => '+6%', 'pitch' => '+0Hz'],
    'male_abdullah'  => ['voice' => 'ar-OM-AbdullahNeural', 'rate' => '+8%', 'pitch' => '+0Hz'],
    'male_shakir'    => ['voice' => 'ar-EG-ShakirNeural',   'rate' => '+6%', 'pitch' => '+0Hz'],
    'male_muawiya'   => ['voice' => 'ar-SD-MuawiyaNeural',  'rate' => '+6%', 'pitch' => '+0Hz'],
    'fusha'          => ['voice' => 'ar-SA-HamedNeural',    'rate' => '+6%', 'pitch' => '+0Hz'],
    'egyptian'       => ['voice' => 'ar-EG-ShakirNeural',   'rate' => '+6%', 'pitch' => '+0Hz'],
    'sudanese'       => ['voice' => 'ar-SD-MuawiyaNeural',  'rate' => '+6%', 'pitch' => '+0Hz'],
    'gulf'           => ['voice' => 'ar-SA-HamedNeural',    'rate' => '+6%', 'pitch' => '+0Hz'],
];

if (!isset($voiceMap[$dialect])) {
    $dialect = 'male_hamed';
}
$cfg = $voiceMap[$dialect];

$cacheDir = dirname(__DIR__) . '/uploads/tts_cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

$hash = md5('edge_neural_hd_v21|' . $cfg['voice'] . '|' . $cfg['rate'] . '|' . $cfg['pitch'] . '|' . $text);
$cacheFile = $cacheDir . '/neural_v21_' . $hash . '.mp3';

// 0. Exact word timings (for perfect image/voice sync)
if (!empty($_GET['meta'])) {
    header('Content-Type: application/json; charset=utf-8');
    $mf = $cacheFile . '.marks.json';
    if (file_exists($mf)) { header('Cache-Control: public, max-age=31536000'); readfile($mf); } else { header('Cache-Control: no-store'); echo '{"marks":[]}'; }
    exit;
}

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

// 1B. Instant non-blocking fast mode (<10ms): if not cached yet, warm in background and return 204 immediately
if (!empty($_GET['fast'])) {
    $scriptPath = __DIR__ . '/edge_male_tts.cjs';
    $nodeBin = @file_exists('C:\\Program Files\\nodejs\\node.exe') ? '"C:\\Program Files\\nodejs\\node.exe"' : 'node';
    $payloadB64 = 'JSONB64:' . base64_encode(json_encode(['text' => $text, 'outFile' => $cacheFile, 'voice' => $cfg['voice'], 'rate' => $cfg['rate'], 'pitch' => $cfg['pitch']], JSON_UNESCAPED_UNICODE));
    $bgCmd = (DIRECTORY_SEPARATOR === '\\') ? ('start /B "" ' . $nodeBin . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($payloadB64) . ' >NUL 2>&1') : ($nodeBin . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($payloadB64) . ' >/dev/null 2>&1 &');
    @pclose(@popen($bgCmd, 'r'));
    http_response_code(204);
    exit;
}

// 2. Acquire per-hash lock so concurrent requests never collide on the same MP3 file
$lockFile = $cacheDir . '/neural_male_v6_' . $hash . '.lock';
$lockFp = @fopen($lockFile, 'c+');
if ($lockFp) {
    @flock($lockFp, LOCK_EX);
}

clearstatcache(true, $cacheFile);
if (file_exists($cacheFile) && filesize($cacheFile) > 500) {
    if ($lockFp) { @flock($lockFp, LOCK_UN); @fclose($lockFp); }
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=31536000');
    header('X-Voice-Engine: Edge-Neural-Male');
    header('X-Voice-Dialect: ' . $dialect);
    readfile($cacheFile);
    exit;
}

// 3A. Fast Direct Microsoft Edge Neural Voice via edge_male_tts.cjs (zero blocking cURL overhead)
clearstatcache(true, $cacheFile);
if (!file_exists($cacheFile) || filesize($cacheFile) <= 500) {
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

$tmpFile = $cacheFile . '.' . getmypid() . '_' . mt_rand(1000, 9999) . '.tmp';
$payloadB64 = 'JSONB64:' . base64_encode(json_encode([
    'text'    => $text,
    'outFile' => $tmpFile,
    'voice'   => $cfg['voice'],
    'rate'    => $cfg['rate'],
    'pitch'   => $cfg['pitch'],
], JSON_UNESCAPED_UNICODE));

$innerCmd = $nodeBin
    . ' ' . escapeshellarg($scriptPath)
    . ' ' . escapeshellarg($payloadB64)
    . ' 2>&1';

$cmd = (DIRECTORY_SEPARATOR === '\\') ? ('cmd /s /c "' . $innerCmd . '"') : $innerCmd;

$output = @shell_exec($cmd);

// Retry once if transient socket hiccup occurred
if (!file_exists($tmpFile) || filesize($tmpFile) <= 500) {
    usleep(180000);
    $output = @shell_exec($cmd);
}

if (file_exists($tmpFile) && filesize($tmpFile) > 500) {
    @rename($tmpFile, $cacheFile);
    if (file_exists($tmpFile . '.marks.json')) { @rename($tmpFile . '.marks.json', $cacheFile . '.marks.json'); }
    if (!file_exists($cacheFile)) {
        @copy($tmpFile, $cacheFile);
        @unlink($tmpFile);
    }
} else {
    @unlink($tmpFile);
    @unlink($tmpFile . '.marks.json');
}
}

if ($lockFp) {
    @flock($lockFp, LOCK_UN);
    @fclose($lockFp);
}

clearstatcache(true, $cacheFile);
if (file_exists($cacheFile) && filesize($cacheFile) > 500) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=31536000');
    header('X-Voice-Engine: Edge-Neural-Male');
    header('X-Voice-Dialect: ' . $dialect);
    readfile($cacheFile);
    exit;
}

// STRICT MALE-ONLY POLICY: Never fallback to Google Translate female TTS under any circumstance.
http_response_code(503);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => false, 'error' => 'Male neural voice synthesis temporarily busy, retry']);

