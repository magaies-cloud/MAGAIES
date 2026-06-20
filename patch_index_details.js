const fs = require('fs');

const indexFile = 'index.html';
let content = fs.readFileSync(indexFile, 'utf8');

// Update CSS for ad-badge
const cssTarget = `        .ad-slide img {`;
const cssInject = `        .ad-badge {
            position: absolute;
            bottom: 15px;
            right: 20px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 5;
            font-family: 'Tajawal', sans-serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .ad-slide img {`;
if (content.includes(cssTarget) && !content.includes('.ad-badge')) {
    content = content.replace(cssTarget, cssInject);
}

// Update JS injection in index.html to include companyName
const renderAdHtmlTarget = `                                slide.innerHTML = \`<img src="\${ad.image}" alt="إعلان">\`;`;
const renderAdHtmlInject = `                                const detailsHtml = ad.companyName ? \`<div class="ad-badge">إعلان: \${ad.companyName}</div>\` : '';
                                slide.innerHTML = \`<img src="\${ad.image}" alt="إعلان">\${detailsHtml}\`;`;
if (content.includes(renderAdHtmlTarget)) {
    content = content.replace(renderAdHtmlTarget, renderAdHtmlInject);
}

fs.writeFileSync(indexFile, content, 'utf8');
console.log('index.html ads updated.');
