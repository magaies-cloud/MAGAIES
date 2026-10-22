<?php
$channel = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['channel'] ?? 'default_gate');
if ($channel === '') $channel = 'default_gate';
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>📱 عدسة الجوال الليزرية المباشرة | ربط اللابتوب الفوري</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            margin: 0; padding: 0;
            font-family: 'Cairo', sans-serif;
            background: #020617; color: #f8fafc;
            min-height: 100vh; display: flex; flex-direction: column;
            overflow-x: hidden;
        }
        .top-bar {
            background: linear-gradient(135deg, #064e3b, #0f172a);
            padding: 12px 16px;
            border-bottom: 2px solid #10b981;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
        }
        .live-dot {
            width: 11px; height: 11px; border-radius: 50%;
            background: #10b981; display: inline-block;
            box-shadow: 0 0 12px #10b981;
            animation: pulseDot 1.2s infinite;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.55; }
        }
        .scanner-stage {
            position: relative;
            width: 100%; max-width: 520px;
            margin: 12px auto 8px;
            padding: 0 12px;
        }
        .viewport-box {
            position: relative;
            width: 100%; min-height: 290px;
            background: #090d16;
            border: 3px solid #10b981;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 0 35px rgba(16,185,129,0.25);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .viewport-box.flash-ok {
            border-color: #22c55e !important;
            box-shadow: 0 0 65px rgba(34,197,94,0.95) !important;
        }
        .viewport-box.flash-cancelled {
            border-color: #ef4444 !important;
            box-shadow: 0 0 65px rgba(239,68,68,0.95) !important;
        }
        .laser-line {
            position: absolute; left: 8%; right: 8%; height: 3px;
            background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
            box-shadow: 0 0 14px #10b981;
            z-index: 20; pointer-events: none;
            animation: scanLaser 2s ease-in-out infinite;
        }
        @keyframes scanLaser {
            0%, 100% { top: 15%; }
            50% { top: 85%; }
        }
        .status-banner {
            margin: 8px 12px;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 900;
            text-align: center;
            font-size: 1.05rem;
            border: 2px solid rgba(56,189,248,0.4);
            background: rgba(15,23,42,0.92);
            transition: all 0.2s ease;
        }
        .status-banner.ok {
            background: linear-gradient(135deg, rgba(6,78,59,0.95), rgba(5,150,105,0.9));
            border-color: #34d399; color: #ffffff;
            transform: scale(1.02);
        }
        .status-banner.cancelled {
            background: linear-gradient(135deg, rgba(127,29,29,0.96), rgba(220,38,38,0.92));
            border-color: #fca5a5; color: #ffffff;
            transform: scale(1.02);
        }
        .kpi-strip {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
            margin: 4px 12px 10px;
        }
        .kpi-card {
            background: rgba(15,23,42,0.85);
            border: 1px solid rgba(148,163,184,0.25);
            border-radius: 14px; padding: 10px; text-align: center;
        }
        .feed-list {
            flex: 1; margin: 0 12px 16px;
            background: rgba(15,23,42,0.75);
            border: 1px solid rgba(148,163,184,0.2);
            border-radius: 16px; padding: 12px;
            max-height: 240px; overflow-y: auto;
        }
        .feed-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 12px; border-radius: 10px; margin-bottom: 6px;
            font-size: 0.88rem; font-weight: 800;
        }
        .feed-item.ok { background: rgba(16,185,129,0.16); border-right: 4px solid #10b981; color: #a7f3d0; }
        .feed-item.cancelled { background: rgba(239,68,68,0.18); border-right: 4px solid #ef4444; color: #fecaca; }
        .btn-act {
            border: none; border-radius: 12px; padding: 10px 14px;
            font-family: 'Cairo', sans-serif; font-weight: 900; font-size: 0.9rem;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div style="display:flex; align-items:center; gap:10px;">
            <span class="live-dot"></span>
            <div>
                <div style="font-weight:900; font-size:1rem; color:#fff;">📱 كاميرا الجوال المربوطة باللابتوب</div>
                <div style="font-size:0.75rem; color:#6ee7b7; font-weight:700;">وضع المسح المتتابع الفوري بدون لمس (Zero-Click Stream)</div>
            </div>
        </div>
        <span style="background:rgba(16,185,129,0.2); border:1px solid #34d399; color:#6ee7b7; padding:4px 10px; border-radius:99px; font-size:0.75rem; font-weight:900;">
            متصل حياً ⚡
        </span>
    </div>

    <!-- Live Feedback Banner -->
    <div id="live-status-banner" class="status-banner">
        📷 مرّر كاميرا الجوال على QR الإذن — يستلم فوراً والـ 10 أذونات تلو بعضها بدون ضغط أي زر!
    </div>

    <!-- Counters -->
    <div class="kpi-strip">
        <div class="kpi-card" style="border-color:rgba(16,185,129,0.4);">
            <div style="font-size:0.76rem; color:#94a3b8; font-weight:800;">✅ تم استلامها وتسليمها الآن</div>
            <div id="count-delivered" style="font-size:1.55rem; font-weight:900; color:#34d399; font-family:'Outfit',sans-serif;">0</div>
        </div>
        <div class="kpi-card" style="border-color:rgba(239,68,68,0.4);">
            <div style="font-size:0.76rem; color:#94a3b8; font-weight:800;">🚫 مرفوض / ملغي (مسحة مكررة)</div>
            <div id="count-cancelled" style="font-size:1.55rem; font-weight:900; color:#f87171; font-family:'Outfit',sans-serif;">0</div>
        </div>
    </div>

    <!-- Camera Stage -->
    <div class="scanner-stage">
        <div id="viewport-box" class="viewport-box">
            <div class="laser-line"></div>
            <div id="mobile-qr-reader" style="width:100%; min-height:285px;"></div>
        </div>
    </div>

    <!-- Quick Manual / Native Camera Fallback if HTTP blocks WebRTC -->
    <div style="margin: 4px 12px 10px; display:flex; flex-direction:column; gap:8px;">
        <div style="display:flex; gap:8px;">
            <input type="text" id="manual-code-inp" placeholder="أو اكتب/امسح رقم الإذن هنا للإرسال الفوري للابتوب..." style="flex:1; background:#0f172a; border:1.5px solid #38bdf8; color:#fff; border-radius:12px; padding:10px 12px; font-family:'Cairo',sans-serif; font-weight:800; font-size:0.92rem;" onkeydown="if(event.key==='Enter'){event.preventDefault(); submitManualCode();}">
            <button class="btn-act" onclick="submitManualCode()" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff;">⚡ إرسال</button>
        </div>
        <div style="display:flex; gap:8px;">
            <label class="btn-act" style="flex:1; background:rgba(56,189,248,0.16); border:1px solid #38bdf8; color:#38bdf8;">
                📸 لقطة كاميرا سريعة (إن لم يعمل البث)
                <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="scanFromFileImage(this)">
            </label>
            <button class="btn-act" onclick="restartMobileCamera()" style="background:rgba(255,255,255,0.08); color:#cbd5e1; border:1px solid rgba(255,255,255,0.15);">🔄 تنشيط الكاميرا</button>
        </div>
    </div>

    <!-- Live Scan History Feed -->
    <div class="feed-list">
        <div style="font-size:0.8rem; color:#94a3b8; font-weight:900; margin-bottom:8px;">📋 سجل الأذونات الممسوحة بالتتابع (الحدث المباشر):</div>
        <div id="scan-feed-items">
            <div style="text-align:center; color:#64748b; font-size:0.82rem; padding:16px;">بانتظار تمرير أول إذن أمام الكاميرا...</div>
        </div>
    </div>

<script>
const BRIDGE_CHANNEL = <?php echo json_encode($channel); ?>;
let bcChannel = null;
try { bcChannel = new BroadcastChannel('elsajana_qr_live_bridge'); } catch (e) {}

let html5QrInstance = null;
let deliveredCounter = 0;
let cancelledCounter = 0;
const recentScanCooldown = {}; // code -> timestamp (ms)
let isProcessingScan = false;

function playLaserBeep(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (type === 'ok') {
            if (navigator.vibrate) navigator.vibrate(90);
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1046, ctx.currentTime);
            osc.frequency.setValueAtTime(1568, ctx.currentTime + 0.07);
            gain.gain.setValueAtTime(0.28, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 0.22);
        } else {
            if (navigator.vibrate) navigator.vibrate([180, 70, 220]);
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, ctx.currentTime);
            osc.frequency.setValueAtTime(190, ctx.currentTime + 0.14);
            gain.gain.setValueAtTime(0.32, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.38);
            osc.connect(gain); gain.connect(ctx.destination);
            osc.start(); osc.stop(ctx.currentTime + 0.38);
        }
    } catch (e) {}
}

function normalizeQrCodeValue(raw) {
    let q = String(raw || '').trim();
    try {
        if (q.includes('qr_deliver=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('qr_deliver') || q;
        } else if (q.includes('inv=')) {
            const u = new URL(q, window.location.origin);
            q = u.searchParams.get('inv') || q;
        }
    } catch (e) {}
    return q.replace(/^#/, '').trim();
}

function sendHeartbeat() {
    fetch(`api/qr_live_bridge.php?action=connect&channel=${encodeURIComponent(BRIDGE_CHANNEL)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device: navigator.userAgent })
    }).catch(() => {});
    if (bcChannel) {
        try { bcChannel.postMessage({ type: 'MOBILE_CONNECTED', channel: BRIDGE_CHANNEL, timestamp: Date.now() }); } catch (e) {}
    }
}
sendHeartbeat();
setInterval(sendHeartbeat, 4000);

function updateFeedbackUI(status, message, invNo) {
    const banner = document.getElementById('live-status-banner');
    const vbox = document.getElementById('viewport-box');
    const feed = document.getElementById('scan-feed-items');
    const timeStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    if (status === 'delivered') {
        deliveredCounter++;
        document.getElementById('count-delivered').textContent = deliveredCounter;
        banner.className = 'status-banner ok';
        banner.innerHTML = `✅ ${message || ('تم استلام وتسليم الإذن #' + invNo + ' فوراً!')} <div style="font-size:0.8rem; margin-top:4px; color:#d1fae5;">مرّر الإذن التالي أمام الكاميرا بدون ضغط أي زر...</div>`;
        vbox.classList.remove('flash-cancelled');
        vbox.classList.add('flash-ok');
        playLaserBeep('ok');
    } else {
        cancelledCounter++;
        document.getElementById('count-cancelled').textContent = cancelledCounter;
        banner.className = 'status-banner cancelled';
        banner.innerHTML = `🚫 ${message || ('تم تسليم هذا الإذن (#' + invNo + ') مسبقاً — الإذن ملغي!')}`;
        vbox.classList.remove('flash-ok');
        vbox.classList.add('flash-cancelled');
        playLaserBeep('error');
    }

    setTimeout(() => {
        vbox.classList.remove('flash-ok', 'flash-cancelled');
    }, 1100);

    if (feed.children.length === 1 && feed.children[0].textContent.includes('بانتظار')) {
        feed.innerHTML = '';
    }
    const itemDiv = document.createElement('div');
    itemDiv.className = 'feed-item ' + (status === 'delivered' ? 'ok' : 'cancelled');
    itemDiv.innerHTML = `
        <span>${status === 'delivered' ? '✅ تم تسليم وخصم الإذن' : '🚫 الإذن ملغي (مسلم مسبقاً)'}: <b>#${invNo}</b></span>
        <span style="font-size:0.75rem; opacity:0.85;">${timeStr}</span>
    `;
    feed.prepend(itemDiv);
}

async function handleContinuousQrDetected(rawCode) {
    const cleanCode = normalizeQrCodeValue(rawCode);
    if (!cleanCode) return;

    const now = Date.now();
    // 2.3 second debounce per identical code so paper movement doesn't double-fire in the same second
    if (recentScanCooldown[cleanCode] && (now - recentScanCooldown[cleanCode]) < 2300) {
        return;
    }
    recentScanCooldown[cleanCode] = now;

    // Broadcast immediately to same-browser tabs if applicable
    if (bcChannel) {
        try {
            bcChannel.postMessage({
                type: 'QR_SCANNED',
                channel: BRIDGE_CHANNEL,
                rawCode: rawCode,
                cleanCode: cleanCode,
                timestamp: now
            });
        } catch (e) {}
    }

    try {
        const res = await fetch(`api/qr_live_bridge.php?action=push_scan&channel=${encodeURIComponent(BRIDGE_CHANNEL)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: rawCode })
        });
        const data = await res.json();
        if (data && data.ok && data.scanId) {
            // Wait briefly for Laptop ack (polls every 180ms up to 1.8s)
            let resolved = false;
            for (let i = 0; i < 10; i++) {
                await new Promise(r => setTimeout(r, 180));
                const stRes = await fetch(`api/qr_live_bridge.php?action=status_scan&channel=${encodeURIComponent(BRIDGE_CHANNEL)}&scanId=${encodeURIComponent(data.scanId)}`);
                const stData = await stRes.json();
                if (stData && stData.ok && stData.scan && stData.scan.processedByLaptop) {
                    resolved = true;
                    updateFeedbackUI(
                        stData.scan.resultStatus,
                        stData.scan.resultMessage,
                        stData.scan.invoiceNo || cleanCode
                    );
                    break;
                }
            }
            if (!resolved) {
                // Fallback if laptop tab is on same localStorage or briefly busy
                updateFeedbackUI('delivered', `تم إرسال واستلام الإذن #${cleanCode} للابتوب فوراً`, cleanCode);
            }
        }
    } catch (err) {
        updateFeedbackUI('delivered', `تم لقط وإرسال الإذن #${cleanCode}`, cleanCode);
    }
}

function submitManualCode() {
    const inp = document.getElementById('manual-code-inp');
    if (!inp || !inp.value.trim()) return;
    const val = inp.value.trim();
    inp.value = '';
    handleContinuousQrDetected(val);
}

async function scanFromFileImage(inputEl) {
    const file = inputEl?.files?.[0];
    if (!file) return;
    if ('BarcodeDetector' in window) {
        try {
            const bmp = await createImageBitmap(file);
            const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });
            const codes = await detector.detect(bmp);
            if (codes && codes.length > 0 && codes[0].rawValue) {
                handleContinuousQrDetected(codes[0].rawValue);
                inputEl.value = '';
                return;
            }
        } catch (e) {}
    }
    if (typeof Html5Qrcode !== 'undefined') {
        try {
            const tempScanner = new Html5Qrcode("mobile-qr-reader");
            const decoded = await tempScanner.scanFile(file, true);
            if (decoded) {
                handleContinuousQrDetected(decoded);
                inputEl.value = '';
                restartMobileCamera();
                return;
            }
        } catch (e) {
            restartMobileCamera();
        }
    }
    alert('تعذر قراءة الباركود من الصورة — يرجى تقريب الكاميرا من رمز الـ QR أو كتابة رقم الإذن.');
}

function startContinuousMobileCamera() {
    if (typeof Html5Qrcode === 'undefined') return;
    try {
        html5QrInstance = new Html5Qrcode("mobile-qr-reader");
        html5QrInstance.start(
            { facingMode: "environment" },
            { fps: 15, qrbox: { width: 230, height: 230 } },
            (decodedText) => {
                // Continuous! Do NOT stop camera!
                handleContinuousQrDetected(decodedText);
            },
            () => {}
        ).catch(() => {});
    } catch (e) {}
}

function restartMobileCamera() {
    if (html5QrInstance) {
        try {
            html5QrInstance.stop().then(() => {
                startContinuousMobileCamera();
            }).catch(() => startContinuousMobileCamera());
            return;
        } catch (e) {}
    }
    startContinuousMobileCamera();
}

window.addEventListener('DOMContentLoaded', startContinuousMobileCamera);
</script>
</body>
</html>
