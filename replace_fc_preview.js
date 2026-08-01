const fs = require('fs');

function replaceFcLogic(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    const regex = /const (valFc|amtFc|debFc|credFc|cashFc|amtFcBt|amtFcCh)\s*=\s*isBaseSDG\s*\?\s*0\s*:\s*([a-zA-Z0-9_]+);/g;
    content = content.replace(regex, 'const $1 = isBaseSDG ? ($2 / (rate && rate !== 1 ? rate : (typeof ledgerExchangeRate !== "undefined" ? ledgerExchangeRate : 1))) : $2;');
    fs.writeFileSync(filePath, content);
    console.log('Replacement complete for ' + filePath);
}

replaceFcLogic('js/preview-ledger.js');
// Let's also run it on general-ledger.js again just in case there were leftover variables like amtFcCh (which I noticed earlier).
replaceFcLogic('js/general-ledger.js');
