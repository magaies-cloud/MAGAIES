<?php
$filePath = 'js/balances-report.js';
$content = file_get_contents($filePath);
$content = str_replace("\r\n", "\n", $content);

// 1. Update Header Context Menu Section 4 & 5
$old_block4 = '                <!-- 4. Thousands Separator & Decimal Point Toggles -->
                <div style="display:flex; gap:10px; margin-bottom:12px;">
                    <!-- Thousands Toggle -->
                    <div style="flex:1; display:flex; flex-direction:column; background:rgba(30,41,59,0.5); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                        <label style="font-size:0.75rem; font-weight:bold; color:#cbd5e1; margin-bottom:4px;">فاصلة الألفيات (1,000):</label>
                        <button type="button" onclick="toggleColThousands(${origIdx}, ${visualIndex})" style="width:100%; padding:6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.thousands !== false ? \'#10b981\' : \'rgba(239,68,68,0.4)\'}; background:${conf.thousands !== false ? \'rgba(16,185,129,0.25)\' : \'rgba(239,68,68,0.15)\'}; color:${conf.thousands !== false ? \'#34d399\' : \'#f87171\'}; font-weight:bold; font-size:0.8rem; transition:all 0.2s;">
                            ${conf.thousands !== false ? \'مفعلة (1,000 ✔)\' : \'ملغاة (1000 ✖)\'}
                        </button>
                    </div>
                    
                    <!-- Decimals Toggle -->
                    <div style="flex:1; display:flex; flex-direction:column; background:rgba(30,41,59,0.5); padding:8px 10px; border-radius:8px; border:1px solid rgba(255,255,255,0.08);">
                        <label style="font-size:0.75rem; font-weight:bold; color:#cbd5e1; margin-bottom:4px;">الكسور العشرية (.00):</label>
                        <button type="button" onclick="toggleColShowDecimals(${origIdx}, ${visualIndex})" style="width:100%; padding:6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.showDecimals !== false ? \'#38bdf8\' : \'rgba(239,68,68,0.4)\'}; background:${conf.showDecimals !== false ? \'rgba(56,189,248,0.25)\' : \'rgba(239,68,68,0.15)\'}; color:${conf.showDecimals !== false ? \'#38bdf8\' : \'#f87171\'}; font-weight:bold; font-size:0.8rem; transition:all 0.2s;">
                            ${conf.showDecimals !== false ? \'مفعلة (.00 ✔)\' : \'ملغاة (أرقام صحيحة ✖)\'}
                        </button>
                    </div>
                </div>

                <!-- 5. Decimal Precision Selector (if decimals enabled) -->
                ${conf.showDecimals !== false ? `
                <div style="margin-bottom:12px;">
                    <label style="font-size:0.78rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:4px;">عدد منازل الكسور العشرية:</label>
                    <select onchange="setColDecimals(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(30, 41, 59, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:6px 8px; border-radius:6px; font-size:0.8rem; outline:none;">
                        <option value="1" ${conf.decimals === 1 ? \'selected\' : \'\'}>منزلة واحدة (0.0)</option>
                        <option value="2" ${conf.decimals === 2 || conf.decimals === undefined ? \'selected\' : \'\'}>منزلتان (0.00)</option>
                        <option value="3" ${conf.decimals === 3 ? \'selected\' : \'\'}>3 منازل (0.000)</option>
                        <option value="4" ${conf.decimals === 4 ? \'selected\' : \'\'}>4 منازل (0.0000)</option>
                        <option value="5" ${conf.decimals === 5 ? \'selected\' : \'\'}>5 منازل (0.00000)</option>
                        <option value="6" ${conf.decimals === 6 ? \'selected\' : \'\'}>6 منازل (0.000000)</option>
                    </select>
                </div>
                ` : \'\'}';

$new_block4 = '                <!-- 4. Number Formatting Section (تنسيق الأرقام والفاصلات والتقريب) -->
                <div style="background:rgba(30,41,59,0.6); padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); margin-bottom:12px;">
                    <div style="font-size:0.8rem; font-weight:bold; color:#38bdf8; margin-bottom:8px; display:flex; align-items:center; gap:5px;">
                        <span>🔢</span>
                        <span>تخصيص الأرقام والفاصلات والتقريب:</span>
                    </div>

                    <!-- Thousands Separator: Toggle & Character -->
                    <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">الفاصلة الألفية:</label>
                            <button type="button" onclick="toggleColThousands(${origIdx}, ${visualIndex})" style="width:100%; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.thousands !== false ? \'#10b981\' : \'rgba(239,68,68,0.4)\'}; background:${conf.thousands !== false ? \'rgba(16,185,129,0.25)\' : \'rgba(239,68,68,0.15)\'}; color:${conf.thousands !== false ? \'#34d399\' : \'#f87171\'}; font-weight:bold; font-size:0.78rem;">
                                ${conf.thousands !== false ? \'مفعلة (1,000 ✔)\' : \'ملغاة (1000 ✖)\'}
                            </button>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">رمز الفاصلة الألفية:</label>
                            <select onchange="setColThousandsSep(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="," ${(!conf.thousandsSep || conf.thousandsSep === \',\') ? \'selected\' : \'\'}>, (فاصلة عادية)</option>
                                <option value="." ${conf.thousandsSep === \'.\' ? \'selected\' : \'\'}>. (نقطة)</option>
                                <option value=" " ${conf.thousandsSep === \' \' ? \'selected\' : \'\'}>[مسافة] (1 000)</option>
                                <option value="_" ${conf.thousandsSep === \'_\' ? \'selected\' : \'\'}>_ (شرطة سفلى)</option>
                                <option value="\'" ${conf.thousandsSep === "\'" ? \'selected\' : \'\'}>\' (اقتباس)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Decimal Separator & Show Decimals Toggle -->
                    <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">رمز الفاصلة العشرية:</label>
                            <select onchange="setColDecSep(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="." ${(!conf.decSep || conf.decSep === \'.\') ? \'selected\' : \'\'}>. (نقطة إنجليزية)</option>
                                <option value="," ${conf.decSep === \',\' ? \'selected\' : \'\'}>, (فاصلة)</option>
                                <option value="٫" ${conf.decSep === \'٫\' ? \'selected\' : \'\'}>٫ (فاصلة عشرية عربية)</option>
                                <option value="/" ${conf.decSep === \'/\' ? \'selected\' : \'\'}>/ (شرطة مائلة)</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">إظهار الكسور العشرية:</label>
                            <button type="button" onclick="toggleColShowDecimals(${origIdx}, ${visualIndex})" style="width:100%; padding:5px 6px; cursor:pointer; border-radius:6px; border:1px solid ${conf.showDecimals !== false ? \'#38bdf8\' : \'rgba(239,68,68,0.4)\'}; background:${conf.showDecimals !== false ? \'rgba(56,189,248,0.25)\' : \'rgba(239,68,68,0.15)\'}; color:${conf.showDecimals !== false ? \'#38bdf8\' : \'#f87171\'}; font-weight:bold; font-size:0.78rem;">
                                ${conf.showDecimals !== false ? \'مفعلة (.00 ✔)\' : \'ملغاة (أرقام صحيحة ✖)\'}
                            </button>
                        </div>
                    </div>

                    <!-- Decimal Count & Rounding Mode -->
                    ${conf.showDecimals !== false ? `
                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">عدد الخانات العشرية:</label>
                            <select onchange="setColDecimals(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="0" ${conf.decimals === 0 ? \'selected\' : \'\'}>بدون خانات (0)</option>
                                <option value="1" ${conf.decimals === 1 ? \'selected\' : \'\'}>منزلة واحدة (0.0)</option>
                                <option value="2" ${conf.decimals === 2 || conf.decimals === undefined ? \'selected\' : \'\'}>منزلتان (0.00)</option>
                                <option value="3" ${conf.decimals === 3 ? \'selected\' : \'\'}>3 منازل (0.000)</option>
                                <option value="4" ${conf.decimals === 4 ? \'selected\' : \'\'}>4 منازل (0.0000)</option>
                                <option value="5" ${conf.decimals === 5 ? \'selected\' : \'\'}>5 منازل (0.00000)</option>
                                <option value="6" ${conf.decimals === 6 ? \'selected\' : \'\'}>6 منازل (0.000000)</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:0.74rem; font-weight:bold; color:#cbd5e1; display:block; margin-bottom:3px;">طريقة والتقريب:</label>
                            <select onchange="setColRoundMode(${origIdx}, ${visualIndex}, this.value)" style="width:100%; background:rgba(15, 23, 42, 0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:5px 6px; border-radius:6px; font-size:0.78rem; outline:none;">
                                <option value="round" ${(!conf.roundMode || conf.roundMode === \'round\') ? \'selected\' : \'\'}>تقريب عادي (أقرب رقم)</option>
                                <option value="floor" ${conf.roundMode === \'floor\' ? \'selected\' : \'\'}>تقريب لأسفل (Floor)</option>
                                <option value="ceil" ${conf.roundMode === \'ceil\' ? \'selected\' : \'\'}>تقريب لأعلى (Ceil)</option>
                                <option value="trunc" ${conf.roundMode === \'trunc\' ? \'selected\' : \'\'}>قطع الكسر (Truncate)</option>
                            </select>
                        </div>
                    </div>
                    ` : \'\'}
                </div>';

if (str_contains($content, $old_block4)) {
    $content = str_replace($old_block4, $new_block4, $content);
    echo "Block 4 updated in renderHeaderContextMenu!\n";
} else {
    echo "ERROR: Block 4 not found\n";
}

// 2. Update Sidebar Config
$old_sidebar = '${[\'debit\', \'credit\', \'debitF\', \'creditF\'].includes(col.id) ? `
                <div class="cfg-row">
                    <span>التقريب والفاصلة الألفية:</span>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <select class="cfg-input" onchange="updateColField(${origIdx}, \'dec\', parseInt(this.value))" style="width:115px;" title="عدد الخانات العشرية للتقريب">
                            <option value="0" ${col.dec === 0 ? \'selected\' : \'\'}>بدون خانات (0)</option>
                            <option value="1" ${col.dec === 1 ? \'selected\' : \'\'}>خانة واحدة (1)</option>
                            <option value="2" ${col.dec === 2 ? \'selected\' : \'\'}>خانتين (2)</option>
                            <option value="3" ${col.dec === 3 ? \'selected\' : \'\'}>3 خانات (3)</option>
                            <option value="4" ${col.dec === 4 ? \'selected\' : \'\'}>4 خانات (4)</option>
                            <option value="5" ${col.dec === 5 ? \'selected\' : \'\'}>5 خانات (5)</option>
                            <option value="6" ${col.dec === 6 ? \'selected\' : \'\'}>6 خانات (6)</option>
                        </select>
                        <label style="font-size:0.75rem; color:#cbd5e1; cursor:pointer; display:flex; align-items:center; gap:3px;">
                            <input type="checkbox" ${col.thousands !== false ? \'checked\' : \'\'} onchange="updateColField(${origIdx}, \'thousands\', this.checked)">
                            فاصلة (,)
                        </label>
                    </div>
                </div>
                ` : \'\'}';

$new_sidebar = '                <div class="cfg-row" style="flex-wrap:wrap; margin-top:8px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:8px;">
                    <span style="font-weight:bold; color:#38bdf8; width:100%; margin-bottom:4px;">🔢 خصائص وتنسيق الأرقام والكسور:</span>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; width:100%;">
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة الألفية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, \'thousandsSep\', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="," ${(!col.thousandsSep || col.thousandsSep === \',\') ? \'selected\' : \'\'}>, (فاصلة عادية)</option>
                                <option value="." ${col.thousandsSep === \'.\' ? \'selected\' : \'\'}>. (نقطة)</option>
                                <option value=" " ${col.thousandsSep === \' \' ? \'selected\' : \'\'}>[مسافة]</option>
                                <option value="_" ${col.thousandsSep === \'_\' ? \'selected\' : \'\'}>_ (شرطة)</option>
                                <option value="\'" ${col.thousandsSep === "\'" ? \'selected\' : \'\'}>\' (اقتباس)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">رمز الفاصلة العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, \'decSep\', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="." ${(!col.decSep || col.decSep === \'.\') ? \'selected\' : \'\'}>. (نقطة)</option>
                                <option value="," ${col.decSep === \',\' ? \'selected\' : \'\'}>, (فاصلة)</option>
                                <option value="٫" ${col.decSep === \'٫\' ? \'selected\' : \'\'}>٫ (عربية)</option>
                                <option value="/" ${col.decSep === \'/\' ? \'selected\' : \'\'}>/ (شرطة)</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">عدد الخانات العشرية:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, \'dec\', parseInt(this.value)); updateColField(${origIdx}, \'decimals\', parseInt(this.value));" style="width:100%; font-size:0.75rem;">
                                <option value="0" ${col.dec === 0 || col.decimals === 0 ? \'selected\' : \'\'}>0 (بدون)</option>
                                <option value="1" ${col.dec === 1 || col.decimals === 1 ? \'selected\' : \'\'}>1 خانة</option>
                                <option value="2" ${col.dec === 2 || col.decimals === 2 || (col.dec === undefined && col.decimals === undefined) ? \'selected\' : \'\'}>2 خانة</option>
                                <option value="3" ${col.dec === 3 || col.decimals === 3 ? \'selected\' : \'\'}>3 خانات</option>
                                <option value="4" ${col.dec === 4 || col.decimals === 4 ? \'selected\' : \'\'}>4 خانات</option>
                                <option value="5" ${col.dec === 5 || col.decimals === 5 ? \'selected\' : \'\'}>5 خانات</option>
                                <option value="6" ${col.dec === 6 || col.decimals === 6 ? \'selected\' : \'\'}>6 خانات</option>
                            </select>
                        </div>
                        <div>
                            <span style="font-size:0.7rem; color:#94a3b8; display:block;">طريقة والتقريب:</span>
                            <select class="cfg-input" onchange="updateColField(${origIdx}, \'roundMode\', this.value)" style="width:100%; font-size:0.75rem;">
                                <option value="round" ${(!col.roundMode || col.roundMode === \'round\') ? \'selected\' : \'\'}>تقريب عادي</option>
                                <option value="floor" ${col.roundMode === \'floor\' ? \'selected\' : \'\'}>تقريب لأسفل</option>
                                <option value="ceil" ${col.roundMode === \'ceil\' ? \'selected\' : \'\'}>تقريب لأعلى</option>
                                <option value="trunc" ${col.roundMode === \'trunc\' ? \'selected\' : \'\'}>قطع الكسر</option>
                            </select>
                        </div>
                    </div>
                </div>';

if (str_contains($content, $old_sidebar)) {
    $content = str_replace($old_sidebar, $new_sidebar, $content);
    echo "Sidebar block updated!\n";
} else {
    echo "ERROR: Sidebar block not found\n";
}

// 3. Update Popup Column Context Menu
$old_ctx = '${[\'debit\', \'credit\', \'debitF\', \'creditF\'].includes(col.id) ? `
                <div class="cfg-row">
                    <span>التقريب والفاصلة:</span>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <select class="cfg-input" onchange="updateColField(${origIdx}, \'dec\', parseInt(this.value))" style="width:100px;">
                            <option value="0" ${col.dec === 0 ? \'selected\' : \'\'}>0 خانة</option>
                            <option value="1" ${col.dec === 1 ? \'selected\' : \'\'}>1 خانة</option>
                            <option value="2" ${col.dec === 2 ? \'selected\' : \'\'}>2 خانة</option>
                            <option value="3" ${col.dec === 3 ? \'selected\' : \'\'}>3 خانات</option>
                            <option value="4" ${col.dec === 4 ? \'selected\' : \'\'}>4 خانات</option>
                        </select>
                        <label style="font-size:0.75rem; color:#cbd5e1; cursor:pointer; display:flex; align-items:center; gap:3px;">
                            <input type="checkbox" ${col.thousands !== false ? \'checked\' : \'\'} onchange="updateColField(${origIdx}, \'thousands\', this.checked)">
                            فاصلة (,)
                        </label>
                    </div>
                </div>
                ` : \'\'}';

if (str_contains($content, $old_ctx)) {
    $content = str_replace($old_ctx, $new_sidebar, $content);
    echo "Popup context menu updated!\n";
} else {
    echo "ERROR: Popup context menu not found\n";
}

file_put_contents($filePath, $content);
