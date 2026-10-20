<?php
$argvBefore = $argv;
require dirname(__DIR__, 2) . '/wp-load.php';
echo "argc=", $argc, "\n";
foreach ($argv as $k => $v) echo "$k=$v\n";
