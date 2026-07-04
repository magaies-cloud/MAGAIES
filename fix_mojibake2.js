const fs = require('fs');

function fixMixedMojibake(content) {
    const utf8Regex = /([\xC2-\xDF][\x80-\xBF])|([\xE0-\xEF][\x80-\xBF]{2})|([\xF0-\xF4][\x80-\xBF]{3})/g;
    return content.replace(utf8Regex, (match) => {
        try {
            return Buffer.from(match, 'binary').toString('utf8');
        } catch(e) {
            return match;
        }
    });
}

const file = 'js/direct-sales.js';
let content = fs.readFileSync(file, 'utf8');

let original = content;
let fixed = fixMixedMojibake(content);

fs.writeFileSync(file, fixed, 'utf8');
console.log('Fixed mojibake cleanly.');
