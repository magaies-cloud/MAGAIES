/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * 🎨 ELSAJANA ERP 2026 - THE 7 MASTER THEMES ENGINE (DESKTOP & LAPTOP)
 * ═══════════════════════════════════════════════════════════════════════════════
 * 1. Ultra-Clean White (أبيض فائق النقاء) - Light
 * 2. Warm Paper Sepia (ورق سيبيا دافئ) - Light
 * 3. Cool Executive Gray (رمادي تنفيذي بارد) - Light
 * 4. High-Contrast Solar Sun (شمس ساطعة عالي التباين) - Light/High Contrast
 * 5. Twilight Slate (سليت مسائي هادئ) - Dark
 * 6. Midnight Obsidian (كحلي فاحم ملكي) - Dark
 * 7. Pure OLED Black (سواد مطلق) - Dark
 */

(function() {
    'use strict';

    const THEMES_7 = [
        { key: 'ultra_white', name: 'أبيض فائق النقاء Ultra-Clean White', icon: 'fa-circle text-blue-500', isLight: true, dot: '#2563eb', meta: '#ffffff', desc: 'إضاءة نهارية مكتبية ناصعة مع نصوص سوداء حادة لأعلى وضوح' },
        { key: 'warm_cream', name: 'ورق سيبيا دافئ Warm Paper Sepia', icon: 'fa-scroll text-amber-600', isLight: true, dot: '#b45309', meta: '#fbf7ee', desc: 'مستوحى من ورق الدفاتر الفاخرة لمنع جفاف وإجهاد العين أثناء العمل الطويل' },
        { key: 'exec_gray', name: 'رمادي تنفيذي بارد Cool Executive Gray', icon: 'fa-briefcase text-blue-600', isLight: true, dot: '#1d4ed8', meta: '#f1f5f9', desc: 'أسطح رمادية هادئة وأناقة الشركات الكبرى والبنوك العالمية' },
        { key: 'solar_contrast', name: 'شمس ساطعة عالي التباين Solar Sun', icon: 'fa-sun text-yellow-500', isLight: true, dot: '#000000', meta: '#ffff00', desc: 'أعلى تباين معتمد طبياً (WCAG AAA) للعمل تحت أشعة الشمس المباشرة' },
        { key: 'slate_twilight', name: 'سليت مسائي هادئ Twilight Slate', icon: 'fa-cloud-moon text-sky-400', isLight: false, dot: '#38bdf8', meta: '#0f172a', desc: 'رمادي داكن مائل للزرقة هادئ جداً لأوقات المساء' },
        { key: 'midnight_obsidian', name: 'كحلي فاحم ملكي Midnight Obsidian', icon: 'fa-gem text-cyan-400', isLight: false, dot: '#00f2fe', meta: '#0a1128', desc: 'السمة المصرفية الفخمة باللون الكحلي السحيق والأمان المالي' },
        { key: 'pure_oled', name: 'سواد مطلق Pure OLED Black', icon: 'fa-moon text-emerald-400', isLight: false, dot: '#10b981', meta: '#000000', desc: 'سواد حالك لإطفاء البكسلات وتوفير طاقة البطارية بنسبة 30%' }
    ];

    const LIGHT_THEMES = ['ultra_white', 'warm_cream', 'exec_gray', 'solar_contrast'];

    const ALIAS_MAP = {
        'warm-alabaster': 'warm_cream',
        'pure-platinum': 'ultra_white',
        'midnight-executive': 'midnight_obsidian',
        'obsidian-charcoal': 'pure_oled',
        'beige': 'warm_cream',
        'ivory': 'warm_cream',
        'offwhite': 'ultra_white',
        'light-linen': 'warm_cream',
        'pearl-platinum': 'exec_gray',
        'nordic-paper': 'warm_cream',
        'navy-kohl': 'midnight_obsidian',
        'royal-blue': 'midnight_obsidian',
        'ocean-navy': 'midnight_obsidian',
        'petrol-cyan': 'midnight_obsidian',
        'twilight-indigo': 'slate_twilight',
        'midnight': 'pure_oled',
        'sepia-brown': 'warm_cream',
        'pale-gold': 'warm_cream',
        'emerald-forest': 'slate_twilight',
        'desert-mocha': 'warm_cream',
        'sunlight-titanium': 'solar_contrast',
        'solar': 'solar_contrast',
        'navy': 'midnight_obsidian',
        'forest': 'slate_twilight',
        'light': 'ultra_white',
        'dark': 'midnight_obsidian'
    };

    // ── Apply Theme to DOM and Storage ──
    function applyTheme(themeKey, showToast = true) {
        if (!themeKey) return;
        if (ALIAS_MAP[themeKey]) themeKey = ALIAS_MAP[themeKey];
        const themeObj = THEMES_7.find(t => t.key === themeKey) || THEMES_7[5];
        themeKey = themeObj.key;

        document.documentElement.setAttribute('data-theme', themeKey);
        if (document.body) document.body.setAttribute('data-theme', themeKey);

        const isLight = LIGHT_THEMES.includes(themeKey);
        if (isLight) {
            document.documentElement.classList.add('light-theme');
            document.documentElement.classList.remove('dark-theme');
            if (document.body) {
                document.body.classList.add('light-theme');
                document.body.classList.remove('dark-theme');
            }
        } else {
            document.documentElement.classList.remove('light-theme');
            document.documentElement.classList.add('dark-theme');
            if (document.body) {
                document.body.classList.remove('light-theme');
                document.body.classList.add('dark-theme');
            }
        }

        try {
            localStorage.setItem('magaies_active_theme', themeKey);
            localStorage.setItem('magaiees_theme', themeKey);
            localStorage.setItem('magaiees_mobile_theme', themeKey);
            localStorage.setItem('magaiees_global_theme', themeKey);
            localStorage.setItem('erp_theme', themeKey);

            const isHttps = location.protocol === 'https:';
            const secureFlag = isHttps ? '; SameSite=Lax; Secure' : '; SameSite=Lax';
            document.cookie = 'magaies_active_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
            document.cookie = 'magaiees_mobile_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
            document.cookie = 'magaiees_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
            document.cookie = 'erp_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
        } catch(e) {}

        highlightActiveThemeCard();
        if (showToast) {
            showToastMessage(themeObj.name);
        }
    }

    // ── Toast Message ──
    function showToastMessage(themeName) {
        let toast = document.getElementById('desktopThemeToastBox');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'desktopThemeToastBox';
            toast.style.cssText = 'position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px); background:linear-gradient(135deg, #10b981, #059669); color:#ffffff; font-weight:800; font-size:14px; padding:12px 26px; border-radius:30px; box-shadow:0 12px 35px rgba(0,0,0,0.45); z-index:9999999; display:flex; align-items:center; gap:10px; pointer-events:none; transition:all 0.35s cubic-bezier(0.16, 1, 0.3, 1); opacity:0; direction:rtl; font-family:inherit;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<span style="font-size:1.3rem;">🎨</span><span>تم تفعيل السمة: <strong>' + themeName + '</strong> وتوحيدها عبر كافة الشاشات</span>';
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(window._desktopThemeToastTimer);
        window._desktopThemeToastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
        }, 2200);
    }

    // ── Modal Control ──
    function openModal() {
        let modal = document.getElementById('desktopThemePaletteModal');
        if (!modal) return;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            const panel = modal.querySelector('.desktop-theme-modal-panel');
            if (panel) panel.style.transform = 'scale(1)';
        }, 10);
        highlightActiveThemeCard();
    }

    function closeModal() {
        const modal = document.getElementById('desktopThemePaletteModal');
        if (!modal) return;
        modal.style.opacity = '0';
        const panel = modal.querySelector('.desktop-theme-modal-panel');
        if (panel) panel.style.transform = 'scale(0.95)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 250);
    }

    function highlightActiveThemeCard() {
        let currentTheme = document.documentElement.getAttribute('data-theme') || 'midnight_obsidian';
        if (ALIAS_MAP[currentTheme]) currentTheme = ALIAS_MAP[currentTheme];
        document.querySelectorAll('.desktop-theme-card').forEach(card => {
            const key = card.getAttribute('data-theme-key');
            if (key === currentTheme) {
                card.classList.add('active-theme-card');
                card.style.borderColor = '#10b981';
                card.style.boxShadow = '0 0 16px rgba(16, 185, 129, 0.45)';
            } else {
                card.classList.remove('active-theme-card');
                card.style.borderColor = 'var(--border-color, rgba(255,255,255,0.12))';
                card.style.boxShadow = 'none';
            }
        });

        const pillLabel = document.getElementById('desktopFloatingThemeLabel');
        if (pillLabel) {
            const obj = THEMES_7.find(t => t.key === currentTheme);
            if (obj) pillLabel.textContent = obj.name.split(' ')[0] + ' ' + (obj.isLight ? '☀️' : '🌙');
        }
    }

    // ── Build Modal HTML ──
    function buildModalHtml() {
        if (document.getElementById('desktopThemePaletteModal')) return;

        const modal = document.createElement('div');
        modal.id = 'desktopThemePaletteModal';
        modal.style.cssText = 'position:fixed; inset:0; background:rgba(2, 6, 23, 0.75); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); z-index:999999; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity 0.25s ease; padding:20px; direction:rtl; font-family:inherit;';

        let cardsHtml = '';
        THEMES_7.forEach(t => {
            const tagBadge = t.isLight ? '<span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:800;">☀️ فاتح عالي التباين</span>' : '<span style="background:#1e293b; color:#38bdf8; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:800;">🌙 ليلي مالي فخم</span>';
            cardsHtml += `
                <div class="desktop-theme-card" data-theme-key="${t.key}" onclick="window.applyDesktopTheme('${t.key}')" style="background:var(--surface, #1e293b); border:1.5px solid var(--border-color, rgba(255,255,255,0.12)); border-radius:14px; padding:12px; cursor:pointer; transition:all 0.2s ease; display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="display:inline-block; width:16px; height:16px; border-radius:50%; background:${t.dot}; border:2px solid #fff; box-shadow:0 0 6px rgba(0,0,0,0.3);"></span>
                            <strong style="font-size:13.5px; color:var(--text-main, #fff); font-weight:800;">${t.name}</strong>
                        </div>
                        ${tagBadge}
                    </div>
                    <div style="font-size:11.5px; color:var(--text-muted, #94a3b8); line-height:1.5; font-weight:600;">
                        ${t.desc}
                    </div>
                </div>
            `;
        });

        modal.innerHTML = `
            <div class="desktop-theme-modal-panel" style="background:var(--bg-secondary, #0f172a); border:2px solid var(--border-color, #334155); border-radius:20px; width:100%; max-width:720px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.6); transform:scale(0.95); transition:transform 0.25s cubic-bezier(0.16, 1, 0.3, 1); overflow:hidden;">
                <!-- Header -->
                <div style="padding:18px 24px; border-bottom:1px solid var(--border-color, #1e293b); display:flex; align-items:center; justify-content:space-between; background:var(--surface, #1e293b);">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.5rem;">🎨</span>
                        <div>
                            <h3 style="margin:0; font-size:16px; font-weight:900; color:var(--text-main, #fff);">السمات السبعة المعتمدة والموحدة (Magaiees 7 Themes)</h3>
                            <p style="margin:0; font-size:11.5px; color:var(--text-muted, #94a3b8);">سمات مدروسة علمياً لراحة العين وأعلى تباين - تتزامن فورياً على كافة شاشات الكمبيوتر والجوال</p>
                        </div>
                    </div>
                    <button type="button" onclick="window.closeDesktopThemeModal()" style="background:transparent; border:none; color:var(--text-muted, #94a3b8); font-size:22px; cursor:pointer; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:10px; transition:all 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--text-muted, #94a3b8)'">&times;</button>
                </div>
                <!-- Body -->
                <div style="padding:18px 24px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
                    ${cardsHtml}
                </div>
                <!-- Footer -->
                <div style="padding:12px 24px; border-top:1px solid var(--border-color, #1e293b); display:flex; align-items:center; justify-content:space-between; background:var(--surface, #1e293b); font-size:12px; color:var(--text-muted, #94a3b8);">
                    <span>💡 تلميح: يتم حفظ السمة المختارة تلقائياً وتطبيقها على المبيعات، المشتريات، وسندات الجوال</span>
                    <button type="button" onclick="window.closeDesktopThemeModal()" style="background:#2563eb; color:#fff; border:none; padding:8px 18px; border-radius:10px; font-weight:800; cursor:pointer; font-size:12px;">إغلاق</button>
                </div>
            </div>
        `;

        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };

        document.body.appendChild(modal);
    }

    // ── Build Floating Trigger Button ──
    function buildFloatingTrigger() {
        if (document.getElementById('desktopFloatingThemeBtn')) return;

        const btn = document.createElement('div');
        btn.id = 'desktopFloatingThemeBtn';
        btn.onclick = openModal;
        btn.title = 'تغيير السمة (7 سمات رئيسية موحدة) 🎨';
        btn.style.cssText = 'position:fixed; bottom:25px; left:25px; z-index:99999; background:linear-gradient(135deg, #1e293b, #0f172a); border:1.5px solid rgba(56, 189, 248, 0.4); border-radius:30px; padding:8px 16px; display:flex; align-items:center; gap:8px; cursor:pointer; box-shadow:0 8px 25px rgba(0,0,0,0.45); color:#fff; font-size:12px; font-weight:800; transition:all 0.25s ease; direction:rtl; select-none;';

        btn.onmouseover = () => {
            btn.style.transform = 'translateY(-3px) scale(1.03)';
            btn.style.borderColor = '#38bdf8';
            btn.style.boxShadow = '0 12px 30px rgba(56, 189, 248, 0.35)';
        };
        btn.onmouseout = () => {
            btn.style.transform = 'none';
            btn.style.borderColor = 'rgba(56, 189, 248, 0.4)';
            btn.style.boxShadow = '0 8px 25px rgba(0,0,0,0.45)';
        };

        btn.innerHTML = `
            <span style="font-size:1.1rem;">🎨</span>
            <span id="desktopFloatingThemeLabel">السمات (7)</span>
        `;

        document.body.appendChild(btn);
    }

    // ── Initialize on Page Load ──
    function init() {
        let saved = localStorage.getItem('magaies_active_theme') || 
                    localStorage.getItem('magaiees_mobile_theme') || 
                    localStorage.getItem('magaiees_theme') || 
                    localStorage.getItem('erp_theme');
        if (!saved) {
            const m = document.cookie.match(/(?:^|;\s*)(?:magaies_active_theme|magaiees_mobile_theme|magaiees_theme|erp_theme)=([^;]+)/);
            if (m) saved = decodeURIComponent(m[1]);
        }
        if (!saved) saved = 'midnight_obsidian';
        applyTheme(saved, false);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                buildModalHtml();
                buildFloatingTrigger();
                highlightActiveThemeCard();
            });
        } else {
            buildModalHtml();
            buildFloatingTrigger();
            highlightActiveThemeCard();
        }

        // Listen for storage events (multi-tab sync)
        window.addEventListener('storage', (e) => {
            if (e.key === 'magaies_active_theme' || e.key === 'magaiees_mobile_theme' || e.key === 'erp_theme') {
                if (e.newValue) applyTheme(e.newValue, false);
            }
        });
    }

    // Expose globals
    window.applyDesktopTheme = applyTheme;
    window.openDesktopThemeModal = openModal;
    window.closeDesktopThemeModal = closeModal;
    window.toggleDesktopThemeModal = function() {
        const modal = document.getElementById('desktopThemePaletteModal');
        if (modal && modal.style.display === 'flex') closeModal();
        else openModal();
    };

    init();
})();
