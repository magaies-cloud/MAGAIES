/**
 * ═════════════════════════════════════════════════════════════════════
 * 🚀 ELSAJANA ERP 2026 - PWA LOCAL PREVIEW & NETWORK TEST SERVER
 * ═════════════════════════════════════════════════════════════════════
 * Zero-dependency Node.js HTTP server configured with proper PWA MIME types,
 * Service Worker headers, and multi-device LAN IP testing.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const os = require('os');

const PORT = 8080;
const ROOT_DIR = path.resolve(__dirname, 'pwa_dist');

// Detect Local Network IPv4 Addresses
function getLocalIPs() {
    const interfaces = os.networkInterfaces();
    const ips = [];
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (iface.family === 'IPv4' && !iface.internal) {
                ips.push({ name, address: iface.address });
            }
        }
    }
    return ips;
}

const MIME_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.json': 'application/manifest+json; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.webp': 'image/webp',
    '.woff2': 'font/woff2',
    '.woff': 'font/woff',
    '.ttf': 'font/ttf'
};

const server = http.createServer((req, res) => {
    // Normalize URL
    let safeUrl = req.url.split('?')[0];
    if (safeUrl === '/') safeUrl = '/index.html';

    let filePath = path.join(ROOT_DIR, safeUrl);

    // If requested file doesn't exist, fallback to index.html (SPA routing)
    if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
        filePath = path.join(ROOT_DIR, 'index.html');
    }

    const ext = path.extname(filePath).toLowerCase();
    const contentType = MIME_TYPES[ext] || 'application/octet-stream';

    // PWA & Service Worker Security Headers
    res.setHeader('Content-Type', contentType);
    res.setHeader('X-Content-Type-Options', 'nosniff');
    res.setHeader('Access-Control-Allow-Origin', '*');

    if (safeUrl === '/sw.js') {
        res.setHeader('Cache-Control', 'public, max-age=0, must-revalidate');
        res.setHeader('Service-Worker-Allowed', '/');
    }

    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.writeHead(500);
            res.end('Server Error: ' + err.message);
            return;
        }
        res.writeHead(200);
        res.end(data);
    });
});

server.listen(PORT, '0.0.0.0', () => {
    const ips = getLocalIPs();
    console.log('\n═════════════════════════════════════════════════════════════════');
    console.log('💎 ELSAJANA ERP 2026 - PWA LOCAL & NETWORK PREVIEW SERVER ACTIVE');
    console.log('═════════════════════════════════════════════════════════════════');
    console.log(`💻 Local Machine:    http://localhost:${PORT}/`);
    ips.forEach(ip => {
        console.log(`📱 Wi-Fi / LAN Phone: http://${ip.address}:${PORT}/`);
    });
    console.log(`⚡ Apache PHP Link:  http://${ips[0]?.address || 'localhost'}/ELSAJANA2/magaies/dashboard.php`);
    console.log('═════════════════════════════════════════════════════════════════');
    console.log('👉 Tip: Open the Wi-Fi link on your iPhone or Android phone to test');
    console.log('   Dynamic Viewport Scaling and 1-Tap "Add to Home Screen"!\n');
});
