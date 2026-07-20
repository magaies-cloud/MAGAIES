<?php
$files = glob('js/*.js');
foreach($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, 'function getAccountName') !== false) {
        $c = preg_replace('/function getAccountName\([^}]+return cleanId \|\| idStr;\s*\}/s', "function getAccountName(id) {
    if(!id) return '';
    let idStr = String(id).trim();
    let found = (ACCOUNTS || []).find(a => String(a.id).toLowerCase() === idStr.toLowerCase());
    if (found) return found.name;

    let cleanId = idStr.toLowerCase().replace(/^(cust|supp|bank|safe|exp)-/, '');
    found = (ACCOUNTS || []).find(a => {
        const aId = String(a.id).toLowerCase();
        const cleanAId = aId.replace(/^(cust|supp|bank|safe|exp)-/, '');
        return aId === idStr.toLowerCase() || cleanAId === cleanId || aId.endsWith(idStr.toLowerCase()) || idStr.toLowerCase().endsWith(cleanAId);
    });
    if (found) return found.name;
    return idStr;
}", $c);
        file_put_contents($f, $c);
        echo "Fixed $f\n";
    }
}
