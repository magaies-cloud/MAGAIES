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
    fetchLatestBaileysVersion,
    proto,
    downloadMediaMessage
} from '@whiskeysockets/baileys';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { execSync, exec } from 'child_process';

// ─── ESM __dirname polyfill ────────────────────────────────────
const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);

// ─── Constants ─────────────────────────────────────────────────
const PORT                   = 3001;
const AUTH_BASE_DIR          = __dirname;
const UPLOADS_DIR            = path.join(__dirname, 'uploads');
const MAX_RECONNECT_ATTEMPTS = 20;
const KEEPALIVE_INTERVAL_MS  = 300_000;  // 5m presence ping (avoid rate limiting)
const WATCHDOG_INTERVAL_MS   = 60_000;   // 60s health check
const INIT_COOLDOWN_MS       = 3_000;    // min gap between init attempts
const QR_TIMEOUT_MS          = 120_000;  // 2min — give up if QR not scanned
const BACKOFF_BASE_MS        = 2_000;    // (unused — flat delay used instead)
const HEALER_INTERVAL_MS     = 180_000;  // 3min — auto-healer cycle
const HEALER_MAX_LOG         = 50;       // keep last 50 healer actions
const BACKOFF_MAX_MS         = 60_000;   // (unused — flat delay used instead)

// ─── Logging ───────────────────────────────────────────────────
const LOG_FILE_PATH = path.join(__dirname, 'whatsapp-service.log');
function log(tag, ...args) {
    const ts = new Date().toISOString().slice(11, 23);
    console.log(`[${ts}][${tag}]`, ...args);
    try {
        const msgStr = args.map(a => (typeof a === 'string' ? a : JSON.stringify(a))).join(' ');
        fs.appendFile(LOG_FILE_PATH, `[${ts}][${tag}] ${msgStr}\n`, () => {});
    } catch (_) {}
}
function logErr(tag, ...args) {
    const ts = new Date().toISOString().slice(11, 23);
    console.error(`[${ts}][${tag}]`, ...args);
    try {
        const msgStr = args.map(a => (a instanceof Error ? a.message : (typeof a === 'string' ? a : JSON.stringify(a)))).join(' ');
        fs.appendFile(LOG_FILE_PATH, `[${ts}][${tag}] ERROR: ${msgStr}\n`, () => {});
    } catch (_) {}
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
// Handle unhandled promise rejections to prevent node from crashing
process.on('uncaughtException', err => {
    console.error('Uncaught Exception:', err);
});
process.on('unhandledRejection', err => {
    console.error('Unhandled Rejection:', err);
});

function unwrapWAMessage(msg) {
    if (!msg || !msg.message) return null;
    let m = msg.message;
    while (m) {
        if (m.deviceSentMessage?.message) { m = m.deviceSentMessage.message; continue; }
        if (m.ephemeralMessage?.message) { m = m.ephemeralMessage.message; continue; }
        if (m.viewOnceMessage?.message) { m = m.viewOnceMessage.message; continue; }
        if (m.viewOnceMessageV2?.message) { m = m.viewOnceMessageV2.message; continue; }
        if (m.viewOnceMessageV2Extension?.message) { m = m.viewOnceMessageV2Extension.message; continue; }
        if (m.documentWithCaptionMessage?.message) { m = m.documentWithCaptionMessage.message; continue; }
        if (m.editedMessage?.message?.protocolMessage?.editedMessage) { m = m.editedMessage.message.protocolMessage.editedMessage; continue; }
        break;
    }
    return m;
}

class WhatsAppClient {
    constructor(userId) {
        this.userId            = userId;
        this.socket            = null;
        this.status            = 'IDLE';
        this.qrCode            = null;
        this.phoneNumber       = null;
        this.reconnectAttempts  = 0;
        this.lastInitTime      = 0;
        this.stateEnteredAt    = Date.now();

        // Timers (stored for cleanup)
        this._keepAliveTimer   = null;
        this._watchdogTimer    = null;
        this._reconnectTimer   = null;
        this._qrTimeoutTimer   = null;

        // Flags
        this._isInitializing   = false;
        this._isDestroying     = false;

        // Message cache for retry requests (prevents phantom messages)
        this.messageCache           = new Map();
        this.lidToPhoneMap          = new Map([
            ['29523806523570@lid', '249912300622'],
            ['29523806523570', '249912300622']
        ]);
        this.processedWaMsgIds      = new Set();
        this.inboundBatchQueues     = new Map();
        this.pendingRoutingPinByJid = new Map();
        this.lastBatchByJid         = new Map();
        this._loadLidMapFromDisk();
    }

    get lidMapFilePath() {
        return path.join(this.authFolder, 'lid_phone_map.json');
    }

    _loadLidMapFromDisk() {
        try {
            if (fs.existsSync(this.lidMapFilePath)) {
                const raw = JSON.parse(fs.readFileSync(this.lidMapFilePath, 'utf8'));
                if (raw && typeof raw === 'object') {
                    for (const [k, v] of Object.entries(raw)) {
                        if (k && v) this.lidToPhoneMap.set(k, String(v));
                    }
                }
            }
        } catch (_) {}
    }

    _saveLidMapToDisk() {
        try {
            if (!fs.existsSync(this.authFolder)) return;
            const obj = Object.fromEntries(this.lidToPhoneMap.entries());
            fs.writeFileSync(this.lidMapFilePath, JSON.stringify(obj), 'utf8');
        } catch (_) {}
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
            this.stateEnteredAt = Date.now();
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
                Date.now() - (this.stateEnteredAt || this.lastInitTime) > 90_000) {
                logErr(this.userId, 'WATCHDOG: stuck in', this.status, 'for >90s. Force restarting.');
                this._destroySocket();
                this._isInitializing = false;
                this.lastInitTime = 0;
                this._scheduleReconnect(1000);
            }
        }, WATCHDOG_INTERVAL_MS);
    }

    _stopWatchdog() {
        if (this._watchdogTimer) {
            clearInterval(this._watchdogTimer);
            this._watchdogTimer = null;
        }
    }

    // ─── Reconnect with flat delay for fast recovery ────────────
    _scheduleReconnect(overrideDelayMs) {
        if (this._isDestroying) return;
        if (this.reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
            logErr(this.userId, `Max reconnect attempts (${MAX_RECONNECT_ATTEMPTS}) reached. Going to FAILED state.`);
            this._setState('FAILED');
            // Do NOT reset attempts or schedule another retry here.
            // The AutoHealer will handle FAILED clients after a cooling period.
            return;
        }

        const delay = overrideDelayMs ?? 1000; // Fast 1s delay for near-instant reconnection
        this.reconnectAttempts++;
        this._setState('RECONNECTING');

        log(this.userId, `Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})`);

        if (this._reconnectTimer) clearTimeout(this._reconnectTimer);
        this._reconnectTimer = setTimeout(() => {
            this._reconnectTimer = null;
            this.lastInitTime = 0;
            this.init();
        }, delay);
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
        // NOTE: do NOT reset reconnectAttempts here — it's managed by _scheduleReconnect
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

            if (state?.creds?.me?.id) {
                this.phoneNumber = state.creds.me.id.split(':')[0].split('@')[0];
            }

            if (!globalThis.__cachedBaileysVersion) {
                const verRes = await Promise.race([
                    fetchLatestBaileysVersion(),
                    new Promise(r => setTimeout(() => r({ version: [2, 3000, 1043857760] }), 1500))
                ]).catch(() => ({ version: [2, 3000, 1043857760] }));
                globalThis.__cachedBaileysVersion = verRes?.version || [2, 3000, 1043857760];
            }
            const version = globalThis.__cachedBaileysVersion;
            log(this.userId, `Using WhatsApp Web protocol version: ${version.join('.')}`);

            const createSocket = makeWASocket.default || makeWASocket;
            const sock = createSocket({
                version,
                auth: state,
                browser: Browsers.macOS('Desktop'),
                printQRInTerminal: false,
                logger: pino({ level: 'error' }),
                syncFullHistory: false,
                shouldSyncHistoryMessage: () => false,       // Flush event buffer immediately (0ms instead of 20s wait)
                markOnlineOnConnect: true,
                fireInitQueries: false,                      // Avoid 60s timeout on fetchProps init query
                connectTimeoutMs: 60_000,
                defaultQueryTimeoutMs: 60_000,
                keepAliveIntervalMs: 30_000,                 // 30s standard ping
                retryRequestDelayMs: 250,                    // Fast retry processing (was 5000ms = too slow)
                getMessage: async (key) => {
                    // Return the original cached message for proper re-encryption on retry
                    if (this.messageCache && key?.id && this.messageCache.has(key.id)) {
                        return this.messageCache.get(key.id);
                    }
                    // Fallback: empty proto to allow retry processing without phantom text
                    return proto.Message.fromObject({});
                }
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

            // ── Map WhatsApp Privacy LIDs to real phone numbers ──
            this._loadLidMapFromDisk();
            const indexContactLid = (c) => {
                if (!c) return;
                const lid = c.lid || (c.id && String(c.id).endsWith('@lid') ? c.id : '');
                const pn  = c.phoneNumber || c.pn || (c.id && String(c.id).endsWith('@s.whatsapp.net') ? c.id : '');
                if (lid && pn) {
                    const cleanPn = String(pn).replace(/:.*$/, '').replace(/@.*$/, '');
                    this.lidToPhoneMap.set(String(lid), cleanPn);
                    this.lidToPhoneMap.set(String(lid).replace(/:.*$/, '').replace(/@.*$/, ''), cleanPn);
                }
            };
            sock.ev.on('contacts.upsert', (contacts) => {
                try { (contacts || []).forEach(indexContactLid); this._saveLidMapToDisk(); } catch (_) {}
            });
            sock.ev.on('contacts.update', (updates) => {
                try { (updates || []).forEach(indexContactLid); this._saveLidMapToDisk(); } catch (_) {}
            });
            sock.ev.on('chats.upsert', (chats) => {
                try { (chats || []).forEach(indexContactLid); this._saveLidMapToDisk(); } catch (_) {}
            });

            // ── Cache messages for retries & Inbound Receipt Listener ──
            sock.ev.on('messages.upsert', async (upsert) => {
                try {
                    const msgs = upsert?.messages || upsert || [];
                    for (const msg of msgs) {
                        if (msg?.key?.id && msg?.message) {
                            this.messageCache.set(msg.key.id, msg.message);
                            // Evict oldest if cache grows too large
                            if (this.messageCache.size > 500) {
                                const oldestKey = this.messageCache.keys().next().value;
                                this.messageCache.delete(oldestKey);
                            }

                            // Auto-ingest customer receipt image if incoming from client (or sent by owner in self-chat)
                            const unwrapped = unwrapWAMessage(msg);
                            const isImg = unwrapped && (
                                unwrapped.imageMessage || 
                                (unwrapped.documentMessage?.mimetype && unwrapped.documentMessage.mimetype.startsWith('image/')) ||
                                (unwrapped.documentMessage?.fileName && /\.(jpg|jpeg|png|webp)$/i.test(unwrapped.documentMessage.fileName))
                            );

                            const jid = msg.key.remoteJid || '';
                            const isGroupOrBroadcast = jid.endsWith('@g.us') || jid === 'status@broadcast';
                            const imgCaption = unwrapped?.imageMessage?.caption || unwrapped?.documentMessage?.caption || '';
                            const isBotOwnVoucherImg = Boolean(
                                (msg.key?.id && this.processedWaMsgIds.has(msg.key.id)) ||
                                imgCaption.includes('منظومة مقاييس المحاسبية') ||
                                imgCaption.includes('سَـنَـد رَصِـيـد')
                            );
                            if (isImg && !isGroupOrBroadcast && !isBotOwnVoucherImg) {
                                // Cancel any pending statement-request timer if user sent PIN right before images
                                const prePin = this.pendingRoutingPinByJid.get(jid);
                                if (prePin && prePin.stmtTimer) {
                                    clearTimeout(prePin.stmtTimer);
                                    prePin.stmtTimer = null;
                                }
                                this._processIncomingWhatsAppReceipt(msg, sock, unwrapped).catch(e => {
                                    logErr(this.userId, 'Error handling incoming receipt:', e.message);
                                });
                            } else if (!isImg && unwrapped && jid && !isGroupOrBroadcast) {
                                const textMsg = (
                                    unwrapped.conversation ||
                                    unwrapped.extendedTextMessage?.text ||
                                    ''
                                ).trim();

                                // Avoid processing bot's own outgoing replies when msg.key.fromMe is true
                                const isBotOwnReply = (
                                    textMsg.includes('━━━━') ||
                                    textMsg.includes('منظومة مقاييس') ||
                                    /^[✅🏦📥⚠️🔔📊💰🔐🛡️🔒⏳⛔📋📄🏛️]/.test(textMsg)
                                );
                                const normalizedText = textMsg.replace(/[٠-٩۰-۹]/g, d => {
                                    const code = d.charCodeAt(0);
                                    if (code >= 0x0660 && code <= 0x0669) return String(code - 0x0660);
                                    if (code >= 0x06F0 && code <= 0x06F9) return String(code - 0x06F0);
                                    return d;
                                }).trim();

                                const hasStmtOrBalanceKw = (
                                    /(?:طلب\s*كشف|كشف\s*حساب|كشف\s*الحساب|كشف|حسابي|رصيدي|الرصيد|رصيد|معرفة\s*الرصيد|كم\s*رصيدي)/i.test(normalizedText) ||
                                    /^[\s#:\-_.,/()]*(\d{1,10}[\s#:\-_.,/()]*)?(?:ك|كاف|الكاف|حرف\s*ك|حرف\s*كاف|حرف\s*الكاف|ر|راء|الراء|حرف\s*ر|حرف\s*راء|حرف\s*الراء)([\s#:\-_.,/()]*\d{1,10})?[\s#:\-_.,/()]*$/i.test(normalizedText)
                                );
                                const isPinOrCodeFormat = /^(?:#|كود|باسورد|الباسورد|الرمز|حساب|سري|السري|باص|رمزي|pin|code|pass|\s)*\d{1,6}\s*$/i.test(normalizedText);
                                const allowFromMeQuery = msg.key.fromMe && !isBotOwnReply && textMsg.length <= 60 && (
                                    hasStmtOrBalanceKw || isPinOrCodeFormat
                                );

                                if (textMsg && textMsg.length <= 120 && (!msg.key.fromMe || allowFromMeQuery)) {
                                    const msgId = msg.key?.id || '';
                                    if (msgId && this.processedWaMsgIds.has(msgId)) {
                                        continue;
                                    }
                                    if (msgId) {
                                        this.processedWaMsgIds.add(msgId);
                                        if (this.processedWaMsgIds.size > 1000) {
                                            const first = this.processedWaMsgIds.values().next().value;
                                            this.processedWaMsgIds.delete(first);
                                        }
                                    }

                                    // Extract pure digits or prefixed routing password (e.g. "532", "#532", "باسورد 532", "كود 532", "حساب 532")
                                    let extractedPin = '';
                                    if (/^\d{1,10}$/.test(normalizedText)) {
                                        extractedPin = normalizedText;
                                    } else if (!hasStmtOrBalanceKw) {
                                        const mPin = normalizedText.match(/^(?:#|كود|باسورد|الباسورد|الرمز|حساب|سري|السري|باص|رمزي|pin|code|pass)\s*[:\-]?\s*(\d{1,10})$/i);
                                        if (mPin) extractedPin = mPin[1];
                                    }

                                    const pendingBatch = this.inboundBatchQueues.get(jid);
                                    const recentBatch = this.lastBatchByJid.get(jid);

                                    if (extractedPin) {
                                        const existingPre = this.pendingRoutingPinByJid.get(jid);
                                        if (existingPre && existingPre.stmtTimer) {
                                            clearTimeout(existingPre.stmtTimer);
                                        }
                                        const hasActivePendingBatch = Boolean(
                                            pendingBatch && (pendingBatch.items.length > 0 || pendingBatch.isProcessing || (pendingBatch.activeDownloads || 0) > 0)
                                        );
                                        const hasUnroutedRecentBatch = Boolean(
                                            !hasActivePendingBatch &&
                                            recentBatch &&
                                            !recentBatch.routedPin &&
                                            recentBatch.hasUnroutedItems &&
                                            (Date.now() - recentBatch.timestamp <= 25000) &&
                                            recentBatch.receiptIds.length > 0
                                        );

                                        let stmtTimer = null;
                                        if (!hasActivePendingBatch && !hasUnroutedRecentBatch) {
                                            stmtTimer = setTimeout(() => {
                                                const curBatch = this.inboundBatchQueues.get(jid);
                                                if (!curBatch || (curBatch.items.length === 0 && !curBatch.isProcessing && !(curBatch.activeDownloads > 0))) {
                                                    const curPre = this.pendingRoutingPinByJid.get(jid);
                                                    if (curPre) curPre.stmtCompleted = true;
                                                    this._processIncomingWhatsAppStatementRequest(msg, sock, textMsg).catch(e => {
                                                        logErr(this.userId, 'Error handling incoming statement request:', e.message);
                                                    });
                                                }
                                            }, 80);
                                        }

                                        // Store the bare PIN in pendingRoutingPinByJid so any images or follow-up "رصيد"/"كشف" inherit this PIN
                                        this.pendingRoutingPinByJid.set(jid, {
                                            pin: extractedPin,
                                            timestamp: Date.now(),
                                            fromBarePin: true,
                                            stmtCompleted: false,
                                            stmtTimer
                                        });
                                        log(this.userId, `🔑 Stored active routing PIN (${extractedPin}) for ${jid}`);

                                        if (hasActivePendingBatch) {
                                            pendingBatch.sharedPin = extractedPin;
                                            pendingBatch.followUpPin = extractedPin;
                                            for (const item of pendingBatch.items) {
                                                if (!item.caption) item.caption = extractedPin;
                                            }
                                            if (!pendingBatch.isProcessing && !(pendingBatch.activeDownloads > 0) && pendingBatch.timer) {
                                                clearTimeout(pendingBatch.timer);
                                                pendingBatch.timer = setTimeout(() => {
                                                    this._flushInboundReceiptBatch(jid).catch(err => {
                                                        logErr(this.userId, 'Batch flush error:', err.message);
                                                    });
                                                }, 150);
                                            }
                                            log(this.userId, `📎 Attached follow-up password (${extractedPin}) to active receipt batch for ${jid}`);
                                        } else if (hasUnroutedRecentBatch) {
                                            this._rerouteRecentBatchByCode(jid, extractedPin, recentBatch, msg, sock).catch(e => {
                                                logErr(this.userId, 'Error rerouting recent batch by code:', e.message);
                                            });
                                        }
                                    } else {
                                        // Message is a text command (e.g. "470 رصيد", "رصيد 470", "470 ر", "ر 470", "رصيد", "كشف", "ر", "ك")
                                        let effectiveTextMsg = textMsg;
                                        const inlineDigitsMatch = normalizedText.match(/(?<!\d)(\d{1,10})(?!\d)/);
                                        const existingPre = this.pendingRoutingPinByJid.get(jid);
                                        if (existingPre && existingPre.stmtTimer) {
                                            clearTimeout(existingPre.stmtTimer);
                                            existingPre.stmtTimer = null;
                                        }
                                        if (inlineDigitsMatch && hasStmtOrBalanceKw) {
                                            // Remember this explicit PIN for 45s in case the user sends bare "كشف حساب" or "رصيد" right after
                                            this.pendingRoutingPinByJid.set(jid, {
                                                pin: inlineDigitsMatch[1],
                                                timestamp: Date.now(),
                                                fromBarePin: true,
                                                stmtCompleted: true,
                                                stmtTimer: null
                                            });
                                        } else if (!inlineDigitsMatch && hasStmtOrBalanceKw && existingPre && existingPre.pin && existingPre.fromBarePin && (Date.now() - existingPre.timestamp <= 45000)) {
                                            // User entered an explicit PIN/Code within the last 45s and now sent bare "رصيد" or "كشف حساب"
                                            effectiveTextMsg = `${existingPre.pin} ${textMsg}`;
                                            existingPre.stmtCompleted = true;
                                            log(this.userId, `🔗 Combined active PIN (${existingPre.pin}) with follow-up command "${textMsg}" for ${jid}`);
                                        }
                                        this._processIncomingWhatsAppStatementRequest(msg, sock, effectiveTextMsg).catch(e => {
                                            logErr(this.userId, 'Error handling incoming statement request:', e.message);
                                        });
                                    }
                                }
                            }
                        }
                    }
                } catch (e) {
                    logErr(this.userId, 'messages.upsert cache error:', e.message);
                }
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
            this.reconnectAttempts = 0;  // Only reset on successful connection
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
                    log(this.userId, 'Disconnected with code 401 (Logged Out) — session invalidated. Cleaning auth and re-initializing for fresh QR.');
                    this.phoneNumber = null;
                    this.reconnectAttempts = 0;
                    // Delete corrupted/old auth and re-initializing to generate fresh QR
                    this._deleteAuthFolder();
                    this._setState('IDLE');
                    // Auto re-init after a short delay to generate fresh QR
                    setTimeout(() => this.init().catch(e => logErr(this.userId, 'Re-init after logout failed:', e.message)), 2000);
                    break;

                case DisconnectReason.restartRequired:
                    log(this.userId, 'Restart required — reconnecting immediately');
                    this._scheduleReconnect(1000);
                    break;

                case DisconnectReason.multideviceMismatch:
                    log(this.userId, 'Multi-device mismatch — requires manual re-link.');
                    this._setState('DISCONNECTED');
                    this.reconnectAttempts = 0;
                    // Do NOT auto-reconnect — multi-device mismatch requires user action.
                    break;

                case DisconnectReason.connectionReplaced:
                    log(this.userId, 'Connection replaced by another session. Going DISCONNECTED to prevent reconnection war.');
                    this._setState('DISCONNECTED');
                    this.reconnectAttempts = 0;
                    break;

                case DisconnectReason.connectionClosed:
                case DisconnectReason.connectionLost:
                case DisconnectReason.timedOut:
                default: {
                    // For fresh sessions (no phone number, no auth) failing repeatedly,
                    // stop after 5 attempts to avoid infinite loop when WA servers reject
                    const credsPath = path.join(this.authFolder, 'creds.json');
                    const isFreshSession = !this.phoneNumber && (!fs.existsSync(credsPath) || fs.statSync(credsPath).size < 100);
                    if (isFreshSession && this.reconnectAttempts >= 5) {
                        log(this.userId, 'Fresh session failed 5 times — stopping. User can retry manually.');
                        this._setState('FAILED');
                    } else {
                        this._scheduleReconnect();
                    }
                    break;
                }
        }
    }
    }

    _deleteAuthFolder() {
        try {
            if (fs.existsSync(this.authFolder)) {
                const trashFolder = this.authFolder + '_trash_' + Date.now();
                try {
                    fs.renameSync(this.authFolder, trashFolder);
                    log(this.userId, `Auth folder renamed to trash: ${trashFolder}`);
                    // Clean up trash asynchronously
                    setTimeout(() => {
                        try { fs.rmSync(trashFolder, { recursive: true, force: true }); } catch (_) {}
                    }, 5000);
                } catch (renameErr) {
                    logErr(this.userId, 'Rename failed, trying to delete/corrupt inner files');
                    const files = fs.readdirSync(this.authFolder);
                    for (const file of files) {
                        const filePath = path.join(this.authFolder, file);
                        try {
                            if (fs.statSync(filePath).isDirectory()) {
                                fs.rmSync(filePath, { recursive: true, force: true });
                            } else {
                                try { fs.unlinkSync(filePath); } 
                                catch (_) {
                                    try { fs.writeFileSync(filePath, 'INVALID'); } catch (_) {}
                                }
                            }
                        } catch (_) {}
                    }
                    try { fs.rmSync(this.authFolder, { recursive: true, force: true }); } catch (_) {}
                }
            }
        } catch (e) {
            logErr(this.userId, 'Failed to process auth folder:', e.message);
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

    _resolveSenderPhone(msg, sock) {
        const remoteJid = msg?.key?.remoteJid || '';
        const candidates = [
            msg?.key?.senderPn,
            msg?.key?.remoteJidAlt,
            msg?.key?.participantAlt,
            msg?.participant,
            this.lidToPhoneMap.get(remoteJid),
            this.lidToPhoneMap.get(remoteJid.replace(/@.*$/, '')),
            (!remoteJid.endsWith('@lid') ? remoteJid : '')
        ];
        for (const c of candidates) {
            if (c && !String(c).endsWith('@lid')) {
                const cleaned = String(c).replace(/:.*$/, '').replace(/@.*$/, '').replace(/\D/g, '');
                if (cleaned.length >= 9) {
                    if (remoteJid.endsWith('@lid')) {
                        const cleanLid = remoteJid.replace(/:.*$/, '').replace(/@.*$/, '');
                        if (this.lidToPhoneMap.get(remoteJid) !== cleaned) {
                            this.lidToPhoneMap.set(remoteJid, cleaned);
                            this.lidToPhoneMap.set(cleanLid, cleaned);
                            this._saveLidMapToDisk();
                        }
                    }
                    return cleaned;
                }
            }
        }
        return remoteJid.replace(/:.*$/, '').replace(/@.*$/, '');
    }

    async _processIncomingWhatsAppReceipt(msg, sock, unwrapped) {
        try {
            const remoteJid = msg.key.remoteJid || '';
            if (remoteJid.includes('@g.us') || remoteJid.includes('status@broadcast')) return;

            const msgId = msg.key?.id || '';
            if (msgId && this.processedWaMsgIds.has(msgId)) {
                return;
            }
            if (msgId) {
                this.processedWaMsgIds.add(msgId);
                if (this.processedWaMsgIds.size > 1000) {
                    const first = this.processedWaMsgIds.values().next().value;
                    this.processedWaMsgIds.delete(first);
                }
            }

            const senderPhone = this._resolveSenderPhone(msg, sock);
            const pushName = msg.pushName || ('عميل واتساب ' + senderPhone);
            const tenantId = this.userId.replace(/\D/g, '') || '12';

            log(this.userId, `📥 Incoming WhatsApp receipt image from ${pushName} (${senderPhone}), downloading...`);

            const unwrappedContent = unwrapped || unwrapWAMessage(msg) || msg.message;
            const caption = unwrappedContent?.imageMessage?.caption || unwrappedContent?.documentMessage?.caption || '';

            // Initialize batchState BEFORE media download so concurrent images in a multi-image burst are synchronized
            let batchState = this.inboundBatchQueues.get(remoteJid);
            if (!batchState) {
                batchState = {
                    items: [],
                    timer: null,
                    isProcessing: false,
                    activeDownloads: 0,
                    ackSent: false,
                    sharedPin: '',
                    followUpPin: ''
                };
                this.inboundBatchQueues.set(remoteJid, batchState);
            }

            // Capture any active pre-routing PIN immediately (before download finishes) and refresh its 180s window
            const preRoutingEarly = this.pendingRoutingPinByJid.get(remoteJid);
            let earlyPin = '';
            if (preRoutingEarly && preRoutingEarly.pin && (Date.now() - preRoutingEarly.timestamp <= 180000)) {
                if (preRoutingEarly.stmtTimer) {
                    clearTimeout(preRoutingEarly.stmtTimer);
                    preRoutingEarly.stmtTimer = null;
                }
                preRoutingEarly.timestamp = Date.now();
                earlyPin = preRoutingEarly.pin;
                batchState.sharedPin = earlyPin;
            }
            if (caption && caption.trim()) {
                earlyPin = caption.trim();
                batchState.sharedPin = earlyPin;
                this.pendingRoutingPinByJid.set(remoteJid, {
                    pin: earlyPin,
                    timestamp: Date.now(),
                    stmtTimer: null
                });
            }

            batchState.activeDownloads = (batchState.activeDownloads || 0) + 1;

            const messageToDownload = {
                key: msg.key,
                message: unwrappedContent
            };

            let buffer = null;
            try {
                try {
                    buffer = await downloadMediaMessage(messageToDownload, 'buffer', {}, {
                        logger: pino({ level: 'silent' }),
                        reuploadRequest: sock.updateMediaMessage
                    });
                } catch (dlErr) {
                    try {
                        buffer = await downloadMediaMessage(msg, 'buffer', {}, {
                            logger: pino({ level: 'silent' }),
                            reuploadRequest: sock.updateMediaMessage
                        });
                    } catch (dlErr2) {
                        logErr(this.userId, 'Buffer download failed:', dlErr2.message);
                    }
                }
            } finally {
                batchState.activeDownloads = Math.max(0, (batchState.activeDownloads || 1) - 1);
            }

            if (!buffer || buffer.length === 0) {
                if (batchState.activeDownloads === 0 && batchState.items.length > 0 && !batchState.isProcessing) {
                    if (batchState.timer) clearTimeout(batchState.timer);
                    batchState.timer = setTimeout(() => {
                        this._flushInboundReceiptBatch(remoteJid).catch(() => {});
                    }, 800);
                }
                return;
            }

            const targetDir = path.join(UPLOADS_DIR, 'receipts_inbox', tenantId);
            if (!fs.existsSync(targetDir)) {
                fs.mkdirSync(targetDir, { recursive: true });
            }

            const filename = `bankak_wa_${Date.now()}_${Math.floor(Math.random() * 10000)}.jpg`;
            const filePath = path.join(targetDir, filename);
            fs.writeFileSync(filePath, buffer);

            const relativePath = `uploads/receipts_inbox/${tenantId}/${filename}`;

            // Re-check if user sent a routing password while this image was downloading
            const preRoutingNow = this.pendingRoutingPinByJid.get(remoteJid);
            if (preRoutingNow && preRoutingNow.pin && (Date.now() - preRoutingNow.timestamp <= 180000)) {
                if (preRoutingNow.stmtTimer) {
                    clearTimeout(preRoutingNow.stmtTimer);
                    preRoutingNow.stmtTimer = null;
                }
                preRoutingNow.timestamp = Date.now();
                batchState.sharedPin = preRoutingNow.pin;
            }

            const finalItemCaption = (caption && caption.trim()) ? caption.trim() : (batchState.followUpPin || batchState.sharedPin || earlyPin || '');

            batchState.items.push({
                msg,
                sock,
                remoteJid,
                senderPhone,
                pushName,
                tenantId,
                relativePath,
                caption: finalItemCaption
            });

            // 1. Send immediate acknowledgment reply once per batch right after receiving the first receipt image (non-blocking so OCR starts immediately!)
            if (!batchState.ackSent) {
                batchState.ackSent = true;
                sock.sendMessage(remoteJid, {
                    text: `✅ مرحباً بك ${pushName}!\nتم استلام إشعار التحويل البنكي بنجاح وجاري تدقيقه الفوري واعتماده في النظام المالي.\nشكراً لتعاملكم معنا. 🏢✨`
                }, { quoted: msg }).then((ackMsg) => {
                    if (ackMsg?.key?.id) {
                        this.processedWaMsgIds.add(ackMsg.key.id);
                    }
                    log(this.userId, `📤 Immediate receipt acknowledgment sent to customer ${senderPhone}`);
                }).catch((ackErr) => {
                    logErr(this.userId, 'Failed to send immediate acknowledgment:', ackErr.message);
                });
            }

            if (batchState.timer) {
                clearTimeout(batchState.timer);
            }
            const flushDelayMs = (batchState.activeDownloads || 0) > 0 ? 350 : 60;
            batchState.timer = setTimeout(() => {
                this._flushInboundReceiptBatch(remoteJid).catch(err => {
                    logErr(this.userId, 'Batch flush error:', err.message);
                });
            }, flushDelayMs);
        } catch (err) {
            logErr(this.userId, 'Failed to queue WhatsApp receipt image:', err.message);
        }
    }

    async _flushInboundReceiptBatch(remoteJid) {
        const batchState = this.inboundBatchQueues.get(remoteJid);
        if (!batchState || batchState.isProcessing || batchState.items.length === 0) return;

        // If other images in the same multi-image burst are still downloading, wait for them so the whole batch flushes together
        if ((batchState.activeDownloads || 0) > 0) {
            if (batchState.timer) clearTimeout(batchState.timer);
            batchState.timer = setTimeout(() => {
                this._flushInboundReceiptBatch(remoteJid).catch(err => {
                    logErr(this.userId, 'Batch flush error:', err.message);
                });
            }, 300);
            return;
        }

        batchState.isProcessing = true;
        const currentBatch = batchState.items.splice(0, batchState.items.length);
        batchState.ackSent = false;

        // Propagate password/caption across all images in the batch whether written on first image, last image, or before/after
        let sharedBatchCaption = batchState.followUpPin || batchState.sharedPin || '';
        const preRouting = this.pendingRoutingPinByJid.get(remoteJid);
        if (!sharedBatchCaption && preRouting && preRouting.pin && (Date.now() - preRouting.timestamp <= 60000)) {
            sharedBatchCaption = preRouting.pin;
        }
        for (const item of currentBatch) {
            if (item.caption && item.caption.trim()) {
                sharedBatchCaption = item.caption.trim();
                break;
            }
        }
        if (sharedBatchCaption) {
            for (const item of currentBatch) {
                if (!item.caption || !item.caption.trim()) {
                    item.caption = sharedBatchCaption;
                }
            }
            this.pendingRoutingPinByJid.set(remoteJid, {
                pin: sharedBatchCaption,
                timestamp: Date.now(),
                fromBarePin: true,
                stmtCompleted: true,
                stmtTimer: null
            });
        }
        batchState.sharedPin = '';
        batchState.followUpPin = '';

        const firstItem = currentBatch[0];
        const sock = firstItem.sock;
        const tenantId = firstItem.tenantId;
        const isWin = process.platform === 'win32';
        const candidateEndpoints = isWin
            ? [
                `http://127.0.0.1/ELSAJANA2/magaies/api/inbound_receipts.php?action=upload&tenant_id=${tenantId}`,
                `http://127.0.0.1/magaees/api/inbound_receipts.php?action=upload&tenant_id=${tenantId}`,
                `http://127.0.0.1/api/inbound_receipts.php?action=upload&tenant_id=${tenantId}`
            ]
            : [
                `http://127.0.0.1/api/inbound_receipts.php?action=upload&tenant_id=${tenantId}`,
                `https://c.elsajana.com/api/inbound_receipts.php?action=upload&tenant_id=${tenantId}`
            ];

        const postedReceipts = [];
        const reviewHoldReceipts = [];
        const duplicateReceipts = [];
        const invalidReceipts = [];
        const allProcessedIds = [];
        const unroutedProcessedIds = [];
        let resolvedCustomerName = firstItem.pushName;
        let resolvedBankName = 'بنك الخرطوم (بنكك)';

        // Process each receipt in the batch strictly sequentially (non-duplicated, no forgery check)
        for (const item of currentBatch) {
            try {
                const activeCaption = batchState.followUpPin || item.caption || sharedBatchCaption || '';
                const payload = {
                    action: 'upload',
                    tenant_id: item.tenantId,
                    customer_name: item.pushName,
                    customer_phone: item.senderPhone,
                    receipt_image: item.relativePath,
                    caption: activeCaption,
                    bank_name: '',
                    source: 'whatsapp',
                    notes: activeCaption ? `إشعار تحويل وارد من واتساب (${activeCaption})` : 'إشعار تحويل وارد تلقائياً عبر محادثة واتساب'
                };

                let data = null;
                let lastErr = null;
                for (const ep of candidateEndpoints) {
                    try {
                        const response = await fetch(ep, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        if (response.ok) {
                            const parsed = await response.json();
                            if (parsed && parsed.success) {
                                data = parsed;
                                break;
                            }
                        } else {
                            lastErr = new Error(`HTTP ${response.status} from ${ep}`);
                        }
                    } catch (postErr) {
                        lastErr = postErr;
                    }
                }

                if (!data) {
                    throw lastErr || new Error('All inbound_receipts endpoints failed');
                }

                if (data.customer_name) {
                    resolvedCustomerName = data.customer_name;
                }
                if (data.bank_name) {
                    resolvedBankName = data.bank_name;
                }
                if (data.id) {
                    allProcessedIds.push(data.id);
                    if (!activeCaption) {
                        unroutedProcessedIds.push(data.id);
                    }
                }

                log(this.userId, `✅ Batch item processed (ID: ${data.id}, Status: ${data.status}, Mode: ${data.approval_mode || 'auto_post'}, Voucher: ${data.voucher_no || 'N/A'}, Ref: ${data.transfer_no || 'N/A'})`);

                if (data.status === 'duplicate_rejected') {
                    duplicateReceipts.push(data);
                } else if (data.approval_mode === 'review_first' && Number(data.amount) > 0) {
                    reviewHoldReceipts.push(data);
                } else if (data.voucher_no && Number(data.amount) > 0) {
                    postedReceipts.push(data);
                } else {
                    invalidReceipts.push(data);
                }
            } catch (itemErr) {
                logErr(this.userId, 'Failed processing batch item:', itemErr.message);
            }
        }

        // Save batch metadata so if sender sends the password a few seconds AFTER an unrouted batch finished, we can still route all of them
        this.lastBatchByJid.set(remoteJid, {
            receiptIds: allProcessedIds,
            hasUnroutedItems: unroutedProcessedIds.length > 0,
            timestamp: Date.now(),
            routedPin: batchState.followUpPin || sharedBatchCaption || '',
            tenantId
        });

        // If user sent a followUpPin mid-flight while earlier items had no caption, reroute those earlier items now
        if (batchState.followUpPin && unroutedProcessedIds.length > 0) {
            const pinToApply = batchState.followUpPin;
            batchState.followUpPin = '';
            await this._rerouteRecentBatchByCode(remoteJid, pinToApply, {
                receiptIds: allProcessedIds,
                timestamp: Date.now(),
                routedPin: '',
                tenantId
            }, firstItem.msg, sock);
            batchState.isProcessing = false;
            if (batchState.items.length > 0) {
                this._flushInboundReceiptBatch(remoteJid).catch(() => {});
            }
            return;
        }

        // 2. Send consolidated confirmation reply to the customer
        try {
            const replyParts = [];
            if (postedReceipts.length === 1) {
                const p = postedReceipts[0];
                const bName = p.bank_name || resolvedBankName;
                replyParts.push(
                    `🏦 تم فحص الإشعار وتنزيله في حسابكم (${resolvedCustomerName}) بنجاح:\n` +
                    `• المبلغ: ${Number(p.amount).toLocaleString()} SDG\n` +
                    `• رقم العملية: ${p.transfer_no || '-'}\n` +
                    `• إلى حساب: ${bName}\n` +
                    `• رقم سند القبض: ${p.voucher_no}`
                );
            } else if (postedReceipts.length > 1) {
                const totalAmt = postedReceipts.reduce((s, r) => s + (Number(r.amount) || 0), 0);
                const lines = postedReceipts.map((p, idx) =>
                    `${idx + 1}. مبلغ *${Number(p.amount).toLocaleString()} SDG* | عملية *${p.transfer_no}* | سند *${p.voucher_no}*`
                ).join('\n');
                replyParts.push(
                    `🏦 تم فحص وإضافة (*${postedReceipts.length}*) إشعارات بنكية وتنزيلها في حسابكم (*${resolvedCustomerName}*) إلى حساب *${resolvedBankName}* بنجاح:\n` +
                    `${lines}\n` +
                    `💰 *إجمالي المبلغ المضاف:* ${totalAmt.toLocaleString()} SDG`
                );
            }

            if (reviewHoldReceipts.length === 1) {
                const r = reviewHoldReceipts[0];
                replyParts.push(
                    `📥 تم استلام وفحص الإشعار البنكي لحساب (${resolvedCustomerName}) بنجاح:\n` +
                    `• المبلغ: ${Number(r.amount).toLocaleString()} SDG\n` +
                    `• رقم العملية: ${r.transfer_no || '-'}\n` +
                    `• الحالة: بانتظار التمرير والاعتماد النهائي من الإدارة المالية (${r.voucher_no || ('#' + r.id)})`
                );
            } else if (reviewHoldReceipts.length > 1) {
                const totalHold = reviewHoldReceipts.reduce((s, r) => s + (Number(r.amount) || 0), 0);
                replyParts.push(
                    `📥 تم استلام وفحص (*${reviewHoldReceipts.length}*) إشعارات بنكية لحساب (*${resolvedCustomerName}*) بإجمالي *${totalHold.toLocaleString()} SDG* وهي الآن جاهزة للتمرير والاعتماد النهائي لدى الإدارة المالية.`
                );
            }

            if (duplicateReceipts.length > 0) {
                const dupRefs = duplicateReceipts.map(d => d.transfer_no || `#${d.id}`).join('، ');
                replyParts.push(`⚠️ إشعارات مكررة لم يتم إضافتها (${duplicateReceipts.length}): رقم العملية (${dupRefs}) مسجل ومعتمد مسبقاً.`);
            }

            if (invalidReceipts.length > 0) {
                replyParts.push(`⚠️ عدد (${invalidReceipts.length}) إشعار قيد المراجعة اليدوية (رقم التحويل أقل من 6 أرقام أو غير واضح).`);
            }

            if (replyParts.length > 0) {
                replyParts.push('شكراً لتعاملكم معنا. ✨');
                await sock.sendMessage(remoteJid, { text: replyParts.join('\n\n') }, { quoted: firstItem.msg });
            }
        } catch (replyErr) {
            logErr(this.userId, 'Failed to send batch confirmation to customer:', replyErr.message);
        }

        // 3. Send System Admin Notification
        const validAdminReceipts = [...postedReceipts, ...reviewHoldReceipts];
        if (validAdminReceipts.length > 0) {
            try {
                const totalAmt = validAdminReceipts.reduce((s, r) => s + (Number(r.amount) || 0), 0);
                const isReviewHold = reviewHoldReceipts.length > 0 && postedReceipts.length === 0;
                const detailsLines = validAdminReceipts.map((p, idx) =>
                    `${p.approval_mode === 'review_first' ? '⏳' : '✅'} إشعار #${idx + 1}: مبلغ *${Number(p.amount).toLocaleString()} SDG* | رقم العملية: *${p.transfer_no}* | سند: *${p.voucher_no || ('#' + p.id)}*`
                ).join('\n');

                const adminMsg =
                    `🔔 *إشعار لمدير النظام المالي*\n` +
                    (isReviewHold
                        ? `تم استلام وفحص عدد (*${validAdminReceipts.length}*) إشعار بنكي لحساب: *${resolvedCustomerName}* (بانتظار التمرير في شاشة الترحيل / صينية الاستلام)\n`
                        : `تم فحص وإضافة عدد (*${validAdminReceipts.length}*) إشعار بنكي تلقائياً في حساب: *${resolvedCustomerName}*\n`) +
                    `إلى حساب: *${resolvedBankName}*\n` +
                    `━━━━━━━━━━━━━━\n` +
                    `${detailsLines}\n` +
                    `━━━━━━━━━━━━━━\n` +
                    `💰 *إجمالي المبلغ:* ${totalAmt.toLocaleString()} SDG\n` +
                    (isReviewHold
                        ? `👁️ الإشعارات جاهزة للمراجعة والتمرير في شاشة الترحيل وصينية الإشعارات.`
                        : `📒 تم الحفظ والترحيل أوتوماتيكياً في كشف حساب (${resolvedCustomerName}).`);

                const adminPhoneClean = (this.phoneNumber || '249123005622').replace(/\D/g, '');
                if (adminPhoneClean) {
                    const adminJid = `${adminPhoneClean}@s.whatsapp.net`;
                    await sock.sendMessage(adminJid, { text: adminMsg });
                    log(this.userId, `📤 System Admin notification sent to ${adminJid} for ${validAdminReceipts.length} receipt(s)`);
                }
            } catch (adminErr) {
                logErr(this.userId, 'Failed to send System Admin WhatsApp notification:', adminErr.message);
            }
        }

        batchState.isProcessing = false;
        if (batchState.items.length > 0) {
            this._flushInboundReceiptBatch(remoteJid).catch(() => {});
        }
    }

    async _rerouteRecentBatchByCode(remoteJid, pinCode, recentBatch, msg, sock) {
        if (!recentBatch || !Array.isArray(recentBatch.receiptIds) || recentBatch.receiptIds.length === 0) return;
        // If already routed with this exact PIN, no need to re-post; just acknowledge
        if (recentBatch.routedPin && String(recentBatch.routedPin).trim() === String(pinCode).trim()) {
            log(this.userId, `ℹ️ Recent batch for ${remoteJid} was already routed using PIN ${pinCode}`);
            return;
        }
        recentBatch.routedPin = String(pinCode).trim();
        const tenantId = recentBatch.tenantId || this.userId.replace(/\D/g, '') || '12';
        const isWin = process.platform === 'win32';
        const candidateEndpoints = isWin
            ? [
                `http://127.0.0.1/ELSAJANA2/magaies/api/inbound_receipts.php?action=assign_batch_by_code&tenant_id=${tenantId}`,
                `http://127.0.0.1/magaees/api/inbound_receipts.php?action=assign_batch_by_code&tenant_id=${tenantId}`,
                `http://127.0.0.1/api/inbound_receipts.php?action=assign_batch_by_code&tenant_id=${tenantId}`
            ]
            : [
                `http://127.0.0.1/api/inbound_receipts.php?action=assign_batch_by_code&tenant_id=${tenantId}`,
                `https://c.elsajana.com/api/inbound_receipts.php?action=assign_batch_by_code&tenant_id=${tenantId}`
            ];

        for (const ep of candidateEndpoints) {
            try {
                const res = await fetch(ep, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'assign_batch_by_code',
                        tenant_id: tenantId,
                        receipt_ids: recentBatch.receiptIds,
                        customer_code: String(pinCode).trim()
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.success) {
                        await sock.sendMessage(remoteJid, {
                            text: `✅ تم اعتماد الباسورد (${pinCode}) وتوجيه عدد (*${data.updated_count || recentBatch.receiptIds.length}*) إشعار بنكي إلى حساب: *${data.customer_name}* (${data.bank_name || 'بنك الخرطوم'}) بنجاح! ✨`
                        }, { quoted: msg });
                        log(this.userId, `🔀 Rerouted ${recentBatch.receiptIds.length} receipt(s) to ${data.customer_name} via follow-up PIN ${pinCode}`);
                        return;
                    }
                }
            } catch (_) {}
        }
    }

    async _processIncomingWhatsAppStatementRequest(msg, sock, textMsg) {
        try {
            const remoteJid = msg.key.remoteJid || '';
            if (!remoteJid || remoteJid === 'status@broadcast' || remoteJid.endsWith('@g.us')) {
                return;
            }

            const senderPhone = this._resolveSenderPhone(msg, sock);
            const pushName = msg.pushName || (senderPhone ? `عميل واتساب (${senderPhone})` : 'عميل واتساب');
            const tenantMatch = String(this.userId).match(/\d+/);
            const tenantId = tenantMatch ? tenantMatch[0] : '12';

            const isWin = process.platform === 'win32';
            const candidateEndpoints = isWin
                ? [
                    `http://127.0.0.1/ELSAJANA2/magaies/api/inbound_receipts.php?action=whatsapp_statement_request&tenant_id=${tenantId}`,
                    `http://127.0.0.1/magaees/api/inbound_receipts.php?action=whatsapp_statement_request&tenant_id=${tenantId}`,
                    `http://127.0.0.1/api/inbound_receipts.php?action=whatsapp_statement_request&tenant_id=${tenantId}`
                ]
                : [
                    `http://127.0.0.1/api/inbound_receipts.php?action=whatsapp_statement_request&tenant_id=${tenantId}`,
                    `https://c.elsajana.com/api/inbound_receipts.php?action=whatsapp_statement_request&tenant_id=${tenantId}`
                ];

            const payload = {
                action: 'whatsapp_statement_request',
                tenant_id: tenantId,
                message_text: textMsg,
                sender_phone: senderPhone,
                sender_name: pushName
            };

            log(this.userId, `💬 Processing WhatsApp statement/balance query "${textMsg}" from ${pushName} (${senderPhone})`);

            let data = null;
            for (const ep of candidateEndpoints) {
                try {
                    const response = await fetch(ep, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (response.ok) {
                        const parsed = await response.json();
                        if (parsed && parsed.success) {
                            data = parsed;
                            break;
                        }
                    }
                } catch (_) {}
            }

            if (data && data.matched && data.reply_text) {
                // Send text response IMMEDIATELY (<100ms) for both 'رصيد' and 'كشف حساب' so there is zero delay
                const sentMsg = await sock.sendMessage(remoteJid, { text: data.reply_text }, { quoted: msg });
                if (sentMsg?.key?.id) {
                    this.processedWaMsgIds.add(sentMsg.key.id);
                }
                log(
                    this.userId,
                    `📊 Statement request handled instantly for ${data.entity_type} "${data.entity_name}" (#${data.code_num}) [allowed=${Boolean(data.allowed)}, mode=${data.access_mode || 'default'}]`
                );

                // If balance_only, also render and send the visual luxury voucher card asynchronously in background
                if (data.allowed && data.voucher_data && data.render_mode === 'balance_only') {
                    this._renderBalanceVoucherImage(data.voucher_data).then(async (voucherPng) => {
                        if (voucherPng && voucherPng.length > 1000) {
                            const imgMsg = await sock.sendMessage(
                                remoteJid,
                                { image: voucherPng, caption: `📜 *بطاقة سند الرصيد المعتمد — ${data.entity_name}*` },
                                { quoted: msg }
                            );
                            if (imgMsg?.key?.id) {
                                this.processedWaMsgIds.add(imgMsg.key.id);
                            }
                            log(this.userId, `🖼️ Sent luxury dual-currency balance voucher card (${voucherPng.length} bytes) to ${remoteJid}`);
                        }
                    }).catch((imgErr) => {
                        logErr(this.userId, 'Async voucher image render skipped:', imgErr.message);
                    });
                }

                if (data.admin_notification && sock.user && sock.user.id) {
                    try {
                        const selfDigits = String(sock.user.id).split(':')[0].split('@')[0].replace(/\D/g, '');
                        if (selfDigits) {
                            const selfJid = `${selfDigits}@s.whatsapp.net`;
                            if (selfJid !== remoteJid) {
                                const sentAdmin = await sock.sendMessage(selfJid, { text: data.admin_notification });
                                if (sentAdmin?.key?.id) {
                                    this.processedWaMsgIds.add(sentAdmin.key.id);
                                }
                            }
                        }
                    } catch (_) {}
                }
            } else {
                log(this.userId, `ℹ️ Text message "${textMsg}" from ${senderPhone} did not match any statement/balance trigger.`);
            }
        } catch (err) {
            logErr(this.userId, 'Failed processing WhatsApp statement request:', err.message);
        }
    }

    async _getVoucherBrowser() {
        if (globalThis.__magaieesVoucherBrowser && globalThis.__magaieesVoucherBrowser.connected) {
            return globalThis.__magaieesVoucherBrowser;
        }
        if (globalThis.__magaieesVoucherBrowserPromise) {
            return globalThis.__magaieesVoucherBrowserPromise;
        }
        globalThis.__magaieesVoucherBrowserPromise = (async () => {
            try {
                if (process.platform !== 'win32' && !process.env.PUPPETEER_CACHE_DIR) {
                    process.env.PUPPETEER_CACHE_DIR = path.join(__dirname, '.puppeteer_cache');
                }
                let puppeteerMod;
                try {
                    puppeteerMod = await import('puppeteer');
                } catch (_) {
                    try {
                        puppeteerMod = await import('puppeteer-core');
                    } catch (__) {
                        const { exec } = await import('child_process');
                        const cacheDir = path.join(__dirname, '.puppeteer_cache');
                        await new Promise((resolve, reject) => {
                            exec(
                                `HOME=/tmp PUPPETEER_CACHE_DIR="${cacheDir}" npm install puppeteer --no-save --legacy-peer-deps`,
                                { cwd: __dirname, timeout: 180000 },
                                (err) => (err ? reject(err) : resolve())
                            );
                        });
                        puppeteerMod = await import('puppeteer');
                    }
                }
                const puppeteer = puppeteerMod.default || puppeteerMod;
                const launchOpts = {
                    headless: 'new',
                    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
                };
                if (process.platform !== 'win32' && !process.env.PUPPETEER_EXECUTABLE_PATH) {
                    const linuxChromeCandidates = [
                        '/usr/bin/google-chrome-stable',
                        '/usr/bin/google-chrome',
                        '/usr/bin/chromium-browser',
                        '/usr/bin/chromium',
                        '/snap/bin/chromium'
                    ];
                    const findCachedChrome = (baseDir) => {
                        try {
                            if (!fs.existsSync(baseDir)) return null;
                            const walk = (dir, depth = 0) => {
                                if (depth > 5) return null;
                                for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
                                    const full = path.join(dir, entry.name);
                                    if (entry.isFile() && (entry.name === 'chrome' || entry.name === 'chromium')) {
                                        return full;
                                    }
                                    if (entry.isDirectory()) {
                                        const found = walk(full, depth + 1);
                                        if (found) return found;
                                    }
                                }
                                return null;
                            };
                            return walk(baseDir);
                        } catch (_) {
                            return null;
                        }
                    };
                    for (const cand of linuxChromeCandidates) {
                        if (fs.existsSync(cand)) {
                            launchOpts.executablePath = cand;
                            break;
                        }
                    }
                    if (!launchOpts.executablePath) {
                        const cached =
                            findCachedChrome(path.join(__dirname, '.puppeteer_cache')) ||
                            findCachedChrome('/root/.cache/puppeteer') ||
                            findCachedChrome('/var/www/.cache/puppeteer') ||
                            findCachedChrome('/tmp/.cache/puppeteer');
                        if (cached) {
                            launchOpts.executablePath = cached;
                        }
                    }
                }
                const browser = await puppeteer.launch(launchOpts);
                browser.on('disconnected', () => {
                    globalThis.__magaieesVoucherBrowser = null;
                    globalThis.__magaieesVoucherBrowserPromise = null;
                    globalThis.__magaieesVoucherWarmPage = null;
                    globalThis.__magaieesVoucherWarmPageBusy = false;
                });
                try {
                    const warmPage = await browser.newPage();
                    await warmPage.setViewport({ width: 1080, height: 1650, deviceScaleFactor: 1.0 });
                    globalThis.__magaieesVoucherWarmPage = warmPage;
                    globalThis.__magaieesVoucherWarmPageBusy = false;
                } catch (_) {}
                globalThis.__magaieesVoucherBrowser = browser;
                globalThis.__magaieesVoucherBrowserPromise = null;
                return browser;
            } catch (err) {
                globalThis.__magaieesVoucherBrowserPromise = null;
                throw err;
            }
        })();
        return globalThis.__magaieesVoucherBrowserPromise;
    }

    async _renderBalanceVoucherImage(v) {
        const esc = (s) => String(s ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        const fmtWhole = (val, fallbackNum) => {
            const raw = (fallbackNum !== undefined && fallbackNum !== null && fallbackNum !== '')
                ? Number(fallbackNum)
                : Number(String(val ?? '0').replace(/,/g, ''));
            if (!Number.isFinite(raw)) return String(val ?? '0');
            return Math.round(raw).toLocaleString('en-US', { maximumFractionDigits: 0 });
        };
        const fmtOneDec = (val, fallbackNum) => {
            const raw = (fallbackNum !== undefined && fallbackNum !== null && fallbackNum !== '')
                ? Number(fallbackNum)
                : Number(String(val ?? '0').replace(/,/g, ''));
            if (!Number.isFinite(raw)) return String(val ?? '0.0');
            return (Math.round(raw * 10) / 10).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        };

        const netLocalFmt = fmtWhole(v.net_balance_local_fmt, v.net_balance_local);
        const totalDebitLocalFmt = fmtWhole(v.total_debit_local_fmt, v.total_debit_local);
        const totalCreditLocalFmt = fmtWhole(v.total_credit_local_fmt, v.total_credit_local);
        const netUsdFmt = fmtOneDec(v.net_balance_usd_fmt, v.net_balance_usd);
        const totalDebitUsdFmt = fmtOneDec(v.total_debit_usd_fmt, v.total_debit_usd);
        const totalCreditUsdFmt = fmtOneDec(v.total_credit_usd_fmt, v.total_credit_usd);

        const statusType = v.status_type || 'settled'; // 'debit' | 'credit' | 'settled'
        const statusLabelAr = statusType === 'debit'
            ? 'مدين عليه'
            : statusType === 'credit'
            ? 'دائن له'
            : (String(v.status_label_ar || 'مسدد بالكامل').replace(/^رصيد\s+/u, '').replace(/[()]/g, '').trim() || 'مسدد بالكامل');

        const statusColors = statusType === 'debit'
            ? {
                badgeBg: 'linear-gradient(135deg, rgba(220, 38, 38, 0.36), rgba(127, 29, 29, 0.85))',
                badgeBorder: '#ef4444',
                badgeText: '#ff6b6b',
                accentGlow: 'rgba(239, 68, 68, 0.45)',
                dotColor: '#ef4444'
            }
            : statusType === 'credit'
            ? {
                badgeBg: 'linear-gradient(135deg, rgba(16, 185, 129, 0.34), rgba(6, 78, 59, 0.85))',
                badgeBorder: '#22c55e',
                badgeText: '#4ade80',
                accentGlow: 'rgba(34, 197, 94, 0.45)',
                dotColor: '#22c55e'
            }
            : {
                badgeBg: 'linear-gradient(135deg, rgba(56, 189, 248, 0.34), rgba(3, 105, 161, 0.78))',
                badgeBorder: '#38bdf8',
                badgeText: '#7dd3fc',
                accentGlow: 'rgba(56, 189, 248, 0.45)',
                dotColor: '#38bdf8'
            };

        // Load official colorful Magaiees "M" logo from assets/logo.png (cached in memory for instant reuse)
        if (globalThis.__magaieesLogoDataUri === undefined) {
            globalThis.__magaieesLogoDataUri = '';
            try {
                const logoPath = path.join(__dirname, 'assets', 'logo.png');
                if (fs.existsSync(logoPath)) {
                    globalThis.__magaieesLogoDataUri = 'data:image/png;base64,' + fs.readFileSync(logoPath).toString('base64');
                }
            } catch (_) {}
        }
        const magaieesLogoDataUri = globalThis.__magaieesLogoDataUri;

        const logoHtml = magaieesLogoDataUri
            ? `<img src="${magaieesLogoDataUri}" alt="MAGAIEES M Logo" class="m-logo-img" />`
            : `<svg viewBox="0 0 120 120" width="92" height="92" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="mRed" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#991b1b"/>
                    <stop offset="100%" stop-color="#f43f5e"/>
                  </linearGradient>
                  <linearGradient id="mYellowOrange" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#fde047"/>
                    <stop offset="55%" stop-color="#fb923c"/>
                    <stop offset="100%" stop-color="#ea580c"/>
                  </linearGradient>
                  <linearGradient id="mBlueNavy" x1="1" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#0ea5e9"/>
                    <stop offset="55%" stop-color="#2563eb"/>
                    <stop offset="100%" stop-color="#312e81"/>
                  </linearGradient>
                  <linearGradient id="mNavyRight" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2563eb"/>
                    <stop offset="100%" stop-color="#0284c7"/>
                  </linearGradient>
                </defs>
                <polygon points="10,12 10,108 46,108" fill="url(#mRed)"/>
                <polygon points="110,12 110,108 74,108" fill="url(#mNavyRight)"/>
                <polygon points="10,12 42,12 74,108 46,108" fill="url(#mYellowOrange)"/>
                <polygon points="78,12 110,12 74,108 46,108" fill="url(#mBlueNavy)"/>
              </svg>`;

        const rawDateTime = String(v.issue_datetime || '').trim();
        const dtParts = rawDateTime.split(/\s+/);
        const issueDate = v.issue_date || dtParts[0] || '';
        const issueTime = v.issue_time || dtParts[1] || rawDateTime;

        const companyName = String(v.company_name || '').trim();
        const stampText = String(v.stamp_text || '').trim() || 'ختم مصادقة مالية إلكترونية';
        const rawStampImage = String(v.stamp_image || '').trim();
        const stampImage = rawStampImage.length >= 150 ? rawStampImage : '';
        const localUnitFull = String(v.currency_full_ar || '').trim() || 'جنيه سوداني';
        const tafqeetLocal = String(v.tafqeet_ar || '').trim() || `فقط صفر ${localUnitFull}`;
        const tafqeetUsd = String(v.tafqeet_usd_ar || '').trim() || 'فقط صفر دولار أمريكي';

        // Increased font sizing (+2 steps) for Sudanese Pound & US Dollar values
        const localAmtLen = String(netLocalFmt || '').length;
        const localHeroFontSize = localAmtLen > 14 ? '78px' : (localAmtLen > 11 ? '88px' : '96px');
        const localUnitFontSize = localAmtLen > 11 ? '42px' : '46px';

        const html = `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8" />
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1080px;
    background: #030e0c;
    font-family: 'Segoe UI', 'Dubai', 'Tahoma', 'Arial', sans-serif;
    color: #ffffff;
    padding: 0;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    text-rendering: geometricPrecision;
  }
  .voucher-canvas {
    position: relative;
    width: 1080px;
    padding: 56px 38px;
    background:
      radial-gradient(circle at 85% 10%, rgba(250, 204, 21, 0.22) 0%, transparent 42%),
      radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.20) 0%, transparent 48%),
      linear-gradient(180deg, #051914 0%, #051622 52%, #020b12 100%);
    overflow: hidden;
  }
  .guilloche-bg {
    position: absolute;
    inset: 0;
    opacity: 0.12;
    pointer-events: none;
    z-index: 0;
  }
  .gold-frame-outer {
    position: relative;
    z-index: 2;
    width: 100%;
    margin: 0;
    border: 6px solid #facc15;
    border-radius: 30px;
    padding: 11px;
    background: linear-gradient(160deg, rgba(250,204,21,0.45) 0%, rgba(6,26,22,0.97) 25%, rgba(5,18,28,0.98) 75%, rgba(250,204,21,0.45) 100%);
    box-shadow: 0 24px 60px rgba(0,0,0,0.88), 0 0 28px rgba(250,204,21,0.28), inset 0 0 30px rgba(250,204,21,0.18);
  }
  .gold-frame-inner {
    position: relative;
    width: 100%;
    border: 2.5px dashed rgba(253, 230, 138, 0.72);
    border-radius: 22px;
    padding: 24px 22px 26px;
    background: linear-gradient(180deg, rgba(8, 30, 25, 0.96) 0%, rgba(5, 17, 27, 0.99) 100%);
  }

  /* 1. الترويسة العليا: شعار مقاييس الملون M على اليمين واسم المنظومة بالعربي والإنجليزي */
  .header-section {
    margin-bottom: 14px;
  }
  .header-brand-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    background: linear-gradient(90deg, rgba(212,175,55,0.20) 0%, rgba(8,33,28,0.95) 50%, rgba(212,175,55,0.20) 100%);
    border: 2.5px solid rgba(250, 204, 21, 0.72);
    border-radius: 20px;
    padding: 15px 22px;
    box-shadow: 0 10px 28px rgba(0,0,0,0.55);
  }
  .magaiees-logo-right {
    width: 112px;
    height: 112px;
    flex-shrink: 0;
    border-radius: 22px;
    border: 2.5px solid rgba(250, 204, 21, 0.85);
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(6,22,34,0.98) 80%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 24px rgba(56, 189, 248, 0.32), inset 0 0 14px rgba(250, 204, 21, 0.22);
    padding: 8px;
  }
  .m-logo-img {
    width: 94px;
    height: 94px;
    object-fit: contain;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.6));
  }
  .header-titles-center {
    flex: 1;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .org-title {
    font-size: 50px;
    font-weight: 900;
    color: #ffffff;
    text-shadow: 0 3px 14px rgba(250,204,21,0.5);
    line-height: 1.15;
    white-space: nowrap;
  }
  .org-sub {
    font-size: 23px;
    font-weight: 900;
    color: #38bdf8;
    letter-spacing: 2.8px;
    direction: ltr;
  }

  /* لوحة سند رصيد وتحتها اسم الشركة مباشرة */
  .title-plaque {
    margin-top: 12px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.36) 0%, rgba(9, 42, 34, 0.97) 50%, rgba(212, 175, 55, 0.36) 100%);
    border: 3px solid #facc15;
    border-radius: 18px;
    padding: 13px 18px 15px;
    text-align: center;
    box-shadow: 0 10px 26px rgba(0,0,0,0.6), inset 0 0 24px rgba(250,204,21,0.22);
  }
  .voucher-main-title {
    font-size: 66px;
    font-weight: 900;
    color: #ffffff;
    text-shadow: 0 4px 18px rgba(250,204,21,0.65);
    line-height: 1.1;
  }
  .voucher-company-name {
    margin-top: 6px;
    font-size: 38px;
    font-weight: 900;
    color: #fde047;
    text-shadow: 0 2px 10px rgba(0,0,0,0.85);
    line-height: 1.2;
  }

  /* 2. شريط اسم العميل كاملاً (يصغر تلقائياً إذا كان طويلاً) وأمامه شارة صغيرة (دائن له بالأخضر / مدين عليه بالأحمر) */
  .customer-banner-card {
    background: linear-gradient(135deg, rgba(14, 42, 54, 0.97) 0%, rgba(7, 22, 32, 0.99) 100%);
    border: 2.5px solid rgba(250, 204, 21, 0.8);
    border-radius: 18px;
    padding: 16px 20px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: nowrap;
    box-shadow: 0 12px 28px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.12);
  }
  .holder-right-group {
    display: flex;
    align-items: baseline;
    gap: 10px;
    flex-wrap: nowrap;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
  }
  .holder-role-label {
    font-size: 38px;
    color: #fde047;
    font-weight: 900;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .holder-name {
    font-size: 56px;
    font-weight: 900;
    color: #ffffff;
    text-shadow: 0 3px 14px rgba(250, 204, 21, 0.4);
    line-height: 1.15;
    white-space: nowrap;
  }
  .status-badge-vertical {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: ${statusColors.badgeBg};
    border: 2px solid ${statusColors.badgeBorder};
    color: ${statusColors.badgeText};
    padding: 7px 15px;
    border-radius: 12px;
    font-size: 24px;
    font-weight: 900;
    box-shadow: 0 0 16px ${statusColors.accentGlow};
    white-space: nowrap;
    flex-shrink: 0;
  }
  .status-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: ${statusColors.dotColor};
    box-shadow: 0 0 10px ${statusColors.dotColor};
    flex-shrink: 0;
  }

  /* 3 & 4. بطاقات العملة بالجنيه السوداني أولاً ثم الدولار ثانياً بوضوح وأناقة فائقة */
  .currency-stack {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 14px;
  }
  .banknote-card {
    position: relative;
    border-radius: 20px;
    padding: 18px 16px;
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(0,0,0,0.65);
  }
  .banknote-local {
    background: linear-gradient(145deg, #10382e 0%, #061c17 55%, #143527 100%);
    border: 3px solid #facc15;
    box-shadow: 0 14px 34px rgba(0,0,0,0.65), inset 0 0 26px rgba(250,204,21,0.16);
  }
  .banknote-usd {
    background: linear-gradient(145deg, #0b3144 0%, #051927 55%, #0c3830 100%);
    border: 3px solid #10b981;
    box-shadow: 0 14px 34px rgba(0,0,0,0.65), inset 0 0 26px rgba(16,185,129,0.16);
  }
  .hero-amount-well {
    position: relative;
    background: rgba(2, 9, 17, 0.88);
    border: 2px solid rgba(250, 204, 21, 0.65);
    border-radius: 16px;
    padding: 18px 16px;
    margin-bottom: 12px;
    text-align: center;
    box-shadow: inset 0 4px 20px rgba(0,0,0,0.85);
  }
  .banknote-usd .hero-amount-well {
    border-color: rgba(16, 185, 129, 0.65);
  }
  .note-amount-row {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 16px;
    direction: rtl;
    flex-wrap: nowrap;
    white-space: nowrap;
  }
  .note-amount-num {
    font-size: 96px;
    font-weight: 900;
    font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.5px;
    line-height: 1.05;
    direction: ltr;
    unicode-bidi: embed;
    text-shadow: 0 4px 18px rgba(0,0,0,0.9);
  }
  .banknote-local .note-amount-num {
    color: #fde047;
    font-size: ${localHeroFontSize};
  }
  .banknote-usd .note-amount-num {
    color: #6ee7b7;
    font-size: 96px;
  }
  .note-amount-unit {
    font-size: 46px;
    font-weight: 900;
    color: #ffffff;
    white-space: nowrap;
  }
  .banknote-local .note-amount-unit {
    font-size: ${localUnitFontSize};
  }
  .direct-tafqeet-line {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1.5px dashed rgba(255, 255, 255, 0.24);
    font-size: 33px;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.35;
    text-align: center;
  }
  .banknote-local .direct-tafqeet-line {
    color: #fef9c3;
  }
  .banknote-usd .direct-tafqeet-line {
    color: #d1fae5;
  }

  .note-sub-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .sub-item {
    background: rgba(2, 8, 20, 0.90);
    border: 1.5px solid rgba(255,255,255,0.24);
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .sub-label {
    font-size: 28px;
    color: #f8fafc;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .dot-debit {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 8px #ef4444;
    display: inline-block;
    flex-shrink: 0;
  }
  .dot-credit {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 8px #22c55e;
    display: inline-block;
    flex-shrink: 0;
  }
  .sub-val {
    font-size: 43px;
    font-weight: 900;
    font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif;
    font-variant-numeric: tabular-nums;
    direction: rtl;
    text-align: right;
    white-space: nowrap;
  }
  .sub-debit { color: #fde047; }
  .sub-credit { color: #6ee7b7; }

  /* 5. بطاقة المراجع وبيانات السند في الأسفل (تحت الجنيه السوداني والدولار) */
  .references-card {
    background: linear-gradient(180deg, rgba(12, 34, 45, 0.96) 0%, rgba(7, 20, 30, 0.99) 100%);
    border: 2.5px solid rgba(229, 193, 88, 0.72);
    border-radius: 18px;
    padding: 16px 14px;
    margin-bottom: 14px;
    box-shadow: 0 12px 28px rgba(0,0,0,0.55);
  }
  .meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  .meta-item {
    background: rgba(3, 11, 20, 0.92);
    border: 1.5px solid rgba(255, 255, 255, 0.22);
    border-right: 6px solid #facc15;
    border-radius: 13px;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .meta-item.emerald-border {
    border-right-color: #10b981;
  }
  .meta-label {
    font-size: 27px;
    color: #cbd5e1;
    font-weight: 900;
  }
  .meta-value {
    font-size: 36px;
    font-weight: 900;
    color: #ffffff;
    direction: rtl;
    text-align: right;
    font-family: 'Segoe UI', 'Arial', 'Tahoma', sans-serif;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
  }
  .meta-value.ltr-val {
    direction: ltr;
    text-align: right;
  }
  .meta-value.gold { color: #fef08a; font-size: 38px; }
  .meta-value.emerald { color: #6ee7b7; font-size: 36px; }
  .meta-value.rose { color: #fda4af; font-size: 34px; letter-spacing: 0.5px; }

  /* 6. ختم مصادقة مالية إلكترونية أسفل السند */
  .voucher-footer {
    border-top: 2px solid rgba(229, 193, 88, 0.45);
    padding-top: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-left: 6px;
    padding-right: 6px;
  }
  .seal-badge {
    display: flex;
    align-items: center;
    gap: 14px;
    background: radial-gradient(circle, rgba(212,175,55,0.28) 0%, rgba(6,24,20,0.98) 100%);
    border: 3px double #facc15;
    border-radius: 50px;
    padding: 10px 26px;
    text-align: right;
    white-space: nowrap;
  }
  .seal-icon {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    border: 2.5px solid #facc15;
    background: rgba(250, 204, 21, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fde047;
    font-size: 30px;
    font-weight: 900;
    flex-shrink: 0;
  }
  .seal-custom-img {
    height: 84px;
    width: auto;
    min-width: 84px;
    max-width: 195px;
    object-fit: contain;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.96);
    padding: 5px 10px;
    border: 2.5px solid #facc15;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
    flex-shrink: 0;
  }
  .seal-text-main {
    font-size: 29px;
    font-weight: 900;
    color: #fef08a;
    white-space: nowrap;
  }
  .seal-text-sub {
    font-size: 20px;
    color: #6ee7b7;
    font-weight: 900;
    letter-spacing: 1px;
    margin-top: 2px;
    white-space: nowrap;
  }
  .barcode-box {
    text-align: left;
    direction: ltr;
    flex-shrink: 0;
  }
  .barcode-bars {
    font-family: 'Consolas', monospace;
    font-size: 26px;
    letter-spacing: 2.5px;
    color: #facc15;
    line-height: 1;
  }
  .barcode-label {
    font-family: 'Segoe UI', 'Arial', sans-serif;
    font-variant-numeric: tabular-nums;
    font-size: 21px;
    font-weight: 900;
    color: #e2e8f0;
    letter-spacing: 1.2px;
    margin-top: 5px;
  }
</style>
</head>
<body>
  <div class="voucher-canvas" id="voucher-card">
    <svg class="guilloche-bg" viewBox="0 0 1080 1650" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse">
          <path d="M 48 0 L 0 0 0 48" fill="none" stroke="#d4af37" stroke-width="0.5" stroke-opacity="0.38"/>
          <circle cx="24" cy="24" r="16" fill="none" stroke="#10b981" stroke-width="0.5" stroke-opacity="0.3"/>
        </pattern>
      </defs>
      <rect width="1080" height="1650" fill="url(#grid)" />
      <circle cx="540" cy="825" r="390" fill="none" stroke="#d4af37" stroke-width="1.4" stroke-dasharray="6 4"/>
    </svg>

    <div class="gold-frame-outer">
      <div class="gold-frame-inner">
        <!-- 1. الترويسة: شعار مقاييس الملون M على اليمين + منظومة مقاييس المحاسبية العالمية بالعربي والإنجليزي -->
        <div class="header-section">
          <div class="header-brand-row">
            <div class="magaiees-logo-right">
              ${logoHtml}
            </div>
            <div class="header-titles-center">
              <div class="org-title">منظومة مقاييس المحاسبية العالمية</div>
              <div class="org-sub">MAGAIEES GLOBAL ACCOUNTING SYSTEM</div>
            </div>
          </div>

          <!-- تحتها مباشرة: سند رصيد + اسم الشركة -->
          <div class="title-plaque">
            <div class="voucher-main-title">سند رصيد</div>
            ${companyName ? `<div class="voucher-company-name">${esc(companyName)}</div>` : ''}
          </div>
        </div>

        <!-- 2. شريط اسم العميل كاملاً وأمامه شارة الحالة المصغرة (دائن له بالأخضر / مدين عليه بالأحمر) -->
        <div class="customer-banner-card">
          <div class="holder-right-group">
            <span class="holder-role-label">${esc(v.role_title || 'العميل')}:</span>
            <span class="holder-name">${esc(v.entity_name)}</span>
          </div>
          <div class="status-badge-vertical">
            <span class="status-dot"></span>
            <span>${esc(statusLabelAr)}</span>
          </div>
        </div>

        <!-- 3 & 4. العملة بالجنيه السوداني أولاً ثم الدولار الأمريكي ثانياً -->
        <div class="currency-stack">
          <!-- العملة المحلية (الجنيه السوداني — رقم صحيح بدون فواصل عشرية) -->
          <div class="banknote-card banknote-local">
            <div class="hero-amount-well">
              <div class="note-amount-row">
                <span class="note-amount-num">${esc(netLocalFmt)}</span>
                <span class="note-amount-unit">${esc(localUnitFull)}</span>
              </div>
              <div class="direct-tafqeet-line">${esc(tafqeetLocal)}</div>
            </div>

            <div class="note-sub-grid">
              <div class="sub-item">
                <span class="sub-label"><span class="dot-debit"></span> مدين (عليه)</span>
                <span class="sub-val sub-debit"><span dir="ltr">${esc(totalDebitLocalFmt)}</span> ${esc(v.currency_label_ar)}</span>
              </div>
              <div class="sub-item">
                <span class="sub-label"><span class="dot-credit"></span> دائن (له)</span>
                <span class="sub-val sub-credit"><span dir="ltr">${esc(totalCreditLocalFmt)}</span> ${esc(v.currency_label_ar)}</span>
              </div>
            </div>
          </div>

          <!-- الدولار الأمريكي (خانة عشرية واحدة) -->
          <div class="banknote-card banknote-usd">
            <div class="hero-amount-well">
              <div class="note-amount-row">
                <span class="note-amount-num">$ ${esc(netUsdFmt)}</span>
                <span class="note-amount-unit">دولار أمريكي</span>
              </div>
              <div class="direct-tafqeet-line">${esc(tafqeetUsd)}</div>
            </div>

            <div class="note-sub-grid">
              <div class="sub-item">
                <span class="sub-label"><span class="dot-debit"></span> مدين ($)</span>
                <span class="sub-val sub-debit"><span style="direction:ltr; unicode-bidi:embed; display:inline-block;">$ ${esc(totalDebitUsdFmt)}</span></span>
              </div>
              <div class="sub-item">
                <span class="sub-label"><span class="dot-credit"></span> دائن ($)</span>
                <span class="sub-val sub-credit"><span style="direction:ltr; unicode-bidi:embed; display:inline-block;">$ ${esc(totalCreditUsdFmt)}</span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. مربع المراجع وبيانات السند في الأسفل (الرقم المرجعي، تاريخ السند، وقت الحركة، تاريخ آخر حركة، سعر الصرف، الحركات) -->
        <div class="references-card">
          <div class="meta-grid">
            <div class="meta-item">
              <span class="meta-label">الرقم المرجعي للسند</span>
              <span class="meta-value ltr-val rose">${esc(v.voucher_serial)}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">تاريخ السند</span>
              <span class="meta-value ltr-val">${esc(issueDate)}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">وقت تاريخ الحركة</span>
              <span class="meta-value ltr-val">${esc(issueTime)}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">تاريخ آخر حركة مالية</span>
              <span class="meta-value ltr-val">${esc(v.last_movement_date)}</span>
            </div>
            <div class="meta-item emerald-border">
              <span class="meta-label">سعر الصرف</span>
              <span class="meta-value emerald"><span dir="ltr">1$ = ${esc(v.exchange_rate_formatted)}</span> ${esc(v.currency_label_ar)}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">الحركات</span>
              <span class="meta-value gold">${esc(v.movements_count)}</span>
            </div>
          </div>
        </div>

        <!-- 6. ختم مصادقة مالية إلكترونية في الأسفل -->
        <div class="voucher-footer">
          <div class="seal-badge">
            ${stampImage ? `<img src="${stampImage}" alt="Stamp" class="seal-custom-img" />` : `<div class="seal-icon">✔</div>`}
            <div>
              <div class="seal-text-main">${esc(stampText)}</div>
              <div class="seal-text-sub">${companyName ? esc(companyName) : 'CERTIFIED • MAGAIEES ERP'}</div>
            </div>
          </div>
          <div class="barcode-box">
            <div class="barcode-bars">||| |||| || ||| |||| ||| ||</div>
            <div class="barcode-label">${esc(v.voucher_serial)}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function() {
      // 1. Auto-scale customer name so the full name is always shown on one single line
      const group = document.querySelector('.holder-right-group');
      const nameEl = document.querySelector('.holder-name');
      const roleEl = document.querySelector('.holder-role-label');
      if (group && nameEl) {
        let nameSize = 56;
        let roleSize = 38;
        while (group.scrollWidth > group.clientWidth && nameSize > 18) {
          nameSize -= 1;
          nameEl.style.fontSize = nameSize + 'px';
          if (nameSize < 40 && roleEl && roleSize > 24) {
            roleSize = Math.max(24, Math.round(nameSize * 0.72));
            roleEl.style.fontSize = roleSize + 'px';
          }
        }
      }

      // 2. Auto-scale hero currency rows if needed so large amounts + unit always stay on one line
      document.querySelectorAll('.hero-amount-well').forEach(well => {
        const row = well.querySelector('.note-amount-row');
        const num = well.querySelector('.note-amount-num');
        const unit = well.querySelector('.note-amount-unit');
        if (row && num) {
          let numSize = parseFloat(window.getComputedStyle(num).fontSize) || 96;
          let unitSize = unit ? (parseFloat(window.getComputedStyle(unit).fontSize) || 46) : 46;
          const maxW = well.clientWidth - 28;
          while (row.scrollWidth > maxW && numSize > 48) {
            numSize -= 1;
            num.style.fontSize = numSize + 'px';
            if (unit && numSize < 80 && unitSize > 32) {
              unitSize = Math.max(32, Math.round(numSize * 0.48));
              unit.style.fontSize = unitSize + 'px';
            }
          }
        }
      });

      // 3. Auto-scale sub-item debit/credit values if needed
      document.querySelectorAll('.sub-item').forEach(box => {
        const val = box.querySelector('.sub-val');
        if (val) {
          let valSize = parseFloat(window.getComputedStyle(val).fontSize) || 43;
          const maxW = box.clientWidth - 28;
          while (val.scrollWidth > maxW && valSize > 24) {
            valSize -= 1;
            val.style.fontSize = valSize + 'px';
          }
        }
      });
    })();
  </script>
</body>
</html>`;

        if (!globalThis.__voucherImgCache) {
            globalThis.__voucherImgCache = new Map();
        }
        const cacheKey = `${v.entity_type || ''}_${v.entity_name || ''}_${v.net_balance_local || 0}_${v.net_balance_usd || 0}_${v.movements_count || 0}_${v.status_type || ''}_${companyName}_${stampText}_${stampImage ? stampImage.length : 0}`;
        const cachedEntry = globalThis.__voucherImgCache.get(cacheKey);
        if (cachedEntry && (Date.now() - cachedEntry.ts <= 10000) && cachedEntry.buf) {
            return cachedEntry.buf;
        }

        const browser = await this._getVoucherBrowser();
        let page = null;
        let usingWarmPage = false;
        if (globalThis.__magaieesVoucherWarmPage && !globalThis.__magaieesVoucherWarmPage.isClosed() && !globalThis.__magaieesVoucherWarmPageBusy) {
            page = globalThis.__magaieesVoucherWarmPage;
            globalThis.__magaieesVoucherWarmPageBusy = true;
            usingWarmPage = true;
        } else {
            page = await browser.newPage();
            await page.setViewport({ width: 1080, height: 1650, deviceScaleFactor: 1.0 });
        }
        try {
            await page.setContent(html, { waitUntil: 'domcontentloaded', timeout: 6000 });
            if (stampImage) {
                await page.evaluate(async () => {
                    const imgs = Array.from(document.querySelectorAll('img'));
                    await Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(r => { img.onload = r; img.onerror = r; setTimeout(r, 1500); })));
                });
            }
            const cardEl = await page.$('#voucher-card');
            const shotOpts = { type: 'jpeg', quality: 82 };
            const buf = Buffer.from(await (cardEl ? cardEl.screenshot(shotOpts) : page.screenshot(shotOpts)));
            if (globalThis.__voucherImgCache.size > 30) {
                const oldestK = globalThis.__voucherImgCache.keys().next().value;
                globalThis.__voucherImgCache.delete(oldestK);
            }
            globalThis.__voucherImgCache.set(cacheKey, { ts: Date.now(), buf });
            return buf;
        } finally {
            if (usingWarmPage) {
                globalThis.__magaieesVoucherWarmPageBusy = false;
            } else {
                await page.close().catch(() => {});
            }
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  PUBLIC API
    // ═══════════════════════════════════════════════════════════

    async sendMessage(phone, content) {
        if (!this.isConnected) throw new Error('WhatsApp not connected');
        
        // Wrap the message sending in a 15-second timeout to prevent blocking or hanging requests
        const sendPromise = this.socket.sendMessage(phone, content);
        const timeoutPromise = new Promise((_, reject) => 
            setTimeout(() => reject(new Error('Send request timed out on WhatsApp server')), 15_000)
        );
        
        const result = await Promise.race([sendPromise, timeoutPromise]);
        if (result?.key?.id && result?.message) {
            try {
                this.messageCache.set(result.key.id, result.message);
                if (this.messageCache.size > 200) {
                    const oldestKey = this.messageCache.keys().next().value;
                    this.messageCache.delete(oldestKey);
                }
            } catch (_) {}
        }
        return result;
    }

    async logout() {
        log(this.userId, 'Logout requested. Clearing session state...');
        this._isDestroying = true;
        this._clearAllTimers();
        this._setState('DISCONNECTING');

        this.qrCode = null;
        this.phoneNumber = null;
        this.reconnectAttempts = 0;

        if (this.socket) {
            try {
                this.socket.ev.removeAllListeners();
                await this.socket.logout().catch(() => {});
            } catch (e) {
                logErr(this.userId, 'socket.logout() error:', e.message);
            }
        }

        this._destroySocket();
        
        // Give OS 300ms to close open handles
        await new Promise(r => setTimeout(r, 300));
        
        this._deleteAuthFolder();

        this._isDestroying = false;
        this.lastInitTime = 0; // Reset cooldown so init can run immediately
        this._setState('IDLE');

        log(this.userId, 'Post-logout: Auto-initializing fresh session for new QR code...');
        return this.init().catch(e => logErr(this.userId, 'post-logout init error:', e.message));
    }

    async restart() {
        log(this.userId, 'Manual restart requested');
        this._clearAllTimers();
        this._destroySocket();
        this._isInitializing = false;
        this._isDestroying   = false;
        this.reconnectAttempts = 0;
        this.lastInitTime = 0;
        this.qrCode = null;
        this._setState('IDLE');

        // Small delay then re-init
        await new Promise(r => setTimeout(r, 500));
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

    getConnectedClient() {
        for (const [uid, client] of this.clients) {
            if (client.isConnected) return client;
        }
        return null;
    }

    findActiveClient(preferredId) {
        if (preferredId && this.clients.has(preferredId)) {
            const client = this.clients.get(preferredId);
            if (client.isConnected) return client;
        }
        const connected = this.getConnectedClient();
        if (connected) return connected;

        if (preferredId) return this.get(preferredId);
        return this.get('default');
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
        this._initialTimer = setTimeout(() => this._runCycle(), 30_000);
        this.timer = setInterval(() => this._runCycle(), HEALER_INTERVAL_MS);
    }

    stop() {
        if (this._initialTimer) {
            clearTimeout(this._initialTimer);
            this._initialTimer = null;
        }
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

        // Guard: never touch a client that is being destroyed
        if (client._isDestroying) return;

        // ── Case 1: FAILED — restart the client (only if no pending reconnect timer) ──
        if (status === 'FAILED') {
            if (client._reconnectTimer) return;
            
            const credsPath = path.join(client.authFolder, 'creds.json');
            const isFreshSession = !client.phoneNumber && (!fs.existsSync(credsPath) || fs.statSync(credsPath).size < 100);
            if (isFreshSession) {
                this._log(userId, 'SKIP_HEAL', 'Fresh session — user must retry manually');
                return;
            }
            
            this._log(userId, 'AUTO_RESTART', 'Status was FAILED → restarting');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client._isDestroying   = false;
            client.reconnectAttempts = 0;
            client.lastInitTime = 0;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 500));
            await client.init();
            return;
        }

        // ── Case 2: IDLE or DISCONNECTED — auto-init if auth folder exists (user previously linked) ──
        if (status === 'IDLE' || (status === 'DISCONNECTED' && process.platform !== 'win32')) {
            const credsPath = path.join(client.authFolder, 'creds.json');
            if (!fs.existsSync(credsPath)) return; // User never linked — don't force init
            this._log(userId, 'AUTO_INIT', `Status was ${status} with existing auth → initializing`);
            this.stats.fixes++;
            client.lastInitTime = 0;
            client._isInitializing = false;
            await client.init();
            return;
        }

        // ── Case 3: CONNECTED but socket is dead ──
        if (status === 'CONNECTED' && !socket) {
            this._log(userId, 'ZOMBIE_FIX', 'Status=CONNECTED but no socket → restarting');
            this.stats.fixes++;
            client._setState('IDLE');
            client._isInitializing = false;
            client.lastInitTime = 0;
            await client.init();
            return;
        }

        const stateAgeMs = Date.now() - (client.stateEnteredAt || client.lastInitTime || Date.now());

        // ── Case 4: Stuck in INITIALIZING/CONNECTING for too long ──
        if ((status === 'INITIALIZING' || status === 'CONNECTING') && stateAgeMs > 120_000) {
            this._log(userId, 'STUCK_FIX', `Stuck in ${status} for >2min → force restarting`);
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client.lastInitTime = 0;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 500));
            await client.init();
            return;
        }

        // ── Case 5: RECONNECTING for too long (>5 min in RECONNECTING state without active timer) ──
        if (status === 'RECONNECTING' && !client._reconnectTimer && stateAgeMs > 300_000) {
            this._log(userId, 'RECONNECT_RESET', 'Stuck in RECONNECTING for >5min → fresh restart');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client.reconnectAttempts = 0;
            client.lastInitTime = 0;
            client._setState('IDLE');
            await new Promise(r => setTimeout(r, 500));
            await client.init();
            return;
        }

        // ── Case 6: QR_READY for too long (>5 min) ──
        if (status === 'QR_READY' && stateAgeMs > 300_000) {
            this._log(userId, 'QR_STALE_FIX', 'QR displayed for >5min without scan → refreshing');
            this.stats.fixes++;
            client._clearAllTimers();
            client._destroySocket();
            client._isInitializing = false;
            client.lastInitTime = 0;
            client._setState('IDLE');
            client.qrCode = null;
            await new Promise(r => setTimeout(r, 500));
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
    phone = String(phone).replace(/[^0-9]/g, '');
    if (phone.startsWith('00')) phone = phone.substring(2);

    if (phone.startsWith('0')) {
        if (phone.startsWith('09')) phone = '249' + phone.substring(1);
        else if (phone.startsWith('01') && phone.length === 10) phone = '249' + phone.substring(1);
        else if (phone.startsWith('05')) phone = '966' + phone.substring(1);
        else if (phone.startsWith('01')) phone = '20'  + phone.substring(1);
    } else {
        if ((phone.startsWith('9') || phone.startsWith('1')) && phone.length === 9) phone = '249' + phone;
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

// (Error handler moved after routes — see bottom of file)

// ── GET /wa/system-repair ──────────────────────────────────────
app.get('/wa/system-repair', async (req, res) => {
    const results = {};
    const run = (cmd) => {
        try {
            return execSync(cmd, { encoding: 'utf8', timeout: 30000, shell: '/bin/bash' });
        } catch (e) {
            return `ERROR: ${e.message}\nSTDOUT: ${e.stdout || ''}\nSTDERR: ${e.stderr || ''}`;
        }
    };

    results.whoami = run('id');
    results.php_sockets_before = run('ls -la /var/run/php/ 2>&1');
    results.services_status = run('systemctl status php8.3-fpm --no-pager 2>&1 || systemctl status php*-fpm --no-pager 2>&1');

    results.restart_php = run(`
        for v in 8.3 8.2 8.1 8.0 7.4; do
            if [ -f "/lib/systemd/system/php$v-fpm.service" ] || [ -f "/etc/systemd/system/php$v-fpm.service" ] || [ -f "/etc/init.d/php$v-fpm" ]; then
                systemctl unmask "php$v-fpm" 2>/dev/null || true
                systemctl enable "php$v-fpm" 2>/dev/null || true
                systemctl restart "php$v-fpm" 2>/dev/null || service "php$v-fpm" restart 2>/dev/null || true
            fi
        done
        systemctl restart php-fpm 2>/dev/null || true
    `);

    results.socket_fix = run(`
        if [ ! -S /var/run/php/php8.3-fpm.sock ]; then
            for s in /var/run/php/php*-fpm.sock; do
                if [ -S "$s" ]; then
                    ln -sf "$s" /var/run/php/php8.3-fpm.sock
                    break
                fi
            done
        fi
        mkdir -p /var/run/php
        chmod 755 /var/run/php
        chmod 666 /var/run/php/*.sock 2>/dev/null || true
        chown -R www-data:www-data /var/run/php 2>/dev/null || true
    `);

    results.php_sockets_after = run('ls -la /var/run/php/ 2>&1');
    results.nginx_test = run('nginx -t 2>&1');
    results.nginx_restart = run('systemctl restart nginx 2>&1 || systemctl reload nginx 2>&1');
    results.curl_test = run('curl -s -I http://127.0.0.1/index.php -H "Host: elsajana.com" 2>&1');

    res.json(results);
});

// Auto-trigger self-healing on boot
setTimeout(() => {
    try {
        exec(`
            for v in 8.3 8.2 8.1 8.0 7.4; do
                [ -f "/lib/systemd/system/php$v-fpm.service" ] && (systemctl unmask "php$v-fpm"; systemctl enable "php$v-fpm"; systemctl restart "php$v-fpm") 2>/dev/null || true
            done
            if [ ! -S /var/run/php/php8.3-fpm.sock ]; then
                for s in /var/run/php/php*-fpm.sock; do
                    [ -S "$s" ] && ln -sf "$s" /var/run/php/php8.3-fpm.sock && break
                done
            fi
            chmod 666 /var/run/php/*.sock 2>/dev/null || true
            systemctl restart nginx 2>/dev/null || systemctl reload nginx 2>/dev/null || true
        `, { shell: '/bin/bash' });
    } catch(e) {}
}, 2000);

// ── GET /wa/status ─────────────────────────────────────────────
app.get('/wa/status', (req, res) => {
    const requestedId = req.query.userId || req.body?.userId;
    const client = manager.findActiveClient(requestedId);

    // Auto-init if idle, failed, or logged out (user wants to reconnect or scan new QR)
    if (!client.isConnected && (client.status === 'IDLE' || client.status === 'FAILED' || client.status === 'LOGGED_OUT' || client.status === 'DISCONNECTED')) {
        client.reconnectAttempts = 0; // Reset so user-triggered retry gets fresh attempts
        client.init().catch(e => logErr(client.userId, 'auto-init failed:', e.message));
    }

    res.json(client.toJSON());
});


// ── GET /wa/qr ─────────────────────────────────────────────────
app.get('/wa/qr', (req, res) => {
    const requestedId = req.query.userId || req.body?.userId;
    const client = manager.findActiveClient(requestedId);
    res.json({
        userId: client.userId,
        status: client.status,
        qrCode: client.qrCode,
        phoneNumber: client.phoneNumber,
    });
});


// ── GET /wa/generate-qr ────────────────────────────────────────
app.get('/wa/generate-qr', async (req, res) => {
    const text = req.query.text || 'VERIFIED';
    try {
        const dataUrl = await QRCode.toDataURL(text, { width: 140, margin: 1, color: { dark: '#0f172a', light: '#ffffff' } });
        res.json({ success: true, dataUrl });
    } catch (e) {
        res.json({ success: false, error: e.message });
    }
});


// ── POST /wa/send (and aliases /wa/send-message, /send) ────────
app.post(['/wa/send', '/wa/send-message', '/send'], upload.any(), async (req, res) => {
    const userId  = req.body?.userId || req.query?.userId;
    let   phone   = req.body?.phone || req.query?.phone;
    const message = req.body?.message || req.query?.message || '';
    const client  = manager.findActiveClient(userId);

    if (!client || !client.isConnected) {
        return res.status(400).json({ success: false, error: 'WhatsApp not connected.' });
    }
    if (!phone) {
        return res.status(400).json({ success: false, error: 'Phone number is required.' });
    }

    const rawPhoneStr = String(phone).trim();
    if (rawPhoneStr.endsWith('@lid')) {
        const lidDigits = rawPhoneStr.replace(/[^0-9]/g, '');
        phone = `${lidDigits}@lid`;
    } else {
        phone = normalizePhoneNumber(rawPhoneStr);
        if (!phone.endsWith('@s.whatsapp.net')) {
            phone = `${phone}@s.whatsapp.net`;
        }
    }

    try {
        const file = (req.files && req.files.length > 0) ? req.files[0] : (req.file || null);
        if (file) {
            const isImage = (file.mimetype && file.mimetype.startsWith('image/')) || 
                            /\.(png|jpe?g|webp)$/i.test(file.originalname || '');
            if (isImage) {
                await client.sendMessage(phone, {
                    image: { url: file.path },
                    caption: message,
                });
            } else {
                let docFileName = req.body?.documentName || req.body?.fileName || file.originalname || 'document.pdf';
                try {
                    const decoded = Buffer.from(docFileName, 'latin1').toString('utf8');
                    if (!decoded.includes('\uFFFD') && /[\u0600-\u06FF]/.test(decoded)) {
                        docFileName = decoded;
                    }
                } catch(e) {}
                await client.sendMessage(phone, {
                    document: { url: file.path },
                    mimetype: file.mimetype || 'application/pdf',
                    fileName: docFileName,
                    caption: message,
                });
            }
            // Clean up uploaded file
            fs.unlink(file.path, () => {});
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

// ── POST /wa/import-session ────────────────────────────────────
app.post('/wa/import-session', async (req, res) => {
    const sessionUserId = String(req.body?.sessionUserId || req.query.userId || 'wa_tenant_12').replace(/[^a-zA-Z0-9_-]/g, '') || 'wa_tenant_12';
    const mode = String(req.body?.mode || req.query.mode || 'single').toLowerCase(); // 'start' | 'append' | 'finish' | 'single'
    const filesMap = req.body?.files || {};
    if (mode !== 'finish' && (!filesMap || typeof filesMap !== 'object' || Object.keys(filesMap).length === 0)) {
        return res.status(400).json({ success: false, error: 'No session files provided' });
    }
    try {
        const client = manager.get(sessionUserId);
        const targetDir = path.join(AUTH_BASE_DIR, `baileys_auth_${sessionUserId}`);
        if (!fs.existsSync(targetDir)) {
            fs.mkdirSync(targetDir, { recursive: true });
        }
        if (mode === 'start' || mode === 'single') {
            await client._destroySocket();
            client._setState('IDLE');
            // Clear any stale QR-only files before importing linked credentials
            for (const oldF of fs.readdirSync(targetDir)) {
                try { fs.unlinkSync(path.join(targetDir, oldF)); } catch (_) {}
            }
        }
        let writtenCount = 0;
        if (filesMap && typeof filesMap === 'object') {
            for (const [fname, b64] of Object.entries(filesMap)) {
                const cleanName = path.basename(String(fname));
                if (!cleanName || cleanName === '.' || cleanName === '..') continue;
                const buf = Buffer.from(String(b64), 'base64');
                fs.writeFileSync(path.join(targetDir, cleanName), buf);
                writtenCount++;
            }
        }
        if (mode === 'finish' || mode === 'single') {
            client._loadLidMapFromDisk();
            client.reconnectAttempts = 0;
            client.lastInitTime = 0;
            client._isInitializing = false;
            client.init().catch(e => logErr(sessionUserId, 'import-session init error:', e.message));
        }
        res.json({ success: true, sessionUserId, mode, writtenFiles: writtenCount });
    } catch (e) {
        logErr(sessionUserId, 'import-session error:', e.message);
        res.status(500).json({ success: false, error: e.message });
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
app.get('/wa/healer', async (_req, res) => {
    try {
        await healer._runCycle();
        res.json({ success: true, message: 'Healer cycle triggered manually' });
    } catch (e) {
        res.json({ success: false, error: e.message });
    }
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

    // Pre-warm Puppeteer voucher renderer in background so the very first "رصيد" request is instant
    setTimeout(() => {
        try {
            const anyClient = manager.findActiveClient('wa_tenant_12');
            if (anyClient && typeof anyClient._getVoucherBrowser === 'function') {
                anyClient._getVoucherBrowser().then(() => {
                    log('STARTUP', '⚡ Pre-warmed Puppeteer voucher browser & page for instant balance cards');
                }).catch((e) => {
                    logErr('STARTUP', 'Puppeteer pre-warm warning:', e.message);
                });
            }
        } catch (_) {}
    }, 1500);

    // Auto-discover and connect any saved WhatsApp sessions on disk (Cloud 24/7 Server only, unless FORCE_LOCAL_WA=1 on Windows)
    const allowStartupConnect = (process.platform !== 'win32') || (process.env.FORCE_LOCAL_WA === '1');
    if (!allowStartupConnect) {
        log('STARTUP', '☁️ Running on Windows local laptop — skipping local Baileys auto-connect so 24/7 Cloud Hub (c.elsajana.com) retains exclusive connection.');
    } else {
        let discoveredSessions = 0;
        try {
            const authDirs = fs.readdirSync(AUTH_BASE_DIR);
            for (const dir of authDirs) {
                if (dir.startsWith('baileys_auth_')) {
                    const credsFile = path.join(AUTH_BASE_DIR, dir, 'creds.json');
                    if (fs.existsSync(credsFile)) {
                        const uid = dir.replace('baileys_auth_', '');
                        discoveredSessions++;
                        log('STARTUP', `Discovered existing saved WhatsApp session: ${uid}`);
                        manager.initClient(uid).catch(e => logErr(uid, 'startup auto-init failed:', e.message));
                    }
                }
            }
        } catch (e) {
            logErr('STARTUP', 'Session discovery failed:', e.message);
        }

        if (discoveredSessions === 0) {
            manager.initClient('default-saas-admin');
        }
        healer.start();
    }
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
