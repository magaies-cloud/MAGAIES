const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updated = 0;
for (const file of files) {
    if (file === 'inventory-report.html') continue; // Already correct
    
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Replace padding and max-width inside .dash-main and .hub-container blocks
    content = content.replace(/(\.(?:dash-main|hub-container)\s*\{[^}]*?)max-width:\s*100%;/g, '$1max-width: 1600px;');
    content = content.replace(/(\.(?:dash-main|hub-container)\s*\{[^}]*?)padding:\s*15px\s*20px;/g, '$1padding: 20px 30px;');

    // Ensure margin: 0 auto is present if we changed max-width
    content = content.replace(/(\.(?:dash-main|hub-container)\s*\{[^}]*?)max-width:\s*1600px;/g, (match) => {
        if (!match.includes('margin:')) {
            return match + ' margin: 0 auto;';
        }
        return match;
    });

    // 2. Fix purchase.html inline padding for hub-view
    content = content.replace(/id="purchase-hub-view" style="display:\s*flex;[^"]*padding:\s*15px;\s*overflow-y:\s*auto;"/g, 
        'id="purchase-hub-view" style="display: flex; flex-direction: column; height: 100%; gap: 20px; animation: fadeIn 0.5s ease-out; padding: 20px 30px; max-width: 1600px; margin: 0 auto; overflow-y: auto;"');

    // 3. Fix inventory.html search container and list
    if (file === 'inventory.html') {
        content = content.replace(/\.search-container\s*\{\s*padding:\s*20px;/g, '.search-container {\n            padding: 20px 30px;\n            max-width: 1600px;\n            margin: 0 auto;');
        content = content.replace(/\.inventory-list\s*\{\s*padding:\s*0\s*20px\s*100px\s*20px;/g, '.inventory-list {\n            padding: 0 30px 100px 30px;\n            max-width: 1600px;\n            margin: 0 auto;');
    }

    // 4. Any standalone inline max-width/padding container that looks like main content
    content = content.replace(/max-width:\s*100%;\s*margin:\s*0\s*auto;\s*padding:\s*15px\s*20px;/g, 
        'max-width: 1600px; margin: 0 auto; padding: 20px 30px;');

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updated++;
        console.log('Updated: ' + file);
    }
}
console.log('Total updated files: ' + updated);
