<?php
// =============================================================
// MAGAIEES ERP - Unified Financial OCR Scanner Modal & Overlay
// واجهة واستوديو تدريب مسح إشعارات التحويل والشيكات بالذكاء الاصطناعي
// =============================================================
$db_ocr_props_json = '{}';
try {
    if (!isset($pdo) || !$pdo) {
        if (function_exists('getDBConnection')) {
            $pdo = getDBConnection();
        }
    }
    if (isset($pdo) && $pdo) {
        $tId = !empty($GLOBAL_TENANT_ID) ? (string)$GLOBAL_TENANT_ID : '1';
        $stmt = $pdo->prepare("SELECT pref_value FROM ui_preferences WHERE tenant_id IN (?, 'default', '1') AND pref_key = 'ocr_left_col_props' ORDER BY CASE WHEN tenant_id = ? THEN 1 WHEN tenant_id = '1' THEN 2 ELSE 3 END LIMIT 1");
        $stmt->execute([$tId, $tId]);
        $res = $stmt->fetchColumn();
        if ($res) $db_ocr_props_json = $res;
    }
} catch(Exception $e) {}
?>
<script>
if (!window.DB_OCR_PROPS || Object.keys(window.DB_OCR_PROPS).length === 0) {
    window.DB_OCR_PROPS = <?= !empty($db_ocr_props_json) ? $db_ocr_props_json : '{}' ?>;
}
</script>
<!-- Hidden File Input for Camera / Image Upload (Supports Single & Multiple Selection) -->
<input type="file" id="financial-ocr-file-input" accept="image/*" multiple="multiple" style="display:none;" onchange="FinancialOCR.handleFileSelected(this)">

<!-- High-Speed OCR Scanning Loading Overlay -->
<div id="financial-ocr-loading" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.96); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); z-index:9999999; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl; padding:20px;">
    <div style="position:relative; width:100px; height:100px; margin-bottom:20px;">
        <div style="position:absolute; inset:0; border:4px solid rgba(56,189,248,0.2); border-top-color:#38bdf8; border-radius:50%; animation:ocrSpinRing 0.8s linear infinite;"></div>
        <div style="position:absolute; inset:12px; border:4px solid rgba(16,185,129,0.2); border-bottom-color:#10b981; border-radius:50%; animation:ocrSpinRingReverse 1.2s linear infinite;"></div>
        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:2.4rem; animation:pulseScale 1.2s ease-in-out infinite;">⚡</div>
    </div>
    
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
        <span style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#7dd3fc; padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:900;">⚡ فحص فائق السرعة (Ultra-Fast)</span>
        <span id="financial-ocr-loading-timer" style="background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:800; font-family:'Consolas',monospace;">0.0s</span>
    </div>

    <h2 id="financial-ocr-loading-title" style="margin:0 0 10px 0; font-size:1.6rem; font-weight:900; background:linear-gradient(135deg, #38bdf8, #34d399); -webkit-background-clip:text; -webkit-text-fill-color:transparent; text-shadow:0 0 25px rgba(56,189,248,0.5); text-align:center;">جاري فحص الإشعار / الشيك بالرؤية الحاسوبية... 🔍</h2>
    
    <!-- Batch Progress Bar -->
    <div style="width:100%; max-width:420px; background:rgba(255,255,255,0.08); border-radius:10px; height:10px; overflow:hidden; margin-bottom:12px; box-shadow:inset 0 2px 4px rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1);">
        <div id="financial-ocr-loading-progress-bar" style="width:10%; height:100%; background:linear-gradient(90deg, #38bdf8, #10b981); border-radius:10px; transition:width 0.25s ease;"></div>
    </div>

    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
        <span id="financial-ocr-loading-count" style="color:#fbbf24; font-size:1.1rem; font-weight:900;">1 / 1</span>
        <span id="financial-ocr-loading-msg" style="color:#cbd5e1; font-size:0.95rem; font-weight:700;">يتم الآن قراءة رقم العملية، المبلغ، واسم البنك عبر محرك الذكاء الاصطناعي...</span>
    </div>
</div>

<!-- OCR Review & Confirmation Modal (Edge-to-Edge 100% Full Screen 3-Column Dashboard) -->
<div id="financial-ocr-review-modal" style="display:none; position:fixed !important; inset:0 !important; top:0 !important; left:0 !important; right:0 !important; bottom:0 !important; width:100vw !important; height:100vh !important; max-width:100vw !important; max-height:100vh !important; background:#020617 !important; z-index:2147483647 !important; padding:6px 8px; margin:0 !important; font-family:'Tajawal',sans-serif; direction:rtl; box-sizing:border-box; overflow:hidden;">
    
    <!-- Three-Column Master Grid: Exactly 3 Parts, Image in the Center, 100% Fullscreen Domination -->
    <div class="ocr-responsive-grid" style="width:100%; height:100%; min-height:0; display:grid; grid-template-columns: minmax(0, 1.98fr) minmax(0, 1.255fr) minmax(0, 0.765fr); gap:8px; overflow:hidden; overflow-x:auto; direction:rtl; box-sizing:border-box;">
            
            <!-- PART 1 (القسم الأول الأيمن الأوسع): السندات، الإجمالي، معرض الكروت الحمراء الموسع، وأزرار الحفظ والاعتماد -->
            <div id="ocr-col-middle" class="ocr-dashboard-col" style="height:100%; min-height:0; display:flex; flex-direction:column; justify-content:space-between; background:rgba(15,23,42,0.98); border:1.5px solid rgba(56,189,248,0.25); border-radius:10px; padding:8px 10px; overflow-y:auto; overflow-x:hidden; scrollbar-width:thin; direction:rtl; box-sizing:border-box; min-width:300px;">
                
                <div style="display:flex; flex-direction:column; height:100%; justify-content:space-between;">
                    
                    <!-- Section Top Elements -->
                    <div style="display:flex; flex-direction:column;">
                        
                        <!-- Row 1: إجمالي السندات (X سند) والمبلغ بخط عريض وواضح -->
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(6,78,59,0.8), rgba(15,23,42,0.95)); border:1.5px solid #10b981; border-radius:10px; padding:6px 12px; box-shadow:0 0 20px rgba(16,185,129,0.35); min-height:48px; box-sizing:border-box;">
                            <div style="display:flex; align-items:center; gap:8px; overflow:hidden;">
                                <span style="font-size:0.95rem; font-weight:900; color:#a7f3d0; white-space:nowrap; display:flex; align-items:center; gap:4px;">
                                    <span>💰</span> <span id="ocr-batch-counter-badge">إجمالي السندات (1 سند):</span>
                                </span>
                            </div>
                            <div id="ocr-batch-sum-badge" style="font-size:1.65rem; font-weight:900; color:#34d399; font-family:'Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; letter-spacing:-0.3px; text-shadow:0 0 18px rgba(16,185,129,0.65); line-height:1; white-space:nowrap;">
                                0 SDG
                            </div>
                        </div>

                        <!-- Row 2: الأزرار أعرض وأكبر بتنسيق متناسق -->
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin:7px 0;">
                            <div style="display:flex; align-items:center; gap:6px; flex:1;">
                                <button type="button" onclick="FinancialOCR.nextItem()" style="flex:1; height:34px; background:linear-gradient(135deg, rgba(56,189,248,0.3), rgba(14,165,233,0.2)); border:1.5px solid #38bdf8; color:#7dd3fc; border-radius:8px; padding:0 10px; font-size:0.86rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;" title="المستند التالي">
                                    <span>▲</span> التالي
                                </button>
                                <button type="button" onclick="FinancialOCR.prevItem()" style="flex:1; height:34px; background:rgba(255,255,255,0.08); border:1.5px solid rgba(255,255,255,0.25); color:#f1f5f9; border-radius:8px; padding:0 10px; font-size:0.86rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;" title="المستند السابق">
                                    <span>▼</span> السابق
                                </button>
                            </div>
                            <!-- زر ريفريش شامل لكل الدفعة (مربع كبير بدون كتابة) -->
                            <button type="button" onclick="FinancialOCR.rescanAllBatchItems()" id="btn-batch-refresh-all" style="width:36px; height:34px; min-width:36px; border-radius:8px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.35)); border:1.5px solid #38bdf8; color:#7dd3fc; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1.15rem; padding:0; transition:all 0.15s ease; box-shadow:0 0 10px rgba(56,189,248,0.25);" title="🔄 إعادة فحص وقراءة جميع المستندات بالدفعة بالذكاء الاصطناعي (Refresh All)">
                                🔄
                            </button>
                            <button type="button" onclick="FinancialOCR.triggerUploadAgain()" style="flex:1.2; height:34px; background:linear-gradient(135deg, rgba(16,185,129,0.3), rgba(5,150,105,0.2)); border:1.5px solid #10b981; color:#34d399; border-radius:8px; padding:0 10px; font-size:0.86rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; white-space:nowrap;" title="إضافة شيكات أو إشعارات أخرى للدفعة">
                                <span>➕</span> إضافة شيكات/إشعارات
                            </button>
                        </div>

                        <!-- Live Background Streaming Progress Indicator (Option B) -->
                        <div id="ocr-streaming-indicator" style="display:none; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(14,165,233,0.2), rgba(2,132,199,0.3)); border:1.5px solid #38bdf8; border-radius:8px; padding:5px 10px; margin-bottom:6px; box-shadow:0 0 15px rgba(56,189,248,0.35);">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="display:inline-block; font-size:1.05rem; animation:pulseScale 1s ease-in-out infinite;">⚡</span>
                                <span id="ocr-streaming-text" style="font-size:0.83rem; font-weight:900; color:#e0f2fe;">جاري استكمال مسح بقية المستندات في الخلفية...</span>
                            </div>
                            <span id="ocr-streaming-badge" style="background:#0284c7; color:#fff; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:900; font-family:'Consolas',monospace; box-shadow:0 0 8px rgba(56,189,248,0.5);">4 / 7</span>
                        </div>

                        <!-- Row 3: Gallery of Cards (معرض الشيكات والإشعارات الموسع - مسافة زائدة 50% بين البطاقات: 9px) -->
                        <div id="ocr-batch-container" style="display:flex; flex-direction:column; flex:1; min-height:380px; height:415px; max-height:470px; background:rgba(10,15,30,0.6); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:6px; margin-bottom:6px; box-sizing:border-box;">
                            <div id="ocr-batch-tabs" style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:9px; width:100%; height:100%; overflow-y:auto; padding:4px; scrollbar-width:thin; box-sizing:border-box;">
                                <!-- Injected dynamically via JS: 7 right, 7 left = 14 notices visible with large clear layout -->
                            </div>
                        </div>

                        <!-- دليل دلالات ألوان البطاقات المعتمد -->
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; background:rgba(15,23,42,0.92); border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:5px 10px; margin-bottom:6px; font-size:0.73rem; font-weight:800; direction:rtl; box-sizing:border-box;">
                            <div style="display:flex; align-items:center; gap:4px; color:#34d399;" title="المستند سليم ومكتمل بنسبة 100% وجاهز للاعتماد الفوري">
                                <span style="width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981; display:inline-block;"></span>
                                <span>أخضر: سليم (مار طوالي)</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:4px; color:#f87171;" title="رقم المعاملة مسجل مسبقاً في النظام أو متكرر بالدفعة">
                                <span style="width:8px; height:8px; border-radius:50%; background:#ef4444; box-shadow:0 0 6px #ef4444; display:inline-block;"></span>
                                <span>أحمر: مكرر</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:4px; color:#fbbf24;" title="المبلغ صفري أو بيانات الإشعار غير واضحة ويحتاج مراجعة">
                                <span style="width:8px; height:8px; border-radius:50%; background:#f59e0b; box-shadow:0 0 6px #f59e0b; display:inline-block;"></span>
                                <span>ذهبي: ناقص / غير واضح</span>
                            </div>
                        </div>

                        <!-- Row 4: Emergency Duplicate Warning Banner (تحت التحويلات مباشرة) -->
                        <div id="ocr-ref-warning-box" style="display:none; flex-direction:column; gap:3px; background:linear-gradient(135deg, rgba(220,38,38,0.45), rgba(153,27,27,0.55)); border:1.5px solid #ef4444; color:#fff; padding:6px 10px; border-radius:8px; box-shadow:0 0 20px rgba(239,68,68,0.7); animation:ocrEmergencyGlow 0.9s infinite alternate; margin-bottom:6px;">
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div style="display:flex; align-items:center; gap:6px; font-size:0.82rem; font-weight:900; color:#fef2f2;">
                                    <span>🚨</span>
                                    <span>تحذير مالي: رقم التحويلة مسجل مسبقاً!</span>
                                </div>
                                <span style="background:#b91c1c; color:#fff; padding:2px 6px; border-radius:4px; font-size:0.7rem; font-weight:900;">خطر ازدواجية الصرف</span>
                            </div>
                            <div id="ocr-ref-warning-text" style="font-size:0.76rem; font-weight:800; color:#fee2e2; line-height:1.2; background:rgba(0,0,0,0.35); padding:3px 8px; border-radius:4px; border-right:3px solid #ef4444;">
                                رقم التحويلة مكرر في النظام!
                            </div>
                        </div>

                    </div>

                    <!-- Row 5: Action Buttons (أزرار التحكم المحاسبية: حفظ الاعتمادات، استبعاد المكرر، استبعاد الكل) -->
                    <div style="display:flex; flex-direction:column; gap:6px; margin-top:auto; padding-top:6px; border-top:1px solid rgba(255,255,255,0.1);">
                        <!-- Primary Action Bar (حفظ الاعتمادات، استبعاد المكرر، استبعاد الكل، إلغاء) -->
                        <div style="display:flex; align-items:center; gap:6px; width:100%;">
                            <button type="button" id="btn-apply-financial-ocr" onclick="FinancialOCR.applyToActiveScreen()" style="flex:1.55; height:42px; background:linear-gradient(135deg, #059669 0%, #10b981 50%, #047857 100%); border:1.5px solid rgba(52,211,153,0.5); color:#fff; padding:0 10px; border-radius:8px; font-size:0.95rem; font-weight:900; cursor:pointer; box-shadow:0 4px 18px rgba(16,185,129,0.45); display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s ease; white-space:nowrap;" title="حفظ واعتماد السندات السليمة واستبعاد المكرر تلقائياً">
                                <span style="font-size:1.15rem;">💾</span> <span id="btn-apply-text">حفظ الاعتمادات</span>
                            </button>
                            <button type="button" id="btn-purge-duplicates" onclick="FinancialOCR.purgeDuplicateItems()" style="flex:1.15; height:42px; background:linear-gradient(135deg, rgba(220,38,38,0.35), rgba(185,28,28,0.55)); border:1.5px solid #ef4444; color:#fca5a5; padding:0 8px; border-radius:8px; font-size:0.86rem; font-weight:900; cursor:pointer; box-shadow:0 3px 14px rgba(239,68,68,0.3); display:flex; align-items:center; justify-content:center; gap:5px; transition:all 0.2s ease; white-space:nowrap;" title="استبعاد وحذف جميع الإشعارات المكررة بنقرة واحدة">
                                <span style="font-size:1.05rem;">🚨</span> <span>استبعاد المكرر</span>
                            </button>
                            <button type="button" id="btn-purge-all" onclick="FinancialOCR.purgeAllItems()" style="flex:0.95; height:42px; background:linear-gradient(135deg, rgba(71,85,105,0.4), rgba(30,41,59,0.6)); border:1.5px solid rgba(239,68,68,0.45); color:#cbd5e1; padding:0 8px; border-radius:8px; font-size:0.84rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; transition:all 0.2s ease; white-space:nowrap;" title="إفراغ الدفعة بالكامل واستبعاد جميع المستندات">
                                <span style="font-size:1.0rem;">🗑️</span> <span>استبعاد الكل</span>
                            </button>
                            <button type="button" onclick="FinancialOCR.closeReviewModal()" style="flex:0.65; height:42px; background:rgba(255,255,255,0.08); border:1.5px solid rgba(255,255,255,0.22); color:#e2e8f0; padding:0 8px; border-radius:8px; font-weight:900; font-size:0.82rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:3px; white-space:nowrap;">
                                <span>✕</span> <span>إلغاء</span>
                            </button>
                        </div>

                        <!-- Secondary Contextual Action (حفظ هذا السند فقط / استبعاد هذا المستند الفردي) -->
                        <div id="ocr-single-actions-bar" style="display:none; justify-content:space-between; align-items:center; gap:6px;">
                            <button type="button" id="btn-apply-single-financial-ocr" onclick="FinancialOCR.applyCurrentItemOnly()" style="flex:1.4; height:32px; background:linear-gradient(135deg, rgba(14,165,233,0.35), rgba(2,132,199,0.5)); border:1.5px solid #38bdf8; color:#ffffff; padding:0 10px; border-radius:6px; font-size:0.82rem; font-weight:900; cursor:pointer; text-align:center; box-shadow:0 2px 10px rgba(56,189,248,0.25); display:flex; align-items:center; justify-content:center; gap:5px;">
                                <span>⚡</span> <span id="btn-apply-single-text">حفظ هذا السند فقط</span>
                            </button>
                            <button type="button" id="btn-delete-current-ocr-item" onclick="FinancialOCR.deleteCurrentItem()" style="flex:0.9; height:32px; background:rgba(239,68,68,0.2); border:1.2px solid rgba(239,68,68,0.4); color:#fca5a5; padding:0 8px; border-radius:6px; font-weight:800; font-size:0.78rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;" title="استبعاد هذا المستند المعروض حالياً">
                                <span>🗑️</span> <span>استبعاد هذا السند</span>
                            </button>
                        </div>

                        <!-- Summary & Arabic Words -->
                        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:6px; padding:4px 8px; display:flex; align-items:center; gap:6px;">
                            <span style="font-size:0.8rem;">🤖</span>
                            <span id="ocr-doc-summary" style="color:#94a3b8; font-size:0.72rem; line-height:1.2; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">تأكد من مطابقة البيانات المستخرجة</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- PART 2 (الثلث الأوسط - المنتصف): صورة التحويل أو الشيك المصرفي (100% Height Top-to-Bottom in the Center) -->
            <div id="ocr-col-image" class="ocr-dashboard-col" style="height:100%; min-height:0; display:flex; flex-direction:column; background:#010409; border:1.5px solid rgba(56,189,248,0.25); border-radius:10px; position:relative; overflow:hidden; direction:rtl; box-sizing:border-box;">
                <!-- Floating Controls: Top Left (Rotate, Rescan, In-Place Zoom & Lightbox Zoom) -->
                <div style="position:absolute; top:8px; left:8px; z-index:20; display:flex; align-items:center; gap:5px;">
                    <button type="button" onclick="FinancialOCR.rotatePreviewImage()" style="background:rgba(15,23,42,0.88); border:1px solid rgba(255,255,255,0.25); color:#e2e8f0; font-size:0.78rem; padding:4px 9px; border-radius:6px; cursor:pointer; font-weight:800; backdrop-filter:blur(10px); display:flex; align-items:center; gap:3px; box-shadow:0 4px 15px rgba(0,0,0,0.6);" title="تدوير الصورة 90 درجة">
                        <span>🔄</span> تدوير
                    </button>
                    <button type="button" onclick="FinancialOCR.rescanCurrentItem()" style="background:linear-gradient(135deg, rgba(56,189,248,0.35), rgba(14,165,233,0.45)); border:1.5px solid #38bdf8; color:#e0f2fe; font-size:0.78rem; padding:4px 9px; border-radius:6px; cursor:pointer; font-weight:900; backdrop-filter:blur(10px); display:flex; align-items:center; gap:3px; box-shadow:0 0 12px rgba(56,189,248,0.4);" title="إعادة فحص هذا المستند بالذكاء الاصطناعي">
                        <span>⚡</span> إعادة فحص
                    </button>
                    <!-- زر الحجم الكامل / تركيز البيانات -->
                    <button type="button" id="btn-full-size-preview" onclick="FinancialOCR.toggleFullSizeView()" style="background:rgba(15,23,42,0.88); border:1.2px solid #10b981; color:#a7f3d0; font-size:0.78rem; padding:4px 9px; border-radius:6px; cursor:pointer; font-weight:800; backdrop-filter:blur(10px); display:flex; align-items:center; gap:3px; box-shadow:0 4px 15px rgba(0,0,0,0.6);" title="عرض الإشعار بالحجم الكامل أو تركيز البيانات">
                        <span>⛶</span> <span id="btn-full-size-text">الحجم الكامل</span>
                    </button>
                    <!-- In-Place Zoom Bar (تكبير / تصغير / إعادة ضبط / نسبة التكبير بالرولا) -->
                    <div style="display:flex; align-items:center; gap:2px; background:rgba(15,23,42,0.88); border:1.2px solid rgba(56,189,248,0.4); border-radius:6px; padding:2px 4px; backdrop-filter:blur(10px); box-shadow:0 4px 15px rgba(0,0,0,0.5);">
                        <button type="button" onclick="FinancialOCR.zoomInPreview()" style="background:none; border:none; color:#7dd3fc; font-weight:900; font-size:0.85rem; cursor:pointer; padding:2px 5px; line-height:1;" title="تكبير بالماوس أو الرولا (+)">🔍+</button>
                        <button type="button" onclick="FinancialOCR.zoomOutPreview()" style="background:none; border:none; color:#7dd3fc; font-weight:900; font-size:0.85rem; cursor:pointer; padding:2px 5px; line-height:1;" title="تصغير بالماوس أو الرولا (-)">🔍-</button>
                        <button type="button" id="ocr-preview-zoom-badge" onclick="FinancialOCR.resetPreviewZoom()" style="background:rgba(56,189,248,0.22); border:1px solid #38bdf8; color:#e0f2fe; font-weight:900; font-size:0.75rem; border-radius:4px; cursor:pointer; padding:1px 6px; font-family:'Consolas',monospace; line-height:1.2;" title="نسبة التكبير - انقر لإعادة الضبط 100%">100%</button>
                    </div>
                    <button type="button" onclick="FinancialOCR.openZoomViewer()" style="background:linear-gradient(135deg, rgba(56,189,248,0.4), rgba(14,165,233,0.55)); border:1.5px solid #38bdf8; color:#fff; font-size:0.78rem; padding:4px 10px; border-radius:6px; cursor:pointer; font-weight:900; backdrop-filter:blur(10px); display:flex; align-items:center; gap:3px; box-shadow:0 0 15px rgba(56,189,248,0.5);" title="تكبير كامل للشاشة وتدقيق الأختام والتوقيعات">
                        <span>⛶</span> تكبير كامل
                    </button>
                </div>

                <!-- Floating Controls: Top Right (Document Badge & Counter) -->
                <div style="position:absolute; top:8px; right:8px; z-index:20; display:flex; align-items:center; gap:5px; background:rgba(15,23,42,0.88); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(10px); padding:4px 10px; border-radius:6px; box-shadow:0 4px 15px rgba(0,0,0,0.6);">
                    <span style="font-size:0.85rem;">📷</span>
                    <span style="font-size:0.78rem; color:#f1f5f9; font-weight:800;">صورة الشيك/المستند</span>
                    <span id="ocr-current-doc-index-badge" style="background:rgba(56,189,248,0.25); color:#7dd3fc; border:1px solid #38bdf8; padding:1px 6px; border-radius:8px; font-size:0.74rem; font-weight:900;">1 / 1</span>
                </div>

                <!-- Floating AI Status Banner on Center Preview (يوضح للمحاسب بدقة تشخيص الذكاء الاصطناعي) -->
                <div id="ocr-preview-status-banner" style="display:none; position:absolute; top:42px; left:8px; right:8px; z-index:22; backdrop-filter:blur(14px); border-radius:8px; padding:4px 10px; font-size:0.75rem; font-weight:800; align-items:center; justify-content:space-between; gap:6px; box-shadow:0 4px 16px rgba(0,0,0,0.6); direction:rtl; transition:all 0.2s ease;">
                    <div style="display:flex; align-items:center; gap:6px; flex:1; min-width:0;">
                        <span id="ocr-preview-status-icon" style="font-size:1.05rem; flex-shrink:0;"></span>
                        <div style="display:flex; flex-direction:column; line-height:1.2; flex:1; min-width:0;">
                            <strong id="ocr-preview-status-title" style="font-size:0.78rem; font-weight:900;"></strong>
                            <span id="ocr-preview-status-desc" style="font-size:0.71rem; font-weight:700; opacity:0.95; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:5px; flex-shrink:0;">
                        <span id="ocr-preview-status-badge" style="padding:2px 7px; border-radius:5px; font-size:0.68rem; font-weight:900; white-space:nowrap;"></span>
                        <button type="button" onclick="document.getElementById('ocr-preview-status-banner').style.display='none'" style="background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.25); color:#fff; border-radius:4px; width:22px; height:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.82rem; font-weight:900;" title="إخفاء التنبيه لمعاينة الصورة">✕</button>
                    </div>
                </div>

                <!-- Main Top-to-Bottom Full Viewport Image Container with Interactive Mouse Wheel Zoom & Drag/Pan -->
                <div id="ocr-preview-container" style="width:100%; height:100%; padding:42px 6px 44px 6px; box-sizing:border-box; display:flex; align-items:center; justify-content:center; cursor:zoom-in; overflow:hidden; position:relative; user-select:none;" title="عجلة الماوس (الرولا) للتكبير والتصغير، انقر مرتين للتبديل، واسحب للتحريك">
                    <img id="ocr-preview-img" src="" alt="صورة المستند المالي" style="max-width:100%; max-height:100%; object-fit:contain; display:block; transform-origin:center center; transition:transform 0.05s ease-out; pointer-events:none; filter:drop-shadow(0 6px 20px rgba(0,0,0,0.6));">
                </div>

                <!-- Floating Controls: Bottom Center Navigation & Wheel Zoom Hint -->
                <div style="position:absolute; bottom:8px; left:50%; transform:translateX(-50%); z-index:20; display:flex; align-items:center; gap:8px; background:rgba(15,23,42,0.92); backdrop-filter:blur(14px); border:1.5px solid rgba(56,189,248,0.45); padding:4px 14px; border-radius:30px; box-shadow:0 6px 25px rgba(0,0,0,0.85);">
                    <button type="button" onclick="FinancialOCR.prevItem()" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; font-size:0.76rem; padding:3px 10px; border-radius:10px; cursor:pointer; font-weight:800;">‹ السابق</button>
                    <span style="color:#7dd3fc; font-size:0.75rem; font-weight:800; white-space:nowrap;">💡 كبّر بالرولا وحرّك بالسحب أو ‹ ›</span>
                    <button type="button" onclick="FinancialOCR.nextItem()" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; font-size:0.76rem; padding:3px 10px; border-radius:10px; cursor:pointer; font-weight:800;">التالي ›</button>
                </div>
            </div>

            <!-- PART 3 (الجهة الأخيرة على اليسار): تفاصيل التحويلة مرتبة بدقة: التاريخ أولاً، رقم التحويلة، المبلغ، من حساب وإلى حساب بألوان مريحة للعين -->
            <div id="ocr-col-form" class="ocr-dashboard-col" style="height:100%; min-height:0; display:flex; flex-direction:column; justify-content:flex-start; background:rgba(15,23,42,0.98); border:2px solid rgba(56,189,248,0.4); border-radius:10px; padding:8px 10px; overflow-y:auto; overflow-x:hidden; scrollbar-width:thin; direction:rtl; box-sizing:border-box;">
                
                <div style="display:flex; flex-direction:column; gap:7px; width:100%;">
                    
                    <!-- Top Toolbar: أزرار واضحة بأيقونات صريحة بدون أسامي (رموز فقط) -->
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1.5px solid rgba(255,255,255,0.12); padding-bottom:5px; margin-bottom:2px; gap:4px; overflow-x:auto; scrollbar-width:none; flex-shrink:0;">
                        <div style="display:flex; align-items:center; gap:4px; flex-wrap:nowrap;">
                            <!-- 1. رمز التبديل (⇄) -->
                            <button type="button" onclick="FinancialOCR.toggleLayoutSide()" style="height:28px; width:34px; min-width:34px; padding:0; border-radius:6px; background:linear-gradient(135deg, rgba(6,182,212,0.25), rgba(14,165,233,0.2)); border:1.8px solid #22d3ee; color:#67e8f9; font-weight:900; font-size:1.15rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(6,182,212,0.3); transition:all 0.15s ease;" title="تبديل موضع صورة الشيك والبيانات (⇄)">
                                <span>⇄</span>
                            </button>
                            <!-- 2. رمز الملء (⛶) -->
                            <button type="button" onclick="FinancialOCR.toggleFullscreen()" style="height:28px; width:34px; min-width:34px; padding:0; border-radius:6px; background:linear-gradient(135deg, rgba(16,185,129,0.25), rgba(5,150,105,0.2)); border:1.8px solid #10b981; color:#6ee7b7; font-weight:900; font-size:1.15rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(16,185,129,0.25); transition:all 0.15s ease;" title="السيطرة على شاشة الكمبيوتر بالكامل (ملء الشاشة ⛶)">
                                <span>⛶</span>
                            </button>
                            <!-- 3. رمز ريفريش (🔄) -->
                            <button type="button" id="btn-rescan-current-ocr" onclick="FinancialOCR.rescanCurrentItem()" style="height:28px; width:34px; min-width:34px; padding:0; border-radius:6px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.2)); border:1.8px solid #38bdf8; color:#7dd3fc; font-weight:900; font-size:1.05rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(56,189,248,0.3); transition:all 0.15s ease;" title="إعادة فحص وقراءة هذا المستند بالذكاء الاصطناعي (🔄)">
                                <span id="rescan-icon">🔄</span>
                                <span id="rescan-text" style="display:none;"></span>
                            </button>
                            <!-- 4. رمز المفتاح (🔑) -->
                            <button type="button" onclick="FinancialOCR.openKeyModal()" style="height:28px; width:34px; min-width:34px; padding:0; border-radius:6px; background:linear-gradient(135deg, rgba(245,158,11,0.25), rgba(217,119,6,0.2)); border:1.8px solid #fbbf24; color:#fde68a; font-weight:900; font-size:1.05rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(245,158,11,0.25); transition:all 0.15s ease;" title="تهيئة مفتاح Gemini API Key (🔑)">
                                <span>🔑</span>
                            </button>
                            <!-- 5. رمز العقل المدبر للتدريب (🧠) -->
                            <button type="button" onclick="FinancialOCR.openTrainer()" style="height:28px; width:34px; min-width:34px; padding:0; border-radius:6px; background:linear-gradient(135deg, rgba(168,85,247,0.25), rgba(147,51,234,0.2)); border:1.8px solid #c084fc; color:#e9d5ff; font-weight:900; font-size:1.05rem; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(168,85,247,0.25); transition:all 0.15s ease;" title="شرح وتدريب النماذج للذكاء الاصطناعي - استوديو التدريب (🧠)">
                                <span>🧠</span>
                            </button>
                        </div>
                        <button type="button" onclick="FinancialOCR.closeReviewModal()" style="height:28px; width:28px; min-width:28px; border-radius:6px; background:rgba(239,68,68,0.25); border:1.8px solid #ef4444; color:#fca5a5; font-size:1.0rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 0 10px rgba(239,68,68,0.3);" title="إغلاق">✕</button>
                    </div>

                    <!-- Hidden Header elements (تم حذف عبارة تفاصيل التحويلة تماماً بناء على طلب المستخدم) -->
                    <div style="display:none;">
                        <h3 id="ocr-header-title"></h3>
                        <span id="ocr-layout-side-indicator"></span>
                    </div>

                    <!-- AI Diagnostic Box (تشخيص الذكاء الاصطناعي الواضح: سبب عدم وضوح الصورة، أو كونها صورة سيارة/غير مالي) -->
                    <div id="ocr-ai-reason-box" style="display:none; border-radius:8px; padding:5px 9px; font-size:0.74rem; font-weight:800; direction:rtl; line-height:1.25; box-shadow:0 4px 14px rgba(0,0,0,0.4); margin-bottom:4px; flex-shrink:0;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2px;">
                            <div style="display:flex; align-items:center; gap:5px;">
                                <span id="ocr-ai-reason-icon" style="font-size:0.90rem;">🤖</span>
                                <span id="ocr-ai-reason-title" style="font-weight:900; font-size:0.76rem;">تشخيص الذكاء الاصطناعي:</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:4px;">
                                <span id="ocr-ai-reason-badge" style="padding:1px 6px; border-radius:4px; font-size:0.68rem; font-weight:900;"></span>
                                <button type="button" onclick="document.getElementById('ocr-ai-reason-box').style.display='none'" style="background:transparent; border:none; color:inherit; font-size:0.85rem; font-weight:900; cursor:pointer; padding:0 3px;" title="إغلاق التنبيه">✕</button>
                            </div>
                        </div>
                        <div id="ocr-ai-reason-desc" style="opacity:0.95; font-size:0.72rem; line-height:1.2;"></div>
                    </div>

                    <!-- Vertical Stacked Fields (تصميم محاسبي عالي التباين، موسط بدقة، وخلفية داكنة للمبلغ بدون هالات) -->
                    <div style="display:flex; flex-direction:column; gap:7px;">
                        
                        <!-- 1. التاريخ: إنقاص 3 درجات (1.25rem) بارتفاع 40px وموسط في المنتصف تماماً -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(14,165,233,0.15), rgba(15,23,42,0.9)); padding:6px 10px; border-radius:8px; border:2px solid #38bdf8; box-shadow:0 0 14px rgba(56,189,248,0.3), inset 0 0 8px rgba(56,189,248,0.12); flex-shrink:0;">
                            <label style="color:#7dd3fc; font-size:0.95rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 8px rgba(56,189,248,0.5);">التاريخ:</label>
                            <div style="display:flex; align-items:center; justify-content:center; flex:1; width:100%;">
                                <input type="date" id="ocr-field-date" oninput="FinancialOCR.syncCurrentItemFromForm()" style="width:100%; height:40px; background:#0f172a; border:1.8px solid #38bdf8; border-radius:6px; color:#ffffff; font-size:1.25rem; font-weight:900; font-family:'Consolas',monospace; padding:0 8px; outline:none; text-align:center; direction:ltr !important; box-shadow:0 0 10px rgba(56,189,248,0.3); box-sizing:border-box; margin:0 auto;" title="التاريخ موسط في المنتصف">
                                <div id="ocr-box-due-date" style="display:none; align-items:center; gap:4px;">
                                    <label style="color:#c084fc; font-size:0.82rem; font-weight:900; white-space:nowrap; margin:0;">الاستحقاق:</label>
                                    <input type="date" id="ocr-field-due-date" oninput="FinancialOCR.syncCurrentItemFromForm()" style="width:135px; height:40px; background:#0f172a; border:2px solid rgba(168,85,247,0.6); border-radius:6px; color:#e9d5ff; font-size:1.15rem; font-weight:900; font-family:'Consolas',monospace; padding:0 6px; outline:none; text-align:center; direction:ltr !important; box-sizing:border-box;">
                                </div>
                            </div>
                        </div>

                        <!-- 2. السند والبنك: يكتب السند وأمامها طوالي اسم البنك موسط في النص -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(6,182,212,0.14), rgba(15,23,42,0.85)); padding:6px 10px; border-radius:8px; border:2px solid #06b6d4; box-shadow:0 0 14px rgba(6,182,212,0.3); flex-shrink:0;">
                            <input type="hidden" id="ocr-field-doc-type" value="bank_transfer">
                            <label id="ocr-badge-doc-type" style="color:#d8b4fe; font-size:0.95rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(168,85,247,0.5);">السند:</label>
                            <input type="text" id="ocr-field-bank-name" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="اسم البنك (مثال: بنك الخرطوم)" style="flex:1; height:38px; background:#0f172a; border:1.8px solid #06b6d4; border-radius:6px; color:#ffffff; font-size:1.05rem; font-weight:800; padding:0 10px; outline:none; text-align:center; box-sizing:border-box; box-shadow:0 0 10px rgba(6,182,212,0.25);">
                        </div>

                        <!-- 3. الرقم: كتابة "الرقم:" وإنقاص خط الرقم درجتين (1.55rem) بارتفاع 40px وموسط في النص -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(2,132,199,0.18), rgba(15,23,42,0.9)); padding:6px 10px; border-radius:8px; border:2px solid #0284c7; box-shadow:0 0 14px rgba(2,132,199,0.4); flex-shrink:0;">
                            <label id="ocr-label-ref-no" style="color:#38bdf8; font-size:0.95rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 8px rgba(56,189,248,0.6);">الرقم:</label>
                            <input type="text" id="ocr-field-ref-no" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="الرقم" style="flex:1; height:40px; background:#0f172a; border:2px solid #38bdf8; border-radius:6px; color:#38bdf8; font-size:1.55rem; font-weight:900; padding:0 10px; font-family:'Consolas',monospace; text-align:center; direction:ltr !important; unicode-bidi:plaintext; outline:none; box-sizing:border-box; box-shadow:0 0 12px rgba(56,189,248,0.4); letter-spacing:0.5px; margin:0 auto;" title="الرقم موسط في النص">
                        </div>

                        <!-- 4. المبلغ: موسط في النص وإنقاص درجتين (2.30rem) بارتفاع 44px باللون الذهبي وبدون خانات عشرية -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:6px; background:#000000; padding:6px 8px; border-radius:8px; border:2.5px solid #fbbf24; box-shadow:0 0 20px rgba(251,191,36,0.45); flex-shrink:0;">
                            <div style="display:flex; align-items:center; gap:4px; white-space:nowrap;">
                                <label style="color:#fbbf24; font-size:1.02rem; font-weight:900; margin:0; text-shadow:0 0 8px rgba(251,191,36,0.4);">المبلغ:</label>
                                <span id="ocr-amount-unclear-badge" style="display:none; background:#262626; border:1px solid #ef4444; color:#fca5a5; font-size:0.70rem; padding:1px 5px; border-radius:4px; font-weight:900;">غير واضح</span>
                            </div>
                            <input type="text" id="ocr-field-amount" oninput="FinancialOCR.handleAmountInput(this)" onblur="FinancialOCR.formatAmountField(this)" placeholder="0" style="flex:1; height:44px; min-width:0; width:100%; background:#000000; border:2.5px solid #fbbf24; border-radius:8px; color:#fef08a; font-size:2.30rem; font-weight:900; padding:0 4px; font-family:'Bahnschrift Condensed','Bahnschrift','Segoe UI','Tajawal',sans-serif; font-variant-numeric:tabular-nums; text-align:center; direction:ltr !important; unicode-bidi:plaintext; outline:none; box-shadow:0 0 20px rgba(251,191,36,0.45); text-shadow:0 0 14px rgba(251,191,36,0.5); box-sizing:border-box; letter-spacing:-0.5px;" title="المبلغ المالي بدون خانات عشرية">
                        </div>

                        <!-- 5. من: إنقاص درجة واحدة (1.06rem) بارتفاع 36px وموسط في المنتصف -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:6px; background:linear-gradient(135deg, rgba(2,132,199,0.18), rgba(15,23,42,0.85)); padding:6px 10px; border-radius:8px; border:2px solid #0284c7; box-shadow:0 0 14px rgba(2,132,199,0.4); flex-shrink:0;">
                            <label style="color:#7dd3fc; font-size:0.98rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(2,132,199,0.6);">من:</label>
                            <input type="text" id="ocr-field-sender-acc" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="رقم الحساب" style="flex:1; height:36px; background:#0f172a; border:2px solid #0284c7; border-radius:6px; color:#bae6fd; font-family:'Consolas',monospace; font-size:1.06rem; font-weight:900; padding:0 10px; outline:none; text-align:center; direction:ltr !important; unicode-bidi:plaintext; box-sizing:border-box; box-shadow:0 0 10px rgba(2,132,199,0.3); margin:0 auto;" title="رقم الحساب موسط في المنتصف">
                        </div>

                        <!-- 6. إلى: رقم حساب المستلم إنقاص درجة واحدة (1.06rem) بارتفاع 36px وموسط في المنتصف -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:6px; background:linear-gradient(135deg, rgba(16,185,129,0.18), rgba(15,23,42,0.85)); padding:6px 10px; border-radius:8px; border:2px solid #10b981; box-shadow:0 0 14px rgba(16,185,129,0.4); flex-shrink:0;">
                            <label style="color:#6ee7b7; font-size:0.98rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(168,85,247,0.6);">إلى:</label>
                            <input type="text" id="ocr-field-receiver-acc" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="رقم الحساب" style="flex:1; height:36px; background:#0f172a; border:2px solid #059669; border-radius:6px; color:#a7f3d0; font-family:'Consolas',monospace; font-size:1.06rem; font-weight:900; padding:0 10px; outline:none; text-align:center; direction:ltr !important; unicode-bidi:plaintext; box-sizing:border-box; box-shadow:0 0 10px rgba(16,185,129,0.3); margin:0 auto;" title="رقم الحساب موسط في المنتصف">
                        </div>

                        <!-- 7. إلى: اسم المستلم إنقاص درجة واحدة (0.94rem) بارتفاع 36px وموسط في المنتصف -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:6px; background:linear-gradient(135deg, rgba(99,102,241,0.15), rgba(15,23,42,0.85)); padding:6px 10px; border-radius:8px; border:2px solid #6366f1; box-shadow:0 0 14px rgba(99,102,241,0.4); flex-shrink:0;">
                            <label style="color:#c7d2fe; font-size:0.98rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(99,102,241,0.5);">إلى:</label>
                            <input type="text" id="ocr-field-receiver" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="اسم المرسل إليه كاملاً" style="flex:1; height:36px; background:#0f172a; border:2px solid #818cf8; border-radius:6px; color:#ffffff; font-size:0.94rem; font-weight:800; padding:0 10px; outline:none; text-align:center; box-sizing:border-box; margin:0 auto;" title="اسم المستلم موسط في المنتصف">
                        </div>

                        <!-- 8. الجوال: الاستغناء عن عبارة "رقم الموبايل" وكتابة "الجوال" فقط -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(236,72,153,0.15), rgba(15,23,42,0.85)); padding:7px 10px; border-radius:8px; border:2.5px solid #ec4899; box-shadow:0 0 16px rgba(236,72,153,0.4); flex-shrink:0;">
                            <label style="color:#f472b6; font-size:1.0rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(236,72,153,0.5);">الجوال:</label>
                            <input type="text" id="ocr-field-phone" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="" style="flex:1; height:38px; background:#0f172a; border:2px solid #f472b6; border-radius:6px; color:#fbcfe8; font-family:'Consolas',monospace; font-size:1.12rem; font-weight:800; padding:0 10px; outline:none; text-align:center; direction:ltr !important; unicode-bidi:plaintext; box-sizing:border-box;">
                        </div>

                        <!-- 9. التعليق: استبدال السرد والبيان والتحويلة بكتابة "التعليق" فقط في سطر أفقي موحد -->
                        <div class="ocr-field-box" style="display:flex; align-items:center; justify-content:space-between; gap:8px; background:linear-gradient(135deg, rgba(20,184,166,0.15), rgba(15,23,42,0.85)); padding:7px 10px; border-radius:8px; border:2.5px solid #14b8a6; box-shadow:0 0 16px rgba(20,184,166,0.4); flex-shrink:0;">
                            <label style="color:#5eead4; font-size:0.95rem; font-weight:900; white-space:nowrap; margin:0; text-shadow:0 0 6px rgba(20,184,166,0.5);">التعليق:</label>
                            <input type="text" id="ocr-field-notes" oninput="FinancialOCR.syncCurrentItemFromForm()" placeholder="" style="flex:1; height:38px; background:#0f172a; border:2px solid #2dd4bf; border-radius:6px; color:#f0fdfa; font-size:0.95rem; font-weight:700; padding:0 10px; outline:none; box-sizing:border-box; direction:rtl; text-align:right;">
                        </div>

                        <!-- Hidden helper input to keep full backward compatibility -->
                        <input type="hidden" id="ocr-field-sender">

                    </div>

                    <!-- الشريط السفلي: زر الرجوع للأصل، متاح التعديل المباشر، واستبدال الصورة -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:8px; border-top:1px solid rgba(255,255,255,0.1); gap:6px; width:100%; box-sizing:border-box;">
                        <button type="button" id="btn-restore-original-ocr" onclick="FinancialOCR.restoreOriginalItemData()" style="height:34px; background:linear-gradient(135deg, rgba(245,158,11,0.3), rgba(217,119,6,0.45)); border:1.5px solid #fbbf24; color:#fef08a; padding:0 10px; border-radius:7px; font-weight:900; font-size:0.80rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:5px; box-shadow:0 0 12px rgba(245,158,11,0.35); transition:all 0.15s ease; white-space:nowrap; flex-shrink:0;" title="استعادة القيم الأصلية المستخرجة بالذكاء الاصطناعي (في حال قمت بتعديل الاسم أو رقم التحويلة أو المبلغ أو الحسابات)">
                            <span style="font-size:0.95rem;">↺</span> <span>الرجوع للأصل</span>
                        </button>
                        <div style="background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#34d399; padding:4px 8px; border-radius:6px; font-size:0.74rem; font-weight:900; white-space:nowrap; display:flex; align-items:center; gap:4px; flex-shrink:0;">
                            <span>✏️</span> <span>متاح التعديل المباشر</span>
                        </div>
                        <button type="button" onclick="FinancialOCR.triggerUploadAgain()" style="height:34px; background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#7dd3fc; border-radius:6px; padding:0 8px; font-size:0.76rem; font-weight:800; cursor:pointer; white-space:nowrap; flex-shrink:0; display:flex; align-items:center; gap:3px;">
                            <span>🔄</span> <span>استبدال</span>
                        </button>
                    </div>

                </div>

            </div>
    </div>
</div>

<!-- AI Trainer & Interactive Knowledge Studio Modal -->
<div id="financial-ocr-trainer-modal" style="display:none; position:fixed !important; inset:0 !important; background:rgba(0,0,0,0.92) !important; backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:2147483647 !important; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
    <div style="background:#0f172a; border:2px solid rgba(168,85,247,0.6); border-radius:22px; width:100%; max-width:920px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.9), 0 0 45px rgba(168,85,247,0.3); overflow:hidden;">
        
        <!-- Header -->
        <div style="padding:16px 24px; background:linear-gradient(135deg, rgba(88,28,135,0.4), rgba(15,23,42,0.9)); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:2rem; filter:drop-shadow(0 0 10px rgba(168,85,247,0.8));">🧠</span>
                <div>
                    <h3 style="margin:0; color:#fff; font-size:1.3rem; font-weight:900; letter-spacing:0.5px;">استوديو تدريب الذكاء الاصطناعي المالي (AI OCR Trainer)</h3>
                    <p style="margin:2px 0 0 0; color:#d8b4fe; font-size:0.85rem; font-weight:600;">اشرح للبرنامج معالم ونماذج الإشعارات والشيكات ليتعرف عليها ويفرقها تلقائياً</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" onclick="FinancialOCR.openKeyModal()" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#7dd3fc; padding:6px 14px; border-radius:10px; font-size:0.85rem; font-weight:800; cursor:pointer;" title="إعداد مفتاح Gemini">
                    <span>🔑</span> مفتاح API
                </button>
                <button type="button" onclick="FinancialOCR.closeTrainer()" style="background:transparent; border:none; color:#94a3b8; font-size:1.8rem; cursor:pointer; padding:0 5px; line-height:1;">✕</button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div style="display:flex; background:rgba(15,23,42,0.7); border-bottom:1px solid rgba(255,255,255,0.08); padding:8px 20px; gap:10px; overflow-x:auto;">
            <button type="button" id="tab-btn-templates" onclick="FinancialOCR.switchTrainerTab('templates')" style="background:rgba(168,85,247,0.25); border:1px solid #c084fc; color:#fff; padding:8px 18px; border-radius:12px; font-weight:900; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <span>📋</span> النماذج المدربة النشطة (<span id="trainer-templates-count">4</span>)
            </button>
            <button type="button" id="tab-btn-add-template" onclick="FinancialOCR.switchTrainerTab('add')" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; padding:8px 18px; border-radius:12px; font-weight:800; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <span>➕</span> شرح وإضافة نموذج بنك جديد
            </button>
            <button type="button" id="tab-btn-guidelines" onclick="FinancialOCR.switchTrainerTab('guidelines')" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; padding:8px 18px; border-radius:12px; font-weight:800; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:8px;">
                <span>💡</span> القواعد والتوجيهات العامة
            </button>
        </div>

        <!-- Body -->
        <div style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:16px;">
            
            <!-- SECTION 1: Active Templates Grid -->
            <div id="trainer-tab-templates" style="display:flex; flex-direction:column; gap:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <span style="color:#cbd5e1; font-size:0.9rem; font-weight:700;">هذه هي النماذج التي يعرفها ويفهم معالمها الذكاء الاصطناعي حالياً:</span>
                    <button type="button" onclick="FinancialOCR.switchTrainerTab('add')" style="background:linear-gradient(135deg, #8b5cf6, #6d28d9); border:none; color:#fff; padding:6px 14px; border-radius:8px; font-size:0.85rem; font-weight:800; cursor:pointer;">
                        ➕ اشرح نموذجاً إضافياً الآن
                    </button>
                </div>
                <div id="trainer-templates-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px;">
                    <!-- Injected dynamically via JS -->
                </div>
            </div>

            <!-- SECTION 2: Add New Template Form -->
            <div id="trainer-tab-add" style="display:none; flex-direction:column; gap:14px;">
                <div style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.3); border-radius:14px; padding:16px; display:flex; flex-direction:column; gap:12px;">
                    <h4 style="margin:0; color:#d8b4fe; font-size:1.1rem; font-weight:900;">📝 شرح نموذج بنكي أو شيك جديد للذكاء الاصطناعي</h4>
                    <p style="margin:0; color:#94a3b8; font-size:0.85rem;">اشرح للذكاء الاصطناعي كيف يتعرف على هذا الإشعار (موقع رقم العملية، اسم البنك، كيفية كتابة المبالغ):</p>
                    
                    <input type="hidden" id="edit-template-id" value="">

                    <!-- Quick Bank Presets -->
                    <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center; background:rgba(0,0,0,0.25); padding:8px 12px; border-radius:10px;">
                        <span style="font-size:0.8rem; color:#d8b4fe; font-weight:800;">⚡ اقتراحات بنكية جاهزة:</span>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('مصرف السلام', 'bank_transfer', 'إشعار تحويل مصرف السلام، شعار البنك أعلى الإشعار، كود المعاملة المرجعي يتكون من 10 أرقام، المبلغ بالجنيه السوداني SDG، اسم المستفيد ورقم حسابه.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">مصرف السلام</button>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('بنك النيل للتجارة والتنمية', 'bank_transfer', 'إشعار تطبيق بنك النيل، كود المعاملة المرجعي، المبلغ، اسم المحول، وتاريخ التحويل.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">بنك النيل</button>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('بنك العمال الوطني', 'cheque', 'شيك مصرفي بنك العمال الوطني، رقم الشيك في شريط MICR السفلي بين علامتي ⑈، اسم المستفيد بعد (ادفعوا لأمر)، المبلغ بالأرقام والحروف، تاريخ الاستحقاق.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">شيك بنك العمال</button>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('بنك التضامن الإسلامي', 'bank_transfer', 'إشعار تحويل بنك التضامن، رقم المرجع، التاريخ، اسم المستفيد والمبلغ المحول.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">بنك التضامن</button>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('بنك الجزيرة السوداني الأردني', 'bank_transfer', 'إشعار تطبيق بنك الجزيرة السوداني الأردني، الرقم المرجعي، المبلغ بالجنيه SDG، التاريخ واسم المستلم.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">بنك الجزيرة</button>
                        <button type="button" onclick="FinancialOCR.applyBankSuggestion('بنك البركة السوداني', 'bank_transfer', 'إشعار تحويل بنك البركة، رقم العملية، المبلغ، اسم المحول والمستفيد.')" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; border-radius:15px; padding:3px 10px; font-size:0.8rem; cursor:pointer;">بنك البركة</button>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; color:#cbd5e1; font-size:0.85rem; font-weight:800; margin-bottom:4px;">اسم النموذج التوضيحي *</label>
                            <input type="text" id="new-template-name" placeholder="مثال: إشعار مصرف السلام أو شيك بنك النيل" style="width:100%; height:44px; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:0.95rem; padding:0 10px; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; color:#cbd5e1; font-size:0.85rem; font-weight:800; margin-bottom:4px;">اسم البنك المستهدف *</label>
                            <input type="text" id="new-template-bank" placeholder="مثال: مصرف السلام، بنك العمال، بنك النيل" style="width:100%; height:44px; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:0.95rem; padding:0 10px; outline:none;">
                        </div>
                    </div>

                    <div>
                        <label style="display:block; color:#cbd5e1; font-size:0.85rem; font-weight:800; margin-bottom:4px;">نوع المستند</label>
                        <select id="new-template-type" style="width:100%; height:44px; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:0.95rem; padding:0 10px; outline:none;">
                            <option value="bank_transfer">🏦 إشعار تحويل بنكي (تطبيق جوال)</option>
                            <option value="cheque">✍️ شيك مصرفي (ورقي / إلكتروني)</option>
                            <option value="deposit_slip">📄 قسيمة إيداع نقدي بنكي</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; color:#cbd5e1; font-size:0.85rem; font-weight:800; margin-bottom:4px;">
                            شرح معالم وقواعد الفحص الخاصة بهذا النموذج (تعليمات واضحة للذكاء الاصطناعي) *
                        </label>
                        <textarea id="new-template-rules" rows="4" placeholder="مثال:&#10;- لون الإشعار أزرق في أعلاه شعار مصرف السلام.&#10;- رقم العملية يتكون من 10 أرقام ويبدأ بـ 404.&#10;- المبلغ يظهر في وسط الإشعار بجانبه SDG.&#10;- اسم المستفيد المعتمد لحسابنا هو مؤسسة السجانة ورقم الحساب 887766." style="width:100%; background:#1e293b; border:1.5px solid rgba(168,85,247,0.4); border-radius:10px; color:#fff; font-size:0.95rem; font-family:'Tajawal',sans-serif; padding:12px; outline:none; resize:vertical;"></textarea>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:5px;">
                        <button type="button" onclick="FinancialOCR.switchTrainerTab('templates')" style="background:rgba(255,255,255,0.08); border:none; color:#cbd5e1; padding:8px 18px; border-radius:8px; font-weight:700; cursor:pointer;">إلغاء</button>
                        <button type="button" onclick="FinancialOCR.submitNewTemplate()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; padding:10px 24px; border-radius:8px; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.3); display:flex; align-items:center; gap:8px;">
                            <span>💾</span> حفظ وتعليم النموذج في عقل الذكاء الاصطناعي
                        </button>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: General Guidelines -->
            <div id="trainer-tab-guidelines" style="display:none; flex-direction:column; gap:14px;">
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:14px; padding:16px;">
                    <label style="display:block; color:#f8fafc; font-size:0.95rem; font-weight:800; margin-bottom:6px;">
                        💡 قواعد عامة لجميع الإشعارات (تُرسل دائماً لتوجيه الذكاء الاصطناعي):
                    </label>
                    <textarea id="financial-ocr-custom-guidelines" rows="6" placeholder="اكتب هنا أي تعليمات إضافية، مثال:&#10;- رقم حساب شركتنا في بنكك هو 1234567 باسم مؤسسة السجانة.&#10;- إذا لم يظهر اسم البنك بوضوح وكان رقم المعاملة يبدأ بـ 28 فهو بنك الخرطوم.&#10;- شيكات بنك العمال الوطني يتم صرفها في فرع بحري." style="width:100%; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:10px; color:#fff; font-size:0.95rem; font-family:'Tajawal',sans-serif; padding:12px; resize:vertical; outline:none;"></textarea>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                        <span id="financial-ocr-save-status" style="color:#10b981; font-size:0.85rem; font-weight:700; display:none;">✓ تم حفظ القواعد في الذاكرة بنجاح</span>
                        <button type="button" onclick="FinancialOCR.saveCustomGuidelines()" style="background:linear-gradient(135deg, #8b5cf6, #6d28d9); border:none; color:#fff; padding:10px 22px; border-radius:8px; font-weight:800; cursor:pointer; font-size:0.95rem; margin-right:auto;">
                            💾 حفظ القواعد العامة
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div style="padding:14px 24px; background:rgba(15,23,42,0.95); border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center;">
            <span style="color:#94a3b8; font-size:0.8rem;">يتم تطبيق جميع هذه النماذج تلقائياً أثناء فحص أي إشعار أو شيك.</span>
            <button type="button" onclick="FinancialOCR.closeTrainer()" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:8px 24px; border-radius:8px; font-weight:800; cursor:pointer;">إغلاق</button>
        </div>

    </div>
</div>

<!-- Gemini API Key Setup Modal -->
<div id="financial-ocr-key-modal" style="display:none; position:fixed !important; inset:0 !important; background:rgba(0,0,0,0.92) !important; backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:2147483647 !important; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
    <div style="background:#0f172a; border:2px solid #38bdf8; border-radius:20px; width:100%; max-width:620px; box-shadow:0 25px 60px rgba(0,0,0,0.9), 0 0 40px rgba(56,189,248,0.3); overflow:hidden;">
        <!-- Header -->
        <div style="padding:18px 24px; background:linear-gradient(135deg, rgba(14,165,233,0.25), rgba(15,23,42,0.9)); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.8rem;">🔑</span>
                <div>
                    <h3 style="margin:0; color:#fff; font-size:1.25rem; font-weight:900;">تهيئة مفتاح الذكاء الاصطناعي (Gemini AI Key)</h3>
                    <p style="margin:2px 0 0 0; color:#7dd3fc; font-size:0.85rem;">مطلوب لتفعيل الفحص المباشر لإشعارات بنكك والشيكات والفواتير</p>
                </div>
            </div>
            <button type="button" onclick="FinancialOCR.closeKeyModal()" style="background:transparent; border:none; color:#94a3b8; font-size:1.6rem; cursor:pointer;">✕</button>
        </div>
        <!-- Body -->
        <div style="padding:22px 24px; display:flex; flex-direction:column; gap:16px;">
            <div style="background:rgba(56,189,248,0.08); border:1px solid rgba(56,189,248,0.25); border-radius:12px; padding:14px; font-size:0.9rem; color:#cbd5e1; line-height:1.6;">
                للاستفادة من ميزة الرؤية الحاسوبية بدقة كاملة، الصق مفتاحك من Google AI Studio أدناه. المفتاح <strong>مجاني 100%</strong> ويتم استخراجه بنقرة زر واحدة:
                <div style="margin-top:8px;">
                    <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:6px; color:#38bdf8; font-weight:800; text-decoration:underline;">
                        <span>🔗</span> اضغط هنا لفتح صفحة الحصول على المفتاح المجاني (Google AI Studio) ↗
                    </a>
                </div>
            </div>

            <div>
                <label style="display:block; color:#f8fafc; font-size:0.9rem; font-weight:800; margin-bottom:6px;">
                    أدخل مفتاح Gemini API Key (يبدأ عادة بـ AIzaSy...):
                </label>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="gemini-api-key-input" placeholder="AIzaSy..." style="flex:1; height:46px; background:#1e293b; border:1.5px solid #38bdf8; border-radius:10px; color:#fff; font-size:1rem; font-family:'Consolas',monospace; padding:0 12px; outline:none; text-align:left; direction:ltr;">
                </div>
                <span id="gemini-key-save-status" style="color:#10b981; font-size:0.85rem; font-weight:700; display:none; margin-top:6px;">✓ تم حفظ وتفعيل المفتاح بنجاح!</span>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px; margin-top:5px;">
                <button type="button" onclick="FinancialOCR.saveAndActivateKey()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; padding:12px 20px; border-radius:10px; font-size:1.05rem; font-weight:900; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.35); display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span>💾</span> حفظ وتفعيل المسح المباشر الآن
                </button>
                <button type="button" onclick="FinancialOCR.useSimulationMode()" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.18); color:#fbbf24; padding:10px 20px; border-radius:10px; font-size:0.95rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span>🧪</span> تجربة المسح بالوضع الافتراضي (Simulation Demo)
                </button>
            </div>
        </div>
    </div>
<!-- WhatsApp AI Ingest Modal (النظام الهجين المتكامل لاستيراد إشعارات الواتساب) -->
<div id="financial-ocr-whatsapp-modal" style="display:none; position:fixed !important; inset:0 !important; background:rgba(0,0,0,0.92) !important; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); z-index:2147483647 !important; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
    <div style="background:#0f172a; border:2px solid #25D366; border-radius:20px; width:100%; max-width:680px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.9), 0 0 45px rgba(37,211,102,0.3); overflow:hidden;">
        
        <!-- Header -->
        <div style="padding:16px 22px; background:linear-gradient(135deg, rgba(37,211,102,0.25), rgba(15,23,42,0.95)); border-bottom:1.5px solid rgba(37,211,102,0.3); display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg,#25D366,#128C7E); display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#fff; box-shadow:0 0 15px rgba(37,211,102,0.5);">
                    💬
                </div>
                <div>
                    <h3 style="margin:0; color:#fff; font-size:1.25rem; font-weight:900; letter-spacing:0.3px;">استيراد إشعارات الواتساب بالذكاء الاصطناعي (WhatsApp AI)</h3>
                    <p style="margin:2px 0 0 0; color:#86efac; font-size:0.82rem; font-weight:700;">اسحب وأفلت صور الإشعارات دفعة واحدة أو الصقها بـ Ctrl+V</p>
                </div>
            </div>
            <button type="button" onclick="FinancialOCR.closeWhatsAppIngest()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#94a3b8; width:34px; height:34px; border-radius:8px; font-size:1.3rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.color='#ef4444'; this.style.borderColor='#ef4444';" onmouseout="this.style.color='#94a3b8'; this.style.borderColor='rgba(255,255,255,0.15)';" title="إغلاق النافذة">✕</button>
        </div>

        <!-- Body -->
        <div style="padding:18px 22px; overflow-y:auto; display:flex; flex-direction:column; gap:14px; flex:1;">
            
            <!-- Drag & Drop Zone -->
            <div id="whatsapp-dropzone" onclick="document.getElementById('whatsapp-file-input').click()" style="border:2.5px dashed #25D366; background:rgba(37,211,102,0.06); border-radius:16px; padding:22px 16px; text-align:center; cursor:pointer; transition:all 0.25s ease; position:relative; overflow:hidden;" ondragover="event.preventDefault(); this.style.background='rgba(37,211,102,0.18)'; this.style.borderColor='#4ade80';" ondragleave="this.style.background='rgba(37,211,102,0.06)'; this.style.borderColor='#25D366';" ondrop="FinancialOCR.handleWhatsAppDrop(event)">
                <input type="file" id="whatsapp-file-input" multiple accept="image/*" style="display:none;" onchange="FinancialOCR.handleWhatsAppFiles(this.files)">
                
                <div style="font-size:2.4rem; margin-bottom:6px; filter:drop-shadow(0 0 12px rgba(37,211,102,0.6));">📥</div>
                <div style="color:#ffffff; font-weight:900; font-size:1.15rem; margin-bottom:4px;">
                    اسحب وأفلت إشعارات الواتساب هنا (إشعار واحد أو حتى 30 إشعاراً)
                </div>
                <div style="color:#86efac; font-size:0.85rem; font-weight:800;">
                    أو اضغط <kbd style="background:#1e293b; color:#fbbf24; padding:2px 8px; border-radius:6px; border:1px solid rgba(255,255,255,0.2); font-family:monospace; font-size:0.9rem;">Ctrl + V</kbd> للصق السريع المتتابع من الواتساب
                </div>
                <div style="margin-top:8px; color:#94a3b8; font-size:0.75rem;">
                    (يدعم إشعارات بنكك، فوري، أوكاش، إشعارات بنك فيصل، والشيكات)
                </div>
            </div>

            <!-- Quick Action Toolbar -->
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; justify-content:space-between;">
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" onclick="FinancialOCR.pasteFromClipboard()" style="background:linear-gradient(135deg, #25D366, #128C7E); border:1.5px solid #4ade80; color:#ffffff; padding:8px 16px; border-radius:10px; font-weight:900; font-size:0.9rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 0 15px rgba(37,211,102,0.45); transition:all 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'" title="لصق صورة الإشعار المنسوخة من الحافظة مباشرة">
                        <span>📋</span> لصق الإشعار المنسوخ فوراً (Paste)
                    </button>
                    <button type="button" onclick="document.getElementById('whatsapp-file-input').click()" style="background:rgba(56,189,248,0.15); border:1.5px solid #38bdf8; color:#7dd3fc; padding:8px 14px; border-radius:10px; font-weight:800; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.28)'" onmouseout="this.style.background='rgba(56,189,248,0.15)'">
                        <span>📂</span> اختيار صور من الجهاز / التنزيلات
                    </button>
                    <button type="button" onclick="FinancialOCR.openWhatsAppWebPopup()" style="background:linear-gradient(135deg, rgba(37,211,102,0.22), rgba(18,140,126,0.22)); border:1.5px solid #25D366; color:#86efac; padding:8px 14px; border-radius:10px; font-weight:800; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.background='rgba(37,211,102,0.35)'" onmouseout="this.style.background='rgba(37,211,102,0.22)'" title="فتح واتساب ويب في نافذة جانبية فورية لسحب الإشعارات منها">
                        <span>🌐</span> فتح واتساب ويب في نافذة جانبية ↗
                    </button>
                </div>
                <button type="button" id="btn-clear-whatsapp-tray" onclick="FinancialOCR.clearWhatsAppTray()" style="display:none; background:rgba(239,68,68,0.12); border:1.5px solid #ef4444; color:#fca5a5; padding:8px 12px; border-radius:10px; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    <span>🧹</span> مسح القائمة
                </button>
            </div>

            <!-- Collected Receipts Container -->
            <div id="whatsapp-tray-wrapper" style="display:none; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.1); border-radius:14px; padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span id="whatsapp-tray-count-badge" style="color:#86efac; font-size:0.85rem; font-weight:900;">
                        🖼️ الإشعارات الجاهزة للمسح: ( 0 إشعار )
                    </span>
                    <span style="color:#94a3b8; font-size:0.75rem;">انقر على ✕ لحذف أي صورة</span>
                </div>
                <div id="whatsapp-tray-gallery" style="display:flex; gap:10px; overflow-x:auto; padding:6px 2px; scrollbar-width:thin;">
                    <!-- Dynamically populated thumbnails -->
                </div>
            </div>

        </div>

        <!-- Footer / Action CTA -->
        <div style="padding:14px 22px; background:rgba(15,23,42,0.98); border-top:1.5px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <button type="button" onclick="FinancialOCR.closeWhatsAppIngest()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); color:#cbd5e1; padding:10px 18px; border-radius:10px; font-weight:800; font-size:0.9rem; cursor:pointer;">
                إلغاء
            </button>
            <button type="button" id="btn-start-whatsapp-scan" onclick="FinancialOCR.startWhatsAppScan()" disabled style="flex:1; background:linear-gradient(135deg, #10b981, #059669); opacity:0.5; cursor:not-allowed; border:none; color:#ffffff; padding:12px 22px; border-radius:10px; font-weight:900; font-size:1.05rem; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 18px rgba(16,185,129,0.35); transition:all 0.2s;">
                <span>🚀</span> <span id="btn-start-whatsapp-scan-text">ابدأ المسح وتفريغ البيانات في السند (0 إشعار)</span>
            </button>
        </div>

    </div>
</div>

<!-- Interactive High-Resolution Image Zoom & Pan Lightbox Modal -->
<div id="financial-ocr-zoom-modal" style="display:none; position:fixed !important; inset:0 !important; top:0 !important; left:0 !important; right:0 !important; bottom:0 !important; width:100vw !important; height:100vh !important; background:rgba(2,6,23,0.97) !important; backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px); z-index:2147483647 !important; flex-direction:column; font-family:'Tajawal',sans-serif; direction:rtl; user-select:none; box-sizing:border-box;">
    
    <!-- Top Floating Toolbar -->
    <div style="padding:12px 24px; background:linear-gradient(180deg, rgba(15,23,42,0.95), rgba(15,23,42,0.7)); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; z-index:10;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:1.6rem; filter:drop-shadow(0 0 10px #38bdf8);">🔍</span>
            <div>
                <h3 id="ocr-zoom-title" style="margin:0; color:#fff; font-size:1.2rem; font-weight:900;">تدقيق وتكبير صورة الشيك / الإشعار</h3>
                <span id="ocr-zoom-meta" style="color:#94a3b8; font-size:0.85rem; font-weight:700;">المستند 1 من 1</span>
            </div>
        </div>

        <!-- Zoom Controls -->
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <button type="button" onclick="FinancialOCR.zoomIn()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:6px 14px; border-radius:8px; font-weight:800; font-size:1rem; cursor:pointer;" title="تكبير (+)">🔍 +</button>
            <button type="button" onclick="FinancialOCR.zoomOut()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:6px 14px; border-radius:8px; font-weight:800; font-size:1rem; cursor:pointer;" title="تصغير (-)">🔍 -</button>
            <span id="ocr-zoom-scale-badge" style="background:rgba(56,189,248,0.22); border:1px solid #38bdf8; color:#7dd3fc; padding:5px 12px; border-radius:8px; font-weight:900; font-size:0.92rem; font-family:'Consolas',monospace;" title="نسبة التكبير الحالية">100%</span>
            <button type="button" onclick="FinancialOCR.zoomReset()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:6px 12px; border-radius:8px; font-weight:800; font-size:0.9rem; cursor:pointer;" title="إعادة ضبط الحجم (100%)">100%</button>
            <button type="button" onclick="FinancialOCR.zoomFit()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:6px 12px; border-radius:8px; font-weight:800; font-size:0.9rem; cursor:pointer;" title="ملاءمة الشاشة">⊡ ملاءمة</button>
            <button type="button" onclick="FinancialOCR.rotateImage()" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#7dd3fc; padding:6px 14px; border-radius:8px; font-weight:800; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; gap:5px;" title="تدوير الصورة 90 درجة">🔄 تدوير 90°</button>
            <button type="button" id="btn-toggle-contrast" onclick="FinancialOCR.toggleContrastFilter()" style="background:rgba(168,85,247,0.15); border:1px solid #c084fc; color:#d8b4fe; padding:6px 14px; border-radius:8px; font-weight:800; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; gap:5px;" title="توضيح خط اليد والأختام">🌓 تباين عالي</button>
            
            <button type="button" onclick="FinancialOCR.closeZoomViewer()" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; padding:6px 16px; border-radius:8px; font-weight:900; font-size:1.1rem; cursor:pointer; margin-right:6px;" title="إغلاق المعاينة (Esc)">✕</button>
        </div>
    </div>

    <!-- Viewport Container for Panning & Zooming -->
    <div id="ocr-zoom-viewport" style="flex:1; width:100%; height:100%; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; cursor:grab;">
        <img id="ocr-zoom-img" src="" alt="معاينة المستند المالي المكبر" style="max-width:90%; max-height:90%; object-fit:contain; transform-origin:center center; transition:transform 0.05s ease-out; pointer-events:auto; filter:drop-shadow(0 15px 35px rgba(0,0,0,0.8));">
    </div>

    <!-- Bottom Verification Helper Bar -->
    <div style="padding:10px 24px; background:linear-gradient(0deg, rgba(15,23,42,0.95), rgba(15,23,42,0.7)); border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; z-index:10;">
        <span style="color:#94a3b8; font-size:0.85rem; display:flex; align-items:center; gap:6px;">
            <span>💡</span> يمكنك استخدام <strong>عجلة الفأرة للتكبير والتصغير</strong>، وسحب الصورة بالماوس لمعاينة التوقيع والمبلغ والأرقام بدقة.
        </span>
        <button type="button" onclick="FinancialOCR.closeZoomViewer()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; padding:8px 24px; border-radius:8px; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.4); display:flex; align-items:center; gap:6px;">
            <span>✓</span> تم التحقق من الشيك
        </button>
    </div>

</div>

<!-- Central Batch Audit & Warning Modal (الرسائل التحذيرية التلخيصية الكبرى في منتصف الشاشة) -->
<div id="financial-ocr-batch-alert-modal" style="display:none; position:fixed; inset:0; background:rgba(2,6,23,0.85); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:2147483647; align-items:center; justify-content:center; padding:16px; font-family:'Tajawal',sans-serif; direction:rtl; box-sizing:border-box;">
    <div style="width:100%; max-width:620px; background:linear-gradient(135deg, rgba(15,23,42,0.98), rgba(30,41,59,0.98)); border:2px solid rgba(239,68,68,0.6); border-radius:16px; box-shadow:0 25px 60px rgba(0,0,0,0.8), 0 0 35px rgba(239,68,68,0.3); padding:24px; color:#fff; display:flex; flex-direction:column; gap:16px; position:relative;">
        
        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.8rem; animation:ocrEmergencyGlow 0.8s infinite alternate;">⚠️</span>
                <div>
                    <h3 style="margin:0; font-size:1.3rem; font-weight:900; color:#f87171;">تقرير تدقيق وتنبيهات الدفعة المصرفية</h3>
                    <span style="color:#94a3b8; font-size:0.85rem; font-weight:700;">فحص محاسبي فوري لكافة السندات قبل الحفظ والاعتماد</span>
                </div>
            </div>
            <button type="button" onclick="FinancialOCR.closeBatchAlertModal()" style="background:rgba(255,255,255,0.08); border:none; color:#cbd5e1; width:34px; height:34px; border-radius:8px; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
        </div>

        <!-- Alert Items Container -->
        <div id="ocr-batch-alert-body" style="display:flex; flex-direction:column; gap:10px; max-height:360px; overflow-y:auto; padding:2px;">
            <!-- Injected dynamically via JS: counts of duplicate, unclear, and non-financial items -->
        </div>

        <!-- Guidance Message -->
        <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; font-size:0.84rem; color:#cbd5e1; line-height:1.4;">
            💡 <strong>توجيه محاسبي:</strong> يمكنك النقر على أي تنبيه أعلاه للانتقال فوراً للمستند وتصحيحه، أو استبعاد المستندات غير الصالحة، أو إعادة قراءتها بالذكاء الاصطناعي.
        </div>

        <!-- Actions -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:8px;">
            <button type="button" onclick="FinancialOCR.closeBatchAlertModal()" style="flex:1; height:42px; background:linear-gradient(135deg, #0284c7, #0369a1); border:none; color:#fff; border-radius:10px; font-size:0.95rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>🔍</span> تدقيق ومراجعة المستندات
            </button>
            <button type="button" onclick="FinancialOCR.rescanAllBatchItems(); FinancialOCR.closeBatchAlertModal();" style="flex:1; height:42px; background:linear-gradient(135deg, rgba(56,189,248,0.25), rgba(14,165,233,0.35)); border:1.5px solid #38bdf8; color:#7dd3fc; border-radius:10px; font-size:0.95rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <span>🔄</span> إعادة فحص الجميع
            </button>
        </div>

<!-- OCR Left Column Custom Properties Context Menu (قائمة خصائص الخط والمظهر للحقول المفرغة كليك يمين) -->
<div id="ocr-custom-props-menu" style="display:none; position:fixed; z-index:2147483647; width:330px; background:linear-gradient(135deg, rgba(15,23,42,0.98), rgba(30,41,59,0.98)); border:2px solid #38bdf8; border-radius:14px; box-shadow:0 15px 40px rgba(0,0,0,0.85), 0 0 25px rgba(56,189,248,0.35); padding:14px 16px; font-family:'Tajawal',sans-serif; direction:rtl; box-sizing:border-box; color:#fff; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); user-select:none;">
    
    <!-- Menu Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:8px; margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:1.2rem;">🎨</span>
            <strong id="ocr-prop-menu-title" style="font-size:0.96rem; font-weight:900; color:#38bdf8;">خصائص المظهر والخط</strong>
        </div>
        <button type="button" onclick="FinancialOCR.closePropsMenu()" style="background:rgba(239,68,68,0.2); border:1px solid #ef4444; color:#fca5a5; width:24px; height:24px; border-radius:6px; font-size:0.9rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="إغلاق">✕</button>
    </div>

    <!-- Active Field Indicator Badge -->
    <div style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); border-radius:6px; padding:4px 8px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; font-size:0.80rem;">
        <span style="color:#94a3b8;">الحقل المحدد:</span>
        <span id="ocr-prop-field-name-badge" style="color:#fef08a; font-weight:900;">المبلغ</span>
    </div>

    <!-- Section 1: حجم الخط (Font Size) -->
    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <label style="font-size:0.86rem; font-weight:800; color:#e2e8f0; margin:0;">حجم الخط:</label>
            <span id="ocr-prop-size-badge" style="font-size:0.86rem; font-weight:900; color:#38bdf8; font-family:'Consolas',monospace; background:rgba(15,23,42,0.8); padding:2px 8px; border-radius:6px; border:1px solid rgba(56,189,248,0.4);">5.60rem</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" onclick="FinancialOCR.stepPropFontSize(-0.1)" style="width:34px; height:32px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:6px; font-size:1.1rem; font-weight:900; cursor:pointer;" title="تصغير الخط (-)">−</button>
            <input type="range" id="ocr-prop-size-range" min="0.8" max="7.5" step="0.05" value="5.60" oninput="FinancialOCR.onPropSizeSlider(this.value)" style="flex:1; cursor:pointer; accent-color:#38bdf8;">
            <button type="button" onclick="FinancialOCR.stepPropFontSize(0.1)" style="width:34px; height:32px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:6px; font-size:1.1rem; font-weight:900; cursor:pointer;" title="تكبير الخط (+)">+</button>
        </div>
    </div>

    <!-- Section 2: لون الخط (Font Color) -->
    <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <label style="font-size:0.86rem; font-weight:800; color:#e2e8f0; margin:0;">لون الخط:</label>
            <div style="display:flex; align-items:center; gap:6px;">
                <span id="ocr-prop-color-code" style="font-size:0.82rem; font-weight:800; color:#cbd5e1; font-family:'Consolas',monospace;">#fef08a</span>
                <span id="ocr-prop-color-preview-circle" style="width:16px; height:16px; border-radius:50%; background:#fef08a; display:inline-block; border:1px solid #fff;"></span>
            </div>
        </div>
        <!-- Color Palette Quick Pick Swatches -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:4px; margin-top:2px;">
            <button type="button" onclick="FinancialOCR.setPropColor('#fef08a')" style="width:26px; height:26px; border-radius:50%; background:#fef08a; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="ذهبي ناصع"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#fbbf24')" style="width:26px; height:26px; border-radius:50%; background:#fbbf24; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="كهرماني"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#ffffff')" style="width:26px; height:26px; border-radius:50%; background:#ffffff; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="أبيض ناصع"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#38bdf8')" style="width:26px; height:26px; border-radius:50%; background:#38bdf8; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="سماوي نيون"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#34d399')" style="width:26px; height:26px; border-radius:50%; background:#34d399; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="زمردي أخضر"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#f87171')" style="width:26px; height:26px; border-radius:50%; background:#f87171; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="أحمر ساطع"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#c084fc')" style="width:26px; height:26px; border-radius:50%; background:#c084fc; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="بنفسجي"></button>
            <button type="button" onclick="FinancialOCR.setPropColor('#f472b6')" style="width:26px; height:26px; border-radius:50%; background:#f472b6; border:2px solid rgba(255,255,255,0.4); cursor:pointer; transition:transform 0.1s;" title="وردي"></button>
            <!-- Native Color Picker Input -->
            <label style="position:relative; width:26px; height:26px; border-radius:50%; overflow:hidden; border:2px solid rgba(255,255,255,0.6); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; background:conic-gradient(red, yellow, lime, aqua, blue, magenta, red);" title="لوحة ألوان مخصصة">
                <input type="color" id="ocr-prop-color-picker" onchange="FinancialOCR.setPropColor(this.value)" style="position:absolute; opacity:0; width:100%; height:100%; cursor:pointer;">
            </label>
        </div>
    </div>

    <!-- Section 3: الفاصلة الألفية (Thousands Separator) -->
    <div id="ocr-prop-thousands-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px 10px;">
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:1.05rem;">🔢</span>
            <span style="font-size:0.86rem; font-weight:800; color:#e2e8f0;">الفاصلة الألفية (،):</span>
        </div>
        <button type="button" id="ocr-prop-sep-btn" onclick="FinancialOCR.togglePropThousandsSep()" style="background:rgba(16,185,129,0.2); border:1.5px solid #10b981; color:#34d399; padding:4px 12px; border-radius:6px; font-weight:900; font-size:0.84rem; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.15s ease;">
            <span id="ocr-prop-sep-icon">✓</span> <span id="ocr-prop-sep-text">مفعل (مثال: 20,000)</span>
        </button>
    </div>

    <!-- Section 4: الخانات العشرية (Decimals) -->
    <div id="ocr-prop-decimals-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px 10px;">
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:1.05rem;">✨</span>
            <span style="font-size:0.86rem; font-weight:800; color:#e2e8f0;">الخانات العشرية:</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <button type="button" id="ocr-prop-dec-0-btn" onclick="FinancialOCR.setPropDecimals(0)" style="background:#1e293b; border:1.5px solid #38bdf8; color:#7dd3fc; border-radius:6px; padding:3px 10px; cursor:pointer; font-weight:900; font-size:0.82rem;">بدون (.0)</button>
            <button type="button" id="ocr-prop-dec-2-btn" onclick="FinancialOCR.setPropDecimals(2)" style="background:#1e293b; border:1px solid rgba(255,255,255,0.2); color:#cbd5e1; border-radius:6px; padding:3px 10px; cursor:pointer; font-weight:800; font-size:0.82rem;">خانتين (.00)</button>
        </div>
    </div>

    <!-- Section 5: سمك الخط (Font Weight) -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:6px 10px;">
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:1.0rem;">💪</span>
            <span style="font-size:0.86rem; font-weight:800; color:#e2e8f0;">سمك الخط:</span>
        </div>
        <div style="display:flex; align-items:center; gap:4px;" id="ocr-prop-weight-group">
            <button type="button" id="ocr-prop-weight-600" onclick="FinancialOCR.setPropWeight('600')" style="background:#1e293b; border:1px solid rgba(255,255,255,0.2); color:#cbd5e1; border-radius:5px; padding:2px 8px; cursor:pointer; font-size:0.78rem;">عادي</button>
            <button type="button" id="ocr-prop-weight-800" onclick="FinancialOCR.setPropWeight('800')" style="background:#1e293b; border:1px solid rgba(255,255,255,0.2); color:#cbd5e1; border-radius:5px; padding:2px 8px; cursor:pointer; font-size:0.78rem;">عريض</button>
            <button type="button" id="ocr-prop-weight-900" onclick="FinancialOCR.setPropWeight('900')" style="background:rgba(56,189,248,0.25); border:1.5px solid #38bdf8; color:#7dd3fc; border-radius:5px; padding:2px 8px; cursor:pointer; font-weight:900; font-size:0.78rem;">ضخم</button>
        </div>
    </div>

    <!-- Section 6: الأزرار الرئيسية (حفظ دائم واستعادة) -->
    <div style="display:flex; flex-direction:column; gap:8px; margin-top:8px; border-top:1px solid rgba(255,255,255,0.1); padding-top:10px;">
        <button type="button" id="ocr-prop-save-btn" onclick="FinancialOCR.saveFieldPreferences()" style="height:40px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); border:1.5px solid #34d399; color:#fff; border-radius:8px; font-size:0.95rem; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 4px 15px rgba(16,185,129,0.4); transition:all 0.2s;">
            <span>💾</span> <span id="ocr-prop-save-text">حفظ الخصائص وتثبيتها</span>
        </button>
        <button type="button" onclick="FinancialOCR.resetCurrentFieldProp()" style="height:30px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.18); color:#cbd5e1; border-radius:6px; font-size:0.78rem; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px;">
            <span>↺</span> <span>استعادة الحجم واللون الافتراضي</span>
        </button>
    </div>

</div>

<script>
// Global Fallback Bridge to ensure openTrainer always works from any screen or timing
window.openFinancialOCRTrainer = function() {
    if (window.FinancialOCR && typeof window.FinancialOCR.openTrainer === 'function') {
        window.FinancialOCR.openTrainer();
    } else {
        var m = document.getElementById('financial-ocr-trainer-modal');
        if (m) m.style.display = 'flex';
    }
};

if (!window.FinancialOCR) {
    window.FinancialOCR = {
        openTrainer: function() { window.openFinancialOCRTrainer(); },
        openScanner: function(opts) {
            var fi = document.getElementById('financial-ocr-file-input');
            if (fi) fi.click();
        },
        openKeyModal: function() {
            var m = document.getElementById('financial-ocr-key-modal');
            if (m) m.style.display = 'flex';
        },
        closeTrainer: function() {
            var m = document.getElementById('financial-ocr-trainer-modal');
            if (m) m.style.display = 'none';
        },
        closeKeyModal: function() {
            var m = document.getElementById('financial-ocr-key-modal');
            if (m) m.style.display = 'none';
        },
        closeReviewModal: function() {
            var m = document.getElementById('financial-ocr-review-modal');
            if (m) m.style.display = 'none';
        }
    };
}
</script>

<style>
@keyframes ocrSpinRing { to { transform: rotate(360deg); } }
@keyframes ocrSpinRingReverse { to { transform: rotate(-360deg); } }
@keyframes pulseScale { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.15); } }
@keyframes ocrEmergencyGlow {
    0% { transform: scale(1); box-shadow: 0 0 6px rgba(239, 68, 68, 0.7); filter: brightness(1); }
    100% { transform: scale(1.08); box-shadow: 0 0 16px rgba(239, 68, 68, 1), 0 0 24px rgba(220, 38, 38, 0.85); filter: brightness(1.2); }
}
@keyframes ocrWarningPulse {
    0% { transform: scale(1); box-shadow: 0 0 5px rgba(245, 158, 11, 0.6); }
    100% { transform: scale(1.06); box-shadow: 0 0 14px rgba(245, 158, 11, 0.95); }
}
.ocr-dashboard-col {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease !important;
}
.ocr-dashboard-col:hover {
    transform: translateY(-3px) scale(1.004) !important;
    border-color: rgba(56, 189, 248, 0.7) !important;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.7), 0 0 25px rgba(56, 189, 248, 0.25) !important;
    z-index: 10;
}
.ocr-field-box {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
    flex-shrink: 0 !important;
}
.ocr-field-box:hover, .ocr-field-box:focus-within {
    transform: translateY(-1.5px) !important;
    filter: brightness(1.08) !important;
}
/* Calibrated Date Digits (-3 degrees: 1.25rem, height 40px, centered in the middle) */
#ocr-field-date,
#ocr-field-date::-webkit-datetime-edit,
#ocr-field-date::-webkit-datetime-edit-fields-wrapper,
#ocr-field-date::-webkit-date-and-time-value {
    display: inline-flex !important;
    justify-content: center !important;
    text-align: center !important;
    width: 100% !important;
    margin: 0 auto !important;
    font-size: 1.25rem !important;
    font-weight: 900 !important;
    height: 40px !important;
    line-height: 40px !important;
}
#ocr-field-date::-webkit-calendar-picker-indicator {
    transform: scale(1.1) !important;
    cursor: pointer !important;
}

/* Operation / Ref Number (-2 degrees: 1.55rem, height 40px, centered in the middle) */
#ocr-field-ref-no {
    font-size: 1.55rem !important;
    font-weight: 900 !important;
    height: 40px !important;
    text-align: center !important;
    direction: ltr !important;
    margin: 0 auto !important;
}

/* Golden Amount styling (-2 degrees: 2.30rem, height 44px) */
#ocr-field-amount {
    color: #fef08a !important;
    background: #000000 !important;
    border: 2.5px solid #fbbf24 !important;
    font-weight: 900 !important;
    font-size: 2.30rem !important;
    height: 44px !important;
    text-shadow: 0 0 14px rgba(251, 191, 36, 0.5) !important;
    font-family: 'Bahnschrift Condensed', 'Bahnschrift', 'Segoe UI', 'Tajawal', sans-serif !important;
    font-variant-numeric: tabular-nums !important;
    letter-spacing: -0.5px !important;
    text-align: center !important;
    direction: ltr !important;
}
#ocr-field-amount::placeholder {
    color: rgba(254, 240, 138, 0.35) !important;
}

/* Sender Account (من: -1 degree: 1.06rem, height 36px, centered in the middle) */
#ocr-field-sender-acc {
    font-size: 1.06rem !important;
    height: 36px !important;
    text-align: center !important;
    direction: ltr !important;
    margin: 0 auto !important;
}

/* Receiver Account and Name (إلى: -1 degree: 1.06rem & 0.94rem, height 36px, centered in the middle) */
#ocr-field-receiver-acc {
    font-size: 1.06rem !important;
    height: 36px !important;
    text-align: center !important;
    direction: ltr !important;
    margin: 0 auto !important;
}
#ocr-field-receiver {
    font-size: 0.94rem !important;
    height: 36px !important;
    text-align: center !important;
    margin: 0 auto !important;
}
.ocr-responsive-grid {
    grid-template-columns: minmax(0, 1.98fr) minmax(0, 1.255fr) minmax(0, 0.765fr) !important;
    height: 100% !important;
    max-height: 100% !important;
    box-sizing: border-box !important;
    overflow-x: auto !important;
}

/* Total Viewport Domination: When OCR Modal is active, HIDE EVERYTHING else in the page */
html.ocr-modal-active, body.ocr-modal-active {
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    background: #020617 !important;
}

body.ocr-modal-active > *:not(#financial-ocr-review-modal):not(#financial-ocr-loading):not(#financial-ocr-zoom-modal):not(#financial-ocr-key-modal):not(#financial-ocr-trainer-modal):not(#financial-ocr-whatsapp-modal):not(#financial-ocr-batch-alert-modal):not(#financial-ocr-file-input):not(.ocr-toast) {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

#financial-ocr-zoom-modal,
#financial-ocr-batch-alert-modal,
#financial-ocr-trainer-modal,
#financial-ocr-whatsapp-modal,
#financial-ocr-key-modal {
    z-index: 2147483647 !important;
}
</style>
