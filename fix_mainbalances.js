const fs = require('fs');
let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'utf8');

const regex = /if \(isDefaultGroup\) \{[\s\S]*?\} else \{/;
const replacement = "if (isDefaultGroup) {\n            btn.innerHTML = p.name + ' 🔒';\n            btn.title = 'قالب ضبط مصنع مقفل (للعرض فقط)';\n\n            btn.onclick = (e) => {\n                if (e) e.preventDefault();\n                window.openPresetModal(idx, p.name);\n            };\n        } else {";

content = content.replace(regex, replacement);

fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', content, 'utf8');
