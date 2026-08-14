const fs = require('fs');
let data = fs.readFileSync('single-cash-payment.php', 'utf8');

// For amount-local
data = data.replace(
    'id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'local\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'local\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 55px;',
    'id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'local\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'local\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 72px;'
);

// For amount-foreign
data = data.replace(
    'id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" readonly tabindex="-1" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'foreign\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'foreign\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 60px;',
    'id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" readonly tabindex="-1" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'foreign\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'foreign\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 78px;'
);

fs.writeFileSync('single-cash-payment.php', data);
console.log('done');
