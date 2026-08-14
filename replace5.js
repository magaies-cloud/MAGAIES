const fs = require('fs');
let data = fs.readFileSync('js/single-cash-payment.js', 'utf8');

const searchStr = `<div class="dynamic-focus-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px;">
            
            <input type="text" maxlength="14" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="\${no}" placeholder="رقم التحويلة" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 48px; font-size:1.5rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#2dd4bf; text-align: center; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900;">
        </div>`;

const replaceStr = `<div class="dynamic-focus-container" style="flex: 1; min-width: 200px; display: flex; align-items: center; gap: 5px; transition: all 0.3s ease;">
            
            <input type="text" maxlength="14" class="v-input ctrl-input transfer-no dynamic-focus-input dynamic-focus-number" value="\${no}" placeholder="رقم التحويلة" onfocus="this.parentElement.style.flex='10'; this.parentElement.style.minWidth='320px'; this.style.fontSize='1.8rem';" onblur="this.parentElement.style.flex='1'; this.parentElement.style.minWidth='200px'; this.style.fontSize='1.5rem';" style="background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.1); height: 48px; font-size:1.5rem; padding: 0 8px; width: 100%; border-radius: 6px; color:#2dd4bf; text-align: center; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; font-weight: 900; transition: all 0.3s ease;">
        </div>`;

if (data.includes(searchStr)) {
    data = data.replace(searchStr, replaceStr);
    fs.writeFileSync('js/single-cash-payment.js', data);
    console.log('done');
} else {
    console.log('Search string not found!');
}
