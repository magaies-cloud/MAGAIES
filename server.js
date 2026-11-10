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

const keepAliveAgent = new http.Agent({ keepAlive: true, maxSockets: 50 });

const net = require('net');
const CLOUD_HOST = 'c.elsajana.com';

let _cachedLocalMySql = null;
let _cachedLocalMySqlAt = 0;

function checkLocalMySql(req, callback) {
    const now = Date.now();
    if (_cachedLocalMySql !== null && (now - _cachedLocalMySqlAt) < 5000) {
        callback(_cachedLocalMySql);
        return;
    }
    req.pause();
    const sock = new net.Socket();
    let done = false;
    const finish = (ok) => {
        if (done) return;
        done = true;
        sock.destroy();
        _cachedLocalMySql = ok;
        _cachedLocalMySqlAt = Date.now();
        callback(ok);
        req.resume();
    };
    sock.setTimeout(40);
    sock.once('connect', () => finish(true));
    sock.once('timeout', () => finish(false));
    sock.once('error', () => finish(false));
    sock.connect(3306, '127.0.0.1');
}

function proxyToCloud(req, res, targetPath) {
    console.log(`[CLOUD-API] ${req.method} ${targetPath} → https://${CLOUD_HOST}`);
    const proxyHeaders = { ...req.headers, host: CLOUD_HOST };
    delete proxyHeaders['origin'];
    delete proxyHeaders['referer'];

    const options = {
        hostname: CLOUD_HOST,
        port: 443,
        path: targetPath,
        method: req.method,
        headers: proxyHeaders,
        rejectUnauthorized: false
    };

    const proxy = https.request(options, (proxyRes) => {
        const resHeaders = { ...proxyRes.headers };
        resHeaders['access-control-allow-origin'] = req.headers.origin || '*';
        resHeaders['access-control-allow-credentials'] = 'true';
        res.writeHead(proxyRes.statusCode, resHeaders);
        proxyRes.pipe(res, { end: true });
    });

    proxy.on('error', (err) => {
        console.error('[CLOUD PROXY ERROR]', err.message);
        if (!res.headersSent) {
            res.writeHead(502, { 'Content-Type': 'application/json; charset=UTF-8' });
            res.end(JSON.stringify({ success: false, error: 'تعذر الاتصال بالسيرفر السحابي', detail: err.message }));
        }
    });

    req.pipe(proxy, { end: true });
}

const requestHandler = (req, res) => {
    // ---- CORS Headers ----
    res.setHeader('Access-Control-Allow-Origin', req.headers.origin || '*');
    res.setHeader('Access-Control-Allow-Credentials', 'true');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Tenant-Subdomain, X-Tenant-Id, Accept, X-Requested-With');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const queryIndex = req.url.indexOf('?');
    const queryString = queryIndex !== -1 ? req.url.slice(queryIndex) : '';
    let reqUrl = queryIndex !== -1 ? req.url.slice(0, queryIndex) : req.url;

    // Redirect folder paths without trailing slash so relative assets resolve properly
    if (/^\/(ELSAJANA2\/magaies|ELSAJANA2\/magaees|magaies|magaees)$/i.test(reqUrl)) {
        res.writeHead(302, { Location: reqUrl + '/' + queryString });
        res.end();
        return;
    }

    // Strip legacy XAMPP subfolder prefixes (/ELSAJANA2/magaies, /magaees, etc.)
    reqUrl = reqUrl.replace(/^\/(ELSAJANA2\/magaies|ELSAJANA2\/magaees|ELSAJANA2|magaies|magaees)(?=\/|$)/i, '') || '/';

    // Ignore favicon requests to prevent 404 errors in the console
    if (reqUrl === '/favicon.ico') {
        res.writeHead(204);
        res.end();
        return;
    }

    // Rewrite .html to .php if the .php file exists (e.g. /register.html?plan=4 -> /register.php?plan=4)
    if (reqUrl.endsWith('.html')) {
        const phpFile = path.join(DIR, reqUrl.replace(/\.html$/, '.php'));
        if (fs.existsSync(phpFile)) {
            reqUrl = reqUrl.replace(/\.html$/, '.php');
        }
    }

    if (reqUrl === '/') reqUrl = '/login.php';

    const isApiCall = reqUrl.startsWith('/api_index.php')
        || reqUrl.startsWith('/api/')
        || reqUrl.startsWith('/api_')
        || /(?:^\?|&)(?:api|action)=/i.test(queryString)
        || (req.method !== 'GET' && req.method !== 'HEAD');

    if (isApiCall) {
        checkLocalMySql(req, (hasLocalDb) => {
            if (!hasLocalDb) {
                proxyToCloud(req, res, reqUrl + queryString);
                return;
            }
            forwardToLocalPhp(req, res, reqUrl, queryString);
        });
        return;
    }

    // ---- Proxy: إرسال واجهات PHP للـ PHP المحلي ----
    if (reqUrl.endsWith('.php') || reqUrl.includes('.php/')) {
        forwardToLocalPhp(req, res, reqUrl, queryString);
        return;
    }

    // ---- Static Files ----
    let safeUrl = reqUrl.startsWith('/') ? reqUrl.slice(1) : reqUrl;
    let file = path.join(DIR, safeUrl);

    if (!path.extname(file)) file += '.php'; // Default to .php for extensions

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
};

function forwardToLocalPhp(req, res, reqUrl, queryString) {
    console.log(`[LOCAL-PHP] ${req.method} ${reqUrl} → ${LIVE_SERVER}:8080`);

    const proxyHeaders = {
        ...req.headers,
        host: LIVE_SERVER
    };

    const options = {
        hostname: LIVE_SERVER,
        port: 8080,
        path: reqUrl + queryString,
        method: req.method,
        headers: proxyHeaders,
        agent: false
    };

    const proxy = http.request(options, (proxyRes) => {
        res.writeHead(proxyRes.statusCode, {
            ...proxyRes.headers,
            'Access-Control-Allow-Origin': req.headers.origin || '*'
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
}

// الاستماع على المنفذ 80 لدعم http://localhost/ELSAJANA2/magaies
const server80 = http.createServer(requestHandler);
server80.on('error', (err) => {
    console.warn(`⚠️ تعذر الاستماع على المنفذ 80 (${err.message})، يعمل السيرفر على المنفذ ${PORT}`);
});
server80.listen(80, () => {
    console.log(`✅ السيرفر المحلي يعمل على المنفذ 80: http://localhost/ELSAJANA2/magaies/`);
});

http.createServer(requestHandler).listen(PORT, () => {
    console.log(`✅ السيرفر المحلي يعمل على: http://localhost:${PORT}/index.php`);
    console.log(`🔗 طلبات PHP تُعاد توجيهها إلى السيرفر المدمج (PHP) على المنفذ 8080 لتجربة التعديلات محلياً.`);
    
    // تشغيل خادم PHP المدمج تلقائياً
    const phpServer = spawn('php', ['-S', '127.0.0.1:8080', 'router.php'], { stdio: 'inherit', shell: true });
    
    phpServer.on('error', (err) => {
        console.error('❌ فشل تشغيل خادم PHP:', err.message);
    });

    // فتح المتصفح تلقائياً على الرابط المحلي
    const { exec } = require('child_process');
    const startCmd = process.platform === 'win32' ? 'start' : (process.platform === 'darwin' ? 'open' : 'xdg-open');
    exec(`${startCmd} http://localhost/ELSAJANA2/magaies/`);
});
