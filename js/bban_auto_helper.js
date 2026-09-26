// ====================================================================
// Al-Sajana ERP 2026 - Universal Automated BBAN & IBAN Generator
// تفعيل فوري وشامل لتوليد رقم الآيبان والـ BBAN تلقائياً بمجرد إدخال اسم البنك ورقم الحساب
// ====================================================================

(function(window, document) {
    'use strict';

    // 🛡️ حماية تامة ضد التكرار (Idempotent Guard) تمنع إعادة الإعلان أو تعارض الدوال
    if (window.BBANAutoHelper && window.BBANAutoHelper.__initialized) {
        return;
    }

    const SUDAN_BANK_MAP = [
        { code: '170', names: ['الخرطوم', 'بنكك', 'bok', 'bank of khartoum', '170', '1'] },
        { code: '180', names: ['أم درمان', 'امدرمان', 'أوكاش', 'اوكاش', 'onb', 'omdurman', '180', '2'] },
        { code: '190', names: ['فيصل', 'فوري', 'fib', 'faisal', '190', '3'] },
        { code: '200', names: ['الأهلي', 'الاهلي', 'ahli', 'al ahli', '200', '4'] },
        { code: '210', names: ['التضامن', 'tadamon', '210', '5'] },
        { code: '220', names: ['العمال', 'workers', '220', '6'] },
        { code: '230', names: ['الثروة الحيوانية', 'animal', '230', '7'] },
        { code: '240', names: ['التنمية الصناعية', 'industrial', '240', '8'] },
        { code: '250', names: ['النيلين', 'neelain', '250', '9'] },
        { code: '260', names: ['المزارع', 'farmers', '260', '10'] },
        { code: '270', names: ['الساحل والصحراء', 'bsic', '270', '11'] },
        { code: '280', names: ['البركة', 'baraka', '280', '12'] },
        { code: '290', names: ['السعودي السوداني', 'saudi sudanese', '290', '13'] },
        { code: '300', names: ['المال المتحد', 'united capital', 'ucb', '300', '14'] },
        { code: '310', names: ['الجزيرة', 'jazeera', 'jordanian', '310', '15'] },
        { code: '320', names: ['النيل', 'nile bank', '320', '16'] }
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

    // Global real-time auto-binder
    function autoGenerateIBANInContext(container) {
        if (!container) container = document;
        if (!container || !container.querySelector) return;
        
        // Find all bank inputs and account inputs in container
        const bankInput = container.querySelector('#bank-name, [name*="bank"], .bank-name, select[id*="bank"]');
        const accInput = container.querySelector('#bank-gl-account, #bank-code, [name*="account"], [name*="acc"], .bank-acc, input[id*="acc"]');
        const ibanInput = container.querySelector('#bank-iban, [name*="iban"], .bank-iban, input[id*="iban"]');
        const branchInput = container.querySelector('#bank-branch, [name*="branch"], .bank-branch');

        if (accInput && ibanInput) {
            const bVal = bankInput ? (bankInput.value || bankInput.options?.[bankInput.selectedIndex]?.text || '') : '170';
            const aVal = accInput.value || '';
            const brVal = branchInput ? branchInput.value : '00001';
            
            if (aVal) {
                const bban = calculateSudanBBAN(bVal, aVal, brVal);
                if (bban) {
                    ibanInput.value = bban;
                }
            }
        }
    }

    // Attach event delegation across entire document
    if (typeof document !== 'undefined' && document && document.addEventListener) {
        document.addEventListener('input', function(e) {
            const target = e.target;
            if (!target) return;
            
            const id = (target.id || '').toLowerCase();
            const cls = (target.className || '').toLowerCase();
            const name = (target.name || '').toLowerCase();
            const placeholder = (target.placeholder || '').toLowerCase();
            
            if (id.includes('bank') || id.includes('acc') || id.includes('gl') ||
                cls.includes('bank') || cls.includes('acc') ||
                name.includes('bank') || name.includes('acc') ||
                placeholder.includes('حساب') || placeholder.includes('بنك')) {
                
                // Find parent row or form
                const form = target.closest('form, .form-grid, .md-card, .cust-bank-row, tr, .transfer-container, .tool-section') || document;
                autoGenerateIBANInContext(form);
            }
        });

        document.addEventListener('change', function(e) {
            const target = e.target;
            if (!target) return;
            const form = target.closest('form, .form-grid, .md-card, .cust-bank-row, tr, .transfer-container, .tool-section') || document;
            autoGenerateIBANInContext(form);
        });
    }

    if (typeof window !== 'undefined' && window) {
        window.SUDAN_BANK_MAP = SUDAN_BANK_MAP;
        window.detectBankCode = detectBankCode;
        window.calculateSudanBBAN = calculateSudanBBAN;
        window.autoGenerateIBANInContext = autoGenerateIBANInContext;
        window.BBANAutoHelper = {
            __initialized: true,
            map: SUDAN_BANK_MAP,
            detectBankCode: detectBankCode,
            calculateSudanBBAN: calculateSudanBBAN,
            autoGenerateIBANInContext: autoGenerateIBANInContext
        };
    }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this), typeof document !== 'undefined' ? document : null);
