const http = require('http');

const data = JSON.stringify({ username: "admin", password: "123456" });

const options = {
  hostname: '127.0.0.1',
  port: 3000,
  path: '/api_index.php/auth/login',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': data.length
  }
};

const req = http.request(options, res => {
  res.on('data', d => process.stdout.write(d));
});

req.on('error', error => console.error(error));
req.write(data);
req.end();
