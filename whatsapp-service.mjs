/**
 * ╔══════════════════════════════════════════════════════════════╗
 * ║  WhatsApp Baileys Microservice — Production-Grade (v3.0)    ║
 * ║  Designed for maximum stability and zero-downtime            ║
 * ╚══════════════════════════════════════════════════════════════╝
 */

import express from 'express';
import cors from 'cors';
import multer from 'multer';
import makeWASocket, {
    useMultiFileAuthState,
    DisconnectReason,
    Browsers,
} from '@whiskeysockets/baileys';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// ─── ESM __dirname polyfill ────────────────────────────────────
const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);

// ─── Constants ─────────────────────────────────────────────────
const PORT                   = 3001;
const AUTH_BASE_DIR          = __dirname;
const UPLOADS_DIR            = path.join(__dirname, 'uploads');
const MAX_RECONNECT_ATTEMPTS = 20;
const KEEPALIVE_INTERVAL_MS  = 25_000;   // 25s presence ping
const WATCHDOG_INTERVAL_MS   = 60_000;   // 60s health check
const INIT_COOLDOWN_MS       = 3_000;    // min gap between init attempts
const QR_TIMEOUT_MS          = 120_000;  // 2min — give up if QR not scanned
const BACKOFF_BASE_MS        = 2_000;
const HEALER_INTERVAL_MS     = 180_000;  // 3min — auto-healer cycle
const HEALER_MAX_LOG         = 50;       // keep last 50 healer actions
const BACKOFF_MAX_MS         = 60_000;

// ─── Logging ───────────────────────────────────────────────────
function log(tag, ...args) {
    const ts = new Date().toISOString().slice(11, 23);
    console.log(`[${ts}][${tag}]`, ...args);
}
function logErr(tag, ...args) {
    const ts = new Date().toISOString().slice(11, 23);
    console.error(`[${ts}][${tag}]`, ...args);
}

// ─── Valid State Transitions (State Machine) ───────────────────
//
//   IDLE ──► INITIALIZING ──► CONNECTING ──► CONNECTED
//                │                │              │
//                ▼                ▼              ▼
//             FAILED        QR_READY       RECONNECTING ──► CONNECTING
//                            │                  │
//                            ▼                  ▼
//                       CONNECTED            FAILED (after max attempts)
//
//   Any ──► LOGGED_OUT (terminal — requires manual re-link)
//   Any ──► DISCONNECTING ──► IDLE (manual logout)
//

// ═══════════════════════════════════════════════════════════════
//  WhatsAppClient — manages a single user's WhatsApp session
// ═══════════════════════════════════════════════════════════════
class WhatsAppClient {
    constructor(userId) {
        this.userId            = userId;
        this.socket            = null;
        this.status            = 'IDLE';
        this.qrCode            = null;
        this.phoneNumber       = null;
        this.reconnectAttempts  = 0;
        this.lastInitTime      = 0;

        // Timers (stored for cleanup)
        this._keepAliveTimer   = null;
        this._watchdogTimer    = null;
        this._reconnectTimer   = null;
        this._qrTimeoutTimer   = null;

        // Flags
        this._isInitializing   = false;
        this._isDestroying     = false;
    }

    get authFolder() {
        return path.join(AUTH_BASE_DIR, `baileys_auth_${this.userId}`);
    }

    get isConnected() {
        return this.status === 'CONNECTED' && this.socket !== null;
    }

    // ─── Safe state setter with logging ────────────────────────
    _setState(newStatus) {
        if (this.status !== newStatus) {
            log(this.userId, `${this.status} → ${newStatus}`);
            this.status = newStatus;
        }
    }

    // ─── Clear all timers ──────────────────────────────────────
    _clearAllTimers() {
        if (this._keepAliveTimer)  { clearInterval(this._keepAliveTimer);  this._keepAliveTimer  = null; }
        if (this._watchdogTimer)   { clearInterval(this._watchdogTimer);   this._watchdogTimer   = null; }
        if (this._reconnectTimer)  { clearTimeout(this._reconnectTimer);   this._reconnectTimer  = null; }
        if (this._qrTimeoutTimer)  { clearTimeout(this._qrTimeoutTimer);   this._qrTimeoutTimer  = null; }
    }

    // ─── Destroy socket without reconnecting ───────────────────
    _destroySocket() {
        if (this.socket) {
            try { this.socket.ev.removeAllListeners(); } catch (_) {}
            try { this.socket.end(undefined); }          catch (_) {}
            this.socket = null;
        }
    }

    // ─── Keep-Alive: periodic presence ping ────────────────────
    _startKeepAlive() {
        this._stopKeepAlive();
        this._keepAliveTimer = setInterval(async () => {
            if (!this.isConnected) { this._stopKeepAlive(); return; }
            try {
                await this.socket.sendPresenceUpdate('available');
            } catch (e) {
                logErr(this.userId, 'keepAlive ping failed:', e.message);
            }
        }, KEEPALIVE_INTERVAL_MS);
    }

    _stopKeepAlive() {
        if (this._keepAliveTimer) {
            clearInterval(this._keepAliveTimer);
            this._keepAliveTimer = null;
        }
    }

    // ─── Watchdog: detect zombie/stuck connections ─────────────
    _startWatchdog() {
        this._stopWatchdog();
        this._watchdogTimer = setInterval(() => {
            // If status says CONNECTED but socket is gone → force reconnect
            if (this.status === 'CONNECTED' && !this.socket) {
                logErr(this.userId, 'WATCHDOG: status=CONNECTED but socket is null! Triggering reconnect.');
                this._scheduleReconnect(1000);
            }
            // If stuck in INITIALIZING/CONNECTING for > 90s → force restart
            if ((this.status === 'INITIALIZING' || this.status === 'CONNECTING') &&
                Date.now() - this.lastInitTime > 90_000) {
                logErr(this.userId, 'WATCHDOG: stuck in', this.status, 'for >90s. Force restarting.');
                this._destroySocket();
                this._isInitializing = false;
                this._scheduleReconnect(2000);
            }
        }, WATCHDOG_INTERVAL_MS);
    }

    _stopWatchdog() {
        if (this._watchdogTimer) {
            clearInterval(this._watchdogTimer);
            this._watchdogTimer = null;
        }
    }

    // ─── Reconnect with exponential backoff ────────────────────
    _scheduleReconnect(overrideDelayMs) {
        if (this._isDestroying) return;
        if (this.reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
            logErr(this.userId, `Max reconnect attempts (${MAX_RECONNECT_ATTEMPTS}) reached. Giving up.`);
            this._setState('FAILED');
            this.reconnectAttempts = 0;
            return;
        }

        const delay = overrideDelayMs ??
            Math.min(BACKOFF_BASE_MS * Math.pow(2, this.reconnectAttempts), BACKOFF_MAX_MS);
        this.reconnectAttempts++;
        this._setState('RECONNECTING');

        log(this.userId, `Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);

        if (this._reconnectTimer) clearTimeout(this._reconnectTimer);
        this._reconnectTimer = setTimeout(() => this.init(), delay);
    }

    // ─── QR Timeout: if nobody scans within 2 min ──────────────
    _startQRTimeout() {
        this._stopQRTimeout();
        this._qrTimeoutTimer = setTimeout(() => {
            if (this.status === 'QR_READY') {
                log(this.userId, 'QR timeout — nobody scanned. Going idle.');
                this._destroySocket();
                this._setState('IDLE');
                this.qrCode = null;
            }
        }, QR_TIMEOUT_MS);
    }

    _stopQRTimeout() {
        if (this._qrTimeoutTimer) {
            clearTimeout(this._qrTimeoutTimer);
            this._qrTimeoutTimer = null;
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  INIT — the core initialization logic
    // ═══════════════════════════════════════════════════════════
    async init() {
        // Guard: already running, destroying, or cooling down
        if (this._isInitializing) {
            log(this.userId, 'init() skipped — already initializing');
            return this.socket;
        }
        if (this._isDestroying) {
            log(this.userId, 'init() skipped — client is being destroyed');
            return null;
        }
        if (this.socket && this.status === 'CONNECTED') {
            return this.socket;
        }
        const elapsed = Date.now() - this.lastInitTime;
        if (elapsed < INIT_COOLDOWN_MS) {
            log(this.userId, `init() skipped — cooldown (${INIT_COOLDOWN_MS - elapsed}ms remaining)`);
            return null;
        }

        this._isInitializing = true;
        this.lastInitTime    = Date.now();
        this._destroySocket();
        this._clearAllTimers();
        this._setState('INITIALIZING');

        try {
            // ── Ensure auth folder exists ──
            if (!fs.existsSync(this.authFolder)) {
                fs.mkdirSync(this.authFolder, { recursive: true });
            }

            // ── Load auth state (with backup & corruption recovery) ──
            const credsPath = path.join(this.authFolder, 'creds.json');
            const bakPath   = credsPath + '.bak';
            
            if (fs.existsSync(credsPath)) {
                try {
                    const stats = fs.statSync(credsPath);
                    if (stats.size === 0 || !this._isValidJsonFile(credsPath)) {
                        logErr(this.userId, 'creds.json is empty or invalid JSON!');
                        if (fs.existsSync(bakPath) && fs.statSync(bakPath).size > 0 && this._isValidJsonFile(bakPath)) {
                            log(this.userId, 'Restoring credentials from backup (.bak)...');
                            fs.copyFileSync(bakPath, credsPath);
                        }
                    }
                } catch (err) {
                    logErr(this.userId, 'Error checking credentials health:', err.message);
                }
            }

            let state, saveCreds;
            try {
                ({ state, saveCreds } = await useMultiFileAuthState(this.authFolder));
            } catch (authErr) {
                logErr(this.userId, 'Auth state recovery failed, attempting backup restore...');
                if (fs.existsSync(bakPath) && this._isValidJsonFile(bakPath)) {
                    try {
                        fs.copyFileSync(bakPath, credsPath);
                        ({ state, saveCreds } = await useMultiFileAuthState(this.authFolder));
                        log(this.userId, 'Successfully recovered session using backup credentials.');
                    } catch (bakErr) {
                        logErr(this.userId, 'Backup credentials also corrupt:', bakErr.message);
                        this._deleteAuthFolder();
                        fs.mkdirSync(this.authFolder, { recursive: true });
                        ({ state, saveCreds } = await useMultiFileAuthState(this.authFolder));
                    }
                } else {
                    logErr(this.userId, 'No valid backup found — resetting auth state completely.');
                    this._deleteAuthFolder();
                    fs.mkdirSync(this.authFolder, { recursive: true });
                    ({ state, saveCreds } = await useMultiFileAuthState(this.authFolder));
                }
            }

            // ── Create socket ──
            const createSocket = makeWASocket.default || makeWASocket;
            const sock = createSocket({
                auth: state,
                browser: Browsers.ubuntu('Chrome'),
                printQRInTerminal: false,
                logger: pino({ level: 'error' }),
                syncFullHistory: false,
                markOnlineOnConnect: false,
                connectTimeoutMs: 180_000,
                defaultQueryTimeoutMs: 180_000,
                keepAliveIntervalMs: 15_000,
                retryRequestDelayMs: 250,
            });

            this.socket = sock;

            // ── Save credentials on every update ──
            sock.ev.on('creds.update', async () => {
                try { 
                    await saveCreds(); 
                    // Backup creds.json
                    const credsPath = path.join(this.authFolder, 'creds.json');
                    const bakPath   = credsPath + '.bak';
                    if (fs.existsSync(credsPath) && this._isValidJsonFile(credsPath)) {
                        fs.copyFileSync(credsPath, bakPath);
                    }
                }
                catch (e) { logErr(this.userId, 'creds.update FAILED:', e.message); }
            });

            // ── Connection update handler ──
            sock.ev.on('connection.update', (update) => {
                this._handleConnectionUpdate(update);
            });

            this._startWatchdog();
            this._isInitializing = false;
            return sock;

        } catch (err) {
            logErr(this.userId, 'init() FATAL ERROR:', err.message);
            this._isInitializing = false;
            this._setState('FAILED');
            this._scheduleReconnect(5000);
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  CONNECTION UPDATE handler
    // ═══════════════════════════════════════════════════════════
    async _handleConnectionUpdate(update) {
        const { connection, lastDisconnect, qr } = update;

        // ── QR Code generated ──
        if (qr) {
            try {
                this.qrCode = await QRCode.toDataURL(qr, {
                    width: 300, margin: 2,
                    color: { dark: '#000000', light: '#ffffff' }
                });
            } catch (_) {
                this.qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(qr);
            }
            this._setState('QR_READY');
            this._startQRTimeout();
            return;
        }

        // ── Connection opened ──
        if (connection === 'open') {
            log(this.userId, '✅ CONNECTED!');
            this._setState('CONNECTED');
            this.qrCode = null;
            this.reconnectAttempts = 0;
            this._stopQRTimeout();

            if (this.socket?.user?.id) {
                this.phoneNumber = this.socket.user.id.split(':')[0];
                log(this.userId, 'Phone:', this.phoneNumber);
            }

            this._startKeepAlive();
            return;
        }

        // ── Connecting ──
        if (connection === 'connecting') {
            this._setState('CONNECTING');
            return;
        }

        // ── Connection closed ──
        if (connection === 'close') {
            const code   = lastDisconnect?.error?.output?.statusCode;
            const reason = lastDisconnect?.error?.message || 'Unknown';
            logErr(this.userId, `Connection closed. Code: ${code}, Reason: ${reason}`);

            this.qrCode = null;
            this._stopKeepAlive();
            this._stopQRTimeout();
            this._destroySocket();

            switch (code) {
                case DisconnectReason.loggedOut:
                    log(this.userId, 'Disconnected with code 401 (Logged Out) — Keeping auth for manual recovery or retry.');
                    this._setState('LOGGED_OUT');
                    this.phoneNumber = null;
                    this.reconnectAttempts = 0;
                    this._scheduleReconnect(10_000); // Try to reconnect after 10s (prevents accidental logout on glitch)
                    break;

                case DisconnectReason.restartRequired:
                    log(this.userId, 'Restart required — reconnecting immediately');
                    this._scheduleReconnect(1000);
                    break;

                case DisconnectReason.multideviceMismatch:
                    log(this.userId, 'Multi-device mismatch — Keeping auth for manual recovery.');
                    this._setState('DISCONNECTED');
                    this.reconnectAttempts = 0;
                    this._scheduleReconnect(10_000);
                    break;

                case DisconnectReason.connectionClosed:
                case DisconnectReason.connectionLost:
                case DisconnectReason.connectionReplaced:
                case DisconnectReason.timedOut:
                default:
                    this._scheduleReconnect();
                    break;
            }
        }
    }

    // ─── Delete auth folder safely ─────────────────────────────
    _deleteAuthFolder() {
        try {
            if (fs.existsSync(this.authFolder)) {
                fs.rmSync(this.authFolder, { recursive: true, force: true });
                log(this.userId, 'Auth folder deleted');
            }
            // Also delete backup if it exists
            const bakPath = path.join(this.authFolder, 'creds.json.bak');
            if (fs.existsSync(bakPath)) {
                fs.unlinkSync(bakPath);
                log(this.userId, 'Backup credentials deleted');
            }
        } catch (e) {
            logErr(this.userId, 'Failed to delete auth folder:', e.message);
        }
    }

    _isValidJsonFile(filepath) {
        try {
            const content = fs.readFileSync(filepath, 'utf8');
            JSON.parse(content);
            return true;
        } catch (_) {
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  PUBLIC API
    // ═══════════════════════════════════════════════════════════

    async sendMessage(phone, content) {
        if (!this.isConnected) throw new Error('WhatsApp not connected');
        return this.socket.sendMessage(phone, content);
    }

    async logout() {
        this._isDestroying = true;
        this._clearAllTimers();
        this._setState('DISCONNECTING');

        if (this.socket) {
            try { await this.socket.logout(); } catch (e) {
                logErr(this.userId, 'logout() error:', e.message);
            }
        }
        this._destroySocket();
        this._deleteAuthFolder();

        this._setState('LOGGED_OUT');
        this.qrCode = null;
        this.phoneNumber = null;
        this.reconnectAttempts = 0;

        // Allow re-init after 3s
        setTimeout(() => { this._isDestroying = false; }, 3000);
    }

    async restart() {
        log(this.userId, 'Manual restart requested');
        this._clearAllTimers();
        this._destroySocket();
        this._isInitializing = false;
        this._isDestroying   = false;
        this.reconnectAttempts = 0;
        this.qrCode = null;
        this._setState('IDLE');

        // Small delay then re-init
        await new Promise(r => setTimeout(r, 2000));
        return this.init();
    }

    shutdown() {
        log(this.userId, 'Shutting down');
        this._clearAllTimers();
        this._destroySocket();
    }

    toJSON() {
        return {
            userId: this.userId,
            status: this.status,
            phoneNumber: this.phoneNumber,
            reconnectAttempts: this.reconnectAttempts,
            hasSocket: this.socket !== null,
            qrAvailable: this.qrCode !== null,
        };
    }
}


// ═══════════════════════════════════════════════════════════════
//  ClientManager — manages all WhatsApp clients
// ═══════════════════════════════════════════════════════════════
class ClientManager {
    constructor() {
        this.clients = new Map();
    }

    get(userId) {
        if (!this.clients.has(userId)) {
            this.clients.set(userId, new WhatsAppClient(userId));
        }
        return this.clients.get(userId);
    }

    async initClient(userId) {
        const client = this.get(userId);
        return client.init();
    }

    shutdownAll() {
        for (const [uid, client] of this.clients) {
            client.shutdown();
        }
    }

    toJSON() {
        const result = {};
        for (const [uid, client] of this.clients) {
            result[uid] = client.toJSON();
        }
        return result;
    }
}


// ═══════════════════════════════════════════════════════════════
//  AutoHealer — self-healing robot that monitors & auto-fixes
// ═══════════════════════════════════════════════════════════════
class AutoHealer {
    constructor(clientManager) {
        this.manager    = clientManager;
        this.enabled    = true;
        this.timer      = null;
        this.actionLog  = [];  // { time, action, userId, details }
        this.stats      = { checks: 0, fixes: 0, lastCheck: null, startedAt: new Date().toISOString() };
    }

    _log(userId, action, details = '') {
        const entry = {
            time: new Date().toISOString(),
            userId,
            action,
            details,
        };
        this.actionLog.push(entry);
        if (this.actionLog.length > HEALER_MAX_LOG) this.actionLog.shift();
        log('🤖 HEALER', `[${userId}] ${action}${details ? ' — ' + details : ''}`);
    }

    start() {
        if (this.timer) return;
        log('🤖 HEALER', '🟢 Auto-healer started (cycle: every 3 min)');
        // Run first check after 30s, then every HEALER_INTERVAL_MS
        setTimeout(() => this._runCycle(), 30_000);
        this.timer = setInterval(() => this._runCycle(), HEALER_INTERVAL_MS);
    }

    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
            log('🤖 HEALER', '🔴 Auto-healer stopped');
        }
    }

    async _runCycle() {
        if (!this.enabled) return;
        this.stats.checks++;
        this.stats.lastCheck = new Date().toISOString();

        for (const [userId, client] of this.manager.clients) {
            try {
                await this._healClient(client);
            } catch (e) {
                logErr('🤖 HEALER', `Error healing ${userId}:`, e.message);
            }
        }
    }

    async _healClient(client) {
        const { userId, status, socket } = client;

        // ── Case 1: FAILED — restart the client ──
        if (status === 'FAILED') {
            this._log(userId, 'AUTO_RESTART', 'Status was FAILED → restarting');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client._isDestroying   = false;
            client.reconnectAttempts = 0;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 2000));
            await client.init();
            return;
        }

        // ── Case 2: IDLE but should be running — init it ──
        if (status === 'IDLE') {
            this._log(userId, 'AUTO_INIT', 'Status was IDLE → initializing');
            this.stats.fixes++;
            await client.init();
            return;
        }

        // ── Case 3: CONNECTED but socket is dead ──
        if (status === 'CONNECTED' && !socket) {
            this._log(userId, 'ZOMBIE_FIX', 'Status=CONNECTED but no socket → restarting');
            this.stats.fixes++;
            client._setState('IDLE');
            client._isInitializing = false;
            await client.init();
            return;
        }

        // ── Case 4: Stuck in INITIALIZING/CONNECTING for too long ──
        if ((status === 'INITIALIZING' || status === 'CONNECTING') &&
            Date.now() - client.lastInitTime > 120_000) {
            this._log(userId, 'STUCK_FIX', `Stuck in ${status} for >2min → force restarting`);
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 2000));
            await client.init();
            return;
        }

        // ── Case 5: RECONNECTING for too long (>5 min) ──
        if (status === 'RECONNECTING' && Date.now() - client.lastInitTime > 300_000) {
            this._log(userId, 'RECONNECT_RESET', 'Stuck in RECONNECTING for >5min → fresh restart');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client.reconnectAttempts = 0;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 2000));
            await client.init();
            return;
        }

        // ── Case 6: QR_READY for too long (>5 min) ──
        if (status === 'QR_READY' && Date.now() - client.lastInitTime > 300_000) {
            this._log(userId, 'QR_STALE_FIX', 'QR displayed for >5min without scan → refreshing');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client._setState('IDLE');
            client.qrCode = null;
            await new Promise(r => setTimeout(r, 2000));
            await client.init();
            return;
        }
    }

    toJSON() {
        return {
            enabled: this.enabled,
            running: this.timer !== null,
            stats: this.stats,
            recentActions: this.actionLog.slice(-20),
        };
    }
}


// ═══════════════════════════════════════════════════════════════
//  Phone number normalization
// ═══════════════════════════════════════════════════════════════
function normalizePhoneNumber(phone) {
    if (!phone) return '';
    phone = phone.replace(/[^0-9]/g, '');
    if (phone.startsWith('00')) phone = phone.substring(2);

    if (phone.startsWith('0')) {
        if (phone.startsWith('09')) phone = '249' + phone.substring(1);
        else if (phone.startsWith('05')) phone = '966' + phone.substring(1);
        else if (phone.startsWith('01')) phone = '20'  + phone.substring(1);
    } else {
        if (phone.startsWith('9') && phone.length === 9) phone = '249' + phone;
        else if (phone.startsWith('5') && phone.length === 9) phone = '966' + phone;
    }

    if (phone.startsWith('2490'))      phone = '249' + phone.substring(4);
    else if (phone.startsWith('9660')) phone = '966' + phone.substring(4);
    else if (phone.startsWith('200'))  phone = '20'  + phone.substring(3);

    return phone;
}


// ═══════════════════════════════════════════════════════════════
//  EXPRESS APP & ROUTES
// ═══════════════════════════════════════════════════════════════
const manager = new ClientManager();
const healer  = new AutoHealer(manager);
const app     = express();

app.use(cors());
app.use(express.json({ limit: '50mb' }));

// Ensure uploads directory
if (!fs.existsSync(UPLOADS_DIR)) fs.mkdirSync(UPLOADS_DIR, { recursive: true });
const upload = multer({ dest: UPLOADS_DIR });

// ─── Global error handler for Express ──────────────────────────
app.use((err, _req, res, _next) => {
    logErr('EXPRESS', 'Unhandled route error:', err.message);
    res.status(500).json({ success: false, error: 'Internal server error' });
});


// ── GET /wa/status ─────────────────────────────────────────────
app.get('/wa/status', (req, res) => {
    const userId = req.query.userId || 'default';
    const client = manager.get(userId);

    // Auto-init if idle or failed
    if (client.status === 'IDLE' || client.status === 'FAILED') {
        client.init().catch(e => logErr(userId, 'auto-init failed:', e.message));
    }

    res.json(client.toJSON());
});


// ── GET /wa/qr ─────────────────────────────────────────────────
app.get('/wa/qr', (req, res) => {
    const userId = req.query.userId || 'default';
    const client = manager.get(userId);
    res.json({
        userId,
        status: client.status,
        qrCode: client.qrCode,
        phoneNumber: client.phoneNumber,
    });
});


// ── POST /wa/send ──────────────────────────────────────────────
app.post('/wa/send', upload.single('pdf'), async (req, res) => {
    const userId  = req.body.userId || 'default';
    let   phone   = req.body.phone;
    const message = req.body.message || '';
    const client  = manager.get(userId);

    if (!client.isConnected) {
        return res.status(400).json({ success: false, error: 'WhatsApp not connected.' });
    }
    if (!phone) {
        return res.status(400).json({ success: false, error: 'Phone number is required.' });
    }

    phone = normalizePhoneNumber(phone);
    if (!phone.endsWith('@s.whatsapp.net')) {
        phone = `${phone}@s.whatsapp.net`;
    }

    try {
        if (req.file) {
            await client.sendMessage(phone, {
                document: { url: req.file.path },
                mimetype: 'application/pdf',
                fileName: req.file.originalname || 'document.pdf',
                caption: message,
            });
            // Clean up uploaded file
            fs.unlink(req.file.path, () => {});
        } else {
            await client.sendMessage(phone, { text: message });
        }
        res.json({ success: true, message: 'Sent successfully!' });
    } catch (err) {
        logErr(userId, 'send error:', err.message);
        res.status(500).json({ success: false, error: 'Failed to send', details: err.message });
    }
});


// ── POST /wa/logout ────────────────────────────────────────────
app.post('/wa/logout', async (req, res) => {
    const userId = req.body?.userId || req.query.userId || 'default';
    const client = manager.get(userId);
    try {
        await client.logout();
        res.json({ success: true, message: 'Logged out.' });
    } catch (e) {
        logErr(userId, 'logout error:', e.message);
        res.status(500).json({ success: false, error: e.message });
    }
});


// ── POST /wa/restart ───────────────────────────────────────────
app.post('/wa/restart', async (req, res) => {
    const userId = req.body?.userId || req.query.userId || 'default';
    const client = manager.get(userId);

    // Respond immediately — restart happens in background
    res.json({ success: true, message: 'Restarting. Check status in a few seconds.' });

    try {
        await client.restart();
    } catch (e) {
        logErr(userId, 'restart error:', e.message);
    }
});


// ── GET /wa/health ─────────────────────────────────────────────
app.get('/wa/health', (_req, res) => {
    res.json({
        uptime: process.uptime(),
        memory: process.memoryUsage(),
        nodeVersion: process.version,
        clients: manager.toJSON(),
    });
});


// ── GET /wa/diagnose ───────────────────────────────────────────
app.get('/wa/diagnose', async (_req, res) => {
    const tests = {};

    // Test 1: DNS
    try {
        const dns = await import('dns');
        const resolve = dns.promises?.resolve4 || dns.default.promises.resolve4;
        tests.dns = { success: true, addresses: await resolve('web.whatsapp.com') };
    } catch (e) {
        tests.dns = { success: false, error: e.message };
    }

    // Test 2: HTTPS
    try {
        const https = await import('https');
        const mod = https.default || https;
        await new Promise((resolve) => {
            const r = mod.get('https://web.whatsapp.com', { timeout: 10_000 }, (resp) => {
                tests.https = { success: true, statusCode: resp.statusCode };
                resp.destroy(); resolve();
            });
            r.on('error', (e) => { tests.https = { success: false, error: e.message }; resolve(); });
            r.on('timeout', () => { tests.https = { success: false, error: 'Timeout' }; r.destroy(); resolve(); });
        });
    } catch (e) {
        tests.https = { success: false, error: e.message };
    }

    // Test 3: WebSocket
    try {
        const { WebSocket } = await import('ws');
        await new Promise((resolve) => {
            const ws = new WebSocket('wss://web.whatsapp.com/ws/chat', {
                origin: 'https://web.whatsapp.com', timeout: 10_000,
                headers: { 'User-Agent': 'Mozilla/5.0 Chrome/120.0.0.0' },
            });
            const t = setTimeout(() => { tests.websocket = { success: false, error: 'Timeout' }; ws.close(); resolve(); }, 10_000);
            ws.on('open', ()  => { clearTimeout(t); tests.websocket = { success: true }; ws.close(); resolve(); });
            ws.on('error', (e) => { clearTimeout(t); tests.websocket = { success: false, error: e.message }; resolve(); });
        });
    } catch (e) {
        tests.websocket = { success: false, error: e.message };
    }

    // Test 4: Auth folder
    const af = path.join(AUTH_BASE_DIR, 'baileys_auth_default-saas-admin');
    try {
        if (!fs.existsSync(af)) fs.mkdirSync(af, { recursive: true });
        const tmp = path.join(af, '.test'); fs.writeFileSync(tmp, 'ok'); fs.unlinkSync(tmp);
        const files = fs.readdirSync(af);
        tests.authFolder = { success: true, path: af, fileCount: files.length };
    } catch (e) {
        tests.authFolder = { success: false, error: e.message };
    }

    tests.clients = manager.toJSON();

    const ok = tests.dns?.success && tests.https?.success && tests.websocket?.success && tests.authFolder?.success;
    res.json({
        success: !!ok,
        nodeVersion: process.version, platform: process.platform, arch: process.arch,
        tests,
        summary: ok
            ? '✅ All tests passed.'
            : '❌ Some tests failed — check details above.',
    });
});


// ── GET /wa/healer ─────────────────────────────────────────────
app.get('/wa/healer', (_req, res) => {
    healer.forceCheck();
    res.json({ success: true, message: 'Healer triggered manually' });
});

// ── POST /wa/healer/toggle ─────────────────────────────────────
app.post('/wa/healer/toggle', (req, res) => {
    healer.enabled = !healer.enabled;
    if (healer.enabled && !healer.timer) healer.start();
    log('🤖 HEALER', `Toggled: ${healer.enabled ? 'ENABLED' : 'DISABLED'}`);
    res.json({ success: true, enabled: healer.enabled });
});

// ── 404 AND GLOBAL ERROR HANDLERS ──────────────────────────────
app.use((req, res) => {
    res.status(404).json({ success: false, error: 'Endpoint not found in Node.js service: ' + req.path });
});

app.use((err, req, res, next) => {
    console.error('Express Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal Node.js Error: ' + err.message,
        stack: err.stack
    });
});


// ═══════════════════════════════════════════════════════════════
//  STARTUP
// ═══════════════════════════════════════════════════════════════

// Clean stale uploads
try {
    if (fs.existsSync(UPLOADS_DIR)) {
        const stale = fs.readdirSync(UPLOADS_DIR);
        stale.forEach(f => { try { fs.unlinkSync(path.join(UPLOADS_DIR, f)); } catch (_) {} });
        if (stale.length) log('STARTUP', `Cleaned ${stale.length} stale uploads`);
    }
} catch (_) {}

const server = app.listen(PORT, () => {
    log('STARTUP', `🚀 WhatsApp service on port ${PORT}`);
    log('STARTUP', `📁 Auth: ${AUTH_BASE_DIR}`);
    log('STARTUP', `📦 Node ${process.version} | ${process.platform}/${process.arch}`);
    manager.initClient('default-saas-admin');
    healer.start();
});


// ═══════════════════════════════════════════════════════════════
//  GRACEFUL SHUTDOWN
// ═══════════════════════════════════════════════════════════════
const shutdown = (signal) => {
    log('SHUTDOWN', `Received ${signal}`);
    manager.shutdownAll();
    server.close(() => {
        log('SHUTDOWN', 'HTTP server closed. Goodbye!');
        process.exit(0);
    });
    setTimeout(() => { process.exit(1); }, 5000);
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT',  () => shutdown('SIGINT'));
process.on('uncaughtException',  (err) => logErr('FATAL', 'Uncaught Exception:', err));
process.on('unhandledRejection', (err) => logErr('FATAL', 'Unhandled Rejection:', err));
