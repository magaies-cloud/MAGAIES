const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const uploadedSource = 'C:\\Users\\amar0\\.gemini\\antigravity\\brain\\54390b25-9264-4098-b278-a9c35db60b8b\\.user_uploaded\\media_1794484714237_21d2d829.png';
const assetsDir = path.resolve(__dirname, 'assets');

async function extractCleanTransparentLogo() {
    const { data, info } = await sharp(uploadedSource)
        .ensureAlpha()
        .raw()
        .toBuffer({ resolveWithObject: true });

    const w = info.width;
    const h = info.height;
    const visited = new Uint8Array(w * h);
    const isOuterBg = new Uint8Array(w * h);

    // Sample corner color (top-left)
    const bgR = data[0], bgG = data[1], bgB = data[2];

    function colorDist(idx) {
        const dr = data[idx] - bgR;
        const dg = data[idx + 1] - bgG;
        const db = data[idx + 2] - bgB;
        return Math.sqrt(dr * dr + dg * dg + db * db);
    }

    // BFS flood-fill from all 4 border edges to only remove exterior background
    // while keeping interior white strokes inside the shield 100% opaque white!
    const queue = new Int32Array(w * h);
    let head = 0, tail = 0;

    function pushIfBg(x, y) {
        if (x < 0 || x >= w || y < 0 || y >= h) return;
        const p = y * w + x;
        if (visited[p]) return;
        visited[p] = 1;
        const idx = p * 4;
        const dist = colorDist(idx);
        const lum = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
        if (dist < 42 || lum > 222) {
            isOuterBg[p] = 1;
            queue[tail++] = p;
        }
    }

    for (let x = 0; x < w; x++) {
        pushIfBg(x, 0);
        pushIfBg(x, h - 1);
    }
    for (let y = 0; y < h; y++) {
        pushIfBg(0, y);
        pushIfBg(w - 1, y);
    }

    while (head < tail) {
        const p = queue[head++];
        const x = p % w;
        const y = (p - x) / w;
        pushIfBg(x + 1, y);
        pushIfBg(x - 1, y);
        pushIfBg(x, y + 1);
        pushIfBg(x, y - 1);
    }

    // Apply transparency to outer background + smooth 1px edge anti-aliasing
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            const p = y * w + x;
            const idx = p * 4;
            if (isOuterBg[p]) {
                data[idx + 3] = 0;
            } else {
                // Check if adjacent to outer background for soft anti-aliased edge
                let bgNeighbors = 0;
                if (x > 0 && isOuterBg[p - 1]) bgNeighbors++;
                if (x < w - 1 && isOuterBg[p + 1]) bgNeighbors++;
                if (y > 0 && isOuterBg[p - w]) bgNeighbors++;
                if (y < h - 1 && isOuterBg[p + w]) bgNeighbors++;
                if (bgNeighbors > 0) {
                    const lum = 0.299 * data[idx] + 0.587 * data[idx + 1] + 0.114 * data[idx + 2];
                    if (lum > 180) {
                        data[idx + 3] = Math.round(255 * Math.max(0.15, (235 - lum) / 75));
                    }
                }
            }
        }
    }

    // Trim transparent margins and resize cleanly into 512x512 with a subtle 4% margin
    const rawPng = await sharp(data, { raw: { width: w, height: h, channels: 4 } })
        .png()
        .toBuffer();

    const trimmedPng = await sharp(rawPng)
        .trim()
        .png()
        .toBuffer();

    const resizedInner = await sharp(trimmedPng)
        .resize(476, 476, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
        .toBuffer();

    const final512 = await sharp({
        create: {
            width: 512,
            height: 512,
            channels: 4,
            background: { r: 0, g: 0, b: 0, alpha: 0 }
        }
    })
    .composite([{ input: resizedInner, gravity: 'center' }])
    .png({ quality: 100 })
    .toBuffer();

    return final512;
}

async function generateIcons() {
    console.log('Extracting and applying user-uploaded Al-Saytara logo across the entire system...');
    const logo512 = await extractCleanTransparentLogo();

    // 1. Overwrite all primary transparent logo files across the workspace
    const primaryLogoFiles = [
        'assets/logo.png',
        'assets/logo_maqayis.png',
        'assets/logo_transparent.png',
        'assets/new_logo_m.png',
        'assets/new_logo_transparent.png',
        'assets/images/logo_final.png',
        'logo_transparent.png',
        'new logo.png',
        'pwa_dist/assets/logo.png',
        'magaiees_app/logo.png',
        'magaiees_app/assets/assets/images/logo.png',
        'magaiees_app/assets/assets/images/logo_final.png',
        'web/logo.png'
    ];

    for (const rel of primaryLogoFiles) {
        const full = path.resolve(__dirname, rel);
        const dir = path.dirname(full);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        fs.writeFileSync(full, logo512);
        console.log('Updated logo:', rel);
    }

    // Helper: create icon of any size with optional background & padding
    async function makeIcon(size, paddingRatio = 0.08, bgColor = null) {
        const innerSize = Math.max(1, Math.round(size * (1 - paddingRatio * 2)));
        const resizedLogo = await sharp(logo512)
            .resize(innerSize, innerSize, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
            .toBuffer();

        return await sharp({
            create: {
                width: size,
                height: size,
                channels: 4,
                background: bgColor || { r: 0, g: 0, b: 0, alpha: 0 }
            }
        })
        .composite([{ input: resizedLogo, gravity: 'center' }])
        .png({ quality: 95 })
        .toBuffer();
    }

    const darkBg = { r: 9, g: 16, b: 32, alpha: 1 };

    const icon192 = await makeIcon(192, 0.08, darkBg);
    const icon256 = await makeIcon(256, 0.08, darkBg);
    const icon384 = await makeIcon(384, 0.08, darkBg);
    const icon512 = await makeIcon(512, 0.08, darkBg);
    const iconMaskable192 = await makeIcon(192, 0.16, darkBg);
    const iconMaskable512 = await makeIcon(512, 0.16, darkBg);
    const apple180 = await makeIcon(180, 0.08, darkBg);
    const fav32 = await makeIcon(32, 0.04, null);

    // 2. Write PWA & Favicon files in root, assets/, pwa_dist/assets/, magaiees_app/, web/
    const iconMap = [
        ['assets/icon-192.png', icon192],
        ['assets/icon-256.png', icon256],
        ['assets/icon-384.png', icon384],
        ['assets/icon-512.png', icon512],
        ['assets/icon-maskable-512.png', iconMaskable512],
        ['assets/apple-touch-icon.png', apple180],
        ['assets/favicon-32x32.png', fav32],
        ['assets/favicon.png', fav32],
        ['assets/favicon.ico', fav32],
        ['favicon.ico', fav32],
        ['pwa_dist/assets/icon-192.png', icon192],
        ['pwa_dist/assets/icon-512.png', icon512],
        ['pwa_dist/assets/icon-maskable-512.png', iconMaskable512],
        ['pwa_dist/assets/apple-touch-icon.png', apple180],
        ['pwa_dist/assets/favicon.ico', fav32],
        ['magaiees_app/favicon.png', fav32],
        ['magaiees_app/icons/Icon-192.png', icon192],
        ['magaiees_app/icons/Icon-512.png', icon512],
        ['magaiees_app/icons/Icon-maskable-192.png', iconMaskable192],
        ['magaiees_app/icons/Icon-maskable-512.png', iconMaskable512],
        ['web/favicon.png', fav32],
        ['web/icons/Icon-192.png', icon192],
        ['web/icons/Icon-512.png', icon512],
        ['web/icons/Icon-maskable-192.png', iconMaskable192],
        ['web/icons/Icon-maskable-512.png', iconMaskable512]
    ];

    for (const [rel, buf] of iconMap) {
        const full = path.resolve(__dirname, rel);
        try {
            if (fs.existsSync(path.dirname(full))) {
                fs.writeFileSync(full, buf);
                console.log('Updated icon:', rel);
            }
        } catch (e) {
            console.warn('Skipped locked file:', rel);
        }
    }

    // 3. Update SVG wrapper icons (assets/icon.svg & pwa_dist/assets/icon.svg)
    const b64 = logo512.toString('base64');
    const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <image href="data:image/png;base64,${b64}" width="512" height="512" preserveAspectRatio="xMidYMid meet"/>
</svg>`;
    fs.writeFileSync(path.resolve(__dirname, 'assets/icon.svg'), svgContent, 'utf8');
    if (fs.existsSync(path.resolve(__dirname, 'pwa_dist/assets'))) {
        fs.writeFileSync(path.resolve(__dirname, 'pwa_dist/assets/icon.svg'), svgContent, 'utf8');
    }

    console.log('✅ User-uploaded Al-Saytara shield & rising arrow logo applied everywhere!');
}

generateIcons().catch(err => {
    console.error(err);
    process.exit(1);
});
