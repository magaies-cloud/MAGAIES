import os

directory = "c:\\xampp\\htdocs\\magaees\\js"

# List of files we expect to exist from html_files
# We don't want to replace "index.html" with "index.php" if it's a random string, only if it matches our pages.
# Let's get the list of original html files.
html_dir = "c:\\xampp\\htdocs\\magaees"
html_files = [f for f in os.listdir(html_dir) if f.endswith('.html')]
# Wait, if they are already converted, they are .php now!
# Let's just find the .php files that used to be .html
php_files = [f for f in os.listdir(html_dir) if f.endswith('.php')]

for root, dirs, files in os.walk(directory):
    for file in files:
        if file.endswith('.js'):
            filepath = os.path.join(root, file)
            with open(filepath, "r", encoding="utf-8") as f:
                content = f.read()
            
            original_content = content
            for php_file in php_files:
                # E.g. 'direct-sales.php'
                html_file = php_file.replace('.php', '.html')
                content = content.replace(f"'{html_file}'", f"'{php_file}'")
                content = content.replace(f'"{html_file}"', f'"{php_file}"')
                content = content.replace(f'`{html_file}`', f'`{php_file}`')
                content = content.replace(f'={html_file}', f'={php_file}')
            
            if content != original_content:
                with open(filepath, "w", encoding="utf-8") as f:
                    f.write(content)
                print(f"Updated JS file: {file}")

print("JS Update completed successfully.")
