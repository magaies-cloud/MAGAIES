$ErrorActionPreference = "Stop"

$source = "C:\xampp2\htdocs\Maga2app\build\web\*"
$dest = "C:\xampp\htdocs\magaiees_app\"
New-Item -ItemType Directory -Force -Path $dest | Out-Null

Write-Host "Copying files..."
Copy-Item -Force -Recurse $source $dest

# Ensure audio assets are accessible from all expected web URL paths
if (Test-Path "$dest\assets\assets\audio") {
    Copy-Item -Force -Recurse "$dest\assets\assets\audio" "$dest\assets\audio"
    Copy-Item -Force -Recurse "$dest\assets\assets\audio" "$dest\audio"
}

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

    $timestamp = (Get-Date).Ticks

    # Script to aggressively kill any old Service Workers and bust cached font/manifest assets
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
  var dartBuildStamp = '$timestamp';
  var dartVersion = '?v=' + dartBuildStamp;
  if (window.fetch) {
    var _origFetch = window.fetch;
    window.fetch = function(input, init) {
      if (typeof input === 'string' && (input.indexOf('MaterialIcons') !== -1 || input.indexOf('FontManifest') !== -1 || input.indexOf('AssetManifest') !== -1)) {
        var sep = input.indexOf('?') !== -1 ? '&' : '?';
        input = input + sep + 'v=' + dartBuildStamp;
      }
      return _origFetch.call(this, input, init);
    };
  }
</script>
"@

    # Inject the killer script right after <head>
    $htmlContent = $htmlContent -replace '(?i)<head>', "<head>`n$swKiller"
    # Ensure base href is dynamically set for both root domain (magaiees.elsajana.com) and subfolders
    $phpBaseHref = '<base href="<?php echo rtrim(str_replace(''\\'', ''/'', dirname($_SERVER[''SCRIPT_NAME''] ?? ''/index.php'')), ''/'') . ''/''; ?>">'
    $htmlContent = [regex]::Replace($htmlContent, '<base href=".*?">', { param($m) $phpBaseHref })
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

# Recompress main.dart.js into main.dart.js.gz for Apache gzip delivery
$jsPath = Join-Path $dest "main.dart.js"
$gzPath = Join-Path $dest "main.dart.js.gz"
if (Test-Path $jsPath) {
    Write-Host "Compressing main.dart.js to main.dart.js.gz..."
    $inputBytes = [System.IO.File]::ReadAllBytes($jsPath)
    $outputStream = [System.IO.File]::Create($gzPath)
    $gzipStream = New-Object System.IO.Compression.GZipStream($outputStream, [System.IO.Compression.CompressionLevel]::Optimal)
    $gzipStream.Write($inputBytes, 0, $inputBytes.Length)
    $gzipStream.Close()
    $outputStream.Close()
    Write-Host "main.dart.js.gz created successfully!"
}

Write-Host "Deployment to XAMPP successful! Cache aggressively busted."
$extraLocalTargets = @(
    "C:\xampp2\htdocs\magaiees_app",
    "C:\xampp2\htdocs\Maga2app\magaiees_app",
    "C:\xampp\htdocs\magaees\magaiees_app",
    "C:\xampp2\htdocs\magaees\magaiees_app"
)
foreach ($targetDir in $extraLocalTargets) {
    New-Item -ItemType Directory -Force -Path $targetDir | Out-Null
    Copy-Item -Force -Recurse "$dest\*" $targetDir
}
Write-Host "Synced to standalone C:\xampp\htdocs\magaiees_app and all local XAMPP paths."
