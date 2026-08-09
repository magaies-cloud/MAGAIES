const fs = require('fs');

function lockExecute(file, searchStr) {
    let content = fs.readFileSync(file, 'utf8');
    let target = searchStr;
    let repl = searchStr + "\n\n    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }";
    if(content.includes(target)) {
        content = content.replace(target, repl);
        fs.writeFileSync(file, content, 'utf8');
        console.log("Locked " + file);
    } else {
        console.log("Could not find target in " + file);
    }
}

lockExecute('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));");
lockExecute('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));");

