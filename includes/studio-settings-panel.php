<link rel="stylesheet" href="css/studio-ribbon-v4.css?v=<?php echo filemtime('css/studio-ribbon-v4.css'); ?>">

<div class="settings-panel no-print" id="settings-panel">

<!-- ════════════════ TAB STRIP ════════════════ -->
<div class="sr-tab-strip" dir="rtl">
    <div class="sr-tabs">
        <button type="button" class="sr-tab" id="sr-tab-presets" onclick="switchStudioTab('presets')">💎 التخصيصات والقوالب</button>
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
<div class="sr-body" style="padding: 8px 14px; gap: 8px; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; justify-content: space-between; align-items: stretch;">

    <!-- 1. Column Settings (Far Right) -->
    <div class="sr-group" style="padding: 4px 8px; flex: 2; min-width: 280px;">
        <div class="sr-group-body" style="align-items:stretch; gap:5px; justify-content:center;">
            <!-- Row 1: Active Column -->
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="color:#0284c7; font-weight:900; font-size:13px; font-family:'Tajawal',sans-serif; width:95px; white-space:nowrap;">العمود النشط 🎯:</span>
                <select id="selected-col-select" class="sr-select" style="border:1.5px solid #0284c7; color:#0284c7; font-weight:900; flex:1; height:28px; font-size:13px; border-radius:4px; text-align:center; padding: 0 4px; box-shadow: 0 1px 3px rgba(2, 132, 199, 0.15);" onchange="selectColumn(parseInt(this.value))"></select>
            </div>
            
            <!-- Row 2: Show/Hide -->
            <div class="sr-group-row" style="gap:6px; justify-content:flex-start;">
                <label style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#0f172a; display:flex; align-items:center; gap:4px; white-space:nowrap; cursor:pointer;">
                    <input type="checkbox" id="col-visible-chk" style="width:14px; height:14px; accent-color:#0284c7; margin:0; cursor:pointer;" onchange="updateSelectedCol('visible', this.checked)"> إظهار العمود
                </label>
                <select id="restore-hidden-col-select" class="sr-select" style="flex:1; height:26px; font-size:12px; font-weight:800; font-family:'Tajawal',sans-serif; color:#b45309; border:1px solid #f59e0b; border-radius:4px; padding:0 4px; background:#fffbeb;" onchange="if(typeof restoreHiddenColumn==='function' && this.value){restoreHiddenColumn(this.value); this.value='';}">
                    <option value="">👁️ الأعمدة المخفية (0)</option>
                </select>
            </div>

            <!-- Row 3: Reorder & Label -->
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; white-space:nowrap;">الترتيب:</span>
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden;">
                    <button type="button" class="sr-btn" onclick="moveSelectedCol(-1)" style="width:22px; height:26px; border:none; border-radius:0; background:#f8fafc; font-size:13px; color:#475569; border-left:1px solid #cbd5e1; font-weight:900;">▶</button>
                    <button type="button" class="sr-btn" onclick="moveSelectedCol(1)" style="width:22px; height:26px; border:none; border-radius:0; background:#f8fafc; font-size:13px; color:#475569; font-weight:900;">◀</button>
                </div>
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; padding-right:4px; white-space:nowrap;">التسمية ✏️:</span>
                <input type="text" id="col-label-input" class="sr-select" style="flex:1; height:26px; font-size:12px; font-weight:800; border:1px solid #cbd5e1; border-radius:4px; padding:0 6px; color:#1e293b;" placeholder="اكتب الاسم..." oninput="updateSelectedCol('label',this.value)">
            </div>
        </div>
    </div>
    
    <div class="sr-vd"></div>

    <!-- 2. Dimensions -->
    <div class="sr-group" style="padding: 4px 8px; flex: 1.2; min-width: 280px;">
        <div class="sr-group-body" style="gap:5px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">عرض العمود:</span>
                <button type="button" id="btn-col-w-minus" class="sr-btn" style="user-select:none; -webkit-user-select:none; padding:0; width:22px; height:22px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="تصغير عرض العمود (اضغط أو استمر بالضغط)">-</button>
                <input type="range" id="col-width-range" class="sr-slider" style="width:95px;" min="1" max="1500" step="1" value="120" oninput="if(typeof updateSelectedCol==='function')updateSelectedCol('width',this.value+'px'); document.getElementById('sr-col-w-lbl').textContent=this.value+'px';">
                <button type="button" id="btn-col-w-plus" class="sr-btn" style="user-select:none; -webkit-user-select:none; padding:0; width:22px; height:22px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="تكبير عرض العمود (اضغط أو استمر بالضغط)">+</button>
                <span id="sr-col-w-lbl" style="font-size:12.5px; font-weight:900; color:#0284c7; width:52px; text-align:left; font-family:'Tajawal',sans-serif;">120px</span>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">عرض الجدول:</span>
                <button type="button" id="btn-tbl-w-minus" class="sr-btn" style="user-select:none; -webkit-user-select:none; padding:0; width:22px; height:22px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="تصغير عرض الجدول (اضغط أو استمر بالضغط)">-</button>
                <input type="range" id="table-width-range" class="sr-slider" style="width:95px;" min="20" max="100" step="1" value="100" oninput="if(typeof setTableWidth==='function')setTableWidth(this.value); document.getElementById('sr-tbl-w-lbl').textContent=this.value+'%';">
                <button type="button" id="btn-tbl-w-plus" class="sr-btn" style="user-select:none; -webkit-user-select:none; padding:0; width:22px; height:22px; font-size:14px; border:none; border-radius:4px; background:#e2e8f0; color:#0f172a; font-weight:900; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="تكبير عرض الجدول (اضغط أو استمر بالضغط)">+</button>
                <span id="sr-tbl-w-lbl" style="font-size:12.5px; font-weight:900; color:#0284c7; width:52px; text-align:left; font-family:'Tajawal',sans-serif;">100%</span>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:75px; white-space:nowrap;">ارتفاع الصف:</span>
                <select id="row-height-select" class="sr-select" style="flex:1; height:26px; font-size:12px; font-family:'Tajawal',sans-serif; border-radius:4px; padding:0 4px; text-align:center; text-align-last:center;" onchange="if(typeof setRowHeight==='function')setRowHeight(this.value)">
                    <option value="compact">مضغوط (4px)</option>
                    <option value="normal" selected>عادي (8px)</option>
                    <option value="comfortable">مريح (12px)</option>
                    <option value="spacious">واسع (16px)</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="sr-vd"></div>

    <!-- 3. Page Settings (Dimensions, Orientation, Margins) -->
    <div class="sr-group" style="padding: 4px 8px; flex: 1.2; min-width: 250px; border: 1.5px solid #bae6fd; background: #f0f9ff;">
        <div class="sr-group-body" style="gap:5px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:900; font-family:'Tajawal',sans-serif; color:#0369a1; width:65px; white-space:nowrap;">المقاس 📄:</span>
                <select id="paper-size-select" class="sr-select" style="flex:1; height:26px; font-size:12.5px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#0284c7; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (معياري / شاشة كاملة)</option>
                    <option value="A5">A5 (نصف صفحة)</option>
                    <option value="A6">A6 (إيصال/صغير)</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:900; font-family:'Tajawal',sans-serif; color:#0369a1; width:65px; white-space:nowrap;">الاتجاه 🔄:</span>
                <select id="paper-orient-select" class="sr-select" style="flex:1; height:26px; font-size:12.5px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPageOrientation(this.value)">
                    <option value="portrait">طولي (عمودي) 📄</option>
                    <option value="landscape" selected>عرضي (أفقي) 📜</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:900; font-family:'Tajawal',sans-serif; color:#0369a1; width:65px; white-space:nowrap;">الهوامش 📐:</span>
                <select id="paper-margins-select" class="sr-select" style="flex:1; height:26px; font-size:12.5px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px; text-align:center; text-align-last:center;" onchange="setPageMargins(this.value)">
                    <option value="normal" selected>عادي (15mm)</option>
                    <option value="compact">ضيق (5mm)</option>
                    <option value="none">بدون هوامش (0mm)</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="sr-vd"></div>

    <!-- 4. Alignment & Decimals Combined -->
    <div class="sr-group" style="padding: 4px 10px; flex: 0.9; min-width: 145px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px;">
        <div class="sr-group-body" style="justify-content:center; gap:5px; align-items:center;">
            <!-- Top: Alignment -->
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="display:flex; border:1px solid #94a3b8; border-radius:5px; overflow:hidden; background:#f8fafc;">
                    <button type="button" class="sr-btn" title="محاذاة لليمين" onclick="updateSelectedCol('align','right');" style="width:26px; height:24px; border:none; border-radius:0; border-left:1px solid #cbd5e1; display:flex; align-items:center; justify-content:center; color:#475569; cursor:pointer;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm6 5h12v2H9zm-6 5h18v2H3zm6 5h12v2H9z"/></svg>
                    </button>
                    <button type="button" class="sr-btn active" title="توسيط" onclick="updateSelectedCol('align','center');" style="width:26px; height:24px; border:none; border-radius:0; border-left:1px solid #cbd5e1; display:flex; align-items:center; justify-content:center; color:#0284c7; background:#dbeafe; cursor:pointer;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm3 5h12v2H6zm-3 5h18v2H3zm3 5h12v2H6z"/></svg>
                    </button>
                    <button type="button" class="sr-btn" title="محاذاة لليسار" onclick="updateSelectedCol('align','left');" style="width:26px; height:24px; border:none; border-radius:0; display:flex; align-items:center; justify-content:center; color:#475569; cursor:pointer;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zm0 5h12v2H3zm0 5h18v2H3zm0 5h12v2H3z"/></svg>
                    </button>
                </div>
                <span style="font-size:12px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; white-space:nowrap;">المحاذاة</span>
            </div>
            
            <div style="width:100%; height:1px; background:#e2e8f0;"></div>

            <!-- Bottom: Decimals -->
            <div style="display:flex; align-items:center; gap:6px;">
                <div style="display:flex; gap:4px; align-items:center;">
                    <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-dec-select');s.selectedIndex=Math.min(4,s.selectedIndex+1);updateSelectedCol('decimals',parseInt(s.value));" title="زيادة المنازل العشرية" style="font-family:'Segoe UI',monospace; font-weight:900; letter-spacing:-1px; padding:2px 5px; border:1px solid #cbd5e1; border-radius:4px; background:#f8fafc; cursor:pointer; color:#0284c7; display:inline-flex; align-items:center; gap:2px; height:24px;">
                        <span style="font-size:13px; font-weight:900; color:#0284c7;">.00</span>
                        <span style="font-size:12px; color:#0284c7;">←</span>
                    </button>
                    <button type="button" class="sr-btn" onclick="var s=document.getElementById('col-dec-select');s.selectedIndex=Math.max(0,s.selectedIndex-1);updateSelectedCol('decimals',parseInt(s.value));" title="تقليل المنازل العشرية" style="font-family:'Segoe UI',monospace; font-weight:900; letter-spacing:-1px; padding:2px 5px; border:1px solid #cbd5e1; border-radius:4px; background:#f8fafc; cursor:pointer; color:#0284c7; display:inline-flex; align-items:center; gap:2px; height:24px;">
                        <span style="font-size:12px; color:#0284c7;">→</span>
                        <span style="font-size:13px; font-weight:900; color:#0284c7;">.0</span>
                    </button>
                    <div style="display:none;">
                        <select id="col-dec-select"><option value="0">0</option><option value="1">1</option><option value="2" selected>2</option><option value="3">3</option><option value="4">4</option></select>
                        <select id="col-sep-select"><option value="," selected>,</option><option value=".">.</option><option value=" ">_</option><option value="none">بدون</option></select>
                    </div>
                </div>
                <span style="font-size:12px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; white-space:nowrap;">الفاصلة العشرية</span>
            </div>
        </div>
    </div>
    
    <div class="sr-vd"></div>

    <!-- 6. Font & Colors -->
    <div class="sr-group" style="padding: 4px 8px; flex: 1.5; min-width: 240px;">
        <div class="sr-group-body" style="gap:4px;">
            <div class="sr-group-row" style="gap:4px;">
                <select id="col-font-select" class="sr-select" style="width:110px; height:26px; font-size:12px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px;" onchange="updateSelectedCol('fontFamily',this.value)">
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
                <select id="col-fs-select" class="sr-select" style="width:45px; height:26px; font-size:12px; font-weight:900; border-radius:4px; color:#1e293b; padding:0 4px;" onchange="if(typeof setColumnFontSize==='function')setColumnFontSize(this.value)">
                    <option value="0.65">8</option><option value="0.72">9</option><option value="0.8">10</option><option value="0.85">11</option><option value="0.9" selected>12</option><option value="1.0">14</option><option value="1.15">16</option><option value="1.3">18</option><option value="1.45">20</option><option value="1.7">24</option><option value="2.0">28</option>
                </select>
                <div style="display:flex; gap:2px; margin-right:2px;">
                    <button type="button" class="sr-btn" title="تكبير الخط" onclick="if(typeof stepFontSize==='function')stepFontSize(1);" style="font-family:'Times New Roman',serif; width:30px; height:26px; padding:0; border:none; background:transparent; color:#475569; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:2px;">
                        <span style="font-size:10px;">▲</span>
                        <span style="font-size:15px; font-weight:900;">A</span>
                    </button>
                    <button type="button" class="sr-btn" title="تصغير الخط" onclick="if(typeof stepFontSize==='function')stepFontSize(-1);" style="font-family:'Times New Roman',serif; width:30px; height:26px; padding:0; border:none; background:transparent; color:#475569; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:2px;">
                        <span style="font-size:10px;">▼</span>
                        <span style="font-size:11px; font-weight:900;">A</span>
                    </button>
                    <input type="range" id="col-fs-range" min="0.6" max="2.0" step="0.1" value="0.9" style="display:none;" oninput="updateSelectedCol('fs',parseFloat(this.value))">
                </div>
            </div>
            
            <div class="sr-group-row" style="gap:4px;">
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#f8fafc;">
                    <button type="button" class="sr-btn active" onclick="updateSelectedCol('fontWeight','900')" title="عريض" style="font-family:serif;font-weight:900;font-size:12px;width:24px;height:26px;border:none;border-radius:0;">B</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('italic',true)" title="مائل" style="font-family:serif;font-style:italic;font-size:12px;width:24px;height:26px;border:none;border-radius:0;border-right:1px solid #cbd5e1;border-left:1px solid #cbd5e1;">I</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('underline',true)" title="تسطير" style="font-family:serif;text-decoration:underline;font-size:12px;width:24px;height:26px;border:none;border-radius:0;">U</button>
                    <button type="button" class="sr-btn" onclick="updateSelectedCol('strikethrough',true)" title="يتوسطه خط" style="font-family:serif;text-decoration:line-through;font-size:12px;width:24px;height:26px;border:none;border-radius:0;border-right:1px solid #cbd5e1;">S</button>
                </div>
                <div class="sr-vd" style="height:26px; margin:0 2px;"></div>
                <label class="sr-color-btn" title="لون تمييز النص" style="width:24px; height:26px; border:none; background:transparent; cursor:pointer; margin-right:2px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="color:#eab308;"><path d="M19 11.5s-4-4.5-4-7.5a4 4 0 0 0-8 0c0 3 4 7.5 4 7.5l8 0zM5 19h14v2H5z"/></svg>
                    <div class="sr-color-swatch" id="sr-hl-swatch" style="background:#eab308; height:3px; margin-top:2px; border-radius:1px;"></div>
                    <input type="color" value="#eab308" style="display:none;" oninput="document.getElementById('sr-hl-swatch').style.background=this.value;updateSelectedCol('bgColor',this.value);">
                </label>
                <label class="sr-color-btn" title="لون الخط" style="width:24px; height:26px; border:none; background:transparent; cursor:pointer;">
                    <span style="font-family:serif;font-weight:900;font-size:15px;line-height:0.9;color:#dc2626;">A</span>
                    <div class="sr-color-swatch" id="sr-fc-swatch" style="background:#dc2626; height:3px; margin-top:2px; border-radius:1px;"></div>
                    <input type="color" id="col-color-picker" value="#dc2626" style="display:none;" oninput="document.getElementById('sr-fc-swatch').style.background=this.value;updateSelectedCol('color',this.value);">
                </label>
            </div>
            
            <div class="sr-group-row" style="gap:6px; justify-content:flex-start; margin-top:2px;">
                <button type="button" class="sr-btn" onclick="updateSelectedCol('bgColor','');updateSelectedCol('color','');updateSelectedCol('fontWeight','400');document.getElementById('col-bg-picker').value='#ffffff';document.getElementById('sr-bg-preview').style.background='#ffffff';" title="مسح التنسيق" style="font-size:12px; font-weight:800; color:#ef4444; padding:2px 8px; border:1px solid #fca5a5; border-radius:4px; background:#fef2f2; font-family:'Tajawal',sans-serif; cursor:pointer;">
                    مسح 🗑️
                </button>
                <label style="cursor:pointer; display:flex; align-items:center; gap:4px;">
                    <div id="sr-bg-preview" style="width:26px; height:18px; border:1.5px solid #10b981; border-radius:4px; background:#dcfce7;"></div>
                    <input type="color" id="col-bg-picker" value="#dcfce7" style="display:none;" oninput="document.getElementById('sr-bg-preview').style.background=this.value;updateSelectedCol('bgColor',this.value);">
                    <span style="font-size:12px; font-weight:800; color:#475569; font-family:'Tajawal',sans-serif;">خلفية العمود:</span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="sr-vd"></div>
    
    <!-- 7. Number Style -->
    <div class="sr-group" style="padding: 4px 8px; flex: 1.4; min-width: 250px;">
        <div class="sr-group-body" style="gap:4px;">
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">خط الأرقام:</span>
                <select id="col-numfont-select" class="sr-select" style="flex:1; height:26px; font-size:12px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#1e293b; padding:0 4px;" onchange="if(typeof setColumnNumberFont==='function')setColumnNumberFont(this.value)">
                    <option value="inherit" selected>Segoe UI</option>
                    <option value="Consolas, monospace">محاسبي Monospace</option>
                    <option value="'Tajawal', sans-serif">تجاري أنيق Tajawal</option>
                    <option value="arabic_indic">مشرقية / عربية (١٢٣)</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">السالب:</span>
                <select id="col-negformat-select" class="sr-select" style="flex:1; height:26px; font-size:12px; font-family:'Tajawal',sans-serif; border-radius:4px; font-weight:800; color:#dc2626; padding:0 4px;" onchange="if(typeof setNegativeNumberFormat==='function')setNegativeNumberFormat(this.value)">
                    <option value="default" selected>-100.00 (افتراضي)</option>
                    <option value="red">-100.00 (لون أحمر)</option>
                    <option value="paren_red">(100.00) محاسبي أحمر</option>
                    <option value="paren">(100.00) بين أقواس</option>
                </select>
            </div>
            <div class="sr-group-row" style="gap:4px; justify-content:flex-start;">
                <span style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; color:#475569; width:70px; white-space:nowrap;">النسبة:</span>
                <label style="font-size:12.5px; font-weight:800; font-family:'Tajawal',sans-serif; display:flex; align-items:center; gap:4px; color:#1e293b; cursor:pointer;">
                    <input type="checkbox" id="col-percent-chk" style="width:14px; height:14px; accent-color:#0284c7; margin:0; cursor:pointer;" onchange="updateSelectedCol('percentFormat', this.checked)"> إضافة علامة %
                </label>
            </div>
        </div>
    </div>

</div><!-- /sr-body -->

<div id="studio-columns-chips-container" style="display:none;"></div>
</div>
</div><!-- /studio-tab-cols -->

<!-- ════════════════ TAB 2 – PAPER & PRINT ════════════════ -->
<div id="studio-tab-paper" style="display:none;">
<div class="sr-paper-body">
    <div class="sr-paper-main">

        <!-- Row 1: Theme & Paper Settings Bar -->
        <div class="sr-paper-row">
            <span class="sr-navy-label">✨ نمط العرض:</span>
            <select id="table-theme-select" class="sr-pill-navy" onchange="setTableTheme(this.value)">
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

            <div class="sr-box sr-box-blue" style="margin-right:auto;">
                <span style="font-weight:900; font-size:12.5px; color:#0369a1;">📄 إعداد الورقة:</span>
                <select id="paper-size-select-tab2" class="sr-select" style="height:26px; font-size:12px; font-weight:800;" onchange="setPaperSize(this.value)">
                    <option value="A4" selected>A4 (معياري / شاشة كاملة)</option>
                    <option value="A5">A5 (نصف صفحة)</option>
                    <option value="A6">A6 (إيصال/صغير)</option>
                    <option value="Letter">Letter</option>
                    <option value="Legal">Legal</option>
                </select>
                <select id="paper-orient-select-tab2" class="sr-select" style="height:26px; font-size:12px; font-weight:800;" onchange="setPageOrientation(this.value)">
                    <option value="portrait">طولي 📄</option>
                    <option value="landscape" selected>عرضي 📜</option>
                </select>
                <select id="paper-margins-select-tab2" class="sr-select" style="height:26px; font-size:12px; font-weight:800;" onchange="setPageMargins(this.value)">
                    <option value="normal" selected>هوامش عادية (15mm)</option>
                    <option value="compact">هوامش ضيقة (5mm)</option>
                    <option value="none">بدون هوامش (0mm)</option>
                </select>
            </div>
        </div>

        <!-- Row 2: Header controls -->
        <div class="sr-paper-row">
            <span class="sr-navy-label">👁️ الترويسة العليا:</span>
            <div class="sr-box sr-box-indigo">
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-logo-chk" checked onchange="toggleHeaderElement('logo',this.checked)"> شعار الشركة (Logo)</label>
                <label class="sr-chk-lbl"><input type="checkbox" id="hdr-show-info-chk" checked onchange="toggleHeaderElement('info',this.checked)"> بيانات الشركة (الهاتف والتفاصيل)</label>
            </div>
        </div>

        <!-- Row 3: Print meta -->
        <div class="sr-paper-row">
            <span class="sr-navy-label">🌟 خيارات الطباعة:</span>
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

    </div>

    <div class="sr-divider"></div>

    <!-- Right side: Quick PDF & Tools -->
    <div style="display:flex; align-items:center; gap:8px; padding:0 8px; flex-shrink:0;">
        <button type="button" class="sr-big-btn" onclick="if(typeof window.openExportModal==='function')window.openExportModal();else window.print();" title="تصدير أو طباعة PDF">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#0284c7" stroke-width="2"/><polyline points="14 2 14 8 20 8" stroke="#0284c7" stroke-width="2"/><path d="M12 18v-6m-3 3l3-3 3 3" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="sr-big-label" style="font-size:10px; line-height:1.2; color:#0284c7;">طباعة / تصدير<br>Adobe PDF</span>
        </button>
    </div>
</div>
</div><!-- /studio-tab-paper -->

<!-- ════════════════ TAB 3 – PRESETS ════════════════ -->
<div id="studio-tab-presets" style="display:none;">
<div class="sr-presets-pane">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span class="sr-navy-label" style="font-size:13.5px;">💎 إدارة التخصيصات والقوالب (6 نماذج أساسية — ضبط المصنع وتخصيصات المستخدم)</span>
        <div style="display:flex; gap:6px; align-items:center;">
            <button type="button" onclick="if(typeof resetAllPresetsToFactory==='function')resetAllPresetsToFactory();"
                    style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:999px;padding:5px 14px;font-weight:900;font-size:12px;cursor:pointer;font-family:'Tajawal',sans-serif;"
                    title="استرجاع كافة القوالب والتصميمات لحالة ضبط المصنع الأصلية">
                🏭 ضبط المصنع للكل
            </button>
            <button type="button" onclick="if(typeof addNewPresetPrompt==='function')addNewPresetPrompt();"
                    style="background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;border-radius:999px;padding:5px 16px;font-weight:900;font-size:12px;cursor:pointer;font-family:'Tajawal',sans-serif;box-shadow:0 2px 8px rgba(2,132,199,0.3);">
                ➕ إضافة قالب جديد
            </button>
        </div>
    </div>
    <div id="presets-management-list" class="sr-presets-grid"></div>
</div>
</div><!-- /studio-tab-presets -->


<!-- ════════════════ UNIFIED PAPER SIMULATION ENGINE ════════════════ -->
<script>
(function() {
    window.currentPaperSize = window.currentPaperSize || 'A4';
    window.currentOrientation = window.currentOrientation || 'landscape';
    window.currentMargins = window.currentMargins || 'normal';

    function getMarginMm(margin) {
        if (margin === 'compact') return '5mm';
        if (margin === 'none') return '0mm';
        return '15mm';
    }

    function getOrientationAr(orient) {
        return orient === 'portrait' ? 'طولي' : 'أفقي';
    }

    function applyPaperSimulation(size, orient, margins) {
        if (size) window.currentPaperSize = size;
        if (orient) window.currentOrientation = orient;
        if (margins) window.currentMargins = margins;

        var s = window.currentPaperSize || 'A4';
        var o = window.currentOrientation || 'landscape';
        var m = window.currentMargins || 'normal';
        var marginMm = getMarginMm(m);
        var orientAr = getOrientationAr(o);

        // 1. Update dynamic @page CSS rule
        var styleEl = document.getElementById('dynamic-paper-style');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'dynamic-paper-style';
            document.head.appendChild(styleEl);
        }
        styleEl.innerHTML = '@page { size: ' + s + ' ' + o + '; margin: ' + marginMm + '; }';

        // 2. Update print area wrapper and sheets
        var paperTargets = document.querySelectorAll('.print-area, #print-area-wrapper, .inv-doc, #studio-live-preview-container > div');
        paperTargets.forEach(function(el) {
            el.setAttribute('data-size', s);
            el.setAttribute('data-orient', o);
            el.setAttribute('data-margins', m);
            el.setAttribute('data-paper-badge', '📄 ورقة طباعة رسمية — ' + s + ' ' + orientAr + ' (' + marginMm + ')');
            
            el.classList.remove('paper-portrait', 'paper-landscape');
            el.classList.add('paper-' + o);
        });

        // 3. Synchronize all select elements
        var s1 = document.getElementById('paper-size-select');
        var s2 = document.getElementById('paper-size-select-tab2');
        if (s1 && s1.value !== s) s1.value = s;
        if (s2 && s2.value !== s) s2.value = s;

        var o1 = document.getElementById('paper-orient-select');
        var o2 = document.getElementById('paper-orient-select-tab2');
        if (o1 && o1.value !== o) o1.value = o;
        if (o2 && o2.value !== o) o2.value = o;

        var m1 = document.getElementById('paper-margins-select');
        var m2 = document.getElementById('paper-margins-select-tab2');
        if (m1 && m1.value !== m) m1.value = m;
        if (m2 && m2.value !== m) m2.value = m;
    }

    window.applyPaperSimulation = applyPaperSimulation;

    window.setPaperSize = function(size, updateSelect) {
        applyPaperSimulation(size, null, null);
    };

    window.setPageOrientation = function(orient, updateSelect) {
        applyPaperSimulation(null, orient, null);
    };

    window.setPageMargins = function(margins, updateSelect) {
        applyPaperSimulation(null, null, margins);
    };

    // Auto initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { applyPaperSimulation(); });
    } else {
        applyPaperSimulation();
    }
})();
</script>

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

/* ── Tab switching ── */
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

<script>
// ════════════════ CONTINUOUS HOLD ACCELERATION ENGINE ════════════════
(function() {
    function initContinuousButtonHold(btnId, callback) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        var timer = null;
        var interval = null;
        var speed = 35; // 35ms per tick = ultra-smooth responsive motion
        
        function start(e) {
            if (e.type === 'mousedown' && e.button !== 0) return;
            if (e.cancelable && e.type.startsWith('touch')) e.preventDefault();
            
            callback(1); // Immediate single step
            stop();
            
            timer = setTimeout(function() {
                interval = setInterval(function() {
                    callback(2); // Accelerated smooth step while holding
                }, speed);
            }, 180);
        }
        
        function stop() {
            if (timer) { clearTimeout(timer); timer = null; }
            if (interval) { clearInterval(interval); interval = null; }
        }
        
        btn.addEventListener('mousedown', start);
        btn.addEventListener('mouseup', stop);
        btn.addEventListener('mouseleave', stop);
        btn.addEventListener('touchstart', start, { passive: false });
        btn.addEventListener('touchend', stop);
        btn.addEventListener('touchcancel', stop);
    }

    window.stepColWidth = function(delta) {
        var s = document.getElementById('col-width-range');
        if (!s) return;
        var cur = parseInt(s.value, 10) || 120;
        var minVal = 1;
        var maxVal = 1500;
        var next = Math.max(minVal, Math.min(maxVal, cur + delta));
        s.value = next;
        var lbl = document.getElementById('sr-col-w-lbl');
        if (lbl) lbl.textContent = next + 'px';
        if (typeof updateSelectedCol === 'function') {
            updateSelectedCol('width', next + 'px');
        }
    };

    window.stepTableWidth = function(delta) {
        var s = document.getElementById('table-width-range');
        if (!s) return;
        var cur = parseInt(s.value, 10) || 100;
        var minVal = 20;
        var maxVal = 100;
        var next = Math.max(minVal, Math.min(maxVal, cur + delta));
        s.value = next;
        var lbl = document.getElementById('sr-tbl-w-lbl');
        if (lbl) lbl.textContent = next + '%';
        if (typeof setTableWidth === 'function') {
            setTableWidth(next);
        }
    };

    function attachHolders() {
        initContinuousButtonHold('btn-col-w-minus', function(step) { window.stepColWidth(-step * 2); });
        initContinuousButtonHold('btn-col-w-plus', function(step) { window.stepColWidth(step * 2); });
        initContinuousButtonHold('btn-tbl-w-minus', function(step) { window.stepTableWidth(-step); });
        initContinuousButtonHold('btn-tbl-w-plus', function(step) { window.stepTableWidth(step); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachHolders);
    } else {
        attachHolders();
    }
})();
</script>
