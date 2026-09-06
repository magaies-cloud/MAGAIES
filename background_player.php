<?php
$channelKey = isset($_GET['ch']) ? htmlspecialchars($_GET['ch']) : 'majd';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بث مباشر في الخلفية | Al-Sajana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', system-ui, sans-serif;
            background: #020617;
            color: #f8fafc;
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            overflow: hidden;
            border: 2px solid #d97706;
            border-radius: 12px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 8px;
        }
        .title-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            color: #fde047;
        }
        .ping {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }
        .center-card {
            background: linear-gradient(135deg, rgba(15,23,42,0.9), rgba(30,27,75,0.8));
            border: 1px solid rgba(245,158,11,0.4);
            border-radius: 14px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 10px 0;
        }
        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(245,158,11,0.2);
            border: 1px solid rgba(245,158,11,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fde047;
            flex-shrink: 0;
        }
        .info { flex: 1; overflow: hidden; }
        .channel-name { font-size: 13px; font-weight: 900; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .channel-sub { font-size: 11px; font-weight: 600; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .waves { display: flex; align-items: center; gap: 3px; height: 18px; }
        .bar { width: 3px; background: #f59e0b; border-radius: 2px; animation: eq 1.2s infinite ease-in-out; }
        .bar:nth-child(1) { height: 8px; animation-delay: 0.1s; }
        .bar:nth-child(2) { height: 16px; animation-delay: 0.3s; background: #fbbf24; }
        .bar:nth-child(3) { height: 12px; animation-delay: 0.5s; background: #fde047; }
        .bar:nth-child(4) { height: 18px; animation-delay: 0.2s; background: #f59e0b; }
        @keyframes eq {
            0%, 100% { transform: scaleY(0.4); }
            50% { transform: scaleY(1.3); }
        }
        audio {
            width: 100%;
            height: 34px;
            border-radius: 10px;
            outline: none;
            background: #020617;
        }
        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            margin-top: 6px;
        }
        .badge-live {
            color: #ef4444;
            font-family: monospace;
            font-weight: 900;
        }
        .tabs-switch {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
        }
        .tab-btn {
            flex: 1;
            padding: 4px 6px;
            border-radius: 8px;
            background: rgba(30,41,59,0.7);
            border: 1px solid rgba(255,255,255,0.1);
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            text-align: center;
            transition: 0.2s;
        }
        .tab-btn:hover, .tab-btn.active {
            background: #f59e0b;
            color: #020617;
            border-color: #f59e0b;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title-badge">
            <span class="ping"></span>
            <span>مشغل البث الخلفي المستقل 24/7</span>
        </div>
        <span class="badge-live">🔴 LIVE</span>
    </div>

    <div class="tabs-switch">
        <button type="button" onclick="switchChannel('majd')" id="btn-majd" class="tab-btn">المجد</button>
        <button type="button" onclick="switchChannel('makkah')" id="btn-makkah" class="tab-btn">مكة المكرمة</button>
        <button type="button" onclick="switchChannel('madinah')" id="btn-madinah" class="tab-btn">المدينة</button>
    </div>

    <div class="center-card">
        <div class="icon-box" id="ch-icon">📺</div>
        <div class="info">
            <div class="channel-name" id="ch-title">تلفزيون وإذاعة المجد للقرآن الكريم</div>
            <div class="channel-sub" id="ch-sub">بث مباشر متواصل بدون انقطاع</div>
        </div>
        <div class="waves">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>

    <div>
        <audio id="popout-audio" controls preload="auto" autoplay></audio>
    </div>

    <div class="footer">
        <span style="color: #34d399;">✓ يعمل بشكل متواصل في خلفية الويندوز</span>
        <span style="color: #cbd5e1;">Al-Sajana Protocol</span>
    </div>

    <script>
        const CHANNELS = {
            majd: {
                title: "تلفزيون وإذاعة المجد للقرآن الكريم",
                sub: "تلاوات عطرة خاشعة متواصلة 24/7",
                url: "https://backup.qurango.net/radio/tarateel",
                emoji: "📺"
            },
            makkah: {
                title: "تلفزيون مكة المكرمة - الحرم المكي الشريف",
                sub: "بث حي ومباشر من بيت الله الحرام 24/7",
                url: "https://backup.qurango.net/radio/maher",
                emoji: "🕋"
            },
            madinah: {
                title: "تلفزيون المدينة المنورة - المسجد النبوي",
                sub: "بث حي ومباشر من الروضة الشريفة 24/7",
                url: "https://backup.qurango.net/radio/ali_alhuthaifi",
                emoji: "🕌"
            }
        };

        const initialKey = '<?= $channelKey ?>' || 'majd';
        const audio = document.getElementById('popout-audio');
        const titleEl = document.getElementById('ch-title');
        const subEl = document.getElementById('ch-sub');
        const iconEl = document.getElementById('ch-icon');

        let bc = null;
        try {
            bc = new BroadcastChannel('elsajana_media_broadcast');
            bc.onmessage = function(ev) {
                if (ev.data && ev.data.type === 'STATE_CHANGED' && ev.data.state) {
                    const st = ev.data.state;
                    if (st.extraMeta && st.extraMeta.channelKey && CHANNELS[st.extraMeta.channelKey]) {
                        switchChannel(st.extraMeta.channelKey, false);
                    } else if (st.sourceUrl) {
                        audio.src = st.sourceUrl;
                        titleEl.innerText = st.title || 'بث مباشر';
                        if (st.extraMeta && st.extraMeta.sub) subEl.innerText = st.extraMeta.sub;
                        audio.play().catch(e => {});
                    }
                } else if (ev.data && ev.data.type === 'STOP_ALL') {
                    audio.pause();
                }
            };
        } catch(e) {}

        function switchChannel(key, notify = true) {
            const ch = CHANNELS[key] || CHANNELS.majd;
            titleEl.innerText = ch.title;
            subEl.innerText = ch.sub;
            iconEl.innerText = ch.emoji;
            audio.src = ch.url;
            audio.play().catch(e => console.log('Autoplay error:', e));

            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            const activeBtn = document.getElementById('btn-' + key);
            if (activeBtn) activeBtn.classList.add('active');

            if (notify) {
                try {
                    localStorage.setItem('elsajana_global_media_state', JSON.stringify({
                        active: true,
                        type: 'tv',
                        sourceUrl: ch.url,
                        title: ch.title,
                        extraMeta: { channelKey: key, sub: ch.sub },
                        startedAt: Date.now(),
                        usePopout: true
                    }));
                    if (bc) bc.postMessage({ type: 'STATE_CHANGED', state: { active: true, sourceUrl: ch.url, title: ch.title, extraMeta: { channelKey: key, sub: ch.sub } } });
                } catch(e) {}
            }
        }

        // Initialize with requested channel
        switchChannel(initialKey, true);
    </script>
</body>
</html>
