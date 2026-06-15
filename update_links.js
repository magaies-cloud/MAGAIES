const fs = require('fs');
const path = require('path');

// 1. Update CSS to ensure cursor: pointer for a[onclick]
const cssPath = 'c:/xampp/htdocs/magaees/css/components.css';
let cssContent = fs.readFileSync(cssPath, 'utf8');
if (!cssContent.includes('a[data-href]')) {
    cssContent += '\n\n/* Ensure a without href have pointer cursor */\na[data-href], a[onclick] { cursor: pointer; }\n';
    fs.writeFileSync(cssPath, cssContent, 'utf8');
}

// 2. Process all HTML files
const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updatedCount = 0;

for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // Replace href="something.html" with data-href="something.html" onclick="..."
    content = content.replace(/href="([a-zA-Z0-9_-]+\.html)"/g, 'data-href="$1" onclick="window.location.href=\'$1\'"');
    
    // Also handle href="something.html?param=1" if any
    content = content.replace(/href="([a-zA-Z0-9_-]+\.html\?[^"]*)"/g, 'data-href="$1" onclick="window.location.href=\'$1\'"');

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updatedCount++;
    }
}

console.log('Replaced href with onclick in ' + updatedCount + ' HTML files.');
