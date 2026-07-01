const { chromium } = require('playwright');
const path = require('path');

(async () => {
    console.log('Starting PDF generation...');
    const browser = await chromium.launch();
    const page = await browser.newPage();
    
    const filePath = `file:///${path.resolve(__dirname, 'diagram.html').replace(/\\/g, '/')}`;
    console.log('Loading page:', filePath);
    
    await page.goto(filePath, { waitUntil: 'networkidle' });
    
    // Wait a little extra time for Mermaid.js to finish rendering the SVG
    await page.waitForTimeout(2000);
    
    const pdfPath = path.resolve(__dirname, 'system_analysis.pdf');
    await page.pdf({ path: pdfPath, format: 'A4', printBackground: true });
    
    console.log(`PDF saved to ${pdfPath}`);
    await browser.close();
})();
