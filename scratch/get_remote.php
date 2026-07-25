<?php
$dir = 'c:\xampp\htdocs\ELSAJANA2\magaies';
$output = shell_exec("cd $dir && git config --get remote.origin.url 2>&1");
echo trim($output);
?>
