const fs = require('fs');
let data = fs.readFileSync('single-cash-payment.php', 'utf8');

data = data.replace(/من حساب <span style="color:#ffffff !important;">\(دائن\)<\/span>/g, 'حساب صرف');

fs.writeFileSync('single-cash-payment.php', data);
console.log('done');
