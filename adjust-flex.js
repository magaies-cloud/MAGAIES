const fs = require('fs');

function adjustFlex() {
    const file = 'c:/ELSAJANA/magaies/purchase.php';
    let content = fs.readFileSync(file, 'utf8');

    // Current values from previous fix:
    // flex: 3; min-width: 320px; (Supplier)
    // flex: 1; min-width: 140px; (Exchange Rate)

    // New values:
    content = content.replace(/flex:\s*3;\s*min-width:\s*320px;/g, 'flex: 2; min-width: 250px;');
    content = content.replace(/flex:\s*1;\s*min-width:\s*140px;/g, 'flex: 1.8; min-width: 200px;');

    fs.writeFileSync(file, content);
    console.log("Flex layout adjusted in purchase.php");
}

adjustFlex();
