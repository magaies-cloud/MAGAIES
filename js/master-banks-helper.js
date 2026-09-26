/**
 * ====================================================================
 * Al-Sajana ERP 2026 - Master Banks Autocomplete & Integration Helper
 * مساعد الإكمال التلقائي لأسماء البنوك من البيانات الأساسية
 * ====================================================================
 * 
 * يربط جميع حقول "اسم البنك" في النظام تلقائياً بقائمة البنوك
 * المسجلة في البيانات الأساسية (erp_banks_v1) في النظام مع واجهة
 * منسدلة تفاعلية ذكية (Smart Floating Autocomplete) متوافقة تماماً
 * مع الشاشات المتعددة وسندات الصرف والقبض والشيكات والتحويلات.
 */

(function(window, document) {
    'use strict';

    // حماية من التكرار
    if (window.MasterBanksHelper && window.MasterBanksHelper.__initialized) {
        return;
    }

    // ═══════════════════════════════════════════════════════════════════
    // 1. البنوك الافتراضية الشائعة (Fallback Baseline)
    // ═══════════════════════════════════════════════════════════════════
    const DEFAULT_MASTER_BANKS = [
        { id: 'bank-bok', name: 'بنك الخرطوم (بنكك - Bankak)', branch: 'الفرع الرئيسي', code: '170', currency: 'SDG', securityLevel: 6, confidentiality: 6, keywords: ['بنكك', 'bankak', 'bok', 'الخرطوم', '170'] },
        { id: 'bank-fib', name: 'بنك فيصل الإسلامي (فوري - Fawry)', branch: 'الفرع الرئيسي', code: '190', currency: 'SDG', securityLevel: 3, confidentiality: 3, keywords: ['فوري', 'fawry', 'fib', 'فيصل', '190'] },
        { id: 'bank-onb', name: 'بنك أمدرمان الوطني (أوكاش - O-Cash)', branch: 'الفرع الرئيسي', code: '180', currency: 'SDG', securityLevel: 3, confidentiality: 3, keywords: ['اوكاش', 'أوكاش', 'ocash', 'onb', 'أمدرمان', 'امدرمان', '180'] },
        { id: 'bank-nile', name: 'بنك النيل (سريع)', branch: 'الفرع الرئيسي', code: '320', currency: 'SDG', keywords: ['سريع', 'النيل', 'nile', '320'] },
        { id: 'bank-workers', name: 'بنك العمال الوطني', branch: 'الفرع الرئيسي', code: '220', currency: 'SDG', keywords: ['العمال', 'workers', '220'] },
        { id: 'bank-baraka', name: 'بنك البركة السوداني', branch: 'الفرع الرئيسي', code: '280', currency: 'SDG', keywords: ['البركة', 'baraka', '280'] },
        { id: 'bank-farmers', name: 'مصرف المزارع التجاري', branch: 'الفرع الرئيسي', code: '260', currency: 'SDG', keywords: ['المزارع', 'farmers', '260'] },
        { id: 'bank-tadamon', name: 'بنك التضامن الإسلامي', branch: 'الفرع الرئيسي', code: '210', currency: 'SDG', keywords: ['التضامن', 'tadamon', '210'] },
        { id: 'bank-salam', name: 'مصرف السلام', branch: 'الفرع الرئيسي', code: '310', currency: 'SDG', keywords: ['السلام', 'salam'] },
        { id: 'bank-jazeera', name: 'بنك الجزيرة السوداني الأردني', branch: 'الفرع الرئيسي', code: '310', currency: 'SDG', keywords: ['الجزيرة', 'jazeera', 'الأردني'] },
        { id: 'bank-ahli', name: 'البنك الأهلي السوداني', branch: 'الفرع الرئيسي', code: '200', currency: 'SDG', keywords: ['الاهلي', 'الأهلي', 'ahli', '200'] },
        { id: 'bank-saudi', name: 'البنك السعودي السوداني', branch: 'الفرع الرئيسي', code: '290', currency: 'SDG', keywords: ['السعودي', 'saudi', '290'] },
        { id: 'bank-ucb', name: 'بنك المال المتحد', branch: 'الفرع الرئيسي', code: '300', currency: 'SDG', keywords: ['المال المتحد', 'ucb', '300'] },
        { id: 'bank-animal', name: 'بنك الثروة الحيوانية', branch: 'الفرع الرئيسي', code: '230', currency: 'SDG', keywords: ['الثروة', 'animal', '230'] },
        { id: 'bank-ind', name: 'بنك التنمية الصناعية', branch: 'الفرع الرئيسي', code: '240', currency: 'SDG', keywords: ['التنمية', 'الصناعية', 'industrial', '240'] },
        { id: 'bank-neelain', name: 'بنك النيلين', branch: 'الفرع الرئيسي', code: '250', currency: 'SDG', keywords: ['النيلين', 'neelain', '250'] },
        { id: 'bank-bsic', name: 'مصرف الساحل والصحراء (BSIC)', branch: 'الفرع الرئيسي', code: '270', currency: 'SDG', keywords: ['الساحل', 'الصحراء', 'bsic', '270'] },
        { id: 'bank-gulf', name: 'بنك الخليج', branch: 'الفرع الرئيسي', code: '330', currency: 'SDG', keywords: ['الخليج', 'gulf', '330'] },
        { id: 'bank-balad', name: 'مصرف البلد', branch: 'الفرع الرئيسي', code: '340', currency: 'SDG', keywords: ['البلد', 'balad', '340'] }
    ];

    // ═══════════════════════════════════════════════════════════════════
    // 2. إدارة البيانات (Master Data Storage & Fetching)
    // ═══════════════════════════════════════════════════════════════════
    let _cachedBanks = null;
    let _lastFetchTime = 0;
    let _activeInput = null;
    let _dropdownEl = null;
    let _datalistEl = null;
    let _activeIndex = -1;

    function getStorageKey() {
        return (typeof window.getTenantKey === 'function') 
            ? window.getTenantKey('erp_banks_v1') 
            : 'erp_banks_v1';
    }

    function normalizeArabic(text) {
        if (!text) return '';
        return text.toString().toLowerCase()
            .replace(/[أإآٱ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/[\u064B-\u065F]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    /**
     * استخراج قائمة البنوك المعتمدة من البيانات الأساسية
     */
    function loadMasterBanks(forceRefresh = false) {
        const now = Date.now();
        if (!forceRefresh && _cachedBanks && (now - _lastFetchTime < 3000)) {
            return _cachedBanks;
        }

        const tenantKey = getStorageKey();
        let raw = null;

        try {
            if (window.DB_STORE && window.DB_STORE[tenantKey]) {
                raw = window.DB_STORE[tenantKey];
            } else if (localStorage.getItem(tenantKey)) {
                raw = localStorage.getItem(tenantKey);
            } else if (window.DB_STORE && window.DB_STORE['erp_banks_v1']) {
                raw = window.DB_STORE['erp_banks_v1'];
            } else {
                raw = localStorage.getItem('erp_banks_v1');
            }
        } catch (e) {
            console.warn('[MasterBanksHelper] Error reading localStorage:', e);
        }

        let parsed = [];
        if (raw) {
            try {
                parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) parsed = [];
            } catch (e) {
                parsed = [];
            }
        }

        // استبعاد المحذوفات والخزائن
        const userBanks = parsed.filter(b => {
            if (!b || b.deleted || b.isInactive) return false;
            if (b.type && b.type === 'safe') return false;
            // Security Clearance Enforcement
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessEntity === 'function') {
                if (!window.MagaieesPermissions.canUserAccessEntity(b)) return false;
            }
            return true;
        }).map(b => ({
            id: b.id || ('bank-' + Math.random()),
            name: (b.name || b.bankName || '').trim(),
            branch: (b.branch || '').trim(),
            currency: b.currency || 'SDG',
            code: b.code || '',
            iban: b.iban || '',
            glAccount: b.glAccount || '',
            securityLevel: b.securityLevel || b.confidentiality || 1,
            isMasterCustom: true // معرف كبنك مسجل فعلياً من قبل المنشأة في البيانات الأساسية
        })).filter(b => b.name.length > 0);

        // دمج البنوك: بنوك المنشأة المسجلة أولاً، ثم استكمال البنوك السودانية الشائعة غير المكررة
        const combined = [];
        const seenNames = new Set();

        userBanks.forEach(b => {
            const norm = normalizeArabic(b.name);
            if (!seenNames.has(norm)) {
                seenNames.add(norm);
                combined.push(b);
            }
        });

        DEFAULT_MASTER_BANKS.forEach(db => {
            // Security Clearance Enforcement for default master banks
            if (window.MagaieesPermissions && typeof window.MagaieesPermissions.canUserAccessEntity === 'function') {
                if (!window.MagaieesPermissions.canUserAccessEntity(db)) return;
            }
            const norm = normalizeArabic(db.name);
            // مطابقة ذكية لتجنب التكرار حتى مع اختلاف التنسيق
            let exists = false;
            for (const sn of seenNames) {
                if (sn.includes(norm) || norm.includes(sn)) {
                    exists = true;
                    break;
                }
                if (db.keywords && db.keywords.some(kw => sn.includes(normalizeArabic(kw)))) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                seenNames.add(norm);
                combined.push({
                    ...db,
                    isMasterCustom: false
                });
            }
        });

        _cachedBanks = combined;
        _lastFetchTime = now;

        // خلفية: لو لم توجد بنوك مسجلة مسبقاً، نطلب التحديث من السيرفر بدون تعطيل الواجهة
        if (userBanks.length === 0 && now - _lastFetchTime > 10000) {
            syncFromBackendAsync();
        }

        return combined;
    }

    /**
     * مزامنة صامتة في الخلفية مع قاعدة بيانات المخدم (MySQL)
     */
    function syncFromBackendAsync() {
        if (!window.fetch) return;
        fetch('api/get_master_data.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.data && Array.isArray(data.data.banks) && data.data.banks.length > 0) {
                    const tenantKey = getStorageKey();
                    try {
                        localStorage.setItem(tenantKey, JSON.stringify(data.data.banks));
                        localStorage.setItem('erp_banks_v1', JSON.stringify(data.data.banks));
                        if (window.DB_STORE) {
                            window.DB_STORE[tenantKey] = JSON.stringify(data.data.banks);
                            window.DB_STORE['erp_banks_v1'] = JSON.stringify(data.data.banks);
                        }
                    } catch (e) {}
                    loadMasterBanks(true);
                    updateDatalist();
                }
            })
            .catch(() => {});
    }

    // ═══════════════════════════════════════════════════════════════════
    // 3. بناء وتحديث عنصر Datalist الأصلي للمتصفح
    // ═══════════════════════════════════════════════════════════════════
    function getOrCreateDatalist() {
        let dl = document.getElementById('master-banks-datalist');
        if (!dl) {
            dl = document.createElement('datalist');
            dl.id = 'master-banks-datalist';
            document.body.appendChild(dl);
        }
        _datalistEl = dl;
        return dl;
    }

    function updateDatalist() {
        const dl = getOrCreateDatalist();
        const banks = loadMasterBanks();
        let html = '';
        banks.forEach(b => {
            const label = b.branch ? ` (${b.branch})` : '';
            html += `<option value="${escapeHtml(b.name)}">${escapeHtml(b.name + label)}</option>`;
        });
        dl.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ═══════════════════════════════════════════════════════════════════
    // 4. بناء القائمة المنسدلة التفاعلية العائمة (Floating Autocomplete)
    // ═══════════════════════════════════════════════════════════════════
    function injectStyles() {
        if (document.getElementById('master-banks-helper-style')) return;
        const style = document.createElement('style');
        style.id = 'master-banks-helper-style';
        style.textContent = `
            #master-banks-dropdown {
                position: absolute;
                z-index: 1000000;
                width: 330px;
                max-width: 95vw;
                max-height: 380px;
                background: rgba(15, 23, 42, 0.98);
                border: 1.5px solid rgba(56, 189, 248, 0.45);
                border-radius: 12px;
                box-shadow: 0 20px 45px rgba(0, 0, 0, 0.75), 0 0 20px rgba(56, 189, 248, 0.2);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
                color: #f8fafc;
                direction: rtl;
                text-align: right;
                animation: masterBanksFadeIn 0.16s cubic-bezier(0.16, 1, 0.3, 1);
                box-sizing: border-box;
            }

            @keyframes masterBanksFadeIn {
                from { opacity: 0; transform: translateY(-6px) scale(0.98); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            .master-banks-header {
                padding: 10px 14px;
                background: rgba(30, 41, 59, 0.85);
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.8rem;
                font-weight: 700;
                color: #94a3b8;
                flex-shrink: 0;
            }
            .master-banks-header-title {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #38bdf8;
                font-weight: 800;
            }
            .master-banks-count {
                background: rgba(56, 189, 248, 0.2);
                color: #38bdf8;
                padding: 1px 7px;
                border-radius: 10px;
                font-size: 0.72rem;
                font-weight: 800;
            }
            .master-banks-hint {
                font-size: 0.72rem;
                color: #64748b;
            }

            .master-banks-list {
                overflow-y: auto;
                max-height: 270px;
                padding: 6px;
                margin: 0;
                list-style: none;
                flex: 1;
            }
            .master-banks-list::-webkit-scrollbar {
                width: 6px;
            }
            .master-banks-list::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.15);
            }
            .master-banks-list::-webkit-scrollbar-thumb {
                background: rgba(56, 189, 248, 0.35);
                border-radius: 4px;
            }

            .master-bank-item {
                padding: 9px 12px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                transition: all 0.15s ease;
                user-select: none;
                margin-bottom: 3px;
                border: 1px solid transparent;
            }
            .master-bank-item:hover, .master-bank-item.active {
                background: linear-gradient(90deg, rgba(6, 182, 212, 0.22), rgba(59, 130, 246, 0.18));
                border-color: rgba(56, 189, 248, 0.4);
                border-right: 3.5px solid #38bdf8;
                padding-right: 14px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);
            }

            .master-bank-icon {
                font-size: 1.3rem;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.06);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: all 0.15s ease;
            }
            .master-bank-item:hover .master-bank-icon, .master-bank-item.active .master-bank-icon {
                background: rgba(56, 189, 248, 0.25);
                transform: scale(1.08);
            }

            .master-bank-info {
                flex: 1;
                min-width: 0;
            }
            .master-bank-name {
                font-size: 0.95rem;
                font-weight: 800;
                color: #ffffff;
                display: flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .master-bank-item:hover .master-bank-name, .master-bank-item.active .master-bank-name {
                color: #38bdf8;
            }
            .master-bank-tag {
                font-size: 0.65rem;
                font-weight: 800;
                padding: 1px 6px;
                border-radius: 4px;
                background: rgba(16, 185, 129, 0.22);
                color: #34d399;
                border: 1px solid rgba(16, 185, 129, 0.4);
                white-space: nowrap;
            }
            .master-bank-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.76rem;
                color: #94a3b8;
                margin-top: 3px;
                flex-wrap: wrap;
            }
            .master-bank-branch {
                color: #cbd5e1;
            }
            .master-bank-currency {
                background: rgba(255, 255, 255, 0.08);
                padding: 1px 5px;
                border-radius: 3px;
                font-size: 0.7rem;
                font-weight: 800;
                color: #fbbf24;
            }

            .master-banks-footer {
                padding: 8px 14px;
                background: rgba(15, 23, 42, 0.95);
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-shrink: 0;
            }
            .master-banks-manage-link {
                color: #38bdf8;
                font-size: 0.78rem;
                font-weight: 800;
                text-decoration: none;
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                transition: color 0.2s ease;
                padding: 3px 0;
            }
            .master-banks-manage-link:hover {
                color: #7dd3fc;
                text-decoration: underline;
            }

            .master-banks-empty {
                padding: 24px 14px;
                text-align: center;
                color: #94a3b8;
                font-size: 0.88rem;
            }

            /* دعم الثيم الفاتح (Light Theme) */
            :root.light-theme #master-banks-dropdown,
            body.light-theme #master-banks-dropdown {
                background: #ffffff;
                border-color: #cbd5e1;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
                color: #0f172a;
            }
            :root.light-theme .master-banks-header,
            body.light-theme .master-banks-header {
                background: #f8fafc;
                border-bottom-color: #e2e8f0;
                color: #475569;
            }
            :root.light-theme .master-bank-name,
            body.light-theme .master-bank-name {
                color: #0f172a;
            }
            :root.light-theme .master-bank-item:hover,
            :root.light-theme .master-bank-item.active,
            body.light-theme .master-bank-item:hover,
            body.light-theme .master-bank-item.active {
                background: #f1f5f9;
                border-color: #cbd5e1;
                border-right-color: #0284c7;
            }
            :root.light-theme .master-banks-footer,
            body.light-theme .master-banks-footer {
                background: #f8fafc;
                border-top-color: #e2e8f0;
            }
        `;
        document.head.appendChild(style);
    }

    function getOrCreateDropdown() {
        if (_dropdownEl) return _dropdownEl;
        injectStyles();
        const drop = document.createElement('div');
        drop.id = 'master-banks-dropdown';
        drop.style.display = 'none';
        document.body.appendChild(drop);
        _dropdownEl = drop;
        return drop;
    }

    /**
     * حساب الموقع الهندسي للقائمة المنسدلة نسبة للحقل النشط
     */
    function positionDropdown(input) {
        if (!_dropdownEl || !input) return;
        const rect = input.getBoundingClientRect();
        const scrollX = window.scrollX || window.pageXOffset || 0;
        const scrollY = window.scrollY || window.pageYOffset || 0;

        const inputWidth = rect.width;
        const dropWidth = Math.max(inputWidth, 310);
        
        let left = rect.left + scrollX;
        let top = rect.bottom + scrollY + 4;

        // التحقق من تجاوز الحافة اليمنى للشاشة في وضع RTL
        if (left + dropWidth > window.innerWidth - 10) {
            left = Math.max(10, window.innerWidth - dropWidth - 10);
        }

        // التحقق من تجاوز الجزء السفلي من الشاشة (الظهور لأعلى الحقل إذا لزم الأمر)
        const estHeight = 280;
        if (rect.bottom + estHeight > window.innerHeight && rect.top > estHeight) {
            top = rect.top + scrollY - estHeight - 4;
        }

        _dropdownEl.style.top = `${top}px`;
        _dropdownEl.style.left = `${left}px`;
        _dropdownEl.style.width = `${dropWidth}px`;
    }

    /**
     * إظهار القائمة المنسدلة وترشيح البنوك
     */
    function showDropdown(input) {
        if (!input) return;
        _activeInput = input;
        _activeIndex = -1;

        const dropdown = getOrCreateDropdown();
        renderDropdownList(input.value || '');
        dropdown.style.display = 'flex';
        positionDropdown(input);

        // إزالة سمة list مؤقتاً أثناء فتح القائمة المنسدلة المخصصة لتجنب ظهور مستطيل الكروم الأبيض فوقها
        input.removeAttribute('list');
    }

    /**
     * إخفاء القائمة المنسدلة وإعادة ربط list للـ fallback
     */
    function hideDropdown() {
        if (_dropdownEl) {
            _dropdownEl.style.display = 'none';
        }
        if (_activeInput) {
            _activeInput.setAttribute('list', 'master-banks-datalist');
            _activeInput = null;
        }
        _activeIndex = -1;
    }

    /**
     * رسم بنود البنوك بناءً على نص البحث
     */
    function renderDropdownList(searchQuery) {
        const dropdown = getOrCreateDropdown();
        const banks = loadMasterBanks();
        const normQuery = normalizeArabic(searchQuery);

        let filtered = banks;
        if (normQuery.length > 0) {
            filtered = banks.filter(b => {
                const normName = normalizeArabic(b.name);
                const normBranch = normalizeArabic(b.branch);
                if (normName.includes(normQuery) || normBranch.includes(normQuery)) return true;
                if (b.keywords && b.keywords.some(kw => normalizeArabic(kw).includes(normQuery) || normQuery.includes(normalizeArabic(kw)))) {
                    return true;
                }
                return false;
            });
        }

        const countLabel = filtered.length > 0 ? `${filtered.length} بنك` : 'لا توجد نتائج';

        let listHtml = '';
        if (filtered.length === 0) {
            listHtml = `
                <div class="master-banks-empty">
                    <div style="font-size: 1.5rem; margin-bottom: 6px;">🔍</div>
                    <div style="font-weight: 700; color: #f87171;">لم يتم العثور على بنك مطابق لـ "${escapeHtml(searchQuery)}"</div>
                    <div style="font-size: 0.78rem; opacity: 0.8; margin-top: 6px;">يمكنك الاستمرار بكتابة هذا الاسم، أو إضافته إلى البيانات الأساسية لحفظه دائماً.</div>
                </div>
            `;
        } else {
            filtered.forEach((b, idx) => {
                const tagHtml = b.isMasterCustom 
                    ? `<span class="master-bank-tag">البيانات الأساسية</span>`
                    : `<span class="master-bank-tag" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">شائع</span>`;
                
                const branchHtml = b.branch 
                    ? `<span class="master-bank-branch">📍 ${escapeHtml(b.branch)}</span>` 
                    : '';

                const currHtml = b.currency 
                    ? `<span class="master-bank-currency">${escapeHtml(b.currency)}</span>` 
                    : '';

                listHtml += `
                    <div class="master-bank-item" data-index="${idx}" data-bank-name="${escapeHtml(b.name)}" data-bank-branch="${escapeHtml(b.branch || '')}">
                        <div class="master-bank-icon">🏦</div>
                        <div class="master-bank-info">
                            <div class="master-bank-name">
                                <span>${highlightMatch(b.name, normQuery)}</span>
                                ${tagHtml}
                            </div>
                            <div class="master-bank-meta">
                                ${branchHtml}
                                ${currHtml}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        dropdown.innerHTML = `
            <div class="master-banks-header">
                <div class="master-banks-header-title">
                    <span>🏛️ البنوك المعتمدة</span>
                    <span class="master-banks-count">${countLabel}</span>
                </div>
                <span class="master-banks-hint">اختر بنقرة أو بالأسهم ↵</span>
            </div>
            <div class="master-banks-list" id="master-banks-items-container">
                ${listHtml}
            </div>
            <div class="master-banks-footer">
                <a href="master-data.php#tab-banks" target="_blank" class="master-banks-manage-link" title="فتح شاشة البيانات الأساسية لإضافة أو تعديل البنوك">
                    <span>➕ إدارة البنوك في البيانات الأساسية</span>
                    <span style="font-size: 0.9rem;">↗</span>
                </a>
            </div>
        `;

        // ربط أحداث النقر على البنود
        const items = dropdown.querySelectorAll('.master-bank-item');
        items.forEach(item => {
            // نستخدم mousedown لمنع حدوث blur للحقل قبل اكتمال الاختيار
            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const bankName = this.getAttribute('data-bank-name');
                const bankBranch = this.getAttribute('data-bank-branch');
                selectBank(bankName, bankBranch);
            });
        });
    }

    function highlightMatch(text, query) {
        if (!text) return '';
        if (!query) return escapeHtml(text);
        const normText = normalizeArabic(text);
        const idx = normText.indexOf(query);
        if (idx === -1) return escapeHtml(text);
        
        const before = text.substring(0, idx);
        const match = text.substring(idx, idx + query.length);
        const after = text.substring(idx + query.length);
        return `${escapeHtml(before)}<span style="color:#38bdf8; text-decoration:underline; font-weight:900;">${escapeHtml(match)}</span>${escapeHtml(after)}`;
    }

    /**
     * اختيار بنك وتعبئته في الحقل وإطلاق كافة الأحداث المطلوبة
     */
    function selectBank(bankName, bankBranch) {
        if (!_activeInput) return;
        const input = _activeInput;
        input.value = bankName;

        // إطلاق أحداث input و change فوراً لربط باقي وحدات النظام (BBAN والحسابات وغيرها)
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        // تعبئة حقل الفرع تلقائياً إذا وجد في نفس السطر أو البطاقة وكان فارغاً
        if (bankBranch) {
            try {
                const parentRow = input.closest('.bank-row, .cheque-row, .transfer-row, tr, .form-grid, .cust-bank-row, div[style*="display: flex"]');
                if (parentRow) {
                    const branchInput = parentRow.querySelector('.block-bank-branch-input, .bank-branch, [name*="branch"], input[placeholder*="فرع"]');
                    if (branchInput && !branchInput.value.trim()) {
                        branchInput.value = bankBranch;
                        branchInput.dispatchEvent(new Event('input', { bubbles: true }));
                        branchInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            } catch (e) {}
        }

        hideDropdown();

        // نقل التركيز بسلاسة إلى الحقل التالي (مثل رقم التحويلة أو رقم الشيك)
        try {
            const parentRow = input.closest('.bank-row, .cheque-row, .transfer-row, tr');
            if (parentRow) {
                const nextInput = parentRow.querySelector('.block-transfer-no-input, .transfer-no, .block-cheque-no-input, .cheque-no');
                if (nextInput) {
                    setTimeout(() => nextInput.focus(), 60);
                }
            }
        } catch (e) {}
    }

    // ═══════════════════════════════════════════════════════════════════
    // 5. الفحص والتعرف الذكي على حقول أسماء البنوك (Selector Matching)
    // ═══════════════════════════════════════════════════════════════════
    function isBankNameInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        const type = (el.type || 'text').toLowerCase();
        if (type !== 'text' && type !== 'search') return false;

        // استبعاد حقول البحث عن الحسابات أو حقول شاشة إدارة البنوك نفسها
        const id = (el.id || '').toLowerCase();
        if (id === 'main-account-search' || id === 'from-account-search' || id === 'to-account-search' || id === 't-from-search' || id === 't-to-search') {
            return false;
        }

        // استبعاد حقل اسم البنك الجديد داخل نموذج إنشاء البنك في master-data.php لتجنب إعاقة كتابة بنك جديد
        if (id === 'bank-name' && (window.location.pathname.includes('master-data.php') || el.closest('#bank-form'))) {
            return false;
        }

        if (el.hasAttribute('data-no-bank-autocomplete')) return false;
        if (el.hasAttribute('data-bank-autocomplete')) return true;

        // مطابقة الكلاسات المعروفة في جميع الشاشات
        if (el.classList.contains('block-bank-name-input') ||
            el.classList.contains('block-cheque-bank-input') ||
            el.classList.contains('transfer-note') ||
            el.classList.contains('cheque-branch') ||
            (el.classList.contains('bank-name') && !el.closest('#bank-form'))) {
            return true;
        }

        // مطابقة المعرفات الخاصة
        if (id === 'check-bank' || id === 'ocr-field-bank-name' || id === 'new-template-bank') {
            return true;
        }

        // مطابقة نص التلميح (placeholder)
        const ph = (el.placeholder || '').trim();
        if (ph.includes('اسم البنك') || ph.includes('البنك المسحوب') || ph.includes('اسم البنك المحول')) {
            return true;
        }

        return false;
    }

    /**
     * فحص وتجهيز الحقول في الصفحة لربطها بـ datalist
     */
    function bindInputsInDocument(container = document) {
        updateDatalist();
        const inputs = container.querySelectorAll('input');
        inputs.forEach(input => {
            if (isBankNameInput(input)) {
                input.setAttribute('list', 'master-banks-datalist');
                input.setAttribute('autocomplete', 'off');
                input.setAttribute('data-master-banks-bound', 'true');
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════
    // 6. موزع الأحداث العام (Universal Event Delegation)
    // ═══════════════════════════════════════════════════════════════════
    document.addEventListener('focusin', function(e) {
        if (isBankNameInput(e.target)) {
            showDropdown(e.target);
        }
    });

    document.addEventListener('click', function(e) {
        if (isBankNameInput(e.target)) {
            showDropdown(e.target);
        } else if (_dropdownEl && !_dropdownEl.contains(e.target)) {
            hideDropdown();
        }
    });

    document.addEventListener('input', function(e) {
        if (isBankNameInput(e.target) && _activeInput === e.target) {
            renderDropdownList(e.target.value || '');
            positionDropdown(e.target);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (!_activeInput || !_dropdownEl || _dropdownEl.style.display === 'none') {
            return;
        }

        const items = _dropdownEl.querySelectorAll('.master-bank-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            _activeIndex = (_activeIndex + 1) % items.length;
            highlightItem(items, _activeIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            _activeIndex = (_activeIndex - 1 + items.length) % items.length;
            highlightItem(items, _activeIndex);
        } else if (e.key === 'Enter') {
            if (_activeIndex >= 0 && _activeIndex < items.length) {
                e.preventDefault();
                const sel = items[_activeIndex];
                selectBank(sel.getAttribute('data-bank-name'), sel.getAttribute('data-bank-branch'));
            }
        } else if (e.key === 'Escape' || e.key === 'Tab') {
            hideDropdown();
        }
    });

    function highlightItem(items, idx) {
        items.forEach((it, i) => {
            if (i === idx) {
                it.classList.add('active');
                it.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                it.classList.remove('active');
            }
        });
    }

    // إغلاق أو إعادة ضبط الموقع عند التمرير أو تغيير أبعاد النافذة
    window.addEventListener('resize', function() {
        if (_activeInput) positionDropdown(_activeInput);
    }, { passive: true });

    window.addEventListener('scroll', function() {
        if (_activeInput) {
            // التحقق مما إذا كان الحقل لا يزال ظاهراً في إطار الرؤية
            const rect = _activeInput.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > window.innerHeight) {
                hideDropdown();
            } else {
                positionDropdown(_activeInput);
            }
        }
    }, { passive: true, capture: true });

    // ═══════════════════════════════════════════════════════════════════
    // 7. مراقب التغييرات الديناميكية في DOM (MutationObserver)
    // ═══════════════════════════════════════════════════════════════════
    const observer = new MutationObserver(mutations => {
        let shouldBind = false;
        for (const mut of mutations) {
            if (mut.addedNodes && mut.addedNodes.length > 0) {
                for (const node of mut.addedNodes) {
                    if (node.nodeType === 1) { // ELEMENT_NODE
                        if (isBankNameInput(node) || node.querySelector?.('input')) {
                            shouldBind = true;
                            break;
                        }
                    }
                }
            }
            if (shouldBind) break;
        }
        if (shouldBind) {
            bindInputsInDocument(document);
        }
    });

    // ═══════════════════════════════════════════════════════════════════
    // 8. المزامنة عند تعديل البيانات الأساسية (Storage / Custom Events)
    // ═══════════════════════════════════════════════════════════════════
    window.addEventListener('storage', function(e) {
        if (e.key && e.key.includes('erp_banks_v1')) {
            loadMasterBanks(true);
            updateDatalist();
        }
    });

    document.addEventListener('DBReady', function() {
        loadMasterBanks(true);
        updateDatalist();
    });

    // ═══════════════════════════════════════════════════════════════════
    // 9. نقطة الانطلاق والتهيئة العامة (Initialization)
    // ═══════════════════════════════════════════════════════════════════
    function init() {
        getOrCreateDatalist();
        updateDatalist();
        bindInputsInDocument(document);

        observer.observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });

        // طلب تحديث أولي
        loadMasterBanks(true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // الكشف العام على الكائن
    window.MasterBanksHelper = {
        __initialized: true,
        getBanks: loadMasterBanks,
        refresh: function() {
            loadMasterBanks(true);
            updateDatalist();
            bindInputsInDocument(document);
        },
        bind: bindInputsInDocument,
        show: showDropdown,
        hide: hideDropdown
    };

})(window, document);
