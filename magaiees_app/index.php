<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
      for(let registration of registrations) {
        registration.unregister();
        console.log('ServiceWorker unregistered to bypass cache');
      }
    });
  }
  // Optional: add a version parameter to main.dart.js to force redownload
  var dartVersion = '?v=' + new Date().getTime();
</script>
  <script>
    var path = window.location.pathname;
    var base = path.substring(0, path.lastIndexOf('/') + 1);
    document.write('<base href="' + base + '">');
  </script>
  <meta charset="UTF-8">
  <meta content="IE=Edge" http-equiv="X-UA-Compatible">
  <meta name="description" content="منصة مقاييس - سوق مواد البناء والمعدات">
  <!-- Flutter Web handles the viewport tag automatically -->

  <!-- Preconnect to Font & CDN Servers for Instant Loading -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- iOS meta tags & icons -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="منصة مقاييس">
  <link rel="apple-touch-icon" href="icons/Icon-192.png">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="logo.png"/>

  <title>منصة مقاييس | Magaiees Marketplace</title>
  <link rel="manifest" href="manifest.json">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      background-color: #F8FAFC;
      font-family: 'Cairo', system-ui, -apple-system, sans-serif;
      overflow: hidden;
    }
    #loading-container {
      position: fixed;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
      z-index: 999999;
      transition: opacity 0.4s ease-out;
    }
    .splash-logo {
      width: 120px;
      height: 120px;
      object-fit: contain;
      animation: pulse 1.8s infinite ease-in-out;
      margin-bottom: 24px;
    }
    .app-title {
      font-size: 22px;
      font-weight: 800;
      color: #1E40AF;
      margin-bottom: 6px;
    }
    .app-subtitle {
      font-size: 13px;
      color: #64748B;
      margin-bottom: 30px;
    }
    /* From Uiverse.io by Fadhilmagass */ 
    .loader {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-pack: center;
      -ms-flex-pack: center;
      justify-content: center;
      -webkit-box-align: center;
      -ms-flex-align: center;
      align-items: center;
      margin-top: 8px;
    }

    .dot {
      display: inline-block;
      width: 12px;
      height: 12px;
      margin: 0 4px;
      border-radius: 50%;
      -webkit-animation: dot-pulse2 1.5s ease-in-out infinite;
      animation: dot-pulse2 1.5s ease-in-out infinite;
    }

    .dot-1 {
      background-color: #4285f4;
      -webkit-animation-delay: 0s;
      animation-delay: 0s;
    }

    .dot-2 {
      background-color: #34a853;
      -webkit-animation-delay: 0.3s;
      animation-delay: 0.3s;
    }

    .dot-3 {
      background-color: #fbbc05;
      -webkit-animation-delay: 0.6s;
      animation-delay: 0.6s;
    }

    .dot-4 {
      background-color: #ea4335;
      -webkit-animation-delay: 0.9s;
      animation-delay: 0.9s;
    }

    .dot-5 {
      background-color: #4285f4;
      -webkit-animation-delay: 1.2s;
      animation-delay: 1.2s;
    }

    @keyframes dot-pulse2 {
      0% {
        -webkit-transform: scale(0.5);
        transform: scale(0.5);
        opacity: 0.5;
      }

      50% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 1;
      }

      100% {
        -webkit-transform: scale(0.5);
        transform: scale(0.5);
        opacity: 0.5;
      }
    }
    @-webkit-keyframes dot-pulse2 {
      0% {
        -webkit-transform: scale(0.5);
        transform: scale(0.5);
        opacity: 0.5;
      }

      50% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 1;
      }

      100% {
        -webkit-transform: scale(0.5);
        transform: scale(0.5);
        opacity: 0.5;
      }
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.06); opacity: 0.92; }
    }
  </style>
</head>
<body>
  <div id="loading-container">
    <img src="logo.png" alt="Magaiees Logo" class="splash-logo">
    <div class="app-title">منصة مقاييس</div>
    <div class="app-subtitle">جاري تحميل المنصة...</div>
    <div class="loader">
      <div class="dot dot-1"></div>
      <div class="dot dot-2"></div>
      <div class="dot dot-3"></div>
      <div class="dot dot-4"></div>
      <div class="dot dot-5"></div>
    </div>
  </div>

  <script>
    window.addEventListener('flutter-first-frame', function() {
      // Small delay to let Flutter precache the logo image in memory
      // before we remove the native splash screen, preventing X-box flash.
      setTimeout(function() {
        var loader = document.getElementById('loading-container');
        if (loader) {
          loader.style.opacity = '0';
          setTimeout(function() {
            loader.remove();
          }, 400);
        }
      }, 300);
    });
  </script>
  <script src="flutter_bootstrap.js?v=639233186873149680" async></script>
</body>
</html>
