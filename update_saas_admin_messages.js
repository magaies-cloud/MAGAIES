const fs = require('fs');
const path = require('path');

const filePath = 'c:/xampp/htdocs/magaees/saas-admin.html';
let content = fs.readFileSync(filePath, 'utf8');

const messageHtml = `
        <!-- Broadcast Messages Card -->
        <div class="glass-panel" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #6366f1; margin: 0;">رسائل النظام المنبثقة (Broadcast)</h3>
                <button class="btn-primary" style="background: #6366f1; width: auto; padding: 8px 15px;" onclick="openMessageModal()">+ رسالة جديدة</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>العنوان</th>
                            <th>الجمهور</th>
                            <th>التكرار</th>
                            <th>اللون</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="messages-body">
                        <tr><td colspan="6" style="text-align:center;">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Broadcast Message Modal -->
        <div id="message-modal" class="modal-overlay">
            <div class="glass-panel modal-content" style="max-width: 600px;">
                <h3 style="color:#fff; margin-bottom: 20px;">إنشاء رسالة نظام جديدة</h3>
                
                <div class="form-group">
                    <label>عنوان الرسالة</label>
                    <input type="text" id="msg-title" placeholder="مثال: تحديث جديد للنظام">
                </div>

                <div class="form-group">
                    <label>محتوى الرسالة</label>
                    <textarea id="msg-body" rows="4" placeholder="اكتب رسالتلك هنا..."></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>الجمهور المستهدف</label>
                        <select id="msg-targets">
                            <option value="all">جميع الشركات</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label>لون الرسالة</label>
                        <select id="msg-theme">
                            <option value="info">معلومة (أزرق)</option>
                            <option value="success">نجاح (أخضر)</option>
                            <option value="warning">تنبيه (برتقالي)</option>
                            <option value="danger">خطر/هام (أحمر)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px;">
                    <input type="checkbox" id="msg-show-once" style="width: 20px; height: 20px;" checked>
                    <div>
                        <label for="msg-show-once" style="margin: 0; display: block;">عرض مرة واحدة فقط لكل مستخدم</label>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">إذا لم تقم بالتحديد، ستظهر الرسالة للمستخدم في كل مرة يسجل فيها الدخول.</span>
                    </div>
                </div>

                <div class="modal-actions" style="margin-top: 20px;">
                    <button class="btn-primary" style="flex:1; background: #6366f1;" onclick="saveMessage()">إرسال ونشر</button>
                    <button class="btn-cancel" onclick="closeMessageModal()">إلغاء</button>
                </div>
            </div>
        </div>
`;

// Insert UI before the tenants table glass-panel
content = content.replace(/<div class="glass-panel">\s*<div class="table-responsive">/, messageHtml + '\n        <div class="glass-panel">\n            <div class="table-responsive">');


const jsLogic = `
        // --- BROADCAST MESSAGES LOGIC ---
        function openMessageModal() {
            document.getElementById('msg-title').value = '';
            document.getElementById('msg-body').value = '';
            document.getElementById('msg-show-once').checked = true;
            document.getElementById('msg-theme').value = 'info';
            
            const targetSelect = document.getElementById('msg-targets');
            targetSelect.innerHTML = '<option value="all">جميع الشركات</option>';
            allTenants.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.company_name;
                targetSelect.appendChild(opt);
            });
            
            document.getElementById('message-modal').style.display = 'flex';
        }

        function closeMessageModal() {
            document.getElementById('message-modal').style.display = 'none';
        }

        async function loadMessages() {
            try {
                const res = await fetch('api_index.php/saas/messages', {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                const tbody = document.getElementById('messages-body');
                tbody.innerHTML = '';
                
                if(!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">لا توجد رسائل حالياً</td></tr>';
                    return;
                }
                
                data.forEach(m => {
                    let targets = 'الجميع';
                    try {
                        const tArr = JSON.parse(m.target_tenants);
                        if(tArr[0] !== 'all') {
                            targets = 'شركة محددة';
                        }
                    } catch(e) {}
                    
                    const statusHtml = m.is_active == 1 
                        ? '<span class="badge badge-active">فعالة</span>' 
                        : '<span class="badge badge-suspended">متوقفة</span>';
                        
                    const themeColors = {
                        'info': '#3b82f6',
                        'success': '#10b981',
                        'warning': '#f59e0b',
                        'danger': '#ef4444'
                    };
                    
                    tbody.innerHTML += \`
                        <tr>
                            <td style="font-weight: 800; color: \${themeColors[m.theme] || '#fff'}">\${m.title}</td>
                            <td>\${targets}</td>
                            <td>\${m.show_once == 1 ? 'مرة واحدة' : 'كل تسجيل دخول'}</td>
                            <td>\${m.theme}</td>
                            <td>\${statusHtml}</td>
                            <td>
                                \${m.is_active == 1 
                                    ? \`<button class="btn-danger" style="padding:4px 8px; width:auto; font-size:0.75rem;" onclick="toggleMessage(\${m.id}, 0)">إيقاف</button>\` 
                                    : \`<button class="btn-primary" style="padding:4px 8px; width:auto; font-size:0.75rem;" onclick="toggleMessage(\${m.id}, 1)">تفعيل</button>\`}
                            </td>
                        </tr>
                    \`;
                });
            } catch (e) {
                console.error(e);
            }
        }

        async function saveMessage() {
            const title = document.getElementById('msg-title').value;
            const body = document.getElementById('msg-body').value;
            const target = document.getElementById('msg-targets').value;
            const theme = document.getElementById('msg-theme').value;
            const showOnce = document.getElementById('msg-show-once').checked ? 1 : 0;
            
            if(!title || !body) return alert('الرجاء إدخال العنوان والمحتوى');
            
            const targetsArr = target === 'all' ? ['all'] : [parseInt(target)];
            
            try {
                const res = await fetch('api_index.php/saas/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({
                        title: title,
                        message: body,
                        target_tenants: targetsArr,
                        theme: theme,
                        show_once: showOnce
                    })
                });
                const data = await res.json();
                if(data.success) {
                    alert('تم الإرسال بنجاح');
                    closeMessageModal();
                    loadMessages();
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }
        
        async function toggleMessage(id, isActive) {
            try {
                const res = await fetch('api_index.php/saas/messages/toggle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id: id, is_active: isActive })
                });
                const data = await res.json();
                if(data.success) {
                    loadMessages();
                }
            } catch (e) {}
        }
`;

// Insert loadMessages() inside loadTenants();
content = content.replace('loadGlobalSettings();', 'loadGlobalSettings();\n                    loadMessages();');

// Insert JS functions before </script>
content = content.replace('</script>\n</body>', jsLogic + '\n    </script>\n</body>');
content = content.replace('</script>\r\n</body>', jsLogic + '\r\n    </script>\r\n</body>');

fs.writeFileSync(filePath, content, 'utf8');
console.log('saas-admin.html updated successfully with messages logic');
