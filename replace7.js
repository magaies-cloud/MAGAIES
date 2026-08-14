const fs = require('fs');

function replaceInFile(file) {
    let data = fs.readFileSync(file, 'utf8');
    if (data.includes('حساب دائن')) {
        data = data.replace(/حساب دائن/g, 'حساب صرف');
        fs.writeFileSync(file, data);
        console.log('Replaced in ' + file);
    }
}

replaceInFile('single-cash-payment.php');
replaceInFile('multi-cash-payment.php');
