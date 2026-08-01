const fs = require('fs');
let code = fs.readFileSync('js/general-ledger.js', 'utf8');
code = code.replace(/typeof ledgerExchangeRate !== "undefined" \? ledgerExchangeRate : 1/g, 'typeof ledgerExchangeRate !== "undefined" && ledgerExchangeRate !== 0 ? ledgerExchangeRate : 1');
fs.writeFileSync('js/general-ledger.js', code, 'utf8');

let code2 = fs.readFileSync('js/preview-ledger.js', 'utf8');
code2 = code2.replace(/typeof ledgerExchangeRate !== "undefined" \? ledgerExchangeRate : 1/g, 'typeof ledgerExchangeRate !== "undefined" && ledgerExchangeRate !== 0 ? ledgerExchangeRate : 1');
fs.writeFileSync('js/preview-ledger.js', code2, 'utf8');

console.log("Replaced successfully!");
