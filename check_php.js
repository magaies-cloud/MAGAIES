const fs = require('fs');
let php = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', 'utf8');

// The static fallback buttons are just placeholders, but it's cleaner if they have the correct CSS class 
// or if we just remove them since JS generates them anyway.
// But the replace I ran earlier php = php.replace(/class="preset-ribbon-btn"/g, 'class="luxury-action-btn"'); 
// should have replaced them! 
// Let's verify why they still have preset-ribbon-btn.
