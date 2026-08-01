const fs = require('fs');

const files = [
    'js/cash-receipt.js',
    'js/cash-payment.js',
    'js/single-cash-payment.js',
    'js/multi-cash-payment.js',
    'js/comprehensive-payment.js',
    'js/comprehensive-receipt.js'
];

files.forEach(file => {
    if (!fs.existsSync(file)) return;
    
    let content = fs.readFileSync(file, 'utf8');
    let changed = false;

    // Replace the division
    if (content.includes('eq = rate === 0 ? 0 : amt / rate;')) {
        content = content.replace(
            /eq = rate === 0 \? 0 : amt \/ rate;/g,
            `let actualRateForForeign = rate;
        if (rate === 1 && typeof window.getExchangeRateForCurrency === 'function') {
            const globalRate = window.getExchangeRateForCurrency('USD');
            if (globalRate && globalRate > 1) actualRateForForeign = globalRate;
            else if (typeof window.getTenantKey === 'function') {
                const settings = JSON.parse(localStorage.getItem(window.getTenantKey('erp_settings_v1')) || localStorage.getItem('erp_settings_v1')) || {};
                if (settings.exchangeRate && settings.exchangeRate > 1) actualRateForForeign = settings.exchangeRate;
            }
        }
        eq = actualRateForForeign === 0 ? 0 : amt / actualRateForForeign;`
        );
        changed = true;
    }

    // Also in multi-cash-payment.js line 819: let eq = rate === 0 ? 0 : total / rate;
    if (content.includes('let eq = rate === 0 ? 0 : total / rate;')) {
        content = content.replace(
            /let eq = rate === 0 \? 0 : total \/ rate;/g,
            `let actualRateForForeign = rate;
        if (rate === 1 && typeof window.getExchangeRateForCurrency === 'function') {
            const globalRate = window.getExchangeRateForCurrency('USD');
            if (globalRate && globalRate > 1) actualRateForForeign = globalRate;
        }
        let eq = actualRateForForeign === 0 ? 0 : total / actualRateForForeign;`
        );
        changed = true;
    }

    // Replace the multiplication
    if (content.includes('amt = eq * rate;')) {
        content = content.replace(
            /amt = eq \* rate;/g,
            `amt = eq * (typeof actualRateForForeign !== 'undefined' ? actualRateForForeign : rate);`
        );
        changed = true;
    }

    if (changed) {
        fs.writeFileSync(file, content);
        console.log('Modified ' + file);
    }
});
