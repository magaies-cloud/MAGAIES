const fs = require('fs');
let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'utf8');
content = content.replace("btn.title = '🔒 معاينة وتعديل التنسيق بهذا النموذج الافتراضي';", "btn.title = 'قالب ضبط مصنع مقفل (للعرض فقط)';");
fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', content, 'utf8');
