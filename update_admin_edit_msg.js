const fs = require('fs');
const filePath = 'c:/xampp/htdocs/magaees/saas-admin.html';
let content = fs.readFileSync(filePath, 'utf8');

const newLogic = `
        // --- BROADCAST MESSAGES LOGIC ---
        let currentMsgId = 0;
        let allMessagesData = [];

        function openMessageModal(isEdit = false) {
            if (!isEdit) {
                currentMsgId = 0;
                document.getElementById('msg-title').value = '';
                document.getElementById('msg-body').value = '';
                document.getElementById('msg-show-once').checked = true;
                document.getElementById('msg-theme').value = 'info';
            }
            
            const targetSelect = document.getElementById('msg-targets');
            targetSelect.innerHTML = '<option value="all">جميع الشركات</option>';
            allTenants.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.company_name;
                targetSelect.appendChild(opt);
            });
            
            // Re-select target if editing
            if (isEdit) {
                const msg = allMessagesData.find(m => m.id === currentMsgId);
                if (msg) {
                    try {
                        const tArr = JSON.parse(msg.target_tenants);
                        if(tArr[0] !== 'all') {
                            document.getElementById('msg-targets').value = tArr[0];
                        }
                    } catch(e) {}
                }
            }
            
            document.getElementById('message-modal').style.display = 'flex';
        }

        function closeMessageModal() {
            document.getElementById('message-modal').style.display = 'none';
        }

        function editMessage(id) {
            const msg = allMessagesData.find(m => m.id === id);
            if (!msg) return;
            currentMsgId = msg.id;
            document.getElementById('msg-title').value = msg.title;
            document.getElementById('msg-body').value = msg.message;
            document.getElementById('msg-theme').value = msg.theme;
            document.getElementById('msg-show-once').checked = msg.show_once == 1;
            openMessageModal(true);
        }

        async function deleteMessage(id) {
            if (!confirm('هل أنت متأكد من حذف هذه الرسالة؟')) return;
            try {
                const res = await fetch('api_index.php/saas/messages', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ id: id })
                });
                const data = await res.json();
                if(data.success) {
                    loadMessages();
                } else {
                    alert(data.error);
                }
            } catch (e) {
                alert('فشل الاتصال');
            }
        }

        async function loadMessages() {
            try {
                const res = await fetch('api_index.php/saas/messages', {
                    headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN }
                });
                const data = await res.json();
                allMessagesData = data || [];
                const tbody = document.getElementById('messages-body');
                tbody.innerHTML = '';
                
                if(!allMessagesData || allMessagesData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">لا توجد رسائل حالياً</td></tr>';
                    return;
                }
                
                allMessagesData.forEach(m => {
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
                                    ? \`<button class="btn-warning" style="padding:4px 8px; width:auto; font-size:0.75rem;" onclick="toggleMessage(\${m.id}, 0)">إيقاف</button>\` 
                                    : \`<button class="btn-primary" style="padding:4px 8px; width:auto; font-size:0.75rem;" onclick="toggleMessage(\${m.id}, 1)">تفعيل</button>\`}
                                <button class="btn-primary" style="background:#6366f1; padding:4px 8px; width:auto; font-size:0.75rem;" onclick="editMessage(\${m.id})">تعديل</button>
                                <button class="btn-danger" style="padding:4px 8px; width:auto; font-size:0.75rem;" onclick="deleteMessage(\${m.id})">حذف</button>
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
                        id: currentMsgId,
                        title: title,
                        message: body,
                        target_tenants: targetsArr,
                        theme: theme,
                        show_once: showOnce
                    })
                });
                const data = await res.json();
                if(data.success) {
                    alert(currentMsgId ? 'تم التعديل بنجاح' : 'تم الإرسال بنجاح');
                    closeMessageModal();
                    loadMessages();
                } else {
                    alert(data.error || 'حدث خطأ');
                }
            } catch(e) {
                alert('فشل الاتصال');
            }
        }
`;

content = content.replace(/\/\/ --- BROADCAST MESSAGES LOGIC ---[\s\S]*?async function toggleMessage/m, newLogic + '\n        async function toggleMessage');
fs.writeFileSync(filePath, content, 'utf8');
console.log('Done updating admin logic');
