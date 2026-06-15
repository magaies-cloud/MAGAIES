const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updated = 0;
for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    
    let original = content;
    // Replace all js/app.js?v=... with js/app.js?v=20260615
    content = content.replace(/js\/app\.js(\?v=[a-zA-Z0-9_]+)?/g, 'js/app.js?v=20260615_v2');
    
    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updated++;
    }
}
console.log('Updated cache buster in ' + updated + ' files.');
