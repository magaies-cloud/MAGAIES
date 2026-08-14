const fs = require('fs');
let data = fs.readFileSync('single-cash-payment.php', 'utf8');

// Replace using regex with whitespace flexibility
data = data.replace(
    /حساب صرف\s*<\/label>/,
    'من حساب <span style="color:#ffffff !important;">(صرف)</span>\n                                </label>'
);

fs.writeFileSync('single-cash-payment.php', data);
console.log('done');
