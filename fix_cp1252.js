const fs = require('fs');
let content = fs.readFileSync('js/direct-sales.js', 'utf8');

const fixes = {
    "ابحث أÙˆ اختر اÙ„صÙ†ف...": "ابحث أو اختر الصنف...",
    "اÙ„Ù…خزÙ† اÙ„رئÙŠسÙŠ (اÙ„سجاÙ†ة)": "المخزن الرئيسي (السجانة)",
    "اÙ„فرع اÙ„رئÙŠسÙŠ": "الفرع الرئيسي",
    "فرع بحرÙŠ": "فرع بحري",
    "فرع أÙ…درÙ…اÙ†": "فرع أمدرمان",
    "زبÙˆÙ† Ù†Ù‚دÙŠ (Walk-in)": "زبون نقدي (Walk-in)",
    "طÙ†": "طن",
    "ÙƒÙŠس": "كيس",
    "سÙŠخة / عÙˆد": "سيخة / عود",
    "حبة / Ù‚طعة": "حبة / قطعة",
    "جاÙ„ÙˆÙ†": "جالون",
    "Ù‚طعة": "قطعة",
    "عÙ…ÙŠÙ„ Ù†Ù‚دÙŠ": "عميل نقدي",
    "Ù„ا تÙˆجد Ùنتائج Ù…طابÙ‚ة Ù„Ù„بحث": "لا توجد نتائج مطابقة للبحث",
    "حدث خطأ داخÙ„ÙŠ": "حدث خطأ داخلي"
};

for (const [broken, fixed] of Object.entries(fixes)) {
    content = content.split(broken).join(fixed);
}

fs.writeFileSync('js/direct-sales.js', content, 'utf8');
console.log('Fixed UI strings');
