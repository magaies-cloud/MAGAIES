<?php
if (!headers_sent() && extension_loaded('zlib') && !ini_get('zlib.output_compression') && ob_get_level() <= 1 && (int)ob_get_length() === 0 && stripos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false && !in_array('ob_gzhandler', ob_list_handlers(), true)) { @ob_start('ob_gzhandler'); }

$channel = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['channel'] ?? 'fin_qr_default');
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>رفع الإشعارات الفوري للكمبيوتر</title>
<link rel="stylesheet" href="css/local-fonts.css?v=2027">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Tajawal',sans-serif}
body{background:#020617;color:#f8fafc;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:18px}
.card{width:100%;max-width:430px;background:linear-gradient(155deg,#0f172a,#1e293b);border:2px solid rgba(34,211,238,0.45);border-radius:24px;padding:24px 20px;box-shadow:0 20px 50px rgba(0,0,0,0.75);text-align:center}
.badge{display:inline-flex;align-items:center;gap:8px;background:rgba(16,185,129,0.16);border:1.5px solid #10b981;color:#34d399;padding:6px 14px;border-radius:99px;font-weight:900;font-size:0.92rem;margin-bottom:14px}
.dot{width:10px;height:10px;border-radius:50%;background:#10b981;box-shadow:0 0 10px #10b981}
h1{font-size:1.45rem;font-weight:900;margin-bottom:6px;color:#fff}
p.sub{color:#cbd5e1;font-size:0.95rem;font-weight:700;margin-bottom:22px;line-height:1.5}
.btn{width:100%;border:none;border-radius:18px;padding:18px 16px;font-size:1.22rem;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;transition:transform 0.15s}
.btn:active{transform:scale(0.97)}
.btn-gallery{background:linear-gradient(135deg,#06b6d4,#0284c7);color:#fff;box-shadow:0 10px 25px rgba(6,182,212,0.35)}
.btn-cam{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 10px 25px rgba(16,185,129,0.3)}
#status{margin-top:14px;padding:12px;border-radius:14px;font-weight:800;font-size:1rem;display:none}
.ok{display:block!important;background:rgba(16,185,129,0.2);border:1.5px solid #34d399;color:#6ee7b7}
.wait{display:block!important;background:rgba(56,189,248,0.18);border:1.5px solid #38bdf8;color:#bae6fd}
.err{display:block!important;background:rgba(239,68,68,0.2);border:1.5px solid #f87171;color:#fecaca}
.preview-row{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:12px}
.preview-row img{width:68px;height:68px;object-fit:cover;border-radius:10px;border:2px solid #22d3ee}
</style>
    <link id="magaies-ophthalmic-ergo-css" rel="stylesheet" href="css/ophthalmic-ergonomics-2027.css?v=2027_gold_v8">
</head>
<body>
<div class="card">
    <div class="badge"><span class="dot"></span> <span>متصل بشاشة الحركة المالية</span></div>
    <h1>📱 إرسال الإشعار للكمبيوتر</h1>
    <p class="sub">اختر صورة التحويل (بنكك / فوري / أوكاش) من الاستوديو أو صوّرها بالكاميرا ليتم فحصها وإدراجها فوراً في الكمبيوتر.</p>

    <input type="file" id="inp-gallery" accept="image/*" multiple style="display:none">
    <input type="file" id="inp-cam" accept="image/*" capture="environment" style="display:none">

    <button type="button" class="btn btn-gallery" id="btn-gal" onclick="document.getElementById('inp-gallery').click()">🖼️ اختيار من الاستوديو</button>
    <button type="button" class="btn btn-cam" id="btn-cm" onclick="document.getElementById('inp-cam').click()">📷 تصوير الإشعار بالكاميرا</button>
    <button type="button" class="btn" id="btn-disc" onclick="disconnectPhone()" style="background:rgba(239,68,68,0.18);border:1.8px solid #ef4444;color:#fca5a5;padding:12px;font-size:1.02rem;margin-bottom:4px;">🔌 إلغاء ربط الجوال</button>

    <div id="status"></div>
    <div class="preview-row" id="previews"></div>
</div>
<script>
const CHANNEL = <?php echo json_encode($channel); ?>;
let pingTimer = null;
function markDisconnectedUI(){
    if (pingTimer) clearInterval(pingTimer);
    document.querySelector('.badge').innerHTML = '<span>🔌 تم إلغاء ربط الجوال</span>';
    document.querySelector('.badge').style.cssText = 'display:inline-flex;align-items:center;gap:8px;background:rgba(239,68,68,0.2);border:1.5px solid #ef4444;color:#fca5a5;padding:6px 14px;border-radius:99px;font-weight:900;font-size:0.92rem;margin-bottom:14px';
    document.getElementById('btn-gal').style.display = 'none';
    document.getElementById('btn-cm').style.display = 'none';
    document.getElementById('btn-disc').style.display = 'none';
    const st = document.getElementById('status');
    st.className = 'err';
    st.textContent = '🔌 تم فصل ارتباط الجوال بنجاح. للربط مجدداً امسح الباركود الجديد من شاشة الكمبيوتر.';
}
async function disconnectPhone(){
    try { await fetch('api/qr_live_bridge.php?action=disconnect&channel='+encodeURIComponent(CHANNEL),{method:'POST'}); } catch(e){}
    markDisconnectedUI();
}
function pingConnect(){
    fetch('api/qr_live_bridge.php?action=connect&channel='+encodeURIComponent(CHANNEL),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({device:navigator.userAgent})}).then(r=>r.json()).then(d=>{ if(d && d.disconnected) markDisconnectedUI(); }).catch(()=>{});
}
pingConnect();
pingTimer = setInterval(pingConnect, 6000);

function compressImg(file){
    return new Promise((resolve,reject)=>{
        const reader = new FileReader();
        reader.onload = (e)=>{
            const img = new Image();
            img.onload = ()=>{
                const maxSide = 1600;
                let w = img.width, h = img.height;
                if (w > maxSide || h > maxSide) {
                    if (w > h) { h = Math.round(h * maxSide / w); w = maxSide; }
                    else { w = Math.round(w * maxSide / h); h = maxSide; }
                }
                const c = document.createElement('canvas');
                c.width = w; c.height = h;
                c.getContext('2d').drawImage(img, 0, 0, w, h);
                resolve(c.toDataURL('image/jpeg', 0.88));
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function sendFiles(fileList){
    if(!fileList || !fileList.length) return;
    const st = document.getElementById('status');
    const pv = document.getElementById('previews');
    st.className = 'wait';
    st.textContent = '⏳ جاري إرسال ' + fileList.length + ' إشعار إلى شاشة الكمبيوتر...';
    pv.innerHTML = '';
    try {
        const images = [];
        for (const f of Array.from(fileList)) {
            const b64 = await compressImg(f);
            images.push(b64);
            const im = document.createElement('img');
            im.src = b64;
            pv.appendChild(im);
        }
        const res = await fetch('api/qr_live_bridge.php?action=push_receipt&channel='+encodeURIComponent(CHANNEL), {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({images})
        });
        const data = await res.json();
        if (data && data.ok) {
            st.className = 'ok';
            st.textContent = '✅ تم إرسال ' + data.count + ' إشعار بنجاح! انظر الآن لشاشة الكمبيوتر.';
        } else {
            throw new Error('فشل الإرسال');
        }
    } catch (err) {
        st.className = 'err';
        st.textContent = '❌ تعذر الإرسال، تأكد من اتصال الجوال بنفس الشبكة أو الإنترنت وحاول مجدداً.';
    }
}
document.getElementById('inp-gallery').addEventListener('change', e=>{ sendFiles(e.target.files); e.target.value=''; });
document.getElementById('inp-cam').addEventListener('change', e=>{ sendFiles(e.target.files); e.target.value=''; });
</script>
</body>
</html>
