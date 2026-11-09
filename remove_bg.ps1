Add-Type -AssemblyName System.Drawing
$bmp = New-Object System.Drawing.Bitmap('C:\Users\amar0\.gemini\antigravity\brain\bec0fc4d-77f2-47f9-a1d1-edd88daf423b\.user_uploaded\media_1787345710797.png')
for ($y = 0; $y -lt $bmp.Height; $y++) {
    for ($x = 0; $x -lt $bmp.Width; $x++) {
        $p = $bmp.GetPixel($x, $y)
        # Check if it's gray-ish
        if ([Math]::Abs($p.R - $p.G) -lt 30 -and [Math]::Abs($p.R - $p.B) -lt 30 -and [Math]::Abs($p.G - $p.B) -lt 30 -and $p.R -lt 180 -and $p.R -gt 50) {
            $bmp.SetPixel($x, $y, [System.Drawing.Color]::Transparent)
        }
    }
}
$bmp.Save('c:\xampp2\htdocs\Maga2app\logo_transparent.png', [System.Drawing.Imaging.ImageFormat]::Png)
$bmp.Dispose()
