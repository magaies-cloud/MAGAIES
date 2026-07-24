<?php
$cmd = 'git status 2>&1';
echo "<pre>";
echo shell_exec($cmd);
echo "</pre>";
echo "Done";
