import re

# 1. Update saas-admin.html
admin_file = 'saas-admin.html'
with open(admin_file, 'r', encoding='utf-8') as f:
    admin_content = f.read()

# Add Tab Button
if 'tab-ads' not in admin_content:
    admin_content = admin_content.replace(
        '<button class="tab-btn" onclick="switchTab(\'tab-broadcasts\', this)">📣 رسائل النظام</button>',
        '<button class="tab-btn" onclick="switchTab(\'tab-broadcasts\', this)">📣 رسائل النظام</button>\n            <button class="tab-btn" onclick="switchTab(\'tab-ads\', this)">🌟 الإعلانات المدفوعة</button>'
    )

# Add Tab Content
ad_tab_html = """
        <!-- TAB: ADS -->
        <div id="tab-ads" class="tab-content">
            <div class="glass-panel">
                <div class="flex-between" style="margin-bottom: 20px;">
                    <h3>إدارة الإعلانات (الصفحة الرئيسية)</h3>
                    <button class="btn-primary" onclick="openAdModal()">+ إضافة إعلان جديد</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>صورة الإعلان</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="ads-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: SETTINGS -->"""
if 'id="tab-ads"' not in admin_content:
    admin_content = admin_content.replace('<!-- TAB: SETTINGS -->', ad_tab_html)

# Add Ads Modal
ad_modal_html = """
    <!-- Ads Modal -->
    <div id="ad-modal" class="modal-overlay">
        <div class="glass-panel modal-content">
            <div class="modal-header"><h3>إضافة إعلان</h3><button class="close-btn" onclick="closeModal('ad-modal')">&times;</button></div>
            <div class="form-group">
                <label>صورة الإعلان</label>
                <input type="file" id="ad-image-upload" accept="image/*" style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 8px; width: 100%; color: #fff;">
                <div id="ad-image-preview" style="margin-top:10px; max-height:100px; overflow:hidden; display:none; border-radius:8px;">
                    <img src="" style="width:100%; object-fit:cover;">
                </div>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                <input type="checkbox" id="ad-active" checked> <label style="margin:0;">تفعيل الإعلان وعرضه للزوار</label>
            </div>
            <div class="modal-actions">
                <button class="btn-primary" style="flex:1;" onclick="saveAd()" id="ad-save-btn">حفظ الإعلان</button>
            </div>
            <input type="hidden" id="ad-id">
            <input type="hidden" id="ad-existing-image">
        </div>
    </div>
    
    <script>"""
if 'id="ad-modal"' not in admin_content:
    admin_content = admin_content.replace('<script>', ad_modal_html)

# Update initDashboard
if 'allAds =' not in admin_content:
    admin_content = admin_content.replace(
        'allPartners = dataPub.partners || [];',
        'allPartners = dataPub.partners || [];\n                allAds = dataPub.ads || [];'
    )
    admin_content = admin_content.replace(
        'renderPartners();',
        'renderPartners();\n            renderAds();'
    )

# Add Ads JS Logic
ad_js = """
        // ==========================================
        // ADS MANAGEMENT
        // ==========================================
        let allAds = [];

        function renderAds() {
            const tbody = document.getElementById('ads-body');
            tbody.innerHTML = '';
            if (allAds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">لا توجد إعلانات</td></tr>';
                return;
            }
            allAds.forEach(ad => {
                const statusHtml = ad.active ? '<span style="color:#10b981;">نشط</span>' : '<span style="color:#ef4444;">غير نشط</span>';
                tbody.innerHTML += `
                    <tr>
                        <td><img src="${ad.image}" style="max-height: 40px; border-radius: 4px;"></td>
                        <td>${statusHtml}</td>
                        <td>
                            <button class="btn-primary" style="padding:4px 8px; font-size:0.8rem;" onclick="editAd(${ad.id})">تعديل</button>
                            <button class="btn-danger" style="padding:4px 8px; font-size:0.8rem;" onclick="deleteAd(${ad.id})">حذف</button>
                        </td>
                    </tr>
                `;
            });
        }

        function openAdModal() {
            document.getElementById('ad-id').value = '';
            document.getElementById('ad-image-upload').value = '';
            document.getElementById('ad-active').checked = true;
            document.getElementById('ad-existing-image').value = '';
            document.getElementById('ad-image-preview').style.display = 'none';
            document.getElementById('ad-modal').style.display = 'flex';
        }

        function editAd(id) {
            const ad = allAds.find(a => a.id == id);
            if (!ad) return;
            document.getElementById('ad-id').value = ad.id;
            document.getElementById('ad-image-upload').value = ''; 
            document.getElementById('ad-active').checked = ad.active;
            document.getElementById('ad-existing-image').value = ad.image;
            
            const preview = document.getElementById('ad-image-preview');
            preview.style.display = 'block';
            preview.querySelector('img').src = ad.image;
            
            document.getElementById('ad-modal').style.display = 'flex';
        }

        async function saveAd() {
            const id = document.getElementById('ad-id').value;
            const fileInput = document.getElementById('ad-image-upload');
            const active = document.getElementById('ad-active').checked;
            let imageUrl = document.getElementById('ad-existing-image').value;

            const btn = document.getElementById('ad-save-btn');
            btn.disabled = true;
            btn.innerText = 'جاري الحفظ...';

            try {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('image', file);

                    const uploadRes = await fetch(apiUrl('saas', 'upload-ad'), {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + SAAS_TOKEN },
                        body: formData
                    });
                    const uploadData = await uploadRes.json();
                    if (uploadData.success) {
                        imageUrl = uploadData.url;
                    } else {
                        throw new Error(uploadData.error || 'فشل رفع الصورة');
                    }
                }

                if (!imageUrl) throw new Error('يجب اختيار صورة للإعلان');

                if (id) {
                    const index = allAds.findIndex(a => a.id == id);
                    if (index !== -1) {
                        allAds[index].image = imageUrl;
                        allAds[index].active = active;
                    }
                } else {
                    allAds.push({
                        id: Date.now(),
                        image: imageUrl,
                        active: active
                    });
                }

                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });

                closeModal('ad-modal');
                renderAds();
            } catch (e) {
                alert('خطأ: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.innerText = 'حفظ الإعلان';
            }
        }

        async function deleteAd(id) {
            if (!confirm('هل أنت متأكد من الحذف؟')) return;
            allAds = allAds.filter(a => a.id != id);
            try {
                await fetch(apiUrl('saas', 'settings', 'ads'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + SAAS_TOKEN },
                    body: JSON.stringify({ ads: allAds })
                });
                renderAds();
            } catch (e) {}
        }
    </script>"""
if 'function renderAds()' not in admin_content:
    admin_content = admin_content.replace('</script>', ad_js)

with open(admin_file, 'w', encoding='utf-8') as f:
    f.write(admin_content)

print("saas-admin.html patched")


# 2. Update index.html
index_file = 'index.html'
with open(index_file, 'r', encoding='utf-8') as f:
    index_content = f.read()

index_css = """
        /* Ads Banner */
        .ads-banner {
            width: 100%;
            height: 120px;
            margin-top: 80px; 
            position: relative;
            overflow: hidden;
            display: none;
            background: rgba(15,23,42,0.8);
            border-bottom: 1px solid var(--glass-border);
            z-index: 900;
        }
        .ad-slide {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ad-slide.active { opacity: 1; z-index: 2; }
        .ad-slide img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            cursor: default;
        }
    </style>"""

if '.ads-banner' not in index_content:
    index_content = index_content.replace('</style>', index_css)

index_html = """
    </nav>

    <!-- Ads Banner Section -->
    <div class="ads-banner" id="ads-banner">
        <!-- Ads will be injected here -->
    </div>

    <!-- Hero Section -->"""
if 'id="ads-banner"' not in index_content:
    index_content = index_content.replace('</nav>\n\n    <!-- Hero Section -->', index_html)
    index_content = index_content.replace('</nav>\r\n\r\n    <!-- Hero Section -->', index_html)

index_js = """
                // Ads Logic
                if (data.ads && data.ads.length > 0) {
                    const activeAds = data.ads.filter(ad => ad.active);
                    if (activeAds.length > 0) {
                        const banner = document.getElementById('ads-banner');
                        banner.style.display = 'block';
                        
                        activeAds.forEach((ad, index) => {
                            const slide = document.createElement('div');
                            slide.className = 'ad-slide' + (index === 0 ? ' active' : '');
                            slide.innerHTML = `<img src="${ad.image}" alt="إعلان">`;
                            banner.appendChild(slide);
                        });

                        if (activeAds.length > 1) {
                            let currentAdIndex = 0;
                            const slides = banner.querySelectorAll('.ad-slide');
                            setInterval(() => {
                                slides[currentAdIndex].classList.remove('active');
                                currentAdIndex = (currentAdIndex + 1) % slides.length;
                                slides[currentAdIndex].classList.add('active');
                            }, 5000);
                        }
                    }
                }
"""
if 'data.ads && data.ads.length' not in index_content:
    index_content = index_content.replace(
        'const data = await res.json();',
        'const data = await res.json();\n' + index_js
    )

with open(index_file, 'w', encoding='utf-8') as f:
    f.write(index_content)

print("index.html patched")
