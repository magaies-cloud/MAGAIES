const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');
const url = require('url');
const { spawn, execSync } = require('child_process');

// تمت إزالة سحب التحديثات التلقائي (Git Pull) لتجنب حدوث تعارضات (Merge Conflicts) وتوقف النظام فجأة.


const PORT = 3000;
const DIR = __dirname;
const LIVE_SERVER = '127.0.0.1';

const MIME = {
    '.html': 'text/html', '.css': 'text/css', '.js': 'application/javascript',
    '.json': 'application/json', '.png': 'image/png', '.jpg': 'image/jpeg',
    '.svg': 'image/svg+xml', '.ico': 'image/x-icon', '.woff2': 'font/woff2'
};

http.createServer((req, res) => {
    // ---- CORS Headers ----
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    let reqUrl = req.url.split('?')[0];

    // Rewrite .html to .php if the .php file exists (e.g. /register.html?plan=4 -> /register.php?plan=4)
    if (reqUrl.endsWith('.html')) {
        const phpFile = path.join(DIR, reqUrl.replace(/\.html$/, '.php'));
        if (fs.existsSync(phpFile)) {
            req.url = req.url.replace(/\.html(\?|$)/, '.php$1');
            reqUrl = reqUrl.replace(/\.html$/, '.php');
        }
    }

    // ---- Proxy: إرسال طلبات API وواجهات PHP للـ PHP المحلي ----
    if (reqUrl === '/') reqUrl = '/index.php';
    
    if (reqUrl.endsWith('.php') || reqUrl.includes('.php/') || reqUrl.startsWith('/api/') || reqUrl.startsWith('/api_index.php')) {
        console.log(`[PROXY] ${req.method} ${reqUrl} → ${LIVE_SERVER}:8080`);

        const proxyHeaders = {
            ...req.headers,
            host: LIVE_SERVER
        };

        const options = {
            hostname: LIVE_SERVER,
            port: 8080,
            path: reqUrl + (req.url.includes('?') ? '?' + req.url.split('?')[1] : ''),
            method: req.method,
            headers: proxyHeaders
        };

        const proxy = http.request(options, (proxyRes) => {
            res.writeHead(proxyRes.statusCode, {
                ...proxyRes.headers,
                'Access-Control-Allow-Origin': '*'
            });
            proxyRes.pipe(res, { end: true });
        });

        proxy.on('error', (err) => {
            console.error('[PROXY ERROR]', err.message);
            if (!res.headersSent) {
                res.writeHead(502, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'لا يمكن الاتصال بخادم PHP', detail: err.message }));
            }
        });

        req.pipe(proxy, { end: true });
        return;
    }

    // ---- Static Files ----
    let safeUrl = reqUrl.startsWith('/') ? reqUrl.slice(1) : reqUrl;
    let file = path.join(DIR, safeUrl);

    if (!path.extname(file)) file += '.php'; // Default to .php for extensions

    console.log(`[STATIC] ${req.method} ${reqUrl}`);

    fs.readFile(file, (err, content) => {
        if (err) {
            res.writeHead(404);
            res.end('File not found');
        } else {
            let ext = path.extname(file);
            res.writeHead(200, { 
                'Content-Type': MIME[ext] || 'text/plain',
                'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            });
            res.end(content, 'utf-8');
        }
    });

}).listen(PORT, () => {
    console.log(`✅ السيرفر المحلي يعمل على: http://localhost:${PORT}/index.php`);
    console.log(`🔗 طلبات PHP تُعاد توجيهها إلى السيرفر المدمج (PHP) على المنفذ 8080 لتجربة التعديلات محلياً.`);
    console.log(`⚠️  تحذير: خادم PHP المدمج (php -S) مصمم للتطوير المحلي فقط. في بيئة الإنتاج (Production) استخدم خوادم قوية مثل Nginx أو Apache.`);
    
    // تشغيل خادم PHP المدمج تلقائياً
    const phpServer = spawn('php', ['-S', '127.0.0.1:8080'], { stdio: 'inherit', shell: true });
    
    phpServer.on('error', (err) => {
        console.error('❌ فشل تشغيل خادم PHP:', err.message);
    });

    // تشغيل خدمة الواتساب تلقائياً
    const waServer = spawn('node', ['whatsapp-service.js'], { stdio: 'inherit', shell: true });
    
    waServer.on('error', (err) => {
        console.error('❌ فشل تشغيل خدمة الواتساب:', err.message);
    });

    // فتح المتصفح تلقائياً على الرابط المحلي بطريقة تدعم جميع أنظمة التشغيل
    const { exec } = require('child_process');
    const startCmd = process.platform === 'win32' ? 'start' : (process.platform === 'darwin' ? 'open' : 'xdg-open');
    exec(`${startCmd} http://localhost:${PORT}/`);
});
