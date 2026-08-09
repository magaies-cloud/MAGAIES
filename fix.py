import re

with open('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Fix isDefaultGroup line
content = re.sub(r'const isDefaultGroup = idx < 4 \|\| \(p\.name && p\.name\.includes\([^)]+\)\);', 
                 "const isDefaultGroup = idx < 4 || (p.name && p.name.includes('ÇÝÊÑÇÖí'));", content)

# Fix button titles
content = re.sub(r"btn\.innerHTML = p\.name \+ ' \?\?\?';\s*btn\.title = '[^']+';", 
                 "btn.innerHTML = p.name + ' ??';\n            btn.title = 'ÞÇáÈ ÖÈØ ãÕäÚ ãÞÝá (ááÚÑÖ ÝÞØ)';", content, count=1)

content = re.sub(r"btn\.innerHTML = p\.name \+ ' \?\?\?';\s*btn\.title = '[^']+';", 
                 "btn.innerHTML = p.name + ' ??';\n            btn.title = 'ÇäÞÑ áÇÓÊÑÌÇÚ ÇáäãæÐÌ Ãæ ÍÝÙ ÇáÊÚÏíáÇÊ ??';", content, count=1)

with open('C:/xampp/htdocs/ELSAJANA2/magaies/js/mainbalances-report.js', 'w', encoding='utf-8') as f:
    f.write(content)
