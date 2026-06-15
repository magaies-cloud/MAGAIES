const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updated = 0;
for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Remove asymmetrical margin from .premium-header
    content = content.replace(/margin:\s*15px\s*20px;\s*width:\s*calc\(100%\s*-\s*40px\);/g, 
        'margin-bottom: 20px; width: 100%;');

    // 2. Fix 100vw causing horizontal scrollbar on fixed elements
    content = content.replace(/width:\s*100vw;/g, 'width: 100%;');

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updated++;
        console.log('Updated: ' + file);
    }
}
console.log('Total updated files: ' + updated);
