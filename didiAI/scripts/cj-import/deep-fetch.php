<?php
/**
 * CJ 产品列表深度抓取：绕过分页偏移上限 6000（按 SKU 前缀递归细分）
 * 用法：php deep-fetch.php [起始前缀]   （输出 append 到 staging.jsonl，幂等：pid 去重）
 */
if (PHP_SAPI !== 'cli') exit("CLI only\n");
require __DIR__ . '/cj-lib.php';

$prefix = isset($argv[1]) ? $argv[1] : 'CJT';
cjDeepFetch($prefix);
echo "DONE prefix=$prefix staging=" . cjStagingCount($GLOBALS['_CJ']) . "\n";
