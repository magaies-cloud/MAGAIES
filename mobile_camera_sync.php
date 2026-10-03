<?php
// ====================================================================
// MAGAIEES ERP - Mobile Live Camera & WhatsApp Sync Bridge 2026
// جسر الكاميرا المباشر لمزامنة صور الهاتف والواتساب مع شاشة اللابتوب
// ====================================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    exit;
}

$tempBaseDir = __DIR__ . '/uploads/recon_temp';
if (!is_dir($tempBaseDir)) {
    @mkdir($tempBaseDir, 0777, true);
}

// ────────────────────────────────────────────────────────────────────
// 1. AJAX API: Polling, Upload & QR Code Handlers
// ────────────────────────────────────────────────────────────────────
$jsonInput = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $jsonInput['action'] ?? ($_GET['action'] ?? ($_POST['action'] ?? ''));
$session = preg_replace('/[^a-zA-Z0-9_\-]/', '', $jsonInput['session'] ?? ($_GET['session'] ?? ($_POST['session'] ?? '')));
$side = in_array($jsonInput['side'] ?? ($_GET['side'] ?? ($_POST['side'] ?? '')), ['A', 'B']) ? ($jsonInput['side'] ?? ($_GET['side'] ?? ($_POST['side'] ?? ''))) : 'A';

// Health Check / Ping Endpoint for Instant Mobile-Laptop Connectivity Verification
if ($action === 'ping') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'pong' => true,
        'time' => time(),
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '127.0.0.1'),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    exit;
}

// QR Code Image Generator
if ($action === 'qr') {
    $text = $_GET['text'] ?? '';
    header('Location: https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=1&data=' . urlencode($text));
    exit;
}

// Endpoints Discovery for Dynamic QR selection (Cloud, Wi-Fi LAN, Local, Tunnel)
if ($action === 'get_endpoints') {
    header('Content-Type: application/json; charset=utf-8');
    
    // 1. Cloud Production URL (Fast, Resilient HTTPS - Works everywhere)
    $cloudUrl = 'https://c.elsajana.com/mobile_camera_sync.php';
    
    // 2. Real LAN IP
    $lanIp = gethostbyname(gethostname());
    if (empty($lanIp) || $lanIp === '127.0.0.1') {
        $lanIp = '192.168.1.101';
    }
    $wifiUrl = 'http://' . $lanIp . '/ELSAJANA2/magaies/mobile_camera_sync.php';
    
    // 3. Live Tunnel (Only if verified and not dead localtunnel)
    $tunnelUrl = '';
    $tunnelFile = __DIR__ . '/LIVE_TUNNEL_URL.txt';
    if (file_exists($tunnelFile)) {
        $raw = trim(file_get_contents($tunnelFile));
        if (filter_var($raw, FILTER_VALIDATE_URL) && !str_contains($raw, 'loca.lt')) {
            $tunnelUrl = rtrim($raw, '/') . '/ELSAJANA2/magaies/mobile_camera_sync.php';
        }
    }
    
    // 4. Localhost URL
    $localUrl = 'http://localhost/ELSAJANA2/magaies/mobile_camera_sync.php';
    
    echo json_encode([
        'success' => true,
        'cloud_url' => $cloudUrl,
        'wifi_url' => $wifiUrl,
        'local_url' => $localUrl,
        'tunnel_url' => $tunnelUrl,
        'lan_ip' => $lanIp,
        'current_origin' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!empty($session)) {
    $sessionDir = $tempBaseDir . '/' . $session;

    // A. POLL: Laptop checks for newly received photos from Phone
    if ($action === 'poll') {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_dir($sessionDir)) {
            echo json_encode(['success' => true, 'has_files' => false, 'photos' => []]);
            exit;
        }

        $files = glob($sessionDir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        if (empty($files)) {
            echo json_encode(['success' => true, 'has_files' => false, 'photos' => []]);
            exit;
        }

        $photos = [];
        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $mime = mime_content_type($filePath) ?: 'image/jpeg';
            $data = file_get_contents($filePath);
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            $photos[] = [
                'name' => $fileName,
                'mime_type' => $mime,
                'dataUrl' => $base64,
                'size' => filesize($filePath)
            ];
            // Remove after delivery so they are not fetched repeatedly
            @unlink($filePath);
        }

        echo json_encode([
            'success' => true,
            'has_files' => true,
            'count' => count($photos),
            'photos' => $photos
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // B. UPLOAD: Mobile phone sends taken photos to Laptop session
    if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0777, true);
        }

        $savedCount = 0;

        // B1. Handle multipart $_FILES
        if (!empty($_FILES['photos'])) {
            $files = $_FILES['photos'];
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $fileCount; $i++) {
                $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $origName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $err = is_array($files['error']) ? $files['error'][$i] : $files['error'];

                if ($err === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $ext = 'jpg';
                    $targetName = sprintf('snap_%s_%04d_%s.%s', $side, time() % 10000, $i + 1, $ext);
                    $dest = $sessionDir . '/' . $targetName;
                    if (move_uploaded_file($tmpName, $dest)) {
                        $savedCount++;
                    }
                }
            }
        }

        // B2. Handle JSON base64 payloads
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $jsonData = json_decode($rawInput, true);
            if (!empty($jsonData['photos']) && is_array($jsonData['photos'])) {
                foreach ($jsonData['photos'] as $idx => $p) {
                    $base64 = is_array($p) ? ($p['dataUrl'] ?? $p['base64'] ?? '') : $p;
                    if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64, $matches)) {
                        $imgData = base64_decode($matches[2]);
                        if ($imgData !== false) {
                            $targetName = sprintf('snap_%s_%04d_%s.jpg', $side, time() % 10000, $idx + 1);
                            file_put_contents($sessionDir . '/' . $targetName, $imgData);
                            $savedCount++;
                        }
                    }
                }
            }
        }

        echo json_encode([
            'success' => $savedCount > 0,
            'count' => $savedCount,
            'message' => $savedCount > 0 
                ? "تم إرسال {$savedCount} صور بنجاح إلى شاشة اللابتوب! 🚀" 
                : "لم يتم حفظ أي صورة، يرجى المحاولة مرة أخرى."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ────────────────────────────────────────────────────────────────────
// 2. MOBILE WEB UI: Rendered when user scans QR Code on their phone
// ────────────────────────────────────────────────────────────────────
$session = htmlspecialchars($session, ENT_QUOTES, 'UTF-8');
$sideLabel = $side === 'A' ? 'الطرف الأول (كشف حسابنا)' : 'الطرف الثاني (المصنع / المورد)';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>جسر كاميرا الجوال | Al-Sajana ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/tailwind.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: radial-gradient(circle at 50% 10%, #1e1b4b 0%, #0f172a 60%, #020617 100%);
            color: #f8fafc;
            min-height: 100vh;
            touch-action: manipulation;
        }
        .neon-glow-btn {
            box-shadow: 0 0 25px rgba(244, 63, 94, 0.4), 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: all 0.2s ease;
        }
        .neon-glow-btn:active {
            transform: scale(0.96);
        }
        .pulse-beacon {
            animation: pulseBeacon 2s infinite;
        }
        @keyframes pulseBeacon {
            0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(56, 189, 248, 0); }
            100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
        }
        .filmstrip-scroll::-webkit-scrollbar {
            height: 6px;
        }
        .filmstrip-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="p-4 flex flex-col justify-between max-w-md mx-auto">

    <!-- Header & Sync Status -->
    <header class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-indigo-600 flex items-center justify-center text-white text-lg shadow-lg">
                    <i class="fa-solid fa-camera"></i>
                </span>
                <div>
                    <h1 class="font-black text-lg text-slate-100 leading-tight">جسر كاميرا الجوال 📱</h1>
                    <span class="text-xs text-slate-400 font-bold">مزامنة فورية مع شاشة اللابتوب</span>
                </div>
            </div>
            <span id="mobile-conn-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 pulse-beacon">
                <span id="mobile-conn-dot" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span id="mobile-conn-text">جاري فحص الاتصال...</span>
            </span>
        </div>

        <!-- Network Helper Banner (Shown if direct LAN connectivity has issues) -->
        <div id="mobile-network-helper" class="hidden p-3 rounded-2xl bg-amber-950/80 border border-amber-500/50 text-amber-200 text-xs space-y-2">
            <div class="flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base"></i>
                <span>تنبيه: تعذر الاتصال المباشر عبر الواي فاي</span>
            </div>
            <p class="text-[11px] leading-relaxed text-amber-300/90 font-medium">
                يبدو أن راوتر الشبكة يفصل الأجهزة عن بعضها (AP Isolation). يمكنك النقر أدناه للتحويل إلى السيرفر السحابي الآمن الذي يعمل فوراً وبدون قيود:
            </p>
            <button type="button" onclick="switchToCloudEndpoint()" class="w-full py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-black text-xs shadow flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-cloud"></i>
                <span>التحويل للسيرفر السحابي (Elsajana HTTPS) ⚡</span>
            </button>
        </div>

        <!-- Target Side Pill -->
        <div class="p-3 rounded-2xl bg-slate-900/90 border border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-400 font-bold">الجهة المستهدفة باللابتوب:</span>
            <span class="font-black px-2.5 py-1 rounded-lg <?php echo $side === 'A' ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40' : 'bg-purple-500/20 text-purple-300 border border-purple-500/40'; ?>">
                <?php echo $sideLabel; ?>
            </span>
        </div>
    </header>

    <!-- Main Capture Controls -->
    <main class="my-6 space-y-5">
        
        <!-- Big Dual Touch Buttons -->
        <div class="grid grid-cols-2 gap-3.5">
            
            <!-- 1. Direct Camera Shoot (Native OS Camera) -->
            <button type="button" onclick="document.getElementById('mobile-native-camera-input').click()" class="p-5 rounded-3xl bg-gradient-to-tr from-rose-600 to-pink-600 text-white flex flex-col items-center justify-center gap-2.5 neon-glow-btn cursor-pointer">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shadow-inner">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <span class="font-black text-sm">التقاط بالكاميرا</span>
                <span class="text-[10px] text-rose-100 font-bold">تصوير صفحة 1، 2، 3...</span>
            </button>
            <input type="file" id="mobile-native-camera-input" accept="image/*" capture="environment" class="hidden" onchange="handleMobileFilesSelected(event)">

            <!-- 2. Phone Gallery / Studio (Multiple Images) -->
            <button type="button" onclick="document.getElementById('mobile-gallery-input').click()" class="p-5 rounded-3xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex flex-col items-center justify-center gap-2.5 shadow-xl cursor-pointer active:scale-95 transition">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl shadow-inner">
                    <i class="fa-solid fa-images"></i>
                </div>
                <span class="font-black text-sm">استوديو الصور</span>
                <span class="text-[10px] text-indigo-100 font-bold">اختيار عدة صور دفعة واحدة</span>
            </button>
            <input type="file" id="mobile-gallery-input" accept="image/*" multiple class="hidden" onchange="handleMobileFilesSelected(event)">
        </div>

        <!-- Processing Feedback Toast -->
        <div id="mobile-compress-toast" class="hidden p-3 rounded-2xl bg-slate-900 border border-sky-500/40 text-sky-300 text-xs font-black flex items-center justify-center gap-2">
            <i class="fa-solid fa-circle-notch fa-spin text-sky-400"></i>
            <span>جاري تحسين وضغط الصور للذكاء الاصطناعي...</span>
        </div>

        <!-- 3. Filmstrip Preview of Captured Photos -->
        <div class="p-4 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-layer-group text-sky-400"></i>
                    <span>الصور الجاهزة للإرسال:</span>
                </span>
                <span id="mobile-photos-count-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-slate-800 text-slate-400 border border-slate-700">
                    0 صور
                </span>
            </div>

            <div id="mobile-empty-state" class="py-8 text-center text-slate-500 space-y-1.5">
                <i class="fa-solid fa-camera-rotate text-3xl text-slate-600"></i>
                <p class="text-xs font-bold">اضغط على زر الكاميرا لتصوير كشف الحساب، أو اختر صوراً من الاستوديو.</p>
            </div>

            <div id="mobile-filmstrip" class="filmstrip-scroll flex items-center gap-2.5 overflow-x-auto pb-2 hidden">
                <!-- Dynamically injected thumbnails -->
            </div>
        </div>

    </main>

    <!-- Footer: Big Send to Laptop Action -->
    <footer class="space-y-3 pb-4">
        <button type="button" id="mobile-send-btn" onclick="sendPhotosToLaptop()" disabled class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-base shadow-xl flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed">
            <i class="fa-solid fa-paper-plane text-lg"></i>
            <span id="mobile-send-btn-text">إرسال الصور إلى شاشة اللابتوب 🚀</span>
        </button>

        <!-- Success Feedback Modal / Alert -->
        <div id="mobile-success-toast" class="hidden p-3.5 rounded-2xl bg-emerald-950/90 border-2 border-emerald-500/60 text-emerald-200 text-center text-xs font-black space-y-1 animate-bounce">
            <div class="flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check text-lg text-emerald-400"></i>
                <span id="mobile-success-msg">تم تسليم الصور لشاشة اللابتوب بنجاح!</span>
            </div>
            <span class="text-[10px] text-emerald-400 font-normal">يقوم الذكاء الاصطناعي الآن بفرزها وتدقيق كشف الحساب فورياً.</span>
        </div>
    </footer>

    <script>
        const SESSION_ID = '<?php echo $session; ?>';
        const TARGET_SIDE = '<?php echo $side; ?>';
        const CLOUD_FALLBACK_URL = 'https://c.elsajana.com/mobile_camera_sync.php';
        let capturedPhotos = [];

        // 1. High-Performance Client-Side Image Compressor (Reduces 12MB photos to ~250KB)
        function compressPhoto(file, maxWidth = 1600, quality = 0.82) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.width;
                        let h = img.height;
                        if (w > maxWidth || h > maxWidth) {
                            if (w > h) {
                                h = Math.round((h * maxWidth) / w);
                                w = maxWidth;
                            } else {
                                w = Math.round((w * maxWidth) / h);
                                h = maxWidth;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        const dataUrl = canvas.toDataURL('image/jpeg', quality);
                        canvas.toBlob((blob) => {
                            const cleanName = (file.name || 'photo.jpg').replace(/\.[^.]+$/, '') + '.jpg';
                            const compressedFile = new File([blob], cleanName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve({ file: compressedFile, dataUrl: dataUrl, size: blob.size });
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = () => {
                        resolve({ file: file, dataUrl: e.target.result, size: file.size });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        // 2. Mobile File Selection Handler
        async function handleMobileFilesSelected(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;

            const compressToast = document.getElementById('mobile-compress-toast');
            if (compressToast) compressToast.classList.remove('hidden');

            for (const file of files) {
                try {
                    const compressed = await compressPhoto(file, 1600, 0.82);
                    capturedPhotos.push({
                        id: Date.now() + Math.random(),
                        file: compressed.file,
                        name: compressed.file.name,
                        dataUrl: compressed.dataUrl,
                        size: compressed.size
                    });
                } catch(err) {
                    console.warn('Compression error, using raw file:', err);
                    capturedPhotos.push({
                        id: Date.now() + Math.random(),
                        file: file,
                        name: file.name || `photo_${capturedPhotos.length + 1}.jpg`,
                        dataUrl: ''
                    });
                }
            }

            if (compressToast) compressToast.classList.add('hidden');
            e.target.value = '';
            renderMobileFilmstrip();

            if (navigator.vibrate) {
                try { navigator.vibrate(50); } catch(err) {}
            }
        }

        function renderMobileFilmstrip() {
            const strip = document.getElementById('mobile-filmstrip');
            const empty = document.getElementById('mobile-empty-state');
            const badge = document.getElementById('mobile-photos-count-badge');
            const sendBtn = document.getElementById('mobile-send-btn');
            const sendBtnText = document.getElementById('mobile-send-btn-text');

            const count = capturedPhotos.length;
            badge.textContent = `${count} صور`;

            if (count === 0) {
                empty.classList.remove('hidden');
                strip.classList.add('hidden');
                strip.innerHTML = '';
                sendBtn.disabled = true;
                sendBtnText.textContent = 'إرسال الصور إلى شاشة اللابتوب 🚀';
            } else {
                empty.classList.add('hidden');
                strip.classList.remove('hidden');
                sendBtn.disabled = false;
                sendBtnText.textContent = `إرسال (${count}) صور إلى اللابتوب فوراً 🚀`;

                strip.innerHTML = capturedPhotos.map((p, idx) => `
                    <div class="relative w-20 h-24 rounded-2xl overflow-hidden border-2 border-slate-700 bg-black shrink-0 shadow-md">
                        <img src="${p.dataUrl}" class="w-full h-full object-cover" alt="لقطة ${idx+1}">
                        <span class="absolute bottom-0 inset-x-0 bg-slate-950/85 text-[10px] text-center font-black text-rose-300 py-0.5">#${idx+1} (${Math.round((p.size||0)/1024)}KB)</span>
                        <button type="button" onclick="removeMobilePhoto(${p.id})" class="absolute top-1 left-1 w-6 h-6 rounded-full bg-rose-600/90 text-white flex items-center justify-center text-xs font-black shadow" title="حذف">✕</button>
                    </div>
                `).join('');
            }
        }

        function removeMobilePhoto(id) {
            capturedPhotos = capturedPhotos.filter(p => p.id !== id);
            renderMobileFilmstrip();
        }

        // 3. Multi-Channel Upload Engine (Primary origin with automatic Cloud fallback)
        async function sendPhotosToLaptop() {
            if (!capturedPhotos.length) return;
            const btn = document.getElementById('mobile-send-btn');
            const btnText = document.getElementById('mobile-send-btn-text');
            const toast = document.getElementById('mobile-success-toast');

            btn.disabled = true;
            btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> جاري الإرسال للابتوب...';

            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('session', SESSION_ID);
            formData.append('side', TARGET_SIDE);

            capturedPhotos.forEach((p, idx) => {
                formData.append('photos[]', p.file, p.name);
            });

            let uploadSuccess = false;
            let responseMessage = '';

            // Attempt A: Direct local / current origin
            try {
                const currentEndpoint = `mobile_camera_sync.php?action=upload&session=${encodeURIComponent(SESSION_ID)}&side=${encodeURIComponent(TARGET_SIDE)}`;
                const res = await fetch(currentEndpoint, {
                    method: 'POST',
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data && data.success) {
                        uploadSuccess = true;
                        responseMessage = data.message || 'تم تسليم الصور لشاشة اللابتوب بنجاح!';
                    }
                }
            } catch (errLocal) {
                console.warn('Local/primary upload failed, attempting Cloud fallback:', errLocal);
            }

            // Attempt B: Automatic Cloud Fallback (Bypasses AP Isolation & Router Restrictions)
            if (!uploadSuccess && !window.location.hostname.includes('elsajana.com')) {
                try {
                    btnText.innerHTML = '<i class="fa-solid fa-cloud-arrow-up fa-spin"></i> جاري النقل عبر السيرفر السحابي...';
                    const cloudEndpoint = `${CLOUD_FALLBACK_URL}?action=upload&session=${encodeURIComponent(SESSION_ID)}&side=${encodeURIComponent(TARGET_SIDE)}`;
                    const cloudRes = await fetch(cloudEndpoint, {
                        method: 'POST',
                        body: formData
                    });

                    if (cloudRes.ok) {
                        const cloudData = await cloudRes.json();
                        if (cloudData && cloudData.success) {
                            uploadSuccess = true;
                            responseMessage = 'تم تسليم الصور لشاشة اللابتوب بنجاح عبر السيرفر السحابي البديل ⚡';
                        }
                    }
                } catch (errCloud) {
                    console.error('Cloud fallback also failed:', errCloud);
                }
            }

            // Handle Result
            if (uploadSuccess) {
                if (navigator.vibrate) {
                    try { navigator.vibrate([100, 50, 100]); } catch(err) {}
                }
                toast.classList.remove('hidden');
                document.getElementById('mobile-success-msg').textContent = responseMessage;

                capturedPhotos = [];
                renderMobileFilmstrip();

                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 5000);
            } else {
                alert('⚠️ تعذر إرسال الصور للابتوب مباشرة.\n\nالسبب المحتمل: راوتر الواي فاي يفصل الأجهزة عن بعضها.\nيرجى النقر على زر "التحويل للسيرفر السحابي" في أعلى الشاشة والمحاولة مجدداً.');
                document.getElementById('mobile-network-helper')?.classList.remove('hidden');
            }

            btn.disabled = capturedPhotos.length === 0;
            btnText.textContent = 'إرسال الصور إلى شاشة اللابتوب 🚀';
        }

        // 4. Instant Connectivity Diagnostic
        async function testSyncConnection() {
            const badge = document.getElementById('mobile-conn-badge');
            const dot = document.getElementById('mobile-conn-dot');
            const text = document.getElementById('mobile-conn-text');
            const helper = document.getElementById('mobile-network-helper');

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3500);

                const res = await fetch(`mobile_camera_sync.php?action=ping&session=${encodeURIComponent(SESSION_ID)}&_t=${Date.now()}`, {
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                if (res.ok) {
                    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 pulse-beacon';
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
                    text.textContent = 'متصل وجاهز للنقل ⚡';
                    if (helper) helper.classList.add('hidden');
                    return;
                }
            } catch(e) {}

            // If not connected
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-300 border border-amber-500/40';
            dot.className = 'w-2 h-2 rounded-full bg-amber-400';
            text.textContent = 'تنبيه: راجع الاتصال';
            if (!window.location.hostname.includes('elsajana.com')) {
                if (helper) helper.classList.remove('hidden');
            }
        }

        function switchToCloudEndpoint() {
            const currentParams = window.location.search || `?session=${SESSION_ID}&side=${TARGET_SIDE}`;
            window.location.href = `${CLOUD_FALLBACK_URL}${currentParams}`;
        }

        // Run connectivity test on load
        window.addEventListener('DOMContentLoaded', testSyncConnection);
    </script>
</body>
</html>
