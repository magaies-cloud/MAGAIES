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
    $file = Await ([Windows.Storage.StorageFile]::GetFileFromPathAsync($resolvedPath)) ([Windows.Storage.StorageFile])
    $stream = Await ($file.OpenAsync([Windows.Storage.FileAccessMode]::Read)) ([Windows.Storage.Streams.IRandomAccessStream])
    $decoder = Await ([Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)) ([Windows.Graphics.Imaging.BitmapDecoder])
    $bitmap = Await ($decoder.GetSoftwareBitmapAsync()) ([Windows.Graphics.Imaging.SoftwareBitmap])

    $arLines = @()
    $enLines = @()

    foreach ($lang in [Windows.Media.Ocr.OcrEngine]::AvailableRecognizerLanguages) {
        $tag = $lang.LanguageTag
        $engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromLanguage($lang)
        if ($null -ne $engine) {
            $res = Await ($engine.RecognizeAsync($bitmap)) ([Windows.Media.Ocr.OcrResult])
            foreach ($line in $res.Lines) {
                $wordsLtr = ($line.Words | Sort-Object { $_.BoundingRect.X } | ForEach-Object { $_.Text }) -join " "
                $wordsRtl = ($line.Words | Sort-Object { $_.BoundingRect.X } -Descending | ForEach-Object { $_.Text }) -join " "
                if ($tag -like 'ar*') {
                    $arLines += [PSCustomObject]@{
                        text = $line.Text
                        ltr  = $wordsLtr
                        rtl  = $wordsRtl
                    }
                } elseif ($tag -like 'en*') {
                    $enLines += [PSCustomObject]@{
                        text = $line.Text
                        ltr  = $wordsLtr
                    }
                }
            }
        }
    }

    $stream.Dispose()

    $out = [PSCustomObject]@{
        success  = $true
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
