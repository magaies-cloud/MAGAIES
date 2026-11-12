/**
 * =========================================================================
 * نظام السيطرة (MAGAIEES ERP 2026)
 * منظومة الباركود والـ QR والماسح الضوئي والواتساب الذكي للسندات المالية
 * تعمل على: حركة مالية (single-cash-payment.php) + صرف وقبض مجمع (multi-cash-payment.php) + المعاملات المالية (vouchers-hub.php)
 * =========================================================================
 */
(function () {
    'use strict';

    if (window.__MagaieesVoucherQRBarcodeSuiteLoaded) return;
    window.__MagaieesVoucherQRBarcodeSuiteLoaded = true;

    // 1. توليد باركود خطي Code-39/128 SVG نظيف وسريع لأي رقم سند
    window.buildCleanBarcodeSVG = function (codeText, height = 46) {
        if (typeof window.generateBarcodeSVG === 'function') {
            try { return window.generateBarcodeSVG(codeText); } catch (e) {}
        }
        const clean = String(codeText || 'M260000').replace(/[^A-Za-z0-9\-]/g, '').toUpperCase() || 'M260000';
        let bars = '';
        let x = 8;
        for (let i = 0; i < clean.length; i++) {
            const c = clean.charCodeAt(i);
            for (let b = 0; b < 6; b++) {
                const w = ((c >> b) & 1) ? 2.4 : 1.1;
                if (b % 2 === 0) {
                    bars += `<rect x="${x.toFixed(1)}" y="4" width="${w}" height="${height}" fill="#0f172a"/>`;
                }
                x += w + 1.1;
            }
            x += 2;
        }
        const totalW = Math.max(160, Math.ceil(x + 8));
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${totalW}" height="${height + 22}" viewBox="0 0 ${totalW} ${height + 22}" style="background:#fff;border-radius:6px;padding:4px 8px;max-width:100%;">
            ${bars}
            <text x="${totalW / 2}" y="${height + 17}" text-anchor="middle" font-family="monospace" font-size="12" font-weight="900" fill="#0f172a" letter-spacing="2">${clean}</text>
        </svg>`;
    };

    // 2. قراءة بيانات السند الحالي من الشاشة المفتوحة (حركة مالية أو صرف وقبض مجمع)
    window.extractCurrentScreenVoucherInfo = function () {
        const path = window.location.pathname.toLowerCase();
        const isMulti = path.includes('multi-cash-payment.php');

        if (isMulti) {
            const vNo = (document.getElementById('voucher-no-display')?.value || 'جديد').trim();
            const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().slice(0, 10);
            const isReceipt = document.getElementById('voucher-type')?.value === 'receipt';
            const mainAcc = (document.getElementById('main-account-search')?.value || 'حساب رئيسي').trim();
            const grandAmt = (document.getElementById('mcp-sum-grand')?.innerText || '0').trim();
            const curr = (document.getElementById('mcp-sum-curr')?.innerText || 'SDG').trim();
            const desc = (document.getElementById('voucher-desc')?.value || 'سند مجمع متعدد الجهات').trim();
            return {
                id: vNo,
                typeLabel: isReceipt ? 'سند قبض مجمع' : 'سند صرف مجمع',
                isReceipt: isReceipt,
                date: vDate,
                fromAccount: isReceipt ? 'متعدد الجهات' : mainAcc,
                toAccount: isReceipt ? mainAcc : 'متعدد الجهات',
                amount: grandAmt,
                currency: curr,
                statement: desc,
                screen: 'multi-cash-payment.php'
            };
        }

        // شاشة حركة مالية المفردة (single-cash-payment.php)
        const vNo = (document.getElementById('voucher-no')?.value || document.getElementById('voucher-no-display')?.value || 'جديد').trim();
        const vDate = document.getElementById('voucher-date')?.value || new Date().toISOString().slice(0, 10);
        const fromAcc = (document.getElementById('from-account-search')?.value || document.getElementById('from-account')?.value || '—').trim();
        const toAcc = (document.getElementById('to-account-search')?.value || document.getElementById('to-account')?.value || '—').trim();
        const amt = (document.getElementById('amount-local')?.value || document.getElementById('cash-amount')?.value || '0').trim();
        const curr = (document.getElementById('currency-select')?.value || 'SDG').trim();
        const desc = (document.getElementById('voucher-notes')?.value || document.getElementById('statement-input')?.value || 'حركة مالية').trim();
        return {
            id: vNo,
            typeLabel: 'حركة مالية معتمدة',
            isReceipt: false,
            date: vDate,
            fromAccount: fromAcc,
            toAccount: toAcc,
            amount: amt,
            currency: curr,
            statement: desc,
            screen: 'single-cash-payment.php'
        };
    };

    // 3. البحث عن أي سند بالرقم في جميع الجداول المحلية (6 مخازن)
    window.findVoucherAnywhereByCode = async function (rawCode) {
        const cleanCode = String(rawCode || '')
            .replace(/^VOUCHER:/i, '')
            .replace(/^VERIFIED_/i, '')
            .replace(/^#/i, '')
            .trim();
        if (!cleanCode) return null;

        const stores = [
            { key: 'erp_single_payments_v1', route: 'single-cash-payment.php', label: 'حركة مالية (صرف/قبض مفرد)' },
            { key: 'erp_single_receipts_v1', route: 'single-cash-payment.php?type=receipt', label: 'سند قبض مفرد' },
            { key: 'erp_payments_v1', route: 'multi-cash-payment.php', label: 'صرف وقبض مجمع' },
            { key: 'erp_receipts_v1', route: 'multi-cash-payment.php?type=receipt', label: 'قبض مجمع' },
            { key: 'erp_comprehensive_payments_v1', route: 'multi-cash-payment.php', label: 'سند صرف مجمع' },
            { key: 'erp_comprehensive_receipts_v1', route: 'multi-cash-payment.php?type=receipt', label: 'سند قبض مجمع' }
        ];

        for (const st of stores) {
            const tk = (typeof window.getTenantKey === 'function') ? window.getTenantKey(st.key) : st.key;
            let list = [];
            try {
                if (typeof localforage !== 'undefined') {
                    list = (await localforage.getItem(tk)) || [];
                }
            } catch (e) {}
            if (!Array.isArray(list) || list.length === 0) {
                try {
                    list = JSON.parse(localStorage.getItem(tk) || localStorage.getItem(st.key) || '[]');
                } catch (e) { list = []; }
            }
            if (!Array.isArray(list)) list = Object.values(list || {});
            const found = list.find(v => v && !v.deleted && (
                String(v.id || '').toLowerCase() === cleanCode.toLowerCase() ||
                String(v.voucherNo || '').toLowerCase() === cleanCode.toLowerCase() ||
                String(v.referenceNo || '').toLowerCase() === cleanCode.toLowerCase()
            ));
            if (found) {
                const vId = found.voucherNo || found.id || cleanCode;
                const sep = st.route.includes('?') ? '&' : '?';
                return {
                    voucher: found,
                    id: vId,
                    typeLabel: st.label,
                    date: found.date || (found.createdAt ? String(found.createdAt).slice(0, 10) : '—'),
                    fromAccount: found.fromAccount || found.withdrawalAccount || found.mainAccountName || 'متعدد',
                    toAccount: found.toAccount || found.depositAccount || found.accountId || 'متعدد',
                    amount: found.amount || found.totalAmount || found.amountLocal || 0,
                    currency: found.currency || 'SDG',
                    statement: found.description || found.notes || found.statement || '—',
                    targetUrl: `${st.route}${sep}id=${encodeURIComponent(vId)}`
                };
            }
        }
        return null;
    };

    // 4. نافذة ختم الباركود والـ QR الذكي للسند + طباعة الملصق + إرسال واتساب
    window.openVoucherQRBarcodeModal = function (customVoucherObj) {
        const v = customVoucherObj || window.extractCurrentScreenVoucherInfo();
        const vId = v.id || 'M260000';
        const baseOrigin = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
        const verifyUrl = `${baseOrigin}/verify-voucher.php?id=${encodeURIComponent(vId)}&amt=${encodeURIComponent(v.amount || 0)}&date=${encodeURIComponent(v.date || '')}&type=${encodeURIComponent(v.typeLabel || '')}&from=${encodeURIComponent(v.fromAccount || '')}&to=${encodeURIComponent(v.toAccount || '')}`;
        const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=2&data=${encodeURIComponent(verifyUrl)}`;
        const barcodeSvg = window.buildCleanBarcodeSVG(vId, 48);

        let modal = document.getElementById('mg-global-qr-barcode-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'mg-global-qr-barcode-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,0.82);backdrop-filter:blur(10px);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;font-family:Cairo,Tajawal,sans-serif;direction:rtl;';
            document.body.appendChild(modal);
        }

        modal.innerHTML = `
            <div style="background:linear-gradient(145deg,#0f172a,#1e1b4b);border:2px solid #22d3ee;border-radius:22px;max-width:540px;width:100%;padding:22px 24px;color:#fff;box-shadow:0 25px 70px rgba(0,0,0,0.85),0 0 35px rgba(34,211,238,0.22);position:relative;">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.12);padding-bottom:12px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:1.5rem;">🔲</span>
                        <div>
                            <h3 style="margin:0;font-size:1.1rem;font-weight:900;color:#22d3ee;">ختم التحقق الرقمي والباركود & QR للسند</h3>
                            <div style="font-size:0.76rem;color:#94a3b8;">مرتبط مع الماسح الضوئي والواتساب وكشف الحساب ودفتر الأستاذ</div>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('mg-global-qr-barcode-modal').style.display='none'" style="background:rgba(244,63,94,0.2);border:1px solid #f43f5e;color:#fda4af;width:34px;height:34px;border-radius:10px;cursor:pointer;font-weight:900;font-size:1rem;">✕</button>
                </div>

                <div id="mg-printable-qr-sticker" style="background:#ffffff;color:#0f172a;border-radius:16px;padding:16px;text-align:center;border:2px dashed #0284c7;">
                    <div style="font-size:0.95rem;font-weight:900;color:#0f172a;margin-bottom:4px;">${v.typeLabel || 'سند مالي معتمد'} — #${vId}</div>
                    <div style="font-size:0.8rem;color:#475569;font-weight:700;margin-bottom:10px;">التاريخ: ${v.date || '—'} | المبلغ: <strong style="color:#047857;">${v.amount} ${v.currency || 'SDG'}</strong></div>
                    <div style="font-size:0.76rem;color:#334155;margin-bottom:12px;">من: <b>${v.fromAccount || '—'}</b> ⬅ إلى: <b>${v.toAccount || '—'}</b></div>
                    <div style="display:flex;align-items:center;justify-content:space-around;gap:12px;flex-wrap:wrap;margin:10px 0;">
                        <div style="text-align:center;">
                            <img src="${qrImgUrl}" alt="QR" style="width:135px;height:135px;border:1px solid #cbd5e1;border-radius:10px;padding:4px;background:#fff;">
                            <div style="font-size:0.7rem;color:#64748b;font-weight:800;margin-top:3px;">امسح الـ QR للتحقق الرقمي</div>
                        </div>
                        <div style="text-align:center;flex:1;min-width:170px;">
                            ${barcodeSvg}
                            <div style="font-size:0.7rem;color:#64748b;font-weight:800;margin-top:3px;">باركود القارئ الليزري (Code-128)</div>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:16px;">
                    <button type="button" onclick="window.printVoucherQRSticker()" style="background:linear-gradient(135deg,#0284c7,#2563eb);border:1px solid #38bdf8;color:#fff;padding:10px 14px;border-radius:12px;font-family:Cairo,sans-serif;font-weight:800;font-size:0.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>🖨️</span> طباعة ملصق الباركود & QR
                    </button>
                    <button type="button" onclick="window.sendVoucherQRWhatsApp(${JSON.stringify(v).replace(/"/g, '&quot;')}, '${verifyUrl}')" style="background:linear-gradient(135deg,#059669,#10b981);border:1px solid #34d399;color:#fff;padding:10px 14px;border-radius:12px;font-family:Cairo,sans-serif;font-weight:800;font-size:0.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>💬</span> إرسال السند والـ QR واتساب
                    </button>
                    <a href="verify-voucher.php?id=${encodeURIComponent(vId)}&amt=${encodeURIComponent(v.amount || 0)}&date=${encodeURIComponent(v.date || '')}&type=${encodeURIComponent(v.typeLabel || '')}&from=${encodeURIComponent(v.fromAccount || '')}&to=${encodeURIComponent(v.toAccount || '')}" target="_blank" style="background:rgba(168,85,247,0.2);border:1px solid #c084fc;color:#e9d5ff;padding:10px 14px;border-radius:12px;font-family:Cairo,sans-serif;font-weight:800;font-size:0.84rem;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>🛡️</span> فتح شهادة التحقق الرقمية
                    </a>
                    <a href="account-statement.php?search=${encodeURIComponent(v.toAccount && v.toAccount !== 'متعدد الجهات' ? v.toAccount : vId)}" style="background:rgba(245,158,11,0.2);border:1px solid #fbbf24;color:#fef08a;padding:10px 14px;border-radius:12px;font-family:Cairo,sans-serif;font-weight:800;font-size:0.84rem;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <span>📊</span> كشف الحساب والترحيل
                    </a>
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    };

    // 5. طباعة ملصق الباركود والـ QR الحراري / المكتبي
    window.printVoucherQRSticker = function () {
        const el = document.getElementById('mg-printable-qr-sticker');
        if (!el) return;
        const w = window.open('', '_blank', 'width=520,height=520');
        if (!w) return;
        w.document.write(`
            <!DOCTYPE html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>طباعة ملصق باركود و QR السند</title>
                <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&display=swap" rel="stylesheet">
                <style>
                    body { font-family: 'Cairo', sans-serif; margin: 15px; text-align: center; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body onload="setTimeout(()=>{window.print();}, 350);">
                ${el.outerHTML}
            </body>
            </html>
        `);
        w.document.close();
    };

    // 6. إرسال السند مع رابط التحقق والـ QR عبر واتساب
    window.sendVoucherQRWhatsApp = function (v, verifyUrl) {
        // إذا كانت شاشة السند تحتوي على نافذة إرسال الكمبيالة الرسمية بالواتساب، نتيح الخيارين
        const msg = [
            `🏛️ *إشعار سند مالي معتمد - السجانة ERP*`,
            `📋 *النوع:* ${v.typeLabel || 'حركة مالية'}`,
            `🔢 *رقم السند:* #${v.id || '—'}`,
            `📅 *التاريخ:* ${v.date || '—'}`,
            `📤 *من حساب:* ${v.fromAccount || '—'}`,
            `📥 *إلى حساب:* ${v.toAccount || '—'}`,
            `💰 *المبلغ:* *${v.amount || 0}* ${v.currency || 'SDG'}`,
            `📝 *البيان:* ${v.statement || '—'}`,
            `🔗 *رابط شهادة التحقق الرقمي والـ QR:*`,
            verifyUrl
        ].join('\n');

        let phone = '';
        try {
            const custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
            const supps = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
            const partyName = (v.toAccount && v.toAccount !== 'متعدد') ? v.toAccount : v.fromAccount;
            const match = [...custs, ...supps].find(x => x && (x.name === partyName || x.id === partyName));
            if (match) phone = String(match.whatsapp || match.phone || '').replace(/[^0-9]/g, '');
        } catch (e) {}

        const waUrl = phone
            ? `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`
            : `https://wa.me/?text=${encodeURIComponent(msg)}`;
        window.open(waUrl, '_blank');
    };

    // 7. نافذة الماسح الضوئي الفوري للباركود والـ QR للسندات (Alt+B)
    window.openVoucherBarcodeScannerModal = function () {
        let modal = document.getElementById('mg-voucher-scanner-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'mg-voucher-scanner-modal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,0.84);backdrop-filter:blur(10px);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;font-family:Cairo,Tajawal,sans-serif;direction:rtl;';
            document.body.appendChild(modal);
        }

        modal.innerHTML = `
            <div style="background:linear-gradient(145deg,#0f172a,#1e293b);border:2px solid #10b981;border-radius:22px;max-width:520px;width:100%;padding:22px 24px;color:#fff;box-shadow:0 25px 70px rgba(0,0,0,0.85),0 0 35px rgba(16,185,129,0.22);">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.12);padding-bottom:12px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:1.5rem;">📷</span>
                        <div>
                            <h3 style="margin:0;font-size:1.08rem;font-weight:900;color:#34d399;">ماسح الباركود والـ QR الفوري للسندات (Alt+B)</h3>
                            <div style="font-size:0.76rem;color:#94a3b8;">مرر قارئ الباركود الليزري أو اكتب رقم السند للتحقق والفتح الفوري</div>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('mg-voucher-scanner-modal').style.display='none'" style="background:rgba(244,63,94,0.2);border:1px solid #f43f5e;color:#fda4af;width:34px;height:34px;border-radius:10px;cursor:pointer;font-weight:900;font-size:1rem;">✕</button>
                </div>

                <div style="display:flex;gap:8px;margin-bottom:14px;">
                    <input type="text" id="mg-scanner-code-input" placeholder="امسح الباركود/QR هنا أو اكتب رقم السند (مثال: M2609...)" style="flex:1;background:#020617;border:1.8px solid #10b981;border-radius:12px;padding:12px 14px;color:#fef08a;font-family:monospace;font-size:1.05rem;font-weight:900;text-align:center;" onkeydown="if(event.key==='Enter') window.executeVoucherBarcodeLookup()">
                    <button type="button" onclick="window.executeVoucherBarcodeLookup()" style="background:linear-gradient(135deg,#059669,#0d9488);border:1px solid #34d399;color:#fff;padding:0 18px;border-radius:12px;font-family:Cairo,sans-serif;font-weight:900;cursor:pointer;">🔍 فحص وفتح</button>
                </div>

                <div id="mg-scanner-result-box" style="min-height:60px;background:rgba(2,6,23,0.6);border:1px dashed rgba(148,163,184,0.25);border-radius:14px;padding:14px;text-align:center;color:#94a3b8;font-size:0.85rem;">
                    📡 جاهز لاستقبال إشارة قارئ الباركود (USB Laser Scanner) أو إدخال رقم السند...
                </div>
            </div>
        `;
        modal.style.display = 'flex';
        setTimeout(() => document.getElementById('mg-scanner-code-input')?.focus(), 80);
    };

    window.executeVoucherBarcodeLookup = async function () {
        const inp = document.getElementById('mg-scanner-code-input');
        const box = document.getElementById('mg-scanner-result-box');
        if (!inp || !box) return;
        let val = (inp.value || '').trim();
        if (val.includes('id=')) {
            try {
                const u = new URL(val);
                val = u.searchParams.get('id') || val;
            } catch (e) {}
        }
        if (!val) return;
        box.innerHTML = `<span style="color:#38bdf8;">⏳ جاري البحث في قواعد بيانات السندات...</span>`;
        const res = await window.findVoucherAnywhereByCode(val);
        if (!res) {
            box.innerHTML = `<div style="color:#fb7185;font-weight:800;">❌ لم يتم العثور على سند برقم (${val}) في السجلات المحلية.</div>`;
            return;
        }
        box.innerHTML = `
            <div style="text-align:right;color:#f8fafc;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="background:rgba(16,185,129,0.2);color:#34d399;padding:3px 10px;border-radius:99px;font-weight:900;font-size:0.8rem;">✅ سند معتمد وموثق: #${res.id}</span>
                    <span style="color:#cbd5e1;font-size:0.8rem;font-weight:700;">📅 ${res.date}</span>
                </div>
                <div style="font-size:0.85rem;margin-bottom:4px;"><strong>النوع:</strong> ${res.typeLabel}</div>
                <div style="font-size:0.85rem;margin-bottom:4px;"><strong>من حساب:</strong> ${res.fromAccount} ⬅ <strong>إلى حساب:</strong> ${res.toAccount}</div>
                <div style="font-size:0.95rem;color:#fef08a;font-weight:900;margin-bottom:10px;">💰 المبلغ: ${Number(res.amount || 0).toLocaleString()} ${res.currency}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="${res.targetUrl}" style="flex:1;background:#0284c7;color:#fff;text-decoration:none;padding:8px 12px;border-radius:10px;font-weight:800;text-align:center;font-size:0.82rem;">✏️ فتح السند في شاشته</a>
                    <button type="button" onclick='window.openVoucherQRBarcodeModal(${JSON.stringify(res).replace(/'/g, "&#39;")})' style="background:rgba(168,85,247,0.25);border:1px solid #c084fc;color:#e9d5ff;padding:8px 12px;border-radius:10px;font-family:Cairo,sans-serif;font-weight:800;cursor:pointer;font-size:0.82rem;">🔲 عرض QR والباركود</button>
                    <a href="general-ledger.php?search=${encodeURIComponent(res.id)}" style="background:rgba(16,185,129,0.2);border:1px solid #34d399;color:#34d399;text-decoration:none;padding:8px 12px;border-radius:10px;font-weight:800;font-size:0.82rem;">📘 القيد المحاسبي</a>
                </div>
            </div>
        `;
    };

    // 8. حقن أزرار الباركود والـ QR والماسح الضوئي في الشاشات الثلاث + اختصار Alt+B
    function injectSuiteButtons() {
        // في شاشة حركة مالية (single-cash-payment.php)
        const c15Bar = document.getElementById('mgC15CrossScreenLinksBar');
        if (c15Bar && !document.getElementById('mg-scp-qr-suite-btn')) {
            const firstDiv = c15Bar.querySelector('div');
            if (firstDiv) {
                const qrBtn = document.createElement('button');
                qrBtn.type = 'button';
                qrBtn.id = 'mg-scp-qr-suite-btn';
                qrBtn.className = 'mg-c15-link-pill';
                qrBtn.style.cssText = 'background:rgba(34,211,238,0.18);border-color:#22d3ee;color:#67e8f9;';
                qrBtn.innerHTML = '🔲 باركود & QR السند';
                qrBtn.onclick = () => window.openVoucherQRBarcodeModal();
                firstDiv.appendChild(qrBtn);

                const scanBtn = document.createElement('button');
                scanBtn.type = 'button';
                scanBtn.className = 'mg-c15-link-pill';
                scanBtn.style.cssText = 'background:rgba(16,185,129,0.18);border-color:#10b981;color:#6ee7b7;';
                scanBtn.innerHTML = '📷 مسح باركود سند (Alt+B)';
                scanBtn.onclick = () => window.openVoucherBarcodeScannerModal();
                firstDiv.appendChild(scanBtn);
            }
        }

        // في شاشة الصرف والقبض المجمع (multi-cash-payment.php)
        const mcpTopRow = document.getElementById('payment-methods-row');
        if (mcpTopRow && !document.getElementById('mg-mcp-qr-suite-btn')) {
            const qrBtn = document.createElement('button');
            qrBtn.type = 'button';
            qrBtn.id = 'mg-mcp-qr-suite-btn';
            qrBtn.className = 'btn crystal-btn';
            qrBtn.style.cssText = 'height:51px;padding:0 14px;border-radius:12px;font-weight:800;font-size:0.9rem;background:linear-gradient(135deg,rgba(34,211,238,0.25),rgba(8,145,178,0.4));border:1.5px solid #22d3ee !important;color:#cffafe;display:inline-flex;align-items:center;gap:6px;cursor:pointer;flex-shrink:0;';
            qrBtn.title = 'عرض وطباعة ختم الباركود والـ QR للسند المجمع وإرساله بالواتساب';
            qrBtn.innerHTML = '<span>🔲</span> <span>باركود & QR</span>';
            qrBtn.onclick = () => window.openVoucherQRBarcodeModal();
            mcpTopRow.appendChild(qrBtn);

            const scanBtn = document.createElement('button');
            scanBtn.type = 'button';
            scanBtn.className = 'btn crystal-btn';
            scanBtn.style.cssText = 'height:51px;padding:0 14px;border-radius:12px;font-weight:800;font-size:0.9rem;background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(5,150,105,0.4));border:1.5px solid #10b981 !important;color:#d1fae5;display:inline-flex;align-items:center;gap:6px;cursor:pointer;flex-shrink:0;';
            scanBtn.title = 'مسح باركود أو QR أي سند للتحقق منه أو فتحه فوراً (Alt+B)';
            scanBtn.innerHTML = '<span>📷</span> <span>مسح باركود</span>';
            scanBtn.onclick = () => window.openVoucherBarcodeScannerModal();
            mcpTopRow.appendChild(scanBtn);
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.altKey && (e.key === 'b' || e.key === 'B' || e.key === 'لا')) {
            e.preventDefault();
            window.openVoucherBarcodeScannerModal();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(injectSuiteButtons, 350));
    } else {
        setTimeout(injectSuiteButtons, 350);
    }
})();
