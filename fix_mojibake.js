const fs = require('fs');

function fixMojibake(str) {
    try {
        // If it's a typical UTF-8 interpreted as Latin-1/Windows-1252
        // We can convert the string to a Buffer using 'binary' (Latin-1) and then decode as 'utf-8'.
        // Let's test on a small chunk first, but we apply it to anything that looks like mojibake.
        // Actually, let's just do it for characters that are clearly mojibake:
        return Buffer.from(str, 'binary').toString('utf8');
    } catch(e) {
        return str;
    }
}

const file = 'js/direct-sales.js';
let content = fs.readFileSync(file, 'utf8');

// Find all strings containing Ø or Ù or ð
// Or simply, we can replace specific known mojibake manually to be safe.
const replacements = {
    "Ø§Ø¨Ø­Ø« Ø£Ùˆ Ø§Ø®ØªØ± Ø§Ù„ØµÙ†Ù ...": "ابحث أو اختر الصنف...",
    "ðŸ—‘ï¸  ØªÙ… Ø­Ø°Ù  Ø§Ù„ØµÙ†Ù ": "🗑️ تم حذف الصنف",
    "Ù…Ù„Ø­ÙˆØ¸Ø©...": "ملحوظة...",
    "Ø®Ø·Ø£ Ù ÙŠ Ø§Ù„Ø§ØªØµØ§Ù„": "خطأ في الاتصال",
    "Ø³Ø¬Ù„ Ø£Ø³Ø¹Ø§Ø± Ø§Ù„ØµÙ†Ù  Ù„Ù„Ø¹Ù…ÙŠÙ„": "سجل أسعار الصنف للعميل",
    "ØªØ¹Ø¯ÙŠÙ„ Ø§Ù„Ø³Ø·Ø±": "تعديل السطر",
    "ØªØ¹Ø¯ÙŠÙ„": "تعديل",
    "Ø§Ù„ÙƒÙ…ÙŠØ© Ø§Ù„ÙˆØ­Ø¯Ø©": "الكمية الوحدة",
    "Ø§Ù„Ø´ÙŠÙ„Ø©": "الشيلة",
    "Ø§Ù„Ø³Ø¹Ø± (Ù…Ø­Ù„ÙŠ / Ø£Ø¬Ù†Ø¨ÙŠ)": "السعر (محلي / أجنبي)",
    "Ø£Ø¬Ù†Ø¨ÙŠ": "أجنبي",
    "Ø¬Ù†ÙŠÙ‡": "جنيه",
    "Ù†Ù Ø³ Ø§Ù„Ù Ø§ØªÙˆØ±Ø© â†©": "نفس الفاتورة ↵"
};

for (const [mojibake, correct] of Object.entries(replacements)) {
    content = content.split(mojibake).join(correct);
}

// Also fix the hideItemDropdownDelayed bug
content = content.replace("listEl.classList.remove('show'); listEl.style.display = 'none';", "listEl.classList.remove('show');");

fs.writeFileSync(file, content, 'utf8');
console.log('Fixed mojibake and hideItemDropdownDelayed bug in direct-sales.js');
