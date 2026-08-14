const fs = require('fs');
let data = fs.readFileSync('single-cash-payment.php', 'utf8');

data = data.replace(
    '</svg>\n                                    </span>\n                                    حساب صرف\n                                </label>',
    '</svg>\n                                    </span>\n                                    من حساب <span style="color:#ffffff !important;">(صرف)</span>\n                                </label>'
);

fs.writeFileSync('single-cash-payment.php', data);
console.log('done');
