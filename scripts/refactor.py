import os
import re

directory = "c:\\xampp\\htdocs\\magaees"
includes_dir = os.path.join(directory, "includes")

if not os.path.exists(includes_dir):
    os.makedirs(includes_dir)

# 1. Write includes/header.php
header_content = """<header class="premium-header" style="display:flex; justify-content:space-between; align-items:center; padding:15px; margin-bottom:15px; gap:15px; border-radius: 16px;">
    <!-- Left: Logo / Brand -->
    <div style="display:flex; align-items:center; gap:12px; flex:1;">
        <div style="background:linear-gradient(135deg,#10b981,#34d399); width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">📦</div>
        <div>
            <div style="color:#fff; font-weight:800; font-size:1rem; letter-spacing:0.5px;" id="header-company-name-top">السجانة ERP</div>
            <div style="color:var(--text-secondary); font-size:0.7rem;">نظام إدارة الأعمال</div>
        </div>
    </div>

    <!-- Center: Page Title -->
    <div style="flex:1; text-align:center;">
        <h2 style="margin:0; color:#fff; font-size:1.2rem; font-weight:900; white-space:nowrap; text-shadow: 0 2px 10px rgba(16,185,129,0.5);"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'السجانة ERP'; ?></h2>
    </div>

    <!-- Right: Controls -->
    <div style="display:flex; align-items:center; gap:12px; flex:1; justify-content:flex-end;">
        <!-- Clock -->
        <div style="display:flex; flex-direction:column; align-items:center; background:rgba(15,23,42,0.6); padding:6px 14px; border-radius:12px; border:1px solid rgba(255,255,255,0.08); min-width:110px;">
            <span id="header-clock" style="color:#34d399; font-size:1.1rem; font-weight:800; font-variant-numeric:tabular-nums; letter-spacing:1px; text-shadow: 0 0 10px rgba(16,185,129,0.5);">00:00:00</span>
            <span id="header-date-mini" style="color:var(--text-secondary); font-size:0.65rem; margin-top:1px;">جاري التحميل...</span>
        </div>
        <!-- Theme -->
        <div style="display:flex; align-items:center; gap:8px;">
            <button class="theme-toggle-btn" style="background:rgba(255,255,255,0.1); border:none; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" title="تبديل المظهر">🌓</button>
            <button class="back-arrow-btn" onclick="history.back()" style="background:rgba(255,255,255,0.1); border:none; border-radius:12px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1.1rem; color:var(--text-primary); transition:0.2s;" title="رجوع">🔙</button>
        </div>
    </div>
</header>
"""
with open(os.path.join(includes_dir, "header.php"), "w", encoding="utf-8") as f:
    f.write(header_content)

html_files = [f for f in os.listdir(directory) if f.endswith('.html')]

# We'll build a map of old filename to new filename to update internal links
link_replacements = {f: f.replace('.html', '.php') for f in html_files}

for filename in html_files:
    filepath = os.path.join(directory, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # 1. Extract <title>
    title_match = re.search(r'<title>(.*?)</title>', content)
    page_title = "السجانة ERP"
    if title_match:
        # e.g., "مرتجع مبيعات | Al-Sajana ERP" -> we just want the first part, or whole
        page_title = title_match.group(1).split('|')[0].strip()
    
    # 2. Add PHP title block at the very top of the file
    php_prepend = f"<?php $page_title = '{page_title}'; ?>\n"
    content = php_prepend + content
    
    # 3. Replace <header class="premium-header">...</header>
    # Using regex to find the header block. It might span multiple lines.
    header_pattern = re.compile(r'<header class="premium-header".*?</header>', re.DOTALL)
    if header_pattern.search(content):
        content = header_pattern.sub("<?php include 'includes/header.php'; ?>", content)
    
    # 4. Replace .html links with .php
    for old_html, new_php in link_replacements.items():
        content = content.replace(f'"{old_html}"', f'"{new_php}"')
        content = content.replace(f"'{old_html}'", f"'{new_php}'")
        content = content.replace(f'={old_html}', f'={new_php}') # just in case
        
    # Save as .php and delete .html
    new_filepath = os.path.join(directory, filename.replace('.html', '.php'))
    with open(new_filepath, "w", encoding="utf-8") as f:
        f.write(content)
        
    os.remove(filepath)

print("Migration completed successfully.")
