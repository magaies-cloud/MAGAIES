const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

data = data.replace(
    /onfocus="this.parentElement.style.flex='10'; this.parentElement.style.minWidth='350px'; this.style.fontSize='1.8rem'; this.style.letterSpacing='2px';"/g,
    'onfocus="this.parentElement.style.flex=\'10\'; this.parentElement.style.minWidth=\'420px\'; this.style.fontSize=\'1.55rem\'; this.style.letterSpacing=\'1px\';"'
);

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
