<?php
$today = new DateTime('now');
$past = new DateTime('4 days ago');
$t = $today->format('Y-m-d H:i:s');
$p = $past->format('Y-m-d H:i:s');

$startDate = new DateTime($t);
$endDate = new DateTime($p);

echo $startDate->format('Y-M-d');
echo '<br />';
echo $endDate->format('Y-M-d');
 