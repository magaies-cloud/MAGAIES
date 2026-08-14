const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

// Replace the container to add transition
data = data.replace(
    '<div class="dynamic-focus-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px;">',
    '<div class="dynamic-focus-container transfer-no-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px; transition: all 0.3s ease;">'
);

// Add the onfocus/onblur
data = data.replace(
    'class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم التحويلة"',
    'class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="${no}" placeholder="رقم التحويلة" onfocus="this.parentElement.style.flex=\'10\'; this.parentElement.style.minWidth=\'350px\'; this.style.fontSize=\'1.8rem\'; this.style.letterSpacing=\'2px\';" onblur="this.parentElement.style.flex=\'1\'; this.parentElement.style.minWidth=\'200px\'; this.style.fontSize=\'1.5rem\'; this.style.letterSpacing=\'normal\';"'
);

// Add transition to the input itself
data = data.replace(
    'font-weight: 900;">',
    'font-weight: 900; transition: all 0.3s ease;">'
);

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
