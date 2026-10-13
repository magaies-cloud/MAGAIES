/**
 * Instant Deployment for Mobile Suite
 * Deploys changes directly to https://c.elsajana.com/mobile-suite.php in ~5 seconds
 * Bypasses long GitHub Actions CI/CD pipelines
 */
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const repoDir = __dirname;
const msg = process.argv[2] || 'instant mobile update';

try {
    // 1. Sync mobile_erp_global_suite.html to mobile-suite.html if needed
    const master = path.join(repoDir, 'mobile_erp_global_suite.html');
    const mirror = path.join(repoDir, 'mobile-suite.html');
    if (fs.existsSync(master)) {
        fs.copyFileSync(master, mirror);
    }

    // 2. Commit and push
    console.log('[1/2] Pushing to repository...');
    execSync(`git -C "${repoDir}" add mobile-suite.html mobile_erp_global_suite.html`, { stdio: 'inherit' });
    try {
        execSync(`git -C "${repoDir}" commit -m "${msg}"`, { stdio: 'inherit' });
    } catch (e) {
        console.log('No new changes to commit.');
    }
    execSync(`git -C "${repoDir}" push origin main`, { stdio: 'inherit' });

    // 3. Fast Webhook pull on the live server (5 seconds)
    console.log('[2/2] Triggering instant live server sync...');
    execSync(`php "${path.join(repoDir, 'trigger_webhook.php')}"`, { stdio: 'inherit' });

    console.log('\n🚀 DEPLOYED INSTANTLY to: https://c.elsajana.com/mobile-suite.php');
} catch (err) {
    console.error('Deployment error:', err.message);
    process.exit(1);
}
