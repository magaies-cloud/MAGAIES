const puppeteer = require('C:/xampp/htdocs/ELSAJANA2/magaies/node_modules/puppeteer');
const fs = require('fs');
const path = require('path');

const assetsDir = path.resolve('C:/xampp/htdocs/ELSAJANA2/magaies/assets');
if (!fs.existsSync(assetsDir)) {
  fs.mkdirSync(assetsDir, { recursive: true });
}

const svgIcon = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' width='512' height='512'>
  <defs>
    <radialGradient id='bgGrad' cx='50%' cy='50%' r='75%'>
      <stop offset='0%' stop-color='#0f172a'/>
      <stop offset='100%' stop-color='#020617'/>
    </radialGradient>
    <linearGradient id='glowGrad' x1='0%' y1='0%' x2='100%' y2='100%'>
      <stop offset='0%' stop-color='#38bdf8'/>
      <stop offset='50%' stop-color='#0ea5e9'/>
      <stop offset='100%' stop-color='#10b981'/>
    </linearGradient>
    <filter id='glow' x='-20%' y='-20%' width='140%' height='140%'>
      <feGaussianBlur stdDeviation='10' result='blur'/>
      <feComposite in='SourceGraphic' in2='blur' operator='over'/>
    </filter>
  </defs>
  <rect width='512' height='512' rx='128' fill='url(#bgGrad)' stroke='rgba(56,189,248,0.4)' stroke-width='6'/>
  <!-- Outer Diamond Accent -->
  <rect x='116' y='116' width='280' height='280' rx='48' transform='rotate(45 256 256)' fill='none' stroke='url(#glowGrad)' stroke-width='8' filter='url(#glow)' opacity='0.75'/>
  <!-- Center Emblem -->
  <g transform='translate(256, 256) scale(1.12) translate(-256, -256)'>
    <path d='M176 160h160c17.67 0 32 14.33 32 32v32c0 17.67-14.33 32-32 32H208c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h160' fill='none' stroke='url(#glowGrad)' stroke-width='36' stroke-linecap='round' stroke-linejoin='round'/>
    <circle cx='256' cy='256' r='20' fill='#38bdf8' filter='url(#glow)'/>
    <circle cx='176' cy='160' r='14' fill='#10b981'/>
    <circle cx='368' cy='352' r='14' fill='#38bdf8'/>
  </g>
  <text x='256' y='446' font-family='system-ui, sans-serif' font-size='34' font-weight='900' fill='#ffffff' text-anchor='middle' letter-spacing='4'>ELSAJANA</text>
  <text x='256' y='480' font-family='system-ui, sans-serif' font-size='20' font-weight='700' fill='#38bdf8' text-anchor='middle' letter-spacing='6'>ERP 2026</text>
</svg>`;

const svgPath = path.join(assetsDir, 'icon.svg');
fs.writeFileSync(svgPath, svgIcon);
console.log('Saved:', svgPath);

(async () => {
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  const page = await browser.newPage();

  const targets = [
    { file: path.join(assetsDir, 'icon-512.png'), size: 512 },
    { file: path.join(assetsDir, 'icon-maskable-512.png'), size: 512 },
    { file: path.join(assetsDir, 'icon-192.png'), size: 192 },
    { file: path.join(assetsDir, 'apple-touch-icon.png'), size: 180 },
    { file: path.join(assetsDir, 'favicon.ico'), size: 64 }
  ];

  for (const t of targets) {
    await page.setViewport({ width: t.size, height: t.size, deviceScaleFactor: 2 });
    const html = `<!DOCTYPE html><html><body style="margin:0;padding:0;background:transparent;display:flex;align-items:center;justify-content:center;height:${t.size}px;width:${t.size}px;overflow:hidden;">${svgIcon.replace(/width='512' height='512'/, `width='${t.size}' height='${t.size}'`)}</body></html>`;
    await page.setContent(html);
    await page.screenshot({ path: t.file, omitBackground: true });
    console.log(`Generated: ${t.file} (${t.size}x${t.size})`);
  }

  await browser.close();
  console.log('🎉 All PWA assets generated successfully!');
})();
