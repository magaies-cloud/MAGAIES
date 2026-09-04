// js/chatbot.js

(function() {
    if (document.getElementById('magaees-chatbot-container')) return;

    // Chatbot Styles
    const style = document.createElement('style');
    style.innerHTML = `
        #magaees-chatbot-container { position: fixed; bottom: 20px; left: 20px; z-index: 99999; font-family: 'Tajawal', 'Inter', sans-serif; direction: rtl; }
        #chatbot-fab { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #0ea5e9); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; cursor: pointer; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: none; outline: none; }
        #chatbot-fab:hover { transform: scale(1.1); }
        #chatbot-window { position: absolute; bottom: 80px; left: 0; width: 350px; height: 500px; max-height: 80vh; background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; flex-direction: column; overflow: hidden; opacity: 0; pointer-events: none; transform: translateY(20px) scale(0.95); transform-origin: bottom left; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        #chatbot-window.open { opacity: 1; pointer-events: all; transform: translateY(0) scale(1); }
        #chatbot-header { background: linear-gradient(135deg, #0ea5e9, #0284c7); padding: 15px 20px; color: white; display: flex; align-items: center; justify-content: space-between; }
        #chatbot-header .title-area { display: flex; align-items: center; gap: 10px; }
        #chatbot-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
        #chatbot-header p { margin: 0; font-size: 0.8rem; opacity: 0.9; }
        #chatbot-close { background: none; border: none; color: white; font-size: 20px; cursor: pointer; opacity: 0.8; transition: 0.2s; }
        #chatbot-close:hover { opacity: 1; }
        #chatbot-body { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: transparent; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent; }
        #chatbot-body::-webkit-scrollbar { width: 6px; }
        #chatbot-body::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.2); border-radius: 10px; }
        .chat-msg { max-width: 85%; padding: 10px 14px; border-radius: 12px; font-size: 0.95rem; line-height: 1.5; word-wrap: break-word; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .chat-msg.bot { align-self: flex-start; background: rgba(15, 23, 42, 0.6); color: #f1f5f9; border: 1px solid rgba(255,255,255,0.05); border-bottom-right-radius: 4px; }
        .chat-msg.user { align-self: flex-end; background: #0ea5e9; color: white; border-bottom-left-radius: 4px; }
        .chat-action-btn { display: inline-block; margin-top: 8px; padding: 6px 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; transition: 0.2s; border: 1px solid rgba(56, 189, 248, 0.3); border: none; cursor: pointer; text-align: center; }
        .chat-action-btn:hover { background: #38bdf8; color: #0f172a; }
        .chat-whatsapp-btn { background: rgba(37, 211, 102, 0.15); color: #25d366; border: 1px solid rgba(37, 211, 102, 0.3); }
        .chat-whatsapp-btn:hover { background: #25d366; color: #fff; }
        #chatbot-input-area { display: flex; padding: 12px; background: rgba(15, 23, 42, 0.8); border-top: 1px solid rgba(255,255,255,0.05); gap: 8px; align-items: center; }
        #chatbot-input { flex: 1; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 20px; padding: 10px 15px; font-size: 0.95rem; outline: none; transition: 0.3s; font-family: inherit; }
        #chatbot-input:focus { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56,189,248,0.2); }
        #chatbot-mic { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-size: 1.1rem; outline: none; }
        #chatbot-mic:hover { background: rgba(255,255,255,0.1); color: #fff; }
        #chatbot-mic.recording { background: #ef4444 !important; color: white !important; animation: micPulse 1.2s infinite; border-color: #ef4444; }
        #chatbot-send { width: 38px; height: 38px; border-radius: 50%; background: #0ea5e9; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-size: 1.1rem; outline: none; }
        #chatbot-send:hover { background: #0284c7; transform: scale(1.05); }
        .chat-options { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .chat-pill { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #cbd5e1; padding: 6px 12px; border-radius: 15px; font-size: 0.8rem; cursor: pointer; transition: 0.2s; }
        .chat-pill:hover { background: rgba(56,189,248,0.2); color: #38bdf8; border-color: #38bdf8; }
        @keyframes micPulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        @media (max-width: 480px) { #chatbot-window { width: calc(100vw - 40px); bottom: 85px; height: 450px; } }
    `;
    document.head.appendChild(style);

    const isToolsPage = window.location.pathname.includes('engineering_tools.php');
    const welcomeHtml = isToolsPage ? `
        أهلاً بك في نظام السجانة الذكي! 🌟<br>
        أنا مرتبط ببيانات النظام الحية، وموسوعة فتاوى البيوع والمعاملات (4,500 فتوى)، وجميع الأدوات المساعدة. جرب أن تسألني:
        <div class="chat-options">
            <span class="chat-pill" onclick="sendPill('حكم البيع بالتقسيط؟')">حكم بيع التقسيط 💳</span>
            <span class="chat-pill" onclick="sendPill('ما حكم غرامات التأخير؟')">غرامات التأخير ⚠️</span>
            <span class="chat-pill" onclick="sendPill('حاسبة زكاة المال')">حاسبة الزكاة 💰</span>
            <span class="chat-pill" onclick="sendPill('سويفت كود بنك الخرطوم')">سويفت بنك الخرطوم 🏦</span>
            <span class="chat-pill" onclick="sendPill('كم السيولة النقدية؟')">كم السيولة؟ 💵</span>
            <span class="chat-pill" onclick="sendPill('آية الدين والمداينة')">آية الدين 📖</span>
            <span class="chat-pill" onclick="sendPill('أحاديث البيوع الصحيحة')">أحاديث البيوع 📜</span>
            <span class="chat-pill" onclick="sendPill('حساب وزن سيخ 12 مم')">أوزان الحديد 🏗️</span>
        </div>
    ` : `
        أهلاً بك! 🌟<br>أنا مرتبط بقاعدة بيانات النظام مباشرة الآن. جرب أن تسألني:
        <div class="chat-options">
            <span class="chat-pill" onclick="sendPill('كم السيولة؟')">كم السيولة؟ 💰</span>
            <span class="chat-pill" onclick="sendPill('كم مديونية عميل محمد؟')">مديونية عميل...</span>
            <span class="chat-pill" onclick="sendPill('كم عدد فواتير اليوم؟')">فواتير اليوم 📊</span>
            <span class="chat-pill" onclick="sendPill('أريد شاشة الكاشير')">شاشة الكاشير 🛒</span>
            <span class="chat-pill" onclick="sendPill('حكم البيع بالتقسيط؟')">فتاوى البيوع ⚖️</span>
            <span class="chat-pill" onclick="sendPill('من هم أكبر المدينين؟')">أكبر المديونيات ⚠️</span>
        </div>
    `;

    const placeholderText = isToolsPage ? 
        'اسأل عن أي فتوى، آية، حديث، سويفت، أو بيانات النظام...' : 
        'اسأل عن أي مديونية أو فاتورة أو فتوى بيوع...';

    const container = document.createElement('div');
    container.id = 'magaees-chatbot-container';
    
    container.innerHTML = `
        <button id="chatbot-fab" title="المساعدة والدعم">🤖</button>
        <div id="chatbot-window">
            <div id="chatbot-header">
                <div class="title-area">
                    <span style="font-size: 24px;">🤖</span>
                    <div>
                        <h3>المساعد الذكي للبيانات</h3>
                        <p>يقرأ بيانات النظام الحية للإجابة!</p>
                    </div>
                </div>
                <button id="chatbot-close">✕</button>
            </div>
            <div id="chatbot-body">
                <div class="chat-msg bot">
                    ${welcomeHtml}
                </div>
            </div>
            <div id="chatbot-input-area">
                <input type="text" id="chatbot-input" placeholder="${placeholderText}" autocomplete="off">
                <button id="chatbot-mic" title="تحدث بالصوت">🎤</button>
                <button id="chatbot-send">➤</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(container);

    const fab = document.getElementById('chatbot-fab');
    const win = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const body = document.getElementById('chatbot-body');

    let isOpen = false;

    function toggleChat() {
        isOpen = !isOpen;
        if(isOpen) { win.classList.add('open'); input.focus(); } 
        else { win.classList.remove('open'); }
    }

    fab.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    // --- DATA QUERY HELPERS ---
    function parseNum(str) { return parseFloat(String(str || '0').replace(/,/g, '')) || 0; }

    function calculatePartnerBalance(partnerName) {
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
        let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
        
        let partner = custs.find(c => c.name && c.name.includes(partnerName)) || 
                      sups.find(s => s.name && s.name.includes(partnerName));
                      
        if (!partner) return null;
        
        const pId = partner.id;
        const pName = partner.name;
        
        let debit = 0; let credit = 0;

        const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1') || '[]');
        receipts.forEach(v => {
            if(v.fromAccount === pId || v.fromAccount === pName) credit += parseNum(v.amountLocal);
            if(v.toAccount === pId || v.toAccount === pName) debit += parseNum(v.amountLocal);
        });

        const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
        payments.forEach(v => {
            if(v.toAccount === pId || v.toAccount === pName) debit += parseNum(v.amountLocal);
            if(v.fromAccount === pId || v.fromAccount === pName) credit += parseNum(v.amountLocal);
        });

        const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
        jvs.forEach(v => {
            if (v.lines) {
                v.lines.forEach(l => {
                    if(l.account === pId || l.account === pName) {
                        debit += parseNum(l.debit);
                        credit += parseNum(l.credit);
                    }
                });
            }
        });

        const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        sales.forEach(inv => {
            const invPartner = inv.customerId || inv.customer;
            if(invPartner === pId || invPartner === pName) {
                let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
                if (total === 0 && inv.items) {
                    total = inv.items.reduce((s, i) => s + (parseNum(i.total) || parseNum(i.quantity) * parseNum(i.price)), 0);
                    total += parseNum(inv.laborCost||0) + parseNum(inv.transportCost||0) + parseNum(inv.additions||0) + parseNum(inv.taxAmt||0) - parseNum(inv.discountAmt||0);
                }
                debit += total;
                const paid = parseNum(inv.paidAmount || inv.paid);
                if(inv.paymentMethod !== 'credit' && paid === 0) credit += total;
                else if(paid > 0) credit += paid;
            }
        });

        const purchases = JSON.parse(localStorage.getItem('erp_purchase_invoices_v1') || '[]');
        purchases.forEach(inv => {
            const invPartner = inv.supplierId || inv.supplier || inv.customer;
            if(invPartner === pId || invPartner === pName) {
                let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
                if (total === 0 && inv.items) {
                    total = inv.items.reduce((s, i) => s + (parseNum(i.total) || parseNum(i.quantity) * parseNum(i.price)), 0);
                    total += parseNum(inv.laborCost||0) + parseNum(inv.transportCost||0) + parseNum(inv.additions||0) + parseNum(inv.taxAmt||0) - parseNum(inv.discountAmt||0);
                }
                credit += total;
                const paid = parseNum(inv.paidAmount || inv.paid);
                if(inv.paymentMethod !== 'credit' && paid === 0) debit += total;
                else if(paid > 0) debit += paid;
            }
        });

        let net = debit - credit;
        return { name: pName, balance: net };
    }

    function calculateSafeBalance(safeName) {
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
        
        let account = safes.find(s => s.name && s.name.includes(safeName)) || 
                      banks.find(b => b.name && b.name.includes(safeName));
                      
        if (!account) return null;
        
        const aId = account.id;
        let balance = parseNum(account.openingBalance || account.balance || 0);

        const receipts = JSON.parse(localStorage.getItem('erp_single_receipts_v1') || '[]');
        receipts.forEach(v => {
            if(v.toAccount === aId) balance += parseNum(v.amountLocal);
            if(v.fromAccount === aId) balance -= parseNum(v.amountLocal);
        });

        const payments = JSON.parse(localStorage.getItem('erp_single_payments_v1') || '[]');
        payments.forEach(v => {
            if(v.fromAccount === aId) balance -= parseNum(v.amountLocal);
            if(v.toAccount === aId) balance += parseNum(v.amountLocal);
        });

        const jvs = JSON.parse(localStorage.getItem('erp_journal_vouchers_v1') || '[]');
        jvs.forEach(v => {
            if (v.lines) {
                v.lines.forEach(l => {
                    if(l.account === aId) {
                        balance += parseNum(l.debit);
                        balance -= parseNum(l.credit);
                    }
                });
            }
        });

        return { name: account.name, balance: balance };
    }

    function getItemStock(itemName) {
        const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(x => !x.deleted);
        const item = items.find(i => i.name && i.name.includes(itemName) && !i.deleted);
        if(!item) return null;
        
        let totalStock = 0;
        if(item.actualStock) {
            for(let whId in item.actualStock) {
                totalStock += parseNum(item.actualStock[whId]);
            }
        } else {
            totalStock = parseNum(item.qty || item.quantity || 0);
        }
        
        return { name: item.name, stock: totalStock, unit: item.unit || 'وحدة' };
    }

    function getDailyStats() {
        const d = new Date();
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const dd = String(d.getDate()).padStart(2,'0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        const todaySales = sales.filter(s => s.date === todayStr);
        
        let totalSalesAmt = 0;
        todaySales.forEach(inv => {
            let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
            if (total === 0 && inv.items) {
                total = inv.items.reduce((s, i) => s + (parseNum(i.total) || parseNum(i.quantity) * parseNum(i.price)), 0);
                total += parseNum(inv.laborCost||0) + parseNum(inv.transportCost||0) + parseNum(inv.additions||0) + parseNum(inv.taxAmt||0) - parseNum(inv.discountAmt||0);
            }
            totalSalesAmt += total;
        });

        return { count: todaySales.length, total: totalSalesAmt };
    }

    function getTotalLiquidity() {
        let safes = JSON.parse(localStorage.getItem('erp_safes_v1') || '[]');
        let banks = JSON.parse(localStorage.getItem('erp_banks_v1') || '[]');
        let total = 0;
        safes.forEach(s => { const res = calculateSafeBalance(s.name); if(res) total += res.balance; });
        banks.forEach(b => { const res = calculateSafeBalance(b.name); if(res) total += res.balance; });
        return total;
    }

    function getTotalInventoryValue() {
        let items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(x => !x.deleted);
        let totalValue = 0;
        items.forEach(item => {
            if(item.deleted) return;
            const res = getItemStock(item.name);
            if(res) {
                let cost = parseNum(item.openingValue) / (parseNum(item.openingQty) || 1);
                if (cost === 0 && item.units && item.units[0]) {
                    cost = parseNum(item.units[0].cashPrice) * 0.8; 
                }
                totalValue += res.stock * cost;
            }
        });
        return totalValue;
    }

    function getTopDebtors() {
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
        let debtors = [];
        custs.forEach(c => {
            const res = calculatePartnerBalance(c.name);
            if(res && res.balance > 0) debtors.push({ name: c.name, balance: res.balance });
        });
        debtors.sort((a,b) => b.balance - a.balance);
        return debtors.slice(0, 3);
    }
    
    function getTotalSalesAllTime() {
        const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        let totalSalesAmt = 0;
        sales.forEach(inv => {
            let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
            if (total === 0 && inv.items) {
                total = inv.items.reduce((s, i) => s + (parseNum(i.total) || parseNum(i.quantity) * parseNum(i.price)), 0);
                total += parseNum(inv.laborCost||0) + parseNum(inv.transportCost||0) + parseNum(inv.additions||0) + parseNum(inv.taxAmt||0) - parseNum(inv.discountAmt||0);
            }
            totalSalesAmt += total;
        });
        return totalSalesAmt;
    }

    function getLowStockItems() {
        let items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]').filter(x => !x.deleted);
        let lowStock = [];
        items.forEach(item => {
            if(item.deleted) return;
            const res = getItemStock(item.name);
            if(res && res.stock <= (parseNum(item.minLimit) || 0)) {
                lowStock.push({ name: item.name, stock: res.stock, limit: parseNum(item.minLimit) || 0 });
            }
        });
        return lowStock;
    }

    function getUnpaidInvoices() {
        const sales = JSON.parse(localStorage.getItem('erp_sales_invoices_v1') || '[]');
        let count = 0;
        let totalUnpaid = 0;
        sales.forEach(inv => {
            if (inv.paymentMethod === 'credit') {
                let total = parseNum(inv.totalAmount || inv.total || inv.grandTotal);
                if (total === 0 && inv.items) {
                    total = inv.items.reduce((s, i) => s + (parseNum(i.total) || parseNum(i.quantity) * parseNum(i.price)), 0);
                    total += parseNum(inv.laborCost||0) + parseNum(inv.transportCost||0) + parseNum(inv.additions||0) + parseNum(inv.taxAmt||0) - parseNum(inv.discountAmt||0);
                }
                const paid = parseNum(inv.paidAmount || inv.paid || 0);
                if (paid < total) {
                    count++;
                    totalUnpaid += (total - paid);
                }
            }
        });
        return { count, totalUnpaid };
    }

    function getDailyCash(type) {
        const d = new Date();
        const todayStr = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const data = JSON.parse(localStorage.getItem(type === 'receipt' ? 'erp_single_receipts_v1' : 'erp_single_payments_v1') || '[]');
        const todayData = data.filter(v => v.date === todayStr);
        let total = todayData.reduce((sum, v) => sum + parseNum(v.amountLocal), 0);
        return { count: todayData.length, total };
    }

    // --- NLP RULES ---
    const dynamicKb = [
        {
            regex: /(?:ما|ماهي|ما هي) (?:النواقص|البضاعة المنتهية|الاصناف المنتهية|نواقص المخزون|أوشكت على النفاذ)/i,
            handler: () => {
                const low = getLowStockItems();
                if(low.length === 0) return "مخزونك ممتاز! لا توجد نواقص حالياً. ✅";
                let res = `⚠️ <b>تنبيه! هناك ${low.length} صنف وصل للحد الأدنى:</b><br><ul style="padding-right: 20px; margin-top:10px;">`;
                low.slice(0, 5).forEach(l => { res += `<li><b>${l.name}</b>: متوفر <span style="color:#f87171;">${l.stock}</span> (الحد: ${l.limit})</li>`; });
                if(low.length > 5) res += `<li>... والمزيد.</li>`;
                res += `</ul>`;
                return res;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي) (?:الفواتير غير المدفوعة|فواتير غير مدفوعة|ديون السوق|الفواتير الآجلة|فواتير آجلة)/i,
            handler: () => {
                const unpaid = getUnpaidInvoices();
                if(unpaid.count === 0) return "ممتاز! لا توجد فواتير آجلة غير مسددة. 🎉";
                return `🧾 <b>تقرير الفواتير غير المدفوعة:</b><br><br>يوجد عدد <b>${unpaid.count}</b> فاتورة آجلة بانتظار السداد.<br>إجمالي المبالغ المتبقية: <span style="font-size:1.5rem;font-weight:bold;color:#f59e0b;">${unpaid.totalUnpaid.toLocaleString()} SDG</span>`;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي) (?:منصرفات اليوم|مصروفات اليوم|المنصرفات اليومية|المصروفات اليومية)/i,
            handler: () => {
                const expenses = getDailyCash('payment');
                return `💸 <b>منصرفات اليوم:</b><br><br>تم تسجيل <b>${expenses.count}</b> سند صرف.<br>بإجمالي: <span style="font-size:1.5rem;font-weight:bold;color:#ef4444;">${expenses.total.toLocaleString()} SDG</span>`;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي) (?:مقبوضات اليوم|ايرادات اليوم|المقبوضات اليومية|الإيرادات اليومية)/i,
            handler: () => {
                const receipts = getDailyCash('receipt');
                return `💰 <b>مقبوضات اليوم:</b><br><br>تم تسجيل <b>${receipts.count}</b> سند قبض.<br>بإجمالي: <span style="font-size:1.5rem;font-weight:bold;color:#10b981;">${receipts.total.toLocaleString()} SDG</span>`;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي|ما هو) (?:السيولة|النقدية|إجمالي النقدية|رصيد الشركة)/i,
            handler: () => {
                const total = getTotalLiquidity();
                return `💰 <b>السيولة النقدية الكلية في النظام:</b><br><span style="font-size:1.5rem;font-weight:bold;color:#4ade80;">${total.toLocaleString('en-US', {minimumFractionDigits:2})} SDG</span>`;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي) (?:قيمة المخزون|قيمة البضاعة|إجمالي المخزون)/i,
            handler: () => {
                const total = getTotalInventoryValue();
                return `📦 <b>القيمة التقديرية للمخزون الحالي:</b><br><span style="font-size:1.5rem;font-weight:bold;color:#38bdf8;">${total.toLocaleString('en-US', {minimumFractionDigits:2})} SDG</span>`;
            }
        },
        {
            regex: /(?:كم|ماهي|ما هي) (?:المبيعات الكلية|إجمالي المبيعات|كل المبيعات)/i,
            handler: () => {
                const total = getTotalSalesAllTime();
                return `📈 <b>إجمالي المبيعات (منذ البداية):</b><br><span style="font-size:1.5rem;font-weight:bold;color:#a855f7;">${total.toLocaleString('en-US', {minimumFractionDigits:2})} SDG</span>`;
            }
        },
        {
            regex: /(?:من|ماهي|ما هي) (?:أكبر المديونيات|أكبر العملاء|الديون الكبيرة|أكثر المدينين|اكبر المدينين)/i,
            handler: () => {
                const top = getTopDebtors();
                if(top.length === 0) return "لا يوجد عملاء عليهم مديونيات حالياً. 🎉";
                let res = `⚠️ <b>أكبر 3 عملاء عليهم مديونية:</b><br><ol style="padding-right: 20px; margin-top:10px;">`;
                top.forEach(t => { res += `<li><b>${t.name}</b>: <span style="color:#f87171; font-weight:bold;">${t.balance.toLocaleString()} SDG</span></li>`; });
                res += `</ol>`;
                return res;
            }
        },
        {
            // Matches: كم مديونية عميل محمد؟ / ما هو حساب شركة الأمل / رصيد مورد أحمد
            regex: /(?:كم|ماهي|ما هي|ما هو) (?:مديونية|حساب|رصيد|ديون) (?:عميل|مورد|شركة)?\s*(.+)/i,
            handler: (match) => {
                let partnerName = match[1].trim().replace('؟', '').replace('?', '').trim();
                if(partnerName.startsWith('العميل')) partnerName = partnerName.replace('العميل', '').trim();
                if(partnerName.startsWith('المورد')) partnerName = partnerName.replace('المورد', '').trim();
                
                if(!partnerName) return "يرجى تحديد اسم العميل أو المورد للبحث عنه، مثلاً: كم مديونية عميل محمد؟";
                
                const result = calculatePartnerBalance(partnerName);
                if(!result) return `عذراً، لم أتمكن من العثور على أي عميل أو مورد يطابق الاسم "<b>${partnerName}</b>" في قاعدة البيانات.`;
                
                const bal = result.balance;
                let status = bal > 0 ? "مدين لنا (عليه أموال)" : (bal < 0 ? "دائن علينا (نحن مدينون له)" : "رصيده مصفر (0)");
                const amount = Math.abs(bal).toLocaleString('en-US', {minimumFractionDigits:2});
                
                return `📊 نتيجة البحث الحية من النظام:<br><br><b>الحساب:</b> ${result.name}<br><b>الرصيد:</b> <span style="font-size:1.4rem;font-weight:bold;color:${bal>0?'#f87171':(bal<0?'#4ade80':'#cbd5e1')};">${amount}</span><br><b>الحالة:</b> ${status}`;
            }
        },
        {
            // Matches: كم فواتير اليوم / مبيعات اليوم / كم عدد الفواتير
            regex: /(?:كم|ما هي|كم عدد) (?:فواتير|مبيعات|الفواتير) (?:اليوم|اليومية)/i,
            handler: () => {
                const stats = getDailyStats();
                return `📈 إحصائيات المبيعات الفورية لتاريخ اليوم:<br><br>تم إصدار <b>${stats.count}</b> فاتورة مبيعات اليوم.<br>بإجمالي مبلغ: <b style="color:#38bdf8; font-size:1.2rem;">${stats.total.toLocaleString('en-US', {minimumFractionDigits:2})}</b>`;
            }
        },
        {
            // Matches: كم رصيد الخزينة / رصيد حساب البنك / بنك الخرطوم
            regex: /(?:كم|ماهي|ما هي|ما هو) (?:رصيد|حساب|مبلغ) (?:خزينة|الخزينة|صندوق|الصندوق|بنك|البنك)?\s*(.+)/i,
            handler: (match) => {
                let safeName = match[1].trim().replace('؟', '').replace('?', '').trim();
                if(!safeName || safeName.toLowerCase() === 'الرئيسية' || safeName === 'الخزينة') safeName = 'الخزينة الرئيسية';
                
                const result = calculateSafeBalance(safeName);
                if(!result) return `عذراً، لم أتمكن من العثور على أي خزينة أو بنك باسم "<b>${safeName}</b>".`;
                
                const bal = result.balance;
                const amount = bal.toLocaleString('en-US', {minimumFractionDigits:2});
                
                return `💰 الرصيد النقدي في <b>${result.name}</b> هو:<br><span style="font-size:1.5rem;font-weight:bold;color:#4ade80;">${amount}</span>`;
            }
        },
        {
            // Matches: كم مخزون / كم عدد / رصيد منتج
            regex: /(?:كم|ماهو|ما هو) (?:مخزون|رصيد|عدد|كمية) (?:منتج|صنف|مادة|المنتج|الصنف)?\s*(.+)/i,
            handler: (match) => {
                let itemName = match[1].trim().replace('؟', '').replace('?', '').trim();
                
                const result = getItemStock(itemName);
                if(!result) return `عذراً، لم أتمكن من العثور على أي منتج يطابق "<b>${itemName}</b>".`;
                
                const stock = result.stock;
                let color = stock > 10 ? '#4ade80' : (stock > 0 ? '#facc15' : '#f87171');
                const amount = stock.toLocaleString('en-US');
                
                return `📦 المخزون الحالي لـ <b>${result.name}</b>:<br><span style="font-size:1.5rem;font-weight:bold;color:${color};">${amount} ${result.unit}</span>`;
            }
        }
    ];

    const staticKb = [
        {
            keywords: ['سلام', 'السلام', 'مرحبا', 'أهلا', 'اهلين', 'مرحب', 'كيف حالك', 'شخبارك', 'مسا', 'صباح'],
            response: 'وعليكم السلام ورحمة الله وبركاته! 👋 أهلاً بك. أنا المساعد الذكي للنظام، كيف يمكنني مساعدتك اليوم؟'
        },
        {
            keywords: ['كاشير', 'نقطة بيع', 'بيع مباشر', 'شاشة الكاشير'],
            response: 'للدخول إلى شاشة نقطة البيع (POS) المباشرة السريعة، يرجى الانتقال إلى <b>نقطة البيع (الكاشير)</b>.',
            link: 'direct-sales.php', linkText: '🛒 فتح شاشة الكاشير'
        },
        {
            keywords: ['رواتب', 'راتب', 'موظف', 'موظفين', 'سلفيات', 'خصومات', 'حضور', 'شؤون الموظفين'],
            response: 'لإدارة الموظفين والرواتب والحضور والانصراف والسلفيات، يرجى الذهاب إلى شاشة <b>شؤون الموظفين والرواتب</b>.',
            link: 'payroll.php', linkText: '👥 شؤون الموظفين'
        },
        {
            keywords: ['تسليم', 'مذكرة تسليم', 'بضاعة محجوزة', 'حجز بضاعة', 'محجوزات'],
            response: 'لإدارة المبيعات المحجوزة وإصدار مذكرات تسليم البضاعة لاحقاً، يرجى التوجه إلى قسم <b>المبيعات المحجوزة والتسليم</b>.',
            link: 'reserved-sales.php', linkText: '🚚 المبيعات المحجوزة والتسليم'
        },
        {
            keywords: ['دفتر استاذ', 'ميزان مراجعة', 'ارباح وخسائر', 'أرباح وخسائر', 'قيود محاسبية', 'ميزانية عمومية', 'دفتر الأستاذ'],
            response: 'يمكنك الوصول إلى التقارير المالية المتقدمة كدفتر الأستاذ العام وميزان المراجعة والأرباح والخسائر من <b>التقارير المالية</b>.',
            link: 'financial-reports.php', linkText: '📊 التقارير المالية (المتقدمة)'
        },
        {
            keywords: ['باركود', 'طباعة باركود', 'ليبل', 'ملصقات'],
            response: 'لطباعة ملصقات الباركود للمنتجات للصقها على البضاعة، يمكنك استخدام شاشة <b>طباعة الباركود</b> المخصصة.',
            link: 'barcode-printer.php', linkText: '🖨️ طباعة الباركود'
        },
        {
            keywords: ['كيف اضيف مستخدم', 'تغيير باسورد', 'تغيير كلمة المرور', 'صلاحيات', 'إدارة المستخدمين', 'تعديل صلاحية'],
            response: 'لإدارة المستخدمين، إعطاء صلاحيات الموظفين، أو تغيير كلمات المرور، يرجى الانتقال إلى شاشة <b>المستخدمين والصلاحيات</b>.',
            link: 'users-roles.php', linkText: '🔐 المستخدمين والصلاحيات'
        },
        {
            keywords: ['تغيير الضريبة', 'تعديل اسم الشركة', 'تغيير اللوجو', 'شعار الشركة', 'نسبة الضريبة', 'الرقم الضريبي'],
            response: 'لتغيير بيانات الشركة، اللوجو، أو تعديل نسبة الضريبة الافتراضية، يرجى زيارة شاشة <b>الإعدادات العامة</b>.',
            link: 'settings.php', linkText: '⚙️ الإعدادات العامة'
        },
        {
            keywords: ['كيف اخذ نسخة', 'مسح البيانات', 'نسخة احتياطية', 'باك اب', 'backup', 'استعادة البيانات'],
            response: 'لأخذ نسخة احتياطية (باك أب) لضمان أمان بياناتك، أو للقيام بمسح بعض البيانات القديمة، يرجى التوجه لشاشة <b>النسخ الاحتياطي</b>.',
            link: 'backup.php', linkText: '💾 النسخ الاحتياطي وإدارة البيانات'
        },
        {
            keywords: ['شجرة الحسابات', 'كيف اضيف حساب', 'اضافة حساب محاسبي', 'الدليل المحاسبي'],
            response: 'لتعريف حسابات محاسبية جديدة وإدارتها، يرجى التوجه إلى <b>الدورة المحاسبية</b> وإدارة شجرة الحسابات.',
            link: 'accounting-workflow.php', linkText: '🌳 الدورة المحاسبية وشجرة الحسابات'
        },
        {
            keywords: ['مندوب', 'مندوبين', 'شريك', 'شركاء', 'وسطاء', 'عمولة', 'عمولات'],
            response: 'لإدارة الشركاء والمندوبين وحساب عمولاتهم من المبيعات، يرجى الذهاب إلى شاشة <b>المندوبين والشركاء</b>.',
            link: 'partners.php', linkText: '🤝 المندوبين والشركاء'
        },
        {
            keywords: ['من انت', 'من أنت', 'وظيفتك', 'بتعمل ايه', 'فائدتك', 'مين انت', 'ما هو عملك'],
            response: 'أنا المساعد الذكي الخاص بك! 🤖<br>وظيفتي هي قراءة البيانات الحية من النظام مثل (أرصدة العملاء، الخزينة، والمخزون) للإجابة على استفساراتك فوراً، بالإضافة لإرشادك إلى الشاشات الصحيحة داخل النظام.'
        },
        {
            keywords: ['كشف حساب خز', 'كشف خز', 'حركة خزينة', 'حركة بنك', 'كشف بنك', 'كشف حساب بنك', 'حساب الخزنة'],
            response: 'لاستخراج كشف حساب تفصيلي لحركة الخزينة أو البنك، يرجى الانتقال إلى شاشة <b>كشف حساب نقدية / بنك</b>.',
            link: 'cash-bank-statement.php', linkText: '🏦 كشف حساب خزينة/بنك'
        },
        {
            keywords: ['شيك', 'شيكات', 'الشيكات'],
            response: 'لإدارة الشيكات (الصادرة والواردة، التحصيل، والمرتجع)، يرجى الذهاب إلى شاشة <b>دورة الشيكات</b>.',
            link: 'checks-cycle.php', linkText: '💳 دورة الشيكات'
        },
        {
            keywords: ['فاتور', 'فواتير', 'مبيع', 'جديد', 'كيف اصدر فاتورة', 'مكان الفواتير', 'كيف ابيع'],
            response: 'لإصدار فاتورة مبيعات جديدة للعملاء، يرجى الانتقال إلى قسم <b>فواتير المبيعات</b> من القائمة، ثم الضغط على زر "إنشاء فاتورة جديدة".',
            link: 'sales-invoice.php', linkText: '📄 الذهاب لفواتير المبيعات'
        },
        {
            keywords: ['كيف اضيف صنف', 'مكان اضافة صنف', 'شاشة الاصناف', 'مكان الاصناف', 'صنف جديد', 'منتج جديد', 'اضافة منتج'],
            response: 'لإضافة صنف أو منتج جديد: اذهب إلى شاشة <b>البيانات الأساسية</b> > اختر تبويب <b>إدارة الأصناف والمخزون</b> > ثم اضغط على زر <b>إضافة صنف جديد</b>.',
            link: 'master-data.php', linkText: '📦 الذهاب لتعريف الأصناف'
        },
        {
            keywords: ['كيف اضيف عميل', 'كيف اضيف مورد', 'مكان اضافة عميل', 'مكان اضافة مورد', 'عميل جديد', 'مورد جديد'],
            response: 'لإضافة عميل أو مورد جديد: اذهب إلى شاشة <b>البيانات الأساسية</b> > اختر تبويب <b>إدارة العملاء والموردين</b> > ثم اضغط على زر إضافة عميل/مورد.',
            link: 'master-data.php', linkText: '👥 الذهاب للعملاء والموردين'
        },
        {
            keywords: ['كيف اضيف خزنة', 'كيف اضيف بنك', 'مكان الخزنة', 'اضافة حساب بنكي', 'خزنة جديدة', 'بنك جديد'],
            response: 'لتعريف خزينة أو حساب بنكي جديد: اذهب إلى شاشة <b>البيانات الأساسية</b> > اختر تبويب <b>إدارة النقدية والبنوك</b> > ثم أضف الخزنة أو البنك.',
            link: 'master-data.php', linkText: '🏦 النقدية والبنوك'
        },
        {
            keywords: ['عميل', 'عملاء', 'زبون', 'زباين', 'زبائن', 'مورد', 'موردين', 'شركات'],
            response: 'لإدارة كافة بيانات العملاء والموردين أو تعديل حساباتهم، يمكنك الانتقال إلى <b>البيانات الأساسية</b> (شاشة العملاء والموردين).',
            link: 'master-data.php', linkText: '👥 إدارة العملاء والموردين'
        },
        {
            keywords: ['كشف', 'ارصدة', 'تقرير'],
            response: 'يمكنك استخراج كشوفات الحسابات والعملاء والموردين من قسم <b>التقارير المالية</b>.',
            link: 'partners_reports_hub.php', linkText: '📊 تقارير العملاء والموردين'
        },
        {
            keywords: ['شراء', 'مشتريات', 'طلب', 'مورد'],
            response: 'لإنشاء طلب شراء أو فاتورة مشتريات، يرجى الانتقال إلى قسم <b>المشتريات</b>.',
            link: 'purchase.php', linkText: '🛒 الذهاب للمشتريات'
        },
        {
            keywords: ['اعدادات', 'إعدادات', 'خط', 'حجم الخط', 'الوان', 'ألوان', 'تعديل حجم', 'شركة', 'شعار', 'ضريبة'],
            response: 'لتعديل إعدادات النظام (مثل حجم الخط، الألوان، الشعار، بيانات الشركة، أو إعدادات الضرائب)، يرجى التوجه إلى شاشة <b>الإعدادات</b>.',
            link: 'settings.php', linkText: '⚙️ الذهاب إلى الإعدادات'
        },
        {
            keywords: ['مستخدمين', 'مستخدم', 'صلاحيات', 'باسورد', 'كلمة مرور', 'اضافة مستخدم', 'حذف مستخدم'],
            response: 'لإدارة المستخدمين، تعيين الصلاحيات، أو تغيير كلمات المرور، يمكنك الانتقال إلى شاشة <b>المستخدمين والصلاحيات</b>.',
            link: 'users-roles.php', linkText: '🔐 إدارة المستخدمين'
        },
        {
            keywords: ['نسخ احتياطي', 'باك اب', 'استرجاع', 'بيانات', 'backup'],
            response: 'لأخذ نسخة احتياطية من النظام أو استعادة البيانات، يرجى زيارة قسم <b>النسخ الاحتياطي (Backup)</b>.',
            link: 'backup.php', linkText: '💾 النسخ الاحتياطي'
        },
        {
            keywords: ['كيف اضيف مخزن', 'مكان اضافة مخزن', 'مستودع جديد', 'اضافة مستودع', 'انشاء مستودع', 'مخزن جديد'],
            response: 'لإنشاء مخزن أو مستودع جديد: اذهب إلى شاشة <b>البيانات الأساسية</b> > اختر تبويب <b>إدارة المخازن والمستودعات</b>.',
            link: 'master-data.php', linkText: '🏭 إدارة المخازن'
        },
        {
            keywords: ['مخزن', 'مستودع', 'منتجات', 'اصناف', 'أصناف', 'وحدات', 'خزينة', 'صندوق', 'بنك', 'بنوك'],
            response: 'تتم إدارة الأصناف، المخازن، الخزائن، والبنوك بالكامل من شاشة <b>البيانات الأساسية</b>. (إذا أردت دقة أكثر، جرب أن تسأل: "كيف أضيف صنف؟" أو "كيف أضيف خزنة؟")',
            link: 'master-data.php', linkText: '🗂️ البيانات الأساسية'
        },
        {
            keywords: ['جرد', 'حركة صنف', 'حركة مخزن', 'تقارير المخزون', 'مخزون'],
            response: 'لمشاهدة حركة الأصناف، الجرد، وتقارير المخزون، يرجى التوجه إلى قسم <b>تقارير المخزون</b>.',
            link: 'inventory-report.php', linkText: '📦 تقارير المخزون'
        },
        {
            keywords: ['سند', 'سندات', 'قبض', 'صرف', 'قيد', 'قيود', 'يومية', 'دفتر'],
            response: 'لإنشاء سندات القبض أو الصرف، أو إدخال قيود اليومية، يرجى الذهاب إلى <b>مركز السندات (القيود والسندات)</b>.',
            link: 'vouchers-hub.php', linkText: '🧾 مركز السندات والقيود'
        },
        {
            keywords: ['ميزانية', 'ارباح', 'خسائر', 'أرباح', 'ميزان مراجعة', 'تقارير مالية', 'مالية'],
            response: 'لعرض الميزانية، الأرباح والخسائر، أو ميزان المراجعة، يرجى زيارة <b>مركز التقارير المالية</b>.',
            link: 'financial-reports.php', linkText: '📈 التقارير المالية'
        },
        {
            keywords: ['دعم', 'مساعد', 'واتس', 'مشكلة', 'تواصل'],
            response: 'يسعدنا تواصلك معنا لتقديم الدعم الفني المباشر لحل أي مشكلة تواجهك.',
            link: 'https://wa.me/249113981477', linkText: '💬 تواصل معنا عبر الواتساب', isExternal: true
        }
    ];

    window.sendPill = function(text) {
        if(!isOpen) toggleChat();
        processUserMessage(text);
    };

    function appendMessage(text, sender, btnHtml = '') {
        const msg = document.createElement('div');
        msg.className = 'chat-msg ' + sender;
        msg.innerHTML = text + btnHtml;
        body.appendChild(msg);
        body.scrollTop = body.scrollHeight;
        return msg;
    }

    // ════════════════════════════════════════════════════════════════════════════
    // 🕌 FIQH & ENGINEERING TOOLS KNOWLEDGE ENGINE 🕌
    // ════════════════════════════════════════════════════════════════════════════
    window.openToolSuite = window.openToolSuite || function(suiteId, subTab, searchKeyword, itemNum) {
        let url = 'engineering_tools.php?tab=' + encodeURIComponent(suiteId);
        if (subTab) url += '&sub=' + encodeURIComponent(subTab);
        if (searchKeyword) url += '&q=' + encodeURIComponent(searchKeyword);
        if (itemNum) url += '&id=' + encodeURIComponent(itemNum);
        window.location.href = url;
    };

    function normalizeArabicSearch(str) {
        if (!str) return '';
        return str.toString()
            .replace(/[\u061F\u060C\u061B!?.؟،;:()\[\]{}«»\-\/\\\"'\`]/g, ' ')
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/ى/g, 'ي')
            .replace(/[\u064B-\u065F]/g, '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();
    }

    function extractTokens(query) {
        const norm = normalizeArabicSearch(query);
        const rawTokens = norm.split(' ').filter(t => t.length > 0);
        const stopWords = ['ما', 'هو', 'هي', 'هل', 'في', 'من', 'عن', 'على', 'الي', 'الى', 'مع', 'هذا', 'هذه', 'كم', 'اين', 'كيف', 'اريد', 'ابحث', 'اعطني', 'حكم', 'الحكم', 'شرعي', 'الشرعي', 'فقهي', 'الفقهي', 'يجوز', 'حلال', 'حرام', 'شرعا', 'مسالة'];
        let tokens = [];
        rawTokens.forEach(w => {
            if (stopWords.includes(w)) return;
            tokens.push(w);
            if ((w.startsWith('بال') || w.startsWith('كال') || w.startsWith('فال') || w.startsWith('وال')) && w.length > 4) {
                tokens.push(w.substring(3));
            } else if ((w.startsWith('لل') || w.startsWith('ال')) && w.length > 3) {
                tokens.push(w.substring(2));
            }
        });
        return [...new Set(tokens)];
    }

    function detectFiqhAndToolsIntent(query) {
        const norm = normalizeArabicSearch(query);
        const words = norm.split(/\s+/);
        const hasAnyWord = (targets) => words.some(w => targets.includes(w));
        
        if (hasAnyWord(['ايه', 'ايات', 'الايه', 'الايات', 'قران', 'القران', 'سوره', 'السوره', 'مداينه', 'المداينه', 'المطففين'])) return 'quran';
        if (hasAnyWord(['حديث', 'احاديث', 'الحديث', 'الاحاديث', 'الرسول', 'النبي', 'البخاري', 'مسلم', 'الترمذي', 'ابوداود', 'السنن', 'البيعان', 'بالخيار', 'الخيار'])) return 'hadith';
        if (hasAnyWord(['قاعده', 'قواعد', 'القاعده', 'القواعد', 'الغرم', 'الخراج', 'الضمان'])) return 'maxim';
        
        if (norm.includes('سويفت') || norm.includes('swift') || norm.includes('ايبان') || norm.includes('iban') || norm.includes('bban') || norm.includes('بنكك') || norm.includes('اوكاش') || norm.includes('بنك الخرطوم') || norm.includes('بنك فيصل') || norm.includes('تحويل بنكي') || norm.includes('تحويلات البنوك')) return 'banks';
        if (norm.includes('زكاه') || norm.includes('نصاب') || norm.includes('عروض التجاره')) return 'zakat';
        if (norm.includes('حديد') || norm.includes('تسليح') || norm.includes('سيخ') || norm.includes('زنك') || norm.includes('مواسير') || norm.includes('كمرات') || norm.includes('لينة') || norm.includes('لينه')) return 'steel';
        if (norm.includes('خرسانه') || norm.includes('اسمنت') || norm.includes('بلوك') || norm.includes('طوب') || norm.includes('مونه') || norm.includes('بياض') || norm.includes('صبة') || norm.includes('صبه')) return 'concrete';
        if (norm.includes('طاقه') || norm.includes('شمسيه') || norm.includes('انفرتر') || norm.includes('الواح شمسيه') || norm.includes('بطاريات')) return 'solar';
        if (norm.includes('شحن') || norm.includes('نولون') || norm.includes('حاويه') || norm.includes('تريلا') || norm.includes('حاويات')) return 'freight';
        if (norm.includes('تحويل وحدات') || norm.includes('فدان') || norm.includes('بوصه') || norm.includes('يارده') || norm.includes('متر مربع')) return 'units';
        if (norm.includes('جدوى') || norm.includes('جدوي') || norm.includes('نقطه التعادل') || norm.includes('تدفق نقدي')) return 'feasibility';
        if (norm.includes('نهايه الخدمه') || norm.includes('مكافاه نهايه') || norm.includes('مستحقات العامل') || norm.includes('حقوق العمال')) return 'hr_gratuity';
        
        if (norm.includes('حكم') || norm.includes('فتوى') || norm.includes('فتاوى') || norm.includes('فتاوي') || norm.includes('يجوز') || norm.includes('حلال') || norm.includes('حرام') || norm.includes('تقسيط') || norm.includes('مرابحه') || norm.includes('تورق') || norm.includes('عربون') || norm.includes('ذهب') || norm.includes('فضه') || norm.includes('ربا') || norm.includes('سلم') || norm.includes('استصناع') || norm.includes('عثيمين') || norm.includes('ابن باز') || norm.includes('الفقه') || norm.includes('غرامات') || norm.includes('تاخير') || norm.includes('شيك')) return 'fatwa';
        
        return null;
    }

    let _cachedFiqhDataset = null;
    function getOrLoadFiqhDataset(callback) {
        if (_cachedFiqhDataset && _cachedFiqhDataset.length > 0) {
            callback(_cachedFiqhDataset);
            return;
        }
        if (window.MASTER_20000_FATWAS && Array.isArray(window.MASTER_20000_FATWAS) && window.MASTER_20000_FATWAS.length > 0) {
            _cachedFiqhDataset = window.MASTER_20000_FATWAS;
            callback(_cachedFiqhDataset);
            return;
        }
        if (typeof window.ensureFiqhLoaded === 'function') {
            window.ensureFiqhLoaded(() => {
                _cachedFiqhDataset = window.MASTER_20000_FATWAS || window.MASTER_5500_FATWAS || [];
                callback(_cachedFiqhDataset);
            });
            return;
        }
        const script = document.createElement('script');
        script.src = 'js/fiqh_20000_data.js?v=4500_v2';
        script.async = true;
        script.onload = () => {
            _cachedFiqhDataset = window.MASTER_20000_FATWAS || window.MASTER_5500_FATWAS || [];
            callback(_cachedFiqhDataset);
        };
        script.onerror = () => {
            callback([]);
        };
        document.head.appendChild(script);
    }

    function handleFiqhAndTools(query, callback) {
        const intent = detectFiqhAndToolsIntent(query);
        if (!intent) {
            callback(false);
            return;
        }

        const tokens = extractTokens(query);

        if (intent === 'quran') {
            const verses = window.QURAN_VERSES_DATA || [];
            let top = null;
            let maxScore = 0;
            for (let v of verses) {
                let score = 0;
                const norm = normalizeArabicSearch((v.search || '') + ' ' + (v.title || '') + ' ' + (v.verseText || ''));
                for (let t of tokens) {
                    if (norm.includes(t)) score += 3;
                }
                if (score > maxScore) { maxScore = score; top = v; }
            }
            if (!top && verses.length > 0) top = verses[0];

            if (top) {
                const responseHtml = `
                    📖 <b>الدستور القرآني للمعاملات المالية (الآية #${top.num}):</b><br>
                    <b style="color:#38bdf8; font-size:0.95rem; display:block; margin:4px 0;">${top.title}</b>
                    <span style="font-size:0.8rem; color:#94a3b8;">${top.surah}</span><br>
                    <div style="font-family:'Amiri', serif; font-size:1.15rem; line-height:1.9; color:#f8fafc; background:rgba(56,189,248,0.08); padding:10px 14px; border-radius:10px; border-right:4px solid #38bdf8; margin:8px 0;">
                        ﴿ ${top.verseText} ﴾
                    </div>
                    <span style="font-size:0.85rem; color:#cbd5e1; display:block; margin-top:4px;">💡 <b>الأحكام والفوائد الاقتصادية:</b> ${top.guidance}</span>
                `;
                const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'quran', '${encodeURIComponent(top.title)}', ${top.num})">📖 عرض الآية في الدستور القرآني (#${top.num})</button>`;
                callback(true, responseHtml, btnHtml);
                return;
            }
            const responseHtml = `
                📖 <b>الدستور القرآني للمعاملات المالية:</b><br><br>
                يضم الدستور القرآني 38 آية محكمة تؤصل أحكام الديون (آية المداينة الكبرى 282 البقرة)، وتحريم الربا القطعي، والوفاء بالعقود، والأمانات المالية، وحرمة التطفيف في الكيل والميزان.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'quran')">📖 فتح الدستور القرآني في الأدوات</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'hadith') {
            const hadiths = window.HADITHS_DATA || [];
            let top = null;
            let maxScore = 0;
            for (let h of hadiths) {
                let score = 0;
                const norm = normalizeArabicSearch((h.search || '') + ' ' + (h.title || '') + ' ' + (h.matn || ''));
                for (let t of tokens) {
                    if (norm.includes(t)) score += 3;
                }
                if (score > maxScore) { maxScore = score; top = h; }
            }
            if (!top && hadiths.length > 0) top = hadiths[0];

            if (top) {
                const responseHtml = `
                    📜 <b>الموسوعة النبوية الصحيحة في المعاملات (الحديث #${top.num}):</b><br>
                    <b style="color:#facc15; font-size:0.95rem; display:block; margin:4px 0;">${top.title}</b>
                    <span style="font-size:0.8rem; color:#4ade80; background:rgba(74,222,128,0.15); padding:2px 8px; border-radius:6px;">${top.gradeBadge}</span> 
                    <span style="font-size:0.8rem; color:#cbd5e1;">${top.catBadge}</span><br>
                    <div style="font-family:'Amiri', serif; font-size:1.1rem; line-height:1.8; color:#fef08a; background:rgba(250,204,21,0.08); padding:10px 14px; border-radius:10px; border-right:4px solid #facc15; margin:8px 0;">
                        ${top.sanad ? '<span style="color:#cbd5e1; font-size:0.9rem;">' + top.sanad + '</span><br>' : ''}${top.matn}
                    </div>
                    <span style="font-size:0.82rem; color:#94a3b8; display:block; margin-top:4px;">📚 <b>التخريج:</b> ${top.source}</span>
                    <span style="font-size:0.85rem; color:#cbd5e1; display:block; margin-top:3px;">💎 <b>الضابط والفائدة للتاجر والمحاسب:</b> ${top.benefit}</span>
                `;
                const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'hadiths', '${encodeURIComponent(top.title)}', ${top.num})">📜 عرض الحديث في الموسوعة النبوية (#${top.num})</button>`;
                callback(true, responseHtml, btnHtml);
                return;
            }
            const responseHtml = `
                📜 <b>الموسوعة النبوية الصحيحة للمعاملات المالية:</b><br><br>
                تضم الموسوعة 100 حديث نبوي صحيح مخرجة من الصحيحين والسنن في أصول التجارة والبيوع: خيار المجلس، النهي عن بيع ما لا يملك، السماحة في البيع والشراء، وتحريم الربا والغرر.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'hadiths')">📜 فتح الموسوعة النبوية في الأدوات</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'maxim') {
            const responseHtml = `
                ⚖️ <b>القواعد الفقهية الكبرى الـ 30 في البيوع والمعاملات:</b><br><br>
                • <b>الأصل في المعاملات الإباحة:</b> كل شرط أو عقد مباح ما لم يقم دليل على تحريمه.<br>
                • <b>الغرم بالغنم:</b> من يستحق ربح السلعة يجب أن يتحمل مخاطرها وضمانها.<br>
                • <b>الخراج بالضمان:</b> منافع المبيع للمشتري في مقابل تحمله مخاطر تلفه.<br>
                • <b>الضرر يزال:</b> تحريم التدليس والغش والاحتكار والإضرار بالمتعاملين.<br>
                • <b>المعروف عرفاً كالمشروط شرطاً:</b> الأعراف التجارية الصحيحة لها قوة الشروط التعاقدية.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'maxims')">⚖️ عرض القواعد الفقهية الـ 30</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'banks') {
            const responseHtml = `
                🏛️ <b>دليل مفاتيح تحويلات البنوك وسويفت و BBAN:</b><br><br>
                💡 <b>القاعدة الذهبية لكافة البنوك:</b> يتم حذف الصفر في بداية رقم الحساب دائماً حتى تكتمل الـ 14 خانة بنجاح!<br><br>
                • <b>بنك الخرطوم (تطبيق بنكك):</b> المفتاح <code>040</code> + 7 أرقام حساب + <code>0001</code><br>
                • <b>بنك أم درمان الوطني (تطبيق أوكاش):</b> المفتاح <code>240</code> + 7 أرقام حساب + <code>0001</code><br>
                • <b>بنك فيصل الإسلامي (تطبيق فوري):</b> المفتاح <code>02</code> + 8 أرقام حساب + <code>0001</code><br>
                • <b>البنك الإسلامي السوداني:</b> المفتاح <code>14000</code> + 4 أرقام حساب + <code>00001</code>
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('banks')">🏦 فتح دليل البنوك ومولد BBAN</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'zakat') {
            const responseHtml = `
                💰 <b>حاسبة زكاة المال وعروض التجارة والذهب:</b><br><br>
                • <b>نصاب الزكاة الشرعي:</b> ما يعادل قيمة <b>85 جراماً من الذهب الخالص (عيار 24)</b>.<br>
                • <b>نسبة الزكاة الواجبة:</b> <b>2.5% (ربع العشر)</b>.<br>
                • <b>شروط الوجوب:</b> بلوغ النصاب + حولان الحول (مرور سنة قمرية) + فضل المال عن الحاجات والديون العاجلة.<br><br>
                يمكنك إدخال السيولة النقدية والذهب والمخزون لحساب الزكاة المستحقة فورياً:
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('zakat')">🧮 فتح حاسبة زكاة المال</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'steel') {
            const responseHtml = `
                🏗️ <b>حاسبة أوزان وقطع حديد التسليح والمواسير والزنك:</b><br><br>
                • <b>معادلة وزن المتر الطولي للسيخ:</b> <code>الوزن (كجم/متر) = القطر² ÷ 162</code><br>
                • <b>أوزان أسياخ التسليح القياسية (طول السيخة 12 متر):</b><br>
                  - سيخ 12 مم (4 لينة): 10.65 كجم/سيخة (الطن ≈ 94 سيخة)<br>
                  - سيخ 16 مم (5 لينة): 18.96 كجم/سيخة (الطن ≈ 53 سيخة)<br>
                  - سيخ 10 مم (3 لينة): 7.40 كجم/سيخة (الطن ≈ 135 سيخة)<br>
                  - سيخ 8 مم (لينة وربع): 4.74 كجم/سيخة (الطن ≈ 211 سيخة)
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('steel')">📏 فتح حاسبة أوزان الحديد والزنك</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'concrete') {
            const responseHtml = `
                🧱 <b>حاسبة الخرسانة المسلحة والبلوك ومواد البناء:</b><br><br>
                • <b>الخلطة القياسية للخرسانة المسلحة (1 : 2 : 4):</b><br>
                  - لكل 1 م³ خرسانة مسلحة: <b>7 شكائر أسمنت (350 كجم)</b> + <b>0.43 م³ رمل</b> + <b>0.86 م³ سن/حصى</b> + <b>175 لتر ماء</b>.<br>
                • <b>المونة والبلوك:</b> لحساب كميات البلوك الأسمنتي بدقة ومونة البناء واللياسة:
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('concrete')">🧱 فتح حاسبة الخرسانة ومواد البناء</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'solar') {
            const responseHtml = `
                ☀️ <b>حاسبة منظومات الطاقة الشمسية والكهرباء:</b><br><br>
                تساعدك الحاسبة على تحديد قدرة الألواح الشمسية المطلوبة، وسعة الانفرتر المناسب، وبنك البطاريات (أمبير/ساعة) لتشغيل المنشأة أو المخازن باستقرار تام.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('solar')">⚡ فتح حاسبة الطاقة الشمسية</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'freight') {
            const responseHtml = `
                🚚 <b>حاسبة الشحن والنولون والحاويات وتوزيع الحمولات:</b><br><br>
                • <b>حاوية 20 قدم:</b> الحجم ~33 م³، أقصى حمولة ~28 طن.<br>
                • <b>حاوية 40 قدم:</b> الحجم ~67 م³، أقصى حمولة ~28 طن.<br>
                • <b>تريلا جامبو:</b> حمولة قياسية ~25 إلى 30 طن.<br>
                لحساب النولون وتوزيع الأوزان على الشاحنات:
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('freight')">📦 فتح حاسبة الشحن والحاويات</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'units') {
            const responseHtml = `
                📐 <b>محول المقاييس والوحدات العالمية:</b><br><br>
                • 1 فدان = 4200.83 متر مربع.<br>
                • 1 متر = 39.37 بوصة = 3.281 قدم.<br>
                • 1 طن = 1000 كجم = 2204.62 رطل.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('units')">🔄 فتح محول المقاييس والوحدات</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'feasibility') {
            const responseHtml = `
                📊 <b>دراسة الجدوى الاستثمارية وحساب نقطة التعادل:</b><br><br>
                • <b>نقطة التعادل (Break-even):</b> التكاليف الثابتة ÷ (سعر بيع الوحدة - التكلفة المتغيرة للوحدة).<br>
                • <b>فترة استرداد رأس المال:</b> إجمالي الاستثمار الأولي ÷ صافي التدفق النقدي السنوي.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('feasibility')">📈 فتح حاسبة دراسة الجدوى</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'hr_gratuity') {
            const responseHtml = `
                👥 <b>حاسبة مكافأة نهاية الخدمة ومستحقات العمال:</b><br><br>
                تحسب مكافأة نهاية الخدمة النظامية وفق قانون العمل بناءً على سنوات الخدمة، والراتب الأساسي الأخير، ورصيد الإجازات، ونوع انتهاء العقد.
            `;
            const btnHtml = `<br><button type="button" class="chat-action-btn" onclick="openToolSuite('hr_gratuity')">⚖️ فتح حاسبة مكافأة نهاية الخدمة</button>`;
            callback(true, responseHtml, btnHtml);
            return;
        }

        if (intent === 'fatwa') {
            const typingEl = appendMessage('<div class="typing-indicator" style="font-size:12px;opacity:0.8;">جاري البحث المباشر في موسوعة فتاوى البيوع والمعاملات (4,500 فتوى)... 📚</div>', 'bot');
            
            getOrLoadFiqhDataset((dataset) => {
                if (!dataset || dataset.length === 0) {
                    typingEl.innerHTML = `
                        🕌 <b>فتاوى البيوع والمعاملات المالية:</b><br><br>
                        الأصل في المعاملات المالية الحل والإباحة والتراضي، ما لم تخالط المعاملة ربا أو غرر فاحش أو ظلم.<br>
                        <button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'fatwas')">🔍 فتح موسوعة فتاوى البيوع (4,500 فتوى)</button>
                    `;
                    body.scrollTop = body.scrollHeight;
                    return;
                }

                let scored = [];
                for (let f of dataset) {
                    let score = 0;
                    const normQues = normalizeArabicSearch(f.question || '');
                    const normTags = normalizeArabicSearch(f.tags || '');
                    const normTopic = normalizeArabicSearch(f.topic_name || '');
                    const normSumm = normalizeArabicSearch(f.summary || '');
                    for (let t of tokens) {
                        if (normQues.includes(t)) score += 6;
                        if (normTags.includes(t)) score += 4;
                        if (normTopic.includes(t)) score += 4;
                        if (normSumm.includes(t)) score += 2;
                    }
                    if (score > 0) scored.push({ fatwa: f, score });
                }

                scored.sort((a,b) => b.score - a.score);
                const topItem = scored.length > 0 ? scored[0].fatwa : dataset[0];

                let badgeColor = '#10b981';
                const rulingText = topItem.ruling || '';
                if (rulingText.includes('محرم') || rulingText.includes('باطل') || rulingText.includes('ربا')) {
                    badgeColor = '#ef4444';
                } else if (rulingText.includes('مكروه') || rulingText.includes('ضوابط')) {
                    badgeColor = '#f59e0b';
                } else if (rulingText.includes('واجب') || rulingText.includes('ملزم')) {
                    badgeColor = '#0ea5e9';
                }

                typingEl.innerHTML = `
                    🕌 <b>مسألة فقهية في المعاملات المالية (#${topItem.id}):</b><br>
                    <b style="color:#f1f5f9; font-size:0.95rem; display:block; margin:6px 0;">${topItem.question}</b>
                    <div style="background:rgba(255,255,255,0.06); padding:8px 12px; border-radius:10px; border-right:4px solid ${badgeColor}; margin:8px 0;">
                        <span style="color:${badgeColor}; font-weight:bold; font-size:0.95rem;">⚖️ الحكم الشرعي: ${topItem.ruling}</span><br>
                        <span style="font-size:0.85rem; color:#cbd5e1; margin-top:4px; display:block; line-height:1.5;"><b>البيان والضابط:</b> ${topItem.summary}</span>
                    </div>
                    <span style="font-size:0.85rem; color:#94a3b8; display:block; margin-top:4px;">📖 <b>الدليل الشرعي:</b> ${topItem.evidence}</span>
                    <span style="font-size:0.8rem; color:#64748b; display:block; margin-top:3px;">📚 <b>المصدر:</b> ${topItem.scholar_name} — ${topItem.source}</span>
                    <button type="button" class="chat-action-btn" onclick="openToolSuite('fiqh', 'fatwas', '${topItem.id}', ${topItem.id})">🔍 عرض الفتوى في الموسوعة (#${topItem.id})</button>
                `;
                body.scrollTop = body.scrollHeight;
            });
            callback(true);
            return;
        }

        callback(false);
    }

    function processUserMessage(text) {
        const query = text.trim();
        if(!query) return;

        appendMessage(query, 'user');
        input.value = '';

        setTimeout(() => {
            let matched = false;
            
            // 1. Try Dynamic Queries (NLP Regex) for Live ERP Data
            for(let rule of dynamicKb) {
                const match = query.match(rule.regex);
                if(match) {
                    appendMessage(rule.handler(match), 'bot');
                    matched = true;
                    break;
                }
            }

            if(matched) return;

            // 2. Try Fiqh & Engineering Tools Engine
            handleFiqhAndTools(query, (handled, responseHtml, actionBtnHtml) => {
                if (handled) {
                    if (responseHtml) {
                        appendMessage(responseHtml, 'bot', actionBtnHtml || '');
                    }
                    return;
                }

                // 3. Try Static Keywords
                const lowerQuery = query.toLowerCase();
                for(let item of staticKb) {
                    if(item.keywords.some(kw => lowerQuery.includes(kw))) {
                        let btn = '';
                        if(item.link) {
                            const target = item.isExternal ? 'target="_blank"' : '';
                            const btnClass = item.isExternal ? 'chat-action-btn chat-whatsapp-btn' : 'chat-action-btn';
                            btn = `<br><a href="${item.link}" class="${btnClass}" ${target}>${item.linkText}</a>`;
                        }
                        appendMessage(item.response, 'bot', btn);
                        matched = true;
                        break;
                    }
                }

                if (matched) return;

                // 4. Fallback -> Use AI Assistant
                const msgEl = appendMessage('<div class="typing-indicator" style="font-size:12px;opacity:0.7;">جاري التفكير... 🤖</div>', 'bot');
                
                let contextStr = "لا توجد بيانات حالية";
                try {
                    // 1. Basic Stats
                    let stats = [
                        "السيولة النقدية: " + getTotalLiquidity(),
                        "إجمالي المبيعات: " + getTotalSalesAllTime(),
                        "قيمة المخزون: " + getTotalInventoryValue(),
                        "إحصائيات اليوم: " + getDailyStats().replace(/<br>/g, " ")
                    ];
                    
                    // 2. Dynamic Partner Balance Extraction
                    let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]').filter(x => !x.deleted);
                    let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]').filter(x => !x.deleted);
                    let allPartners = [...custs, ...sups];
                    let specificBalances = [];
                    for(let p of allPartners) {
                        if(p.name && query.includes(p.name)) {
                            let bal = calculatePartnerBalance(p.name);
                            if(bal !== null) specificBalances.push(`رصيد ${p.name}: ${bal} ريال`);
                        }
                    }
                    if(specificBalances.length > 0) {
                        stats.push("أرصدة مطلوبة: " + specificBalances.join("، "));
                    }
                    
                    // 3. Top Debtors
                    let topDebtors = getTopDebtors().replace(/<[^>]*>?/gm, ' ');
                    stats.push("أبرز المديونيات: " + topDebtors);

                    contextStr = stats.join(" | ");
                } catch(e) { console.error("Context Error:", e); }

                fetch('api/ai-assistant.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: query, context: contextStr })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.reply) {
                        let formattedReply = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                        
                        // Parse AI Actions if present
                        let actionBtnHtml = '';
                        if (data.action && data.action.type && data.action.params) {
                            const action = data.action;
                            const type = action.type;
                            const params = action.params;
                            
                            const queryParams = new URLSearchParams();
                            queryParams.set('chatbot_draft', 'true');
                            queryParams.set('action_type', type);
                            for (let key in params) {
                                if (typeof params[key] === 'object') {
                                    queryParams.set(key, JSON.stringify(params[key]));
                                } else {
                                    queryParams.set(key, params[key]);
                                }
                            }
                            
                            let targetPage = '';
                            let btnText = '';
                            
                            if (type === 'draft_receipt') {
                                targetPage = 'single-cash-payment.php?type=receipt';
                                btnText = '💵 فتح شاشة السند وتعبئته';
                            } else if (type === 'draft_payment') {
                                targetPage = 'single-cash-payment.php';
                                btnText = '💸 فتح شاشة الصرف وتعبئته';
                            } else if (type === 'draft_transfer') {
                                targetPage = 'safe-transfer.php';
                                btnText = '🏦 فتح شاشة التحويل وتعبئته';
                            } else if (type === 'draft_sales_invoice') {
                                targetPage = 'sales-invoice.php';
                                btnText = '📄 فتح شاشة الفاتورة وتعبئتها';
                            } else if (type === 'draft_purchase_invoice') {
                                targetPage = 'purchase.php';
                                btnText = '🛒 فتح شاشة المشتريات وتعبئتها';
                            }
                            
                            if (targetPage) {
                                const fullUrl = `${targetPage}?${queryParams.toString()}`;
                                const currentPath = window.location.pathname;
                                
                                if (currentPath.includes(targetPage)) {
                                    const actionJsonEscaped = JSON.stringify(action).replace(/"/g, '&quot;');
                                    actionBtnHtml = `<br><button class="chat-action-btn" onclick="executeLocalDraft(${actionJsonEscaped})">⚡ تعبئة الحقول هنا فوراً</button>`;
                                } else {
                                    actionBtnHtml = `<br><a href="${fullUrl}" class="chat-action-btn">${btnText}</a>`;
                                }
                            }
                        }
                        
                        msgEl.innerHTML = formattedReply + actionBtnHtml;
                    } else {
                        msgEl.innerHTML = 'عذراً، حدث خطأ في الاتصال بالمساعد الذكي.';
                    }
                    body.scrollTop = body.scrollHeight;
                })
                .catch(err => {
                    console.error(err);
                    msgEl.innerHTML = 'عذراً، لم أتمكن من الوصول للخادم.';
                });
            });
        }, 500);
    }

    sendBtn.addEventListener('click', () => { processUserMessage(input.value); });
    input.addEventListener('keypress', (e) => { if(e.key === 'Enter') processUserMessage(input.value); });

    // --- Speech Recognition ---
    const micBtn = document.getElementById('chatbot-mic');
    if (micBtn) {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            recognition.lang = 'ar-SA';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            
            let isRecording = false;
            
            recognition.onstart = () => {
                isRecording = true;
                micBtn.innerHTML = '🛑';
                micBtn.classList.add('recording');
                input.placeholder = 'جاري الاستماع لصوتك... 🎙️';
            };
            
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                input.value = transcript;
            };
            
            recognition.onerror = (e) => {
                console.error('Speech Recognition Error:', e.error);
                input.placeholder = placeholderText;
                stopRec();
            };
            
            recognition.onend = () => {
                stopRec();
                if (input.value.trim() !== '') {
                    processUserMessage(input.value);
                }
            };
            
            function stopRec() {
                isRecording = false;
                micBtn.innerHTML = '🎤';
                micBtn.classList.remove('recording');
                input.placeholder = placeholderText;
            }
            
            micBtn.addEventListener('click', () => {
                if (isRecording) {
                    recognition.stop();
                } else {
                    recognition.start();
                }
            });
        } else {
            micBtn.style.display = 'none';
        }
    }

    // --- Database Helper Finders ---
    function findBestAccountMatch(name) {
        console.log('Chatbot: findBestAccountMatch query =', name);
        if (!name) return null;
        
        const normalize = (str) => {
            if (!str) return '';
            return str.toString()
                .replace(/[أإآ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/ى/g, 'ي')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
        };
        
        const cleanWord = (word) => {
            if (word.startsWith('ال') && word.length > 3) {
                word = word.substring(2);
            }
            return word;
        };
        
        const nameNorm = normalize(name);
        const nameWords = nameNorm.split(' ').map(cleanWord).filter(w => w.length > 0);
        
        const keys = ['erp_customers_v1', 'erp_suppliers_v1', 'erp_banks_v1', 'erp_safes_v1', 'erp_accounts_v1'];
        let bestMatch = null;
        let highestScore = 0;
        
        for (let k of keys) {
            try {
                const raw = localStorage.getItem(k);
                if (!raw) continue;
                
                const list = typeof raw === 'string' ? JSON.parse(raw || '[]') : (raw || []);
                console.log(`Chatbot: searching key ${k}, elements count = ${list.length}`);
                
                for (let item of list) {
                    if (!item.name || item.deleted) continue;
                    const itemNorm = normalize(item.name);
                    
                    if (itemNorm === nameNorm) {
                        console.log('Chatbot: exact account match found:', item.name);
                        return { id: item.id || item.code, name: item.name };
                    }
                    if (itemNorm.includes(nameNorm) || nameNorm.includes(itemNorm)) {
                        const score = Math.min(itemNorm.length, nameNorm.length) / Math.max(itemNorm.length, nameNorm.length);
                        if (score + 0.5 > highestScore) {
                            highestScore = score + 0.5;
                            bestMatch = { id: item.id || item.code, name: item.name };
                        }
                    }
                    
                    const itemWords = itemNorm.split(' ').map(cleanWord).filter(w => w.length > 0);
                    let matchCount = 0;
                    nameWords.forEach(w1 => {
                        itemWords.forEach(w2 => {
                            if (w1 === w2 || w1.includes(w2) || w2.includes(w1)) {
                                matchCount++;
                            }
                        });
                    });
                    
                    const score = matchCount / Math.max(nameWords.length, itemWords.length);
                    if (score > highestScore) {
                        highestScore = score;
                        bestMatch = { id: item.id || item.code, name: item.name };
                    }
                }
            } catch(e) {
                console.warn(`Chatbot: Error parsing account list for key ${k}`, e);
            }
        }
        
        console.log('Chatbot: findBestAccountMatch best match result =', bestMatch, 'with score =', highestScore);
        return highestScore > 0.3 ? bestMatch : null;
    }

    function findBestItemMatch(name) {
        console.log('Chatbot: findBestItemMatch query =', name);
        if (!name) return null;
        
        const normalize = (str) => {
            if (!str) return '';
            return str.toString()
                .replace(/[أإآ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/ى/g, 'ي')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
        };
        
        const cleanWord = (word) => {
            if (word.startsWith('ال') && word.length > 3) {
                word = word.substring(2);
            }
            return word;
        };
        
        const nameNorm = normalize(name);
        const nameWords = nameNorm.split(' ').map(cleanWord).filter(w => w.length > 0);
        
        let bestMatch = null;
        let highestScore = 0;
        
        try {
            const raw = localStorage.getItem('erp_items_v1');
            if (raw) {
                const list = typeof raw === 'string' ? JSON.parse(raw || '[]') : (raw || []);
                console.log(`Chatbot: searching items, elements count = ${list.length}`);
                
                for (let item of list) {
                    if (!item.name || item.deleted) continue;
                    const itemNorm = normalize(item.name);
                    
                    if (itemNorm === nameNorm) {
                        console.log('Chatbot: exact item match found:', item.name);
                        return { id: item.id, name: item.name, price: parseFloat(item.price || item.openingValue || 0) };
                    }
                    if (itemNorm.includes(nameNorm) || nameNorm.includes(itemNorm)) {
                        const score = Math.min(itemNorm.length, nameNorm.length) / Math.max(itemNorm.length, nameNorm.length);
                        if (score + 0.5 > highestScore) {
                            highestScore = score + 0.5;
                            bestMatch = { id: item.id, name: item.name, price: parseFloat(item.price || item.openingValue || 0) };
                        }
                    }
                    
                    const itemWords = itemNorm.split(' ').map(cleanWord).filter(w => w.length > 0);
                    let matchCount = 0;
                    nameWords.forEach(w1 => {
                        itemWords.forEach(w2 => {
                            if (w1 === w2 || w1.includes(w2) || w2.includes(w1)) {
                                matchCount++;
                            }
                        });
                    });
                    
                    const score = matchCount / Math.max(nameWords.length, itemWords.length);
                    if (score > highestScore) {
                        highestScore = score;
                        bestMatch = { id: item.id, name: item.name, price: parseFloat(item.price || item.openingValue || 0) };
                    }
                }
            }
        } catch(e) {
            console.warn('Chatbot: Error parsing items list', e);
        }
        
        console.log('Chatbot: findBestItemMatch best match result =', bestMatch, 'with score =', highestScore);
        return highestScore > 0.3 ? bestMatch : null;
    }

    // --- Local Execution ---
    window.executeLocalDraft = function(action) {
        console.log('Chatbot: executeLocalDraft action =', action);
        if (!action || !action.type || !action.params) return;
        const type = action.type;
        const params = action.params;
        
        try {
            if (type === 'draft_receipt') {
                autoFillReceipt(params.from_name, params.to_name, params.amount, params.notes);
            } else if (type === 'draft_payment') {
                autoFillPayment(params.from_name, params.to_name, params.amount, params.notes);
            } else if (type === 'draft_transfer') {
                autoFillTransfer(params.from_name, params.to_name, params.amount, params.notes);
            } else if (type === 'draft_sales_invoice') {
                autoFillSalesInvoice(params.customer_name, params.items, params.notes);
            } else if (type === 'draft_purchase_invoice') {
                autoFillPurchaseInvoice(params.supplier_name, params.items, params.notes);
            }
        } catch(e) {
            console.error('Chatbot: Error executing local draft:', e);
        }
    };

    // --- Page Auto-fill Handlers ---
    function autoFillReceipt(fromName, toName, amount, notes) {
        console.log('Chatbot: autoFillReceipt executing with inputs:', { fromName, toName, amount, notes });
        const amtEl = document.getElementById('amount-local');
        if (amtEl) {
            amtEl.value = amount;
            amtEl.dispatchEvent(new Event('input', { bubbles: true }));
            amtEl.dispatchEvent(new Event('blur', { bubbles: true }));
            console.log('Chatbot: amount-local populated with:', amount);
        } else {
            console.warn('Chatbot: amount-local element not found in DOM!');
        }
        
        const fromStmt = document.getElementById('from-statement');
        if (fromStmt) fromStmt.value = notes || 'سند قبض مجهز بالمساعد الذكي';
        const toStmt = document.getElementById('to-statement');
        if (toStmt) toStmt.value = notes || 'سند قبض مجهز بالمساعد الذكي';
        
        if (fromName) {
            const acc = findBestAccountMatch(fromName);
            console.log('Chatbot: fromName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('from', acc.id, acc.name);
                console.log('Chatbot: from account selected:', acc.name);
            } else {
                console.warn('Chatbot: could not select from-account!', { acc, selectAccount: typeof window.selectAccount });
            }
        }
        if (toName) {
            const acc = findBestAccountMatch(toName);
            console.log('Chatbot: toName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('to', acc.id, acc.name);
                console.log('Chatbot: to account selected:', acc.name);
            } else {
                console.warn('Chatbot: could not select to-account!', { acc, selectAccount: typeof window.selectAccount });
            }
        }
        
        if (typeof showToast === 'function') {
            showToast('✨ تم تجهيز وتعبئة بيانات سند القبض تلقائياً!', 'success', 5000);
        }
    }

    function autoFillPayment(fromName, toName, amount, notes) {
        console.log('Chatbot: autoFillPayment executing with inputs:', { fromName, toName, amount, notes });
        const amtEl = document.getElementById('amount-local');
        if (amtEl) {
            amtEl.value = amount;
            amtEl.dispatchEvent(new Event('input', { bubbles: true }));
            amtEl.dispatchEvent(new Event('blur', { bubbles: true }));
            console.log('Chatbot: amount-local populated with:', amount);
        }
        
        const toStmt = document.getElementById('to-statement');
        if (toStmt) toStmt.value = notes || 'سند صرف مجهز بالمساعد الذكي';
        
        if (fromName) {
            const acc = findBestAccountMatch(fromName);
            console.log('Chatbot: fromName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('from', acc.id, acc.name);
                console.log('Chatbot: from account selected:', acc.name);
            }
        }
        if (toName) {
            const acc = findBestAccountMatch(toName);
            console.log('Chatbot: toName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('to', acc.id, acc.name);
                console.log('Chatbot: to account selected:', acc.name);
            }
        }
        
        if (typeof showToast === 'function') {
            showToast('✨ تم تجهيز وتعبئة بيانات سند الصرف تلقائياً!', 'success', 5000);
        }
    }

    function autoFillTransfer(fromName, toName, amount, notes) {
        console.log('Chatbot: autoFillTransfer executing with inputs:', { fromName, toName, amount, notes });
        const amtEl = document.getElementById('t-amount');
        if (amtEl) {
            amtEl.value = amount;
            amtEl.dispatchEvent(new Event('input', { bubbles: true }));
            console.log('Chatbot: t-amount populated with:', amount);
        }
        
        const noteEl = document.getElementById('t-notes');
        if (noteEl) noteEl.value = notes || 'تحويل مالي مجهز بالمساعد الذكي';
        
        if (fromName) {
            const acc = findBestAccountMatch(fromName);
            console.log('Chatbot: fromName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('from', acc.id, acc.name);
                console.log('Chatbot: from account selected:', acc.name);
            }
        }
        if (toName) {
            const acc = findBestAccountMatch(toName);
            console.log('Chatbot: toName match =', acc);
            if (acc && typeof window.selectAccount === 'function') {
                window.selectAccount('to', acc.id, acc.name);
                console.log('Chatbot: to account selected:', acc.name);
            }
        }
        
        if (typeof showToast === 'function') {
            showToast('✨ تم تجهيز وتعبئة بيانات التحويل المالي تلقائياً!', 'success', 5000);
        } else {
            alert('✨ تم تجهيز وتعبئة بيانات التحويل المالي تلقائياً!');
        }
    }

    function autoFillSalesInvoice(customerName, items, notes) {
        console.log('Chatbot: autoFillSalesInvoice executing with inputs:', { customerName, items, notes });
        if (customerName) {
            const cust = findBestAccountMatch(customerName);
            console.log('Chatbot: customerName match =', cust);
            if (cust && typeof window.selectCustomer === 'function') {
                window.selectCustomer(cust.id, cust.name);
                console.log('Chatbot: customer selected:', cust.name);
            }
        }
        
        const descEl = document.getElementById('hdr-description');
        if (descEl) descEl.value = notes || 'فاتورة مبيعات مجهزة بالمساعد الذكي';
        
        if (items) {
            try {
                const parsedItems = typeof items === 'string' ? JSON.parse(items) : items;
                if (Array.isArray(parsedItems) && parsedItems.length > 0) {
                    if (typeof window.rows !== 'undefined') {
                        window.rows = []; // Clear
                    }
                    
                    parsedItems.forEach(item => {
                        const matchedItem = findBestItemMatch(item.name);
                        console.log(`Chatbot: matching item ${item.name} result =`, matchedItem);
                        if (matchedItem) {
                            if (typeof window.addRow === 'function') {
                                window.addRow();
                                const newRow = window.rows[window.rows.length - 1];
                                if (newRow && typeof window.updateRow === 'function') {
                                    window.updateRow(newRow.id, 'itemId', matchedItem.id);
                                    window.updateRow(newRow.id, 'quantity', item.qty || 1);
                                    window.updateRow(newRow.id, 'price', item.price || matchedItem.price || 0);
                                    console.log(`Chatbot: added row for item ${matchedItem.name}`);
                                }
                            }
                        }
                    });
                    
                    if (typeof window.recalcAll === 'function') window.recalcAll();
                    if (typeof window.renderGrid === 'function') window.renderGrid();
                }
            } catch(e) {
                console.error('Chatbot: Sales invoice auto-fill items error:', e);
            }
        }
        
        if (typeof showToast === 'function') {
            showToast('✨ تم تجهيز وتعبئة فاتورة المبيعات تلقائياً!', 'success', 5000);
        }
    }

    function autoFillPurchaseInvoice(supplierName, items, notes) {
        if (supplierName) {
            const supp = findBestAccountMatch(supplierName);
            if (supp && typeof window.selectCustomer === 'function') {
                window.selectCustomer(supp.id, supp.name);
            }
        }
        
        const descEl = document.getElementById('hdr-description');
        if (descEl) descEl.value = notes || 'فاتورة مشتريات مجهزة بالمساعد الذكي';
        
        if (items) {
            try {
                const parsedItems = typeof items === 'string' ? JSON.parse(items) : items;
                if (Array.isArray(parsedItems) && parsedItems.length > 0) {
                    if (typeof window.rows !== 'undefined') {
                        window.rows = []; // Clear
                    }
                    
                    parsedItems.forEach(item => {
                        const matchedItem = findBestItemMatch(item.name);
                        if (matchedItem) {
                            if (typeof window.addRow === 'function') {
                                window.addRow(true); // quiet mode
                                const newRow = window.rows[window.rows.length - 1];
                                if (newRow && typeof window.updateRow === 'function') {
                                    window.updateRow(newRow.id, 'itemId', matchedItem.id);
                                    window.updateRow(newRow.id, 'quantity', item.qty || 1);
                                    window.updateRow(newRow.id, 'price', item.price || matchedItem.price || 0);
                                }
                            }
                        }
                    });
                    
                    if (typeof window.recalcAll === 'function') window.recalcAll();
                    if (typeof window.renderGrid === 'function') window.renderGrid();
                }
            } catch(e) {
                console.error('Purchase invoice auto-fill items error:', e);
            }
        }
        
        if (typeof showToast === 'function') {
            showToast('✨ تم تجهيز وتعبئة فاتورة المشتريات تلقائياً!', 'success', 5000);
        }
    }

    // --- On-load URL parameter checker ---
    (function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('chatbot_draft') === 'true') {
            const actionType = urlParams.get('action_type');
            console.log('Chatbot: URL checker detected chatbot_draft parameter with actionType =', actionType);
            
            const runAutoFill = () => {
                let attempts = 0;
                const interval = setInterval(() => {
                    attempts++;
                    if (attempts > 100) { // Stop polling after 10 seconds
                        console.warn('Chatbot: Polling timeout reached. DOM elements or controllers not ready.');
                        clearInterval(interval);
                        return;
                    }
                    
                    // Verify if core elements of target pages are ready in DOM
                    let elementsReady = false;
                    if (actionType === 'draft_receipt' || actionType === 'draft_payment') {
                        elementsReady = document.getElementById('amount-local') && 
                                        document.getElementById('from-account') && 
                                        typeof window.selectAccount === 'function';
                    } else if (actionType === 'draft_transfer') {
                        elementsReady = document.getElementById('t-amount') && 
                                        typeof window.selectAccount === 'function';
                    } else if (actionType === 'draft_sales_invoice' || actionType === 'draft_purchase_invoice') {
                        elementsReady = document.getElementById('hdr-description') && 
                                        typeof window.selectCustomer === 'function' && 
                                        typeof window.addRow === 'function';
                    }
                    
                    console.log(`Chatbot: Polling attempt #${attempts}, elementsReady = ${elementsReady}`);
                    
                    if (elementsReady) {
                        clearInterval(interval);
                        console.log('Chatbot: Target elements found! Clearing interval and launching autoFill.');
                        try {
                            if (actionType === 'draft_receipt') {
                                autoFillReceipt(
                                    urlParams.get('from_name'),
                                    urlParams.get('to_name'),
                                    urlParams.get('amount'),
                                    urlParams.get('notes')
                                );
                            } else if (actionType === 'draft_payment') {
                                autoFillPayment(
                                    urlParams.get('from_name'),
                                    urlParams.get('to_name'),
                                    urlParams.get('amount'),
                                    urlParams.get('notes')
                                );
                            } else if (actionType === 'draft_transfer') {
                                autoFillTransfer(
                                    urlParams.get('from_name'),
                                    urlParams.get('to_name'),
                                    urlParams.get('amount'),
                                    urlParams.get('notes')
                                );
                            } else if (actionType === 'draft_sales_invoice') {
                                autoFillSalesInvoice(
                                    urlParams.get('customer_name'),
                                    urlParams.get('items'),
                                    urlParams.get('notes')
                                );
                            } else if (actionType === 'draft_purchase_invoice') {
                                autoFillPurchaseInvoice(
                                    urlParams.get('supplier_name'),
                                    urlParams.get('items'),
                                    urlParams.get('notes')
                                );
                            }
                        } catch(e) {
                            console.error('Chatbot: On-load auto-fill execution failed:', e);
                        }
                    }
                }, 100);
            };
            
            runAutoFill();
        }
    })();

})();
