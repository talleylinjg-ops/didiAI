<?php
// 快速批量 publish：PHP 预生成 slug（含冲突后缀），SQL CASE 批量更新
require __DIR__ . '/../wp-load.php';

global $wpdb;

$ids = $wpdb->get_results(
    "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='product' AND post_status='draft' ORDER BY ID ASC",
    ARRAY_A
);
fwrite(STDERR, "draft=" . count($ids) . "\n");

// 已占用的 slug（全部 post_type 的 product 层级内需唯一，此处按 post_name 全局查已存在值）
$used = array();
$names = $wpdb->get_col("SELECT DISTINCT post_name FROM {$wpdb->posts} WHERE post_name <> ''");
foreach ($names as $nm) $used[$nm] = true;
fwrite(STDERR, "existing_names=" . count($used) . "\n");

$map = array();
foreach ($ids as $r) {
    $name = sanitize_title($r['post_title']);
    if (!$name) $name = 'product-' . $r['ID'];
    if (isset($used[$name])) {
        $i = 2;
        while (isset($used[$name . '-' . $i])) $i++;
        $name = $name . '-' . $i;
    }
    $used[$name] = true;
    $map[$r['ID']] = $name;
}

// 500 条/批：CASE 批量写 post_name，同批置 publish
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
    $inPlaceholders = implode(',', array_fill(0, count($slice), '%d'));
    $paramsAll = array_merge($params, $slice);
    $sql = "UPDATE {$wpdb->posts} SET post_name = CASE ID " . implode(' ', $caseParts)
        . ' ELSE post_name END, post_status=\'publish\', post_modified=\''
        . $now . '\', post_modified_gmt=\'' . $nowGmt . '\''
        . " WHERE ID IN ($inPlaceholders)";
    $wpdb->query(call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $paramsAll)));
    $done += count($slice);
    fwrite(STDERR, "progress=$done\n");
}

$remain = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product' AND post_status='draft'");
$pub = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'");
fwrite(STDERR, "DONE remain_draft=$remain publish=$pub\n");
echo $remain;
