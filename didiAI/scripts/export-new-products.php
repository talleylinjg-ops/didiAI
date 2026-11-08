<?php
// 导出本地新导入产品为 JSON，供 shop.daqi.asia 源站导入
// 用法：php export-new-products.php <输出.json> [起始日期YYYY-MM-DD]
if (PHP_SAPI !== 'cli') exit("CLI only\n");
if (!isset($_SERVER['HTTP_HOST'])) { $_SERVER['HTTP_HOST'] = 'localhost:8080'; $_SERVER['REQUEST_URI'] = '/'; }
require dirname(__DIR__) . '/wp-load.php';

global $wpdb;
$outFile = isset($argv[1]) ? $argv[1] : __DIR__ . '/products-data.json';
$since = isset($argv[2]) ? $argv[2] : '2026-11-04';

$ids = $wpdb->get_col($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish' AND post_date >= %s ORDER BY ID ASC",
    $since
));
fwrite(STDERR, "候选产品=" . count($ids) . " (post_date >= $since)\n");

// 排除内部/可自动生成的 meta
$skipMeta = array(
    '_edit_lock', '_edit_last', '_cj_images_migrated', '_wp_old_slug', '_wp_old_date',
    '_wc_rest_api_permissions', '_wc_review_count', '_wc_rating_count', '_wc_average_rating',
    '_cj_import_time', '_cj_import_batch',
);

$products = array();
foreach ($ids as $i => $pid) {
    $p = get_post($pid);
    if (!$p) continue;

    // SKU 幂等锚点
    $sku = get_post_meta($pid, '_sku', true);
    if (!$sku) continue;

    $meta = array();
    $all = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d", $pid), ARRAY_A);
    foreach ($all as $m) {
        $k = $m['meta_key'];
        if (in_array($k, $skipMeta, true)) continue;
        if (strpos($k, '_cj_r2_') === 0) continue;
        $meta[$k] = $m['meta_value'];
    }

    // 分类/标签/品牌（按 slug+name 对齐到源站）
    $terms = array();
    foreach (array('product_cat', 'product_tag', 'product_brand') as $tax) {
        $ts = wp_get_post_terms($pid, $tax, array('orderby' => 'none'));
        if (is_wp_error($ts) || !$ts) continue;
        $list = array();
        foreach ($ts as $t) {
            $parentSlug = '';
            if ($t->parent) {
                $pt = get_term($t->parent, $tax);
                if ($pt && !is_wp_error($pt)) $parentSlug = $pt->slug;
            }
            $list[] = array('slug' => $t->slug, 'name' => $t->name, 'parent' => $parentSlug, 'description' => $t->description);
        }
        if ($list) $terms[$tax] = $list;
    }

    $products[] = array(
        'sku' => $sku,
        'title' => $p->post_title,
        'slug' => $p->post_name,
        'content' => $p->post_content,
        'excerpt' => $p->post_excerpt,
        'status' => 'publish',
        'date' => $p->post_date,
        'menu_order' => $p->menu_order,
        'meta' => $meta,
        'terms' => $terms,
        'images' => get_post_meta($pid, '_cj_images', true) ?: array(),
    );
    if (($i + 1) % 100 === 0) fwrite(STDERR, "exported=" . ($i + 1) . "\n");
}

file_put_contents($outFile, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
fwrite(STDERR, "DONE 导出=" . count($products) . " -> $outFile (" . round(filesize($outFile) / 1048576, 1) . "MB)\n");
