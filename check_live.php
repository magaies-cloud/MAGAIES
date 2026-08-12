<?php
$c = file_get_contents('https://c.elsajana.com/js/single-cash-payment.js');
if (strpos($c, 'window.location.href = window.location.pathname;') !== false) {
    if (strpos($c, "}\r\n}") !== false || strpos($c, "}\n}") !== false) {
        echo 'LIVE SITE HAS BROKEN JS!';
    } else {
        echo 'LIVE SITE HAS FIXED JS!';
    }
} else {
    echo 'LIVE SITE HAS OLD JS WITHOUT RELOAD!';
}
