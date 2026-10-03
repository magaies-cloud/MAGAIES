const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const assetsDir = path.resolve(__dirname, 'assets');
const inputLogo = path.join(assetsDir, 'logo_maqayis.png');

async function generateIcons() {
    console.log('Generating official Magaiees icons from:', inputLogo);

    // 1. icon-192.png (192x192)
    await sharp(inputLogo)
        .resize(192, 192, { fit: 'cover' })
        .png({ quality: 95 })
        .toFile(path.join(assetsDir, 'icon-192.png'));
    console.log('Generated: icon-192.png');

    // 2. icon-512.png (512x512)
    await sharp(inputLogo)
        .resize(512, 512, { fit: 'cover' })
        .png({ quality: 95 })
        .toFile(path.join(assetsDir, 'icon-512.png'));
    console.log('Generated: icon-512.png');

    // 3. icon-maskable-512.png (512x512 with safe padding)
    const innerIcon = await sharp(inputLogo)
        .resize(410, 410, { fit: 'contain' })
        .toBuffer();
    
    await sharp({
        create: {
            width: 512,
            height: 512,
            channels: 4,
            background: { r: 15, g: 23, b: 42, alpha: 1 }
        }
    })
    .composite([{ input: innerIcon, gravity: 'center' }])
    .png({ quality: 95 })
    .toFile(path.join(assetsDir, 'icon-maskable-512.png'));
    console.log('Generated: icon-maskable-512.png');

    // 4. apple-touch-icon.png (180x180)
    await sharp(inputLogo)
        .resize(180, 180, { fit: 'cover' })
        .png({ quality: 95 })
        .toFile(path.join(assetsDir, 'apple-touch-icon.png'));
    console.log('Generated: apple-touch-icon.png');

    // 5. favicon-32x32.png and favicon.ico
    await sharp(inputLogo)
        .resize(32, 32, { fit: 'cover' })
        .png()
        .toFile(path.join(assetsDir, 'favicon-32x32.png'));
    fs.copyFileSync(path.join(assetsDir, 'favicon-32x32.png'), path.join(assetsDir, 'favicon.png'));
    fs.copyFileSync(path.join(assetsDir, 'favicon-32x32.png'), path.join(assetsDir, 'favicon.ico'));
    console.log('Generated: favicon.ico, favicon.png, favicon-32x32.png');

    // 6. SVG with embedded base64 of official logo
    const buf512 = await sharp(inputLogo).resize(512, 512).png().toBuffer();
    const b64 = buf512.toString('base64');
    const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <defs>
    <clipPath id="circleClip">
      <circle cx="256" cy="256" r="240"/>
    </clipPath>
  </defs>
  <rect width="512" height="512" rx="128" fill="#0f172a"/>
  <image href="data:image/png;base64,${b64}" width="512" height="512" preserveAspectRatio="xMidYMid slice" clip-path="url(#circleClip)"/>
</svg>`;
    fs.writeFileSync(path.join(assetsDir, 'icon.svg'), svgContent, 'utf8');
    console.log('Generated: icon.svg');
    console.log('🎉 All Magaiees official PWA assets generated successfully!');
}

generateIcons().catch(err => {
    console.error(err);
    process.exit(1);
});
