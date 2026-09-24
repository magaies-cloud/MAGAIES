/**
 * 🌟 AutoHeaderScroll: ضبط موضع مساحة العمل مباشرة بدون أي مؤثر حركي
 * - انتقال مباشر وفوري بعد 0.5 ثانية بدون مؤثرات حركية أو بطء
 * - مُستثنى تماماً من لوحة التحكم (لوحة التحكم ثابتة)
 * - يوفر زراً عائماً لإرجاع الترويسة في أي لحظة مباشرة
 */
(function() {
    'use strict';

    // 1. فحص استثناء لوحة التحكم
    function isDashboardPage() {
        try {
            const path = window.location.pathname.split('/').pop().toLowerCase();
            
            // أسماء ملفات لوحات التحكم والشاشات المستثناة
            const excludedPages = [
                'magaiees_unified_dashboard.php',
                'dashboard.php',
                'dashboard-pro.php',
                'index.php',
                'saas-admin.php',
                'login.php',
                'register.php'
            ];
            
            if (excludedPages.includes(path)) {
                return true;
            }

            // فحص عنوان الصفحة ومحتوى العنوان
            const titleEl = document.querySelector('.header-page-title');
            if (titleEl) {
                const txt = titleEl.textContent || '';
                if (txt.includes('لوحة التحكم') || txt.toLowerCase().includes('dashboard')) {
                    return true;
                }
            }

            if (document.title && (document.title.includes('لوحة التحكم') || document.title.toLowerCase().includes('dashboard'))) {
                return true;
            }
        } catch(e) {}
        return false;
    }

    const AutoHeaderScroll = {
        timer: null,
        hasAutoScrolled: false,

        init: function() {
            // استثناء لوحة التحكم والشاشات المستثناة
            if (isDashboardPage()) {
                return;
            }

            try {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
            } catch(e) {}

            // التأكد من بدء الصفحة من القمة عند أول فتح
            if (window.pageYOffset > 0 && !sessionStorage.getItem('mcp_page_scrolled_' + window.location.pathname)) {
                window.scrollTo(0, 0);
            }

            // مراقبة التمرير لتحديث ظهور الزر العائم وزر الترويسة
            window.addEventListener('scroll', () => {
                this.handleScroll();
            }, { passive: true });

            // إلغاء العداد التلقائي إذا بدأ المستخدم بالتمرير اليدوي أو الكتابة قبل انقضاء الـ 0.5 ثانية
            const cancelOnUserAction = () => {
                if (!this.hasAutoScrolled && this.timer) {
                    clearTimeout(this.timer);
                    this.timer = null;
                }
            };

            window.addEventListener('wheel', cancelOnUserAction, { passive: true });
            window.addEventListener('touchmove', cancelOnUserAction, { passive: true });
            window.addEventListener('keydown', (e) => {
                if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Space'].includes(e.code)) {
                    cancelOnUserAction();
                }
            }, { passive: true });

            // بدء مؤقت الـ 0.5 ثانية
            this.startCountdown(500);
        },

        startCountdown: function(ms) {
            this.timer = setTimeout(() => {
                this.timer = null;
                // الانتقال المباشر فقط إذا كان المستخدم ما زال في أعلى الصفحة (لم يقم بتمرير يدوي)
                if (window.pageYOffset < 35) {
                    this.scrollToTarget();
                }
            }, ms);
        },

        getTargetY: function() {
            const header = document.querySelector('.premium-header');
            if (header) {
                const rect = header.getBoundingClientRect();
                // الصعود في حدود الترويسة فقط:
                // ينتهي التمرير عند الحافة السفلية للترويسة تماماً، مما يضمن بقاء جميع أزرار العمليات
                // مثل (فاتورة مشتريات مباشرة، استلام بضاعة، صرف، إلخ) ظاهرة وكاملة الوضوح في قمة الصفحة
                return Math.max(0, Math.round(rect.bottom + window.pageYOffset));
            }

            return 130;
        },

        scrollToTarget: function() {
            const targetY = this.getTargetY();
            
            // إذا كانت الصفحة قصيرة جداً ولا تستوعب التمرير
            const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
            if (maxScroll < 40) return;

            this.hasAutoScrolled = true;
            try { sessionStorage.setItem('mcp_page_scrolled_' + window.location.pathname, '1'); } catch(e) {}

            // انتقال فوري ومباشر بدون أي مؤثر حركي أو إبطاء
            window.scrollTo(0, Math.min(targetY, maxScroll));
        },

        scrollToHeader: function() {
            // رجوع فوري ومباشر إلى أعلى الصفحة
            window.scrollTo(0, 0);
        },

        toggleHeader: function() {
            const targetY = this.getTargetY();
            if (window.pageYOffset > targetY * 0.35) {
                this.scrollToHeader();
            } else {
                this.scrollToTarget();
            }
        },

        handleScroll: function() {
            const targetY = this.getTargetY();
            const pill = document.getElementById('auto-scroll-header-pill');
            const toggleBtn = document.getElementById('btn-toggle-top-header');

            const isScrolledDown = window.pageYOffset >= Math.max(45, targetY * 0.35);

            if (pill) {
                if (isScrolledDown) {
                    pill.classList.add('visible');
                } else {
                    pill.classList.remove('visible');
                }
            }

            if (toggleBtn) {
                const icon = toggleBtn.querySelector('.toggle-icon') || toggleBtn.querySelector('i');
                const text = toggleBtn.querySelector('.toggle-text');
                if (isScrolledDown) {
                    if (icon) {
                        if (icon.tagName === 'I') icon.className = 'fa-solid fa-angles-up';
                        else icon.textContent = '▲';
                    }
                    if (text) text.textContent = 'الترويسة';
                    toggleBtn.title = 'الرجوع إلى أعلى الصفحة وإظهار الترويسة';
                } else {
                    if (icon) {
                        if (icon.tagName === 'I') icon.className = 'fa-solid fa-angles-down';
                        else icon.textContent = '▼';
                    }
                    if (text) text.textContent = 'الأزرار';
                    toggleBtn.title = 'الانتقال إلى أزرار العمليات وبدء العمل';
                }
            }
        }
    };

    window.AutoHeaderScroll = AutoHeaderScroll;

    // تشغيل المحرك تلقائياً بمجرد اكتمال تجهيز الصفحة
    if (document.readyState === 'complete') {
        AutoHeaderScroll.init();
    } else {
        window.addEventListener('load', () => AutoHeaderScroll.init());
    }
})();
