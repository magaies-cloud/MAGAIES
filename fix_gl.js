const fs = require('fs');
let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js', 'utf8');

const target = "btn.innerHTML = p.name || ('افتراضي ' + (idx + 1));";
const repl = "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));\n        btn.innerHTML = (p.name || ('افتراضي ' + (idx + 1))) + (isDefaultGroup ? ' 🔒' : '');";

content = content.replace(target, repl);
fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/general-ledger.js', content, 'utf8');
