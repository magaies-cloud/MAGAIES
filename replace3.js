const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

// Upgrade transfer-no onfocus to also increase font size
data = data.replace(/onfocus="this\.parentElement\.style\.flex='3'" onblur="this\.parentElement\.style\.flex='1'"/g,
    'onfocus="this.parentElement.style.flex=\'4\'; this.style.fontSize=\'2rem\';" onblur="this.parentElement.style.flex=\'1\'; this.style.fontSize=\'1.5rem\';"');

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
