const fs = require('fs');

function fixLedger() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', 'utf8');
    
    // Fix executeLongPressSave
    let target1 = "    if (isDefaultGroup) {\n        // Save updated Factory Reset settings without hard-locking future edits";
    let repl1 = "    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (isDefaultGroup) {\n        // Save updated Factory Reset settings without hard-locking future edits";
    content = content.replace(target1, repl1);
    
    // Fix startLongPressCount
    let target2 = "        if (isDefaultGroup) {\n            if (icon) icon.innerText = '🔒';\n            title.innerText = حفظ وتثبيت ضبط المصنع (\);";
    let repl2 = "        if (isDefaultGroup) {\n            alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n            modal.style.display = 'none';\n            return;\n        } else if (false) {\n            if (icon) icon.innerText = '🔒';\n            title.innerText = حفظ وتثبيت ضبط المصنع (\);";
    content = content.replace(target2, repl2);
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-ledger.js', content, 'utf8');
}

function fixBalances() {
    let content = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', 'utf8');
    
    // Fix executeLongPressSave
    let target1 = "    if (p.isLocked) {\n        if (typeof showToast === 'function') {";
    let repl1 = "    if (isDefaultGroup) {\n        alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n        return;\n    }\n\n    if (p.isLocked) {\n        if (typeof showToast === 'function') {";
    content = content.replace(target1, repl1);
    
    // Fix startLongPressCount
    let target2 = "        if (isDefaultGroup) {\n            hudIcon.innerText = '🔒';\n            hudTitle.innerText = حفظ وتثبيت ضبط المصنع (\);";
    let repl2 = "        if (isDefaultGroup) {\n            alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n            modal.style.display = 'none';\n            return;\n        } else if (false) {\n            hudIcon.innerText = '🔒';\n            hudTitle.innerText = حفظ وتثبيت ضبط المصنع (\);";
    content = content.replace(target2, repl2);
    
    // Fix promptConfirmationAndSave
    let target3 = "        if (isDefaultGroup) {\n            const confirmSave = confirm(🔒 اكتملت الـ 8 ثوانٍ بنجاح!\\n\\nهل تريد حفظ وتثبيت التعديلات الجديدة في (\) كـ \"ضبط مصنع نهائي\"؟);\n            if (confirmSave) executeLongPressSave(idx);\n        } else {";
    let repl3 = "        if (isDefaultGroup) {\n            alert('خطأ: لا يمكن تعديل قوالب ضبط المصنع الافتراضية نهائياً!');\n            modal.style.display = 'none';\n            return;\n        } else {";
    content = content.replace(target3, repl3);
    
    fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/js/preview-balances.js', content, 'utf8');
}

fixLedger();
fixBalances();
