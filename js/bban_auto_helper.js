// ====================================================================
// Al-Sajana ERP 2026 - Universal Automated BBAN & IBAN Generator
// يولد رقم الآيبان والـ BBAN تلقائياً بمجرد إدخال اسم البنك ورقم الحساب
// ====================================================================

const SUDAN_BANK_MAP = [
    { code: '170', names: ['الخرطوم', 'بنكك', 'bok', 'bank of khartoum', '170'] },
    { code: '180', names: ['أم درمان', 'امدرمان', 'أوكاش', 'اوكاش', 'onb', 'omdurman', '180'] },
    { code: '190', names: ['فيصل', 'فوري', 'fib', 'faisal', '190'] },
    { code: '200', names: ['الأهلي', 'الاهلي', 'ahli', 'al ahli', '200'] },
    { code: '210', names: ['التضامن', 'tadamon', '210'] },
    { code: '220', names: ['العمال', 'workers', '220'] },
    { code: '230', names: ['الثروة الحيوانية', 'animal', '230'] },
    { code: '240', names: ['التنمية الصناعية', 'industrial', '240'] },
    { code: '250', names: ['النيلين', 'neelain', '250'] },
    { code: '260', names: ['المزارع', 'farmers', '260'] },
    { code: '270', names: ['الساحل والصحراء', 'bsic', '270'] },
    { code: '280', names: ['البركة', 'baraka', '280'] },
    { code: '290', names: ['السعودي السوداني', 'saudi sudanese', '290'] },
    { code: '300', names: ['المال المتحد', 'united capital', 'ucb', '300'] },
    { code: '310', names: ['الجزيرة', 'jazeera', 'jordanian', '310'] },
    { code: '320', names: ['النيل', 'nile bank', '320'] }
];

function detectBankCode(bankInput) {
    if (!bankInput) return '170';
    const clean = bankInput.toString().toLowerCase().trim();
    for (const b of SUDAN_BANK_MAP) {
        if (b.code === clean) return b.code;
        for (const n of b.names) {
            if (clean.includes(n)) return b.code;
        }
    }
    return '170'; // Default: Bank of Khartoum
}

function calculateSudanBBAN(bankInput, accountInput, branchInput = '00001') {
    const bankCode = detectBankCode(bankInput);
    
    // Clean account: remove non-digits, strip leading zeros
    let acc = (accountInput || '').toString().replace(/\D/g, '');
    acc = acc.replace(/^0+/, '');
    
    if (!acc) return '';
    
    // Pad to at least 6 digits
    while (acc.length < 6) {
        acc = '0' + acc;
    }
    
    let branch = (branchInput || '00001').toString().replace(/\D/g, '');
    branch = branch.padStart(5, '0');
    
    return bankCode + acc + branch;
}

// Auto-wire helper for any input elements
function bindAutoIBAN(bankInputSelector, accountInputSelector, ibanTargetSelector, branchSelector = null) {
    const bEl = typeof bankInputSelector === 'string' ? document.querySelector(bankInputSelector) : bankInputSelector;
    const aEl = typeof accountInputSelector === 'string' ? document.querySelector(accountInputSelector) : accountInputSelector;
    const iEl = typeof ibanTargetSelector === 'string' ? document.querySelector(ibanTargetSelector) : ibanTargetSelector;

    if (!bEl || !aEl || !iEl) return;

    function update() {
        const bVal = bEl.value;
        const aVal = aEl.value;
        const brVal = branchSelector ? (document.querySelector(branchSelector)?.value || '00001') : '00001';
        
        if (aVal) {
            const bban = calculateSudanBBAN(bVal, aVal, brVal);
            if (bban) {
                iEl.value = bban;
                iEl.dispatchEvent(new Event('input', { bubbles: true }));
                iEl.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    bEl.addEventListener('input', update);
    bEl.addEventListener('change', update);
    aEl.addEventListener('input', update);
    aEl.addEventListener('change', update);
}

if (typeof window !== 'undefined') {
    window.detectBankCode = detectBankCode;
    window.calculateSudanBBAN = calculateSudanBBAN;
    window.bindAutoIBAN = bindAutoIBAN;

    // Auto-bind across known ERP forms on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Master Data -> Banks Form
        bindAutoIBAN('#bank-name', '#bank-gl-account', '#bank-iban', '#bank-branch');
        bindAutoIBAN('#bank-name', '#bank-code', '#bank-iban', '#bank-branch');
    });
}
