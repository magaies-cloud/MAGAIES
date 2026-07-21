const puppeteer = require('puppeteer');

(async () => {
    try {
        const browser = await puppeteer.launch({ headless: "new" });
        const page = await browser.newPage();

        const logs = [];
        const errors = [];

        page.on('console', msg => {
            const text = msg.text();
            logs.push('[' + msg.type().toUpperCase() + '] ' + text);
        });
        page.on('pageerror', error => {
            errors.push('[PAGE ERROR] ' + error.message);
        });

        // Step 1: Go to login page
        console.log("1. Navigating to login page...");
        await page.goto('http://localhost:3000/login.php', { waitUntil: 'networkidle2', timeout: 15000 });
        
        // Step 2: Check what's on the login page
        const loginFields = await page.evaluate(() => {
            const inputs = document.querySelectorAll('input');
            return Array.from(inputs).map(i => ({id: i.id, name: i.name, type: i.type, placeholder: i.placeholder}));
        });
        console.log("Login page inputs:", JSON.stringify(loginFields, null, 2));

        // Step 3: Try to log in with test credentials
        // First check if there's an authToken we can set manually
        // Let's just set an authToken in localStorage and navigate directly
        console.log("\n2. Setting up auth and navigating to balances-report...");
        
        // Try setting a mock auth state
        await page.evaluate(() => {
            // Set a mock auth token - the exact format depends on the app
            localStorage.setItem('authToken', 'test-debug-token');
            localStorage.setItem('companyName', 'test');
            localStorage.setItem('user', JSON.stringify({username: 'admin', role: 'admin'}));
        });
        
        await page.goto('http://localhost:3000/balances-report.php', { waitUntil: 'networkidle2', timeout: 15000 });

        // Wait for DBReady or timeout
        const appReady = await page.evaluate(() => {
            return new Promise((resolve) => {
                if (window.APP_READY) {
                    resolve('APP_READY=true');
                } else {
                    document.addEventListener('DBReady', () => resolve('DBReady fired'), { once: true });
                    setTimeout(() => resolve('TIMEOUT - DBReady never fired'), 5000);
                }
            });
        });
        console.log("3. App status: " + appReady);

        // Check if we got redirected
        const currentUrl = page.url();
        console.log("4. Current URL: " + currentUrl);

        // Check function availability
        const funcs = await page.evaluate(() => {
            return {
                runReport: typeof window.runReport,
                loadData: typeof window.loadData,
                renderTable: typeof window.renderTable,
                APP_READY: window.APP_READY,
                ALL_PARTNERS: typeof ALL_PARTNERS !== 'undefined' ? ALL_PARTNERS.length : 'undefined',
                ALL_TXNS: typeof ALL_TXNS !== 'undefined' ? ALL_TXNS.length : 'undefined',
            };
        });
        console.log("5. Functions & state:", JSON.stringify(funcs, null, 2));

        if (funcs.runReport === 'function') {
            console.log("\n6. Calling runReport directly...");
            const result = await page.evaluate(() => {
                try {
                    runReport();
                    return 'SUCCESS - runReport completed';
                } catch(e) {
                    return 'ERROR: ' + e.message + '\n' + e.stack;
                }
            });
            console.log("   Result: " + result);

            await new Promise(r => setTimeout(r, 1000));

            const tbodyText = await page.evaluate(() => {
                const tbody = document.querySelector('#balances-tbody');
                if (!tbody) return 'NO TBODY FOUND';
                const rows = tbody.querySelectorAll('tr');
                return rows.length + ' rows. First row: ' + (rows[0] ? rows[0].innerText.substring(0, 200) : 'empty');
            });
            console.log("7. Table result: " + tbodyText);
        }

        console.log("\n=== ERRORS ===");
        errors.forEach(e => console.log(e));
        
        console.log("\n=== RELEVANT LOGS ===");
        logs.filter(l => !l.includes('font') && !l.includes('Password')).forEach(l => console.log(l));

        await browser.close();
        console.log("\nDone.");
    } catch (err) {
        console.error("Puppeteer Error:", err.message);
    }
})();
