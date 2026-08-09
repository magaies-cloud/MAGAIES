const fs = require('fs');
let php = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', 'utf8');

// Update static buttons with height 42px
php = php.replace(/class="luxury-action-btn" style="--btn-color:/g, 'class="luxury-action-btn" style="height: 42px; font-size: 1rem; padding: 5px 15px; border-radius: 12px; --btn-color:');

// Update Control Button and Visual Intelligence Hub
php = php.replace(
    /class="luxury-action-btn" (.*?) style="background:/g, 
    'class="luxury-action-btn"  style="height: 42px; font-size: 1rem; padding: 5px 15px; border-radius: 12px; background:'
);

fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', php, 'utf8');
