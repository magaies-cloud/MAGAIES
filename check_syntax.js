const fs = require('fs');
const content = fs.readFileSync('saas-admin.html', 'utf8');
const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gm;
let match;
while ((match = regex.exec(content)) !== null) {
    const scriptContent = match[1];
    try {
        new Function(scriptContent);
        console.log("Syntax OK for script block");
    } catch (e) {
        console.error("Syntax Error in block:", e.message);
        const lines = scriptContent.split('\n');
        // print around error line
    }
}
