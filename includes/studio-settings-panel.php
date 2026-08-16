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
            <button id="reset-design-btn" class="ms-ribbon-btn"><span class="btn-text">🔄 ضبط</span></button>
            <button id="save-settings-btn-main" class="ms-ribbon-btn" onclick="saveCurrentPreset()" style="color:#4ade80;">💾 حفظ</button>
            <button type="button" onclick="toggleSettingsPanel()" class="ms-ribbon-btn" style="color:#f87171;">✕</button>
        </div>
    </div>

    <!-- TAB 1: Main Home (Columns & Formatting) -->
    <div id="studio-tab-cols" class="ribbon-tab-content">

        <!-- Group: Font -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-font-select" class="ms-ribbon-input" style="width:110px;" onchange="updateSelectedCol('fontFamily', this.value)">
                    <option value="inherit">الافتراضي</option>
                    <option value="'Inter', sans-serif">Inter</option>
                    <option value="'Plus Jakarta Sans', sans-serif">Plus Jakarta</option>
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
                <input type="number" id="col-fs-input-ribbon" class="ms-ribbon-input" style="width:40px;" step="0.1" value="0.9" oninput="document.getElementById('col-fs-range').value=this.value; updateSelectedCol('fs', parseFloat(this.value))">
                <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" style="display:none;" oninput="document.getElementById('col-fs-input-ribbon').value=this.value; updateSelectedCol('fs', parseFloat(this.value))">
            </div>
            <div class="ms-ribbon-row">
                <select id="col-fw-select" class="ms-ribbon-input" style="width:68px;" onchange="updateSelectedCol('fontWeight', this.value)">
                    <option value="400">عادي</option>
                    <option value="500">متوسط</option>
                    <option value="600">بارز</option>
                    <option value="700">عريض</option>
                    <option value="800" selected>عريض+</option>
                    <option value="900">أسود</option>
                </select>
                <div class="color-picker-group">
                    <label title="لون الخط"><span style="font-size:11px;font-weight:bold;color:#ef4444;padding:0 2px;">A</span><input type="color" id="col-color-picker" class="ms-ribbon-input" oninput="updateSelectedCol('color', this.value)" style="border:none;padding:0;width:14px;height:14px;"></label>
                    <div class="divider"></div>
                    <label title="خلفية العمود"><span style="font-size:10px;padding:0 2px;">🪣</span><input type="color" id="col-bg-picker" class="ms-ribbon-input" value="#ffffff" oninput="updateSelectedCol('bgColor', this.value)" style="border:none;padding:0;width:14px;height:14px;"></label>
                </div>
            </div>
            <span class="ms-ribbon-group-title">خط</span>
        </div>

        <!-- Group: Alignment -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-align-select" class="ms-ribbon-input" style="width:80px;" onchange="updateSelectedCol('align', this.value)">
                    <option value="right">يمين</option>
                    <option value="center" selected>توسيط</option>
                    <option value="left">يسار</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <select id="num-font-select" class="ms-ribbon-input" style="width:80px;" onchange="setNumberFont(this.value)">
                    <option value="font-ultra" selected>Inter</option>
                    <option value="font-jakarta">Jakarta</option>
                    <option value="font-roboto">Roboto</option>
                    <option value="font-outfit">Outfit</option>
                    <option value="font-newsons">JetBrains</option>
                </select>
            </div>
            <span class="ms-ribbon-group-title">محاذاة</span>
        </div>

        <!-- Group: Column -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="selected-col-select" class="ms-ribbon-input" style="width:100px;color:#60a5fa;font-weight:bold;" onchange="selectColumn(parseInt(this.value))"></select>
                <input type="text" id="col-label-input" class="ms-ribbon-input" style="width:80px;" placeholder="تسمية..." oninput="updateSelectedCol('label', this.value)">
                <label style="display:flex;align-items:center;gap:2px;font-size:0.72rem;cursor:pointer;"><input type="checkbox" id="col-visible-chk" checked onchange="updateSelectedCol('visible', this.checked)"> إظهار</label>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.7rem;">عرض:</span>
                <input type="number" id="col-width-input" class="ms-ribbon-input" style="width:38px;" min="5" max="60" placeholder="%" oninput="if(document.getElementById('col-width-range')) document.getElementById('col-width-range').value=this.value; updateSelectedCol('width', this.value)">
                <input type="range" id="col-width-range" style="display:none;" min="5" max="60" step="1" value="15" oninput="document.getElementById('col-width-input').value=this.value; updateSelectedCol('width', this.value)">
                <button class="ms-ribbon-btn" style="font-size:0.65rem;" onclick="updateSelectedCol('width', ''); document.getElementById('col-width-input').value=''; document.getElementById('col-width-range').value=15;">تلقائي</button>
                <button class="ms-ribbon-btn" onclick="moveSelectedCol(-1)">➡</button>
                <button class="ms-ribbon-btn" onclick="moveSelectedCol(1)">⬅</button>
                <select id="restore-hidden-col-select" class="ms-ribbon-input" style="width:80px;" onchange="restoreHiddenColumn(this.value)"><option value="">👁 مخفية</option></select>
            </div>
            <span class="ms-ribbon-group-title">العمود</span>
        </div>

        <!-- Group: Numbers -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-sep-select" class="ms-ribbon-input" style="width:62px;" onchange="updateSelectedCol('thousandSep', this.value)">
                    <option value="," selected>1,000</option>
                    <option value=".">1.000</option>
                    <option value=" ">1 000</option>
                    <option value="'">1'000</option>
                </select>
                <select id="col-dec-select" class="ms-ribbon-input" style="width:52px;" onchange="updateSelectedCol('decimals', parseInt(this.value))">
                    <option value="0">0</option>
                    <option value="1">.0</option>
                    <option value="2" selected>.00</option>
                    <option value="3">.000</option>
                    <option value="4">.0000</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <select id="col-rounding-select" class="ms-ribbon-input" style="width:72px;" onchange="updateSelectedCol('rounding', this.value)">
                    <option value="nearest" selected>عادي</option>
                    <option value="up">أعلى</option>
                    <option value="down">أسفل</option>
                    <option value="cut">قطع</option>
                </select>
                <label style="display:flex;align-items:center;gap:2px;font-size:0.7rem;cursor:pointer;"><input type="checkbox" id="col-dec-sep-chk" checked onchange="updateSelectedCol('showDecSep', this.checked)"> فاصلة</label>
            </div>
            <span class="ms-ribbon-group-title">أرقام</span>
        </div>

        <!-- Group: Styles & Layout -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="table-theme-select" class="ms-ribbon-input" style="width:110px;" onchange="setTableTheme(this.value)">
                    <option value="">⚪ الأساسي</option>
                    <option value="theme-classic-navy">🔷 أزرق كلاسيك</option>
                    <option value="theme-emerald-pro">💚 زمردي</option>
                    <option value="theme-modern-slate">🩶 رمادي</option>
                    <option value="theme-luxury-gold">👑 ذهبي</option>
                    <option value="theme-gold">🟡 أصفر</option>
                    <option value="theme-lightblue">🔵 أزرق فاتح</option>
                    <option value="theme-slate">⚪ سليت</option>
                    <option value="theme-luxury-black">🖤 أسود فاخر</option>
                </select>
                <select id="layout-theme-select" class="ms-ribbon-input" style="width:100px;" onchange="setLayoutTheme(this.value)">
                    <option value="">📑 كلاسيكي</option>
                    <option value="layout-modern">🏢 مودرن</option>
                    <option value="layout-split">🗂 منفصل</option>
                    <option value="layout-compact">📋 مضغوط</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.7rem;">عرض:</span>
                <input type="range" id="table-width-range" min="50" max="100" step="5" value="100" oninput="setTableWidth(this.value)" style="width:70px;height:14px;">
                <span id="table-width-label" style="font-size:0.7rem;font-weight:bold;min-width:28px;">100%</span>
            </div>
            <span class="ms-ribbon-group-title">أنماط</span>
        </div>
    </div>

    <!-- TAB 2: Page Layout -->
    <div id="studio-tab-paper" class="ribbon-tab-content" style="display:none;">
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="paper-size-select" class="ms-ribbon-input" style="width:90px;" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4</option>
                    <option value="A3">A3</option>
                    <option value="A5">A5</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>
                <select id="paper-orient-select" class="ms-ribbon-input" style="width:80px;" onchange="setPageOrientation(this.value)">
                    <option value="landscape" selected>عرضي</option>
                    <option value="portrait">طولي</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.7rem;">هوامش:</span>
                <input type="range" id="print-margin-range" min="0" max="30" step="2" value="10" oninput="setPrintMargin(this.value)" style="width:60px;height:14px;">
                <span id="print-margin-label" style="font-size:0.7rem;">10mm</span>
            </div>
            <span class="ms-ribbon-group-title">الصفحة</span>
        </div>

        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex;align-items:center;gap:3px;font-size:0.75rem;cursor:pointer;"><input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo', this.checked)"> الشعار</label>
                <label style="display:flex;align-items:center;gap:3px;font-size:0.75rem;cursor:pointer;"><input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info', this.checked)"> البيانات</label>
            </div>
            <div class="ms-ribbon-row">
                <label style="display:flex;align-items:center;gap:3px;font-size:0.75rem;cursor:pointer;"><input type="checkbox" id="hdr-show-header-chk" checked onchange="toggleHeaderElement('header', this.checked)"> الترويسة</label>
                <label style="display:flex;align-items:center;gap:3px;font-size:0.75rem;cursor:pointer;"><input type="checkbox" id="hdr-show-footer-chk" checked onchange="toggleHeaderElement('footer', this.checked)"> التذييل</label>
            </div>
            <span class="ms-ribbon-group-title">الترويسة</span>
        </div>

        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex;align-items:center;gap:3px;font-size:0.75rem;cursor:pointer;"><input type="checkbox" id="hdr-print-bg-chk" checked onchange="togglePrintBackground(this.checked)"> ألوان الخلفيات</label>
            </div>
            <span class="ms-ribbon-group-title">طباعة</span>
        </div>
    </div>

    <!-- Collapse/Expand toggle at bottom-left -->
    <button id="ribbon-collapse-btn" class="ribbon-collapse-btn" onclick="toggleSettingsPanel()" title="طي/توسيع شريط الأدوات">
        <span id="ribbon-toggle-icon">▲</span>
    </button>
</div>
