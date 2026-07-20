<?php
$f = file_get_contents('alerts_manager.php');
$f = str_replace("WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' AND", "WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' AND", $f);
file_put_contents('alerts_manager.php', $f);
echo "alerts_manager.php double WHERE fixed\n";
