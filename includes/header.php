<header class="premium-header" style="display:flex; justify-content:space-between; align-items:center; padding:15px; margin-bottom:15px; gap:15px; border-radius: 16px;">
    <!-- Left: Logo / Brand -->
    <div style="display:flex; align-items:center; gap:12px; flex:1;">
        <div style="background:linear-gradient(135deg,#10b981,#34d399); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">📦</div>
        <div>
            <div style="color:#fff; font-weight:800; font-size:1rem; letter-spacing:0.5px;" id="header-company-name-top">السجانة ERP</div>
            <div style="color:var(--text-secondary); font-size:0.7rem;">نظام إدارة الأعمال</div>
        </div>
    </div>

    <!-- Center: Page Title -->
    <div style="flex:1; text-align:center;">
        <h2 class="header-page-title">
            <span class="header-page-title-icon">✨</span>
            <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'السجانة ERP'; ?>
        </h2>
        <div id="dashboard-athkar-container" class="islamic-ticker-container" style="display: none;">
            <div class="islamic-ticker">
                سُبْحَانَ اللَّهِ وَبِحَمْدِهِ ، سُبْحَانَ اللَّهِ الْعَظِيمِ 🌸 أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ 🌸 اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ
            </div>
        </div>
    </div>

    <!-- Right: Controls -->
    <div style="display:flex; align-items:center; gap:12px; flex:1; justify-content:flex-end;">
        <!-- Clock -->
        <div style="display:flex; flex-direction:column; align-items:center; background:rgba(15,23,42,0.6); padding:6px 14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); min-width:110px;">
            <span id="header-clock" style="color:#34d399; font-size:1.1rem; font-weight:800; font-variant-numeric:tabular-nums; letter-spacing:1px; text-shadow: 0 0 10px rgba(16,185,129,0.5);">00:00:00</span>
            <span id="header-date-mini" style="color:var(--text-secondary); font-size:0.65rem; margin-top:1px;">جاري التحميل...</span>
        </div>
        <!-- Theme -->
        <div style="display:flex; align-items:center; gap:8px;">
            <button class="theme-toggle-btn" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" title="تبديل المظهر">🌓</button>
            <button class="back-arrow-btn" onclick="history.back()" style="background:rgba(255,255,255,0.1); border:none; border-radius:12px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" title="رجوع">🔙</button>
        </div>
    </div>
</header>