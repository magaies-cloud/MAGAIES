$ErrorActionPreference = "Stop"

$source = "C:\xampp2\htdocs\Maga2app\build\web\*"
$dest = "C:\xampp\htdocs\magaees\magaiees_app\"

Write-Host "Copying files..."
Copy-Item -Force -Recurse $source $dest

Write-Host "Setting up index.php with cache busting headers..."
$indexHtmlPath = Join-Path $dest "index.html"
$indexPhpPath = Join-Path $dest "index.php"

if (Test-Path $indexHtmlPath) {
    $htmlContent = Get-Content -Path $indexHtmlPath -Encoding UTF8 -Raw
    
    $phpHeaders = @"
<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
?>
"@

    # Script to aggressively kill any old Service Workers in the browser
    $swKiller = @"
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
"@

    # Inject the killer script right after <head>
    $htmlContent = $htmlContent -replace '(?i)<head>', "<head>`n$swKiller"
    
    $timestamp = (Get-Date).Ticks
    # Replace flutter_bootstrap.js reference with a versioned one
    $htmlContent = $htmlContent -replace 'flutter_bootstrap\.js', "flutter_bootstrap.js?v=$timestamp"
    
    $newContent = $phpHeaders + "`n" + $htmlContent
    
    # Write as UTF-8 without BOM
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($indexPhpPath, $newContent, $utf8NoBom)
    
    Remove-Item -Path $indexHtmlPath -Force
}

$swPath = Join-Path $dest "flutter_service_worker.js"
$dummySw = "self.addEventListener('install', (e) => { self.skipWaiting(); }); self.addEventListener('activate', (e) => { self.registration.unregister(); });"
[System.IO.File]::WriteAllText($swPath, $dummySw)

# Update flutter_bootstrap.js to append version to main.dart.js
$bootstrapPath = Join-Path $dest "flutter_bootstrap.js"
if (Test-Path $bootstrapPath) {
    $bootstrapContent = Get-Content -Path $bootstrapPath -Encoding UTF8 -Raw
    # Stop it from loading service worker by removing the configuration block
    $bootstrapContent = $bootstrapContent -replace 'serviceWorkerVersion:\s*".*?",?', ''
    # Force version on main.dart.js
    $bootstrapContent = $bootstrapContent -replace 'main\.dart\.js', "main.dart.js?v=$timestamp"
    [System.IO.File]::WriteAllText($bootstrapPath, $bootstrapContent, $utf8NoBom)
}

Write-Host "Deployment to XAMPP successful! Cache aggressively busted."
