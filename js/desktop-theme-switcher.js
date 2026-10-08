/**
 * ═════════════════════════════════════════════════════════════════════
 * 🎨 ELSAJANA ERP 2026 - UNIVERSAL THEME SWITCHER ENGINE FOR LAPTOP/DESKTOP
 * ═════════════════════════════════════════════════════════════════════
 * Works in both Local ("المحل") and Live Server ("الحي").
 * Provides:
 * 1. Ultra-elegant floating theme pill on Desktop & Laptop screens.
 * 2. Instant access to the 21 WCAG AAA Scientific Themes Matrix.
 * 3. Autonomous self-injection on any page (with or without header.php).
 * 4. Multi-tab / cross-window instant synchronization.
 */

(function() {
    'use strict';

    const THEME_NAMES = {
        'warm-alabaster': 'العاجي الدافئ',
        'pure-platinum': 'الفضي النقي',
        'midnight-executive': 'كحلي منتصف الليل',
        'obsidian-charcoal': 'حجر السج الجرافيتي',
        'beige': 'البيج الدافئ المريح',
        'ivory': 'العاجي الملكي الكلاسيكي',
        'offwhite': 'الأوف وايت الحديث المطفأ',
        'light-linen': 'الكتان الطبيعي المريح',
        'pearl-platinum': 'اللؤلؤي الفضي المريح',
        'nordic-paper': 'الورقي النوردي المريح',
        'navy-kohl': 'الكحلي الملكي الليلي',
        'royal-blue': 'الأزرق الزفيري الحيوي',
        'ocean-navy': 'الأزرق المحيطي العميق',
        'petrol-cyan': 'الأزرق البترولي والتيل',
        'twilight-indigo': 'أزرق الشفق والإنديجو',
        'midnight': 'الليل الملكي الداكن',
        'sepia-brown': 'البني الإسبريسو والورق القديم',
        'pale-gold': 'الذهبي الباهت والفخامة',
        'emerald-forest': 'زمرد الغابات لصفاء الذهن',
        'desert-mocha': 'الرمال الصحراوية وموكا',
        'sunlight-titanium': 'التيتانيوم الشمسي فائق التباين'
    };

    const LIGHT_THEMES = [
        'warm-alabaster', 'pure-platinum', 'beige', 'ivory',
        'offwhite', 'light-linen', 'pearl-platinum', 'nordic-paper'
    ];

    const ALIAS_MAP = {
        'ultra_white': 'pure-platinum',
        'warm_cream': 'warm-alabaster',
        'exec_gray': 'pearl-platinum',
        'solar_contrast': 'sunlight-titanium',
        'solar': 'sunlight-titanium',
        'slate_twilight': 'midnight-executive',
        'midnight_obsidian': 'midnight-executive',
        'pure_oled': 'midnight',
        'navy': 'navy-kohl',
        'forest': 'emerald-forest',
        'theme-imperial': 'warm-alabaster'
    };

    // ═══ Apply Theme to DOM and Storage ═══
    function applyTheme(themeKey, showToast = true) {
        if (!themeKey) return;
        if (ALIAS_MAP[themeKey]) themeKey = ALIAS_MAP[themeKey];
        if (!THEME_NAMES[themeKey]) themeKey = 'warm-alabaster';

        document.documentElement.setAttribute('data-theme', themeKey);
        if (document.body) document.body.setAttribute('data-theme', themeKey);

        const isLight = LIGHT_THEMES.includes(themeKey);
        if (isLight) {
            document.documentElement.classList.add('light-theme');
            if (document.body) document.body.classList.add('light-theme');
        } else {
            document.documentElement.classList.remove('light-theme');
            if (document.body) document.body.classList.remove('light-theme');
        }

        try {
            localStorage.setItem('magaies_active_theme', themeKey);
            localStorage.setItem('magaiees_theme', themeKey);
            localStorage.setItem('magaiees_mobile_theme', themeKey);
            localStorage.setItem('magaiees_global_theme', themeKey);

            const isHttps = location.protocol === 'https:';
            const secureFlag = isHttps ? '; SameSite=Lax; Secure' : '; SameSite=Lax';
            document.cookie = 'magaies_active_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
            document.cookie = 'magaiees_theme=' + encodeURIComponent(themeKey) + '; path=/; max-age=31536000' + secureFlag;
        } catch(e) {}

        highlightActiveThemeCard();
        if (showToast) {
            showToastMessage(THEME_NAMES[themeKey] || themeKey);
        }
    }

    // ═══ Toast Message ═══
    function showToastMessage(themeName) {
        let toast = document.getElementById('desktopThemeToastBox');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'desktopThemeToastBox';
            toast.style.cssText = 'position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px); background:linear-gradient(135deg, #10b981, #059669); color:#ffffff; font-weight:800; font-size:14px; padding:12px 26px; border-radius:30px; box-shadow:0 12px 35px rgba(0,0,0,0.45); z-index:9999999; display:flex; align-items:center; gap:10px; pointer-events:none; transition:all 0.35s cubic-bezier(0.16, 1, 0.3, 1); opacity:0; direction:rtl; font-family:inherit;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<span style="font-size:1.3rem;">🎨</span><span>تم تفعيل السمة: <strong>' + themeName + '</strong> بنجاح</span>';
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(window._desktopThemeToastTimer);
        window._desktopThemeToastTimer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
        }, 2500);
    }

    // ═══ Modal Control ═══
    function openModal() {
        let modal = document.getElementById('desktopThemePaletteModal');
        if (!modal) return;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.zIndex = '2147483647';
        document.body.style.overflow = 'hidden';
        highlightActiveThemeCard();
    }

    function closeModal() {
        const modal = document.getElementById('desktopThemePaletteModal');
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function highlightActiveThemeCard() {
        const active = document.documentElement.getAttribute('data-theme') || 'warm-alabaster';
        document.querySelectorAll('.desktop-theme-card').forEach(card => {
            const key = card.getAttribute('data-theme-key');
            const badge = card.querySelector('.desktop-active-indicator');
            if (key === active) {
                card.classList.add('is-current-active');
                if (badge) badge.style.display = 'inline-flex';
            } else {
                card.classList.remove('is-current-active');
                if (badge) badge.style.display = 'none';
            }
        });
        const lbl = document.getElementById('desktopCurrentThemeLabel');
        if (lbl) lbl.textContent = THEME_NAMES[active] || active;
    }

    function filterCategory(category) {
        document.querySelectorAll('.desktop-theme-tab-btn').forEach(btn => {
            if (btn.getAttribute('data-cat') === category) {
                btn.classList.add('active-tab');
            } else {
                btn.classList.remove('active-tab');
            }
        });
        document.querySelectorAll('.desktop-theme-card').forEach(card => {
            const cats = (card.getAttribute('data-category') || '').split(' ');
            if (category === 'all' || cats.includes(category)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // ═══ Inject Floating Button for Laptop/Desktop ═══
    function injectFloatingThemeButton() {
        if (document.getElementById('floating-theme-quick-switch')) return;

        const btn = document.createElement('div');
        btn.id = 'floating-theme-quick-switch';
        btn.className = 'floating-theme-dock';
        btn.setAttribute('role', 'button');
        btn.setAttribute('tabindex', '0');
        btn.title = 'تغيير سمات النظام وراحة العين (21 سمة علمية معتمدة)';
        btn.innerHTML = `
            <span class="floating-theme-icon">🎨</span>
            <span class="floating-theme-text">السمات</span>
            <span class="floating-theme-count">21</span>
        `;
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openModal();
        });
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal();
            }
        });

        document.body.appendChild(btn);
    }

    // ═══ Expose Global Functions ═══
    window.openDesktopThemePaletteModal = openModal;
    window.closeDesktopThemePaletteModal = closeModal;
    window.applyDesktopTheme = applyTheme;
    window.filterDesktopThemeCategory = filterCategory;
    window.highlightActiveDesktopThemeCard = highlightActiveThemeCard;
    window.showDesktopThemeToast = showToastMessage;

    // Close on Escape
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Cross-tab synchronization
    window.addEventListener('storage', function(e) {
        if (e.key === 'magaies_active_theme' && e.newValue) {
            applyTheme(e.newValue, false);
        }
    });

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            injectFloatingThemeButton();
            highlightActiveThemeCard();
        });
    } else {
        injectFloatingThemeButton();
        highlightActiveThemeCard();
    }
})();
