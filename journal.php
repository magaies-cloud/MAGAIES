<?php
// تم دمج وتوحيد دفتر اليومية وسند قيد اليومية في الاستوديو المحاسبي الموحد: journal-voucher.php
$qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
header('Location: journal-voucher.php' . $qs);
exit;