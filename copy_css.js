const fs = require('fs');
const glPhp = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/general-ledger.php', 'utf8');
const mbPhp = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', 'utf8');

// Extract CSS from general-ledger.php
const startStr = ".luxury-action-btn { position: relative;";
const endStr = ":root.light-theme .luxury-action-btn::after { background: linear-gradient(90deg, transparent, rgba(255,255,255,1), transparent); }";

const startIndex = glPhp.indexOf(startStr);
const endIndex = glPhp.indexOf(endStr) + endStr.length;

if(startIndex > -1 && endIndex > -1) {
    const cssBlock = glPhp.substring(startIndex, endIndex);
    
    // Inject into mainbalances-report.php right before "/* Ribbon Toolbar Styles */"
    if(!mbPhp.includes(startStr)) {
        const replaceTarget = "/* Ribbon Toolbar Styles */";
        const newMbPhp = mbPhp.replace(replaceTarget, "/* Luxury Action Btn */\n" + cssBlock + "\n\n        " + replaceTarget);
        fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', newMbPhp, 'utf8');
        console.log("CSS injected!");
    } else {
        console.log("CSS already exists!");
    }
} else {
    console.log("Could not find CSS block in general-ledger.php");
}
