const fs = require('fs');
const path = require('path');

const dir = 'c:/xampp/htdocs/magaees';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

let updated = 0;
for (const file of files) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Extract online-users block
    // We can find it by its starting comment or class
    const onlineMatch = content.match(/<!-- Online Users Badge -->\s*<div class="online-users"[\s\S]*?<\/div>\s*<\/div>/);
    let onlineBlock = '';
    
    // Actually, looking at dashboard-pro.html:
    /*
        <!-- Online Users Badge -->
        <div class="online-users" style="...">
            <span style="...">...</span>
            <span style="...">متصل الآن</span>
        </div>
    */
    // Let's use a simpler match:
    const onlineRegex = /<!-- Online Users Badge -->[\s\S]*?<div class="online-users"[\s\S]*?<\/div>\s*<\/div>/;
    const match1 = content.match(/<!-- Online Users Badge -->[\s\S]*?<div class="online-users"[\s\S]*?<\/div>\s*<\/div>/);
    // Wait, </div> </div> is wrong. Let's just match div by div.
    
    // A better approach: 
    // We know the exact string or we can use regex to remove the existing online-users block.
    // In dashboard-pro.html:
    // <!-- Online Users Badge -->
    // <div class="online-users" style="display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); padding:4px 12px; border-radius:12px; min-width:70px;">
    //     <span style="color:#10b981; font-weight:800; font-size:1.1rem; display:flex; align-items:center; gap:6px;">
    //         <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981; animation:pulseDanger 2s infinite;"></span>
    //         <span id="online-users-count">3</span>
    //     </span>
    //     <span style="color:var(--text-secondary); font-size:0.65rem; font-weight:700;">متصل الآن</span>
    // </div>

    // Since I know the structure, let's just use string replace.
    
    // Remove the existing online-users block:
    let onlineBlockStr = `                <!-- Online Users Badge -->
                <div class="online-users" style="display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); padding:4px 12px; border-radius:12px; min-width:70px;">
                    <span style="color:#10b981; font-weight:800; font-size:1.1rem; display:flex; align-items:center; gap:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981; animation:pulseDanger 2s infinite;"></span>
                        <span id="online-users-count">3</span>
                    </span>
                    <span style="color:var(--text-secondary); font-size:0.65rem; font-weight:700;">متصل الآن</span>
                </div>`;
                
    content = content.replace(onlineBlockStr, "");
    // Try without spaces if it fails:
    if (content === original) {
        // Try regex
        content = content.replace(/<!-- Online Users Badge -->[\s\S]*?<div class="online-users"[\s\S]*?متصل الآن<\/span>\s*<\/div>/g, "");
    }

    // 2. Replace user-info block with the stacked version
    /*
        <div class="user-info" style="display: flex; align-items: center; gap: 10px; background: rgba(15,23,42,0.6); padding: 6px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
            <img id="header-user-avatar" src="..." alt="User" style="...">
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <span id="header-user-name" style="...">المدير العام</span>
                <span id="header-user-role" style="...">صلاحيات كاملة</span>
            </div>
        </div>
    */
    const stackedHtml = `
                <div class="user-stack" style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                    <!-- Online Users Badge -->
                    <div class="online-users" style="display:flex; flex-direction:column; align-items:center; justify-content:center; background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); padding:2px 12px; border-radius:10px; min-width:70px;">
                        <span style="color:#10b981; font-weight:800; font-size:0.95rem; display:flex; align-items:center; gap:6px;">
                            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#10b981; box-shadow:0 0 8px #10b981; animation:pulseDanger 2s infinite;"></span>
                            <span id="online-users-count">3</span>
                        </span>
                        <span style="color:var(--text-secondary); font-size:0.6rem; font-weight:700;">متصل الآن</span>
                    </div>

                    <!-- User Name Only -->
                    <div class="user-info" style="display: flex; align-items: center; justify-content: center; background: rgba(15,23,42,0.6); padding: 4px 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); min-width: 80px;">
                        <span id="header-user-name" style="color:#fff; font-weight:800; font-size:0.85rem;">جاري التحميل...</span>
                    </div>
                </div>`;

    // Regex to find existing user-info and replace it
    const userInfoRegex = /<div class="user-info"[\s\S]*?id="header-user-name"[\s\S]*?<\/div>\s*<\/div>/g;
    content = content.replace(userInfoRegex, stackedHtml);

    // Some files might have different formatting, let's catch edge cases
    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        updated++;
        console.log('Updated: ' + file);
    }
}
console.log('Total updated files: ' + updated);
