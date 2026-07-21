const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    
    // Log console messages
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.message));
    page.on('response', response => {
        if (!response.ok()) {
            console.log('HTTP ERROR:', response.url(), response.status());
        }
    });

    console.log('Navigating to login...');
    await page.goto('http://localhost:3000/login.php');

    // Simulate Auth
    await page.evaluate(() => {
        localStorage.setItem('magaiees_auth_token', 'mock_token');
        localStorage.setItem('erp_user_v1', JSON.stringify({id: 1, name: 'Admin'}));
    });

    console.log('Navigating to general-ledger.php...');
    await page.goto('http://localhost:3000/general-ledger.php');
    
    await new Promise(r => setTimeout(r, 2000));

    // Type in search box
    console.log('Typing in account search...');
    await page.type('#account-search-input', 'safe');
    
    // Wait for autocomplete
    await page.waitForSelector('#autocomplete-list li');
    
    // Click the first account
    console.log('Clicking the first account in autocomplete...');
    await page.click('#autocomplete-list li');

    // Wait a bit to see what happens
    await new Promise(r => setTimeout(r, 2000));

    console.log('Current URL after click:', page.url());

    await browser.close();
})();
