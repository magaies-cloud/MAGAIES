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

// QR Code Image Generator
if ($action === 'qr') {
    $text = $_GET['text'] ?? '';
    header('Location: https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=1&data=' . urlencode($text));
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
    <script src="https://cdn.tailwindcss.com"></script>
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 pulse-beacon">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>متصل باللابتوب</span>
            </span>
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
        let capturedPhotos = [];

        async function handleMobileFilesSelected(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;

            for (const file of files) {
                await new Promise(resolve => {
                    const reader = new FileReader();
                    reader.onload = () => {
                        capturedPhotos.push({
                            id: Date.now() + Math.random(),
                            file: file,
                            name: file.name || `photo_${capturedPhotos.length + 1}.jpg`,
                            dataUrl: reader.result
                        });
                        resolve();
                    };
                    reader.readAsDataURL(file);
                });
            }

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
                        <span class="absolute bottom-0 inset-x-0 bg-slate-950/85 text-[10px] text-center font-black text-rose-300 py-0.5">#${idx+1}</span>
                        <button type="button" onclick="removeMobilePhoto(${p.id})" class="absolute top-1 left-1 w-6 h-6 rounded-full bg-rose-600/90 text-white flex items-center justify-center text-xs font-black shadow" title="حذف">✕</button>
                    </div>
                `).join('');
            }
        }

        function removeMobilePhoto(id) {
            capturedPhotos = capturedPhotos.filter(p => p.id !== id);
            renderMobileFilmstrip();
        }

        async function sendPhotosToLaptop() {
            if (!capturedPhotos.length) return;
            const btn = document.getElementById('mobile-send-btn');
            const btnText = document.getElementById('mobile-send-btn-text');
            const toast = document.getElementById('mobile-success-toast');

            btn.disabled = true;
            btnText.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> جاري الإرسال للابتوب...';

            try {
                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('session', SESSION_ID);
                formData.append('side', TARGET_SIDE);

                capturedPhotos.forEach((p, idx) => {
                    formData.append('photos[]', p.file, p.name);
                });

                const res = await fetch(`mobile_camera_sync.php?action=upload&session=${encodeURIComponent(SESSION_ID)}&side=${encodeURIComponent(TARGET_SIDE)}`, {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();
                if (data.success) {
                    if (navigator.vibrate) {
                        try { navigator.vibrate([100, 50, 100]); } catch(err) {}
                    }
                    toast.classList.remove('hidden');
                    document.getElementById('mobile-success-msg').textContent = data.message || 'تم تسليم الصور لشاشة اللابتوب بنجاح!';
                    
                    // Clear after success
                    capturedPhotos = [];
                    renderMobileFilmstrip();

                    setTimeout(() => {
                        toast.classList.add('hidden');
                    }, 5000);
                } else {
                    alert('خطأ: ' + (data.message || 'تعذر الإرسال'));
                }
            } catch (e) {
                console.error('Upload error:', e);
                alert('فشل الاتصال باللابتوب، تأكد من اتصال الهاتف واللابتوب بنفس الشبكة.');
            } finally {
                btn.disabled = capturedPhotos.length === 0;
                btnText.textContent = 'إرسال الصور إلى شاشة اللابتوب 🚀';
            }
        }
    </script>
</body>
</html>
