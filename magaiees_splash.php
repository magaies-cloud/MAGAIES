<?php
// =============================================================
// نظام MAGAIEES ERP 2026 - المحرك السينمائي لشاشة الترحيب المشتركة
// =============================================================
?>
<style>
    /* =============================================================
       🎬 تجميعة الحركات السينمائية (الشاشة الترحيبية)
       ============================================================= */
    #splash-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; z-index: 99999; background-color: #000; font-family: 'Cairo', sans-serif; }
    .bg-video-stream { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none; filter: brightness(1.1) contrast(1.2); z-index: 1; }
    .overlay-dim { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(3,5,8,0.2) 0%, rgba(2,4,6,0.8) 100%); z-index: 2; pointer-events: none; }
    .neon-luminous-ring { position: absolute; width: 380px; height: 380px; border-radius: 50%; border: 4px solid transparent; border-top: 4px solid #00ffcc; border-bottom: 4px solid #ff00ff; border-left: 4px solid #fbbf24; z-index: 3; animation: spinRing 2s linear infinite; filter: drop-shadow(0 0 25px rgba(0,255,204,0.7)); }
    .logo-3d-center { position: relative; z-index: 4; text-align: center; animation: emergeFromDepth 4s forwards cubic-bezier(0.1, 1, 0.1, 1); transform-style: preserve-3d; perspective: 1000px; }
    .logo-text-epic { font-size: 70px; font-weight: 900; font-family: 'Inter', 'Cairo', sans-serif; margin: 0; background: linear-gradient(135deg, #ffffff 0%, #00ffcc 50%, #ff00ff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.8)) drop-shadow(0 0 40px rgba(255, 0, 255, 0.5)); letter-spacing: 3px; transform: translateZ(50px); text-shadow: 0 0 50px rgba(0,255,255,0.4); }
    .logo-sub-epic { font-size: 14px; font-weight: bold; color: #38bdf8; letter-spacing: 8px; margin-top: 15px; text-transform: uppercase; opacity: 0.9; text-shadow: 0 0 10px rgba(56,189,248,0.5); }
    
    @keyframes spinRing { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes emergeFromDepth {
        0% { transform: scale(0.3) translateZ(-500px); filter: blur(10px) brightness(0); opacity: 0; }
        30% { transform: scale(1.1) translateZ(100px); filter: blur(0px) brightness(1.5); opacity: 1; }
        45% { transform: scale(1) translateZ(0); filter: brightness(1.2); }
        100% { transform: scale(1.03) translateZ(20px); filter: brightness(1.2); }
    }
    .splash-fade-out { opacity: 0; visibility: hidden; transition: opacity 1s ease, visibility 1s ease; }
</style>

<!-- 🎥 محرك الشاشة الترحيبية السينمائية الخلفية الحية للشاشات الفائقة -->
<div id="splash-container">
    <video autoplay loop muted playsinline class="bg-video-stream">
        <source src="271262.mp4" type="video/mp4">
    </video>
    
    <div class="overlay-dim"></div>
    <div class="neon-luminous-ring"></div>
    
    <div class="logo-3d-center">
        <h1 class="logo-text-epic">مقاييس ERP</h1>
        <div class="logo-sub-epic">MAGAIEES GLOBAL SYSTEM</div>
    </div>
</div>

<script>
    // دقة المؤقت: 4 ثوانٍ (4000 مللي ثانية) لعرض الدعاية والجمالية بالكامل
    setTimeout(() => {
        const splashContainer = document.getElementById('splash-container');
        if (splashContainer) {
            splashContainer.classList.add('splash-fade-out');
            // إزالة كاملة للشاشة من الذاكرة العشوائية بعد الاختفاء لتخفيف الجهاز
            setTimeout(() => {
                splashContainer.remove();
            }, 1000);
        }
    }, 4000);
</script>
