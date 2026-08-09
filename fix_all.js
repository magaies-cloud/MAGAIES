const fs = require('fs');

function fixMainBalances() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'utf8');
    
    // Regex for the entire isDefaultGroup block in mainbalances-report.js
    const targetBlock = /if \(isDefaultGroup\) \{[\s\S]*?\}\n            };\n        \} else \{/;
    
    const replacement = "if (isDefaultGroup) {\n            btn.innerHTML = p.name + ' 🔒';\n            btn.title = 'قالب ضبط مصنع مقفل (للعرض فقط)';\n            \n            btn.onclick = (e) => {\n                if (e) e.preventDefault();\n                window.openPresetModal(idx, p.name);\n            };\n        } else {";
    
    if (content.match(targetBlock)) {
        content = content.replace(targetBlock, replacement);
        fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', content, 'utf8');
        console.log("mainbalances-report.js fixed successfully");
    } else {
        console.log("Could not find target block in mainbalances-report.js");
    }
}

function fixPreviewLedger() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', 'utf8');
    
    // Fix executeLongPressSave
    let target1 = /const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\('افتراضي'\)\);\n\n    if \(isDefaultGroup\) \{[\s\S]*?p\.configs = JSON\.parse\(JSON\.stringify\(columnConfigs\)\);/;
    let repl1 = "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));\n\n    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (isDefaultGroup) {\n        p.configs = JSON.parse(JSON.stringify(columnConfigs));";
    
    if (content.match(target1)) {
        content = content.replace(target1, repl1);
    } else {
        console.log("Could not find target1 in preview-ledger.js");
    }
    
    // Fix startLongPressCount
    let target2 = /if \(isDefaultGroup\) \{[\s\S]*?\} else \{/;
    let repl2 = "if (isDefaultGroup) {\n            alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n            modal.style.display = 'none';\n            return;\n        } else {";
    
    if (content.match(target2)) {
        content = content.replace(target2, repl2);
    } else {
        console.log("Could not find target2 in preview-ledger.js");
    }
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', content, 'utf8');
    console.log("preview-ledger.js fixed successfully");
}

function fixPreviewBalances() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', 'utf8');
    
    // Fix executeLongPressSave
    let target1 = /const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\('افتراضي'\)\);\n\n    if \(p\.isLocked\) \{/;
    let repl1 = "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('افتراضي'));\n\n    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (p.isLocked) {";
    
    if (content.match(target1)) {
        content = content.replace(target1, repl1);
    } else {
        console.log("Could not find target1 in preview-balances.js");
    }
    
    // Fix startLongPressCount
    let target2 = /if \(isDefaultGroup\) \{[\s\S]*?\} else \{/;
    let repl2 = "if (isDefaultGroup) {\n            alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n            modal.style.display = 'none';\n            return;\n        } else {";
    
    if (content.match(target2)) {
        content = content.replace(target2, repl2);
    } else {
        console.log("Could not find target2 in preview-balances.js");
    }
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', content, 'utf8');
    console.log("preview-balances.js fixed successfully");
}

fixMainBalances();
fixPreviewLedger();
fixPreviewBalances();
