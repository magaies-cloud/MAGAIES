(function() {
        window._hubShowInUsd = false;
        window.getHubExchangeRate = function() {
            const elRate = document.getElementById('hdr-exchange-rate');
            if (elRate && parseFloat(elRate.value) > 1) return parseFloat(elRate.value);
            const saved = parseFloat(localStorage.getItem('magaies_usd_exchange_rate') || localStorage.getItem('usd_exchange_rate') || '2650');
            return (saved && saved > 1) ? saved : 2650;
        };
        window.toggleHubSummaryCurrency = function() {
            window._hubShowInUsd = !window._hubShowInUsd;
            if (typeof filterHubTransactions === 'function') filterHubTransactions();
            else if (typeof loadHubTransactions === 'function') loadHubTransactions();
        };
        // 1. Inline Math Calculator (12*24 -> 288) in all numeric inputs (0ms)
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== 'Tab') return;
            const el = e.target;
            if (!el || el.tagName !== 'INPUT' || el.type === 'date') return;
            const raw = String(el.value || '').trim();
            if (/^[0-9.,+\-*/()\s]+$/.test(raw) && /[+\-*/]/.test(raw.slice(1))) {
                try {
                    const expr = raw.replace(/,/g, '');
                    const val = Function('"use strict";return (' + expr + ')')();
                    if (typeof val === 'number' && isFinite(val)) {
                        el.value = Math.round(val * 100) / 100;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } catch (_) {}
            }
        }, true);
        // 2. Privacy Eye (****), Split-Screen (50/50), AI Executive CFO Summary & Direct Appearance Button
        window.togglePurchasePrivacyMask = function() {
            document.body.classList.toggle('pur-privacy-masked');
            if (!document.getElementById('pur-privacy-mask-css')) {
                const st = document.createElement('style');
                st.id = 'pur-privacy-mask-css';
                st.textContent = 'body.pur-privacy-masked .fin-val, body.pur-privacy-masked .total-val, body.pur-privacy-masked #t-grand-dynamic-loc, body.pur-privacy-masked #t-grand-dynamic-fc, body.pur-privacy-masked #hub-summary-bar strong { filter: blur(7px); transition: filter 0.2s; } body.pur-privacy-masked .fin-val:hover, body.pur-privacy-masked .total-val:hover { filter: none; }';
                document.head.appendChild(st);
            }
        };
        window.togglePurchaseSplitScreen = function() {
            let sp = document.getElementById('pur-split-screen-pane');
            if (sp) { sp.remove(); document.body.style.width = ''; return; }
            sp = document.createElement('div');
            sp.id = 'pur-split-screen-pane';
            sp.style.cssText = 'position:fixed;top:0;left:0;width:48vw;height:100dvh;z-index:999990;background:#0f172a;border-right:3px solid #38bdf8;box-shadow:12px 0 35px rgba(0,0,0,0.75);display:flex;flex-direction:column;direction:rtl;';
            sp.innerHTML = `<div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:#1e293b;border-bottom:2px solid #38bdf8;gap:6px;"><span style="color:#fde047;font-weight:900;font-size:0.86rem;">◫ الشاشة المزدوجة الحية</span><select onchange="document.getElementById('pur-split-iframe').src=this.value" style="background:#0b1120;color:#fff;border:1.5px solid #38bdf8;border-radius:6px;padding:3px 8px;font-weight:900;font-size:0.8rem;"><option value="account-statement.php">📊 كشف الحساب</option><option value="purchase-receipt.php">🚛 استلام البضاعة (GRN)</option><option value="sales.php">🛒 شاشة المبيعات</option><option value="master-data.php">🗂️ البيانات الأساسية</option><option value="inbound-Single-Receipt.php">🏦 إشعارات بنكك</option></select><button type="button" onclick="window.togglePurchaseSplitScreen()" style="background:#dc2626;color:#fff;border:none;border-radius:6px;padding:3px 9px;font-weight:900;cursor:pointer;">✕ إغلاق</button></div><iframe id="pur-split-iframe" src="account-statement.php" style="flex:1;width:100%;border:none;background:#fff;"></iframe>`;
            document.body.appendChild(sp);
        };
        window.runPurchaseAiExecutiveSummary = function() {
            const rows = document.querySelectorAll('#hub-transactions-body tr');
            const sumTxt = (document.getElementById('hub-summary-bar') || {}).innerText || '';
            const msg = `📊 تحليل المدير المالي الذكي (AI CFO): إجمالي الحركات الظاهرة في السجل (${rows.length}) حركة. ${sumTxt}. التوصية الذكية: راقب لمبة رادار الأسعار الحمراء في الفاتورة لتجنب ارتفاع التكلفة، واستخدم زر الذكاء الاصطناعي للأسعار لضبط هامش البيع تلقائياً.`;
            if (typeof window.showToast === 'function') window.showToast(msg, 'info', 6500);
            if (typeof window.speakMdExplanationPanelText === 'function') window.speakMdExplanationPanelText(msg);
        };
        window.openPurchaseAlSajanaModal = function() {
            const old = document.getElementById('pur-alsajana-modal');
            if (old) old.remove();
            const invTot = (document.getElementById('grand-total-display') || {}).textContent || '0';
            const totNum = parseFloat(String(invTot).replace(/,/g, '')) || 0;
            const m = document.createElement('div');
            m.id = 'pur-alsajana-modal';
            m.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(2,6,23,0.82);display:flex;align-items:center;justify-content:center;padding:16px;direction:rtl;font-family:Cairo,Tajawal,sans-serif;';
            m.onclick = (e) => { if (e.target === m) m.remove(); };
            m.innerHTML = `<div style="width:min(520px,94vw);background:#0f172a;border:2px solid #fbbf24;border-radius:14px;padding:16px 18px;color:#fff;box-shadow:0 24px 60px rgba(0,0,0,0.9);">
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(251,191,36,0.3);padding-bottom:8px;margin-bottom:10px;">
                    <strong style="color:#fde047;font-size:1rem;font-weight:900;">🏗️ معيار السجانة وحاسبة فرق البسكول</strong>
                    <button type="button" onclick="document.getElementById('pur-alsajana-modal').remove()" style="background:#dc2626;color:#fff;border:none;border-radius:7px;width:28px;height:28px;font-weight:900;cursor:pointer;">✕</button>
                </div>
                <div style="background:rgba(180,83,9,0.22);border:1px solid rgba(251,191,36,0.45);border-radius:9px;padding:8px 10px;font-size:0.84rem;font-weight:800;color:#fef3c7;margin-bottom:12px;">معيار حديد السجانة: الطن العددي = 135 سيخة | الطن الوزني = 140 سيخة | الشيلة = 15 سيخة</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:10px;">
                    <div><label style="font-size:0.75rem;color:#94a3b8;font-weight:800;">الوزن الدفتري (طن)</label><input id="pur-bsk-book" type="number" step="0.01" value="10" style="width:100%;height:34px;border-radius:7px;border:1.4px solid #38bdf8;background:#020617;color:#fff;text-align:center;font-weight:900;"></div>
                    <div><label style="font-size:0.75rem;color:#94a3b8;font-weight:800;">وزن البسكول الفعلي</label><input id="pur-bsk-act" type="number" step="0.01" value="10" style="width:100%;height:34px;border-radius:7px;border:1.4px solid #fbbf24;background:#020617;color:#fde047;text-align:center;font-weight:900;"></div>
                    <div><label style="font-size:0.75rem;color:#94a3b8;font-weight:800;">سعر الطن</label><input id="pur-bsk-prc" type="number" step="1" value="${totNum > 0 ? Math.round(totNum / 10) : 0}" style="width:100%;height:34px;border-radius:7px;border:1.4px solid #34d399;background:#020617;color:#6ee7b7;text-align:center;font-weight:900;"></div>
                </div>
                <div id="pur-bsk-out" style="background:rgba(15,23,42,0.85);border:1px solid rgba(56,189,248,0.4);border-radius:9px;padding:9px 12px;font-size:0.86rem;font-weight:900;color:#7dd3fc;text-align:center;">أدخل الأوزان لحساب فرق البسكول والأثر المالي فوراً</div>
            </div>`;
            document.body.appendChild(m);
            const calc = () => {
                const bk = parseFloat(document.getElementById('pur-bsk-book').value) || 0;
                const ac = parseFloat(document.getElementById('pur-bsk-act').value) || 0;
                const pr = parseFloat(document.getElementById('pur-bsk-prc').value) || 0;
                const df = Math.round((ac - bk) * 1000) / 1000;
                const fv = Math.round(df * pr);
                const c = df === 0 ? '#34d399' : (df > 0 ? '#38bdf8' : '#f87171');
                document.getElementById('pur-bsk-out').innerHTML = `<span style="color:${c};">فرق الوزن: ${df > 0 ? '+' : ''}${df} طن | الأثر المالي: ${fv.toLocaleString()}</span>`;
            };
            ['pur-bsk-book', 'pur-bsk-act', 'pur-bsk-prc'].forEach(id => document.getElementById(id).addEventListener('input', calc));
            calc();
        };
        document.addEventListener('DOMContentLoaded', function() {
            /* pur-hub-sovereign-tools removed for clean layout */
        });
    })();
