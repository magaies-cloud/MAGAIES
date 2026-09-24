/**
 * ═══════════════════════════════════════════════════════════════════════════
 *  Royal Promissory Dispatch Animation (المؤثر الحركي الملكي لإرسال الكمبيالة)
 *  - تحرير السند وكتابته بريشة مذهبة وختم رسمي معتمد
 *  - طي السند ودخوله داخل ظرف بنكي ملكي وإحكام ختم الشمع
 *  - تحليق الظرف وانطلاقه السلس عبر مسار هوائي رقمي بتقنية الواتساب
 *  - وصول واكتمال التسليم مع مؤثرات الاحتفال والتحقق الرسمي في منتصف الشاشة
 * ═══════════════════════════════════════════════════════════════════════════
 */

(function() {
    'use strict';

    function injectFlightStyles() {
        if (document.getElementById('promissory-flight-styles')) return;

        const style = document.createElement('style');
        style.id = 'promissory-flight-styles';
        style.textContent = `
            /* ═══ حاوية الخلفية الشاملة في منتصف الشاشة ═══ */
            .pfa-overlay {
                position: fixed;
                inset: 0;
                z-index: 1000000;
                display: flex;
                align-items: center;
                justify-content: center;
                background: radial-gradient(circle at center, rgba(11, 19, 43, 0.94) 0%, rgba(2, 6, 23, 0.98) 100%);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                opacity: 0;
                transition: opacity 0.35s ease;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                direction: rtl;
                overflow: hidden;
                pointer-events: auto;
            }
            .pfa-overlay.pfa-visible {
                opacity: 1;
            }

            /* جزيئات ونجوم خلفية متحركة */
            .pfa-stars-bg {
                position: absolute;
                inset: 0;
                pointer-events: none;
                background-image: 
                    radial-gradient(2px 2px at 30px 40px, rgba(212, 175, 55, 0.65), rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 160px 90px, rgba(37, 211, 102, 0.55), rgba(0,0,0,0)),
                    radial-gradient(1.5px 1.5px at 260px 190px, rgba(255, 255, 255, 0.8), rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 340px 50px, rgba(212, 175, 55, 0.5), rgba(0,0,0,0)),
                    radial-gradient(2px 2px at 470px 230px, rgba(37, 211, 102, 0.6), rgba(0,0,0,0));
                background-repeat: repeat;
                background-size: 500px 500px;
                animation: pfaStarDrift 35s linear infinite;
                opacity: 0.75;
            }
            @keyframes pfaStarDrift {
                from { background-position: 0 0; }
                to { background-position: 500px 500px; }
            }

            /* ═══ البطاقة المركزية الفاخرة ═══ */
            .pfa-modal-card {
                position: relative;
                width: 90vw;
                max-width: 530px;
                height: 500px;
                background: linear-gradient(165deg, rgba(15, 23, 42, 0.96) 0%, rgba(9, 14, 28, 0.99) 100%);
                border: 2px solid rgba(212, 175, 55, 0.5);
                border-radius: 24px;
                box-shadow: 
                    0 30px 80px rgba(0, 0, 0, 0.95),
                    0 0 50px rgba(212, 175, 55, 0.22),
                    inset 0 0 35px rgba(212, 175, 55, 0.08);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                padding: 22px 28px 18px 28px;
                box-sizing: border-box;
                transform: scale(0.92) translateY(20px);
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                overflow: hidden;
            }
            .pfa-overlay.pfa-visible .pfa-modal-card {
                transform: scale(1) translateY(0);
            }

            /* وميض ذهبي علوي */
            .pfa-modal-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 15%;
                right: 15%;
                height: 3px;
                background: linear-gradient(90deg, transparent, #f59e0b, #fbbf24, #f59e0b, transparent);
                box-shadow: 0 0 18px #f59e0b;
            }

            /* رأس البطاقة */
            .pfa-header {
                width: 100%;
                text-align: center;
            }
            .pfa-brand-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 4px 15px;
                background: rgba(212, 175, 55, 0.14);
                border: 1px solid rgba(212, 175, 55, 0.4);
                border-radius: 20px;
                color: #fbbf24;
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.3px;
                margin-bottom: 6px;
                box-shadow: 0 0 15px rgba(212, 175, 55, 0.15);
            }
            .pfa-title {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 800;
                color: #ffffff;
                text-shadow: 0 2px 10px rgba(0,0,0,0.7);
            }
            .pfa-subtitle {
                margin: 4px 0 0 0;
                font-size: 0.85rem;
                color: #94a3b8;
                font-weight: 500;
            }

            /* ═══ مسرح العرض الحركي الرئيسي (3D Stage) ═══ */
            .pfa-stage {
                position: relative;
                width: 100%;
                height: 280px;
                perspective: 1200px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            /* ── ورقة السند / الكمبيالة في المرحلة الأولى ── */
            .pfa-doc-container {
                position: absolute;
                width: 190px;
                height: 230px;
                top: 20px;
                z-index: 15;
                transition: all 0.75s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .pfa-document {
                position: relative;
                width: 100%;
                height: 100%;
                background: #fdfbf7;
                border: 2px solid #b45309;
                border-radius: 8px;
                box-shadow: 0 14px 35px rgba(0,0,0,0.65), 0 0 20px rgba(212, 175, 55, 0.3);
                display: flex;
                flex-direction: column;
                padding: 10px;
                box-sizing: border-box;
            }
            .pfa-doc-inner-border {
                position: absolute;
                inset: 4px;
                border: 1px dashed rgba(180, 83, 9, 0.45);
                border-radius: 5px;
                pointer-events: none;
            }
            .pfa-doc-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1.5px solid #d97706;
                padding-bottom: 4px;
                margin-bottom: 6px;
            }
            .pfa-doc-emblem {
                font-size: 1.15rem;
            }
            .pfa-doc-title {
                font-size: 0.68rem;
                font-weight: 800;
                color: #78350f;
                text-align: center;
            }
            .pfa-doc-number {
                font-size: 0.58rem;
                font-weight: 700;
                color: #b45309;
                font-family: monospace;
            }
            .pfa-doc-parties {
                background: rgba(254, 243, 199, 0.65);
                border-radius: 4px;
                padding: 4px 6px;
                margin-bottom: 6px;
                border: 1px solid rgba(217, 119, 6, 0.25);
            }
            .pfa-doc-party-row {
                display: flex;
                justify-content: space-between;
                font-size: 0.56rem;
                color: #451a03;
                margin-bottom: 2px;
            }
            .pfa-doc-party-val {
                font-weight: 800;
                color: #92400e;
                max-width: 105px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .pfa-doc-lines {
                display: flex;
                flex-direction: column;
                gap: 5px;
                margin-bottom: 8px;
            }
            .pfa-doc-line {
                height: 4px;
                background: #e2e8f0;
                border-radius: 2px;
                position: relative;
                overflow: hidden;
            }
            .pfa-doc-line::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 0;
                background: linear-gradient(90deg, #b45309, #d97706);
                transition: width 0.4s ease;
            }
            .pfa-doc-line.pfa-written::after {
                width: 100%;
            }

            .pfa-doc-amount-box {
                background: #0f172a;
                border: 1px solid #d97706;
                border-radius: 4px;
                padding: 3px 6px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 6px;
            }
            .pfa-doc-amount-label {
                font-size: 0.52rem;
                color: #cbd5e1;
            }
            .pfa-doc-amount-val {
                font-size: 0.68rem;
                font-weight: 900;
                color: #fbbf24;
                font-family: monospace;
            }

            .pfa-doc-seal {
                position: absolute;
                bottom: 12px;
                left: 12px;
                width: 38px;
                height: 38px;
                background: radial-gradient(circle, #dc2626 40%, #991b1b 100%);
                border: 2px solid #fef08a;
                border-radius: 50%;
                box-shadow: 0 4px 10px rgba(153, 27, 27, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 0.45rem;
                font-weight: 900;
                transform: scale(0) rotate(-25deg);
                transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                z-index: 15;
            }
            .pfa-doc-seal.pfa-stamped {
                transform: scale(1) rotate(-15deg);
            }
            .pfa-seal-ripple {
                position: absolute;
                inset: -6px;
                border: 2px solid #ef4444;
                border-radius: 50%;
                opacity: 0;
            }
            .pfa-doc-seal.pfa-stamped .pfa-seal-ripple {
                animation: pfaRipple 0.6s ease-out forwards;
            }
            @keyframes pfaRipple {
                0% { transform: scale(0.6); opacity: 1; }
                100% { transform: scale(1.8); opacity: 0; }
            }

            .pfa-quill {
                position: absolute;
                width: 55px;
                height: 55px;
                pointer-events: none;
                z-index: 40;
                top: 85px;
                right: 35px;
                filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
                transform-origin: bottom left;
                transition: all 0.25s ease;
                opacity: 1;
            }
            @keyframes pfaQuillWrite {
                0% { transform: translate(0, 0) rotate(0deg); }
                25% { transform: translate(-30px, 8px) rotate(-6deg); }
                50% { transform: translate(-65px, 14px) rotate(4deg); }
                75% { transform: translate(-100px, 20px) rotate(-4deg); }
                100% { transform: translate(-120px, 26px) rotate(0deg); }
            }
            .pfa-quill.pfa-writing {
                animation: pfaQuillWrite 0.9s ease-in-out infinite alternate;
            }

            /* ── الظرف الملكي المتكامل (The Luxury SVG Master Envelope) ── */
            .pfa-envelope-scene {
                position: absolute;
                width: 270px;
                height: 180px;
                top: 50px;
                perspective: 1200px;
                z-index: 20;
                opacity: 0;
                transform: scale(0.8) translateY(40px);
                transition: opacity 0.5s ease, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .pfa-envelope-scene.pfa-active {
                opacity: 1;
                transform: scale(1) translateY(0);
            }

            /* انزلاق السند المطوي داخل الظرف عبر الـ SVG */
            .pfa-folded-letter {
                transition: transform 0.85s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
                transform-origin: center top;
            }

            /* غطاء الظرف العلوي القابل للطي (Top Flap) */
            .pfa-svg-top-flap {
                transform-origin: 135px 25px;
                transform: scaleY(-1);
                transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .pfa-svg-top-flap.pfa-closed {
                transform: scaleY(1);
            }

            /* ختم الشمع على الظرف */
            .pfa-svg-seal {
                transform-origin: 135px 105px;
                transform: scale(0);
                transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .pfa-svg-seal.pfa-sealed {
                transform: scale(1);
            }

            /* ── حركة الطيران والانطلاق الجوي ── */
            .pfa-envelope-scene.pfa-flying {
                animation: pfaFlyAway 1.1s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            }
            @keyframes pfaFlyAway {
                0% {
                    transform: scale(1) translate3d(0, 0, 0) rotate(0deg);
                    opacity: 1;
                }
                25% {
                    transform: scale(0.95) translate3d(20px, 15px, 0) rotate(-8deg);
                }
                50% {
                    transform: scale(0.82) translate3d(-40px, -45px, 80px) rotate(-18deg);
                }
                75% {
                    transform: scale(0.55) translate3d(180px, -130px, 200px) rotate(14deg);
                    opacity: 0.9;
                }
                100% {
                    transform: scale(0.1) translate3d(450px, -260px, 400px) rotate(25deg);
                    opacity: 0;
                }
            }

            .pfa-jet-stream {
                position: absolute;
                inset: 0;
                pointer-events: none;
                opacity: 0;
                z-index: 6;
                transition: opacity 0.3s ease;
            }
            .pfa-jet-stream.pfa-active {
                opacity: 1;
            }
            .pfa-streak {
                position: absolute;
                height: 3px;
                background: linear-gradient(90deg, transparent, #25d366, #fbbf24, transparent);
                border-radius: 4px;
                opacity: 0.85;
                animation: pfaStreakMove 0.8s linear infinite;
            }
            @keyframes pfaStreakMove {
                0% { transform: translateX(120px) scaleX(0.4); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateX(-180px) scaleX(1.3); opacity: 0; }
            }

            /* ── مرحلة النجاح واكتمال التسليم (Delivery Success) ── */
            .pfa-success-view {
                position: absolute;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                opacity: 0;
                pointer-events: none;
                transform: scale(0.75);
                transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
                z-index: 50;
            }
            .pfa-success-view.pfa-active {
                opacity: 1;
                pointer-events: auto;
                transform: scale(1);
            }

            .pfa-success-badge-circle {
                width: 84px;
                height: 84px;
                border-radius: 50%;
                background: radial-gradient(circle, #25d366 30%, #059669 100%);
                border: 3.5px solid #86efac;
                box-shadow: 
                    0 0 40px rgba(37, 211, 102, 0.65),
                    0 10px 25px rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #ffffff;
                font-size: 2.7rem;
                margin-bottom: 14px;
                animation: pfaPopIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            @keyframes pfaPopIn {
                0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                70% { transform: scale(1.18) rotate(10deg); }
                100% { transform: scale(1) rotate(0deg); opacity: 1; }
            }

            .pfa-recipient-card {
                background: rgba(15, 23, 42, 0.85);
                border: 1px solid rgba(37, 211, 102, 0.45);
                border-radius: 16px;
                padding: 14px 20px;
                width: 90%;
                max-width: 370px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.6);
                text-align: center;
            }
            .pfa-recipient-name {
                font-size: 1.12rem;
                font-weight: 800;
                color: #f8fafc;
                margin-bottom: 4px;
            }
            .pfa-recipient-phone {
                font-size: 0.88rem;
                color: #4ade80;
                font-family: monospace;
                direction: ltr;
                display: inline-block;
                margin-bottom: 8px;
            }
            .pfa-recipient-meta {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                font-size: 0.78rem;
                color: #cbd5e1;
                border-top: 1px solid rgba(255,255,255,0.12);
                padding-top: 8px;
            }

            /* شريط التقدم السفلي */
            .pfa-footer {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .pfa-status-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.82rem;
                color: #e2e8f0;
                font-weight: 600;
            }
            .pfa-status-text {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #fbbf24;
                transition: color 0.3s ease;
            }
            .pfa-status-text.pfa-success {
                color: #34d399;
            }
            .pfa-progress-track {
                width: 100%;
                height: 7px;
                background: rgba(255, 255, 255, 0.12);
                border-radius: 6px;
                overflow: hidden;
            }
            .pfa-progress-fill {
                height: 100%;
                width: 25%;
                background: linear-gradient(90deg, #f59e0b, #25d366);
                border-radius: 6px;
                box-shadow: 0 0 12px rgba(37, 211, 102, 0.5);
                transition: width 0.4s ease;
            }

            .pfa-btn-close {
                align-self: center;
                margin-top: 6px;
                padding: 6px 22px;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 8px;
                color: #cbd5e1;
                font-size: 0.82rem;
                cursor: pointer;
                transition: all 0.2s ease;
                display: none;
            }
            .pfa-btn-close:hover {
                background: rgba(255, 255, 255, 0.2);
                color: #fff;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * تشغيل المؤثر الحركي الملكي لإرسال كمبيالة السند
     * @param {Object} opts إعدادات السند والمستلم
     * @returns {Object} كائن التحكم بالحركة (controller)
     */
    window.startPromissoryFlightAnimation = function(opts = {}) {
        injectFlightStyles();

        // إزالة أي نافذة سابقة إن وجدت
        const oldOverlay = document.getElementById('promissory-flight-overlay');
        if (oldOverlay && oldOverlay.parentNode) {
            oldOverlay.parentNode.removeChild(oldOverlay);
        }

        const recipientName = opts.recipientName || 'المستفيد المعتمد';
        const phone = opts.phone || '';
        const voucherNo = opts.voucherNo || 'M26090001';
        const amount = opts.amount || '0';
        const currency = opts.currency || 'SDG';
        const partyType = opts.partyType || 'المستلم';

        // إنشاء هيكل العرض
        const overlay = document.createElement('div');
        overlay.id = 'promissory-flight-overlay';
        overlay.className = 'pfa-overlay';

        overlay.innerHTML = `
            <div class="pfa-stars-bg"></div>
            <div class="pfa-modal-card">
                <!-- الرأس -->
                <div class="pfa-header">
                    <div class="pfa-brand-badge">
                        <span>🏛️</span>
                        <span>النظام المحاسبي الرقمي المعتمد</span>
                    </div>
                    <h3 class="pfa-title" id="pfa-main-title">تحرير وإرسال كمبيالة الصرف المالية</h3>
                    <p class="pfa-subtitle" id="pfa-sub-title">سند رسمي رقم #${voucherNo} • موثق ومعتمد</p>
                </div>

                <!-- مسرح الحركة (3D Stage) -->
                <div class="pfa-stage">
                    <!-- مسارات السرعة الهوائية -->
                    <div class="pfa-jet-stream" id="pfa-jet-stream">
                        <div class="pfa-streak" style="top: 25%; right: 10%; width: 140px; animation-delay: 0s;"></div>
                        <div class="pfa-streak" style="top: 48%; right: 5%; width: 190px; animation-delay: 0.2s;"></div>
                        <div class="pfa-streak" style="top: 72%; right: 15%; width: 130px; animation-delay: 0.4s;"></div>
                    </div>

                    <!-- ريشة الكتابة المذهبة -->
                    <div class="pfa-quill pfa-writing" id="pfa-quill">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M56 4C45 7 24 24 16 38L12 52L26 48C40 40 57 19 60 8L56 4Z" fill="url(#quillGrad)" stroke="#fef08a" stroke-width="1.5"/>
                            <path d="M12 52L8 60L16 56L12 52Z" fill="#fbbf24" stroke="#d97706" stroke-width="1"/>
                            <circle cx="10" cy="58" r="2.5" fill="#ef4444"/>
                            <defs>
                                <linearGradient id="quillGrad" x1="60" y1="4" x2="12" y2="52" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#fbbf24"/>
                                    <stop offset="0.5" stop-color="#d97706"/>
                                    <stop offset="1" stop-color="#b45309"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <!-- ورقة السند في المرحلة الأولى -->
                    <div class="pfa-doc-container" id="pfa-doc-container">
                        <div class="pfa-document" id="pfa-document">
                            <div class="pfa-doc-inner-border"></div>
                            <div class="pfa-doc-header">
                                <span class="pfa-doc-emblem">🏛️</span>
                                <span class="pfa-doc-title">كمبيالة صرف مالي رسمي</span>
                                <span class="pfa-doc-number">#${voucherNo}</span>
                            </div>
                            <div class="pfa-doc-parties">
                                <div class="pfa-doc-party-row">
                                    <span>المستفيد:</span>
                                    <span class="pfa-doc-party-val">${recipientName}</span>
                                </div>
                                <div class="pfa-doc-party-row">
                                    <span>الصفة:</span>
                                    <span class="pfa-doc-party-val">${partyType}</span>
                                </div>
                            </div>
                            <div class="pfa-doc-lines">
                                <div class="pfa-doc-line" id="pfa-line-1"></div>
                                <div class="pfa-doc-line" id="pfa-line-2"></div>
                                <div class="pfa-doc-line" id="pfa-line-3"></div>
                            </div>
                            <div class="pfa-doc-amount-box">
                                <span class="pfa-doc-amount-label">المبلغ المعتمد:</span>
                                <span class="pfa-doc-amount-val">${amount} ${currency}</span>
                            </div>
                            <!-- ختم الشمع الرسمي -->
                            <div class="pfa-doc-seal" id="pfa-doc-seal">
                                <div class="pfa-seal-ripple"></div>
                                <span>معتمد</span>
                            </div>
                        </div>
                    </div>

                    <!-- مشهد الظرف الملكي المتكامل (Vector Envelope Scene) -->
                    <div class="pfa-envelope-scene" id="pfa-envelope-scene">
                        <svg viewBox="0 0 270 180" width="270" height="180" xmlns="http://www.w3.org/2000/svg" style="overflow: visible; filter: drop-shadow(0 20px 35px rgba(0,0,0,0.85));">
                            <defs>
                                <linearGradient id="pfaEnvBackGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#0f172a"/>
                                    <stop offset="100%" stop-color="#1e293b"/>
                                </linearGradient>
                                <linearGradient id="pfaEnvFlapLeft" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#1e3a8a"/>
                                    <stop offset="100%" stop-color="#1e40af"/>
                                </linearGradient>
                                <linearGradient id="pfaEnvFlapRight" x1="100%" y1="0%" x2="0%" y2="0%">
                                    <stop offset="0%" stop-color="#172554"/>
                                    <stop offset="100%" stop-color="#1e3a8a"/>
                                </linearGradient>
                                <linearGradient id="pfaEnvFlapBottom" x1="0%" y1="100%" x2="0%" y2="0%">
                                    <stop offset="0%" stop-color="#1e3a8a"/>
                                    <stop offset="100%" stop-color="#2563eb"/>
                                </linearGradient>
                                <linearGradient id="pfaEnvFlapTop" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#2563eb"/>
                                    <stop offset="100%" stop-color="#1d4ed8"/>
                                </linearGradient>
                                <linearGradient id="pfaGoldBorder" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#f59e0b"/>
                                    <stop offset="50%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#b45309"/>
                                </linearGradient>
                                <clipPath id="pfaEnvInnerClip">
                                    <rect x="10" y="25" width="250" height="150" rx="10"/>
                                </clipPath>
                            </defs>

                            <!-- 1. خلفية الظرف والبطانة الداخلية -->
                            <rect x="10" y="25" width="250" height="150" rx="10" fill="url(#pfaEnvBackGrad)" stroke="url(#pfaGoldBorder)" stroke-width="2.5"/>
                            <rect x="14" y="29" width="242" height="142" rx="7" fill="#0b1329" stroke="rgba(212,175,55,0.3)" stroke-width="1"/>

                            <!-- 2. السند المنزلق داخل الظرف (مقصوص بواسطة حدود الظرف) -->
                            <g clip-path="url(#pfaEnvInnerClip)">
                                <g id="pfa-svg-folded-letter" class="pfa-folded-letter" transform="translate(45, -130)">
                                    <!-- ورقة السند المطوية مصغرة -->
                                    <rect x="0" y="0" width="180" height="135" rx="6" fill="#fdfbf7" stroke="#b45309" stroke-width="1.5"/>
                                    <line x1="12" y1="20" x2="168" y2="20" stroke="#d97706" stroke-width="1.5"/>
                                    <text x="90" y="16" fill="#78350f" font-size="8" font-weight="bold" text-anchor="middle">🏛️ كمبيالة صرف مالي رسمي معتمد</text>
                                    <rect x="12" y="28" width="156" height="25" rx="3" fill="#fef3c7" stroke="rgba(217,119,6,0.3)"/>
                                    <text x="162" y="44" fill="#92400e" font-size="7.5" font-weight="bold" text-anchor="end">👤 ${recipientName}</text>
                                    <text x="18" y="44" fill="#b45309" font-size="7" font-weight="bold" text-anchor="start">#${voucherNo}</text>
                                    <line x1="15" y1="65" x2="165" y2="65" stroke="#cbd5e1" stroke-width="3" stroke-linecap="round"/>
                                    <line x1="15" y1="75" x2="165" y2="75" stroke="#cbd5e1" stroke-width="3" stroke-linecap="round"/>
                                    <rect x="15" y="86" width="150" height="24" rx="4" fill="#0f172a" stroke="#d97706"/>
                                    <text x="90" y="102" fill="#fbbf24" font-size="10" font-weight="900" font-family="monospace" text-anchor="middle">${amount} ${currency}</text>
                                    <!-- ختم الشمع المعتمد على الورقة -->
                                    <circle cx="32" cy="116" r="14" fill="#dc2626" stroke="#fef08a" stroke-width="1.5"/>
                                    <text x="32" y="119" fill="#ffffff" font-size="6" font-weight="bold" text-anchor="middle">معتمد</text>
                                </g>
                            </g>

                            <!-- 3. جيوب الظرف الأمامية (تغطي الورقة عند استقرارها) -->
                            <!-- الجيب الأيسر -->
                            <path d="M 10,25 L 135,115 L 10,175 Z" fill="url(#pfaEnvFlapLeft)" opacity="0.95"/>
                            <!-- الجيب الأيمن -->
                            <path d="M 260,25 L 135,115 L 260,175 Z" fill="url(#pfaEnvFlapRight)" opacity="0.95"/>
                            <!-- الجيب السفلي -->
                            <path d="M 10,175 L 135,95 L 260,175 Z" fill="url(#pfaEnvFlapBottom)" stroke="rgba(212,175,55,0.4)" stroke-width="1"/>

                            <!-- 4. غطاء الظرف العلوي الذي يغلق فوق الجيوب -->
                            <g id="pfa-svg-top-flap" class="pfa-svg-top-flap">
                                <path d="M 10,25 L 135,110 L 260,25 Z" fill="url(#pfaEnvFlapTop)" stroke="url(#pfaGoldBorder)" stroke-width="1.5"/>
                            </g>

                            <!-- 5. ختم الشمع الملكي النهائي على الغطاء -->
                            <g id="pfa-svg-seal" class="pfa-svg-seal">
                                <circle cx="135" cy="105" r="22" fill="url(#pfaSealGrad)" stroke="#fef08a" stroke-width="2" filter="drop-shadow(0 4px 10px rgba(37,211,102,0.6))"/>
                                <circle cx="135" cy="105" r="17" fill="none" stroke="rgba(255,255,255,0.4)" stroke-dasharray="3,2"/>
                                <!-- أيقونة الواتساب الرسمية المعتمدة -->
                                <path d="M125 108 L126.5 102.5 A6.5 6.5 0 1 1 133 111.5 L125 108 Z" fill="#ffffff"/>
                                <path d="M131.5 102.5 C131 101.8 130.5 101.8 130 102 C129.5 102.2 128.5 102.8 128 103.8 C127.5 104.8 128 106.5 129.5 108 C131 109.5 132.8 110 133.8 109.5 C134.8 109 135.4 108 135.6 107.5 C135.8 107 135.8 106.5 135.1 106 Z" fill="#128c7e"/>
                            </g>
                            <defs>
                                <radialGradient id="pfaSealGrad" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#25d366"/>
                                    <stop offset="100%" stop-color="#059669"/>
                                </radialGradient>
                            </defs>
                        </svg>
                    </div>

                    <!-- شاشة اكتمال التسليم بنجاح (Success View) -->
                    <div class="pfa-success-view" id="pfa-success-view">
                        <div class="pfa-success-badge-circle">
                            <span>✓</span>
                        </div>
                        <div class="pfa-recipient-card">
                            <div class="pfa-recipient-name">👤 ${recipientName}</div>
                            <div class="pfa-recipient-phone">📱 ${phone || 'الرقم المعتمد'}</div>
                            <div class="pfa-recipient-meta">
                                <span>📋 سند #${voucherNo}</span>
                                <span>💰 ${amount} ${currency}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- شريط الحالة والتقدم السفلي -->
                <div class="pfa-footer">
                    <div class="pfa-status-bar">
                        <div class="pfa-status-text" id="pfa-status-text">
                            <span id="pfa-status-icon">✍️</span>
                            <span id="pfa-status-desc">جاري تحرير واعتماد الكمبيالة المالية...</span>
                        </div>
                        <span id="pfa-status-percent" style="color: #94a3b8;">25%</span>
                    </div>
                    <div class="pfa-progress-track">
                        <div class="pfa-progress-fill" id="pfa-progress-fill" style="width: 25%;"></div>
                    </div>
                    <button class="pfa-btn-close" id="pfa-btn-close" type="button">إغلاق</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            overlay.classList.add('pfa-visible');
        });

        const docContainer= document.getElementById('pfa-doc-container');
        const quillEl     = document.getElementById('pfa-quill');
        const sealEl      = document.getElementById('pfa-doc-seal');
        const envScene    = document.getElementById('pfa-envelope-scene');
        const foldedLetter= document.getElementById('pfa-svg-folded-letter');
        const svgTopFlap  = document.getElementById('pfa-svg-top-flap');
        const svgSeal     = document.getElementById('pfa-svg-seal');
        const jetStream   = document.getElementById('pfa-jet-stream');
        const successView = document.getElementById('pfa-success-view');
        const statusIcon  = document.getElementById('pfa-status-icon');
        const statusDesc  = document.getElementById('pfa-status-desc');
        const statusPct   = document.getElementById('pfa-status-percent');
        const progressFill= document.getElementById('pfa-progress-fill');
        const btnClose    = document.getElementById('pfa-btn-close');

        let isCompleted = false;

        // ══════════════════════════════════════════════════════
        // جدول تشغيل المراحل الزمنية التلقائي (Timeline Orchestration)
        // ══════════════════════════════════════════════════════

        // خطوة 1: كتابة خطوط الوثيقة بريشة الحبر
        setTimeout(() => {
            const l1 = document.getElementById('pfa-line-1');
            if (l1) l1.classList.add('pfa-written');
        }, 150);
        setTimeout(() => {
            const l2 = document.getElementById('pfa-line-2');
            if (l2) l2.classList.add('pfa-written');
        }, 350);
        setTimeout(() => {
            const l3 = document.getElementById('pfa-line-3');
            if (l3) l3.classList.add('pfa-written');
        }, 550);

        // ختم الوثيقة الرسمي بالشمع الأحمر
        setTimeout(() => {
            if (sealEl) sealEl.classList.add('pfa-stamped');
            if (quillEl) {
                quillEl.style.opacity = '0';
                quillEl.style.transform = 'translate(-80px, -40px) scale(0.6)';
            }
        }, 750);

        // خطوة 2: طي السند وظهور الظرف الملكي (Fold & Envelope Reveal)
        setTimeout(() => {
            if (isCompleted) return;
            statusIcon.textContent = '✉️';
            statusDesc.textContent = 'إدراج السند في الظرف المشفر وإحكام الختم الملكي...';
            statusPct.textContent = '55%';
            progressFill.style.width = '55%';

            // طي الوثيقة وتصغيرها تمهيداً للانتقال للظرف
            if (docContainer) {
                docContainer.style.transform = 'scale(0.7) translateY(40px)';
                docContainer.style.opacity = '0';
            }

            // ظهور الظرف الملكي
            if (envScene) envScene.classList.add('pfa-active');
        }, 1000);

        // انزلاق السند المطوي من فوهة الظرف واستقراره داخل الجيب
        setTimeout(() => {
            if (isCompleted) return;
            if (foldedLetter) {
                foldedLetter.style.transform = 'translate(45px, 35px)';
            }
        }, 1250);

        // إغلاق غطاء الظرف العلوي
        setTimeout(() => {
            if (isCompleted) return;
            if (svgTopFlap) svgTopFlap.classList.add('pfa-closed');
        }, 1750);

        // ختم الظرف بالشمع الأخضر والمذهب الرسمي
        setTimeout(() => {
            if (isCompleted) return;
            if (svgSeal) svgSeal.classList.add('pfa-sealed');
        }, 2100);

        // خطوة 3: انطلاق وتحليق الظرف نحو المستلم (Flight & Jet Stream)
        setTimeout(() => {
            if (isCompleted) return;
            statusIcon.textContent = '🚀';
            statusDesc.textContent = `انطلاق السند الرقمي عبر الواتساب إلى (${recipientName})...`;
            statusPct.textContent = '80%';
            progressFill.style.width = '80%';

            if (jetStream) jetStream.classList.add('pfa-active');
            if (envScene) envScene.classList.add('pfa-flying');
        }, 2400);

        // كائن التحكم المتاح للاستدعاء الخارجي
        return {
            /**
             * اكتمال الإرسال بنجاح وإظهار شارة النصر والتسليم
             */
            complete: function(result = {}) {
                isCompleted = true;
                const finalName = result.recipientName || recipientName;
                const finalPhone = result.phone || phone;

                setTimeout(() => {
                    if (envScene) envScene.style.display = 'none';
                    if (docContainer) docContainer.style.display = 'none';
                    if (quillEl) quillEl.style.display = 'none';
                    if (jetStream) jetStream.classList.remove('pfa-active');

                    if (successView) successView.classList.add('pfa-active');

                    statusIcon.textContent = '✅';
                    statusDesc.textContent = `تم تسليم كمبيالة السند بنجاح عبر الواتساب!`;
                    statusDesc.classList.add('pfa-success');
                    statusPct.textContent = '100%';
                    progressFill.style.width = '100%';
                    progressFill.style.background = '#25d366';

                    if (btnClose) {
                        btnClose.style.display = 'inline-block';
                        btnClose.onclick = () => this.close();
                    }

                    // إغلاق تلقائي سلس بعد استمتاع المستخدم بالمؤثر (2.4 ثانية)
                    setTimeout(() => {
                        this.close();
                    }, 2400);

                }, 400);
            },

            /**
             * إظهار حالة الخطأ في حال تعذر الإرسال
             */
            error: function(errText) {
                isCompleted = true;
                statusIcon.textContent = '❌';
                statusDesc.textContent = errText || 'تعذر الإرسال عبر خادم الواتساب';
                statusDesc.style.color = '#ef4444';
                progressFill.style.background = '#ef4444';

                if (btnClose) {
                    btnClose.style.display = 'inline-block';
                    btnClose.textContent = 'إغلاق المحاولة';
                    btnClose.onclick = () => this.close();
                }
            },

            /**
             * إغلاق النافذة وتنظيف الـ DOM
             */
            close: function() {
                if (!overlay || !overlay.parentNode) return;
                overlay.classList.remove('pfa-visible');
                setTimeout(() => {
                    if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                }, 380);
            }
        };
    };

})();
