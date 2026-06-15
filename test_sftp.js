const Client = require('ssh2-sftp-client');
const sftp = new Client();

async function testConnection() {
    try {
        await sftp.connect({
            host: '147.182.214.194',
            port: 22,
            username: 'root',
            password: '1q2w3e4r5t'
        });
        console.log('SUCCESS: Connected with password!');
        await sftp.end();
    } catch (err) {
        console.error('FAILED:', err.message);
    }
}

testConnection();
