const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');
const url = require('url');
const { spawn, execSync } = require('child_process');

// ---- سحب آخر التحديثات من GitHub تلقائياً عند التشغيل ----
try {
    console.log('🔄 جاري التحقق من التحديثات وسحب آخر التعديلات من GitHub لضمان التزامن مع الفريق...');
    const pullOutput = execSync('git pull origin main', { encoding: 'utf8' });
    console.log(pullOutput);
    console.log('✅ اكتمل التزامن بنجاح!');
} catch (err) {
    console.error('❌ حدث خطأ أثناء سحب التحديثات من GitHub. تأكد من اتصالك أو حل التعارضات:', err.message);
}


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

    // ---- Proxy: إرسال طلبات API للـ PHP المحلي لتجربة التعديلات ----
    if (reqUrl.startsWith('/api_index.php') || reqUrl.startsWith('/api/') || reqUrl.startsWith('/api_saas_auth.php')) {
        console.log(`[PROXY] ${req.method} ${req.url} → ${LIVE_SERVER}:8080`);

        // Buffer the request body first to avoid streaming issues
        let bodyChunks = [];
        req.on('data', (chunk) => bodyChunks.push(chunk));
        req.on('end', () => {
            const bodyBuffer = Buffer.concat(bodyChunks);

            const proxyHeaders = {
                ...req.headers,
                host: LIVE_SERVER
            };

            // Set correct Content-Length if there is a body
            if (bodyBuffer.length > 0) {
                proxyHeaders['content-length'] = bodyBuffer.length;
            }

            const options = {
                hostname: LIVE_SERVER,
                port: 8080,
                path: req.url,
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
                res.writeHead(502, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'لا يمكن الاتصال بالسيرفر الحي', detail: err.message }));
            });

            if (bodyBuffer.length > 0) {
                proxy.write(bodyBuffer);
            }
            proxy.end();
        });
        return;
    }

    // ---- Static Files ----
    if (reqUrl === '/') reqUrl = '/index.html';

    let safeUrl = reqUrl.startsWith('/') ? reqUrl.slice(1) : reqUrl;
    let file = path.join(DIR, safeUrl);

    if (!path.extname(file)) file += '.html';

    console.log(`[STATIC] ${req.method} ${reqUrl}`);

    const ext = path.extname(file);
    fs.readFile(file, (err, data) => {
        if (err) {
            res.writeHead(404, { 'Content-Type': 'text/plain' });
            res.end('Not Found');
            return;
        }
        res.writeHead(200, { 'Content-Type': MIME[ext] || 'text/plain' });
        res.end(data);
    });

}).listen(PORT, () => {
    console.log(`✅ السيرفر المحلي يعمل على: http://localhost:${PORT}/index.html`);
    console.log(`🔗 طلبات API تُعاد توجيهها إلى السيرفر المدمج (PHP) على المنفذ 8080 لتجربة التعديلات محلياً.`);
    
    // تشغيل خادم PHP المدمج تلقائياً
    const phpServer = spawn('php', ['-S', '127.0.0.1:8080'], { stdio: 'inherit' });
    
    phpServer.on('error', (err) => {
        console.error('❌ فشل تشغيل خادم PHP:', err.message);
    });

    // فتح المتصفح تلقائياً على الرابط المحلي
    const { exec } = require('child_process');
    exec(`start http://localhost:${PORT}/saas-admin.html`);
});
