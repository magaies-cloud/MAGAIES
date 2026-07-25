
// Debug wrapper: override runReport to catch and display errors
document.addEventListener('DOMContentLoaded', function() {
    // Wait for DBReady to fire, then wrap runReport
    var checkInterval = setInterval(function() {
        if (typeof window.runReport === 'function' && !window._runReportWrapped) {
            window._runReportWrapped = true;
            var originalRunReport = window.runReport;
            window.runReport = function() {
                try {
                    console.log('[DEBUG] runReport called. ALL_PARTNERS:', (typeof ALL_PARTNERS !== 'undefined' ? ALL_PARTNERS.length : 'UNDEF'), 'ALL_TXNS:', (typeof ALL_TXNS !== 'undefined' ? ALL_TXNS.length : 'UNDEF'));
                    originalRunReport();
                    console.log('[DEBUG] runReport completed successfully');
                } catch(e) {
                    alert('خطأ في التقرير: ' + e.message + '\n\nStack: ' + e.stack);
                    console.error('[DEBUG] runReport error:', e);
                }
            };
            clearInterval(checkInterval);
            console.log('[DEBUG] runReport wrapped successfully');
        }
    }, 200);
    
    // Also check after 10 seconds if runReport was ever defined
    setTimeout(function() {
        if (typeof window.runReport !== 'function') {
            console.error('[DEBUG] runReport is still not defined after 10 seconds!');
            var tbody = document.getElementById('balances-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" style="color:red;text-align:center;padding:20px;font-size:1.2em;">⚠️ خطأ: لم يتم تحميل ملف التقرير بشكل صحيح. يرجى مسح الكاش (Ctrl+Shift+Delete) وإعادة المحاولة.</td></tr>';
            }
        }
    }, 10000);
});
