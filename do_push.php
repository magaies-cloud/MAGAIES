<?php
$output = shell_exec('git add . 2>&1');
$output .= shell_exec('git commit -m "Auto-update: UI CSS fixes for buttons" 2>&1');
$output .= shell_exec('git push origin main 2>&1');
echo "<pre>$output</pre>";
