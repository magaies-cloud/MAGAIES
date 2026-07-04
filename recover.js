const fs = require('fs');

// Reverse mapping for Windows-1252 extra characters (0x80 - 0x9F)
const win1252Rev = {
    0x20AC: 0x80, 0x0192: 0x83, 0x201E: 0x84, 0x2026: 0x85, 0x2020: 0x86, 0x2021: 0x87,
    0x02C6: 0x88, 0x2030: 0x89, 0x0160: 0x8A, 0x2039: 0x8B, 0x0152: 0x8C, 0x017D: 0x8E,
    0x2018: 0x91, 0x2019: 0x92, 0x201C: 0x93, 0x201D: 0x94, 0x2022: 0x95, 0x2013: 0x96,
    0x2014: 0x97, 0x02DC: 0x98, 0x2122: 0x99, 0x0161: 0x9A, 0x203A: 0x9B, 0x0153: 0x9C,
    0x017E: 0x9E, 0x0178: 0x9F,
    // Add U+201A (‚) -> 0x82
    0x201A: 0x82
};

function recoverWindows1252ToUtf8(str) {
    const bytes = [];
    for (let i = 0; i < str.length; i++) {
        const code = str.charCodeAt(i);
        if (code in win1252Rev) {
            bytes.push(win1252Rev[code]);
        } else if (code <= 0xFF) {
            bytes.push(code);
        } else {
            // If it's a character > 255 that is not in the win1252 table,
            // it might be a character that was ALREADY correct UTF-8 or something else.
            // But we have to encode it to UTF-8 bytes to preserve it.
            const utf8Bytes = Buffer.from(str[i], 'utf8');
            for (const b of utf8Bytes) {
                bytes.push(b);
            }
        }
    }
    return Buffer.from(bytes).toString('utf8');
}

const content = fs.readFileSync('js/direct-sales-v3.js', 'utf8');
const recovered = recoverWindows1252ToUtf8(content);
fs.writeFileSync('js/direct-sales-v3.js', recovered, 'utf8');
console.log('Recovery attempted!');
