const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updated = 0;
for (const file of files) {
    if (file === 'inventory-report.html') continue;
    
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // Standardize specific classes
    const classesToUpdate = ['hub-container', 'dash-main', 'report-container', 'jobs-container', 'cycle-container', 'statement-container', 'transfer-container'];
    
    classesToUpdate.forEach(cls => {
        let regex = new RegExp(`(\\.${cls}\\s*\\{[^}]*?)max-width:\\s*\\d+px;`, 'g');
        content = content.replace(regex, '$1max-width: 1600px;');
        
        regex = new RegExp(`(\\.${cls}\\s*\\{[^}]*?)padding:\\s*\\d+px(?:\\s+\\d+px)?;`, 'g');
        content = content.replace(regex, '$1padding: 20px 30px;');
        
        regex = new RegExp(`(\\.${cls}\\s*\\{[^}]*?)margin:\\s*0\\s+auto;`, 'g');
        // already has margin, let it be.
    });

    // Handle balances-report and general-ledger inline styles
    content = content.replace(/max-width:\s*1300px;\s*margin:\s*0\s*auto;\s*padding:\s*20px;\s*padding-bottom:\s*80px;/g, 
        'max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 80px;');

    content = content.replace(/max-width:\s*1100px;\s*margin:\s*0\s*auto;\s*padding-bottom:\s*100px;/g, 
        'max-width: 1600px; margin: 0 auto; padding: 20px 30px; padding-bottom: 100px;'); // journal-voucher

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updated++;
        console.log('Updated: ' + file);
    }
}
console.log('Total updated files: ' + updated);
