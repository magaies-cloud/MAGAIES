const fs = require('fs');
let content = fs.readFileSync('js/general-ledger.js', 'utf8');
content = content.replace(/const (valFc|amtFc|debFc|credFc|cashFc|amtFcBt)\s*=\s*isBaseSDG\s*\?\s*0\s*:\s*([a-zA-Z0-9_]+);/g, 'const $1 = isBaseSDG ? ($2 / (rate && rate !== 1 ? rate : (typeof ledgerExchangeRate !== "undefined" ? ledgerExchangeRate : 1))) : $2;');
fs.writeFileSync('js/general-ledger.js', content);
console.log('Replacement complete.');
