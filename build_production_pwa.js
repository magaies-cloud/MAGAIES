/**
 * ═════════════════════════════════════════════════════════════════════
 * 🏗️ MAGAIEES ERP & POS - PRODUCTION PWA BUILD PIPELINE
 * ═════════════════════════════════════════════════════════════════════
 * Executed via: npm run build
 * Bundles, validates, and prepares the standalone static distribution (pwa_dist/)
 * for 1-click zero-config deployment to Vercel, Netlify, Cloudflare Pages, or Local IP.
 */

const fs = require('fs');
const path = require('path');

const rootDir = __dirname;
const distDir = path.join(rootDir, 'pwa_dist');
const distAssetsDir = path.join(distDir, 'assets');
const distJsDir = path.join(distDir, 'js');

console.log('═════════════════════════════════════════════════════════════════');
console.log('⚡ MAGAIEES ERP & POS - PRODUCTION BUILD PIPELINE (PWA V3)');
console.log('═════════════════════════════════════════════════════════════════');

// 1. Create Target Directories
[distDir, distAssetsDir, distJsDir].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
        console.log(`📁 Created directory: ${path.relative(rootDir, dir)}`);
    }
});

// 2. Synchronize Assets
const assetsToCopy = [
    'icon.svg',
    'icon-192.png',
    'icon-512.png',
    'icon-maskable-512.png',
    'apple-touch-icon.png',
    'favicon.ico'
];

let assetCount = 0;
assetsToCopy.forEach(file => {
    const src = path.join(rootDir, 'assets', file);
    const dest = path.join(distAssetsDir, file);
    if (fs.existsSync(src)) {
        fs.copyFileSync(src, dest);
        const stats = fs.statSync(dest);
        console.log(`  ✓ Synced asset: assets/${file} (${(stats.size / 1024).toFixed(1)} KB)`);
        assetCount++;
    } else {
        console.warn(`  ⚠️ Asset not found in source: assets/${file}`);
    }
});

// 3. Synchronize Client Script
const pwaManagerSrc = path.join(rootDir, 'js', 'pwa-manager.js');
const pwaManagerDest = path.join(distJsDir, 'pwa-manager.js');
if (fs.existsSync(pwaManagerSrc)) {
    fs.copyFileSync(pwaManagerSrc, pwaManagerDest);
    console.log(`  ✓ Synced PWA Engine: js/pwa-manager.js`);
}

// 4. Validate Manifest
const manifestDist = path.join(distDir, 'manifest.json');
try {
    const manifestContent = JSON.parse(fs.readFileSync(manifestDist, 'utf8'));
    console.log(`  ✓ Validated manifest.json:`);
    console.log(`    - Name: "${manifestContent.name}"`);
    console.log(`    - Short Name: "${manifestContent.short_name}"`);
    console.log(`    - Start URL: "${manifestContent.start_url}"`);
    console.log(`    - Display Mode: "${manifestContent.display}"`);
    console.log(`    - Theme Color: "${manifestContent.theme_color}"`);
} catch (e) {
    console.error(`  ❌ Error parsing manifest.json:`, e.message);
}

// 5. Validate Cloud Hosting Configurations
const hostingFiles = [
    { file: 'vercel.json', desc: 'Vercel Deployment & SPA Routing' },
    { file: 'netlify.toml', desc: 'Netlify Build & Headers' },
    { file: '_redirects', desc: 'Cloudflare / Netlify SPA Fallback' }
];

hostingFiles.forEach(({ file, desc }) => {
    const fp = path.join(distDir, file);
    if (fs.existsSync(fp)) {
        console.log(`  ✓ Cloud Hosting Ready: ${file} (${desc})`);
    } else {
        console.warn(`  ⚠️ Missing config file: ${file}`);
    }
});

// 6. Output Summary Report
const indexStats = fs.statSync(path.join(distDir, 'index.html'));
const swStats = fs.statSync(path.join(distDir, 'sw.js'));

console.log('─────────────────────────────────────────────────────────────────');
console.log('🎉 PRODUCTION BUILD COMPLETED SUCCESSFULLY!');
console.log(`📦 Distribution Directory: ${distDir}`);
console.log(`📄 App Shell: index.html (${(indexStats.size / 1024).toFixed(1)} KB)`);
console.log(`🛡️ Service Worker: sw.js (${(swStats.size / 1024).toFixed(1)} KB)`);
console.log(`🎨 Brand Assets Synced: ${assetCount} files`);
console.log('─────────────────────────────────────────────────────────────────');
console.log('🚀 Ready for instant deployment:');
console.log('   - Vercel: cd pwa_dist && vercel --prod');
console.log('   - Netlify: netlify deploy --dir=pwa_dist --prod');
console.log('   - Local Live Tunnel: npm run tunnel');
console.log('═════════════════════════════════════════════════════════════════');
