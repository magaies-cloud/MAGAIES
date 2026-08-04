<script>
    // قراءة الثيم الذي اختاره الباشمهندس مهدي من الشاشة الموحدة وتطبيقه طالما الصفحة تفتح
    const currentTheme = localStorage.getItem('magaiees_global_theme') || 'theme-imperial';
    document.documentElement.className = currentTheme;
    
    // رصد وتطبيق كلاس الشريط الجانبي فورا على HTML و Body لمنع اختفاء/تداخل الشاشة خلف الشريط الجانبي عند بداية الفتح
    const pageName = window.location.pathname.split('/').pop().toLowerCase();
    if (pageName !== 'login.php' && pageName !== 'index.php' && pageName !== 'register.php' && !pageName.includes('preview')) {
        document.documentElement.classList.add('has-sidebar');
        if (document.body) {
            document.body.classList.add('has-sidebar');
        } else {
            document.addEventListener("DOMContentLoaded", function() {
                if (document.body) document.body.classList.add('has-sidebar');
            });
        }
    }

    const isReportPage = pageName && pageName !== 'smart_reports_hub.php' && pageName !== 'dashboard-pro.php' && pageName !== 'login.php' && pageName !== 'index.php' && pageName !== 'inventory-report.php' && pageName !== 'analytics.php';
    if (isReportPage) {
        document.documentElement.classList.add('inner-report-page');
    } else if (pageName === 'inventory-report.php') {
        document.documentElement.classList.add('inventory-hub-page');
    } else if (pageName === 'analytics.php') {
        document.documentElement.classList.add('hub-report-page');
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (document.body) {
            document.body.classList.add(currentTheme);
            if (document.documentElement.classList.contains('has-sidebar')) {
                document.body.classList.add('has-sidebar');
            }
        }
    });
</script>
<header class="premium-header <?php echo isset($header_theme_class) ? $header_theme_class : ''; ?>" style="display:flex; justify-content:space-between; align-items:center; gap:15px;">
    <!-- Left: Logo / Brand -->
    <div style="display:flex; align-items:center; gap:12px; flex:1;">
        <div style="background:linear-gradient(135deg,#10b981,#34d399); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">📦</div>
        <div>
            <div style="color:#fff; font-weight:800; font-size:1rem; letter-spacing:0.5px;" id="header-company-name-top" data-i18n="sidebar.brand">مقاييس (MAGAIEES)</div>
            <div style="color:var(--text-secondary); font-size:0.7rem;" data-i18n="header.systemName">نظام إدارة الأعمال</div>
        </div>
    </div>

    <!-- Center: Page Title -->
    <?php
    $dynamic_icon = '✨'; // default
    if (isset($page_title)) {
        if (strpos($page_title, 'لوحة التحكم') !== false || strpos($page_title, 'SaaS') !== false || strpos($page_title, 'Dashboard') !== false) $dynamic_icon = '🎛️';
        elseif (strpos($page_title, 'مبيعات') !== false || strpos($page_title, 'المبيعات') !== false || strpos($page_title, 'Sales') !== false) $dynamic_icon = '🛒';
        elseif (strpos($page_title, 'مشتريات') !== false || strpos($page_title, 'المشتريات') !== false || strpos($page_title, 'استلام') !== false || strpos($page_title, 'Purchase') !== false) $dynamic_icon = '🛍️';
        elseif (strpos($page_title, 'مخزون') !== false || strpos($page_title, 'مخازن') !== false || strpos($page_title, 'جرد') !== false || strpos($page_title, 'صنف') !== false || strpos($page_title, 'أصناف') !== false || strpos($page_title, 'تسليم') !== false || strpos($page_title, 'أذونات') !== false || strpos($page_title, 'Inventory') !== false) $dynamic_icon = '📦';
        elseif (strpos($page_title, 'حساب') !== false || strpos($page_title, 'مالي') !== false || strpos($page_title, 'صرف') !== false || strpos($page_title, 'قبض') !== false || strpos($page_title, 'خزن') !== false || strpos($page_title, 'بنوك') !== false || strpos($page_title, 'أرصدة') !== false || strpos($page_title, 'أرباح') !== false || strpos($page_title, 'ديون') !== false || strpos($page_title, 'قيد') !== false || strpos($page_title, 'Finance') !== false || strpos($page_title, 'Account') !== false) $dynamic_icon = '💰';
        elseif (strpos($page_title, 'شيكات') !== false || strpos($page_title, 'Check') !== false) $dynamic_icon = '🧾';
        elseif (strpos($page_title, 'شركاء') !== false || strpos($page_title, 'مهام') !== false || strpos($page_title, 'شغل') !== false || strpos($page_title, 'رواتب') !== false || strpos($page_title, 'Partner') !== false) $dynamic_icon = '👥';
        elseif (strpos($page_title, 'إعدادات') !== false || strpos($page_title, 'تدقيق') !== false || strpos($page_title, 'نشاط') !== false || strpos($page_title, 'نظام') !== false || strpos($page_title, 'أساسية') !== false || strpos($page_title, 'نسخ') !== false || strpos($page_title, 'تصفير') !== false || strpos($page_title, 'Settings') !== false || strpos($page_title, 'Master') !== false) $dynamic_icon = '⚙️';
        elseif (strpos($page_title, 'تقارير') !== false || strpos($page_title, 'إحصائيات') !== false || strpos($page_title, 'تحليل') !== false || strpos($page_title, 'Report') !== false) $dynamic_icon = '📊';
        elseif (strpos($page_title, 'تنبيهات') !== false || strpos($page_title, 'Alert') !== false) $dynamic_icon = '🔔';
        elseif (strpos($page_title, 'باركود') !== false || strpos($page_title, 'Barcode') !== false) $dynamic_icon = '🖨️';
        elseif (strpos($page_title, 'Audit') !== false || strpos($page_title, 'الصندوق') !== false) $dynamic_icon = '🔒';
    }
    ?>
    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;">
        <h2 class="header-page-title">
            <span class="header-page-title-icon"><?php echo $dynamic_icon; ?></span>
            <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'مقاييس (MAGAIEES)'; ?>
        </h2>
        <div id="dashboard-athkar-container" class="islamic-ticker-container" style="display: none;">
            <div class="islamic-ticker">
                سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
            </div>
        </div>
    </div>

    <!-- Right: Controls -->
    <div style="display:flex; align-items:center; gap:12px; flex:1; justify-content:flex-end;">
        <!-- User Info & Online Users -->
        <div style="display:flex; flex-direction:column; align-items:flex-end; justify-content:center; gap:6px; margin:6px 0;">
            <div class="user-info" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); padding: 5px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); cursor: pointer; min-width: 90px;" onclick="document.getElementById('profile-modal').style.display='flex'">
                <div id="header-user-name" style="font-size: 0.95rem; font-weight: 800; color: #fff; text-align: center;">جاري التحميل...</div>
                <div id="header-user-role" style="font-size: 0.75rem; color: #38bdf8; text-align: center;" data-i18n="header.sysAdmin">مدير النظام</div>
            </div>
            <div id="online-users-badge" style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:3px 12px; min-width:90px; text-align:center; box-shadow:inset 0 0 10px rgba(16,185,129,0.1); cursor:pointer;" onclick="alert('المستخدمين المتصلين حالياً: \n' + (window.onlineUsersList || []).join('\n'))">
                <span style="color:#6ee7b7; font-size:0.7rem; font-weight:800;"><span data-i18n="header.onlineNow">متصل الآن:</span> <span id="online-users-count" style="color:#fff; font-size:0.8rem;">1</span> <span style="font-size:0.6rem;">🟢</span></span>
            </div>
        </div>

        <!-- Clock & Exchange Rate -->
        <div class="header-time-stack" style="display:flex; flex-direction:column; gap:6px; align-items:center; margin:6px 0;">
            <div class="clock-container" style="display:flex; flex-direction:column; align-items:center; background:rgba(15,23,42,0.6); padding:4px 14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); min-width:110px; width:100%; box-sizing:border-box;">
                <span id="header-clock" style="color:#34d399; font-size:1.1rem; font-weight:800; font-variant-numeric:tabular-nums; letter-spacing:1px; text-shadow: 0 0 10px rgba(16,185,129,0.5);">00:00:00</span>
                <span id="header-date-mini" style="color:var(--text-secondary); font-size:0.65rem; margin-top:1px;">جاري التحميل...</span>
            </div>
            <div id="global-exchange-rate-box" style="display:none; position:relative; cursor:pointer; background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3); border-radius:10px; padding:3px 12px; min-width:110px; text-align:center; box-shadow:inset 0 0 10px rgba(245,158,11,0.1); width:100%; box-sizing:border-box;" onclick="toggleExchangeHistory(event)">
                <span style="color:#fbd38d; font-size:0.75rem; font-weight:800;">
                    <span id="global-exchange-rate-trend" style="margin-left:4px;"></span>
                    <span data-i18n="header.exchangeRate">سعر الصرف:</span> 
                    <span id="global-exchange-rate-val" style="color:#fff; font-size:0.85rem;">-</span>
                </span>
            </div>
        </div>
        <!-- Theme & Currency -->
        <div style="display:flex; align-items:center; gap:8px;">
            <?php
            $current_page_name = basename($_SERVER['PHP_SELF']);
            $show_header_currency = false; // Disabled by user request
            if ($show_header_currency):
                $header_curr = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : (isset($_GET['currency']) ? $_GET['currency'] : 'SDG');
            ?>
            <?php endif; ?>

            <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                <button class="fullscreen-toggle-btn" onclick="toggleFullScreen()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.15rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.fullscreen" title="توسيع الشاشة">⛶</button>
                <button class="theme-toggle-btn" onclick="toggleTheme()" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" data-i18n-title="header.toggleTheme" title="تبديل المظهر">🌓</button>
            </div>
        </div>
        <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error("Error enabling full-screen:", err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
        
        // Theme initialization and toggle logic
        if (localStorage.getItem('erp_theme') === 'light') {
            document.documentElement.classList.add('light-theme');
        }
        function toggleTheme() {
            document.documentElement.classList.toggle('light-theme');
            localStorage.setItem('erp_theme', document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
            window.dispatchEvent(new Event('themeChanged'));
        }

        // Global Backdrop Click to Go Back Interaction (Immediate, bypasses JS cache)
        document.addEventListener('click', function(e) {
            try {
                if (!e || !e.target) return;
                
                // Exclude home/dashboard and login screens
                const path = window.location.pathname.toLowerCase();
                if (path.includes('dashboard-pro') || path.includes('login') || path.includes('index') || path === '/' || path.endsWith('/magaees') || path.endsWith('/magaees/')) {
                    return;
                }

                const target = e.target;
                if (typeof target.closest !== 'function') return;

                // التقييد: تفعيل زر الرجوع للخلف عند الضغط في المساحات الفارغة داخل الترويسة والشريط الجانبي فقط
                if (!target.closest('.global-sidebar') && !target.closest('header.premium-header')) {
                    return; // تجاهل النقرات في أي مكان آخر (مثل شاشة التقارير أو الحاويات الرئيسية)
                }

                // Protect ONLY interactive elements. We want background/empty areas to trigger 'back'
                const interactiveSelectors = [
                    'button', 'input', 'select', 'textarea', 'a', 'label', 'table', 'form', 'li', 'ul',
                    '.modal-overlay', '.modal', '.custom-dropdown-list', '.toast-container', '.choices',
                    '.user-info', '#online-users-badge', '.clock-container', '.crystal-pill', '.bal-card',
                    '.autocomplete-list', '[onclick]' // Any element with an onclick attribute
                ];

                for (let i = 0; i < interactiveSelectors.length; i++) {
                    if (target.closest(interactiveSelectors[i])) {
                        return; // Ignored as click is in or on an interactive component
                    }
                }

                // If we get here, it's an empty space, margin, or layout container
                // Navigate back immediately
                e.preventDefault();
                e.stopPropagation();
                if (document.referrer) {
                    window.history.back();
                } else {
                    window.location.href = 'dashboard-pro.php';
                }
            } catch (err) {
                console.error('Error in backdrop click:', err);
            }
        }, true); // Use CAPTURE phase to intercept before any other script calls stopPropagation
        </script>
    </div>
</header>
<script src="js/auto-charts.js"></script>
