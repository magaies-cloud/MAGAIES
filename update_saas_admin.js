const fs = require('fs');
const path = require('path');

const filePath = 'c:/xampp/htdocs/magaees/saas-admin.html';
let content = fs.readFileSync(filePath, 'utf8');

const htmlToInsert = `
        <div class="glass-panel" style="margin-bottom: 20px;">
            <h3 style="color: var(--theme-orange); margin-bottom: 15px;">إعدادات النظام العامة (لجميع الشركات)</h3>
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>سعر الصرف اليومي (مثال: الدولار = 3.75)</label>
                    <input type="number" id="global-exchange-rate" step="0.01" value="1.00">
                </div>
                <div class="form-group" style="flex: 1; min-width: 250px; display: flex; align-items: center; gap: 10px; margin-top: 25px;">
                    <input type="checkbox" id="global-exchange-visible" style="width: 20px; height: 20px;">
                    <label for="global-exchange-visible" style="margin: 0;">إظهار سعر الصرف في الشاشات</label>
                </div>
                <div style="margin-top: 25px;">
                    <button class="btn-primary" onclick="saveGlobalSettings()" style="background: var(--theme-orange); padding: 12px 25px;">حفظ الإعدادات</button>
                </div>
            </div>
        </div>
`;

// Insert HTML before the table's glass-panel
content = content.replace('<div class="glass-panel">\r\n            <div class="table-responsive">', htmlToInsert + '\n        <div class="glass-panel">\r\n            <div class="table-responsive">');
// If windows line endings didn't match, fallback:
content = content.replace('<div class="glass-panel">\n            <div class="table-responsive">', htmlToInsert + '\n        <div class="glass-panel">\n            <div class="table-responsive">');


const jsToInsert = `
        async function loadGlobalSettings() {
            try {
                const res = await fetch('api_index.php/saas/settings/exchange-rate', {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                if(data) {
                    document.getElementById('global-exchange-rate').value = data.rate || 1.0;
                    document.getElementById('global-exchange-visible').checked = data.visible === true || data.visible === "true" || data.visible === 1;
                }
            } catch(e) {}
        }

        async function saveGlobalSettings() {
            const rate = document.getElementById('global-exchange-rate').value;
            const visible = document.getElementById('global-exchange-visible').checked;
            try {
                const res = await fetch('api_index.php/saas/settings/exchange-rate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ rate: rate, visible: visible })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم حفظ الإعدادات العامة بنجاح');
                } else {
                    alert(data.error || 'خطأ في الحفظ');
                }
            } catch(e) {
                alert('فشل الاتصال بالخادم');
            }
        }
`;

// Insert loadGlobalSettings() inside loadTenants();
// Wait, better to call it alongside loadTenants() in loginSaas();
content = content.replace('loadTenants();', 'loadTenants();\n                    loadGlobalSettings();');

// Insert JS functions before </script>
content = content.replace('</script>\n</body>', jsToInsert + '\n    </script>\n</body>');
content = content.replace('</script>\r\n</body>', jsToInsert + '\r\n    </script>\r\n</body>');

fs.writeFileSync(filePath, content, 'utf8');
console.log('saas-admin.html updated successfully');
