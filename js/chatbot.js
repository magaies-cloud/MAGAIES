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
        .chat-action-btn { display: inline-block; margin-top: 8px; padding: 6px 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; transition: 0.2s; border: 1px solid rgba(56, 189, 248, 0.3); }
        .chat-action-btn:hover { background: #38bdf8; color: #0f172a; }
        .chat-whatsapp-btn { background: rgba(37, 211, 102, 0.15); color: #25d366; border: 1px solid rgba(37, 211, 102, 0.3); }
        .chat-whatsapp-btn:hover { background: #25d366; color: #fff; }
        #chatbot-input-area { display: flex; padding: 12px; background: rgba(15, 23, 42, 0.8); border-top: 1px solid rgba(255,255,255,0.05); gap: 8px; }
        #chatbot-input { flex: 1; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 20px; padding: 10px 15px; font-size: 0.95rem; outline: none; transition: 0.3s; font-family: inherit; }
        #chatbot-input:focus { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56,189,248,0.2); }
        #chatbot-send { width: 42px; height: 42px; border-radius: 50%; background: #0ea5e9; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-size: 1.2rem; }
        #chatbot-send:hover { background: #0284c7; transform: scale(1.05); }
        .chat-options { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .chat-pill { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #cbd5e1; padding: 6px 12px; border-radius: 15px; font-size: 0.8rem; cursor: pointer; transition: 0.2s; }
        .chat-pill:hover { background: rgba(56,189,248,0.2); color: #38bdf8; border-color: #38bdf8; }
        @media (max-width: 480px) { #chatbot-window { width: calc(100vw - 40px); bottom: 85px; height: 450px; } }
    `;
    document.head.appendChild(style);

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
                    أهلاً بك! 🌟<br>أنا مرتبط بقاعدة بيانات النظام مباشرة الآن. جرب أن تسألني:
                    <div class="chat-options">
                        <span class="chat-pill" onclick="sendPill('كم السيولة؟')">كم السيولة؟ 💰</span>
                        <span class="chat-pill" onclick="sendPill('كم مديونية عميل محمد؟')">مديونية عميل...</span>
                        <span class="chat-pill" onclick="sendPill('كم عدد فواتير اليوم؟')">فواتير اليوم 📊</span>
                        <span class="chat-pill" onclick="sendPill('أريد شاشة الكاشير')">شاشة الكاشير 🛒</span>
                        <span class="chat-pill" onclick="sendPill('من هم أكبر المدينين؟')">أكبر المديونيات ⚠️</span>
                    </div>
                </div>
            </div>
            <div id="chatbot-input-area">
                <input type="text" id="chatbot-input" placeholder="اسأل عن أي مديونية أو فاتورة..." autocomplete="off">
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
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
        let sups = JSON.parse(localStorage.getItem('erp_suppliers_v1') || '[]');
        
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
        const items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
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
        let items = JSON.parse(localStorage.getItem('erp_items_v1') || '[]');
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
        let custs = JSON.parse(localStorage.getItem('erp_customers_v1') || '[]');
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

    // --- NLP RULES ---
    const dynamicKb = [
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
            link: 'direct-sales.html', linkText: '🛒 فتح شاشة الكاشير'
        },
        {
            keywords: ['رواتب', 'راتب', 'موظف', 'موظفين', 'سلفيات', 'خصومات', 'حضور', 'شؤون الموظفين'],
            response: 'لإدارة الموظفين والرواتب والحضور والانصراف والسلفيات، يرجى الذهاب إلى شاشة <b>شؤون الموظفين والرواتب</b>.',
            link: 'payroll.html', linkText: '👥 شؤون الموظفين'
        },
        {
            keywords: ['تسليم', 'مذكرة تسليم', 'بضاعة محجوزة', 'حجز بضاعة', 'محجوزات'],
            response: 'لإدارة المبيعات المحجوزة وإصدار مذكرات تسليم البضاعة لاحقاً، يرجى التوجه إلى قسم <b>المبيعات المحجوزة والتسليم</b>.',
            link: 'reserved-sales.html', linkText: '🚚 المبيعات المحجوزة والتسليم'
        },
        {
            keywords: ['دفتر استاذ', 'ميزان مراجعة', 'ارباح وخسائر', 'أرباح وخسائر', 'قيود محاسبية', 'ميزانية عمومية', 'دفتر الأستاذ'],
            response: 'يمكنك الوصول إلى التقارير المالية المتقدمة كدفتر الأستاذ العام وميزان المراجعة والأرباح والخسائر من <b>التقارير المالية</b>.',
            link: 'financial-reports.html', linkText: '📊 التقارير المالية (المتقدمة)'
        },
        {
            keywords: ['باركود', 'طباعة باركود', 'ليبل', 'ملصقات'],
            response: 'لطباعة ملصقات الباركود للمنتجات للصقها على البضاعة، يمكنك استخدام شاشة <b>طباعة الباركود</b> المخصصة.',
            link: 'barcode-printer.html', linkText: '🖨️ طباعة الباركود'
        },
        {
            keywords: ['مندوب', 'مندوبين', 'شريك', 'شركاء', 'وسطاء', 'عمولة', 'عمولات'],
            response: 'لإدارة الشركاء والمندوبين وحساب عمولاتهم من المبيعات، يرجى الذهاب إلى شاشة <b>المندوبين والشركاء</b>.',
            link: 'partners.html', linkText: '🤝 المندوبين والشركاء'
        },
        {
            keywords: ['من انت', 'من أنت', 'وظيفتك', 'بتعمل ايه', 'فائدتك', 'مين انت', 'ما هو عملك'],
            response: 'أنا المساعد الذكي الخاص بك! 🤖<br>وظيفتي هي قراءة البيانات الحية من النظام مثل (أرصدة العملاء، الخزينة، والمخزون) للإجابة على استفساراتك فوراً، بالإضافة لإرشادك إلى الشاشات الصحيحة داخل النظام.'
        },
        {
            keywords: ['كشف حساب خز', 'كشف خز', 'حركة خزينة', 'حركة بنك', 'كشف بنك', 'كشف حساب بنك', 'حساب الخزنة'],
            response: 'لاستخراج كشف حساب تفصيلي لحركة الخزينة أو البنك، يرجى الانتقال إلى شاشة <b>كشف حساب نقدية / بنك</b>.',
            link: 'cash-bank-statement.html', linkText: '🏦 كشف حساب خزينة/بنك'
        },
        {
            keywords: ['شيك', 'شيكات', 'الشيكات'],
            response: 'لإدارة الشيكات (الصادرة والواردة، التحصيل، والمرتجع)، يرجى الذهاب إلى شاشة <b>دورة الشيكات</b>.',
            link: 'checks-cycle.html', linkText: '💳 دورة الشيكات'
        },
        {
            keywords: ['فاتور', 'فواتير', 'مبيع', 'جديد'],
            response: 'لإصدار فاتورة مبيعات جديدة أو إدارة الفواتير، يرجى الانتقال إلى قسم <b>فواتير المبيعات</b>.',
            link: 'sales-invoice.html', linkText: '📄 الذهاب للفواتير'
        },
        {
            keywords: ['عميل', 'عملاء', 'زبون', 'زباين', 'زبائن', 'مورد', 'موردين', 'شركات'],
            response: 'لإضافة وتعديل بيانات العملاء (الزبائن) والموردين، يمكنك الانتقال إلى <b>البيانات الأساسية (شاشة العملاء والموردين)</b>.',
            link: 'master-data.html', linkText: '👥 إدارة العملاء والموردين'
        },
        {
            keywords: ['كشف', 'ارصدة', 'تقرير'],
            response: 'يمكنك استخراج كشوفات الحسابات والعملاء والموردين من قسم <b>التقارير المالية</b>.',
            link: 'statement.html', linkText: '📊 كشف حساب عميل/مورد'
        },
        {
            keywords: ['شراء', 'مشتريات', 'طلب', 'مورد'],
            response: 'لإنشاء طلب شراء أو فاتورة مشتريات، يرجى الانتقال إلى قسم <b>المشتريات</b>.',
            link: 'purchase.html', linkText: '🛒 الذهاب للمشتريات'
        },
        {
            keywords: ['اعدادات', 'إعدادات', 'خط', 'حجم الخط', 'الوان', 'ألوان', 'تعديل حجم', 'شركة', 'شعار', 'ضريبة'],
            response: 'لتعديل إعدادات النظام (مثل حجم الخط، الألوان، الشعار، بيانات الشركة، أو إعدادات الضرائب)، يرجى التوجه إلى شاشة <b>الإعدادات</b>.',
            link: 'settings.html', linkText: '⚙️ الذهاب إلى الإعدادات'
        },
        {
            keywords: ['مستخدمين', 'مستخدم', 'صلاحيات', 'باسورد', 'كلمة مرور', 'اضافة مستخدم', 'حذف مستخدم'],
            response: 'لإدارة المستخدمين، تعيين الصلاحيات، أو تغيير كلمات المرور، يمكنك الانتقال إلى شاشة <b>المستخدمين والصلاحيات</b>.',
            link: 'users-roles.html', linkText: '🔐 إدارة المستخدمين'
        },
        {
            keywords: ['نسخ احتياطي', 'باك اب', 'استرجاع', 'بيانات', 'backup'],
            response: 'لأخذ نسخة احتياطية من النظام أو استعادة البيانات، يرجى زيارة قسم <b>النسخ الاحتياطي (Backup)</b>.',
            link: 'backup.html', linkText: '💾 النسخ الاحتياطي'
        },
        {
            keywords: ['مخزن', 'مستودع', 'منتجات', 'اصناف', 'أصناف', 'وحدات', 'خزينة', 'صندوق', 'بنك', 'بنوك'],
            response: 'لإضافة وتعديل (الأصناف، المنتجات، المخازن، الخزائن، والبنوك)، يرجى الذهاب إلى شاشة <b>البيانات الأساسية</b>.',
            link: 'master-data.html', linkText: '🗂️ البيانات الأساسية'
        },
        {
            keywords: ['جرد', 'حركة صنف', 'حركة مخزن', 'تقارير المخزون', 'مخزون'],
            response: 'لمشاهدة حركة الأصناف، الجرد، وتقارير المخزون، يرجى التوجه إلى قسم <b>تقارير المخزون</b>.',
            link: 'inventory-report.html', linkText: '📦 تقارير المخزون'
        },
        {
            keywords: ['سند', 'سندات', 'قبض', 'صرف', 'قيد', 'قيود', 'يومية', 'دفتر'],
            response: 'لإنشاء سندات القبض أو الصرف، أو إدخال قيود اليومية، يرجى الذهاب إلى <b>مركز السندات (القيود والسندات)</b>.',
            link: 'vouchers-hub.html', linkText: '🧾 مركز السندات والقيود'
        },
        {
            keywords: ['ميزانية', 'ارباح', 'خسائر', 'أرباح', 'ميزان مراجعة', 'تقارير مالية', 'مالية'],
            response: 'لعرض الميزانية، الأرباح والخسائر، أو ميزان المراجعة، يرجى زيارة <b>مركز التقارير المالية</b>.',
            link: 'financial-reports.html', linkText: '📈 التقارير المالية'
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
    }

    function processUserMessage(text) {
        const query = text.trim();
        if(!query) return;

        appendMessage(query, 'user');
        input.value = '';

        setTimeout(() => {
            let matched = false;
            
            // 1. Try Dynamic Queries (NLP Regex)
            for(let rule of dynamicKb) {
                const match = query.match(rule.regex);
                if(match) {
                    appendMessage(rule.handler(match), 'bot');
                    matched = true;
                    break;
                }
            }

            if(matched) return;

            // 2. Try Static Keywords
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

            // 3. Fallback
            if(!matched) {
                const fallbackResponse = 'عذراً، لم أتمكن من إيجاد المطلوب بدقة في بيانات النظام. هل يمكنك إعادة صياغة السؤال؟';
                const fallbackBtn = `<br><a href="https://wa.me/249113981477" class="chat-action-btn chat-whatsapp-btn" target="_blank">💬 التحدث مع فريق الدعم</a>`;
                appendMessage(fallbackResponse, 'bot', fallbackBtn);
            }
        }, 500);
    }

    sendBtn.addEventListener('click', () => { processUserMessage(input.value); });
    input.addEventListener('keypress', (e) => { if(e.key === 'Enter') processUserMessage(input.value); });

})();
