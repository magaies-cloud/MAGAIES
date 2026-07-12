const fs = require('fs');
const path = require('path');

function processPurchase() {
    const file = 'c:/ELSAJANA/magaies/purchase.php';
    let content = fs.readFileSync(file, 'utf8');

    // 1. Flex Layouts
    content = content.replace(/flex:\s*1\.5;\s*min-width:\s*200px;/g, 'flex: 3; min-width: 320px;');
    content = content.replace(/flex:\s*1\.6;\s*min-width:\s*220px;/g, 'flex: 1; min-width: 140px;');
    content = content.replace(/flex:\s*1;\s*min-width:\s*120px;/g, 'flex: 0.8; min-width: 100px;');

    // 2. Ledger Button Text and Class
    content = content.replace(
        /<button type="button" id="hdr-customer-ledger-btn"[^>]*>.*?<\/button>/g,
        `<button type="button" id="hdr-customer-ledger-btn" onclick="openGeneralLedgerForAccount(document.getElementById('hdr-customer').value)" class="ledger-btn-2027" title="دفتر الأستاذ العام">أستاذ</button>`
    );

    // 3. Adjust padding-left in search to fit the smaller "أستاذ" text
    content = content.replace(/padding-left:\s*95px\s*!important;/g, 'padding-left: 60px !important;');

    // 4. Inject CSS
    const customCss = `
    /* ================= 2027 LEDGER BUTTON DESIGN ================= */
    @keyframes neonPulse {
        0% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
        50% { box-shadow: 0 0 15px #38bdf8, 0 0 25px #38bdf8, inset 0 0 10px rgba(56, 189, 248, 0.8); border-color: #38bdf8; }
        100% { box-shadow: 0 0 5px #0ea5e9, 0 0 10px #0ea5e9, inset 0 0 5px rgba(14, 165, 233, 0.5); border-color: rgba(14, 165, 233, 0.8); }
    }

    @keyframes subtleShake {
        0% { transform: translateY(-50%) rotate(0deg); }
        25% { transform: translateY(-50%) rotate(0.8deg); }
        50% { transform: translateY(-50%) rotate(0deg); }
        75% { transform: translateY(-50%) rotate(-0.8deg); }
        100% { transform: translateY(-50%) rotate(0deg); }
    }

    .ledger-btn-2027 {
        position: absolute;
        left: 6px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        font-size: 0.8rem;
        font-weight: 800;
        padding: 0 10px;
        height: 26px;
        display: none; /* Displayed via JS when customer selected */
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(2, 132, 199, 0.3));
        color: #e0f2fe;
        border: 1px solid rgba(14, 165, 233, 0.5);
        border-radius: 6px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: neonPulse 2s infinite alternate, subtleShake 4s infinite ease-in-out;
        text-shadow: 0 0 4px rgba(255,255,255,0.4);
        cursor: pointer;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
    }

    .ledger-btn-2027::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.4) 0%, transparent 50%);
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.4s ease;
        pointer-events: none;
    }

    .ledger-btn-2027:hover::before {
        opacity: 0.3;
        transform: scale(1);
    }

    .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.4), rgba(2, 132, 199, 0.7));
        color: #ffffff;
        text-shadow: 0 0 8px #ffffff;
        box-shadow: 0 0 20px #0ea5e9, 0 0 30px #38bdf8, inset 0 0 15px rgba(56, 189, 248, 0.9);
        animation: subtleShake 1s infinite ease-in-out; /* Shake faster on hover */
    }

    :root.light-theme .ledger-btn-2027 {
        color: #0c4a6e;
        text-shadow: none;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(14, 165, 233, 0.4));
        border-color: #0ea5e9;
        animation: subtleShake 4s infinite ease-in-out; /* Removed pulse from light theme to avoid washed out look */
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4);
    }
    :root.light-theme .ledger-btn-2027:hover {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.8), rgba(2, 132, 199, 1));
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.6);
    }
    `;

    if (!content.includes('2027 LEDGER BUTTON DESIGN')) {
        content = content.replace('</style>', customCss + '\n</style>');
    }

    // 5. Hard Onkeypress Injection (Amounts only, NOT names or emails)
    const keypressScript = ' onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ';
    const injectKeypress = (html, idPrefix) => {
        const regex = new RegExp('(<input[^>]*id="' + idPrefix + '"[^>]*)(>)', 'g');
        return html.replace(regex, (match, p1, p2) => {
            if (!p1.includes('onkeypress')) {
                return p1 + keypressScript + p2;
            }
            return match;
        });
    };

    content = injectKeypress(content, 't-transport');
    content = injectKeypress(content, 't-transport-fc');
    content = injectKeypress(content, 't-labor');
    content = injectKeypress(content, 't-labor-fc');
    content = injectKeypress(content, 'addition-amt');
    content = injectKeypress(content, 'addition-amt-fc');
    content = injectKeypress(content, 'tax-amt');
    content = injectKeypress(content, 'tax-amt-fc');
    content = injectKeypress(content, 'discount-amt');
    content = injectKeypress(content, 'discount-amt-fc');
    content = injectKeypress(content, 'hdr-exchange-rate');

    fs.writeFileSync(file, content);
}

function processDirectSales() {
    const file = 'c:/ELSAJANA/magaies/direct-sales.php';
    let content = fs.readFileSync(file, 'utf8');

    // 1. Flex Layouts
    content = content.replace(/flex:\s*65;/g, 'flex: 70;');
    content = content.replace(/flex:\s*25;/g, 'flex: 15;');

    const keypressScript = ' onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ';
    const injectKeypress = (html, idPrefix) => {
        const regex = new RegExp('(<input[^>]*id="' + idPrefix + '"[^>]*)(>)', 'g');
        return html.replace(regex, (match, p1, p2) => {
            if (!p1.includes('onkeypress')) {
                return p1 + keypressScript + p2;
            }
            return match;
        });
    };

    content = injectKeypress(content, 't-transport');
    content = injectKeypress(content, 't-transport-fc');
    content = injectKeypress(content, 't-labor');
    content = injectKeypress(content, 't-labor-fc');
    content = injectKeypress(content, 'addition-amt');
    content = injectKeypress(content, 'addition-amt-fc');
    content = injectKeypress(content, 'tax-amt');
    content = injectKeypress(content, 'tax-amt-fc');
    content = injectKeypress(content, 'discount-amt');
    content = injectKeypress(content, 'discount-amt-fc');
    content = injectKeypress(content, 'hdr-exchange-rate');

    fs.writeFileSync(file, content);
}

function processJS(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');

    // Make sure table row inputs (prices/amounts) are blocked as well
    const inputsToFix = [
        "data-qty-input",
        "data-price-input",
        "data-fcprice-input",
        "data-base-input"
    ];

    inputsToFix.forEach(dataAttr => {
        const regex = new RegExp('(<input[^>]*' + dataAttr + '="\\\\\\$\\\\{row\\.id\\\\}"[^>]*)(>)', 'g');
        content = content.replace(regex, (match, p1, p2) => {
            if (!p1.includes('onkeypress')) {
                return p1 + ' onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" ' + p2;
            }
            return match;
        });
    });

    // Also block inline updates
    content = content.replace(/(updateRow\(\$\{row\.id\},'buyPrice',this\.value\)"[^>]*)(>)/g, '$1 onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" $2');
    content = content.replace(/(updateRow\(\$\{row\.id\},'sellPrice',this\.value\)"[^>]*)(>)/g, '$1 onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" $2');
    content = content.replace(/(updateRow\(\$\{row\.id\},'customFactor',this\.value\)"[^>]*)(>)/g, '$1 onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46" $2');

    fs.writeFileSync(filePath, content);
}

function processAppJs() {
    const appJsPath = 'c:/ELSAJANA/magaies/js/app.js';
    let appJsContent = fs.readFileSync(appJsPath, 'utf8');

    const globalNumericLogic = `
// ==========================================
// GLOBAL NUMERIC FIELD PROTECTION (IRONCLAD)
// ==========================================
document.addEventListener('keypress', function(e) {
    if (e.target && e.target.tagName === 'INPUT') {
        const t = e.target;
        const isNumField = (
            t.type === 'number' || 
            t.inputMode === 'decimal' || 
            t.inputMode === 'numeric' || 
            (t.id && (t.id.toLowerCase().includes('amount') || t.id.toLowerCase().includes('price') || t.id.toLowerCase().includes('qty') || t.id.toLowerCase().includes('amt') || t.id.toLowerCase().includes('total') || t.id.toLowerCase().includes('balance')))
        );

        if (isNumField) {
            const charCode = (e.which) ? e.which : e.keyCode;
            // Allow numbers (48-57), dot (46), minus (45)
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46 && charCode !== 45) {
                e.preventDefault();
            }
        }
    }
});

// Also prevent pasting letters
document.addEventListener('paste', function(e) {
    if (e.target && e.target.tagName === 'INPUT') {
        const t = e.target;
        const isNumField = (
            t.type === 'number' || 
            t.inputMode === 'decimal' || 
            t.inputMode === 'numeric' || 
            (t.id && (t.id.toLowerCase().includes('amount') || t.id.toLowerCase().includes('price') || t.id.toLowerCase().includes('qty') || t.id.toLowerCase().includes('amt') || t.id.toLowerCase().includes('total') || t.id.toLowerCase().includes('balance')))
        );
        if (isNumField) {
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            if (/[^0-9.-]/.test(paste)) {
                e.preventDefault();
            }
        }
    }
});
`;

    if (!appJsContent.includes('GLOBAL NUMERIC FIELD PROTECTION')) {
        appJsContent += '\n' + globalNumericLogic;
        fs.writeFileSync(appJsPath, appJsContent);
    }
}

try {
    processPurchase();
    processDirectSales();
    processJS('c:/ELSAJANA/magaies/js/purchase-pro-v4.js');
    processJS('c:/ELSAJANA/magaies/js/direct-sales-v3.js');
    processAppJs();
    console.log("UI layout, ledger button design, and strict numeric protections applied successfully.");
} catch (e) {
    console.error(e);
}
