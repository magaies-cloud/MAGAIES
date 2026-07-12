const fs = require('fs');

function fixBug() {
    const file = 'c:/ELSAJANA/magaies/js/app.js';
    let content = fs.readFileSync(file, 'utf8');

    // Find the formatting section
    const targetBlock = "if (el.tagName === 'INPUT' && document.activeElement !== el) {";
    
    if (content.includes(targetBlock)) {
        const replacement = `if (el.tagName === 'INPUT' && document.activeElement !== el) {
                        // SAFEGUARD: Do not format text fields that are not obviously numeric
                        const isSearch = el.id && el.id.toLowerCase().includes('search');
                        const isDesc = el.className.includes('desc') || el.className.includes('note') || el.className.includes('center');
                        if (isSearch || isDesc) return;
                        
                        // SAFEGUARD 2: Skip if it's the customer/account search input
                        if (el.placeholder && el.placeholder.includes('ابحث')) return;`;
                        
        content = content.replace(targetBlock, replacement);
        fs.writeFileSync(file, content);
        console.log("Bug fixed successfully in app.js.");
    } else {
        console.log("Could not find the target block in app.js.");
    }
}

fixBug();
