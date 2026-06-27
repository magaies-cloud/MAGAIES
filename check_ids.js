const fs = require('fs');

const html = fs.readFileSync('purchase.html', 'utf8');
const js = fs.readFileSync('js/purchase-pro.js', 'utf8');

// Find all id="..." in HTML
const idRegex = /id=["']([^"']+)["']/g;
const htmlIds = new Set();
let match;
while ((match = idRegex.exec(html)) !== null) {
    htmlIds.add(match[1]);
}

// Find all getElementById in JS
const getElRegex = /getElementById\(['"]([^'"]+)['"]\)/g;
const missingIds = [];
while ((match = getElRegex.exec(js)) !== null) {
    const id = match[1];
    // Ignore template literals or dynamic ids if they contain $
    if (id.includes('${')) continue;
    
    if (!htmlIds.has(id)) {
        missingIds.push(id);
    }
}

console.log('Missing IDs referenced in JS but not found in HTML:', [...new Set(missingIds)]);
