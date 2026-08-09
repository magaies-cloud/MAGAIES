const fs = require('fs');
let php = fs.readFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', 'utf8');

// Replace static fallback buttons classes
php = php.replace(/class="preset-ribbon-btn preset-slot-0"/g, 'class="luxury-action-btn" style="--btn-color: #38bdf8;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-1"/g, 'class="luxury-action-btn" style="--btn-color: #eab308;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-2"/g, 'class="luxury-action-btn" style="--btn-color: #a855f7;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-3"/g, 'class="luxury-action-btn" style="--btn-color: #ef4444;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-4"/g, 'class="luxury-action-btn" style="--btn-color: #10b981;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-5"/g, 'class="luxury-action-btn" style="--btn-color: #f97316;"');
php = php.replace(/class="preset-ribbon-btn preset-slot-6"/g, 'class="luxury-action-btn" style="--btn-color: #ec4899;"');

fs.writeFileSync('C:/xampp/htdocs/ELSAJANA2/magaies/mainbalances-report.php', php, 'utf8');
