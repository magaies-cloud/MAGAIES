/**
 * ═════════════════════════════════════════════════════════════════════
 * 🌐 MAGAIEES ERP & POS - ROBUST PUBLIC HTTPS TUNNEL ENGINE
 * ═════════════════════════════════════════════════════════════════════
 * Uses native localtunnel module with automated auto-reconnect,
 * heartbeat monitoring, and instant direct Apache forwarding.
 */

const localtunnel = require('localtunnel');
const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = 80;
const SUBDOMAIN_CANDIDATES = [
    'magaiees-live-erp',
    'magaiees-sys',
    'magaiees-' + Math.floor(1000 + Math.random() * 9000)
];

console.log('═════════════════════════════════════════════════════════════════');
console.log('🚀 MAGAIEES ERP & POS - RESILIENT LIVE HTTPS TUNNEL LAUNCHER');
console.log('═════════════════════════════════════════════════════════════════');

async function testApache() {
    return new Promise((resolve) => {
        const req = http.get(`http://localhost:${PORT}/ELSAJANA2/magaies/login.php`, (res) => {
            console.log(`✓ Apache server verified active (HTTP ${res.statusCode})`);
            resolve(true);
        });
        req.on('error', () => {
            console.log(`⚠️ Apache test request noticed error, proceeding with tunnel anyway...`);
            resolve(false);
        });
        req.setTimeout(3000, () => {
            req.abort();
            resolve(false);
        });
    });
}

let activeTunnel = null;
let reconnectTimer = null;

async function startTunnel(retryCount = 0) {
    if (activeTunnel) {
        try { activeTunnel.close(); } catch(e) {}
        activeTunnel = null;
    }

    const sub = SUBDOMAIN_CANDIDATES[retryCount % SUBDOMAIN_CANDIDATES.length];
    console.log(`📡 Opening secure tunnel on port ${PORT} (preferred: ${sub})...`);

    try {
        const tunnel = await localtunnel({
            port: PORT,
            subdomain: sub
        });

        activeTunnel = tunnel;
        const publicUrl = tunnel.url;

        console.log('\n─────────────────────────────────────────────────────────────────');
        console.log(`🚀 Magaiees ERP Live URL: ${publicUrl}`);
        console.log('─────────────────────────────────────────────────────────────────');
        console.log('📱 Mobile & Web Ready:');
        console.log(`   - Direct URL: ${publicUrl}`);
        console.log(`   - Auto-redirects directly to: ${publicUrl}/ELSAJANA2/magaies/login.php`);
        console.log('─────────────────────────────────────────────────────────────────');
        console.log('💡 Note: When opening in a new browser, click "Click to Continue"');
        console.log('   if localtunnel displays a first-visit verification page.');
        console.log('═════════════════════════════════════════════════════════════════\n');

        fs.writeFileSync(path.join(__dirname, 'LIVE_TUNNEL_URL.txt'), publicUrl, 'utf8');

        tunnel.on('close', () => {
            console.warn('⚠️ Tunnel closed. Auto-reconnecting in 3s...');
            scheduleReconnect(retryCount + 1);
        });

        tunnel.on('error', (err) => {
            console.error('⚠️ Tunnel error:', err.message, '- Auto-reconnecting in 3s...');
            scheduleReconnect(retryCount + 1);
        });

    } catch (err) {
        console.error('❌ Failed to create tunnel:', err.message);
        scheduleReconnect(retryCount + 1);
    }
}

function scheduleReconnect(nextRetry) {
    if (reconnectTimer) clearTimeout(reconnectTimer);
    reconnectTimer = setTimeout(() => {
        startTunnel(nextRetry);
    }, 3000);
}

// Keep Node process running
setInterval(() => {}, 1000 * 60 * 60);

(async () => {
    await testApache();
    await startTunnel(0);
})();
