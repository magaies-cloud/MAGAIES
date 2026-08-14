const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

// Remove all labels inside dynamic rows
data = data.replace(/<label.*?<\/label>/g, '');

// Update placeholders to be clearer
data = data.replace(/placeholder="0\.00"/g, 'placeholder="المبلغ"');
data = data.replace(/placeholder="اسم البنك..."/g, 'placeholder="اسم البنك"');
data = data.replace(/placeholder="رقم التحويلة..."/g, 'placeholder="رقم التحويلة"');
data = data.replace(/placeholder="البيان..."/g, 'placeholder="البيان"');
data = data.replace(/placeholder="رقم الشيك..."/g, 'placeholder="رقم الشيك"');

fs.writeFileSync('js/single-cash-payment.js', data);
console.log('done');
