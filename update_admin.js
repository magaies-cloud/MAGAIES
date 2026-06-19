const fs = require('fs');
let html = fs.readFileSync('saas-admin.html', 'utf8');

// 1. Add Partners tab button
html = html.replace(
    `<button class="tab-btn" onclick="switchTab('tab-plans', this)">💎 الباقات (Plans)</button>`,
    `<button class="tab-btn" onclick="switchTab('tab-plans', this)">💎 الباقات (Plans)</button>\n            <button class="tab-btn" onclick="switchTab('tab-partners', this)">🤝 الشركاء والعملاء</button>`
);

// 2. Add Partners Tab Content (after plans tab)
const plansTabEnd = `                    </table>
                </div>
            </div>
        </div>`;
const partnersTabHtml = `
        <!-- TAB: PARTNERS -->
        <div id="tab-partners" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>الشركاء والعملاء</h3>
                    <button class="btn-primary" onclick="openPartnerModal()">+ شريك جديد</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>اسم الشريك</th>
                                <th>الشعار</th>
                                <th>الرابط</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="partners-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>`;
        
const planTabIndex = html.indexOf('<!-- TAB: BROADCASTS -->');
html = html.slice(0, planTabIndex) + partnersTabHtml + '\n\n        ' + html.slice(planTabIndex);

// 3. Add Partner Modal
const partnerModalHtml = `
    <!-- Partner Modal -->
    <div id="partner-modal" class="modal-overlay">
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
html = html.replace('<!-- User Modal (Add User / Reset Pass) -->', partnerModalHtml + '\n\n    <!-- User Modal (Add User / Reset Pass) -->');

// 4. JS global vars
html = html.replace(`let allPlans = JSON.parse(localStorage.getItem('saas_plans') || '[]');`, `let allPlans = [];\n        let allPartners = [];`);

// remove localStorage plan block
html = html.replace(/if\(allPlans\.length === 0\) \{[\s\S]*?localStorage\.setItem\('saas_plans', JSON\.stringify\(allPlans\)\);\s*\}/, '');

// 5. Update initDashboard
html = html.replace(`            renderPlans();`, `            try {
                const resPub = await fetch('api_index.php?r=public');
                const dataPub = await resPub.json();
                allPlans = dataPub.plans || [];
                allPartners = dataPub.partners || [];
            } catch(e) {}
            renderPlans();
            renderPartners();`);

// 6. Update savePlan
html = html.replace(`localStorage.setItem('saas_plans', JSON.stringify(allPlans));`, `try { await fetch(apiUrl('saas', 'settings', 'plans'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ plans: allPlans }) }); } catch(e){}`);

// 7. Add Partners JS Functions
const partnersJs = `
        function renderPartners() {
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
        }
        function openPartnerModal() {
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
        }
        async function deletePartner(id) {
            if(!confirm('هل أنت متأكد من الحذف؟')) return;
            allPartners = allPartners.filter(x => x.id !== id);
            try { await fetch(apiUrl('saas', 'settings', 'partners'), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN }, body: JSON.stringify({ partners: allPartners }) }); } catch(e){}
            renderPartners();
        }
`;

html = html.replace('// Original API calls ported', partnersJs + '\n        // Original API calls ported');

fs.writeFileSync('saas-admin.html', html);
console.log('saas-admin.html updated successfully.');
