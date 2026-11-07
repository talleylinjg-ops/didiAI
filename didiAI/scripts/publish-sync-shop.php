<?php
// shop.daqi.asia 源站同步脚本：按 ID 白名单把产品 draft -> publish 并补 slug
// 用法：php publish-sync-shop.php ids.txt
// 幂等：已 publish 的 ID 自动跳过；slug 查重使用源站自身数据库
require __DIR__ . '/../../wp-load.php';

global $wpdb;

$listFile = isset($argv[1]) ? $argv[1] : __DIR__ . '/ids.txt';
if (!file_exists($listFile)) {
    fwrite(STDERR, "找不到 ID 列表: $listFile\n");
    exit(1);
}
$ids = array_filter(array_map('intval', file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
fwrite(STDERR, "目标 ID 数=" . count($ids) . "\n");

// 过滤出仍为 draft 的（幂等）
$inChunks = array_chunk($ids, 1000);
$pending = array();
foreach ($inChunks as $chunk) {
    $pl = implode(',', $chunk);
    $rows = $wpdb->get_results(
        "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID IN ($pl) AND post_type='product' AND post_status='draft'",
        ARRAY_A
    );
    foreach ($rows as $r) $pending[$r['ID']] = $r['post_title'];
}
fwrite(STDERR, "待处理 draft=" . count($pending) . "（其余已 publish，跳过）\n");

// slug 查重（源站全量 post_name）
$used = array();
$names = $wpdb->get_col("SELECT DISTINCT post_name FROM {$wpdb->posts} WHERE post_name <> ''");
foreach ($names as $nm) $used[$nm] = true;

$map = array();
foreach ($pending as $pid => $title) {
    $name = sanitize_title($title);
    if (!$name) $name = 'product-' . $pid;
    if (isset($used[$name])) {
        $i = 2;
        while (isset($used[$name . '-' . $i])) $i++;
        $name = $name . '-' . $i;
    }
    $used[$name] = true;
    $map[$pid] = $name;
}

// 500 条/批 CASE 更新
$idsArr = array_keys($map);
$now = current_time('mysql');
$nowGmt = gmdate('Y-m-d H:i:s');
$batch = 500;
$done = 0;
for ($i = 0; $i < count($idsArr); $i += $batch) {
    $slice = array_slice($idsArr, $i, $batch);
    if (!$slice) break;
    $caseParts = array();
    $params = array();
    foreach ($slice as $pid) {
        $caseParts[] = 'WHEN %d THEN %s';
        $params[] = $pid;
        $params[] = $map[$pid];
    }
    $inPh = implode(',', array_fill(0, count($slice), '%d'));
    $paramsAll = array_merge($params, $slice);
    $sql = "UPDATE {$wpdb->posts} SET post_name = CASE ID " . implode(' ', $caseParts)
        . ' ELSE post_name END, post_status=\'publish\', post_modified=\'' . $now . '\', post_modified_gmt=\'' . $nowGmt . '\''
        . " WHERE ID IN ($inPh)";
    $wpdb->query(call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $paramsAll)));
    $done += count($slice);
    fwrite(STDERR, "progress=$done\n");
}

fwrite(STDERR, "DONE 同步完成\n");
