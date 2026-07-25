<?php
$dir = 'c:\xampp\htdocs\ELSAJANA2\magaies';
$cmd = "cd $dir && set GIT_TERMINAL_PROMPT=0 && git pull origin main --no-edit 2>&1";
$output = shell_exec($cmd);
echo "<pre>$output</pre>";
?>
