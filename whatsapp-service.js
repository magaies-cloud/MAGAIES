// CommonJS wrapper that launches the ESM whatsapp-service.mjs
// This file exists for backwards compatibility with server.js and api_index.php
// The actual service code is in whatsapp-service.mjs (ESM format required by Baileys v7)

const { spawn } = require('child_process');
const path = require('path');

const serviceFile = path.join(__dirname, 'whatsapp-service.mjs');
const child = spawn(process.execPath, [serviceFile], {
    stdio: 'inherit',
    cwd: __dirname
});

child.on('error', (err) => {
    console.error('Failed to start WhatsApp service:', err);
});

child.on('exit', (code) => {
    console.log(`WhatsApp service exited with code ${code}`);
    process.exit(code || 0);
});
