<?php
$filePath = __DIR__ . '/../js/balances-report.js';
$content = file_get_contents($filePath);

// Wrap loadData
$loadDataStart = "function loadData() {";
$loadDataStartReplacement = "function loadData() {\n    try {";

$loadDataEnd = "        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value=\"\${cc}\">{\${cc}}</option>`;
        });
    }
}";
$loadDataEndReplacement = "        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value=\"\${cc}\">{\${cc}}</option>`;
        });
    }
    } catch (e) {
        alert('Error in loadData: ' + e.message + '\\n' + e.stack);
        console.error(e);
    }
}";

// We can just use string replace for function body
$content = preg_replace('/function loadData\(\) \{/', "function loadData() {\n    try {", $content, 1);

// Find the end of loadData
// It ends right before `document.addEventListener('DBReady', () => {`
$content = str_replace(
"    if(ccSelect) {
        ccSelect.innerHTML = '<option value=\"ALL\">جميع مراكز التكلفة (مجمعة)</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value=\"\${cc}\">{\${cc}}</option>`;
        });
    }
}",
"    if(ccSelect) {
        ccSelect.innerHTML = '<option value=\"ALL\">جميع مراكز التكلفة (مجمعة)</option>';
        Array.from(ccSet).forEach(cc => {
            ccSelect.innerHTML += `<option value=\"\${cc}\">{\${cc}}</option>`;
        });
    }
    } catch(e) {
        alert('Error in loadData: ' + e.message + '\\n' + e.stack);
        console.error('loadData error:', e);
    }
}", $content);

// Wrap runReport
$content = preg_replace('/function runReport\(\) \{/', "function runReport() {\n    try {", $content, 1);

// Find the end of runReport
// It ends right before `function openWhatsAppModal() {`
$content = str_replace(
"    renderTable(results);
}",
"    renderTable(results);
    } catch(e) {
        alert('Error in runReport: ' + e.message + '\\n' + e.stack);
        console.error('runReport error:', e);
    }
}", $content);

file_put_contents($filePath, $content);
echo "Added try-catch correctly.\n";
