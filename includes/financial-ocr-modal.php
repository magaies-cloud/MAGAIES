<?php
// =============================================================
// MAGAIEES ERP - Unified Financial OCR Scanner Modal & Overlay
// واجهة واستوديو تدريب مسح إشعارات التحويل والشيكات بالذكاء الاصطناعي
// =============================================================
?>
<!-- Hidden File Input for Camera / Image Upload -->
<input type="file" id="financial-ocr-file-input" accept="image/*" capture="environment" style="display:none;" onchange="FinancialOCR.handleFileSelected(this)">

<!-- OCR Scanning Loading Overlay -->
<div id="financial-ocr-loading" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.94); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); z-index:9999999; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-family:'Tajawal',sans-serif; direction:rtl;">
    <div style="position:relative; width:90px; height:90px; margin-bottom:24px;">
        <div style="position:absolute; inset:0; border:4px solid rgba(56,189,248,0.15); border-top-color:#38bdf8; border-radius:50%; animation:ocrSpinRing 1s linear infinite;"></div>
        <div style="position:absolute; inset:10px; border:4px solid rgba(16,185,129,0.15); border-bottom-color:#10b981; border-radius:50%; animation:ocrSpinRingReverse 1.5s linear infinite;"></div>
        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:2rem; animation:pulseScale 1.5s ease-in-out infinite;">📷</div>
    </div>
    <h2 style="margin:0 0 10px 0; font-size:1.5rem; font-weight:900; background:linear-gradient(135deg, #38bdf8, #34d399); -webkit-background-clip:text; -webkit-text-fill-color:transparent; text-shadow:0 0 20px rgba(56,189,248,0.4);">جاري فحص الإشعار / الشيك بالرؤية الحاسوبية... 🔍</h2>
    <p id="financial-ocr-loading-msg" style="margin:0; color:#94a3b8; font-size:1rem; font-weight:600;">يتم الآن قراءة رقم العملية، المبلغ، اسم البنك، والتفاصيل المحاسبية عبر Gemini AI</p>
</div>

<!-- OCR Review & Confirmation Modal -->
<div id="financial-ocr-review-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); z-index:9999998; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
    <div style="background:#0f172a; border:2px solid rgba(56,189,248,0.5); border-radius:20px; width:100%; max-width:920px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 25px 60px rgba(0,0,0,0.8), 0 0 40px rgba(56,189,248,0.25); overflow:hidden;">
        
        <!-- Header -->
        <div style="padding:16px 24px; background:linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,41,59,0.95)); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div id="ocr-doc-badge" style="background:rgba(16,185,129,0.2); border:1px solid #10b981; color:#34d399; padding:6px 14px; border-radius:20px; font-size:0.95rem; font-weight:900; display:flex; align-items:center; gap:6px;">
                    <span>🏦</span> <span id="ocr-doc-badge-text">إشعار تحويل بنكي</span>
                </div>
                <div>
                    <h3 style="margin:0; color:#fff; font-size:1.25rem; font-weight:900;">مراجعة وتأكيد بيانات المستند المالي</h3>
                    <p id="ocr-doc-summary" style="margin:2px 0 0 0; color:#94a3b8; font-size:0.85rem;">تأكد من صحة البيانات المستخرجة أو عدلها قبل التفريغ المباشر</p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <button type="button" onclick="FinancialOCR.openKeyModal()" style="background:rgba(56,189,248,0.15); border:1px solid #38bdf8; color:#7dd3fc; padding:6px 12px; border-radius:10px; font-size:0.85rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.2s;" title="تهيئة مفتاح Gemini API Key">
                    <span>🔑</span> إعداد المفتاح
                </button>
                <button type="button" onclick="FinancialOCR.openTrainer()" style="background:rgba(168,85,247,0.15); border:1px solid #c084fc; color:#d8b4fe; padding:6px 12px; border-radius:10px; font-size:0.85rem; font-weight:800; cursor:pointer; display:flex; align-items:center; gap:6px; transition:0.2s;" title="شرح وتدريب النماذج للذكاء الاصطناعي">
                    <span>🧠</span> تدريب النماذج
                </button>
                <button type="button" onclick="FinancialOCR.closeReviewModal()" style="background:transparent; border:none; color:#94a3b8; font-size:1.6rem; cursor:pointer; padding:2px 8px; line-height:1;">✕</button>
            </div>
        </div>

        <!-- Body -->
        <div style="padding:20px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:16px;">
            
            <div style="display:grid; grid-template-columns: 280px 1fr; gap:18px; align-items:start;" class="ocr-responsive-grid">
                
                <!-- Scanned Image Preview -->
                <div style="background:rgba(0,0,0,0.4); border:1px dashed rgba(255,255,255,0.2); border-radius:14px; padding:10px; display:flex; flex-direction:column; align-items:center; gap:8px; position:relative;">
                    <span style="font-size:0.8rem; color:#94a3b8; font-weight:700;">صورة الإشعار / الشيك الممسوح</span>
                    <div style="width:100%; height:260px; border-radius:10px; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center;">
                        <img id="ocr-preview-img" src="" alt="المستند المالي" style="max-width:100%; max-height:100%; object-fit:contain; cursor:pointer;" onclick="FinancialOCR.zoomImage(this.src)" title="انقر لتكبير الصورة">
                    </div>
                    <button type="button" onclick="FinancialOCR.triggerUploadAgain()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; font-size:0.8rem; padding:6px 12px; border-radius:8px; cursor:pointer; width:100%; font-weight:700;">🔄 إعادة التقاط الصورة</button>
                </div>

                <!-- Form Fields Extracted -->
                <div style="display:flex; flex-direction:column; gap:12px;">
                    
                    <!-- Top Row: Document Type & Amount -->
                    <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:12px;">
                        <div>
                            <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">نوع المستند المستخرج</label>
                            <select id="ocr-field-doc-type" onchange="FinancialOCR.handleTypeChange(this.value)" style="width:100%; height:50px; background:#1e293b; border:1.5px solid #38bdf8; border-radius:10px; color:#fff; font-size:1rem; font-weight:800; padding:0 12px; outline:none;">
                                <option value="bank_transfer">🏦 إشعار تحويل بنكي (بنكك / فوري / أوكاش)</option>
                                <option value="cheque">✍️ شيك مصرفي ورقي / إلكتروني</option>
                                <option value="deposit_slip">📄 قسيمة إيداع نقدي بنكي</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; color:#fbbf24; font-size:0.85rem; font-weight:800; margin-bottom:4px;">المبلغ المستخرج (جنيه سوداني / عملة) *</label>
                            <div style="position:relative;">
                                <input type="number" id="ocr-field-amount" step="any" placeholder="0.00" style="width:100%; height:50px; background:rgba(251,191,36,0.1); border:2px solid #fbbf24; border-radius:10px; color:#fef08a; font-size:1.6rem; font-weight:900; padding:0 12px; font-family:'Consolas',monospace; text-align:center; outline:none;">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Bank Name & Reference/Cheque Number -->
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">اسم البنك المسحوب عليه / المستلم</label>
                            <input type="text" id="ocr-field-bank-name" placeholder="مثال: بنك الخرطوم، بنك فيصل، بنك أمدرمان" style="width:100%; height:46px; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:10px; color:#fff; font-size:1rem; font-weight:700; padding:0 12px; outline:none;">
                        </div>
                        <div>
                            <label id="ocr-label-ref-no" style="display:block; color:#38bdf8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">رقم العملية / رقم التحويلة *</label>
                            <input type="text" id="ocr-field-ref-no" placeholder="رقم المعاملة أو رقم الشيك" style="width:100%; height:46px; background:#1e293b; border:1.5px solid #38bdf8; border-radius:10px; color:#38bdf8; font-size:1.2rem; font-weight:900; padding:0 12px; font-family:'Consolas',monospace; text-align:center; outline:none;">
                        </div>
                    </div>

                    <!-- Row 3: Dates (Transfer Date & Due Date for Cheques) -->
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">تاريخ المعاملة / التحرير</label>
                            <input type="date" id="ocr-field-date" style="width:100%; height:46px; background:#1e293b; border:1px solid rgba(255,255,255,0.15); border-radius:10px; color:#fff; font-size:1rem; font-weight:700; padding:0 12px; outline:none;">
                        </div>
                        <div id="ocr-box-due-date">
                            <label style="display:block; color:#c084fc; font-size:0.85rem; font-weight:800; margin-bottom:4px;">تاريخ الاستحقاق (للشيكات فقط)</label>
                            <input type="date" id="ocr-field-due-date" style="width:100%; height:46px; background:#1e293b; border:1px solid rgba(168,85,247,0.4); border-radius:10px; color:#e9d5ff; font-size:1rem; font-weight:700; padding:0 12px; outline:none;">
                        </div>
                    </div>

                    <!-- Row 4: Sender / Receiver -->
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">الساحب / المحول (من)</label>
                            <input type="text" id="ocr-field-sender" placeholder="اسم المحول أو صاحب الشيك" style="width:100%; height:42px; background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#cbd5e1; font-size:0.95rem; font-weight:600; padding:0 10px; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">المستفيد / المستلم (إلى)</label>
                            <input type="text" id="ocr-field-receiver" placeholder="اسم المستلم أو المستفيد" style="width:100%; height:42px; background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#cbd5e1; font-size:0.95rem; font-weight:600; padding:0 10px; outline:none;">
                        </div>
                    </div>

                    <!-- Row 5: Notes & Statement -->
                    <div>
                        <label style="display:block; color:#94a3b8; font-size:0.85rem; font-weight:800; margin-bottom:4px;">بيان المعاملة / الملاحظة (سيتم إدراجه في السند)</label>
                        <input type="text" id="ocr-field-notes" placeholder="البيان التوضيحي للعملية" style="width:100%; height:42px; background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#fff; font-size:0.95rem; font-weight:600; padding:0 10px; outline:none;">
                    </div>

                </div>
            </div>

        </div>

        <!-- Footer Actions -->
        <div style="padding:14px 24px; background:rgba(15,23,42,0.95); border-top:1px solid rgba(255,255,255,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <button type="button" onclick="FinancialOCR.closeReviewModal()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#cbd5e1; padding:10px 20px; border-radius:10px; font-weight:800; font-size:0.95rem; cursor:pointer;">إلغاء</button>
            <div style="display:flex; gap:10px;">
                <button type="button" id="btn-apply-financial-ocr" onclick="FinancialOCR.applyToActiveScreen()" style="background:linear-gradient(135deg, #10b981, #059669); border:none; color:#fff; padding:10px 26px; border-radius:10px; font-size:1.1rem; font-weight:900; cursor:pointer; box-shadow:0 4px 20px rgba(16,185,129,0.4); display:flex; align-items:center; gap:8px;">
                    <span>✅ اعتماد وتفريغ في شاشة الدفع</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- AI Trainer & Interactive Knowledge Studio Modal -->
<div id="financial-ocr-trainer-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
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
<div id="financial-ocr-key-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999999; align-items:center; justify-content:center; padding:15px; font-family:'Tajawal',sans-serif; direction:rtl;">
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
@media (max-width: 768px) {
    .ocr-responsive-grid { grid-template-columns: 1fr !important; }
}
</style>
