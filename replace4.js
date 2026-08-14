const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

// Upgrade transfer-no onfocus to enforce minWidth and even larger flex
data = data.replace(/onfocus="this\.parentElement\.style\.flex='4'; this\.style\.fontSize='2rem';" onblur="this\.parentElement\.style\.flex='1'; this\.style\.fontSize='1\.5rem';"/g,
    'onfocus="this.parentElement.style.flex=\'10\'; this.parentElement.style.minWidth=\'380px\'; this.style.fontSize=\'2.2rem\'; this.style.letterSpacing=\'2px\';" onblur="this.parentElement.style.flex=\'1\'; this.parentElement.style.minWidth=\'200px\'; this.style.fontSize=\'1.5rem\'; this.style.letterSpacing=\'normal\';"');

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
