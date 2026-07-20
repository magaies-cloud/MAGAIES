<?php
function fixRadioCheck($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    // single-cash-payment.js, single-cash-receipt.js
    $oldStr = 'document.querySelector(`input[name="payment_method"][value="${method}"]`).checked = true;';
    $newStr = 'const methodEl = document.querySelector(`input[name="payment_method"][value="${method}"]`); if (methodEl) { methodEl.checked = true; } else { /* Fallback */ const firstMethod = document.querySelector(`input[name="payment_method"]`); if(firstMethod) firstMethod.checked = true; }';
    $c = str_replace($oldStr, $newStr, $c);
    file_put_contents($file, $c);
}

fixRadioCheck('js/single-cash-payment.js');
fixRadioCheck('js/single-cash-receipt.js');

echo "JS radio check fixed.\n";
