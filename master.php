<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP 2026 - UNIVERSAL MASTER GATEWAY & LINK ORCHESTRATOR
 * ═══════════════════════════════════════════════════════════════════════════
 * بوابة الرابط الماستر الشامل لتشغيل أي رابط والتكيف مع جميع الشاشات والأجهزة
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$defaultUrl = 'https://elsajana.com/index.php';
$requestedUrl = $_GET['url'] ?? $_GET['target'] ?? $_GET['link'] ?? $defaultUrl;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>بوابة الرابط الماستر الذكي - Antigravity Universal Gateway</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="js/master-device-engine.js?v=2026_master_v1"></script>
    <style>
        :root {
            --bg-dark: #070b14;
            --surface-dark: #0f172a;
            --surface-card: rgba(30, 41, 59, 0.7);
            --border-glow: rgba(56, 189, 248, 0.25);
            --primary: #38bdf8;
            --primary-gradient: linear-gradient(135deg, #0284c7, #2563eb 50%, #7c3aed);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: radial-gradient(circle at 50% 0%, rgba(56, 189, 248, 0.12) 0%, transparent 65%),
                              radial-gradient(circle at 100% 100%, rgba(124, 58, 237, 0.08) 0%, transparent 50%);
        }

        /* Header Navigation */
        .master-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .master-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .master-brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
        }

        .master-brand-text h1 {
            font-size: 1.15rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ffffff 40%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .master-brand-text p {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* URL Controller Bar */
        .url-controller-card {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-glow);
            border-radius: 16px;
            padding: 16px 20px;
            margin: 15px auto;
            width: 95%;
            max-width: 1200px;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        }

        .url-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .url-input-container {
            flex: 1;
            min-width: 280px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .url-input-container i {
            position: absolute;
            right: 14px;
            color: var(--primary);
            font-size: 1rem;
        }

        .url-input-field {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px 42px 12px 16px;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'Inter', 'Tajawal', sans-serif;
            direction: ltr;
            text-align: left;
            transition: all 0.2s;
            outline: none;
        }

        .url-input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        .btn-launch {
            padding: 12px 24px;
            background: var(--primary-gradient);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
            white-space: nowrap;
        }

        .btn-launch:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.6);
        }

        /* Live Device Diagnostics Capsule */
        .live-diag-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .diag-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .diag-pill-icon {
            font-size: 1.3rem;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .diag-pill-content h4 {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 2px;
        }

        .diag-pill-content p {
            font-size: 0.85rem;
            font-weight: 850;
            color: #ffffff;
        }

        /* Interactive Device Selector Matrix */
        .device-selector-tray {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 10px 0 5px 0;
            scrollbar-width: thin;
        }

        .device-chip {
            padding: 8px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 750;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .device-chip:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .device-chip.active {
            background: rgba(56, 189, 248, 0.2);
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
        }

        /* Adaptive Frame Viewer */
        .viewer-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 20px 25px 20px;
            width: 100%;
        }

        .device-frame-shell {
            background: #000000;
            border: 3px solid rgba(255, 255, 255, 0.15);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7), 0 0 30px rgba(56, 189, 248, 0.15);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            max-width: 100%;
        }

        .device-frame-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 24px;
            background: #000000;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .device-frame-notch-cam {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #1e293b;
        }

        .device-frame-notch-mic {
            width: 40px;
            height: 4px;
            border-radius: 4px;
            background: #1e293b;
        }

        .target-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #0c101b;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="master-header">
        <div class="master-brand">
            <div class="master-brand-logo">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div class="master-brand-text">
                <h1>رابط مقاييس الماستر الذكي 2026</h1>
                <p>بوابة التكيف التلقائي مع جميع شاشات الجوالات والتابلت واللابتوب والكمبيوتر</p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" onclick="openDirectFullscreen()" class="btn-launch" style="padding: 8px 16px; font-size: 0.85rem; background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15);">
                <i class="fa-solid fa-up-right-from-square"></i>
                <span>فتح بملء الشاشة</span>
            </button>
            <a href="login.php" class="btn-launch" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none;">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>شاشة الدخول المباشرة</span>
            </a>
        </div>
    </header>

    <!-- URL Controller & Diagnostic Card -->
    <div class="url-controller-card">
        <div class="url-input-wrapper">
            <div class="url-input-container">
                <i class="fa-solid fa-globe"></i>
                <input type="text" id="master-url-input" class="url-input-field" value="<?php echo htmlspecialchars($requestedUrl); ?>" placeholder="أدخل أي رابط تريده ليعمل متكيفاً مع كل الشاشات">
            </div>
            <button type="button" class="btn-launch" onclick="launchCustomUrl()">
                <i class="fa-solid fa-bolt"></i>
                <span>تشغيل الرابط المتكيف</span>
            </button>
        </div>

        <!-- Live Device Diagnostic Grid -->
        <div class="live-diag-grid">
            <div class="diag-pill">
                <div class="diag-pill-icon" id="diag-icon">📱</div>
                <div class="diag-pill-content">
                    <h4>الجهاز المكتشف حالياً</h4>
                    <p id="diag-device-name">جاري الكشف...</p>
                </div>
            </div>

            <div class="diag-pill">
                <div class="diag-pill-icon"><i class="fa-solid fa-expand" style="color:#38bdf8;"></i></div>
                <div class="diag-pill-content">
                    <h4>أبعاد الشاشة والنقاء</h4>
                    <p id="diag-screen-dims">0 × 0 pt (@1x)</p>
                </div>
            </div>

            <div class="diag-pill">
                <div class="diag-pill-icon"><i class="fa-solid fa-film" style="color:#c084fc;"></i></div>
                <div class="diag-pill-content">
                    <h4>نسبة الشاشة والأطراف</h4>
                    <p id="diag-screen-ratio">قياسي 16:9</p>
                </div>
            </div>

            <div class="diag-pill">
                <div class="diag-pill-icon"><i class="fa-solid fa-shield-check" style="color:#10b981;"></i></div>
                <div class="diag-pill-content">
                    <h4>حالة التكيف التلقائي</h4>
                    <p style="color:#10b981;">نشط 100% متطابق</p>
                </div>
            </div>
        </div>

        <!-- Interactive Device Simulation Selector -->
        <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1);">
            <div style="font-size: 0.75rem; color: #38bdf8; font-weight: 800; margin-bottom: 6px;">
                <i class="fa-solid fa-sliders ml-1"></i> اختر أي مقاس جهاز لمحاكاته أو اتركه على (جهازك الفعلي):
            </div>
            <div class="device-selector-tray" id="preset-selector-tray">
                <button type="button" class="device-chip active" onclick="applyPreset('native')">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> <span>جهازي الفعلي (تلقائي)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('iphone-promax')">
                    🍎 <span>iPhone 16 Pro Max (430×932)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('iphone-std')">
                    🍎 <span>iPhone 15 / X (393×852)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('huawei-mate')">
                    🌺 <span>Huawei Mate 60 / 20 Pro (Curved OLED)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('huawei-p')">
                    💎 <span>Huawei Pura 70 / P20 (Compact)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('honor-magic')">
                    ⚡ <span>Honor Magic 6 Pro (Quad-Curved)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('samsung-ultra')">
                    🌌 <span>Samsung S24 Ultra (Dynamic AMOLED)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('samsung-fold')">
                    📖 <span>Samsung Galaxy Fold (Tablet Mode)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('sony-xperia')">
                    🎬 <span>Sony Xperia (21:9 CinemaWide)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('motorola-edge')">
                    Ⓜ️ <span>Motorola Edge (Endless 165Hz)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('ipad-pro')">
                    📟 <span>Apple iPad Pro 12.9" (1024×1366)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('android-tablet')">
                    📟 <span>Huawei / Samsung Tab (800×1280)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('laptop-14')">
                    💻 <span>لابتوب 14 بوصة (1366×768)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('desktop-fhd')">
                    🖥️ <span>لابتوب / كمبيوتر Full HD (1920×1080)</span>
                </button>
                <button type="button" class="device-chip" onclick="applyPreset('desktop-ultrawide')">
                    🖥️ <span>شاشة فائقة العرض (Ultrawide 21:9)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Adaptive Frame Viewer -->
    <main class="viewer-container">
        <div id="frame-shell" class="device-frame-shell" style="width: 100%; height: 75vh;">
            <div id="frame-notch" class="device-frame-notch" style="display: none;">
                <div class="device-frame-notch-cam"></div>
                <div class="device-frame-notch-mic"></div>
            </div>
            <iframe id="master-target-frame" class="target-iframe" src="<?php echo htmlspecialchars($requestedUrl); ?>" allow="camera; microphone; geolocation; clipboard-read; clipboard-write; fullscreen"></iframe>
        </div>
    </main>

    <script>
        const PRESETS = {
            'native': { name: 'جهازي الفعلي', width: '100%', height: '78vh', radius: '16px', notch: false },
            'iphone-promax': { name: 'iPhone 16 Pro Max', width: '430px', height: '890px', radius: '48px', notch: true },
            'iphone-std': { name: 'iPhone 15 / X', width: '393px', height: '830px', radius: '44px', notch: true },
            'huawei-mate': { name: 'Huawei Mate 60 / 20 Pro', width: '412px', height: '880px', radius: '38px', notch: false },
            'huawei-p': { name: 'Huawei Pura 70 / P20', width: '390px', height: '820px', radius: '32px', notch: false },
            'honor-magic': { name: 'Honor Magic 6 Pro', width: '412px', height: '890px', radius: '42px', notch: false },
            'samsung-ultra': { name: 'Samsung S24 Ultra', width: '412px', height: '890px', radius: '20px', notch: false },
            'samsung-fold': { name: 'Samsung Galaxy Z Fold', width: '768px', height: '920px', radius: '24px', notch: false },
            'sony-xperia': { name: 'Sony Xperia (21:9)', width: '384px', height: '896px', radius: '16px', notch: false },
            'motorola-edge': { name: 'Motorola Edge Curved', width: '412px', height: '890px', radius: '36px', notch: false },
            'ipad-pro': { name: 'Apple iPad Pro 12.9"', width: '1024px', height: '820px', radius: '28px', notch: false },
            'android-tablet': { name: 'Huawei/Samsung Tab', width: '800px', height: '780px', radius: '24px', notch: false },
            'laptop-14': { name: 'لابتوب 14 بوصة', width: '1080px', height: '680px', radius: '14px', notch: false },
            'desktop-fhd': { name: 'كمبيوتر Full HD', width: '1240px', height: '750px', radius: '14px', notch: false },
            'desktop-ultrawide': { name: 'شاشة عريضة 21:9', width: '1380px', height: '680px', radius: '14px', notch: false }
        };

        function launchCustomUrl() {
            const input = document.getElementById('master-url-input');
            let url = input ? input.value.trim() : '';
            if (!url) return;
            if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/')) {
                url = 'https://' + url;
            }
            const frame = document.getElementById('master-target-frame');
            if (frame) {
                frame.src = url;
            }
            // تحديث رابط المتصفح دون إعادة تحميل
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('url', url);
            window.history.pushState({}, '', newUrl);
        }

        function openDirectFullscreen() {
            const input = document.getElementById('master-url-input');
            const url = input ? input.value.trim() : 'https://elsajana.com/index.php';
            window.location.href = url;
        }

        function applyPreset(key) {
            const preset = PRESETS[key] || PRESETS['native'];
            const shell = document.getElementById('frame-shell');
            const notch = document.getElementById('frame-notch');
            const chips = document.querySelectorAll('.device-chip');

            chips.forEach(c => c.classList.remove('active'));
            const targetBtn = Array.from(chips).find(c => c.getAttribute('onclick').includes(key));
            if (targetBtn) targetBtn.classList.add('active');

            if (shell) {
                shell.style.width = preset.width;
                shell.style.height = preset.height;
                shell.style.borderRadius = preset.radius;
            }
            if (notch) {
                notch.style.display = preset.notch ? 'flex' : 'none';
            }
        }

        // تحديث كبسولة التشخيص التلقائية
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.MasterDeviceEngine !== 'undefined') {
                const dev = window.MasterDeviceEngine.detect();
                const devNameEl = document.getElementById('diag-device-name');
                const devIconEl = document.getElementById('diag-icon');
                const screenDimsEl = document.getElementById('diag-screen-dims');
                const screenRatioEl = document.getElementById('diag-screen-ratio');

                if (devNameEl) devNameEl.innerHTML = `<span style="color:${dev.color};">${dev.icon} ${dev.brandName}</span> - ${dev.modelName}`;
                if (devIconEl) devIconEl.textContent = dev.icon;
                if (screenDimsEl) screenDimsEl.textContent = `${dev.viewportWidth} × ${dev.viewportHeight} pt (@${dev.dpr}x)`;
                if (screenRatioEl) screenRatioEl.textContent = `${dev.aspectRatio}:1 (${dev.aspectDesc})`;
            }

            // Enter key support on url input
            const inp = document.getElementById('master-url-input');
            if (inp) {
                inp.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') launchCustomUrl();
                });
            }
        });
    </script>
</body>
</html>
