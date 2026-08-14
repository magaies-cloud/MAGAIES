const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

// Fix Bank Name width in both rows to be exactly 220px
data = data.replace(/<div style="flex: none; width: 200px; display: flex; align-items: center; gap: 5px;">\s*<input type="text" class="v-input ctrl-input transfer-note"/g, 
    '<div style="flex: none; width: 240px; display: flex; align-items: center; gap: 5px;">\n            <input type="text" class="v-input ctrl-input transfer-note"');

data = data.replace(/<div style="flex: 1; min-width: 80px; display: flex; align-items: center; gap: 5px;">\s*<input type="text" class="v-input ctrl-input cheque-branch"/g, 
    '<div style="flex: none; width: 240px; display: flex; align-items: center; gap: 5px;">\n            <input type="text" class="v-input ctrl-input cheque-branch"');

// For transfer-no, add onfocus and onblur to make it grow, and ensure it has transition
data = data.replace(/<div class="dynamic-focus-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px;">\s*<input type="text" maxlength="14" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="\$\{no\}" placeholder="رقم التحويلة"/g, 
    '<div class="dynamic-focus-container transfer-no-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px; transition: all 0.3s ease;">\n            <input type="text" maxlength="14" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم التحويلة" onfocus="this.parentElement.style.flex=\'3\'" onblur="this.parentElement.style.flex=\'1\'"');

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
