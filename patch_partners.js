const fs = require('fs');

let saasAdmin = fs.readFileSync('saas-admin.html', 'utf8');

saasAdmin = saasAdmin.replace('<th>الرابط</th>', '<th>الحالة</th>');

const renderPartnersTarget = `        function renderPartners() {
            const tbody = document.getElementById('partners-body');
            tbody.innerHTML = '';
            allPartners.forEach(p => {
                tbody.innerHTML += \`
                    <tr>
                        <td><strong>\${p.name}</strong></td>
                        <td><img src="\${p.logo}" height="30" alt="logo"></td>
                        <td><a href="\${p.link}" target="_blank" style="color:var(--primary-accent)">رابط</a></td>
                        <td>
                            <button class="btn-primary" style="padding:5px 10px; font-size:0.8rem;" onclick="editPartner(\${p.id})">تعديل</button>
                            <button class="btn-danger" style="padding:5px 10px; font-size:0.8rem;" onclick="deletePartner(\${p.id})">حذف</button>
                        </td>
                    </tr>
                \`;
            });
        }`;

const renderPartnersInject = `        function renderPartners() {
            const tbody = document.getElementById('partners-body');
            tbody.innerHTML = '';
            allPartners.forEach(p => {
                const statusHtml = (p.active !== false) ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">غير نشط</span>';
                tbody.innerHTML += \`
                    <tr>
                        <td><strong>\${p.name}</strong></td>
                        <td><img src="\${p.logo}" height="30" alt="logo" style="border-radius:4px; max-height:40px;"></td>
                        <td>\${statusHtml}</td>
                        <td>
                            <button class="btn-primary" style="padding:4px 8px; font-size:0.8rem;" onclick="editPartner(\${p.id})">تعديل</button>
                            <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deletePartner(\${p.id})">حذف</button>
                        </td>
                    </tr>
                \`;
            });
        }`;
saasAdmin = saasAdmin.replace(renderPartnersTarget, renderPartnersInject);

const modalTarget = `    <div id="partner-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة شريك</h3><button class="close-btn" onclick="closeModal('partner-modal')">&times;</button></div>
            <input type="hidden" id="partner-id">
            <div class="form-group"><label>الاسم</label><input type="text" id="partner-name"></div>
            <div class="form-group"><label>رابط الشعار</label><input type="text" id="partner-logo"></div>
            <div class="form-group"><label>رابط الموقع</label><input type="text" id="partner-link"></div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="savePartner()">حفظ</button>
            </div>
        </div>
    </div>`;

const modalInject = `    <div id="partner-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إدارة شريك</h3><button class="close-btn" onclick="closeModal('partner-modal')">&times;</button></div>
            <input type="hidden" id="partner-id">
            <input type="hidden" id="partner-existing-logo">
            <div class="form-group"><label>الاسم</label><input type="text" id="partner-name"></div>
            <div class="form-group">
                <label>صورة الشعار (اختيار ملف)</label>
                <input type="file" id="partner-logo-upload" accept="image/*">
                <div id="partner-logo-preview" style="display:none; margin-top:10px; text-align:center;">
                    <img src="" style="max-height:60px; border-radius:8px;">
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="partner-active" checked>
                <label for="partner-active" style="margin:0; font-size:0.9rem; color:var(--text-secondary);">تفعيل العرض في الشاشة الرئيسية</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" id="save-partner-btn" style="flex:1;" onclick="savePartner()">حفظ</button>
            </div>
        </div>
    </div>`;
saasAdmin = saasAdmin.replace(modalTarget, modalInject);

const modalJsTarget = `        function openPartnerModal() {
            document.getElementById('partner-id').value = '';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-logo').value = '';
            document.getElementById('partner-link').value = '';
            document.getElementById('partner-modal').style.display = 'flex';
        }
        function editPartner(id) {
            let p = allPartners.find(x => x.id === id);
            document.getElementById('partner-id').value = p.id;
            document.getElementById('partner-name').value = p.name;
            document.getElementById('partner-logo').value = p.logo;
            document.getElementById('partner-link').value = p.link;
            document.getElementById('partner-modal').style.display = 'flex';
        }
        async function savePartner() {
            let id = document.getElementById('partner-id').value;
            let p = {
                id: id ? parseInt(id) : Date.now(),
                name: document.getElementById('partner-name').value,
                logo: document.getElementById('partner-logo').value,
                link: document.getElementById('partner-link').value
            };
            if(id) {
                let idx = allPartners.findIndex(x => x.id == id);
                allPartners[idx] = p;
            } else { allPartners.push(p); }
            
            try { await fetch(apiUrl('saas', 'settings', 'partners'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ partners: allPartners }) }); } catch(e){}
            closeModal('partner-modal');
            renderPartners();
        }`;

const modalJsInject = `        function openPartnerModal() {
            document.getElementById('partner-id').value = '';
            document.getElementById('partner-name').value = '';
            document.getElementById('partner-existing-logo').value = '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-logo-preview').style.display = 'none';
            document.getElementById('partner-active').checked = true;
            document.getElementById('partner-modal').style.display = 'flex';
        }
        function editPartner(id) {
            let p = allPartners.find(x => x.id === id);
            if(!p) return;
            document.getElementById('partner-id').value = p.id;
            document.getElementById('partner-name').value = p.name || '';
            document.getElementById('partner-existing-logo').value = p.logo || '';
            document.getElementById('partner-logo-upload').value = '';
            document.getElementById('partner-active').checked = (p.active !== false);
            
            const preview = document.getElementById('partner-logo-preview');
            if (p.logo) {
                preview.style.display = 'block';
                preview.querySelector('img').src = p.logo;
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('partner-modal').style.display = 'flex';
        }
        async function savePartner() {
            const btn = document.getElementById('save-partner-btn');
            btn.innerText = 'جاري الحفظ...';
            btn.disabled = true;

            let id = document.getElementById('partner-id').value;
            let name = document.getElementById('partner-name').value;
            let active = document.getElementById('partner-active').checked;
            let existingLogo = document.getElementById('partner-existing-logo').value;
            let fileInput = document.getElementById('partner-logo-upload');
            
            let finalLogo = existingLogo;

            try {
                if (fileInput.files.length > 0) {
                    const formData = new FormData();
                    formData.append('image', fileInput.files[0]);
                    const uploadRes = await fetch(apiUrl('saas', 'upload-partner'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        finalLogo = uploadData.url;
                    } else {
                        alert('خطأ في رفع الصورة: ' + (uploadData.error || ''));
                        btn.innerText = 'حفظ';
                        btn.disabled = false;
                        return;
                    }
                }

                let p = {
                    id: id ? parseInt(id) : Date.now(),
                    name: name,
                    logo: finalLogo,
                    active: active
                };

                if(id) {
                    let idx = allPartners.findIndex(x => x.id == id);
                    if(idx !== -1) allPartners[idx] = p;
                } else { 
                    allPartners.push(p); 
                }
                
                await fetch(apiUrl('saas', 'settings', 'partners'), { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, 
                    body: JSON.stringify({ partners: allPartners }) 
                });
                
                closeModal('partner-modal');
                renderPartners();
            } catch(e) {
                console.error(e);
                alert('حدث خطأ أثناء الحفظ');
            } finally {
                btn.innerText = 'حفظ';
                btn.disabled = false;
            }
        }`;

if(saasAdmin.includes(modalJsTarget)) {
    saasAdmin = saasAdmin.replace(modalJsTarget, modalJsInject);
} else {
    console.log("Could not find modalJsTarget in saas-admin.html");
}
fs.writeFileSync('saas-admin.html', saasAdmin, 'utf8');

let apiIndex = fs.readFileSync('api_index.php', 'utf8');
const apiTarget = `            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                jsonResponse(['success' => true, 'url' => 'uploads/ads/' . $filename]);
            } else {
                jsonResponse(['error' => 'Failed to save file'], 500);
            }
        }`;

const apiInject = `            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                jsonResponse(['success' => true, 'url' => 'uploads/ads/' . $filename]);
            } else {
                jsonResponse(['error' => 'Failed to save file'], 500);
            }
        }
        
        // --- UPLOAD PARTNER LOGO ---
        if ($method === 'POST' && $action === 'upload-partner') {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(['error' => 'No file uploaded or upload error'], 400);
            }
            $file = $_FILES['image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array(strtolower($ext), $allowed)) {
                jsonResponse(['error' => 'Invalid file type'], 400);
            }
            
            $dir = __DIR__ . '/uploads/partners/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            
            $filename = 'partner_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = $dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                jsonResponse(['success' => true, 'url' => 'uploads/partners/' . $filename]);
            } else {
                jsonResponse(['error' => 'Failed to save file'], 500);
            }
        }`;

if (!apiIndex.includes('upload-partner') && apiIndex.includes(apiTarget)) {
    apiIndex = apiIndex.replace(apiTarget, apiInject);
    fs.writeFileSync('api_index.php', apiIndex, 'utf8');
} else {
    console.log("Could not inject api_index.php or already injected");
}

let indexHtml = fs.readFileSync('index.html', 'utf8');
const indexTarget = `                    const renderLogos = () => {
                        data.partners.forEach(p => {
                            html += \`<a href="\${p.link || '#'}" class="partner-logo" target="_blank" title="\${p.name}">
                                <img src="\${p.logo}" alt="\${p.name}"> \${p.name}
                            </a>\`;
                        });
                    };`;

const indexInject = `                    const renderLogos = () => {
                        data.partners.forEach(p => {
                            if (p.active === false) return;
                            html += \`<div class="partner-logo" title="\${p.name}" style="display:inline-flex; align-items:center; gap:8px;">
                                <img src="\${p.logo}" alt="\${p.name}" style="height:35px; border-radius:4px;"> \${p.name}
                            </div>\`;
                        });
                    };`;

if(indexHtml.includes(indexTarget)) {
    indexHtml = indexHtml.replace(indexTarget, indexInject);
    fs.writeFileSync('index.html', indexHtml, 'utf8');
} else {
    console.log("Could not find indexTarget in index.html");
}

console.log("Patching completed.");
