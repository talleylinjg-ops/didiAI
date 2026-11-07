<?php
// 批量 publish 产品：draft -> publish，同时补全 slug
// 幂等：每轮取当前仍为 draft 的前 N 条，publish 后自动移出下一轮查询
require __DIR__ . '/../wp-load.php';

$limit = isset($argv[1]) ? intval($argv[1]) : 500;
if ($limit <= 0) $limit = 500;

$q = new WP_Query(array(
    'post_type' => 'product',
    'post_status' => 'draft',
    'posts_per_page' => $limit,
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
    'no_found_rows' => true,
));

$n = 0;
foreach ($q->posts as $id) {
    $p = get_post($id);
    if (!$p) continue;
    $name = $p->post_name;
    if (!$name) {
        $name = sanitize_title($p->post_title);
    }
    if (!$name) {
        $name = 'product-' . $id;
    }
    wp_update_post(array('ID' => $id, 'post_name' => $name, 'post_status' => 'publish'));
    $n++;
}

// 输出剩余 draft 数（游标判断）
global $wpdb;
$remain = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product' AND post_status='draft'");
fwrite(STDERR, "published=$n remain_draft=$remain\n");
echo $remain;
