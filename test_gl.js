const fs = require('fs');

const store = {};
global.localStorage = {
    getItem: (k) => store[k] || null,
    setItem: (k, v) => { store[k] = v; },
    removeItem: (k) => { delete store[k]; }
};
global.window = { location: { origin: 'http://localhost', pathname: '/index.php' } };
global.document = { addEventListener: () => {} };

const code = fs.readFileSync('js/app.js', 'utf8');
const execCode = code.replace(/^\(function \(\) \{/m, '').replace(/\}\)\(\);$/m, '');
eval(execCode);

global.window.getTenantKey = (k) => k;
global.getTenantKey = (k) => k;
global.parseNum = (n) => parseFloat(n) || 0;
global.getEffectiveRate = (v, r) => r;
global.resolveAccount = (a, def) => a || def;
global.timeNow = '12:00';

store['erp_payments_v1'] = JSON.stringify([{
    "id": "MV-TEST1",
    "voucherNo": "MV-TEST1",
    "isGrouped": true,
    "mainAccount": "BANK-001",
    "currency": "SDG",
    "totalAmount": 1000,
    "items": [{
        "account": "SUPPLIER-001",
        "amount": 1000,
        "paymentMethods": ["bank"],
        "bankAmount": 1000,
        "bankTransfers": [{
            "amount": 1000,
            "bankName": "Faisal",
            "transferNo": "123456"
        }]
    }]
}]);

rebuildGLFromAllTables(true);
console.log(JSON.stringify(JSON.parse(store['erp_gl_v1']).filter(t => t.ref === 'MV-TEST1'), null, 2));
