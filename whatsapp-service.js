const express = require('express');
const cors = require('cors');
const multer = require('multer');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');

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

// Helper to initialize a client for a user
function initClient(userId) {
    if (clients[userId]) return clients[userId];

    console.log(`Initializing WhatsApp Client for User: ${userId}`);
    statuses[userId] = 'INITIALIZING';

    const fs = require('fs');
    let execPath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    if (!fs.existsSync(execPath)) {
        execPath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
    }

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: userId }),
        puppeteer: { 
            executablePath: execPath,
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            headless: true
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

    client.on('ready', () => {
        console.log(`WhatsApp Client for User ${userId} is READY!`);
        statuses[userId] = 'CONNECTED';
        qrCodes[userId] = null; // clear qr code
    });

    client.on('authenticated', () => {
        console.log(`WhatsApp Client for User ${userId} is AUTHENTICATED!`);
        statuses[userId] = 'AUTHENTICATED';
    });

    client.on('auth_failure', msg => {
        console.error(`WhatsApp Auth Failure for User ${userId}:`, msg);
        statuses[userId] = 'AUTH_FAILED';
    });

    client.on('disconnected', (reason) => {
        console.log(`WhatsApp Client for User ${userId} was DISCONNECTED:`, reason);
        statuses[userId] = 'DISCONNECTED';
        delete clients[userId];
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
    res.json({
        userId,
        status: statuses[userId] || 'STARTING'
    });
});

// 2. Get QR Code
app.get('/wa/qr', (req, res) => {
    const userId = req.query.userId || 'default';
    res.json({
        userId,
        status: statuses[userId],
        qrCode: qrCodes[userId] || null
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

    // Format phone number to standard WhatsApp format (add country code if missing, e.g. default to +249 for Sudan if needed, but let's assume UI handles it or clean it)
    phone = phone.replace(/[^0-9]/g, '');
    if (!phone.endsWith('@c.us')) {
        phone = `${phone}@c.us`;
    }

    try {
        const client = clients[userId];
        console.log(`Attempting to send message to ${phone}`);

        const numberId = await client.getNumberId(phone);
        if (!numberId) {
            console.error('Number not registered on WhatsApp:', phone);
            return res.status(400).json({ error: 'الرقم غير مسجل في واتساب.' });
        }
        
        console.log(`Validated number ID: ${numberId._serialized}`);
        const finalPhone = numberId._serialized;

        // If there's a file attached
        if (req.file) {
            const media = MessageMedia.fromFilePath(req.file.path);
            await client.sendMessage(finalPhone, media, { caption: message });
            
            // Clean up the temp file asynchronously
            const fs = require('fs');
            fs.unlink(req.file.path, () => {});
        } else {
            // Just text
            await client.sendMessage(finalPhone, message);
        }

        res.json({ success: true, message: 'Sent successfully!' });
    } catch (err) {
        console.error('Send Error:', err);
        res.status(500).json({ error: 'Failed to send message', details: err.message });
    }
});

// 4. Logout
app.post('/wa/logout', async (req, res) => {
    const userId = req.body.userId || 'default';
    if (clients[userId]) {
        try {
            await clients[userId].logout();
            delete clients[userId];
            statuses[userId] = 'LOGGED_OUT';
            qrCodes[userId] = null;
            res.json({ success: true, message: 'Logged out successfully.' });
        } catch (err) {
            res.status(500).json({ error: 'Logout failed.' });
        }
    } else {
        res.json({ success: true, message: 'Not connected.' });
    }
});

const PORT = 3001;
app.listen(PORT, () => {
    console.log(`🚀 WhatsApp Microservice is running on port ${PORT}`);
});
