const fs = require('fs');

const indexFile = 'index.html';
let indexContent = fs.readFileSync(indexFile, 'utf8');

const targetStr = `                // Render Partners`;

const adsLogic = `                // Ads Logic
                if (data && data.ads && data.ads.length > 0) {
                    const activeAds = data.ads.filter(ad => ad.active);
                    if (activeAds.length > 0) {
                        const banner = document.getElementById('ads-banner');
                        banner.style.display = 'block';
                        
                        activeAds.forEach((ad, index) => {
                            const slide = document.createElement('div');
                            slide.className = 'ad-slide' + (index === 0 ? ' active' : '');
                            slide.innerHTML = \`<img src="\${ad.image}" alt="إعلان">\`;
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

`;

if (indexContent.includes(targetStr) && !indexContent.includes('// Ads Logic')) {
    indexContent = indexContent.replace(targetStr, adsLogic + targetStr);
    fs.writeFileSync(indexFile, indexContent, 'utf8');
    console.log('index.html JS patched successfully.');
} else {
    console.log('Could not find target string or already patched.');
}
