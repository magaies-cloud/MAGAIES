<?php
$src = "assets/logo.png";
if (!file_exists($src)) die("File not found");

$img = imagecreatefrompng($src);
if (!$img) die("Could not load image");

// Enable alpha blending and save alpha
imagealphablending($img, false);
imagesavealpha($img, true);

$width = imagesx($img);
$height = imagesy($img);

// Define the tolerance for "white"
$tolerance = 30; // Colors with RGB all > 255-tolerance will be transparent

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($img, $x, $y);
        $colors = imagecolorsforindex($img, $rgb);
        
        if ($colors["red"] > 255 - $tolerance && $colors["green"] > 255 - $tolerance && $colors["blue"] > 255 - $tolerance) {
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagesetpixel($img, $x, $y, $transparent);
        }
    }
}

imagepng($img, "assets/logo_transparent.png");
echo "Done";
?>
