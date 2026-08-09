const fs = require('fs');
let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'utf8');

content = content.replace(/const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\([^)]+\)\);/, 
                 "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));");

content = content.replace(/btn\.innerHTML = p\.name \+ ' \?\?\?';\s*btn\.title = '([^']+)';/, 
                 "btn.innerHTML = p.name + ' 🔒';\n            btn.title = 'قالب ضبط مصنع مقفل (للعرض فقط)';");

content = content.replace(/btn\.innerHTML = p\.name \+ ' \?\?\?';\s*btn\.title = '([^']+)';/, 
                 "btn.innerHTML = p.name + ' 🔄';\n            btn.title = 'انقر لاسترجاع النموذج أو حفظ التعديلات 🔄';");

fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', content, 'utf8');
