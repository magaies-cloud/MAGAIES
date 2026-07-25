
function runAISmartSearch() {
    const input = document.getElementById('account-search-input');
    const q = input.value.trim();
    const btn = document.getElementById('btn-smart-search');
    
    if (!q) {
        showToast('الرجاء كتابة وصف للحساب أولاً', 'warning');
        input.focus();
        return;
    }

    if (typeof ALL_ACCOUNTS === 'undefined' || ALL_ACCOUNTS.length <= 1) {
        showToast('قائمة الحسابات غير متوفرة بعد', 'error');
        return;
    }

    btn.innerHTML = '⏳...';
    btn.disabled = true;

    let accountsList = ALL_ACCOUNTS.filter(a => a.id !== 'ALL').map(a => `${a.id} - ${a.name} (${a.type})`).join('\n');
    
    let promptMsg = "أنت مساعد بحث ذكي في نظام محاسبي. المستخدم يبحث عن الحساب الأقرب للمعنى التالي: '" + q + "'.\nإليك قائمة الحسابات المتاحة:\n" + accountsList + "\n\nاستخرج فقط وفقط 'ID' (الكود) الخاص بأقرب حساب يطابق نية المستخدم. لا تكتب أي كلمة أخرى، فقط الـ ID.";

    fetch('api/ai-assistant.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: promptMsg, context: '' })
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        
        if (data.reply) {
            let matchId = data.reply.trim().replace(/[^a-zA-Z0-9-]/g, '');
            let matchedAccount = ALL_ACCOUNTS.find(a => a.id === matchId);
            
            if (matchedAccount) {
                if (typeof selectAccount === 'function') {
                    selectAccount(matchedAccount.id, matchedAccount.name, matchedAccount.icon || '🏷️');
                    showToast('تم إيجاد الحساب الذكي: ' + matchedAccount.name);
                }
            } else {
                showToast('لم يتم العثور على حساب مطابق بدقة.', 'warning');
            }
        } else {
            showToast('حدث خطأ في البحث الذكي.', 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = '🤖 ذكي';
        btn.disabled = false;
        showToast('تعذر الاتصال بالخادم.', 'error');
    });
}
