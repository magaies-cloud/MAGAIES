<link rel="stylesheet" href="css/studio-ribbon-v4.css?v=<?php echo filemtime('css/studio-ribbon-v4.css'); ?>">

<div class="settings-panel no-print" id="settings-panel">

<!-- ════════════════ TAB STRIP ════════════════ -->
<div class="sr-tab-strip" dir="rtl">
    <div class="sr-tabs">
        <button type="button" class="sr-tab" id="sr-tab-presets" onclick="switchStudioTab('presets')">💎 التخصيصات الجاهزة</button>
        <button type="button" class="sr-tab" id="sr-tab-paper"   onclick="switchStudioTab('paper')">📄 إعدادات الورقة والطباعة</button>
        <button type="button" class="sr-tab active" id="sr-tab-cols" onclick="switchStudioTab('cols')">📊 إعدادات الأعمدة والتنسيق</button>
    </div>
    <div class="sr-qat" style="direction:ltr;">
        <button type="button" class="sr-qat-btn" onclick="saveCurrentPreset()" title="حفظ الإعدادات الحالية">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
        </button>
        <button type="button" class="sr-qat-btn" onclick="if(typeof resetDefaultColumnOrder==='function')resetDefaultColumnOrder();" title="إعادة الإعدادات الافتراضية">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
        </button>
        <button type="button" class="sr-qat-btn" onclick="toggleSettingsPanel()" title="إغلاق الشريط">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>
    </div>
</div>

<!-- ════════════════ TAB 1 – COLUMNS ════════════════ -->
<div id="studio-tab-cols">
<div class="sr-body" style="padding: 8px 14px; gap: 10px; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; justify-content: space-between; align-items: stretch;">

    <!-- 1. Column Settings (Far Right) -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 2; min-width: 280px;">
        <div class="sr-group-body" style="align-items:stretch; gap:6px; justify-content:center;">
            <!-- Row 1: Active Column -->
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="color:#dc2626; font-weight:900; font-size:14px; font-family:'Tajawal',sans-serif; width:95px; white-space:nowrap;">العمود النشط 🎯:</span>
                <select id="selected-col-select" class="sr-select" style="border:1.5px solid #dc2626; color:#dc2626; font-weight:900; flex:1; height:30px; font-size:14px; border-radius:4px; text-align:center; padding: 0 4px; box-shadow: 0 1px 2px rgba(220, 38, 38, 0.1);" onchange="selectColumn(parseInt(this.value))"></select>
            </div>
            
            <!-- Row 2: Show/Hide -->
            <div class="sr-group-row" style="gap:6px; justify-content:flex-start;">
                <label style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#0f172a; display:flex; align-items:center; gap:4px; white-space:nowrap; cursor:pointer;">
                    <input type="checkbox" id="col-visible-chk" style="width:14px; height:14px; accent-color:#0284c7; margin:0; cursor:pointer;" onchange="updateSelectedCol('visible', this.checked)"> إظهار العمود
                </label>
                <select id="restore-hidden-col-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#b45309; border:1px solid #f59e0b; border-radius:4px; padding:0 4px; background:#fffbeb;" onchange="if(typeof restoreHiddenColumn==='function' && this.value){restoreHiddenColumn(this.value); this.value='';}">
                    <option value="">👁️ الأعمدة المخفية (0)</option>
                </select>
            </div>

            <!-- Row 3: Reorder & Label -->
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; white-space:nowrap;">الترتيب:</span>
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden;">
                    <button type="button" class="sr-btn" onclick="moveSelectedCol(-1)" style="width:22px; height:28px; border:none; border-radius:0; background:#f8fafc; font-size:14px; color:#475569; border-left:1px solid #cbd5e1; font-weight:900;">▶</button>
                    <button type="button" class="sr-btn" onclick="moveSelectedCol(1)" style="width:22px; height:28px; border:none; border-radius:0; background:#f8fafc; font-size:14px; color:#475569; font-weight:900;">◀</button>
                </div>
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; padding-right:4px; white-space:nowrap;">التسمية ✏️:</span>
                <input type="text" id="col-label-input" class="sr-select" style="flex:1; height:28px; font-size:13px; font-weight:800; border:1px solid #cbd5e1; border-radius:4px; padding:0 6px; color:#1e293b;" placeholder="اكتب الاسم..." oninput="updateSelectedCol('label',this.value)">
            </div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>

    <!-- 2. Dimensions -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 1.2; min-width: 280px;">
        <div class="sr-group-body" style="gap:6px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:80px; white-space:nowrap;">عرض العمود:</span>
                <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-width-range'); s.value=Math.max(40, parseInt(s.value)-5); updateSelectedCol('width', s.value+'px'); document.getElementById('sr-col-w-lbl').textContent=s.value+'px';" style="padding:0; width:20px; height:20px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center;">-</button>
                <input type="range" id="col-width-range" class="sr-slider" style="width:90px;" min="40" max="400" step="5" value="120" oninput="updateSelectedCol('width',this.value+'px'); document.getElementById('sr-col-w-lbl').textContent=this.value+'px';">
                <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-width-range'); s.value=Math.min(400, parseInt(s.value)+5); updateSelectedCol('width', s.value+'px'); document.getElementById('sr-col-w-lbl').textContent=s.value+'px';" style="padding:0; width:20px; height:20px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center;">+</button>
                <span id="sr-col-w-lbl" style="font-size:13px; font-weight:900; color:#2563eb; width:45px; text-align:left; font-family:'Tajawal',sans-serif;">120px</span>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:80px; white-space:nowrap;">عرض الجدول:</span>
                <button type="button" class="sr-btn" onclick="var s=document.getElementById('table-width-range'); s.value=Math.max(50, parseInt(s.value)-5); if(typeof setTableWidth==='function')setTableWidth(s.value); document.getElementById('sr-tbl-w-lbl').textContent=s.value+'%';" style="padding:0; width:20px; height:20px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center;">-</button>
                <input type="range" id="table-width-range" class="sr-slider" style="width:90px;" min="50" max="100" step="5" value="100" oninput="if(typeof setTableWidth==='function')setTableWidth(this.value); document.getElementById('sr-tbl-w-lbl').textContent=this.value+'%';">
                <button type="button" class="sr-btn" onclick="var s=document.getElementById('table-width-range'); s.value=Math.min(100, parseInt(s.value)+5); if(typeof setTableWidth==='function')setTableWidth(s.value); document.getElementById('sr-tbl-w-lbl').textContent=s.value+'%';" style="padding:0; width:20px; height:20px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center;">+</button>
                <span id="sr-tbl-w-lbl" style="font-size:13px; font-weight:900; color:#2563eb; width:45px; text-align:left; font-family:'Tajawal',sans-serif;">100%</span>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:80px; white-space:nowrap;">ارتفاع الصف:</span>
                <select class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; padding:0 4px; text-align:center; text-align-last:center;" onchange="setRowHeight(this.value)">
                    <option value="compact">مضغوط (4px)</option>
                    <option value="normal" selected>عادي (8px)</option>
                    <option value="comfortable">مريح (12px)</option>
                    <option value="spacious">واسع (16px)</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>

    <!-- 3. Page Settings -->
    <div class="sr-group" style="padding: 2px 4px; border:none; box-shadow:none; flex: 1.2; min-width: 240px;">
        <div class="sr-group-body" style="gap:6px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">المقاس:</span>
                <select id="paper-size-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#0284c7; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (افتراضي)</option>
                    <option value="A5">A5 (نصف صفحة)</option>
                    <option value="A3">A3 (كبير)</option>
                    <option value="A6">A6 (إيصال/صغير)</option>
                    <option value="Letter">Letter</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">الاتجاه:</span>
                <select id="paper-orient-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPageOrientation(this.value)">
                    <option value="portrait" selected>طولي (عمودي) 📄</option>
                    <option value="landscape">عرضي (أفقي) 📜</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">الهوامش:</span>
                <select id="paper-margins-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPageMargins(this.value)">
                    <option value="normal" selected>عادي (15mm)</option>
                    <option value="compact">ضيق (5mm)</option>
                    <option value="none">بدون هوامش (0mm)</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>

    <!-- 4. Decimals -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 0.6; min-width: 90px;">
        <div class="sr-group-body" style="flex-direction:row; gap:10px; align-items:center;">
            <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-dec-select');s.selectedIndex=Math.min(4,s.selectedIndex+1);updateSelectedCol('decimals',parseInt(s.value));" title="زيادة المنازل العشرية" style="font-family:'Segoe UI',monospace; font-weight:900; letter-spacing:-1px; padding:4px; border:none; display:flex; flex-direction:column; align-items:center; background:transparent; cursor:pointer;">
                <div style="display:flex; align-items:center;">
                    <span style="font-size:18px; line-height:1; color:#0284c7;">.00</span>
                </div>
                <div style="display:flex; align-items:center; margin-right:8px;">
                    <span style="font-size:16px; color:#0284c7;">→</span><span style="font-size:18px; line-height:0.9; color:#0284c7;">.0</span>
                </div>
            </button>
            <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-dec-select');s.selectedIndex=Math.max(0,s.selectedIndex-1);updateSelectedCol('decimals',parseInt(s.value));" title="تقليل المنازل العشرية" style="font-family:'Segoe UI',monospace; font-weight:900; letter-spacing:-1px; padding:4px; border:none; display:flex; flex-direction:column; align-items:center; background:transparent; cursor:pointer;">
                <div style="display:flex; align-items:center; margin-right:8px;">
                    <span style="font-size:18px; line-height:1; color:#0284c7;">←</span><span style="font-size:16px; color:#0284c7;">.0</span>
                </div>
                <span style="font-size:18px; line-height:0.9; color:#0284c7;">.00</span>
            </button>
            <div style="display:none;">
                <select id="col-dec-select"><option value="0">0</option><option value="1">1</option><option value="2" selected>2</option><option value="3">3</option><option value="4">4</option></select>
                <select id="col-sep-select"><option value="," selected>,</option><option value=".">.</option><option value=" ">_</option><option value="none">بدون</option></select>
            </div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>

    <!-- 5. Alignment -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 0.6; min-width: 90px;">
        <div class="sr-group-body" style="justify-content:center; gap:4px;">
            <div style="display:flex; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#f8fafc;">
                <button type="button" class="sr-btn" title="محاذاة لليمين" onclick="updateSelectedCol('align','right');" style="width:30px; height:28px; border:none; border-radius:0; border-left:1px solid #cbd5e1; display:flex; align-items:center; justify-content:center; color:#475569; cursor:pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm6 5h12v2H9zm-6 5h18v2H3zm6 5h12v2H9z"/></svg>
                </button>
                <button type="button" class="sr-btn active" title="توسيط" onclick="updateSelectedCol('align','center');" style="width:30px; height:28px; border:none; border-radius:0; border-left:1px solid #cbd5e1; display:flex; align-items:center; justify-content:center; color:#2563eb; background:#dbeafe; cursor:pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm3 5h12v2H6zm-3 5h18v2H3zm3 5h12v2H6z"/></svg>
                </button>
                <button type="button" class="sr-btn" title="محاذاة لليسار" onclick="updateSelectedCol('align','left');" style="width:30px; height:28px; border:none; border-radius:0; display:flex; align-items:center; justify-content:center; color:#475569; cursor:pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm0 5h12v2H3zm0 5h18v2H3zm0 5h12v2H3z"/></svg>
                </button>
            </div>
            <div style="text-align:center; font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#64748b; margin-top:2px;">المحاذاة</div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>

    <!-- 6. Font & Colors -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 1.5; min-width: 240px;">
        <div class="sr-group-body" style="gap:4px;">
            <div class="sr-group-row" style="gap:4px;">
                <select id="col-font-select" class="sr-select" style="width:110px; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px;" onchange="updateSelectedCol('fontFamily',this.value)">
                    <option value="'Segoe UI',sans-serif" selected>Segoe UI</option>
                    <option value="'Tajawal',sans-serif">Tajawal (تجوّل)</option>
                    <option value="'Cairo',sans-serif">Cairo (القاهرة)</option>
                    <option value="'Almarai',sans-serif">Almarai</option>
                    <option value="'Readex Pro',sans-serif">Readex Pro</option>
                    <option value="'Amiri',serif">Amiri (أميري)</option>
                    <option value="'Changa',sans-serif">Changa</option>
                    <option value="'Arial',sans-serif">Arial</option>
                    <option value="Consolas,monospace">Consolas</option>
                </select>
                <select id="col-fs-select" class="sr-select" style="width:45px; height:28px; font-size:13px; font-weight:900; border-radius:4px; color:#1e293b; padding:0 4px;" onchange="setColumnFontSize(this.value)">
                    <option value="0.65">8</option><option value="0.72">9</option><option value="0.8">10</option><option value="0.85">11</option><option value="0.9" selected>12</option><option value="1.0">14</option><option value="1.15">16</option><option value="1.3">18</option><option value="1.45">20</option><option value="1.7">24</option><option value="2.0">28</option>
                </select>
                <div style="display:flex; gap:2px; margin-right:2px;">
                    <button type="button" class="sr-btn" title="تكبير الخط" onclick="stepFontSize(1);" style="font-family:'Times New Roman',serif; width:34px; height:28px; padding:0; border:none; background:transparent; color:#475569; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:2px;">
                        <span style="font-size:10px;">▲</span>
                        <span style="font-size:16px; font-weight:900;">A</span>
                    </button>
                    <button type="button" class="sr-btn" title="تصغير الخط" onclick="stepFontSize(-1);" style="font-family:'Times New Roman',serif; width:34px; height:28px; padding:0; border:none; background:transparent; color:#475569; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:2px;">
                        <span style="font-size:10px;">▼</span>
                        <span style="font-size:12px; font-weight:900;">A</span>
                    </button>
                    <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" style="display:none;" oninput="updateSelectedCol('fs',parseFloat(this.value))">
                </div>
            </div>
            
            <div class="sr-group-row" style="gap:4px;">
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#f8fafc;">
                    <button type="button" class="sr-btn active" onclick="updateSelectedCol('fontWeight','900')" title="عريض" style="font-family:serif;font-weight:900;font-size:13px;width:26px;height:28px;border:none;border-radius:0;">B</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('italic',true)" title="مائل" style="font-family:serif;font-style:italic;font-size:13px;width:26px;height:28px;border:none;border-radius:0;border-right:1px solid #cbd5e1;border-left:1px solid #cbd5e1;">I</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('underline',true)" title="تسطير" style="font-family:serif;text-decoration:underline;font-size:13px;width:26px;height:28px;border:none;border-radius:0;">U</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('strikethrough',true)" title="يتوسطه خط" style="font-family:serif;text-decoration:line-through;font-size:13px;width:26px;height:28px;border:none;border-radius:0;border-right:1px solid #cbd5e1;">S</button>
                </div>
                <div class="sr-vd" style="height:28px; margin:0 2px;"></div>
                <label class="sr-color-btn" title="لون تمييز النص" style="width:24px; height:28px; border:none; background:transparent; cursor:pointer; margin-right:2px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="color:#eab308;"><path d="M19 11.5s-4-4.5-4-7.5a4 4 0 0 0-8 0c0 3 4 7.5 4 7.5l8 0zM5 19h14v2H5z"/></svg>
                    <div class="sr-color-swatch" id="sr-hl-swatch" style="background:#eab308; height:3px; margin-top:2px; border-radius:1px;"></div>
                    <input type="color" value="#eab308" style="display:none;" oninput="document.getElementById('sr-hl-swatch').style.background=this.value;updateSelectedCol('bgColor',this.value);">
                </label>
                <label class="sr-color-btn" title="لون الخط" style="width:24px; height:28px; border:none; background:transparent; cursor:pointer;">
                    <span style="font-family:serif;font-weight:900;font-size:16px;line-height:0.9;color:#dc2626;">A</span>
                    <div class="sr-color-swatch" id="sr-fc-swatch" style="background:#dc2626; height:3px; margin-top:2px; border-radius:1px;"></div>
                    <input type="color" id="col-color-picker" value="#dc2626" style="display:none;" oninput="document.getElementById('sr-fc-swatch').style.background=this.value;updateSelectedCol('color',this.value);">
                </label>
            </div>
            
            <div class="sr-group-row" style="gap:6px; justify-content:flex-start; margin-top:2px;">
                <button type="button" class="sr-btn" onclick="updateSelectedCol('bgColor','');updateSelectedCol('color','');updateSelectedCol('fontWeight','400');document.getElementById('col-bg-picker').value='#ffffff';document.getElementById('sr-bg-preview').style.background='#ffffff';" title="مسح التنسيق" style="font-size:13px; font-weight:900; color:#ef4444; padding:2px 10px; border:1px solid #fca5a5; border-radius:4px; background:#fef2f2; font-family:'Tajawal',sans-serif; cursor:pointer;">
                    مسح 🗑️
                </button>
                <label style="cursor:pointer; display:flex; align-items:center; gap:4px;">
                    <div id="sr-bg-preview" style="width:30px; height:20px; border:1.5px solid #10b981; border-radius:4px; background:#dcfce7;"></div>
                    <input type="color" id="col-bg-picker" value="#dcfce7" style="display:none;" oninput="document.getElementById('sr-bg-preview').style.background=this.value;updateSelectedCol('bgColor',this.value);">
                    <span style="font-size:13px; font-weight:800; color:#475569; font-family:'Tajawal',sans-serif;">خلفية العمود:</span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="sr-vd" style="height:155px; margin:0;"></div>
    
    <!-- 7. Number Style -->
    <div class="sr-group" style="padding: 4px 8px; border:none; box-shadow:none; flex: 1.4; min-width: 250px;">
        <div class="sr-group-body" style="gap:4px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">خط الأرقام:</span>
                <select id="col-numfont-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px;" onchange="setColumnNumberFont(this.value)">
                    <option value="inherit" selected>Segoe UI</option>
                    <option value="Consolas, monospace">محاسبي Monospace</option>
                    <option value="'Tajawal', sans-serif">تجاري أنيق Tajawal</option>
                    <option value="arabic_indic">مشرقية / عربية (١٢٣)</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">السالب:</span>
                <select id="col-negformat-select" class="sr-select" style="flex:1; height:28px; font-size:13px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#dc2626; padding:0 4px;" onchange="setNegativeNumberFormat(this.value)">
                    <option value="default" selected>-100.00 (افتراضي)</option>
                    <option value="red">-100.00 (لون أحمر)</option>
                    <option value="paren_red">(100.00) محاسبي أحمر</option>
                    <option value="paren">(100.00) بين أقواس</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">النسبة:</span>
                <label style="font-size:13px; font-weight:800; font-family:'Tajawal',sans-serif; display:flex; align-items:center; gap:4px; color:#1e293b; cursor:pointer;">
                    <input type="checkbox" id="col-percent-chk" style="width:14px; height:14px; accent-color:#0284c7; margin:0; cursor:pointer;" onchange="updateSelectedCol('percentFormat', this.checked)"> إضافة علامة %
                </label>
            </div>
        </div>
    </div>

</div><!-- /sr-body -->

<div id="studio-columns-chips-container" style="display:none;"></div>
</div>
</div><!-- /studio-tab-cols -->

<!-- ════════════════ TAB 2 – PAPER ════════════════ -->
<div id="studio-tab-paper" style="display:none;">
<div class="sr-paper-body">
    <div class="sr-paper-main">

        <!-- Theme picker -->
        <div class="sr-paper-row">
            <span class="sr-amber-label">✨ نمط العرض:</span>
            <select id="table-theme-select" class="sr-pill-amber" onchange="setTableTheme(this.value)">
                <option value="theme-gold" selected>🟡 الأصفر الغامق (Amber Gold)</option>
                <option value="">⚪ الأساسي المعياري</option>
                <option value="theme-classic-navy">🔷 الأزرق الكلاسيكي</option>
                <option value="theme-emerald-pro">💚 الزمردي الراقي</option>
                <option value="theme-modern-slate">🩶 الرمادي العصري</option>
                <option value="theme-luxury-gold">👑 الذهبي الملكي</option>
                <option value="theme-lightblue">🔵 الأزرق الفاتح</option>
                <option value="theme-slate">⚪ الرمادي الكلاسيكي</option>
                <option value="theme-luxury-black">🖤 الأسود الفاخر</option>
            </select>
        </div>

        <!-- Header controls -->
        <div class="sr-paper-row">
            <span class="sr-amber-label">👁️ الترويسة العليا:</span>
            <div class="sr-box sr-box-amber">
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo',this.checked)"> شعار الشركة (Logo)</label>
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info',this.checked)"> بيانات الشركة (الهاتف والتفاصيل)</label>
            </div>
        </div>

        <!-- Print meta -->
        <div class="sr-paper-row">
            <span class="sr-amber-label">🌟 خيارات الطباعة:</span>
            <div class="sr-box sr-box-blue">
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-user-chk" checked onchange="updatePrintMeta()"> اسم المستخدم 👤</label>
                <select id="meta-user-pos-select" class="sr-pill-sm" onchange="updatePrintMeta()">
                    <option value="bottom-right" selected>↘️ أسفل اليمين</option>
                    <option value="bottom-left">↙️ أسفل اليسار</option>
                    <option value="top-right">↗️ أعلى اليمين</option>
                    <option value="top-left">↖️ أعلى اليسار</option>
                </select>
            </div>
            <div class="sr-box sr-box-green">
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-datetime-chk" checked onchange="updatePrintMeta()"> وقت الطباعة 🕒</label>
            </div>
            <div class="sr-box sr-box-purple">
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-pagenums-chk" checked onchange="updatePrintMeta()"> أرقام الصفحات 📑</label>
            </div>
        </div>

        <!-- Hidden helpers -->
        <div style="display:none;">
            <select id="paper-size-select" onchange="setPaperSize(this.value)"><option value="A4" selected>A4</option><option value="A3">A3</option><option value="A5">A5</option><option value="Letter">Letter</option><option value="Legal">Legal</option></select>
            <select id="paper-orient-select" onchange="setPageOrientation(this.value)"><option value="landscape" selected>Landscape</option><option value="portrait">Portrait</option></select>
            <select id="layout-theme-select" onchange="setLayoutTheme(this.value)"><option value="">Classic</option><option value="layout-modern">Modern</option></select>
        </div>

    </div>

    <div class="sr-divider"></div>

    <!-- Right side: Illustrations / Text / Acrobat / Themes -->
    <div style="display:flex;align-items:center;gap:6px;padding:0 10px;flex-shrink:0;">
        <button type="button" class="sr-big-btn">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="9" r="6" stroke="#475569" stroke-width="1.8"/><rect x="9" y="9" width="11" height="11" rx="1.5" stroke="#0284c7" stroke-width="1.8"/></svg>
            <span class="sr-big-label">Illustrations ▾</span>
        </button>
        <button type="button" class="sr-big-btn">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><path d="M4 20h16M12 4v16M8 4h8"/></svg>
            <span class="sr-big-label">Text ▾</span>
        </button>
        <button type="button" class="sr-big-btn" onclick="if(typeof window.openExportModal==='function')window.openExportModal();else window.print();">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#dc2626" stroke-width="1.8"/><polyline points="14 2 14 8 20 8" stroke="#dc2626" stroke-width="1.8"/><path d="M12 18v-6m-3 3l3-3 3 3" stroke="#dc2626" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="sr-big-label" style="font-size:9px;line-height:1.2;">Create &amp; Convert<br>Adobe PDF</span>
        </button>
        <div class="sr-divider"></div>
        <div style="display:flex;flex-direction:column;gap:3px;min-width:95px;">
            <button type="button" class="sr-btn" style="justify-content:flex-start;font-size:13px;gap:5px;padding:2px 6px;"><span style="color:#10b981;font-size:14px;">⊞</span> Colors ▾</button>
            <button type="button" class="sr-btn" style="justify-content:flex-start;font-size:13px;gap:5px;padding:2px 6px;"><span style="color:#0284c7;font-weight:900;font-family:serif;font-size:14px;">A</span> Fonts ▾</button>
            <button type="button" class="sr-btn" style="justify-content:flex-start;font-size:13px;gap:5px;padding:2px 6px;"><span style="color:#84cc16;font-size:14px;">●</span> Effects ▾</button>
            <span style="font-size:9.5px;color:#64748b;text-align:center;font-weight:700;">Themes</span>
        </div>
    </div>
</div>
</div><!-- /studio-tab-paper -->

<!-- ════════════════ TAB 3 – PRESETS ════════════════ -->
<div id="studio-tab-presets" style="display:none;">
<div class="sr-presets-pane">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span class="sr-amber-label" style="font-size:14px;">💎 إدارة التخصيصات والقوالب الجاهزة</span>
        <button type="button" onclick="addNewPresetPrompt()"
                style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:999px;padding:7px 18px;font-weight:900;font-size:13px;cursor:pointer;font-family:'Tajawal',sans-serif;">
            ➕ إضافة قالب جديد
        </button>
    </div>
    <div id="presets-management-list" class="sr-presets-grid"></div>
</div>
</div><!-- /studio-tab-presets -->


<!-- Custom Visual Dropdown for Table Theme -->
<div id="table-theme-dropdown" style="display:none; position:fixed; background:#fff; border:1px solid #94a3b8; box-shadow:0 15px 35px rgba(0,0,0,0.25); border-radius:8px; padding:12px; width:320px; z-index:99999; direction:rtl;">
    <style>
        .theme-thumb { width:52px; height:38px; border:1px solid #cbd5e1; border-radius:3px; cursor:pointer; display:flex; flex-direction:column; overflow:hidden; transition:all 0.15s; background:#fff; }
        .theme-thumb:hover { transform:scale(1.1); box-shadow:0 4px 12px rgba(0,0,0,0.2); border-color:#2563eb; position:relative; z-index:2; }
        .theme-thumb-header { height:10px; width:100%; }
        .theme-thumb-body { display:flex; flex-direction:column; flex:1; padding:2px; gap:1px; }
        .theme-thumb-row { height:4px; width:100%; background:rgba(0,0,0,0.03); border-radius:1px; }
        .theme-thumb-row.alt { background:rgba(0,0,0,0.08); }
        .theme-section-title { font-size:12px; font-weight:900; color:#334155; margin-bottom:8px; border-bottom:1px solid #e2e8f0; padding-bottom:4px; font-family:'Tajawal',sans-serif; }
    </style>
    <div class="theme-section-title">فاتح / كلاسيكي (Light)</div>
    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <div class="theme-thumb" onclick="applySelectedTheme('');" title="افتراضي">
            <div class="theme-thumb-header" style="background:#e2e8f0;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt"></div><div class="theme-thumb-row"></div><div class="theme-thumb-row alt"></div><div class="theme-thumb-row"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-lightblue');" title="أزرق فاتح">
            <div class="theme-thumb-header" style="background:#0284c7;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt" style="background:#bae6fd;"></div><div class="theme-thumb-row" style="background:#e0f2fe;"></div><div class="theme-thumb-row alt" style="background:#bae6fd;"></div><div class="theme-thumb-row" style="background:#e0f2fe;"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-emerald-pro');" title="زمردي">
            <div class="theme-thumb-header" style="background:#065f46;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt" style="background:#d1fae5;"></div><div class="theme-thumb-row" style="background:#ecfdf5;"></div><div class="theme-thumb-row alt" style="background:#d1fae5;"></div><div class="theme-thumb-row" style="background:#ecfdf5;"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-gold');" title="ذهبي">
            <div class="theme-thumb-header" style="background:#b45309;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt" style="background:#fde68a;"></div><div class="theme-thumb-row" style="background:#fffbeb;"></div><div class="theme-thumb-row alt" style="background:#fde68a;"></div><div class="theme-thumb-row" style="background:#fffbeb;"></div></div>
        </div>
    </div>

    <div class="theme-section-title">داكن / فاخر (Medium/Luxury)</div>
    <div style="display:flex; flex-wrap:wrap; gap:8px;">
        <div class="theme-thumb" onclick="applySelectedTheme('theme-classic-navy');" title="كحلي كلاسيك">
            <div class="theme-thumb-header" style="background:#1e3a8a;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt" style="background:#dbeafe;"></div><div class="theme-thumb-row" style="background:#eff6ff;"></div><div class="theme-thumb-row alt" style="background:#dbeafe;"></div><div class="theme-thumb-row" style="background:#eff6ff;"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-slate');" title="رمادي داكن">
            <div class="theme-thumb-header" style="background:#475569;"></div>
            <div class="theme-thumb-body"><div class="theme-thumb-row alt" style="background:#cbd5e1;"></div><div class="theme-thumb-row" style="background:#f8fafc;"></div><div class="theme-thumb-row alt" style="background:#cbd5e1;"></div><div class="theme-thumb-row" style="background:#f8fafc;"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-luxury-gold');" title="ذهبي فاخر">
            <div class="theme-thumb-header" style="background:#78350f;"></div>
            <div class="theme-thumb-body" style="background:#fffbeb;"><div class="theme-thumb-row alt" style="background:#fef3c7;"></div><div class="theme-thumb-row" style="background:#fffbeb;"></div><div class="theme-thumb-row alt" style="background:#fef3c7;"></div><div class="theme-thumb-row" style="background:#fffbeb;"></div></div>
        </div>
        <div class="theme-thumb" onclick="applySelectedTheme('theme-luxury-black');" title="أسود فاخر">
            <div class="theme-thumb-header" style="background:#0a0a0a;"></div>
            <div class="theme-thumb-body" style="background:#ffffff;"><div class="theme-thumb-row alt" style="background:#e5e5e5;"></div><div class="theme-thumb-row" style="background:#ffffff;"></div><div class="theme-thumb-row alt" style="background:#e5e5e5;"></div><div class="theme-thumb-row" style="background:#ffffff;"></div></div>
        </div>
    </div>
</div>

<script>
function toggleThemeDropdown(btn) {
    var dd = document.getElementById('table-theme-dropdown');
    if (dd.style.display === 'block') {
        dd.style.display = 'none';
    } else {
        dd.style.display = 'block';
        var rect = btn.getBoundingClientRect();
        dd.style.top = (rect.bottom + 4) + 'px';
        dd.style.left = (rect.left + (rect.width / 2) - 160) + 'px';
    }
}
function applySelectedTheme(theme) {
    if (typeof setTableTheme === 'function') { setTableTheme(theme); }
    document.getElementById('table-theme-dropdown').style.display = 'none';
}
document.addEventListener('click', function(e) {
    var dd = document.getElementById('table-theme-dropdown');
    if (dd && dd.style.display === 'block') {
        if (!e.target.closest('#table-theme-dropdown') && !e.target.closest('.theme-btn-trigger')) {
            dd.style.display = 'none';
        }
    }
});
</script>
</div><!-- /settings-panel -->

<script>
/* ── Print meta helper ── */
function getPrintUsername(){try{var u=localStorage.getItem('user');if(u){var p=JSON.parse(u);return p.fullName||p.username||'المستخدم';}}catch(e){}return 'المستخدم';}
function updatePrintMeta(){
    var su=document.getElementById('hdr-show-user-chk'),up=document.getElementById('meta-user-pos-select'),
        sd=document.getElementById('hdr-show-datetime-chk'),sp=document.getElementById('hdr-show-pagenums-chk'),
        tb=document.getElementById('print-top-bar'),fb=document.getElementById('print-footer-bar');
    if(!tb||!fb)return;
    var un=getPrintUsername(),now=new Date(),
        ds=now.toLocaleDateString('ar-EG')+' '+now.toLocaleTimeString('ar-EG');
    tb.innerHTML='<div></div><div></div><div></div>';fb.innerHTML='<div></div><div></div><div></div>';
    var ht=false,hf=false;
    if(su&&su.checked){var pos=up?up.value:'bottom-right';if(pos.startsWith('top')){tb.children[pos.endsWith('right')?0:2].innerHTML='👤 طباعة: '+un;ht=true;}else{fb.children[pos.endsWith('right')?0:2].innerHTML='👤 طباعة: '+un;hf=true;}}
    if(sd&&sd.checked){fb.children[1].innerHTML='🕒 '+ds;hf=true;}
    if(sp&&sp.checked){fb.children[1].innerHTML+=' <span>ص 1</span>';hf=true;}
    tb.style.display=ht?'flex':'none';fb.style.display=hf?'flex':'none';
}
window.updatePrintMeta=updatePrintMeta;
document.addEventListener('DOMContentLoaded',updatePrintMeta);

/* ── Tab switching – ensure legacy tabs are hidden by default ── */
window.switchStudioTab=function(tab){
    ['cols','paper','presets'].forEach(function(t){
        var el=document.getElementById('studio-tab-'+t);
        var btn=document.getElementById('sr-tab-'+t);
        if(!el)return;
        el.style.display=(t===tab)?'block':'none';
        if(btn){btn.classList.toggle('active',t===tab);}
    });
};
</script>