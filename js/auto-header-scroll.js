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
                'register.php',
                'settings.php',
                'backup.php',
                'master-data.php',
                'users.php'
            ];
            
            if (excludedPages.includes(path)) {
                return true;
            }

            // فحص كلاسات وخصائص تعطيل التمرير التلقائي صراحة
            if (document.body && (document.body.classList.contains('no-auto-scroll') || document.body.hasAttribute('data-no-auto-scroll'))) {
                return true;
            }

            // فحص عنوان الصفحة ومحتوى العنوان
            const titleEl = document.querySelector('.header-page-title');
            if (titleEl) {
                const txt = (titleEl.textContent || '').toLowerCase();
                if (txt.includes('لوحة التحكم') || txt.includes('dashboard') || txt.includes('الإعدادات') || txt.includes('settings')) {
                    return true;
                }
            }

            if (document.title) {
                const dt = document.title.toLowerCase();
                if (dt.includes('لوحة التحكم') || dt.includes('dashboard') || dt.includes('الإعدادات') || dt.includes('settings')) {
                    return true;
                }
            }
        } catch(e) {}
        return false;
    }

    const AutoHeaderScroll = {
        timer: null,
        hasAutoScrolled: false,

        ensureScrollableSpace: function() {
            try {
                const targetY = this.getTargetY();
                const neededHeight = window.innerHeight + targetY + 30;
                if (document.documentElement.scrollHeight < neededHeight) {
                    document.body.style.minHeight = neededHeight + 'px';
                }
            } catch(e) {}
        },

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
            window.scrollTo(0, 0);

            // ضمان وجود مساحة كافية للتمرير في أي شاشة مهما كان محتواها قصيراً
            this.ensureScrollableSpace();

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

            window.addEventListener('resize', () => {
                this.ensureScrollableSpace();
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
                // ظاهرة وكاملة الوضوح في قمة الصفحة
                const y = Math.round(rect.bottom + window.pageYOffset);
                if (y > 30) return y;
            }

            return 130;
        },

        scrollToTarget: function() {
            // التأكد من توفر مساحة كافية للتمرير
            this.ensureScrollableSpace();

            const targetY = this.getTargetY();
            const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
            if (maxScroll < 15) return;

            this.hasAutoScrolled = true;
            try { sessionStorage.setItem('mcp_page_scrolled_' + window.location.pathname, '1'); } catch(e) {}

            const scrollY = Math.min(targetY, maxScroll);
            try {
                window.scrollTo({
                    top: scrollY,
                    behavior: 'smooth'
                });
            } catch(e) {
                window.scrollTo(0, scrollY);
            }
        },

        scrollToHeader: function() {
            // رجوع سلس ومباشر إلى أعلى الصفحة
            try {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } catch(e) {
                window.scrollTo(0, 0);
            }
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

    // تشغيل المحرك تلقائياً بمجرد جاهزية الـ DOM دون انتظار الملفات الخارجية الثقيلة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AutoHeaderScroll.init());
    } else {
        AutoHeaderScroll.init();
    }

    // فحص إضافي عند اكتمال تحميل الصفحة بالكامل لضمان دقة الأبعاد
    window.addEventListener('load', () => {
        if (!isDashboardPage()) {
            AutoHeaderScroll.ensureScrollableSpace();
            if (!AutoHeaderScroll.hasAutoScrolled && !AutoHeaderScroll.timer && window.pageYOffset < 35) {
                AutoHeaderScroll.scrollToTarget();
            }
        }
    });
})();
