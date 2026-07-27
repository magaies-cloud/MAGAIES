<div class="settings-panel no-print" id="settings-panel">
    <!-- Studio Tabs Header -->
    <div class="studio-tabs" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ca8a04; margin-bottom: 10px; padding-bottom: 6px;">
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button class="studio-tab-btn active" id="tab-btn-cols" onclick="switchStudioTab('cols')">📊 إعدادات الأعمدة والتنسيق</button>
            <button class="studio-tab-btn" id="tab-btn-paper" onclick="switchStudioTab('paper')">📄 إعدادات الورقة والطباعة</button>
            <button class="studio-tab-btn" id="tab-btn-presets" onclick="switchStudioTab('presets')">💎 التخصيصات الجاهزة</button>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <button id="reset-design-btn" class="ribbon-btn" style="background: linear-gradient(135deg, #64748b, #475569); color:#fff; border:none; padding:5px 16px; font-size:0.85rem; font-weight:800; border-radius:18px; box-shadow: 0 2px 8px rgba(71,85,105,0.3); position: relative; overflow: hidden; display: flex; align-items: center; gap: 4px; cursor: pointer; user-select: none;">
                <span class="btn-progress" style="position: absolute; right: 0; top: 0; bottom: 0; width: 0%; background: rgba(255,255,255,0.25); transition: none; pointer-events: none;"></span>
                <span class="btn-text">🔄 إعادة ضبط</span>
            </button>
            <button class="ribbon-btn" onclick="saveCurrentPreset()" style="background: linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:5px 16px; font-size:0.85rem; font-weight:800; border-radius:18px; box-shadow: 0 2px 8px rgba(16,185,129,0.3);">
                💾 حفظ الإعدادات
            </button>
        </div>
    </div>

    <!-- TAB 1: Columns & Formatting -->
    <div id="studio-tab-cols">
        <!-- Row 1: Column Selection & Reordering -->
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>العمود المحدد:</span>
                <select id="selected-col-select" class="panel-input" style="color:#dc2626; font-size:0.95rem; font-weight:900; border-color:#dc2626; background:#ffffff;" onchange="selectColumn(parseInt(this.value))">
                    <!-- Column options populated dynamically via JS -->
                </select>
                
                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" id="col-visible-chk" checked onchange="updateSelectedCol('visible', this.checked)">
                    إظهار
                </label>

                <!-- Restore Hidden Columns Dropdown -->
                <select id="restore-hidden-col-select" class="panel-input" style="background:#fffeb3; border-color:#d97706;" onchange="restoreHiddenColumn(this.value)">
                    <option value="">👁️ الأعمدة المخفية (0)</option>
                </select>
            </div>

            <div class="panel-group">
                <span>إعادة تسمية الترويسة:</span>
                <input type="text" id="col-label-input" class="panel-input" style="width:130px;" oninput="updateSelectedCol('label', this.value)">
                
                <span>الترتيب:</span>
                <button class="ribbon-btn" style="padding:3px 10px; color:#000; background:#fff; border-color:#ca8a04;" onclick="moveSelectedCol(-1)">➡️ يمين</button>
                <button class="ribbon-btn" style="padding:3px 10px; color:#000; background:#fff; border-color:#ca8a04;" onclick="moveSelectedCol(1)">⬅️ يسار</button>
            </div>

            <div class="panel-group">
                <span>المحاذاة:</span>
                <select id="col-align-select" class="panel-input" onchange="updateSelectedCol('align', this.value)">
                    <option value="right">يمين ➡️</option>
                    <option value="center" selected>وسط 🟢</option>
                    <option value="left">يسار ⬅️</option>
                </select>
            </div>
        </div>

        <!-- Row 2: Colors, Font Sizes, Font Weights & Global Themes -->
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>حجم الخط:</span>
                <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" oninput="updateSelectedCol('fs', parseFloat(this.value))">
                <span id="col-fs-val">0.9rem</span>

                <span>وزن الخط:</span>
                <select id="col-fw-select" class="panel-input" onchange="updateSelectedCol('fontWeight', this.value)">
                    <option value="400">عادي (Normal 400)</option>
                    <option value="500">متوسط (Medium 500)</option>
                    <option value="600">بارز (Semi-Bold 600)</option>
                    <option value="700">عريض (Bold 700)</option>
                    <option value="800" selected>عريض جداً (Extra-Bold 800)</option>
                    <option value="900">أسود داكن (Black 900)</option>
                </select>

                <span>نوع الخط:</span>
                <select id="col-font-select" class="panel-input" onchange="updateSelectedCol('fontFamily', this.value)">
                    <option value="inherit">الافتراضي (افتراضي المستند)</option>
                    <option value="'Inter', sans-serif">Inter (إنتـر المريح - Normal 400)</option>
                    <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans (جاكارتا للأرقام)</option>
                    <option value="'Roboto', sans-serif">Roboto (روبوتو وضوح فائق للأرقام)</option>
                    <option value="'Outfit', sans-serif">Outfit (أوتفيت العصري)</option>
                    <option value="'Tajawal', sans-serif">Tajawal (تجوال العصري)</option>
                    <option value="'Cairo', sans-serif">Cairo (القاهرة الفاخر)</option>
                    <option value="'Almarai', sans-serif">Almarai (المراعي النظيف)</option>
                    <option value="'Alexandria', sans-serif">Alexandria (الإسكندرية الحديث)</option>
                    <option value="'Changa', sans-serif">Changa (شانغا الهندسي)</option>
                    <option value="'Amiri', serif">Amiri (الأميري الكلاسيكي)</option>
                    <option value="'JetBrains Mono', monospace">JetBrains Mono (رقمي برمجيات)</option>
                    <option value="'Roboto Mono', monospace">Roboto Mono (روبوتو أحادي)</option>
                    <option value="'Fira Code', monospace">Fira Code (فيرا كود الحسابي)</option>
                </select>

                <span>لون الخط:</span>
                <input type="color" id="col-color-picker" oninput="updateSelectedCol('color', this.value)">

                <span>خلفية العمود:</span>
                <input type="color" id="col-bg-picker" value="#ffffff" oninput="updateSelectedCol('bgColor', this.value)">
            </div>

            <div class="panel-group">
                <span>✨ أنماط العرض الفاخرة:</span>
                <select id="table-theme-select" class="panel-input" style="font-weight:800;" onchange="setTableTheme(this.value)">
                    <option value="">⚪ 1. الأساسي المعياري</option>
                    <option value="theme-classic-navy">🔷 2. الأزرق الكلاسيكي (Classic Navy)</option>
                    <option value="theme-emerald-pro">💚 3. الزمردي الراقي (Emerald Pro)</option>
                    <option value="theme-modern-slate">🩶 4. الرمادي العصري (Modern Slate)</option>
                    <option value="theme-luxury-gold">👑 5. الذهبي الملكي (Luxury Gold)</option>
                    <option value="theme-gold">🟡 6. الأصفر الغامق (Amber Gold)</option>
                    <option value="theme-lightblue">🔵 7. الأزرق الفاتح (Ocean Blue)</option>
                    <option value="theme-slate">⚪ 8. الرمادي الكلاسيكي (Slate Gray)</option>
                    <option value="theme-luxury-black">🖤 9. الأسود الفاخر (Luxury Black)</option>
                </select>
            </div>
        </div>

        <!-- Row 3: Number Formatting, Decimals, Separators & Rounding -->
        <div class="panel-card">
            <div class="panel-group">
                <span>الفاصلة الألفية:</span>
                <select id="col-sep-select" class="panel-input" onchange="updateSelectedCol('thousandSep', this.value)">
                    <option value="," selected>كومة ( 1,000 )</option>
                    <option value=".">نقطة ( 1.000 )</option>
                    <option value=" ">مسافة ( 1 000 )</option>
                    <option value="'">اقتباس ( 1'000 )</option>
                    <option value="_">شريط ( 1_000 )</option>
                </select>

                <span>الخانات العشرية:</span>
                <select id="col-dec-select" class="panel-input" onchange="updateSelectedCol('decimals', parseInt(this.value))">
                    <option value="0">0 (بدون كسور)</option>
                    <option value="1">1 (0.0)</option>
                    <option value="2" selected>2 (0.00)</option>
                    <option value="3">3 (0.000)</option>
                    <option value="4">4 (0.0000)</option>
                </select>

                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" id="col-dec-sep-chk" checked onchange="updateSelectedCol('showDecSep', this.checked)">
                    إظهار فاصلة الكسور
                </label>
            </div>

            <div class="panel-group">
                <span>التقريب:</span>
                <select id="col-rounding-select" class="panel-input" onchange="updateSelectedCol('rounding', this.value)">
                    <option value="nearest" selected>تقريب عادي (Math.round)</option>
                    <option value="up">للأعلى (Math.ceil)</option>
                    <option value="down">للأسفل (Math.floor)</option>
                    <option value="cut">قطع الكسر بدون تقريب</option>
                </select>

                <span>خط الأرقام:</span>
                <select id="num-font-select" class="panel-input" onchange="setNumberFont(this.value)">
                    <option value="font-ultra" selected>1. Inter (إنتـر المريح - Normal 400)</option>
                    <option value="font-jakarta">2. Plus Jakarta Sans (أفضل خط مالي للأرقام)</option>
                    <option value="font-roboto">3. Roboto (روبوتو القياسي الأوضح للأرقام)</option>
                    <option value="font-outfit">4. Outfit (أوتفيت العصري للأرقام)</option>
                    <option value="font-newsons">5. JetBrains Mono (رقمي برمجيات)</option>
                    <option value="font-ribot">6. Roboto Mono (روبوتو أحادي)</option>
                    <option value="font-firacode">7. Fira Code (فيرا كود الحسابي)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TAB 2: Paper & Print Setup -->
    <div id="studio-tab-paper" style="display:none;">
        <div class="panel-card" style="margin-bottom: 8px;">
            <div class="panel-group">
                <span>📏 مقاس الورقة (Paper Size):</span>
                <select id="paper-size-select" class="panel-input" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (المعياري - 210x297 mm)</option>
                    <option value="A3">A3 (الموسع - 297x420 mm)</option>
                    <option value="A5">A5 (المصغر - 148x210 mm)</option>
                    <option value="Letter">Letter (الرسائل - 8.5x11 in)</option>
                    <option value="Legal">Legal (المستندات - 8.5x14 in)</option>
                </select>
            </div>

            <div class="panel-group">
                <span>📐 اتجاه الورقة (Orientation):</span>
                <select id="paper-orient-select" class="panel-input" onchange="setPageOrientation(this.value)">
                    <option value="landscape" selected>📜 عرضي (Landscape)</option>
                    <option value="portrait">📄 طولي (Portrait)</option>
                </select>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-group">
                <span>👁️ التحكم بعناصر الترويسة العليا:</span>
                
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer; background:rgba(255,255,255,0.6); padding:5px 12px; border-radius:6px; border:1px solid #ca8a04;">
                    <input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo', this.checked)">
                    إظهار شعار الشركة (Logo)
                </label>

                <label style="display:flex; align-items:center; gap:5px; cursor:pointer; background:rgba(255,255,255,0.6); padding:5px 12px; border-radius:6px; border:1px solid #ca8a04;">
                    <input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info', this.checked)">
                    إظهار اسم وبيانات الشركة (الهاتف والتفاصيل)
                </label>
            </div>
        </div>
    </div>

    <!-- TAB 3: Presets Management -->
    <div id="studio-tab-presets" style="display:none;">
        <div class="panel-card" style="margin-bottom: 10px; justify-content: space-between; align-items: center;">
            <span style="font-size:0.95rem; font-weight:800;">💎 إدارة التخصيصات الجاهزة:</span>
            <button class="ribbon-btn" onclick="addNewPresetPrompt()" style="background: linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; padding:5px 16px; font-weight:800; border-radius:18px;">
                ➕ إضافة تخصيص جديد
            </button>
        </div>

        <div id="presets-management-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 10px;">
            <!-- Presets list items rendered via JS -->
        </div>
    </div>
</div>

