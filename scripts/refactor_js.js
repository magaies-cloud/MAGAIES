const fs = require('fs');
const path = require('path');

const directory = 'c:\\xampp\\htdocs\\magaees\\js';
const rootDir = 'c:\\xampp\\htdocs\\magaees';

// Get list of php files that were html
const rootFiles = fs.readdirSync(rootDir);
const phpFiles = rootFiles.filter(f => f.endsWith('.php'));

function walkSync(dir, filelist) {
    const files = fs.readdirSync(dir);
    filelist = filelist || [];
    files.forEach(function(file) {
        if (fs.statSync(path.join(dir, file)).isDirectory()) {
            filelist = walkSync(path.join(dir, file), filelist);
        }
        else {
            filelist.push(path.join(dir, file));
        }
    });
    return filelist;
}

const jsFiles = walkSync(directory);
// Also include sw.js
jsFiles.push(path.join(rootDir, 'sw.js'));

jsFiles.forEach(filepath => {
    if (!filepath.endsWith('.js')) return;
    
    let content = fs.readFileSync(filepath, 'utf8');
    let originalContent = content;
    
    phpFiles.forEach(phpFile => {
        const htmlFile = phpFile.replace('.php', '.html');
        // We only want to replace if it's exact match string
        content = content.split(`'${htmlFile}'`).join(`'${phpFile}'`);
        content = content.split(`"${htmlFile}"`).join(`"${phpFile}"`);
        content = content.split(`\`${htmlFile}\``).join(`\`${phpFile}\``);
        content = content.split(`=${htmlFile}`).join(`=${phpFile}`);
    });
    
    if (content !== originalContent) {
        fs.writeFileSync(filepath, content, 'utf8');
        console.log("Updated: " + filepath);
    }
});

console.log("JS Update completed successfully.");
