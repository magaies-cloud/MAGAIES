process.env.HOME = '/tmp';
const express = require('express');
const cors = require('cors');
const multer = require('multer');
const { makeWASocket, useMultiFileAuthState, DisconnectReason, Browsers } = require('@whiskeysockets/baileys');
const pino = require('pino');
const qrcode = require('qrcode');
const fs = require('fs');

process.on('uncaughtException', (err) => console.error('⚠️ Uncaught Exception:', err));
process.on('unhandledRejection', (reason, promise) => console.error('⚠️ Unhandled Rejection:', reason));

const app = express();
app.use(cors());
app.use(express.json({ limit: '50mb' }));
const upload = multer({ dest: 'uploads/' });

const clients = {};
const qrCodes = {};
const statuses = {};
const phoneNumbers = {};
const destroyingClients = new Set();

function normalizePhoneNumber(phone) {
    if (!phone) return '';
    phone = phone.replace(/[^0-9]/g, '');
    if (phone.startsWith('00')) phone = phone.substring(2);
    
    if (phone.startsWith('0')) {
        if (phone.startsWith('09')) phone = '249' + phone.substring(1);
        else if (phone.startsWith('05')) phone = '966' + phone.substring(1);
        else if (phone.startsWith('01')) phone = '20' + phone.substring(1);
    } else {
        if (phone.startsWith('9') && phone.length === 9) phone = '249' + phone;
        else if (phone.startsWith('5') && phone.length === 9) phone = '966' + phone;
    }
    
    if (phone.startsWith('2490')) phone = '249' + phone.substring(4);
    else if (phone.startsWith('9660')) phone = '966' + phone.substring(4);
    else if (phone.startsWith('200')) phone = '20' + phone.substring(3);
    
    return phone;
}

async function initClient(userId) {
    if (clients[userId]) return clients[userId];
    if (destroyingClients.has(userId)) return null;

    console.log(`Initializing Baileys Client for User: ${userId}`);
    statuses[userId] = 'INITIALIZING';

    const { state, saveCreds } = await useMultiFileAuthState(`baileys_auth_${userId}`);
    
    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
        logger: pino({ level: 'info' }), // Show logs to debug disconnects
        browser: Browsers.macOS('Desktop'), // Disguise as Mac OS to bypass WhatsApp Web ban
        syncFullHistory: false // Speed up login
    });

    clients[userId] = sock;

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            console.log(`QR Code generated for user ${userId}`);
            // Completely bypass native qrcode module to prevent Node.js Segmentation Fault on Linux
            qrCodes[userId] = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' + encodeURIComponent(qr);
            statuses[userId] = 'QR_READY';
        }

        if (connection === 'close') {
            const shouldReconnect = (lastDisconnect?.error)?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log(`Connection closed for ${userId}. Reconnecting: ${shouldReconnect}`, lastDisconnect?.error);
            statuses[userId] = 'DISCONNECTED';
            qrCodes[userId] = null;
            delete clients[userId];
            
            if (shouldReconnect && !destroyingClients.has(userId)) {
                setTimeout(() => initClient(userId), 3000);
            }
        } else if (connection === 'connecting') {
            statuses[userId] = 'AUTHENTICATING';
        } else if (connection === 'open') {
            console.log(`WhatsApp Client for User ${userId} is READY!`);
            statuses[userId] = 'CONNECTED';
            qrCodes[userId] = null;
            // Get user phone number from session
            if (sock.user && sock.user.id) {
                phoneNumbers[userId] = sock.user.id.split(':')[0]; // format is number:device@s.whatsapp.net
            }
        }
    });

    return sock;
}

app.get('/wa/status', (req, res) => {
    const userId = req.query.userId || 'default';
    if (!clients[userId] && statuses[userId] !== 'INITIALIZING' && statuses[userId] !== 'QR_READY' && statuses[userId] !== 'AUTHENTICATING' && !destroyingClients.has(userId)) {
        initClient(userId);
    }
    res.json({
        userId,
        status: statuses[userId] || 'STARTING',
        phoneNumber: phoneNumbers[userId] || null
    });
});

app.get('/wa/qr', (req, res) => {
    const userId = req.query.userId || 'default';
    res.json({
        userId,
        status: statuses[userId],
        qrCode: qrCodes[userId] || null,
        phoneNumber: phoneNumbers[userId] || null
    });
});

app.post('/wa/send', upload.single('pdf'), async (req, res) => {
    const userId = req.body.userId || 'default';
    let phone = req.body.phone;
    const message = req.body.message || '';

    if (!clients[userId] || statuses[userId] !== 'CONNECTED') {
        return res.status(400).json({ error: 'WhatsApp not connected for this user.' });
    }
    if (!phone) {
        return res.status(400).json({ error: 'Phone number is required.' });
    }

    phone = normalizePhoneNumber(phone);
    if (!phone.endsWith('@s.whatsapp.net')) {
        phone = `${phone}@s.whatsapp.net`;
    }

    try {
        const sock = clients[userId];
        console.log(`Attempting to send message to ${phone}`);

        if (req.file) {
            await sock.sendMessage(phone, {
                document: { url: req.file.path },
                mimetype: 'application/pdf',
                fileName: req.file.originalname || 'document.pdf',
                caption: message
            });
            fs.unlink(req.file.path, () => {});
        } else {
            await sock.sendMessage(phone, { text: message });
        }

        res.json({ success: true, message: 'Sent successfully!' });
    } catch (err) {
        console.error('Send Error:', err);
        res.status(500).json({ error: 'Failed to send message', details: err.message });
    }
});

app.post('/wa/logout', async (req, res) => {
    const userId = req.body.userId || req.query.userId || 'default';
    const sock = clients[userId];
    if (sock || statuses[userId]) {
        destroyingClients.add(userId);
        statuses[userId] = 'DISCONNECTING';
        
        if (sock) {
            try { await sock.logout(); } catch (e) {}
            delete clients[userId];
        }
        
        statuses[userId] = 'LOGGED_OUT';
        qrCodes[userId] = null;
        phoneNumbers[userId] = null;
        
        // Remove auth folder completely to ensure clean state next time
        const authFolder = `baileys_auth_${userId}`;
        if (fs.existsSync(authFolder)) {
            fs.rmSync(authFolder, { recursive: true, force: true });
        }

        setTimeout(() => { destroyingClients.delete(userId); }, 3000);
        res.json({ success: true, message: 'Logged out successfully.' });
    } else {
        res.json({ success: true, message: 'Not connected.' });
    }
});

const PORT = 3001;
app.listen(PORT, () => {
    console.log(`🚀 WhatsApp Baileys Microservice running on port ${PORT}`);
    initClient('default-saas-admin');
});
