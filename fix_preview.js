const fs = require('fs');

function fixLedger() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', 'utf8');
    let regex = /const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\('افتراضي'\)\);\s*if \(isDefaultGroup\) \{[\s\S]*?p\.configs = JSON\.parse\(JSON\.stringify\(columnConfigs\)\);/;
    let replacement = "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));\n\n    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (isDefaultGroup) {\n        p.configs = JSON.parse(JSON.stringify(columnConfigs));";
    content = content.replace(regex, replacement);
    
    regex = /if \(isDefaultGroup\) \{[\s\S]*?\} else \{/;
    replacement = "if (isDefaultGroup) {\n            alert(خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!);\n            modal.style.display = 'none';\n            return;\n        } else {";
    content = content.replace(regex, replacement);
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', content, 'utf8');
}

function fixBalances() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', 'utf8');
    let regex = /const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\('افتراضي'\)\);\s*if \(p\.isLocked\) \{/;
    let replacement = "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));\n\n    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (p.isLocked) {";
    content = content.replace(regex, replacement);
    
    regex = /if \(isDefaultGroup\) \{[\s\S]*?\} else \{/;
    replacement = "if (isDefaultGroup) {\n            alert(خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!);\n            modal.style.display = 'none';\n            return;\n        } else {";
    content = content.replace(regex, replacement);
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', content, 'utf8');
}

fixLedger();
fixBalances();
