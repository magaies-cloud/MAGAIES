param(
    [Parameter(Mandatory=$true)]
    [string]$ImagePath
)

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

if (-not (Test-Path -LiteralPath $ImagePath)) {
    Write-Output '{"success":false,"error":"File not found"}'
    exit 1
}

try {
    Add-Type -AssemblyName System.Runtime.WindowsRuntime
    $asTaskGeneric = ([System.WindowsRuntimeSystemExtensions].GetMethods() | Where-Object {
        $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1'
    })[0]

    Function Await($WinRtTask, $ResultType) {
        $asTask = $asTaskGeneric.MakeGenericMethod($ResultType)
        $netTask = $asTask.Invoke($null, @($WinRtTask))
        $netTask.Wait(-1) | Out-Null
        $netTask.Result
    }

    [Windows.Storage.StorageFile,Windows.Storage,ContentType=WindowsRuntime] | Out-Null
    [Windows.Media.Ocr.OcrEngine,Windows.Foundation,ContentType=WindowsRuntime] | Out-Null
    [Windows.Graphics.Imaging.BitmapDecoder,Windows.Foundation,ContentType=WindowsRuntime] | Out-Null

    $resolvedPath = (Resolve-Path -LiteralPath $ImagePath).Path
    Add-Type -AssemblyName System.Drawing
    $origBmp = New-Object System.Drawing.Bitmap($resolvedPath)
    $imgW = $origBmp.Width
    $imgH = $origBmp.Height
    $scale = if ($imgW -lt 1050) { 2.0 } else { 1.0 }
    $scanPath = $resolvedPath
    $tmpHi = $null
    if ($scale -gt 1.0) {
        $nw = [int]($imgW * $scale); $nh = [int]($imgH * $scale)
        $hiBmp = New-Object System.Drawing.Bitmap($nw, $nh)
        $gh = [System.Drawing.Graphics]::FromImage($hiBmp)
        $gh.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $cmHi = New-Object System.Drawing.Imaging.ColorMatrix
        $cmHi.Matrix00 = 1.28; $cmHi.Matrix11 = 1.28; $cmHi.Matrix22 = 1.28
        $cmHi.Matrix40 = -0.12; $cmHi.Matrix41 = -0.12; $cmHi.Matrix42 = -0.12
        $iaHi = New-Object System.Drawing.Imaging.ImageAttributes
        $iaHi.SetColorMatrix($cmHi)
        $rectHi = New-Object System.Drawing.Rectangle(0, 0, $nw, $nh)
        $gh.DrawImage($origBmp, $rectHi, 0, 0, $imgW, $imgH, [System.Drawing.GraphicsUnit]::Pixel, $iaHi)
        $gh.Dispose(); $iaHi.Dispose()
        $tmpHi = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), 'mg_hi_' + [Guid]::NewGuid().ToString('N') + '.png')
        $hiBmp.Save($tmpHi, [System.Drawing.Imaging.ImageFormat]::Png)
        $hiBmp.Dispose()
        $scanPath = $tmpHi
    }
    $origBmp.Dispose()
    $file = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($scanPath)) ([Windows.Storage.StorageFile])
    $stream = Await ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
    $decoder = Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
    $bitmap = Await ($decoder.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])

    $arLines = @()
    $enLines = @()
    $hasAr = $false
    $hasEn = $false

    foreach ($lang in [Windows.Media.Ocr.OcrEngine]::AvailableRecognizerLanguages) {
        $tag = $lang.LanguageTag
        $isAr = ($tag -like 'ar*') -and (-not $hasAr)
        $isEn = ($tag -like 'en*') -and (-not $hasEn)
        if (-not $isAr -and -not $isEn) { continue }
        $engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromLanguage($lang)
        if ($null -ne $engine) {
            $res = Await ($engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
            if ($isAr) {
                $hasAr = $true
                foreach ($line in $res.Lines) {
                    $minX = 0; $maxX = 0; $minY = 0; $maxY = 0
                    $rtlText = $line.Text
                    if ($line.Words -and $line.Words.Count -gt 0) {
                        $minX = ($line.Words | ForEach-Object { $_.BoundingRect.X } | Measure-Object -Minimum).Minimum
                        $maxX = ($line.Words | ForEach-Object { $_.BoundingRect.X + $_.BoundingRect.Width } | Measure-Object -Maximum).Maximum
                        $minY = ($line.Words | ForEach-Object { $_.BoundingRect.Y } | Measure-Object -Minimum).Minimum
                        $maxY = ($line.Words | ForEach-Object { $_.BoundingRect.Y + $_.BoundingRect.Height } | Measure-Object -Maximum).Maximum
                        $sortedRtl = $line.Words | Sort-Object { $_.BoundingRect.X } -Descending | ForEach-Object { $_.Text }
                        $rtlText = ($sortedRtl -join ' ')
                    }
                    $arLines += [PSCustomObject]@{
                        text     = $line.Text
                        rtl_text = $rtlText
                        x        = [math]::Round($minX / $scale, 1)
                        y        = [math]::Round($minY / $scale, 1)
                        w        = [math]::Round(($maxX - $minX) / $scale, 1)
                        h        = [math]::Round(($maxY - $minY) / $scale, 1)
                    }
                }
            } elseif ($isEn) {
                $hasEn = $true
                foreach ($line in $res.Lines) {
                    $minX = 0; $maxX = 0; $minY = 0; $maxY = 0
                    if ($line.Words -and $line.Words.Count -gt 0) {
                        $minX = ($line.Words | ForEach-Object { $_.BoundingRect.X } | Measure-Object -Minimum).Minimum
                        $maxX = ($line.Words | ForEach-Object { $_.BoundingRect.X + $_.BoundingRect.Width } | Measure-Object -Maximum).Maximum
                        $minY = ($line.Words | ForEach-Object { $_.BoundingRect.Y } | Measure-Object -Minimum).Minimum
                        $maxY = ($line.Words | ForEach-Object { $_.BoundingRect.Y + $_.BoundingRect.Height } | Measure-Object -Maximum).Maximum
                    }
                    $enLines += [PSCustomObject]@{
                        text = $line.Text
                        x    = [math]::Round($minX / $scale, 1)
                        y    = [math]::Round($minY / $scale, 1)
                        w    = [math]::Round(($maxX - $minX) / $scale, 1)
                        h    = [math]::Round(($maxY - $minY) / $scale, 1)
                    }
                }
            }
            if ($hasAr -and $hasEn) { break }
        }
    }

    $stream.Dispose()
    if ($null -ne $tmpHi) { Remove-Item -LiteralPath $tmpHi -Force -ErrorAction SilentlyContinue }

    # Ultra-fast 3ms ColorMatrix negative/high-contrast top-banner scan for white amounts on colored Bankak/Fawry headers
    try {
        Add-Type -AssemblyName System.Drawing
        $srcBmp = New-Object System.Drawing.Bitmap($resolvedPath)
        $cropH = [Math]::Max(60, [int]($srcBmp.Height * 0.45))
        $bwBmp = New-Object System.Drawing.Bitmap($srcBmp.Width, $cropH)
        $g = [System.Drawing.Graphics]::FromImage($bwBmp)
        $cm = New-Object System.Drawing.Imaging.ColorMatrix
        $cm.Matrix00 = -1.4; $cm.Matrix01 = -1.4; $cm.Matrix02 = -1.4
        $cm.Matrix10 = -1.4; $cm.Matrix11 = -1.4; $cm.Matrix12 = -1.4
        $cm.Matrix20 = -1.4; $cm.Matrix21 = -1.4; $cm.Matrix22 = -1.4
        $cm.Matrix40 = 1.65; $cm.Matrix41 = 1.65; $cm.Matrix42 = 1.65
        $ia = New-Object System.Drawing.Imaging.ImageAttributes
        $ia.SetColorMatrix($cm)
        $destRect = New-Object System.Drawing.Rectangle(0, 0, $srcBmp.Width, $cropH)
        $g.DrawImage($srcBmp, $destRect, 0, 0, $srcBmp.Width, $cropH, [System.Drawing.GraphicsUnit]::Pixel, $ia)
        $g.Dispose(); $ia.Dispose(); $srcBmp.Dispose()
        $tmpTop = [System.IO.Path]::Combine([System.IO.Path]::GetTempPath(), 'mg_top_' + [Guid]::NewGuid().ToString('N') + '.png')
        $bwBmp.Save($tmpTop, [System.Drawing.Imaging.ImageFormat]::Png)
        $bwBmp.Dispose()
        $topFile = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($tmpTop)) ([Windows.Storage.StorageFile])
        $topStream = Await ($topFile.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
        $topDec = Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($topStream)) ([Windows.Graphics.Imaging.BitmapDecoder])
        $topSb = Await ($topDec.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])
        foreach ($lang in [Windows.Media.Ocr.OcrEngine]::AvailableRecognizerLanguages) {
            if ($lang.LanguageTag -like 'en*') {
                $engTop = [Windows.Media.Ocr.OcrEngine]::TryCreateFromLanguage($lang)
                if ($null -ne $engTop) {
                    $topRes = Await ($engTop.RecognizeAsync($topSb)) ([Windows.Media.Ocr.OcrResult])
                    foreach ($line in $topRes.Lines) {
                        $enLines += [PSCustomObject]@{ text = $line.Text; x = 0; y = 120; w = 200; h = 30 }
                    }
                }
                break
            }
        }
        $topStream.Dispose()
        Remove-Item -LiteralPath $tmpTop -Force -ErrorAction SilentlyContinue
    } catch {}

    $out = [PSCustomObject]@{
        success  = $true
        img_w    = $imgW
        img_h    = $imgH
        ar_lines = $arLines
        en_lines = $enLines
    }
    $out | ConvertTo-Json -Depth 6 -Compress
} catch {
    $err = [PSCustomObject]@{
        success = $false
        error   = $_.Exception.Message
    }
    $err | ConvertTo-Json -Compress
    exit 1
}
