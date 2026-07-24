<?php
$filePath = 'balances-report.php';
$content = file_get_contents($filePath);
$content = str_replace("\r\n", "\n", $content);

$oldTabs = '<div style="display:flex; gap:6px; margin-bottom:14px; background:rgba(0,0,0,0.25); padding:4px; border-radius:10px; border:1px solid rgba(255,255,255,0.08);">
            <button type="button" id="tab-btn-cols" onclick="switchCtxTab(\'cols\')" style="flex:1; padding:8px; border:none; border-radius:8px; background:#38bdf8; color:#0f172a; font-weight:900; font-family:inherit; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                📊 ترتيب الأعمدة والإظهار
            </button>
            <button type="button" id="tab-btn-formatting" onclick="switchCtxTab(\'formatting\')" style="flex:1; padding:8px; border:none; border-radius:8px; background:transparent; color:#94a3b8; font-weight:700; font-family:inherit; cursor:pointer; font-size:0.85rem; transition:all 0.2s;">
                🎨 الثيمات والخطوط والألوان
            </button>
        </div>';

$newTabs = '<div style="display:flex; gap:4px; margin-bottom:14px; background:rgba(0,0,0,0.3); padding:4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
            <button type="button" id="tab-btn-cols" onclick="switchCtxTab(\'cols\')" style="flex:1; padding:8px 4px; border:none; border-radius:8px; background:#38bdf8; color:#0f172a; font-weight:900; font-family:inherit; cursor:pointer; font-size:0.8rem; transition:all 0.2s; box-shadow:0 2px 8px rgba(56,189,248,0.3);">
                📊 الأعمدة والترتيب
            </button>
            <button type="button" id="tab-btn-numbers" onclick="switchCtxTab(\'numbers\')" style="flex:1; padding:8px 4px; border:none; border-radius:8px; background:transparent; color:#94a3b8; font-weight:700; font-family:inherit; cursor:pointer; font-size:0.8rem; transition:all 0.2s;">
                🔢 خصائص الأرقام والتقريب
            </button>
            <button type="button" id="tab-btn-formatting" onclick="switchCtxTab(\'formatting\')" style="flex:1; padding:8px 4px; border:none; border-radius:8px; background:transparent; color:#94a3b8; font-weight:700; font-family:inherit; cursor:pointer; font-size:0.8rem; transition:all 0.2s;">
                🎨 الثيمات والخطوط
            </button>
        </div>';

if (str_contains($content, $oldTabs)) {
    $content = str_replace($oldTabs, $newTabs, $content);
    echo "Updated tabs header in balances-report.php\n";
} else {
    echo "WARNING: oldTabs not found\n";
}

$oldColTabEnd = '</div>

        <!-- Tab 2: Formatting, Themes & Number Fonts -->';

$newNumbersTab = '</div>

        <!-- Tab 2: Global Number Formatting (خصائص الأرقام والفاصلات والتقريب الشاملة) -->
        <div id="ctx-tab-numbers" style="display:none;">
            <!-- Header Note -->
            <div style="background:rgba(56, 189, 248, 0.1); border:1px solid rgba(56, 189, 248, 0.25); border-radius:10px; padding:10px 12px; margin-bottom:14px; font-size:0.8rem; color:#7dd3fc; line-height:1.5; display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.2rem;">🔢</span>
                <span>تخصيص كافة خصائص الأرقام والفاصلات والكسور والتقريب لكافة أعمدة التقرير دفعة واحدة بكل سهولة!</span>
            </div>

            <!-- Card 1: Thousands Separator -->
            <div style="background:rgba(0,0,0,0.25); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); margin-bottom:12px;">
                <label style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-weight:800; color:#38bdf8;">
                    <span>1. الفاصلة الألفية (Thousands Separator):</span>
                    <span style="font-size:0.75rem; background:rgba(56,189,248,0.15); color:#38bdf8; padding:2px 8px; border-radius:6px;">مثال: 1,000,000</span>
                </label>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">حالة الفاصلة الألفية:</label>
                        <select id="global-thousands-active" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value="1">مفعلة (1,000 ✔)</option>
                            <option value="0">ملغاة (1000 ✖)</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">رمز الفاصلة الألفية:</label>
                        <select id="global-thousands-sep" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value=",">, (فاصلة عادية)</option>
                            <option value=".">. (نقطة)</option>
                            <option value=" ">[مسافة] (1 000 000)</option>
                            <option value="_">_ (شرطة سفلى)</option>
                            <option value="\'">\' (علامة اقتباس)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 2: Decimal Separator & Precision -->
            <div style="background:rgba(0,0,0,0.25); padding:14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); margin-bottom:12px;">
                <label style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-weight:800; color:#fbbf24;">
                    <span>2. الكسور العشرية ورمز الفاصلة:</span>
                    <span style="font-size:0.75rem; background:rgba(251,191,36,0.15); color:#fbbf24; padding:2px 8px; border-radius:6px;">مثال: 0.00</span>
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">رمز الفاصلة العشرية:</label>
                        <select id="global-dec-sep" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value=".">. (نقطة إنجليزية)</option>
                            <option value=",">, (فاصلة)</option>
                            <option value="٫">٫ (فاصلة عشرية عربية)</option>
                            <option value="/">/ (شرطة مائلة)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">إظهار الكسر العشرية:</label>
                        <select id="global-show-decimals" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value="1">مفعلة (.00 ✔)</option>
                            <option value="0">إخفاء الكسر (أرقام صحيحة ✖)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">عدد الخانات العشرية:</label>
                        <select id="global-decimals-count" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value="0">0 (أرقام صحيحة بدون كسور)</option>
                            <option value="1">1 (منزلة واحدة 0.0)</option>
                            <option value="2" selected>2 (منزلتان 0.00)</option>
                            <option value="3">3 (3 منازل 0.000)</option>
                            <option value="4">4 (4 منازل 0.0000)</option>
                            <option value="5">5 (5 منازل 0.00000)</option>
                            <option value="6">6 (6 منازل 0.000000)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:#cbd5e1; display:block; margin-bottom:4px;">طريقة التقريب (Rounding Mode):</label>
                        <select id="global-round-mode" style="width:100%; background:rgba(30,41,59,0.9); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:7px; border-radius:8px; font-size:0.82rem; outline:none;">
                            <option value="round">تقريب عادي (أقرب رقم)</option>
                            <option value="floor">تقريب لأسفل (Floor)</option>
                            <option value="ceil">تقريب لأعلى (Ceil)</option>
                            <option value="trunc">قطع الكسر بدون تقريب (Truncate)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Apply All Button -->
            <button type="button" onclick="applyGlobalNumberSettingsFromUI()" style="width:100%; padding:10px; background:linear-gradient(135deg, #0284c7, #0369a1); color:white; border:none; border-radius:8px; font-weight:bold; font-size:0.88rem; cursor:pointer; box-shadow:0 4px 12px rgba(2,132,199,0.3); transition:all 0.2s;">
                ⚡ تطبيق إعدادات الأرقام والكسور على كل الجدول
            </button>
        </div>

        <!-- Tab 3: Formatting, Themes & Number Fonts -->';

if (str_contains($content, $oldColTabEnd)) {
    $content = str_replace($oldColTabEnd, $newNumbersTab, $content);
    echo "Added ctx-tab-numbers to balances-report.php\n";
} else {
    echo "WARNING: oldColTabEnd not found\n";
}

file_put_contents($filePath, $content);
