const fs = require('fs');
let js = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'utf8');

const targetStr = "btn.className = 'preset-ribbon-btn preset-slot-' + idx;";

const replaceStr = "btn.className = 'luxury-action-btn';\n" +
"        let color = '#38bdf8';\n" +
"        if (idx === 1) color = '#eab308';\n" +
"        else if (idx === 2) color = '#a855f7';\n" +
"        else if (idx === 3) color = '#ef4444';\n" +
"        else if (idx === 4) color = '#10b981';\n" +
"        else if (idx === 5) color = '#f97316';\n" +
"        else if (idx === 6) color = '#ec4899';\n" +
"        btn.style.cssText = '--btn-color: ' + color + '; height: 42px; font-size: 1rem; padding: 5px 15px; border-radius: 12px;';";

if(js.includes(targetStr)) {
    js = js.replace(targetStr, replaceStr);
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', js, 'utf8');
    console.log('JS Updated!');
} else {
    console.log('Target not found in mainbalances-report.js.');
}
