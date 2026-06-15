const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updatedCount = 0;

for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    content = content.replace(/js\/app\.js\?v=20260615_v2/g, 'js/app.js?v=20260615_v3');

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updatedCount++;
    }
}

console.log('Bumped cache version to v3 in ' + updatedCount + ' files.');
