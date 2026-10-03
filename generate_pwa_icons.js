const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const assetsDir = path.resolve(__dirname, 'assets');
const inputLogo = path.join(assetsDir, 'logo.png');

async function generateIcons() {
    console.log('Generating official Magaiees primary icons from:', inputLogo);

    // Verify input logo exists
    if (!fs.existsSync(inputLogo)) {
        throw new Error('Input logo does not exist: ' + inputLogo);
    }

    const metadata = await sharp(inputLogo).metadata();
    console.log(`Source Logo dimensions: ${metadata.width}x${metadata.height}, format: ${metadata.format}`);

    // Helper: create icon with optional background & padding
    async function makeIcon(size, paddingRatio = 0.12, bgColor = null) {
        const innerSize = Math.round(size * (1 - paddingRatio * 2));
        const resizedLogo = await sharp(inputLogo)
            .resize(innerSize, innerSize, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
            .toBuffer();

        if (bgColor) {
            return await sharp({
                create: {
                    width: size,
                    height: size,
                    channels: 4,
                    background: bgColor
                }
            })
            .composite([{ input: resizedLogo, gravity: 'center' }])
            .png({ quality: 95 })
            .toBuffer();
        } else {
            return await sharp({
                create: {
                    width: size,
                    height: size,
                    channels: 4,
                    background: { r: 0, g: 0, b: 0, alpha: 0 }
                }
            })
            .composite([{ input: resizedLogo, gravity: 'center' }])
            .png({ quality: 95 })
            .toBuffer();
        }
    }

    // 1. icon-192.png (192x192, dark slate rounded background for high contrast & elegance)
    const icon192 = await makeIcon(192, 0.10, { r: 15, g: 23, b: 42, alpha: 1 });
    fs.writeFileSync(path.join(assetsDir, 'icon-192.png'), icon192);
    console.log('Generated: icon-192.png (192x192)');

    // 2. icon-512.png (512x512, high res for splash screen & app install)
    const icon512 = await makeIcon(512, 0.10, { r: 15, g: 23, b: 42, alpha: 1 });
    fs.writeFileSync(path.join(assetsDir, 'icon-512.png'), icon512);
    console.log('Generated: icon-512.png (512x512)');

    // 3. icon-maskable-512.png (512x512 with safe padding for Android adaptive icons)
    const iconMaskable = await makeIcon(512, 0.20, { r: 15, g: 23, b: 42, alpha: 1 });
    fs.writeFileSync(path.join(assetsDir, 'icon-maskable-512.png'), iconMaskable);
    console.log('Generated: icon-maskable-512.png (512x512 with 20% safe zone)');

    // 4. apple-touch-icon.png (180x180 for iOS home screen)
    const appleIcon = await makeIcon(180, 0.10, { r: 15, g: 23, b: 42, alpha: 1 });
    fs.writeFileSync(path.join(assetsDir, 'apple-touch-icon.png'), appleIcon);
    console.log('Generated: apple-touch-icon.png (180x180)');

    // 5. favicon-32x32.png, favicon.png, favicon.ico (for laptop/desktop browser tabs)
    const favicon32 = await makeIcon(32, 0.05, { r: 15, g: 23, b: 42, alpha: 1 });
    fs.writeFileSync(path.join(assetsDir, 'favicon-32x32.png'), favicon32);
    fs.writeFileSync(path.join(assetsDir, 'favicon.png'), favicon32);
    fs.writeFileSync(path.join(assetsDir, 'favicon.ico'), favicon32);
    console.log('Generated: favicon-32x32.png, favicon.png, favicon.ico');

    // 6. icon.svg with high-res base64
    const b64 = icon512.toString('base64');
    const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <defs>
    <clipPath id="circleClip">
      <circle cx="256" cy="256" r="248"/>
    </clipPath>
  </defs>
  <rect width="512" height="512" rx="128" fill="#0f172a"/>
  <image href="data:image/png;base64,${b64}" width="512" height="512" preserveAspectRatio="xMidYMid meet"/>
</svg>`;
    fs.writeFileSync(path.join(assetsDir, 'icon.svg'), svgContent, 'utf8');
    console.log('Generated: icon.svg');

    console.log('🎉 All Magaiees primary brand logo PWA icons successfully generated!');
}

generateIcons().catch(err => {
    console.error('Error generating icons:', err);
    process.exit(1);
});
