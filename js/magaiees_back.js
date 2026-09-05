/**
 * MAGAIEES ERP — Universal "الرجوع للخلف" (Go Back) Master Component
 * 
 * Provides a standardized, high-prestige Amber-Gold glowing Back button across ALL screens.
 * Features:
 * - Smart 1-step back navigation via history.back() & document.referrer
 * - Graceful fallback to magaiees_unified_dashboard.php if opened directly
 * - Dynamic scroll-docking: stays accessible at top-left even when scrolling long reports
 * - Self-injected responsive styling with zero dependencies
 */

(function () {
    'use strict';

    // 1. Identify current page
    const pathname = window.location.pathname.toLowerCase();
    const pageFile = pathname.split('/').pop().split('?')[0] || '';

    // Ignore login / auth / index redirect
    const excludedPages = ['login.php', 'register.php', 'auto-login.php', 'index.php'];
    if (excludedPages.includes(pageFile)) {
        return;
    }

    // 2. Global Go Back Navigation Function
    window.magaieesGoBack = function () {
        const isDashboard = pageFile.includes('magaiees_unified_dashboard.php') || pageFile === 'dashboard-pro.php';

        // Check referrer
        const ref = document.referrer;
        if (ref && ref !== window.location.href) {
            try {
                const refUrl = new URL(ref);
                if (refUrl.origin === window.location.origin && !refUrl.pathname.toLowerCase().includes('login.php')) {
                    window.location.href = ref;
                    return;
                }
            } catch (e) {}
        }

        // Use history.back if history exists
        if (window.history.length > 1) {
            window.history.back();
            // Fallback timeout in case history.back doesn't navigate
            setTimeout(function () {
                if (!isDashboard) {
                    window.location.href = 'magaiees_unified_dashboard.php';
                }
            }, 600);
            return;
        }

        // Fallback to unified dashboard
        if (!isDashboard) {
            window.location.href = 'magaiees_unified_dashboard.php';
        } else {
            // If already on dashboard and no history, quick polite pulse
            const btn = document.getElementById('magaiees-master-back-btn');
            if (btn) {
                btn.style.transform = 'scale(0.92)';
                setTimeout(() => { btn.style.transform = ''; }, 200);
            }
        }
    };

    // 3. Inject Self-Contained Styles
    function injectStyles() {
        if (document.getElementById('magaiees-back-btn-styles')) return;

        const css = `
            /* ═══ Universal Master Back Button Styling (Amber-Gold Luxury) ═══ */
            .magaiees-back-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 8px !important;
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
                color: #ffffff !important;
                font-family: 'Tajawal', sans-serif !important;
                font-size: 0.88rem !important;
                font-weight: 800 !important;
                padding: 7px 16px !important;
                border-radius: 12px !important;
                border: 1.5px solid rgba(254, 240, 138, 0.75) !important;
                box-shadow: 0 4px 16px rgba(245, 158, 11, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
                cursor: pointer !important;
                text-decoration: none !important;
                user-select: none !important;
                -webkit-user-select: none !important;
                transition: all 0.28s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                white-space: nowrap !important;
                direction: rtl !important;
                line-height: 1.2 !important;
                box-sizing: border-box !important;
                position: relative !important;
                overflow: hidden !important;
            }

            .magaiees-back-btn::after {
                content: '' !important;
                position: absolute !important;
                top: 0 !important; left: -100% !important;
                width: 60% !important; height: 100% !important;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent) !important;
                transform: skewX(-20deg) !important;
                animation: magaieesBackShine 5s infinite !important;
                pointer-events: none !important;
            }

            @keyframes magaieesBackShine {
                0% { left: -100%; }
                20% { left: 200%; }
                100% { left: 200%; }
            }

            .magaiees-back-btn:hover {
                transform: translateY(-2px) scale(1.04) !important;
                background: linear-gradient(135deg, #fbbf24 0%, #ea580c 100%) !important;
                border-color: #ffffff !important;
                box-shadow: 0 6px 24px rgba(245, 158, 11, 0.7), inset 0 1px 2px rgba(255, 255, 255, 0.6) !important;
                color: #ffffff !important;
            }

            .magaiees-back-btn:active {
                transform: scale(0.95) !important;
                box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5) !important;
            }

            .magaiees-back-btn .back-icon-svg {
                width: 17px !important;
                height: 17px !important;
                flex-shrink: 0 !important;
                transition: transform 0.28s ease !important;
            }

            .magaiees-back-btn:hover .back-icon-svg {
                transform: translateX(3px) !important;
            }

            /* Floating Mode (when scrolled or on headerless pages) */
            .magaiees-back-btn.floating-docked {
                position: fixed !important;
                top: 16px !important;
                left: 24px !important;
                z-index: 99998 !important;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.75), 0 0 20px rgba(245, 158, 11, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.5) !important;
                backdrop-filter: blur(12px) !important;
                animation: backBtnFloatIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            }

            @keyframes backBtnFloatIn {
                from { opacity: 0; transform: translateY(-12px) scale(0.9); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            @media (max-width: 640px) {
                .magaiees-back-btn {
                    padding: 6px 10px !important;
                    font-size: 0.8rem !important;
                }
                .magaiees-back-btn.floating-docked {
                    top: 10px !important;
                    left: 10px !important;
                }
            }
        `;

        const style = document.createElement('style');
        style.id = 'magaiees-back-btn-styles';
        style.textContent = css;
        document.head.appendChild(style);
    }

    // 4. Generate Back Button HTML
    function createBackButtonElement() {
        const btn = document.createElement('button');
        btn.id = 'magaiees-master-back-btn';
        btn.className = 'magaiees-back-btn';
        btn.type = 'button';
        btn.onclick = window.magaieesGoBack;
        btn.setAttribute('title', 'الرجوع للخلف (الشاشة السابقة)');
        btn.setAttribute('aria-label', 'الرجوع للخلف');

        btn.innerHTML = `
            <svg class="back-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 19 12 12 5"></polyline>
            </svg>
            <span class="back-btn-text">الرجوع للخلف</span>
        `;
        return btn;
    }

    // 5. Mount or Ensure Button in DOM
    function ensureBackButton() {
        injectStyles();

        // If button already exists in DOM, just make sure scroll listener is attached
        let existingBtn = document.getElementById('magaiees-master-back-btn');
        if (!existingBtn) {
            const header = document.querySelector('header.premium-header, .magical-glass-header');
            if (header) {
                // Find controls container inside header (rendered on the left in RTL)
                let controlsWrap = header.querySelector('.header-controls, div[style*="justify-content:flex-end"], div[style*="justify-content: flex-end"]');
                if (controlsWrap) {
                    const btn = createBackButtonElement();
                    controlsWrap.appendChild(btn);
                    existingBtn = btn;
                } else {
                    const btn = createBackButtonElement();
                    header.appendChild(btn);
                    existingBtn = btn;
                }
            } else {
                // No header present on page: inject as floating docked button at top-left
                const btn = createBackButtonElement();
                btn.classList.add('floating-docked');
                document.body.appendChild(btn);
                existingBtn = btn;
            }
        }

        // Scroll listener: if page has scrolled past header, float the button
        if (existingBtn && !existingBtn.classList.contains('floating-docked')) {
            const header = document.querySelector('header.premium-header, .magical-glass-header');
            if (header) {
                let isFloating = false;
                window.addEventListener('scroll', function () {
                    const scrollY = window.scrollY || window.pageYOffset || 0;
                    if (scrollY > 110 && !isFloating) {
                        isFloating = true;
                        existingBtn.classList.add('floating-docked');
                    } else if (scrollY <= 110 && isFloating) {
                        isFloating = false;
                        existingBtn.classList.remove('floating-docked');
                    }
                }, { passive: true });
            }
        }
    }

    // Initialize on DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureBackButton);
    } else {
        ensureBackButton();
    }
})();
