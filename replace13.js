const fs = require('fs');
let data = fs.readFileSync('single-cash-payment.php', 'utf8');

// Ensure box-sizing and explicit heights for local
data = data.replace(
    /id="amount-local"[^>]*style="height:\s*72px;([^"]*)"/g,
    'id="amount-local" data-next="from-account" autofocus value="0" inputmode="numeric" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'local\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'local\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 72px !important; box-sizing: border-box !important; font-size: 2.35rem !important; font-weight: 900; text-align: center; font-family: \'Inter\', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px;"'
);

// Ensure box-sizing and explicit heights for foreign
data = data.replace(
    /id="amount-foreign"[^>]*style="height:\s*72px;([^"]*)"/g,
    'id="amount-foreign" data-next="from-account" value="0" inputmode="decimal" readonly tabindex="-1" onfocus="this.select()" oncontextmenu="showNumberProperties(event, this)" onblur="formatAmount(this); if(typeof calcEquiv === \'function\') calcEquiv(\'foreign\');" onkeydown="if(event.key.length===1 && !/[0-9.]/.test(event.key)) event.preventDefault();" oninput="let val = this.value.replace(/[^0-9.]/g, \'\'); this.value = val; calcEquiv(\'foreign\'); if(typeof adjustAmountFontSize === \'function\') adjustAmountFontSize(this); if(this.getAttribute(\'data-thousands\') !== \'false\') { let parts = val.split(\'.\'); parts[0] = parts[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, \',\'); this.value = parts.join(\'.\'); }" style="height: 72px !important; box-sizing: border-box !important; font-size: 2.35rem !important; text-align: center; background: #fafafa !important; color: #ef4444 !important; font-weight: 900; font-family: \'Inter\', sans-serif; font-variant-numeric: tabular-nums; letter-spacing: 2px; direction: ltr; border-radius: 10px; width: 100%; transition: font-size 0.2s ease, letter-spacing 0.2s ease; padding-left: 60px; cursor: not-allowed; border: 1px solid #fca5a5;"'
);

fs.writeFileSync('single-cash-payment.php', data);
console.log('done');
