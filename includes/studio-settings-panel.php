<link rel="stylesheet" href="css/studio-panel.css?v=<?php echo file_exists('css/studio-panel.css') ? filemtime('css/studio-panel.css') : time(); ?>">
<div class="settings-panel no-print" id="settings-panel">
    <!-- Tabs Bar -->
    <div class="studio-tabs" id="studio-tabs-bar">
        <div style="display:flex; gap:0;">
            <button class="ms-ribbon-tab-btn active" id="tab-btn-cols" onclick="switchStudioTab('cols')">الصفحة الرئيسية</button>
            <button class="ms-ribbon-tab-btn" id="tab-btn-paper" onclick="switchStudioTab('paper')">تخطيط الصفحة</button>
            <button class="ms-ribbon-tab-btn" id="tab-btn-presets" onclick="switchStudioTab('presets')">التخصيصات</button>
        </div>
        <div style="display:flex; gap:6px; align-items:center;">
            <button id="reset-design-btn" class="ms-icon-btn" style="width:auto; padding:0 8px; font-size:0.8rem;"><span class="btn-text">🔄 إعادة ضبط</span></button>
            <button id="save-settings-btn-main" class="ms-icon-btn" onclick="saveCurrentPreset()" style="width:auto; padding:0 8px; font-size:0.8rem; color:#4ade80;">💾 حفظ</button>
            <button type="button" onclick="toggleSettingsPanel()" class="ms-icon-btn" style="width:auto; padding:0 8px; font-size:0.8rem; color:#f87171;">✕ إغلاق</button>
        </div>
    </div>

    <!-- ═══════ TAB 1: Main (Columns & Formatting) ═══════ -->
    <div id="studio-tab-cols" class="ribbon-tab-content">

        <!-- ── Group: خط (Font) ── -->
        <div class="ms-ribbon-group">
            <!-- Row 1: Font Name + Size -->
            <div class="ms-ribbon-row">
                <select id="col-font-select" class="ms-ribbon-input lg" style="width:140px;" onchange="updateSelectedCol('fontFamily', this.value)">
                    <option value="inherit">الافتراضي</option>
                    <option value="'Inter', sans-serif">Inter</option>
                    <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta Sans</option>
                    <option value="'Roboto', sans-serif">Roboto</option>
                    <option value="'Outfit', sans-serif">Outfit</option>
                    <option value="'Tajawal', sans-serif">Tajawal</option>
                    <option value="'Cairo', sans-serif">Cairo</option>
                    <option value="'Almarai', sans-serif">Almarai</option>
                    <option value="'Alexandria', sans-serif">Alexandria</option>
                    <option value="'Changa', sans-serif">Changa</option>
                    <option value="'Amiri', serif">Amiri</option>
                    <option value="'JetBrains Mono', monospace">JetBrains Mono</option>
                    <option value="'Roboto Mono', monospace">Roboto Mono</option>
                    <option value="'Fira Code', monospace">Fira Code</option>
                </select>
                <input type="number" id="col-fs-input-ribbon" class="ms-ribbon-input sm" step="0.1" value="0.9" title="حجم الخط" oninput="document.getElementById('col-fs-range').value=this.value; updateSelectedCol('fs', parseFloat(this.value))">
                <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" style="display:none;" oninput="document.getElementById('col-fs-input-ribbon').value=this.value; updateSelectedCol('fs', parseFloat(this.value))">
                <button class="ms-icon-btn" title="تكبير الخط" onclick="let v=parseFloat(document.getElementById('col-fs-input-ribbon').value||0.9)+0.1; document.getElementById('col-fs-input-ribbon').value=v.toFixed(1); updateSelectedCol('fs',v);" style="font-size:1rem; font-weight:900;">A<span style="font-size:0.6rem;vertical-align:super;">▲</span></button>
                <button class="ms-icon-btn" title="تصغير الخط" onclick="let v=parseFloat(document.getElementById('col-fs-input-ribbon').value||0.9)-0.1; if(v<0.5)v=0.5; document.getElementById('col-fs-input-ribbon').value=v.toFixed(1); updateSelectedCol('fs',v);" style="font-size:0.85rem; font-weight:900;">A<span style="font-size:0.55rem;vertical-align:super;">▼</span></button>
            </div>
            <!-- Row 2: Weight + Colors -->
            <div class="ms-ribbon-row">
                <select id="col-fw-select" class="ms-ribbon-input" style="width:90px;" onchange="updateSelectedCol('fontWeight', this.value)" title="وزن الخط">
                    <option value="400">عادي 400</option>
                    <option value="500">متوسط 500</option>
                    <option value="600">بارز 600</option>
                    <option value="700">عريض 700</option>
                    <option value="800" selected>عريض+ 800</option>
                    <option value="900">أسود 900</option>
                </select>

                <!-- Font Color Button (A with colored bar) -->
                <label class="ms-color-btn" title="لون الخط">
                    <span class="letter" style="color:#e0e0e0;">A</span>
                    <span class="bar" id="font-color-bar" style="background:#ff0000;"></span>
                    <input type="color" id="col-color-picker" oninput="updateSelectedCol('color', this.value); document.getElementById('font-color-bar').style.background=this.value;">
                </label>

                <!-- Background Color Button (paint bucket with colored bar) -->
                <label class="ms-color-btn" title="تلوين خلفية العمود">
                    <span class="letter" style="font-size:0.85rem;">🪣</span>
                    <span class="bar" id="bg-color-bar" style="background:#ffff00;"></span>
                    <input type="color" id="col-bg-picker" value="#ffffff" oninput="updateSelectedCol('bgColor', this.value); document.getElementById('bg-color-bar').style.background=this.value;">
                </label>
            </div>
            <span class="ms-ribbon-group-title">خط (Font)</span>
        </div>

        <!-- ── Group: محاذاة (Alignment) ── -->
        <div class="ms-ribbon-group">
            <!-- Row 1: Text Alignment -->
            <div class="ms-ribbon-row">
                <select id="col-align-select" class="ms-ribbon-input" style="width:100px;" onchange="updateSelectedCol('align', this.value)" title="محاذاة النص">
                    <option value="right">➡ محاذاة يمين</option>
                    <option value="center" selected>☰ توسيط</option>
                    <option value="left">⬅ محاذاة يسار</option>
                </select>
            </div>
            <!-- Row 2: Number Font -->
            <div class="ms-ribbon-row">
                <select id="num-font-select" class="ms-ribbon-input" style="width:100px;" onchange="setNumberFont(this.value)" title="خط الأرقام">
                    <option value="font-ultra" selected>أرقام Inter</option>
                    <option value="font-jakarta">أرقام Jakarta</option>
                    <option value="font-roboto">أرقام Roboto</option>
                    <option value="font-outfit">أرقام Outfit</option>
                    <option value="font-newsons">أرقام JetBrains</option>
                </select>
            </div>
            <span class="ms-ribbon-group-title">محاذاة (Alignment)</span>
        </div>

        <!-- ── Group: العمود (Column) ── -->
        <div class="ms-ribbon-group">
            <!-- Row 1: Column select + Label + Show -->
            <div class="ms-ribbon-row">
                <select id="selected-col-select" class="ms-ribbon-input lg" style="width:120px; color:#60a5fa; font-weight:700;" onchange="selectColumn(parseInt(this.value))" title="العمود المحدد"></select>
                <input type="text" id="col-label-input" class="ms-ribbon-input" style="width:90px;" placeholder="تسمية العمود..." oninput="updateSelectedCol('label', this.value)">
                <label style="display:flex; align-items:center; gap:3px; font-size:0.82rem; cursor:pointer; color:#d4d4d4;">
                    <input type="checkbox" id="col-visible-chk" checked onchange="updateSelectedCol('visible', this.checked)" style="accent-color:#4fc3f7;">
                    إظهار
                </label>
            </div>
            <!-- Row 2: Width + Move + Hidden -->
            <div class="ms-ribbon-row">
                <span style="font-size:0.78rem; color:#a0a0a0;">عرض%</span>
                <input type="number" id="col-width-input" class="ms-ribbon-input sm" min="5" max="60" placeholder="%" oninput="if(document.getElementById('col-width-range')) document.getElementById('col-width-range').value=this.value; updateSelectedCol('width', this.value)">
                <input type="range" id="col-width-range" style="display:none;" min="5" max="60" step="1" value="15" oninput="document.getElementById('col-width-input').value=this.value; updateSelectedCol('width', this.value)">
                <button class="ms-icon-btn" style="font-size:0.7rem;" onclick="updateSelectedCol('width', ''); document.getElementById('col-width-input').value=''; document.getElementById('col-width-range').value=15;" title="عرض تلقائي">تلقائي</button>
                <button class="ms-icon-btn" onclick="moveSelectedCol(-1)" title="تحريك يمين">➡</button>
                <button class="ms-icon-btn" onclick="moveSelectedCol(1)" title="تحريك يسار">⬅</button>
                <select id="restore-hidden-col-select" class="ms-ribbon-input" style="width:90px;" onchange="restoreHiddenColumn(this.value)"><option value="">👁 مخفية (0)</option></select>
            </div>
            <span class="ms-ribbon-group-title">العمود (Column)</span>
        </div>

        <!-- ── Group: أرقام (Number) ── -->
        <div class="ms-ribbon-group">
            <!-- Row 1: Separators -->
            <div class="ms-ribbon-row">
                <select id="col-sep-select" class="ms-ribbon-input" style="width:76px;" onchange="updateSelectedCol('thousandSep', this.value)" title="الفاصلة الألفية">
                    <option value="," selected>1,000</option>
                    <option value=".">1.000</option>
                    <option value=" ">1 000</option>
                    <option value="'">1'000</option>
                </select>
                <select id="col-dec-select" class="ms-ribbon-input" style="width:60px;" onchange="updateSelectedCol('decimals', parseInt(this.value))" title="الخانات العشرية">
                    <option value="0">بدون</option>
                    <option value="1">.0</option>
                    <option value="2" selected>.00</option>
                    <option value="3">.000</option>
                    <option value="4">.0000</option>
                </select>
            </div>
            <!-- Row 2: Rounding + Decimal Sep -->
            <div class="ms-ribbon-row">
                <select id="col-rounding-select" class="ms-ribbon-input" style="width:82px;" onchange="updateSelectedCol('rounding', this.value)" title="التقريب">
                    <option value="nearest" selected>تقريب عادي</option>
                    <option value="up">للأعلى ↑</option>
                    <option value="down">للأسفل ↓</option>
                    <option value="cut">قطع الكسر</option>
                </select>
                <label style="display:flex; align-items:center; gap:3px; font-size:0.82rem; cursor:pointer; color:#d4d4d4;">
                    <input type="checkbox" id="col-dec-sep-chk" checked onchange="updateSelectedCol('showDecSep', this.checked)" style="accent-color:#4fc3f7;">
                    فاصلة
                </label>
            </div>
            <span class="ms-ribbon-group-title">أرقام (Number)</span>
        </div>

        <!-- ── Group: أنماط (Styles) ── -->
        <div class="ms-ribbon-group">
            <!-- Row 1: Theme + Layout -->
            <div class="ms-ribbon-row">
                <select id="table-theme-select" class="ms-ribbon-input" style="width:120px;" onchange="setTableTheme(this.value)" title="نمط الألوان">
                    <option value="">⚪ الأساسي</option>
                    <option value="theme-classic-navy">🔷 أزرق كلاسيكي</option>
                    <option value="theme-emerald-pro">💚 زمردي راقي</option>
                    <option value="theme-modern-slate">🩶 رمادي عصري</option>
                    <option value="theme-luxury-gold">👑 ذهبي ملكي</option>
                    <option value="theme-gold">🟡 أصفر غامق</option>
                    <option value="theme-lightblue">🔵 أزرق فاتح</option>
                    <option value="theme-slate">⚪ رمادي كلاسيك</option>
                    <option value="theme-luxury-black">🖤 أسود فاخر</option>
                </select>
                <select id="layout-theme-select" class="ms-ribbon-input" style="width:110px;" onchange="setLayoutTheme(this.value)" title="هيكل الفاتورة">
                    <option value="">📑 كلاسيكي</option>
                    <option value="layout-modern">🏢 مودرن مبسط</option>
                    <option value="layout-split">🗂 منفصل (شركات)</option>
                    <option value="layout-compact">📋 مضغوط (مراجعات)</option>
                </select>
            </div>
            <!-- Row 2: Table Width -->
            <div class="ms-ribbon-row">
                <span style="font-size:0.78rem; color:#a0a0a0;">عرض الجدول:</span>
                <input type="range" id="table-width-range" min="50" max="100" step="5" value="100" oninput="setTableWidth(this.value)" style="width:80px; height:16px; accent-color:#4fc3f7;">
                <span id="table-width-label" style="font-size:0.82rem; font-weight:700; min-width:32px; color:#e0e0e0;">100%</span>
            </div>
            <span class="ms-ribbon-group-title">أنماط (Styles)</span>
        </div>
    </div>

    <!-- ═══════ TAB 2: Page Layout ═══════ -->
    <div id="studio-tab-paper" class="ribbon-tab-content" style="display:none;">
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="paper-size-select" class="ms-ribbon-input lg" style="width:110px;" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (المعياري)</option>
                    <option value="A3">A3 (الموسع)</option>
                    <option value="A5">A5 (المصغر)</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>
                <select id="paper-orient-select" class="ms-ribbon-input lg" style="width:100px;" onchange="setPageOrientation(this.value)">
                    <option value="landscape" selected>📜 عرضي</option>
                    <option value="portrait">📄 طولي</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.8rem; color:#a0a0a0;">هوامش:</span>
                <input type="range" id="print-margin-range" min="0" max="30" step="2" value="10" oninput="setPrintMargin(this.value)" style="width:80px; height:16px; accent-color:#4fc3f7;">
                <span id="print-margin-label" style="font-size:0.82rem; color:#e0e0e0;">10mm</span>
            </div>
            <span class="ms-ribbon-group-title">إعداد الصفحة</span>
        </div>

        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem; cursor:pointer;"><input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo', this.checked)" style="accent-color:#4fc3f7;"> شعار الشركة</label>
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem; cursor:pointer;"><input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info', this.checked)" style="accent-color:#4fc3f7;"> بيانات الشركة</label>
            </div>
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem; cursor:pointer;"><input type="checkbox" id="hdr-show-header-chk" checked onchange="toggleHeaderElement('header', this.checked)" style="accent-color:#4fc3f7;"> الترويسة</label>
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem; cursor:pointer;"><input type="checkbox" id="hdr-show-footer-chk" checked onchange="toggleHeaderElement('footer', this.checked)" style="accent-color:#4fc3f7;"> التذييل</label>
            </div>
            <span class="ms-ribbon-group-title">الترويسة والتذييل</span>
        </div>

        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem; cursor:pointer;"><input type="checkbox" id="hdr-print-bg-chk" checked onchange="togglePrintBackground(this.checked)" style="accent-color:#4fc3f7;"> طباعة ألوان الخلفيات</label>
            </div>
            <span class="ms-ribbon-group-title">خيارات الطباعة</span>
        </div>
    </div>

    <!-- Collapse/Expand toggle at bottom-left -->
    <button id="ribbon-collapse-btn" class="ribbon-collapse-btn" onclick="toggleSettingsPanel()" title="طي/توسيع شريط الأدوات">
        <span id="ribbon-toggle-icon">▲</span>
    </button>
</div>
