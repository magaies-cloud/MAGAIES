<div id="long-press-hud" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:999999; pointer-events:none; background:rgba(15,23,42,0.96); border:3px solid #eab308; color:#fff; padding:25px 35px; border-radius:24px; box-shadow:0 25px 60px rgba(0,0,0,0.8); text-align:center; min-width:320px; max-width:90%; backdrop-filter:blur(12px);">
    <div id="hud-icon" style="font-size:3.5rem; margin-bottom:10px;">⏳</div>
    <div id="hud-title" style="font-size:1.25rem; font-weight:900; color:#fef08a; margin-bottom:6px;">جاري الحفظ والتثبيت...</div>
    <div id="hud-desc" style="font-size:0.9rem; opacity:0.9; margin-bottom:18px; line-height:1.4;">استمر بالضغط المتواصل لمدة 8 ثوانٍ لحفظ تصميم التقرير النهائي</div>
    <div style="background:rgba(255,255,255,0.12); height:14px; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,0.25); margin-bottom:12px;">
        <div id="hud-progress-bar" style="background:linear-gradient(90deg, #eab308, #10b981); height:100%; width:0%;"></div>
    </div>
    <div id="hud-timer" style="font-weight:900; font-size:1.15rem; color:#38bdf8;">8.0 ثانية متبقية</div>
</div>

<!-- Custom In-Page Save Confirmation Modal -->
<div id="save-confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(11,15,25,0.85); backdrop-filter:blur(10px); z-index:9999999; align-items:center; justify-content:center; direction:rtl;">
    <div style="background:#1e293b; border:2px solid #ca8a04; border-radius:20px; padding:26px 30px; width:460px; max-width:90%; text-align:center; color:#fff; box-shadow:0 25px 60px rgba(0,0,0,0.8);">
        <div id="confirm-modal-icon" style="font-size:3.5rem; margin-bottom:10px;">🔒</div>
        <h3 id="confirm-modal-title" style="margin:0 0 10px 0; font-size:1.3rem; color:#fef08a; font-weight:900;">تأكيد حفظ تصميم التقرير</h3>
        <p id="confirm-modal-body" style="font-size:0.95rem; opacity:0.9; line-height:1.6; margin-bottom:24px; color:#cbd5e1;">هل تريد حفظ وتثبيت هذا التصميم؟</p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button type="button" id="confirm-modal-ok-btn" style="flex:1; padding:12px 20px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; border-radius:12px; font-weight:900; font-size:1rem; cursor:pointer; box-shadow:0 4px 15px rgba(16,185,129,0.4);">
                🟢 موافق، حفظ الآن
            </button>
            <button type="button" id="confirm-modal-cancel-btn" style="flex:1; padding:12px 20px; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); color:#f87171; border-radius:12px; font-weight:900; font-size:1rem; cursor:pointer;">
                🔴 إلغاء
            </button>
        </div>
    </div>
</div>

<!-- Custom Glassmorphic Confirm Save Factory Reset Modal -->
<div id="reset-confirm-modal" class="reset-modal-overlay">
    <div class="reset-modal-container">
        <div class="reset-modal-icon">🔧</div>
        <h3 class="reset-modal-title">حفظ التصميم الحالي كـ ضبط مصنع</h3>
        <p class="reset-modal-desc">سيتم حفظ جميع إعدادات التصميم الحالية كنقطة ضبط المصنع. يمكنك استرجاعها لاحقاً بالضغط المطوّل على زر إعادة الضبط لمدة 8 ثوانٍ.</p>
        
        <div class="reset-modal-buttons">
            <button id="modal-cancel-btn" class="modal-btn cancel">
                ❌ إلغاء
            </button>
            <button id="modal-ok-btn" class="modal-btn ok" style="position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; min-width: 170px;">
                <span class="modal-ok-progress" style="position: absolute; right: 0; top: 0; bottom: 0; width: 0%; background: rgba(255, 255, 255, 0.3); transition: none; pointer-events: none;"></span>
                <span class="ok-btn-text" style="position: relative; z-index: 2;">🟢 موافق (اضغط 3 ثوانٍ)</span>
            </button>
        </div>
    </div>
</div>

