const express = require('express');
const cors = require('cors');
const multer = require('multer');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');

// Prevent process crashes due to EBUSY file locks during LocalAuth logouts on Windows
process.on('uncaughtException', (err) => {
    console.error('⚠️ Uncaught Exception:', err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('⚠️ Unhandled Rejection at:', promise, 'reason:', reason);
});

const app = express();
app.use(cors());
app.use(express.json({ limit: '50mb' }));
const upload = multer({ dest: 'uploads/' });

// Store active clients by userId
const clients = {};
// Store QR codes temporarily for frontend to fetch
const qrCodes = {};
// Store connection status
const statuses = {};
const destroyingClients = new Set();

function normalizePhoneNumber(phone) {
    if (!phone) return '';
    // Strip all non-numeric characters
    phone = phone.replace(/[^0-9]/g, '');
    
    // Strip leading 00
    if (phone.startsWith('00')) {
        phone = phone.substring(2);
    }
    
    // If it starts with 0, check prefixes
    if (phone.startsWith('0')) {
        if (phone.startsWith('09')) {
            // Sudan (e.g. 0912300105 -> 249912300105)
            phone = '249' + phone.substring(1);
        } else if (phone.startsWith('05')) {
            // Saudi Arabia (e.g. 0501234567 -> 966501234567)
            phone = '966' + phone.substring(1);
        } else if (phone.startsWith('01')) {
            // Egypt (e.g. 01012345678 -> 201012345678)
            phone = '20' + phone.substring(1);
        }
    } else {
        // No leading 0. Check if they omitted country code
        if (phone.startsWith('9') && phone.length === 9) {
            phone = '249' + phone;
        } else if (phone.startsWith('5') && phone.length === 9) {
            phone = '966' + phone;
        }
    }
    
    // Strip redundant leading 0 after country code if any
    if (phone.startsWith('2490')) {
        phone = '249' + phone.substring(4);
    } else if (phone.startsWith('9660')) {
        phone = '966' + phone.substring(4);
    } else if (phone.startsWith('200')) {
        phone = '20' + phone.substring(3);
    }
    
    return phone;
}

// Helper to initialize a client for a user
function initClient(userId) {
    if (clients[userId]) return clients[userId];
    if (destroyingClients.has(userId)) {
        console.log(`Client for ${userId} is currently destroying. Skipping initialization.`);
        return null;
    }

    console.log(`Initializing WhatsApp Client for User: ${userId}`);
    statuses[userId] = 'INITIALIZING';

    const fs = require('fs');
    let puppeteerConfig = {
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-first-run',
            '--no-zygote',
            '--disable-features=IsolateOrigins,site-per-process',
            '--disable-site-isolation-trials'
        ],
        headless: true
    };
    
    if (process.platform === 'win32') {
        let winChrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
        let winEdge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
        if (fs.existsSync(winChrome)) {
            puppeteerConfig.executablePath = winChrome;
        } else if (fs.existsSync(winEdge)) {
            puppeteerConfig.executablePath = winEdge;
        }
    } else {
        let linuxChrome = '/usr/bin/google-chrome';
        let linuxChromium = '/usr/bin/chromium-browser';
        let linuxChromiumAlt = '/usr/bin/chromium';
        if (fs.existsSync(linuxChrome)) {
            puppeteerConfig.executablePath = linuxChrome;
        } else if (fs.existsSync(linuxChromium)) {
            puppeteerConfig.executablePath = linuxChromium;
        } else if (fs.existsSync(linuxChromiumAlt)) {
            puppeteerConfig.executablePath = linuxChromiumAlt;
        }
    }

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: userId }),
        puppeteer: puppeteerConfig,
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        webVersionCache: {
            type: 'local'
        }
    });

    client.on('qr', async (qr) => {
        console.log(`QR Code generated for user ${userId}`);
        statuses[userId] = 'QR_READY';
        try {
            const dataUrl = await qrcode.toDataURL(qr);
            qrCodes[userId] = dataUrl;
        } catch (err) {
            console.error('Failed to generate QR data URL', err);
        }
    });

    client.on('loading_screen', (percent, message) => {
        console.log(`WhatsApp Client for User ${userId} is LOADING SCREEN:`, percent, message);
        statuses[userId] = 'AUTHENTICATING';
        qrCodes[userId] = null;
    });

    client.on('ready', () => {
        console.log(`WhatsApp Client for User ${userId} is READY!`);
        statuses[userId] = 'CONNECTED';
        qrCodes[userId] = null; // clear qr code
    });

    client.on('authenticated', () => {
        console.log(`WhatsApp Client for User ${userId} is AUTHENTICATED!`);
        statuses[userId] = 'AUTHENTICATING';
    });

    client.on('auth_failure', msg => {
        console.error(`WhatsApp Auth Failure for User ${userId}:`, msg);
        statuses[userId] = 'AUTH_FAILED';
    });

    client.on('disconnected', async (reason) => {
        console.log(`WhatsApp Client for User ${userId} was DISCONNECTED:`, reason);
        statuses[userId] = 'DISCONNECTED';
        const cl = clients[userId];
        delete clients[userId];
        if (cl) {
            destroyingClients.add(userId);
            try {
                await cl.destroy();
            } catch (e) {
                console.error('Error destroying client on disconnect:', e.message);
            }
            setTimeout(() => {
                destroyingClients.delete(userId);
            }, 3000);
        }
    });

    client.initialize();
    clients[userId] = client;
    return client;
}

// 1. Get Status / Initialize
app.get('/wa/status', (req, res) => {
    const userId = req.query.userId || 'default';
    if (!clients[userId]) {
        initClient(userId);
    }
    const client = clients[userId];
    const phoneNumber = client && client.info && client.info.wid ? client.info.wid.user : null;
    res.json({
        userId,
        status: statuses[userId] || 'STARTING',
        phoneNumber
    });
});

// 2. Get QR Code
app.get('/wa/qr', (req, res) => {
    const userId = req.query.userId || 'default';
    const client = clients[userId];
    const phoneNumber = client && client.info && client.info.wid ? client.info.wid.user : null;
    res.json({
        userId,
        status: statuses[userId],
        qrCode: qrCodes[userId] || null,
        phoneNumber
    });
});

// 3. Send Message / PDF
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

    // Format phone number to standard WhatsApp format (add country code if missing)
    phone = normalizePhoneNumber(phone);

    if (!phone.endsWith('@c.us')) {
        phone = `${phone}@c.us`;
    }

    try {
        const client = clients[userId];
        console.log(`Attempting to send message to ${phone}`);

        // If there's a file attached
        if (req.file) {
            const numberId = await client.getNumberId(phone);
            const finalPhone = numberId ? numberId._serialized : phone;
            
            const media = MessageMedia.fromFilePath(req.file.path);
            await client.sendMessage(finalPhone, media, { caption: message });
            
            // Clean up the temp file asynchronously
            const fs = require('fs');
            fs.unlink(req.file.path, () => {});
        } else {
            // Just text (OTP/Notification) - send directly for maximum speed and instant delivery
            await client.sendMessage(phone, message);
        }

        res.json({ success: true, message: 'Sent successfully!' });
    } catch (err) {
        console.error('Send Error:', err);
        res.status(500).json({ error: 'Failed to send message', details: err.message });
    }
});

// 4. Logout
app.post('/wa/logout', async (req, res) => {
    const userId = req.body.userId || req.query.userId || 'default';
    const client = clients[userId];
    if (client) {
        destroyingClients.add(userId);
        statuses[userId] = 'DISCONNECTING';
        delete clients[userId];
        try {
            try {
                await client.logout();
            } catch (e) {
                console.error('Error logging out client:', e.message);
            }
            try {
                await client.destroy();
            } catch (e) {
                console.error('Error destroying client on logout:', e.message);
            }
            statuses[userId] = 'LOGGED_OUT';
            qrCodes[userId] = null;
            res.json({ success: true, message: 'Logged out successfully.' });
        } catch (err) {
            console.error('Logout main error:', err);
            res.status(500).json({ error: 'Logout failed.' });
        } finally {
            setTimeout(() => {
                destroyingClients.delete(userId);
            }, 3000);
        }
    } else {
        res.json({ success: true, message: 'Not connected.' });
    }
});

const PORT = 3001;
app.listen(PORT, () => {
    console.log(`🚀 WhatsApp Microservice is running on port ${PORT}`);
    // Auto-initialize the default SaaS admin client so it's ready to send OTPs immediately on start
    initClient('default-saas-admin');
});
