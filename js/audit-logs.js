document.addEventListener('DBReady', () => {
    if (!localStorage.getItem('authToken')) {
        window.location.href = 'login.html';
        return;
    }
    renderLogs();
});

function renderLogs() {
    const list = document.getElementById('log-list');
    const logs = JSON.parse(localStorage.getItem('erp_audit_logs') || '[]');

    if (logs.length === 0) {
        list.innerHTML = `<div style="text-align:center; padding:50px; color:var(--text-secondary);">لا توجد نشاطات مسجلة حتى الآن.</div>`;
        return;
    }

    list.innerHTML = logs.map(log => {
        let icon = '📝';
        let cardClass = 'glass-panel';
        
        // Basic icon assignment based on action type
        if (log.actionType && log.actionType.toLowerCase().includes('delete')) {
            icon = '🗑️';
            cardClass += ' danger';
        } else if (log.actionType && log.actionType.toLowerCase().includes('add')) {
            icon = '✨';
            cardClass += ' success';
        } else if (log.module === 'LOGIN') {
            icon = '🔑';
            cardClass += ' success';
        } else if (log.module === 'SALES') {
            icon = '🛒';
        } else if (log.module === 'FINANCE') {
            icon = '💰';
        }

        const dateObj = new Date(log.timestamp);
        const timeStr = dateObj.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
        const dateStr = dateObj.toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' });

        return `
            <div class="log-card ${cardClass}">
                <div class="log-icon">${icon}</div>
                <div class="log-content">
                    <div class="log-header">
                        <span class="log-user">${log.userName || log.userId || 'مجهول'}</span>
                        <span class="log-time">${dateStr} - ${timeStr}</span>
                    </div>
                    <div class="log-action">${log.description}</div>
                    <div class="log-meta">الوحدة: ${log.module || 'عام'} | نوع الحدث: ${log.actionType || 'N/A'}</div>
                </div>
            </div>
        `;
    }).join('');
}
