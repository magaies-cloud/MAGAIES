const fs = require('fs');
let c = fs.readFileSync('js/direct-sales-v2.js', 'utf8');
c = c.replace(/â€”/g, '—')
     .replace(/Ù„/g, 'ل')
     .replace(/ÙŠ/g, 'ي')
     .replace(/Ù…/g, 'م')
     .replace(/Ùˆ/g, 'و')
     .replace(/Ù‡/g, 'ه')
     .replace(/Ù/g, 'ف');
fs.writeFileSync('js/direct-sales-v2.js', c);
console.log('Done!');
