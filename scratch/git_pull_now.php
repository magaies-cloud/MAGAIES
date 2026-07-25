<?php
$dir = 'c:\xampp\htdocs\ELSAJANA2\magaies';
$output = shell_exec("cd $dir && set GIT_TERMINAL_PROMPT=0 && git pull origin main 2>&1");
echo "<pre>$output</pre>";
?>
