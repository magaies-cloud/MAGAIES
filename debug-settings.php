<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Debug Settings</title>
<style>
body { font-family: monospace; background: #1a1a2e; color: #0f0; padding: 20px; font-size: 14px; }
pre { background: #0d0d1a; padding: 15px; border: 1px solid #333; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; }
h2 { color: #f59e0b; }
button { background: #10b981; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin: 5px; font-size: 16px; }
input { background: #222; color: #fff; border: 1px solid #555; padding: 10px; font-size: 18px; width: 200px; border-radius: 5px; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #333; border-radius: 10px; }
</style>
</head>
<body>
<h1>🔍 تشخيص مشكلة حفظ سعر الصرف</h1>

<div class="section">
<h2>1. اختبار الحفظ والقراءة</h2>
<input type="text" id="test-rate" placeholder="اكتب سعر الصرف هنا" inputmode="decimal">
<button onclick="testSave()">💾 حفظ تجريبي</button>
<button onclick="testRead()">📖 قراءة</button>
<button onclick="testAll()">🔄 فحص شامل</button>
<pre id="output">جاهز...</pre>
</div>

<div class="section">
<h2>2. كل المفاتيح المتعلقة بالإعدادات</h2>
<button onclick="scanAllKeys()">🔎 فحص جميع المفاتيح</button>
<pre id="keys-output">-</pre>
</div>

<div class="section">
<h2>3. تتبع الأحداث الزمنية</h2>
<pre id="timeline">-</pre>
</div>

<script defer src="js/localforage.min.js"></script>
<script defer src="js/app.js?v=debug"></script>
<script>
const log = [];
function addLog(msg) {
    const ts = new Date().toLocaleTimeString();
    log.push(`[${ts}] ${msg}`);
    document.getElementById('timeline').textContent = log.join('\n');
}

// Track all storage events
window.addEventListener('storage', function(e) {
    if (e.key && (e.key.includes('settings') || e.key.includes('rate'))) {
        addLog(`⚡ storage event: key=${e.key}, newValue=${e.newValue ? e.newValue.substring(0, 80) : 'null'}`);
    }
});

document.addEventListener('DOMContentLoaded', () => addLog('DOMContentLoaded'));
document.addEventListener('DBReady', () => addLog('DBReady'));
window.addEventListener('strictDataReady', () => addLog('strictDataReady'));
window.addEventListener('cloudDataRestored', () => {
    addLog('cloudDataRestored - READING SETTINGS NOW:');
    readAndLog();
});

function readAndLog() {
    const out = [];
    // 1. Check window.getTenantKey
    const tenantKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
    out.push(`tenantKey = "${tenantKey}"`);

    // 2. Check DB_STORE
    const dbVal = window.DB_STORE ? window.DB_STORE[tenantKey] : null;
    if (dbVal) {
        try {
            const parsed = JSON.parse(dbVal);
            out.push(`DB_STORE[${tenantKey}].rateUSD = ${parsed.rateUSD}`);
            out.push(`DB_STORE[${tenantKey}].exchangeMode = ${parsed.exchangeMode}`);
        } catch(e) {
            out.push(`DB_STORE[${tenantKey}] = (not JSON: ${String(dbVal).substring(0,50)})`);
        }
    } else {
        out.push(`DB_STORE[${tenantKey}] = NULL/undefined`);
    }

    // 3. Check DB_STORE with base key
    const dbVal2 = window.DB_STORE ? window.DB_STORE['erp_settings_v1'] : null;
    if (dbVal2) {
        try {
            const parsed = JSON.parse(dbVal2);
            out.push(`DB_STORE['erp_settings_v1'].rateUSD = ${parsed.rateUSD}`);
        } catch(e) {
            out.push(`DB_STORE['erp_settings_v1'] = (not JSON)`);
        }
    } else {
        out.push(`DB_STORE['erp_settings_v1'] = NULL`);
    }

    // 4. Check raw localStorage (original)
    try {
        const rawOriginal = Storage.prototype.getItem.call(localStorage, tenantKey);
        if (rawOriginal) {
            try {
                const parsed = JSON.parse(rawOriginal);
                out.push(`localStorage.getItem(proxy)(${tenantKey}).rateUSD = ${parsed.rateUSD}`);
            } catch(e) { out.push(`proxy getItem = not JSON`); }
        } else {
            out.push(`proxy getItem(${tenantKey}) = NULL`);
        }
    } catch(e) { out.push(`proxy getItem error: ${e.message}`); }

    // 5. Check erp_rate_usd dedicated key
    const rateKey = localStorage.getItem('erp_rate_usd');
    out.push(`localStorage.getItem('erp_rate_usd') = ${rateKey}`);

    // 6. Check if the proxy getItem reads from DB_STORE
    const proxyResult = localStorage.getItem('erp_settings_v1');
    if (proxyResult) {
        try {
            const parsed = JSON.parse(proxyResult);
            out.push(`localStorage.getItem('erp_settings_v1')[PROXY].rateUSD = ${parsed.rateUSD}`);
        } catch(e) {
            out.push(`localStorage.getItem('erp_settings_v1')[PROXY] = not JSON`);
        }
    } else {
        out.push(`localStorage.getItem('erp_settings_v1')[PROXY] = NULL`);
    }

    addLog(out.join('\n    '));
}

function testSave() {
    const val = document.getElementById('test-rate').value;
    const rate = parseFloat(val) || 0;
    const out = [];
    
    if (rate <= 0) {
        out.push('❌ الرجاء كتابة رقم صحيح');
        document.getElementById('output').textContent = out.join('\n');
        return;
    }

    const tenantKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
    out.push(`Rate to save: ${rate}`);
    out.push(`Tenant key: ${tenantKey}`);

    // Read current
    let currentCfg = {};
    try {
        currentCfg = JSON.parse(localStorage.getItem('erp_settings_v1') || '{}');
    } catch(e) {}
    
    out.push(`Current rateUSD before save: ${currentCfg.rateUSD}`);
    
    currentCfg.rateUSD = rate;
    currentCfg.exchangeMode = 'manual';
    currentCfg.savedAt = new Date().toISOString();
    
    const strVal = JSON.stringify(currentCfg);
    
    // Method 1: Through proxy
    try {
        localStorage.setItem('erp_settings_v1', strVal);
        out.push('✅ localStorage.setItem(proxy) - OK');
    } catch(e) {
        out.push(`❌ localStorage.setItem(proxy) error: ${e.message}`);
    }
    
    // Method 2: Direct to DB_STORE
    if (window.DB_STORE) {
        window.DB_STORE[tenantKey] = strVal;
        window.DB_STORE['erp_settings_v1'] = strVal;
        out.push('✅ DB_STORE direct write - OK');
    }
    
    // Verify immediately
    const verify = localStorage.getItem('erp_settings_v1');
    try {
        const parsed = JSON.parse(verify);
        out.push(`✅ Verify after save: rateUSD = ${parsed.rateUSD}`);
    } catch(e) {
        out.push(`❌ Verify failed: ${e.message}`);
    }
    
    document.getElementById('output').textContent = out.join('\n');
    addLog(`SAVE: rate=${rate}, tenantKey=${tenantKey}`);
}

function testRead() {
    readAndLog();
    const out = [];
    const tenantKey = window.getTenantKey ? window.getTenantKey('erp_settings_v1') : 'erp_settings_v1';
    
    // Same logic as loadSettings
    let cfgRaw = null;
    try { cfgRaw = Storage.prototype.getItem.call(localStorage, tenantKey) || Storage.prototype.getItem.call(localStorage, 'erp_settings_v1'); } catch(e){}
    if (!cfgRaw) {
        cfgRaw = (window.DB_STORE && window.DB_STORE[tenantKey]) || localStorage.getItem(tenantKey) || localStorage.getItem('erp_settings_v1') || '{}';
    }
    
    let cfg = {};
    try { cfg = JSON.parse(cfgRaw); } catch(e) { cfg = {}; }
    
    out.push(`loadSettings would read:`);
    out.push(`  rateUSD = ${cfg.rateUSD}`);
    out.push(`  exchangeMode = ${cfg.exchangeMode}`);
    out.push(`  savedAt = ${cfg.savedAt}`);
    out.push(`  companyName = ${cfg.companyName}`);
    
    document.getElementById('output').textContent = out.join('\n');
}

function testAll() {
    testRead();
}

function scanAllKeys() {
    const out = [];
    const allKeys = Object.keys(localStorage);
    
    out.push(`Total localStorage keys: ${allKeys.length}`);
    out.push(`---`);
    
    allKeys.forEach(key => {
        if (key.includes('settings') || key.includes('rate') || key.includes('erp_settings')) {
            const val = Storage.prototype.getItem.call(localStorage, key);
            if (val) {
                try {
                    const parsed = JSON.parse(val);
                    out.push(`${key}:`);
                    out.push(`  rateUSD = ${parsed.rateUSD}`);
                    out.push(`  exchangeMode = ${parsed.exchangeMode}`);
                    out.push(`  savedAt = ${parsed.savedAt}`);
                } catch(e) {
                    out.push(`${key} = ${val.substring(0, 100)}`);
                }
            }
        }
    });
    
    // Also check DB_STORE
    if (window.DB_STORE) {
        out.push(`\n--- DB_STORE ---`);
        Object.keys(window.DB_STORE).forEach(key => {
            if (key.includes('settings') || key.includes('rate')) {
                const val = window.DB_STORE[key];
                if (val) {
                    try {
                        const parsed = JSON.parse(val);
                        out.push(`DB_STORE[${key}]:`);
                        out.push(`  rateUSD = ${parsed.rateUSD}`);
                        out.push(`  exchangeMode = ${parsed.exchangeMode}`);
                        out.push(`  savedAt = ${parsed.savedAt}`);
                    } catch(e) {
                        out.push(`DB_STORE[${key}] = ${String(val).substring(0, 100)}`);
                    }
                }
            }
        });
    }
    
    document.getElementById('keys-output').textContent = out.join('\n');
}

// Auto-scan after 3 seconds
setTimeout(() => {
    addLog('Auto-scan after 3 seconds:');
    readAndLog();
    scanAllKeys();
}, 3000);
</script>

<script src="js/global_background_media.js?v=2026_seamless_v3" defer></script>
<script src="js/magaiees_back.js?v=2026_back_v2" defer></script>
</body>
</html>
