<?php
$lines = file('general_ledger_v2.php');
foreach($lines as $k=>$v) {
    if(preg_match('/(?:شركة النيل|محلات أولاد|المقاولات السودانية)/ui', $v)) {
        echo 'Line '.($k+1).': '.$v;
    }
}
