<link rel="stylesheet" href="css/studio-panel.css?v=<?php echo file_exists('css/studio-panel.css') ? filemtime('css/studio-panel.css') : time(); ?>">
<div class="settings-panel no-print" id="settings-panel">
    <!-- Studio Tabs Header -->
    <div class="studio-tabs" id="studio-tabs-bar" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; margin-bottom: 5px; padding-bottom: 4px; background: transparent; box-shadow: none; color: inherit;">
        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
            <button class="ms-ribbon-btn active" id="tab-btn-cols" onclick="switchStudioTab('cols')" style="font-weight:bold; border-bottom: 2px solid #0284c7; border-radius:0;">الصفحة الرئيسية</button>
            <button class="ms-ribbon-btn" id="tab-btn-paper" onclick="switchStudioTab('paper')" style="font-weight:bold; border-radius:0;">تخطيط الصفحة</button>
            <button class="ms-ribbon-btn" id="tab-btn-presets" onclick="switchStudioTab('presets')" style="font-weight:bold; border-radius:0;">التخصيصات</button>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <button id="reset-design-btn" class="ms-ribbon-btn" title="إعادة ضبط التصميم الافتراضي">
                <span class="btn-text">🔄 إعادة ضبط</span>
            </button>
            <button id="save-settings-btn-main" class="ms-ribbon-btn" onclick="saveCurrentPreset()" style="color:#059669; font-weight:bold;">
                💾 حفظ التنسيق
            </button>
            <button type="button" class="btn-close-modal" onclick="toggleSettingsPanel()" title="إغلاق الاستوديو" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size:0.8rem;">❌ إغلاق</button>
        </div>
    </div>

    <!-- TAB 1: Columns & Formatting -->
    <div id="studio-tab-cols" class="ribbon-tab-content">
        
        <!-- Group: Font -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-font-select" class="ms-ribbon-input" style="width: 130px;" onchange="updateSelectedCol('fontFamily', this.value)" title="نوع الخط">
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
                
                <input type="number" id="col-fs-input-ribbon" class="ms-ribbon-input" style="width: 50px;" step="0.1" value="0.9" title="حجم الخط" oninput="document.getElementById('col-fs-range').value=this.value; updateSelectedCol('fs', parseFloat(this.value))">
                <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" oninput="document.getElementById('col-fs-input-ribbon').value=this.value; updateSelectedCol('fs', parseFloat(this.value))" style="display:none;">
            </div>
            <div class="ms-ribbon-row" style="margin-top: 2px;">
                <select id="col-fw-select" class="ms-ribbon-input" style="width: 80px;" onchange="updateSelectedCol('fontWeight', this.value)" title="وزن الخط">
                    <option value="400">عادي</option>
                    <option value="500">متوسط</option>
                    <option value="600">بارز</option>
                    <option value="700">عريض</option>
                    <option value="800" selected>عريض جداً</option>
                    <option value="900">أسود داكن</option>
                </select>
                <div style="display: flex; gap: 4px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; background: #fff;">
                    <label title="لون الخط" style="display:flex; align-items:center; cursor:pointer;">
                        <span style="font-size:12px; font-weight:bold; color:red; padding: 0 4px;">A</span>
                        <input type="color" id="col-color-picker" class="ms-ribbon-input" oninput="updateSelectedCol('color', this.value)" style="border:none; padding:0; width:16px; height:16px;">
                    </label>
                    <div style="width:1px; background:#cbd5e1;"></div>
                    <label title="لون التعبئة (الخلفية)" style="display:flex; align-items:center; cursor:pointer;">
                        <span style="font-size:12px; padding: 0 4px;">🪣</span>
                        <input type="color" id="col-bg-picker" class="ms-ribbon-input" value="#ffffff" oninput="updateSelectedCol('bgColor', this.value)" style="border:none; padding:0; width:16px; height:16px;">
                    </label>
                </div>
            </div>
            <span class="ms-ribbon-group-title">خط (Font)</span>
        </div>

        <!-- Group: Alignment -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-align-select" class="ms-ribbon-input" onchange="updateSelectedCol('align', this.value)" title="محاذاة النص">
                    <option value="right">محاذاة لليمين</option>
                    <option value="center" selected>توسيط</option>
                    <option value="left">محاذاة لليسار</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <select id="num-font-select" class="ms-ribbon-input" onchange="setNumberFont(this.value)" title="خط الأرقام للمستند">
                    <option value="font-ultra" selected>أرقام Inter</option>
                    <option value="font-jakarta">أرقام Jakarta</option>
                    <option value="font-roboto">أرقام Roboto</option>
                    <option value="font-outfit">أرقام Outfit</option>
                    <option value="font-newsons">أرقام JetBrains</option>
                </select>
            </div>
            <span class="ms-ribbon-group-title">محاذاة (Alignment)</span>
        </div>

        <!-- Group: Column Format -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="selected-col-select" class="ms-ribbon-input" style="color:#0284c7; font-weight:bold; width: 120px;" onchange="selectColumn(parseInt(this.value))" title="العمود المحدد">
                </select>
                
                <input type="text" id="col-label-input" class="ms-ribbon-input" style="width:100px;" placeholder="اسم العمود..." oninput="updateSelectedCol('label', this.value)">
                
                <label style="display:flex; align-items:center; gap:2px; font-size: 0.8rem; cursor:pointer;">
                    <input type="checkbox" id="col-visible-chk" checked onchange="updateSelectedCol('visible', this.checked)">
                    إظهار
                </label>
            </div>
            <div class="ms-ribbon-row">
                <div style="display: flex; align-items: center; gap: 4px; font-size: 0.8rem;">
                    عرض: 
                    <input type="number" id="col-width-input" class="ms-ribbon-input" style="width:50px;" min="5" max="60" placeholder="%" oninput="if(document.getElementById('col-width-range')) document.getElementById('col-width-range').value=this.value; updateSelectedCol('width', this.value)">
                    <input type="range" id="col-width-range" style="display:none;" min="5" max="60" step="1" value="15" oninput="document.getElementById('col-width-input').value=this.value; updateSelectedCol('width', this.value)">
                    <button class="ms-ribbon-btn" style="padding:2px 4px; font-size: 0.7rem;" onclick="updateSelectedCol('width', ''); document.getElementById('col-width-input').value=''; document.getElementById('col-width-range').value=15;" title="تلقائي">تلقائي</button>
                </div>
                
                <div style="display: flex; gap: 2px;">
                    <button class="ms-ribbon-btn" title="تحريك لليمين" onclick="moveSelectedCol(-1)">➡️</button>
                    <button class="ms-ribbon-btn" title="تحريك لليسار" onclick="moveSelectedCol(1)">⬅️</button>
                </div>

                <select id="restore-hidden-col-select" class="ms-ribbon-input" style="width:110px;" onchange="restoreHiddenColumn(this.value)">
                    <option value="">👁️ مخفية (0)</option>
                </select>
            </div>
            <span class="ms-ribbon-group-title">العمود (Column)</span>
        </div>

        <!-- Group: Numbers -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="col-sep-select" class="ms-ribbon-input" style="width:80px;" onchange="updateSelectedCol('thousandSep', this.value)" title="الفاصلة الألفية">
                    <option value="," selected>1,000</option>
                    <option value=".">1.000</option>
                    <option value=" ">1 000</option>
                    <option value="'">1'000</option>
                </select>
                <select id="col-dec-select" class="ms-ribbon-input" style="width:70px;" onchange="updateSelectedCol('decimals', parseInt(this.value))" title="الخانات العشرية">
                    <option value="0">.0</option>
                    <option value="1">.0</option>
                    <option value="2" selected>.00</option>
                    <option value="3">.000</option>
                    <option value="4">.0000</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <select id="col-rounding-select" class="ms-ribbon-input" onchange="updateSelectedCol('rounding', this.value)" title="طريقة التقريب">
                    <option value="nearest" selected>تقريب (عادي)</option>
                    <option value="up">للأعلى</option>
                    <option value="down">للأسفل</option>
                    <option value="cut">قطع الكسر</option>
                </select>
                <label style="display:flex; align-items:center; gap:2px; font-size: 0.8rem; cursor:pointer;">
                    <input type="checkbox" id="col-dec-sep-chk" checked onchange="updateSelectedCol('showDecSep', this.checked)">
                    فاصلة الكسر
                </label>
            </div>
            <span class="ms-ribbon-group-title">الأرقام (Number)</span>
        </div>

        <!-- Group: Table & Theme -->
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="table-theme-select" class="ms-ribbon-input" style="width: 140px; font-weight:bold;" onchange="setTableTheme(this.value)">
                    <option value="">⚪ الأساسي</option>
                    <option value="theme-classic-navy">🔷 أزرق كلاسيك</option>
                    <option value="theme-emerald-pro">💚 زمردي راقي</option>
                    <option value="theme-modern-slate">🩶 رمادي عصري</option>
                    <option value="theme-luxury-gold">👑 ذهبي ملكي</option>
                    <option value="theme-gold">🟡 أصفر غامق</option>
                    <option value="theme-lightblue">🔵 أزرق فاتح</option>
                    <option value="theme-slate">⚪ رمادي كلاسيك</option>
                    <option value="theme-luxury-black">🖤 الأسود الفاخر</option>
                </select>
                <select id="layout-theme-select" class="ms-ribbon-input" style="width: 140px;" onchange="setLayoutTheme(this.value)">
                    <option value="">📑 هيكل كلاسيكي</option>
                    <option value="layout-modern">🏢 هيكل مودرن</option>
                    <option value="layout-split">🗂️ منفصل (شركات)</option>
                    <option value="layout-compact">📋 مضغوط (مراجعات)</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.8rem;">عرض الفاتورة:</span>
                <input type="range" id="table-width-range" min="50" max="100" step="5" value="100" oninput="setTableWidth(this.value)" style="width:80px;">
                <span id="table-width-label" style="font-size:0.8rem; font-weight:bold;">100%</span>
            </div>
            <span class="ms-ribbon-group-title">أنماط وتخطيط (Styles)</span>
        </div>
    </div>

    <!-- TAB 2: Paper & Print Setup (Hidden by default) -->
    <div id="studio-tab-paper" class="ribbon-tab-content" style="display:none;">
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <select id="paper-size-select" class="ms-ribbon-input" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (المعياري)</option>
                    <option value="A3">A3 (الموسع)</option>
                    <option value="A5">A5 (المصغر)</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>
                <select id="paper-orient-select" class="ms-ribbon-input" onchange="setPageOrientation(this.value)">
                    <option value="landscape" selected>عرضي (Landscape)</option>
                    <option value="portrait">طولي (Portrait)</option>
                </select>
            </div>
            <div class="ms-ribbon-row">
                <span style="font-size:0.8rem;">هوامش الورقة:</span>
                <input type="range" id="print-margin-range" min="0" max="30" step="2" value="10" oninput="setPrintMargin(this.value)" style="width:80px;">
                <span id="print-margin-label" style="font-size:0.8rem;">10mm</span>
            </div>
            <span class="ms-ribbon-group-title">إعداد الصفحة (Page Setup)</span>
        </div>
        
        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem;"><input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo', this.checked)"> شعار الشركة</label>
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem;"><input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info', this.checked)"> تفاصيل الشركة</label>
            </div>
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem;"><input type="checkbox" id="hdr-show-header-chk" checked onchange="toggleHeaderElement('header', this.checked)"> الترويسة كاملة</label>
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem;"><input type="checkbox" id="hdr-show-footer-chk" checked onchange="toggleHeaderElement('footer', this.checked)"> التذييل (Footer)</label>
            </div>
            <span class="ms-ribbon-group-title">الترويسة (Header & Footer)</span>
        </div>

        <div class="ms-ribbon-group">
            <div class="ms-ribbon-row">
                <label style="display:flex; align-items:center; gap:4px; font-size:0.85rem;"><input type="checkbox" id="hdr-print-bg-chk" checked onchange="togglePrintBackground(this.checked)"> طباعة ألوان الخلفيات</label>
            </div>
            <span class="ms-ribbon-group-title">الطباعة (Print Options)</span>
        </div>
    </div>
</div>
