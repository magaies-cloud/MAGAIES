const fs = require('fs');

const adminFile = 'saas-admin.html';
let content = fs.readFileSync(adminFile, 'utf8');

// 1. Add companyName field to Ad modal
const adModalTarget = `            <input type="hidden" id="ad-existing-image">`;
const adModalInject = `            <input type="hidden" id="ad-existing-image">\n            <div class="form-group">\n                <label>اسم الشركة المعلنة (التفاصيل)</label>\n                <input type="text" id="ad-company" placeholder="مثال: مصنع الأسعد للحديد">\n            </div>`;
if (content.includes(adModalTarget) && !content.includes('ad-company')) {
    content = content.replace(adModalTarget, adModalInject);
}

// 2. Add companyName to table header
const adTableTarget = `                        <th>صورة الإعلان</th>\n                        <th>الحالة</th>\n                        <th>إجراءات</th>`;
const adTableInject = `                        <th>صورة الإعلان</th>\n                        <th>اسم الشركة</th>\n                        <th>الحالة</th>\n                        <th>إجراءات</th>`;
if (content.includes(adTableTarget)) {
    content = content.replace(adTableTarget, adTableInject);
}

// 3. Update renderAds to show companyName
const renderAdsTarget = `                        <td><img src="\${ad.image}" style="max-height: 40px; border-radius: 4px;"></td>\n                        <td>\${statusHtml}</td>`;
const renderAdsInject = `                        <td><img src="\${ad.image}" style="max-height: 40px; border-radius: 4px;"></td>\n                        <td><strong>\${ad.companyName || 'بدون اسم'}</strong></td>\n                        <td>\${statusHtml}</td>`;
if (content.includes(renderAdsTarget)) {
    content = content.replace(renderAdsTarget, renderAdsInject);
}

// 4. Update openAdModal to clear it
const openAdTarget = `            document.getElementById('ad-existing-image').value = '';`;
const openAdInject = `            document.getElementById('ad-existing-image').value = '';\n            document.getElementById('ad-company').value = '';`;
if (content.includes(openAdTarget)) {
    content = content.replace(openAdTarget, openAdInject);
}

// 5. Update editAd to fill it
const editAdTarget = `            document.getElementById('ad-existing-image').value = ad.image;`;
const editAdInject = `            document.getElementById('ad-existing-image').value = ad.image;\n            document.getElementById('ad-company').value = ad.companyName || '';`;
if (content.includes(editAdTarget)) {
    content = content.replace(editAdTarget, editAdInject);
}

// 6. Update saveAd to grab and save it
const saveAdTarget = `            const active = document.getElementById('ad-active').checked;`;
const saveAdInject = `            const active = document.getElementById('ad-active').checked;\n            const companyName = document.getElementById('ad-company').value;`;
if (content.includes(saveAdTarget)) {
    content = content.replace(saveAdTarget, saveAdInject);
}

const saveAdObjUpdateTarget = `                        allAds[index].image = imageUrl;\n                        allAds[index].active = active;`;
const saveAdObjUpdateInject = `                        allAds[index].image = imageUrl;\n                        allAds[index].active = active;\n                        allAds[index].companyName = companyName;`;
if (content.includes(saveAdObjUpdateTarget)) {
    content = content.replace(saveAdObjUpdateTarget, saveAdObjUpdateInject);
}

const saveAdObjPushTarget = `                        image: imageUrl,\n                        active: active\n                    });`;
const saveAdObjPushInject = `                        image: imageUrl,\n                        active: active,\n                        companyName: companyName\n                    });`;
if (content.includes(saveAdObjPushTarget)) {
    content = content.replace(saveAdObjPushTarget, saveAdObjPushInject);
}

fs.writeFileSync(adminFile, content, 'utf8');
console.log('saas-admin.html ads updated.');
